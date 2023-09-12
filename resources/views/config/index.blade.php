@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Ganhos Variável</h1>
@stop

@section('js')
    <script>

    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#config" data-toggle="tab">Configuração</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="config">
                            <form id="formAddValues" action="{{ route('save_config') }}">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                            <label for="savings_percentage">Valor de lazer</label>
                                            <input type="number" name="savings_percentage" id="savings_percentage" class="form-control" value="{{ $user->savings_percentage }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12 text-right">
                                        <button class="btn btn-success col-sm-6"><i class="fa fa-save"></i> Salvar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
