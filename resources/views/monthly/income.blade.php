@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Ganhos Variável</h1>
@stop

@section('js')
    <script>
        let datatable;

        $(function(){
            loadTable();
        });

        const loadTable = () => {
            if (typeof datatable === "undefined") {
                datatable = $('#datatables').DataTable({
                    "paging": true,
                    "ordering": true,
                    "responsive": true,
                });
            }

            $.get($('#route_income_list').val(), function(newDataArray) {
                datatable.clear();
                datatable.rows.add(newDataArray);
                datatable.draw();
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

                    loadTable();
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
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary col-sm-12 pt-3 pb-3" data-toggle="modal" data-target="#addValue"><i class="fa fa-plus"></i> Novo Ganho</button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <table id="datatables" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Preço</th>
                                        <th>Referência</th>
                                    </tr>
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
                                    <option value="">Selecione um mês</option>
                                    {!! getOptionsMonthsToSelect() !!}
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="type" value="income">
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary col-sm-5" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary col-sm-5">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <input type="hidden" id="route_income_list" value="{{ route('ajax.monthly.income.list') }}">
@stop
