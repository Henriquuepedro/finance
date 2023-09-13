@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('js')
    <script>
        let datatable;

        $(function(){
            getAreaChart();
            getDonutChart();
            getDataDashboard();
            loadMonthlyExpenseTable();
        });

        const getLastMonthsChart = (months_sub = 3) => {
            let date_now = moment(new Date());
            let labels = [];

            for (let month = months_sub; month > 0; month--) {
                date_now.subtract({M:month === months_sub ? 0 : 1});
                labels.push(`${date_now.format("MMM").toLowerCase().replace(/(?:^|\s)\S/g, a => a.toUpperCase())}/${date_now.format("YY")}`)
            }

            return labels;
        }

        const getDataDashboard = () => {
            let real = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });


            $.get($('#route_monthly_data_dashboard').val(), function (response) {
                $('#box_amount_available_for_use').removeClass('bg-success').removeClass('bg-warning').removeClass('bg-success').addClass(response.amount_available_for_use < 0 ? 'bg-danger' : (response.amount_available_for_use < 750 ? 'bg-warning' : 'bg-success'))
                $('#amount_available_for_use').text(real.format(response.amount_available_for_use))
                $('#economy').text(Number(response.economy.toFixed(2)).toLocaleString() + '%')
                $('#expenses').text(real.format(response.expenses))
                $('#incomes').text(real.format(response.incomes))
                $('#leisure_value').text(Number(response.leisure_value.toFixed(2)).toLocaleString())
                $('#liquid').text(real.format(response.liquid))
                $('#savings_goal').text(real.format(response.savings_goal))
            });
        }

        const loadMonthlyExpenseTable = () => {
            if (typeof datatable === "undefined") {
                datatable = $('#dataTablesMonthlyExpense').DataTable({
                    "paging": true,
                    "ordering": false,
                    "responsive": true,
                });
            }

            $.get($('#route_monthly_expense_list').val(), function(newDataArray) {
                datatable.clear();
                datatable.rows.add(newDataArray);
                datatable.draw();
            });
        }

        const getDonutChart = () => {
            $.get($('#route_monthly_expense_this_month').val(), function (response) {
                var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
                var donutData        = {
                    labels: [
                        'Gasto Fixo',
                        'Gasto Variável'
                    ],
                    datasets: [
                        {
                            data: [response.fixed, response.monthly],
                            backgroundColor : ['#f56954', '#dcb20b'],
                        }
                    ]
                }
                var donutOptions     = {
                    maintainAspectRatio : false,
                    responsive : true,

                    events: false,
                    animation: {
                        duration: 500,
                        easing: "easeOutQuart",
                        onComplete: function () {
                            var ctx = this.chart.ctx;
                            ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';

                            this.data.datasets.forEach(function (dataset) {
                                let real = new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL',
                                });

                                for (var i = 0; i < dataset.data.length; i++) {
                                    var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
                                        total = dataset._meta[Object.keys(dataset._meta)[0]].total,
                                        mid_radius = model.innerRadius + (model.outerRadius - model.innerRadius)/2,
                                        start_angle = model.startAngle,
                                        end_angle = model.endAngle,
                                        mid_angle = start_angle + (end_angle - start_angle)/2;

                                    var x = mid_radius * Math.cos(mid_angle);
                                    var y = mid_radius * Math.sin(mid_angle);

                                    ctx.fillStyle = '#000';
                                    if (i == 3){ // Darker text color for lighter background
                                        ctx.fillStyle = '#444';
                                    }

                                    var percent = String(Math.round(dataset.data[i]/total*100)) + "%";

                                    ctx.fillText(real.format(dataset.data[i]), model.x + x, model.y + y - 7);
                                    ctx.fillText(percent, model.x + x, model.y + y + 8);
                                }
                            });
                        }
                    }
                }
                new Chart(donutChartCanvas, {
                    type: 'doughnut',
                    data: donutData,
                    options: donutOptions
                })
            });
        }


        const getAreaChart = () => {
            $.get($('#route_monthly_expense_all').val(), function(response) {
                var areaChartData = {
                    labels: getLastMonthsChart(),
                    datasets: [
                        {
                            label               : 'Despesas Mensais',
                            backgroundColor     : 'rgb(169,13,13)',
                            borderColor         : 'rgba(169,13,13, 1)',
                            pointRadius         : false,
                            pointColor          : 'rgba(169,13,13, 1)',
                            pointStrokeColor    : '#A90D0D',
                            pointHighlightFill  : '#fff',
                            pointHighlightStroke: 'rgba(169,13,13,1)',
                            data                : response.expense,
                            font: {
                                weight: 'bold',
                                size: 26,
                            }
                        },
                        {
                            label               : 'Economias Mensais',
                            backgroundColor     : 'rgb(4,143,31)',
                            borderColor         : 'rgba(4,143,31, 1)',
                            pointRadius         : false,
                            pointColor          : 'rgba(4,143,31, 1)',
                            pointStrokeColor    : '#048f1f',
                            pointHighlightFill  : '#fff',
                            pointHighlightStroke: 'rgba(4,143,31,1)',
                            data                : response.economy
                        }
                    ]
                }

                var barChartCanvas = $('#barChart').get(0).getContext('2d')
                var barChartData = $.extend(true, {}, areaChartData)
                barChartData.datasets[0] = areaChartData.datasets[1]
                barChartData.datasets[1] = areaChartData.datasets[0]

                var barChartOptions = {
                    responsive              : true,
                    maintainAspectRatio     : false,
                    datasetFill             : false,

                    events: false,
                    tooltips: {
                        enabled: false
                    },
                    hover: {
                        animationDuration: 0
                    },
                    animation: {
                        duration: 1,
                        onComplete: function () {
                            var chartInstance = this.chart,
                                ctx = chartInstance.ctx;
                            ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillStyle = '#000';

                            this.data.datasets.forEach(function (dataset, i) {
                                let real = new Intl.NumberFormat('pt-BR');
                                var meta = chartInstance.controller.getDatasetMeta(i);
                                meta.data.forEach(function (bar, index) {
                                    var data = dataset.data[index];
                                    ctx.fillText(real.format(data), bar._model.x, bar._model.y - 5);
                                });
                            });
                        }
                    }



                }

                new Chart(barChartCanvas, {
                    type: 'bar',
                    data: barChartData,
                    options: barChartOptions
                })
            });
        }

        $('#addValue').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const type = button.data('type');
            const modal = $(this);

            if (modal.find('[name="type"]').val() != type) {
                modal.find('[name="price"]').val('');
                modal.find('[name="description"]').val('');
            }

            modal.find('[name="type"]').val(type);
        });

        $('#formAddValues').on('submit', function(e){
            $(this).find('button[type="submit"]').attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: 'json',
                success: response => {
                    $('#formAddValues button[type="submit"]').attr('disabled', false);
                    if (!response.success) {
                        toastr.warning('<ol><li>' + response.message + '</li></ol>', 'Atenção');
                        return false;
                    }

                    getAreaChart();
                    getDonutChart();
                    getDataDashboard();
                    loadMonthlyExpenseTable();

                    toastr.success(response.message);

                    $('#addValue').modal('hide');
                }, error: e => {
                    $('#formAddValues button[type="submit"]').attr('disabled', false);
                    console.log(e);
                    let arrErrors = [];

                    $.each(e.responseJSON.errors, function( index, value ) {
                        arrErrors.push(value);
                    });

                    if (!arrErrors.length && e.responseJSON.message !== undefined) {
                        arrErrors.push('Ocorreu um erro inesperado!');
                    }

                    toastr.warning('<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>', 'Atenção');
                }
            });
            e.preventDefault();
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex justify-content-between">
                        <button class="btn btn-danger col-sm-5 pt-3 pb-3" data-toggle="modal" data-target="#addValue" data-type="expense"><i class="fa fa-minus"></i> Despesas</button>
{{--                        <button class="btn btn-success col-sm-5 pt-3 pb-3" data-toggle="modal" data-target="#addValue" data-type="income"><i class="fa fa-plus"></i> Receitas</button>--}}
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-box mb-3" id="box_amount_available_for_use">
                                <span class="info-box-icon"><i class="fas fa-money-bill-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Valor disponível para uso</span>
                                    <span class="info-box-number" id="amount_available_for_use"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped">
                                <tbody>
                                    <tr class="bg-success">
                                        <td class="text-right">Ganhos:</td>
                                        <td class="text-left font-weight-bold" id="incomes"></td>
                                    </tr>
                                    <tr class="bg-danger">
                                        <td class="text-right">Gastos:</td>
                                        <td class="text-left font-weight-bold" id="expenses"></td>
                                    </tr>
                                    <tr class="bg-blue">
                                        <td class="text-right">Líquido:</td>
                                        <td class="text-left font-weight-bold" id="liquid"></td>
                                    </tr>
                                    <tr class="bg-cyan">
                                        <td class="text-right">Economia Atual:</td>
                                        <td class="text-left font-weight-bold" id="economy"></td>
                                    </tr>
                                    <tr class="bg-purple">
                                        <td class="text-right">Meta de Economia (<span id="leisure_value"></span>%):</td>
                                        <td class="text-left font-weight-bold" id="savings_goal"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row mt-5">
                        <div class="chart col-md-12">
                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row mt-5">
                        <div class="chart col-md-12">
                            <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <table class="table" id="dataTablesMonthlyExpense">
                                <thead>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Criado Em</th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addValue" tabindex="-1" role="dialog" aria-labelledby="exampleAddValue" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formAddValues" action="{{ route('ajax.monthly.store') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleAddValue">Adicionar</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="description">Descrição</label>
                                <input type="text" name="description" id="description" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="price">Valor</label>
                                <input type="number" name="price" id="price" class="form-control" min="0.00" step="0.01" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="month">Mês de Referente</label>
                                <select name="month" id="month" class="form-control">
                                    {!! getOptionsMonthsToSelect() !!}
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="type">
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary col-sm-5">Salvar</button>
                        <button type="button" class="btn btn-secondary col-sm-5" data-dismiss="modal">Fechar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <input type="hidden" id="route_monthly_expense_list" value="{{ route('ajax.monthly.expense.list') }}">
    <input type="hidden" id="route_monthly_expense_all" value="{{ route('ajax.monthly.expense.all') }}">
    <input type="hidden" id="route_monthly_expense_this_month" value="{{ route('ajax.monthly.expense.this_month') }}">
    <input type="hidden" id="route_monthly_data_dashboard" value="{{ route('ajax.monthly.data_dashboard') }}">
@stop
