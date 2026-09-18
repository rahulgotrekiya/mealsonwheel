@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-lg-center flex-lg-row flex-column mb-4">
                <div class="flex-grow-1">
                    <h4 class="fs-16 mb-1">Good morning, {{ auth()->user()->firstname }}!</h4>
                    <p class="text-muted mb-0">Here is what is happening across the store today.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach ([
            ['label' => 'Total Earnings', 'value' => '₹'.number_format($totalEarnings, 2), 'icon' => 'bx bx-dollar-circle', 'tone' => 'success'],
            ['label' => "Today's Earnings", 'value' => '₹'.number_format($earningsToday, 2), 'icon' => 'bx bx-line-chart', 'tone' => 'info'],
            ['label' => 'Orders', 'value' => number_format($orderCount), 'icon' => 'bx bx-shopping-bag', 'tone' => 'warning'],
            ['label' => 'Customers', 'value' => number_format($customerCount), 'icon' => 'bx bx-user-circle', 'tone' => 'primary'],
        ] as $card)
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                    {{ $card['label'] }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $card['value'] }}</h4>
                            </div>
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
                    <h4 class="card-title mb-0 flex-grow-1">Revenue, last 10 days</h4>
                </div>
                <div class="card-body px-0 pb-0">
                    <div id="revenue-chart" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Sales by category</h4>
                </div>
                <div class="card-body">
                    @if ($salesByCategory->isEmpty())
                        <p class="text-muted text-center my-5">No sales recorded yet.</p>
                    @else
                        <div id="category-chart" class="apex-charts" dir="ltr"></div>
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

            new ApexCharts(document.querySelector('#revenue-chart'), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                series: [{ name: 'Revenue', data: Object.values(revenue) }],
                xaxis: {
                    categories: Object.keys(revenue).map(function (d) {
                        return new Date(d).toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
                    })
                },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                dataLabels: { enabled: false },
                colors: ['#0ab39c'],
                yaxis: { labels: { formatter: function (v) { return '₹' + v.toLocaleString(); } } },
                tooltip: { y: { formatter: function (v) { return '₹' + v.toLocaleString(); } } }
            }).render();

            var categories = @json($salesByCategory);

            if (Object.keys(categories).length) {
                new ApexCharts(document.querySelector('#category-chart'), {
                    chart: { type: 'donut', height: 333 },
                    series: Object.values(categories),
                    labels: Object.keys(categories),
                    legend: { position: 'bottom' },
                    colors: ['#0ab39c', '#f7b84b', '#299cdb', '#f06548', '#405189'],
                    tooltip: { y: { formatter: function (v) { return v + ' units'; } } }
                }).render();
            }
        })();
    </script>
@endpush
