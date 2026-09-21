@extends('layouts.panel')

@section('title', 'Merchant Approvals')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Awaiting approval</h5>
            @if ($pending->total() > 0)
                <span class="badge bg-warning-subtle text-warning fs-12">{{ $pending->total() }} waiting</span>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>#</th>
                            <th>Business</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Applied</th>
                            <th>Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pending as $merchant)
                            <tr>
                                <td>{{ $merchant->id }}</td>
                                <td>{{ $merchant->full_name }}</td>
                                <td>{{ $merchant->email }}</td>
                                <td>{{ $merchant->phone ?? '—' }}</td>
                                <td>{{ $merchant->created_at->format('Y-m-d') }}</td>
                                <td class="text-nowrap">
                                    <form method="POST" action="{{ route('admin.merchants.approve', $merchant) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.merchants.reject', $merchant) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Decline this application? They will be emailed.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No applications waiting.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $pending->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Reviewed</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>#</th>
                            <th>Business</th>
                            <th>Email</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviewed as $merchant)
                            <tr>
                                <td>{{ $merchant->id }}</td>
                                <td>{{ $merchant->full_name }}</td>
                                <td>{{ $merchant->email }}</td>
                                <td>{{ $merchant->products()->count() }}</td>
                                <td>
                                    <span class="badge {{ $merchant->status->badgeClass() }}">
                                        {{ $merchant->status->label() }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    @if ($merchant->isActive())
                                        <form method="POST" action="{{ route('admin.merchants.reject', $merchant) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Suspend this supplier?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm">Suspend</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.merchants.approve', $merchant) }}"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm">Reinstate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No merchants yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $reviewed->links() }}
        </div>
    </div>
@endsection
