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
        <div class="table-responsive" id="listar" style="min-height: 500px">
            <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo movimiento</th>
                        <th>Valor</th>
                        <th>Conductor</th>
                    </tr>
                    <tr>
                        <form method="GET" id="formfiltro" class="form-inline" action="/sucursales/movimientos/filtrar"></form>
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
                            </select>   
                        </th>
                        <th></th>
                        <th>
                            @if (!empty($c3))
                                <input type="number" value="{{$c3}}" form="formfiltro" name="conductor" class="form-control filt">
                            @else
                                <input type="number" form="formfiltro" name="conductor" class="form-control filt">
                            @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transacciones as $transaccion)
                        <tr>
                            <td>{{ $transaccion->fecha}}</td>
                            <td>{{ $transaccion->tipo}}</td>
                            <td>
                                @if ($transaccion->tipo == "Recarga")
                                    @if ($transaccion->tiporecarga == "Egreso")
                                        ${{ number_format(-$transaccion->valor)}}
                                    @else
                                        ${{ number_format($transaccion->valor)}}
                                    @endif
                                @else
                                    ${{ number_format($transaccion->valor)}}</td>
                                @endif                              
                            <td>
                                @if ($transaccion->cuentac != null)
                                    {{$transaccion->cuentac->conductor->NUMERO_IDENTIFICACION}}
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

        @if(!empty($c2))
            $("#tipo").val("{{$c2}}");
        @endif
        
        $("#fecha").daterangepicker({
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,			
            locale: {
                format: 'YYYY/MM/DD HH:mm',
                cancelLabel: 'Clear'
            }				
        });
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
</script>
@endsection
