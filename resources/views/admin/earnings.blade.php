@extends('layouts.panel')

@section('title', 'Merchant Earnings')

@section('content')
    <div class="row">
        @foreach ([
            ['label' => 'Gross Sales', 'value' => '₹'.number_format($storeGross, 2), 'icon' => 'bx bx-line-chart', 'tone' => 'primary'],
            ['label' => 'Commission Kept', 'value' => '₹'.number_format($storeCommission, 2), 'icon' => 'bx bx-transfer', 'tone' => 'success'],
            ['label' => 'Owed to Merchants', 'value' => '₹'.number_format($owedToMerchants, 2), 'icon' => 'bx bx-wallet', 'tone' => 'warning'],
        ] as $card)
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ $card['label'] }}</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $card['value'] }}</h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-{{ $card['tone'] }}-subtle rounded fs-3">
                                    <i class="{{ $card['icon'] }} text-{{ $card['tone'] }}"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">By supplier</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                What each supplier has earned on sales that completed. Nothing is recorded as settled,
                so these figures are everything owed to date.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Supplier</th>
                            <th>Units sold</th>
                            <th>Gross</th>
                            <th>Commission kept</th>
                            <th>Owed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($perSeller as $row)
                            <tr>
                                <td>
                                    {{ $row->firstname }} {{ $row->lastname }}<br>
                                    <small class="text-muted">{{ $row->email }}</small>
                                </td>
                                <td>{{ number_format($row->units) }}</td>
                                <td>&#8377; {{ number_format($row->gross, 2) }}</td>
                                <td>&#8377; {{ number_format($row->commission, 2) }}</td>
                                <td class="fw-semibold">&#8377; {{ number_format($row->net, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No supplier sales yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($perSeller->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>{{ number_format($perSeller->sum('units')) }}</th>
                                <th>&#8377; {{ number_format($perSeller->sum('gross'), 2) }}</th>
                                <th>&#8377; {{ number_format($perSeller->sum('commission'), 2) }}</th>
                                <th>&#8377; {{ number_format($perSeller->sum('net'), 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
