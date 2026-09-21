@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-lg-center flex-lg-row flex-column mb-4">
                <div class="flex-grow-1">
                    <h4 class="fs-16 mb-1">Hello, {{ auth()->user()->firstname }}!</h4>
                    <p class="text-muted mb-0">Here is how your products are doing.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach ([
            ['label' => 'Units Sold', 'value' => number_format($unitsSold), 'icon' => 'bx bx-package', 'tone' => 'info'],
            ['label' => 'Gross Sales', 'value' => '₹'.number_format($grossSales, 2), 'icon' => 'bx bx-line-chart', 'tone' => 'primary'],
            ['label' => 'Commission ('.(int) config('marketplace.commission_rate').'%)', 'value' => '₹'.number_format($commission, 2), 'icon' => 'bx bx-transfer', 'tone' => 'warning'],
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

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Your sales, last 10 days</h4>
                </div>
                <div class="card-body px-0 pb-0">
                    <div id="merchant-revenue-chart" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Running low</h4>
                    <span class="badge bg-light text-muted">{{ $productCount }} products</span>
                </div>
                <div class="card-body">
                    @if ($lowStock->isEmpty())
                        <p class="text-muted text-center my-5">Nothing needs restocking.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($lowStock as $product)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-truncate me-2">{{ $product->name }}</span>
                                    <span class="badge bg-danger-subtle text-danger">{{ $product->stock }} left</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var revenue = @json($dailyRevenue);

            new ApexCharts(document.querySelector('#merchant-revenue-chart'), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                series: [{ name: 'Your sales', data: Object.values(revenue) }],
                xaxis: {
                    categories: Object.keys(revenue).map(function (d) {
                        return new Date(d).toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
                    })
                },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                dataLabels: { enabled: false },
                colors: ['#299cdb'],
                yaxis: { labels: { formatter: function (v) { return '₹' + v.toLocaleString(); } } },
                tooltip: { y: { formatter: function (v) { return '₹' + v.toLocaleString(); } } }
            }).render();
        })();
    </script>
@endpush
