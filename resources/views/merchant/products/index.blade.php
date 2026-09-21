@extends('layouts.panel')

@section('title', 'My Products')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">My Products</h5>
            <a href="{{ route('merchant.products.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Add Product
            </a>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="search" name="search" class="form-control" placeholder="Search your products"
                        value="{{ $search }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('merchant.products.index') }}" class="btn btn-light">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered nowrap table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset($product->primary_image) }}" alt=""
                                            class="rounded avatar-xs me-2" style="object-fit:cover">
                                        <span>{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
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
                                    @if ($product->reject_reason)
                                        {{-- The admin's note, so the supplier knows what to fix. --}}
                                        <div class="text-danger small mt-1">{{ $product->reject_reason }}</div>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if ($product->isApproved())
                                        <a href="{{ route('product', $product) }}"
                                            class="btn btn-success btn-sm">View</a>
                                    @endif
                                    <a href="{{ route('merchant.products.edit', $product) }}"
                                        class="btn btn-primary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('merchant.products.destroy', $product) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Remove this product? Past orders keep their history.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    You have no products yet.
                                    <a href="{{ route('merchant.products.create') }}">Add your first one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $products->links() }}
        </div>
    </div>
@endsection
