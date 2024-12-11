@extends('layouts.logeado')

@if (isset($filtro))
@section('sub_title', 'Vales de '. $cuenta->conductor->NOMBRE . '. Filtro: ' . $filtro)
@else
@section('sub_title', 'Vales de '. $cuenta->conductor->NOMBRE)
@endif

@section('sub_content')
<div class="card">
    <div class="card-body">
        @isset($filtro)
            <input type="hidden" id="filtro" value="{{implode(",", $servicios->pluck('id')->toArray())}}">
        @endisset
        <div class="align-center" style="display: inline">
            <select name="orden" form="formfiltro" class="form-control" style="width: 200px; float: left;" id="orden">
                <option value="DESC">Descendente</option>
                <option value="ASC">Ascendente</option>
            </select>
            <button data-toggle="modal" data-target="#Modal" class="btn open-modal" style="background-color: #00965e; margin-left: 5px; float: right">
                <i class="fa fa-file-excel-o" aria-hidden="true"></i>
            </button> 
            <p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
        </div>
        <div class="table-responsive" id="listar" style="min-height: 500px">
            <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Empresa</th>
                        <th>Valera</th>
                        <th>Código vale</th>
                        <th>Unidades</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <form method="GET" id="formfiltro" class="form-inline" action="/vales/{{$cuenta->id}}/filtrar"></form>
                        <th>
                            @if (!empty($c1))
                                <input type="number" value="{{$c1}}" form="formfiltro" name="id" class="form-control filt">
							@else
							    <input type="number" form="formfiltro" name="id" class="form-control filt">
							@endif
                        </th>
                        <th>
                            @if (!empty($c2))
                                <input type="date" name="fecha" value="{{$c2}}" form="formfiltro" onchange="this.form.submit()" class="form-control filt">
							@else
							     <input type="date" name="fecha" form="formfiltro" onchange="this.form.submit()" class="form-control filt">
							@endif
                        </th>
                        <th>
                            @if (!empty($c3))
                                <input type="text" name="empresa" value="{{$c3}}" form="formfiltro" class="form-control filt">
							@else
							     <input type="text" name="empresa" form="formfiltro" class="form-control filt">
							@endif
                        </th>
                        <th>
                            @if (!empty($c4))
                                <input type="text" name="valera" value="{{$c4}}" form="formfiltro" class="form-control filt">
							@else
							    <input type="text" name="valera" form="formfiltro" class="form-control filt">
							@endif
                        </th>
                        <th>
                            @if (!empty($c5))
                                <input type="text" name="codigo" value="{{$c5}}" form="formfiltro" class="form-control filt">
							@else
							    <input type="text" name="codigo" form="formfiltro"  class="form-control filt">
							@endif
                        </th>
                        <th>
                            @if (!empty($c6))
                                <input type="text" name="unidades" value="{{$c6}}" form="formfiltro" class="form-control filt">
							@else
							    <input type="text" name="unidades" form="formfiltro"  class="form-control filt">
							@endif
                        </th>
                        <th>
                            @if (!empty($c7))
                                <input type="number" name="valor" value="{{$c7}}" form="formfiltro" class="form-control filt">
							@else
							    <input type="number" name="valor" form="formfiltro" class="form-control filt">
							@endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servicios as $servicio)
                    <tr>
                        @if ($servicio->vale != null)
                            <td>{{ $servicio->vale->id}}</td>
                            <td>{{ $servicio->fecha}}</td>
                            <td>{{ $servicio->vale->valera->cuentae->agencia->NOMBRE}}</td>
                            <td>{{ $servicio->vale->valera->nombre}}</td>
                            <td>{{ $servicio->vale->codigo}}</td>                          
                        @else
                            <td>{{ $servicio->valeav->id}}</td>
                            <td>{{ $servicio->fecha}}</td>
                            <td>AVIANCA</td>
                            <td>{{ $servicio->valeav->valera->nombre}}</td>
                            <td>{{ $servicio->valeav->codigo}}</td>
                        @endif
                        <td>{{ $servicio->unidades}} {{$servicio->cobro}}</td>
                        <td>${{ number_format($servicio->valor)}}</td>
                        <td><a href="/servicios/detalles/{{$servicio->id}}" class="btn btn-info btn-sm">Detalle servicio</a></td>                      
                    </tr>
                    @empty
                    <tr class="align-center">
                        <td colspan="4">No hay datos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($servicios,'links'))
            {{ $servicios->appends(request()->query())->links() }}
            @endif
        </div>
    </div>
</div>
@endsection

@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="width: 40%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Exportar</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row form-group">
                    <label for="desde" class="col-md-3 label-required">Desde</label>                         
                    <div class="col-md-9">
                        <input type="date" name="desde" id="desde" class="form-control">	
                    </div>
                </div>
                <div class="row form-group">
                    <label for="hasta" class="col-md-3 label-required">Hasta</label>                         
                    <div class="col-md-9">
                        <input type="date" name="hasta" id="hasta" class="form-control">
                    </div>
                </div>					
            </div>
            <div class="modal-footer">
                <button type="button" onclick="toexcel();" class="btn btn-success">Enviar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>

    @isset($orden)
        $(document).ready(function () {
            $("#orden").val('{{$orden}}');
        });
    @endisset

    $(".filt").keydown(function (event) {
        var keypressed = event.keyCode || event.which;
        if (keypressed == 13) {
            $("#formfiltro").submit();
        }
    });

    $("#orden").change(function () {
        $(location).attr("href", "/vales/{{$cuenta->id}}?orden=" + $(this).val());
    })

    function toexcel() {
        
        if($("#desde").val() != ""){
        
            Swal.fire({
                title: '<strong>Exportando...</strong>',
                html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
                showConfirmButton: false,
            });

            $.ajax({
                method: "GET",
                url: "/vales/{{$cuenta->id}}/exportar",
                data: { 'filtro': $("#filtro").val(), 
                        'orden': $("#orden").val(),
                        'desde': $("#desde").val(),
                        'hasta': $("#hasta").val()}
            })
            .done(function (data, textStatus, jqXHR) {
                const byteCharacters = atob(data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);

                var csvFile;
                var downloadLink;

                filename = "Vales conductor.xlsx";
                csvFile = new Blob([byteArray], { type: 'application/vnd.ms-excel' });
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();
                $("#Modal").modal("hide");
                    Swal.close();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                    Swal.close();
                    Swal.fire(
                        'Error',
                        textStatus,
                        'error'
                    );
            });
        }else{
            Swal.fire(
                'Error',
                'Debe ingresar una fecha de inicio',
                'error'
            );
        }
    }
</script>
@endsection