@extends('layouts.master')

@section('content')

        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner" style="min-height: 100px">
                        <h3>
                            {{$totalTasks}}
                        </h3>

                        <p>{{ __('Total Open Jobs') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-book-outline"></i>
                    </div>
                    <a href="{{route('tasks.index')}}" class="small-box-footer">{{ __('All Jobs') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner">
                        <h3>
                            {{$invoiceGenerated}}
                         </h3>

                        <p>{{ __('Invoice Generated') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{route('leads.unqualified')}}" class="small-box-footer">{{ __('All Invoices') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner">
                        <h3>{{$payments}}</h3>
                        <p>{{ __('Payments Done') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{route('projects.index')}}" class="small-box-footer">{{ __('All Payments') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner">
                        <h3>{{$todayTasks}}</h3>

                        <p>{{ __('Today Jobs') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="{{route('tasks.index')}}" class="small-box-footer">{{ __('All Jobs') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-12 col-xs-6">
                <div class="tablet">
                    <div class="tablet__body">
                        <div class="tablet__items">
                        <div id="invoice_payment"></div>

                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="col-lg-12 col-xs-6">
                <div class="tablet">
                    <div class="tablet__body">
                        <div class="tablet__items">
                        <div id="staff_payment"></div>

                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('pages._users')
            </div>
            @if(auth()->user()->can('absence-view'))
                <div class="col-lg-4 col-xs-6">
                    @include('pages._absent')
                </div>
            @endif
        </div>
        <!-- /.row -->
@if(!$settings->company)
<div class="modal fade" id="modal-create-client" tabindex="-1" role="dialog">
    @include('pages._firstStep')
</div>
@endif
@push('scripts')

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>
        $(document).ready(function () {
            var dates =  <?php echo json_encode($dates) ?>;
            var invoiceAmounts =  <?php echo json_encode($invoiceAmounts) ?>;
            var paymentDatas =  <?php echo json_encode($paymentDatas) ?>;
            var users =  <?php echo json_encode($userName) ?>;

            if(!'{{$settings->company}}') {
                $('#modal-create-client').modal({backdrop: 'static', keyboard: false})
                $('#modal-create-client').modal('show');
            }
            $('[data-toggle="tooltip"]').tooltip(); //Tooltip on icons top

            $('.popoverOption').each(function () {
                var $this = $(this);
                $this.popover({
                    trigger: 'hover',
                    placement: 'left',
                    container: $this,
                    html: true,

                });
            });
            Highcharts.chart('invoice_payment', {
                chart: {
                    type: 'line'
                },
                title: {
                    text: 'Invoice Generated vs Payments'
                },
                xAxis: {
                    categories: dates
                },
                yAxis: {
                    title: {
                        text: 'Amount'
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle'
                },
                plotOptions: {
                    series: {
                    allowPointSelect: true
                }
                },
                series: [
                    {
                    name: 'Invoice',
                    data: invoiceAmounts
                }, 
                {
                    name: 'Payment',
                    data: paymentDatas
                }
                ],
            
            });
            Highcharts.chart('staff_payment', {
                chart: {
                    zoomType: 'xy'
                },
                title: {
                    text: 'Staff vs Payments'
                },
               
                xAxis: [{
                    categories: users,
                    crosshair: true
                }],
                yAxis: [{ // Primary yAxis
                    labels: {
                        format: '{value}',
                        style: {
                            color: Highcharts.getOptions().colors[1]
                        }
                    },
                    title: {
                        text: 'Payments',
                        style: {
                            color: Highcharts.getOptions().colors[1]
                        }
                    }
                }, { // Secondary yAxis
                    title: {
                        text: 'Projects',
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    },
                    labels: {
                        format: '{value}',
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    },
                    opposite: true
                }],
                tooltip: {
                    shared: true
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    x: 120,
                    verticalAlign: 'top',
                    y: 100,
                    floating: true,
                    backgroundColor:
                        Highcharts.defaultOptions.legend.backgroundColor || // theme
                        'rgba(255,255,255,0.25)'
                },
                series: [{
                    name: 'Projects',
                    type: 'column',
                    yAxis: 1,
                    data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
                    tooltip: {
                        valueSuffix: ' mm'
                    }

                }, {
                    name: 'Payments',
                    type: 'spline',
                    data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                    tooltip: {
                        valueSuffix: '°C'
                    }
                }]
            });
        });
        $(document).ready(function () {
            if(!getCookie("step_dashboard") && "{{$settings->company}}") {
                $("#clients").addClass("in");
                // Instance the tour
                var tour = new Tour({
                    storage: false,
                    backdrop: true,
                    steps: [
                        {
                            element: ".col-lg-12",
                            title: "{{trans("Dashboard")}}",
                            content: "{{trans("This is your dashboard, which you can use to get a fast and nice overview, of all your tasks, leads, etc.")}}",
                            placement: 'top'
                        },
                        {
                            element: "#myNavmenu",
                            title: "{{trans("Navigation")}}",
                            content: "{{trans("This is your primary navigation bar, which you can use to get around Daybyday CRM")}}"
                        }
                    ]
                });

                var canCreateClient = '{{ auth()->user()->can('client-create') }}';
                if(canCreateClient) {
                    tour.addSteps([
                        {
                            element: "#newClient",
                            title: "{{trans("Create New Client")}}",
                            content: "{{trans("Let's take our first step, by creating a new client")}}"
                        },
                        {
                            path: '/clients/create'
                        }
                    ])
                }

                // Initialize the tour
                tour.init();

                tour.start();
                setCookie("step_dashboard", true, 1000)
            }
            function setCookie(key, value, expiry) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 2000));
                document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
            }

            function getCookie(key) {
                var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
                return keyValue ? keyValue[2] : null;
            }
        });
    </script>
@endpush
@endsection
