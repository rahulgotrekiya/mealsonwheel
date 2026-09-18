@php($category = $category ?? null)

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name', $category?->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="slug">Slug</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug"
                        name="slug" value="{{ old('slug', $category?->slug) }}" required>
                    <small class="text-muted">Used in the category's web address, for example
                        <code>/category/dog</code>.</small>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="image">Image</label>
                    @if ($category?->image)
                        <div class="mb-2">
                            <img src="{{ asset($category->image) }}" class="rounded" width="120" height="90"
                                style="object-fit:cover" alt="">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image"
                        name="image" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Leave empty to keep the current image.</small>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="text-end mb-3">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-success w-sm">{{ $submitLabel }}</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Fill the slug from the name while it is untouched, so the common case
        // needs no thought but a deliberate slug is never overwritten.
        (function () {
            var name = document.getElementById('name');
            var slug = document.getElementById('slug');
            var edited = slug.value !== '';

            slug.addEventListener('input', function () { edited = true; });

            name.addEventListener('input', function () {
                if (edited) return;

                slug.value = name.value.toLowerCase().trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            });
        })();
    </script>
@endpush
