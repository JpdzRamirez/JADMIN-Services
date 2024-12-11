@extends('layouts.logeado')

@if (isset($filtro))
@section('sub_title', 'Transacciones de '. $suc->user->nombres . '. Filtro: ' . $filtro)
@else
@section('sub_title', 'Transacciones de '. $suc->user->nombres)
@endif

@section('sub_content')
<div class="card">
    <div class="card-body">
        @isset($filtro)
			<input type="hidden" id="filtro" value="{{implode(",", $idsfiltro)}}">
		@endisset
        <div class="align-center" style="display: inline">
            <button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right"
                onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>
            <p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
        </div>
        <div class="table-responsive" id="listar" style="min-height: 500px">
            <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo transacción</th>
                        <th>Tipo recarga</th>
                        <th>Valor</th>
                        <th>Conductor</th>
                        <th>Identificación</th>
                        <th>Agente</th>
                        <th>Comentarios</th>
                    </tr>
                    <tr>
                        <form method="GET" class="form-inline" id="formfiltro" action="/transacciones/sucursal/{{$suc->id}}/filtrar">
                        <th>
                            @if (!empty($c1))
                                <input type="number" name="id" value="{{$c1}}" form="formfiltro" class="form-control filt">
                            @else
                                <input type="number" name="id" form="formfiltro" class="form-control filt">
                            @endif
                        </th>
                        <th>
                            @if (!empty($c2))
                                <input type="text" id="fecha" value="{{$c2}}" name="fecha"  class="form-control" form="formfiltro" autocomplete="off" >
                            @else
                                <input type="text" id="fecha" name="fecha" class="form-control" form="formfiltro" autocomplete="off">
                            @endif
                        </th>
                        <th>
                            <select id="tipo" name="tipo" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Recarga">Recarga</option>
                                <option value="Venta de Combustible">Venta de Combustible</option>
                            </select>
                        </th>
                        <th>
                             <select id="tiporecarga" name="tiporecarga" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Ingreso">Ingreso</option>
                                <option value="Egreso">Egreso</option>
                                <option value="Devolución">Devolución</option>
                                <option value="Cortesía">Cortesía</option>
                            </select>
                        </th>
                        <th>
                            
                        </th>
                        <th>
                            @if (!empty($c5))
                                <input type="text" value="{{$c5}}" name="conductor" class="form-control filt" form="formfiltro">
                            @else
                                <input type="text" name="conductor" class="form-control filt" form="formfiltro">
                            @endif	
                        </th>
                        <th>
                            @if (!empty($c6))
                                <input type="text" value="{{$c6}}" name="identificacion" class="form-control filt" form="formfiltro">
                            @else
                                <input type="text" name="identificacion" class="form-control filt" form="formfiltro">
                            @endif
                        </th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    
                    @forelse($transacciones as $transaccion)
                    <tr>
                        <td>{{ $transaccion->id}}</td>
                        <td>{{ $transaccion->fecha}}</td>
                        <td>{{ $transaccion->tipo}}</td>
                        <td>{{ $transaccion->tiporecarga}}</td>
                        <td>${{ number_format($transaccion->valor)}}</td>
                        <td>@if ($transaccion->cuentac != null)
                                {{ $transaccion->cuentac->conductor->NOMBRE}}
                            @endif
                        </td>
                        <td>@if ($transaccion->cuentac != null)
                                {{ $transaccion->cuentac->conductor->NUMERO_IDENTIFICACION}}
                            @endif
                        </td>
                        <td>@if ($transaccion->user != null)
                                {{$transaccion->user->identificacion}}, {{$transaccion->user->nombres}}
                            @endif
                        </td>
                        <td>{{ $transaccion->comentarios}}</td>
                    </tr>
                    @empty
                    <tr class="align-center">
                        <td colspan="4">No hay datos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($transacciones,'links'))
            {{ $transacciones->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript" src="/js/moment.min.js"></script>
<script type="text/javascript" src="/js/daterangepicker.js"></script>
<script>

    $(document).ready(function () {
		$("#fecha").daterangepicker({
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,			
            locale: {
                format: 'YYYY/MM/DD HH:mm',
                cancelLabel: 'Clear'
            }				
        });

        @if(!empty($c3))
          $("#tipo").val("{{$c3}}");
        @endif

        @if(!empty($c4))
          $("#tiporecarga").val("{{$c4}}");
        @endif
    });

    $(".filt").keydown(function (event) {
        var keypressed = event.keyCode || event.which;
        if (keypressed == 13) {
            $("#formfiltro").submit();
        }
    });

    $("#fecha").on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
        $("#formfiltro").submit();
    });

    $("#fecha").on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    function toexcel() {

        Swal.fire({
            title: '<strong>Exportando...</strong>',
            html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
            showConfirmButton: false,
        });

        $.ajax({
            method: "POST",
            url: "/transacciones/sucursal/{{$suc->id}}/exportar",
            data: { 'filtro': $("#filtro").val() }
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

                filename = "Transacciones {{$suc->user->nombres}}.xlsx";
                csvFile = new Blob([byteArray], { type: 'application/vnd.ms-excel' });
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();

                Swal.close()

            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
            });
    }
</script>
@endsection