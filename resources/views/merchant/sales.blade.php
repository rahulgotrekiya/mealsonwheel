@extends('layouts.panel')

@section('title', 'My Sales')

@section('content')
    <div class="row">
        @foreach ([
            ['label' => 'Units Sold', 'value' => number_format($unitsSold), 'icon' => 'bx bx-package', 'tone' => 'info'],
            ['label' => 'Gross Sales', 'value' => '₹'.number_format($grossSales, 2), 'icon' => 'bx bx-line-chart', 'tone' => 'primary'],
            ['label' => 'Commission ('.(int) config('marketplace.commission_rate').'%)', 'value' => '−₹'.number_format($commission, 2), 'icon' => 'bx bx-transfer', 'tone' => 'warning'],
            ['label' => 'You Have Earned', 'value' => '₹'.number_format($netEarnings, 2), 'icon' => 'bx bx-dollar-circle', 'tone' => 'success'],
        ] as $card)
            <div class="col-xl-3 col-md-6">
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
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Every line you have sold</h5>
            <span class="badge bg-light text-muted">across {{ $orderCount }} orders</span>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Figures are the price, commission and supplier recorded when each sale was made, so
                they never move if you change a product afterwards. Cancelled and returned orders are
                shown but not counted.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit price</th>
                            <th>Line total</th>
                            <th>Commission</th>
                            <th>You earned</th>
                            <th>Order status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $line)
                            @php($void = in_array($line->order->status, $voidStatuses, true))
                            <tr @class(['text-muted' => $void])>
                                {{-- The order reference only. No customer name, contact or address. --}}
                                <td>#{{ $line->order_id }}</td>
                                <td>{{ $line->created_at->format('Y-m-d') }}</td>
                                <td>{{ $line->product->name }}</td>
                                <td>{{ $line->quantity }}</td>
                                <td>&#8377; {{ number_format($line->unit_price, 2) }}</td>
                                <td>&#8377; {{ number_format($line->subtotal, 2) }}</td>
                                <td>&minus;&#8377; {{ number_format($line->commission, 2) }}</td>
                                <td class="fw-semibold">
                                    @if ($void)
                                        &mdash;
                                    @else
                                        &#8377; {{ number_format($line->net_earnings, 2) }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $line->order->status->badgeClass() }}">
                                        {{ $line->order->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Nothing has sold yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sales->links() }}
        </div>
    </div>
@endsection
