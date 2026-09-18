<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware and the product policy decide who gets this far.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:99999'],
            'description' => ['required', 'string'],
            'additional_info' => ['nullable', 'string'],

            /*
             * Uploads are checked for type and size, and the framework renames
             * every file. A client-supplied name is never trusted, and nothing
             * but a real image is accepted.
             */
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],

            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.*.image' => 'Each upload must be an image file.',
            'images.*.max' => 'Images must be 2 MB or smaller.',
            'images.max' => 'You can attach at most 8 images.',
        ];
    }
}
