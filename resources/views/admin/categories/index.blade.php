@extends('layouts.panel')

@section('title', 'Categories')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Categories</h5>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Add Category
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($category->image)
                                            <img src="{{ asset($category->image) }}" alt=""
                                                class="rounded avatar-xs me-2" style="object-fit:cover">
                                        @endif
                                        <span>{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>{{ $category->products_count }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('category', $category) }}" class="btn btn-success btn-sm">View</a>
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn btn-primary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            @disabled($category->products_count > 0)>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $categories->links() }}
        </div>
    </div>
@endsection
