@extends('layouts.panel')

@section('title', 'Stock')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Stock in the warehouse</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Lowest first. Changing a figure here does not send the listing back for review.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th style="width:220px">Units held</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $product->status->badgeClass() }}">
                                        {{ $product->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('merchant.stock.update', $product) }}"
                                        class="d-flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="stock" min="0" class="form-control form-control-sm"
                                            value="{{ $product->stock }}"
                                            aria-label="Units of {{ $product->name }}">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    You have no products yet.
                                    <a href="{{ route('merchant.products.create') }}">Add one</a>.
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
