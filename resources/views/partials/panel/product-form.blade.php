@php($product = $product ?? null)

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="name">Product Title</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name', $product?->name) }}" placeholder="Enter product title"
                        required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                            name="category_id" required>
                            <option value="" disabled @selected(! old('category_id', $product?->category_id))>Choose a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id', $product?->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="price">Price (&#8377;)</label>
                        <input type="number" step="0.01" min="0"
                            class="form-control @error('price') is-invalid @enderror" id="price" name="price"
                            value="{{ old('price', $product?->price) }}" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="stock">Stock</label>
                        <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror"
                            id="stock" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea id="editor-description" name="description">{{ old('description', $product?->description) }}</textarea>
                    @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Additional Information</label>
                    <textarea id="editor-additional" name="additional_info">{{ old('additional_info', $product?->additional_info) }}</textarea>
                    @error('additional_info')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Images</h5>
            </div>
            <div class="card-body">
                @if ($product && $product->images->isNotEmpty())
                    <label class="form-label">Current images</label>
                    <div class="row mb-3">
                        @foreach ($product->images as $image)
                            <div class="col-6 mb-2">
                                <div class="card mb-0">
                                    <img src="{{ asset($image->path) }}" class="card-img-top"
                                        style="height:110px;object-fit:cover">
                                    <div class="card-body p-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remove_images[]"
                                                value="{{ $image->id }}" id="remove-{{ $image->id }}">
                                            <label class="form-check-label small text-danger"
                                                for="remove-{{ $image->id }}">Remove</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <label class="form-label" for="product-images">Add images</label>
                <input type="file" id="product-images" class="form-control @error('images.*') is-invalid @enderror"
                    multiple accept="image/jpeg,image/png,image/webp,image/gif">
                <small class="text-muted">JPG, PNG, WEBP or GIF. Up to 2 MB each.</small>
                @error('images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                <div class="mt-3">
                    <div id="image-preview" class="row"></div>
                </div>

                {{-- Populated from the preview widget, which is what actually gets submitted. --}}
                <div id="image-input-holder"></div>
            </div>
        </div>
    </div>
</div>

<div class="text-end mb-3">
    <a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-success w-sm">{{ $submitLabel }}</button>
</div>
