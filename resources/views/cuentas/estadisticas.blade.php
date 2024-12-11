@extends('layouts.logeado')

@if (isset($filtro))
    @section('sub_title', 'Movimientos. Filtro: ' . $filtro)
@else
    @section('sub_title', 'Movimientos')
@endif

@section('sub_content')
<div class="card">
    <div class="card-body">
        @isset($filtro)
            <input type="hidden" id="filtro" value="{{$exp}}">
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
                        <th>Tipo movimiento</th>
                        <th>Tipo recarga</th>
                        <th>Valor</th>
                        <th>Conductor</th>
                        <th>Sucursal</th>
                    </tr>
                    <tr>
                        <form method="GET" id="formfiltro" class="form-inline" action="/movimientos/filtrar"></form>
                        <th></th>
                        <th>
                            @if (!empty($c1))
                                <input type="text" autocomplete="off" value="{{$c1}}" form="formfiltro" id="fecha" name="fecha" class="form-control" onchange="this.form.submit()">
							@else
							    <input type="text" autocomplete="off" form="formfiltro" name="fecha" id="fecha" class="form-control" onchange="this.form.submit()">
							@endif
                        </th>
                        <th>
                            <select name="tipo" id="tipo" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                <option value=""></option>
                                <option value="Recarga">Recarga</option>
                                <option value="Venta de Combustible">Venta de Combustible</option>
                                <option value="Pago">Pago</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Consumo">Consumo</option>
                                <option value="Servicio con Vale">Servicio con Vale</option>
                                <option value="Ajuste de Vale">Ajuste de Vale</option>
                                <option value="Egreso">Egreso</option>
                                <option value="Ingreso">Ingreso</option>
                            </select>   
                        </th>
                        <th></th>
                        <th></th>
                        <th>
                            @if (!empty($c3))
                                <input type="number" value="{{$c3}}" form="formfiltro" name="conductor" class="form-control filt">
                            @else
                                <input type="number" form="formfiltro" name="conductor" class="form-control filt">
                            @endif
                        </th>
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
                            <td>
                                @if ($transaccion->cuentac != null)
                                    {{$transaccion->cuentac->conductor->NUMERO_IDENTIFICACION}}
                                @endif
                            </td>
                            <td>@if ($transaccion->sucursal != null)
                                    {{ $transaccion->sucursal->user->nombres}}
                                @else
                                    CRM
                                @endif
                            </td>
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
              
        @if(!empty($c2))
            $("#tipo").val("{{$c2}}");
        @endif
    });
    
    $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formfiltro").submit();
  		});

  	$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  	});

    $(".filt").keydown(function (event) {
        var keypressed = event.keyCode || event.which;
        if (keypressed == 13) {
            $("#formfiltro").submit();
        }
	});

    function toexcel() {

        Swal.fire({
            title: '<strong>Exportando...</strong>',
            html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
            showConfirmButton: false,
		});

        $.ajax({
            method: "GET",
            url: "/movimientos/exportar",
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

            filename = "Movimientos.xlsx";
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
