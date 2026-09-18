<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Creating and updating a product, including its images.
 *
 * Shared by the admin and merchant panels so both go through the same
 * validation, the same slug rules and the same upload handling.
 */
class SaveProduct
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function create(array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($data, $images) {
            $product = Product::create([
                ...$data,
                'slug' => $this->uniqueSlug($data['name']),
            ]);

            $this->addImages($product, $images);

            return $product;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @param  array<int, int>  $removeImageIds
     */
    public function update(Product $product, array $data, array $images = [], array $removeImageIds = []): Product
    {
        return DB::transaction(function () use ($product, $data, $images, $removeImageIds) {
            // The slug is part of the product's public address, so it only
            // changes when the name does — an edit should not break links or
            // anything already pointing at it.
            if ($data['name'] !== $product->name) {
                $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
            }

            $product->update($data);

            $this->removeImages($product, $removeImageIds);
            $this->addImages($product, $images);

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                $this->deleteFile($image->path);
            }

            $product->images()->delete();

            // Soft deleted: order lines still point at it, and their history
            // must stay readable.
            $product->delete();
        });
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function addImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            // The framework names the file. A client-supplied name could carry
            // a second extension and land as something executable.
            $name = Str::uuid().'.'.$image->extension();

            $image->storeAs('', $name, 'products');

            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'images/products/'.$name,
            ]);
        }
    }

    /**
     * @param  array<int, int>  $imageIds
     */
    private function removeImages(Product $product, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $images = $product->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            $this->deleteFile($image->path);
            $image->delete();
        }
    }

    private function deleteFile(string $path): void
    {
        $name = basename($path);

        if (Storage::disk('products')->exists($name)) {
            Storage::disk('products')->delete($name);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 1;

        while (
            Product::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
