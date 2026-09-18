@extends('layouts.panel')

@section('title', 'Products')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Products</h5>
            <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Add Product
            </a>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="search" name="search" class="form-control" placeholder="Search products"
                        value="{{ $search }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered nowrap table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset($product->primary_image) }}" alt=""
                                            class="rounded avatar-xs me-2" style="object-fit:cover">
                                        <span>{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->seller?->full_name ?? 'Store' }}</td>
                                <td>&#8377; {{ number_format($product->price, 2) }}</td>
                                <td>
                                    <span class="{{ $product->stock <= config('marketplace.low_stock_threshold') ? 'text-danger fw-semibold' : '' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->status->badgeClass() }}">
                                        {{ $product->status->label() }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('product', $product) }}" class="btn btn-success btn-sm">View</a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                        class="btn btn-primary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $products->links() }}
        </div>
    </div>
@endsection
