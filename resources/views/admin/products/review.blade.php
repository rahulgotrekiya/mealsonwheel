@extends('layouts.panel')

@section('title', 'Product Reviews')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Waiting for review</h5>
            @if ($pending->total() > 0)
                <span class="badge bg-warning-subtle text-warning fs-12">{{ $pending->total() }} waiting</span>
            @endif
        </div>
        <div class="card-body">
            @forelse ($pending as $product)
                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="{{ asset($product->primary_image) }}" class="img-fluid rounded" alt=""
                                style="max-height:120px;object-fit:cover">
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <p class="text-muted mb-1">
                                {{ $product->category?->name ?? '—' }} &middot;
                                &#8377; {{ number_format($product->price, 2) }} &middot;
                                {{ $product->stock }} in stock
                            </p>
                            <p class="text-muted mb-2">
                                Supplied by <strong>{{ $product->seller?->full_name ?? 'Store' }}</strong>,
                                submitted {{ $product->updated_at->diffForHumans() }}
                            </p>
                            <div class="small">{{ Str::limit(strip_tags($product->description), 220) }}</div>
                        </div>
                        <div class="col-md-4">
                            <form method="POST" action="{{ route('admin.reviews.approve', $product) }}" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success w-100">Approve and publish</button>
                            </form>

                            <form method="POST" action="{{ route('admin.reviews.reject', $product) }}">
                                @csrf
                                @method('PATCH')
                                <textarea name="reject_reason" class="form-control form-control-sm mb-2" rows="2"
                                    placeholder="Why is this being sent back?" required minlength="5"></textarea>
                                <button type="submit" class="btn btn-outline-danger w-100">Send back</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center my-4">Nothing waiting for review.</p>
            @endforelse

            {{ $pending->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Sent back</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Reason given</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rejected as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->seller?->full_name ?? 'Store' }}</td>
                                <td class="text-danger">{{ $product->reject_reason }}</td>
                                <td>{{ $product->updated_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nothing has been sent back.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $rejected->links() }}
        </div>
    </div>
@endsection
