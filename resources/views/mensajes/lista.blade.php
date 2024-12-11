@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de mensajes. Filtro: ' . $filtro)
@else
	@section('sub_title', 'Lista de mensajes')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Texto</th>
							<th>Emisor</th>
							<th>Receptor</th>
						</tr>
						<tr>
							<form id="formfiltro" method="GET" class="form-inline" action="/mensajes/filtrar"></form>
							<th>
								@if (!empty($c1))
									<input type="text" id="fecha" value="{{$c1}}" name="fecha" form="formfiltro" class="form-control filt" autocomplete="off" >
								@else
									<input type="text" id="fecha" name="fecha" class="form-control filt" form="formfiltro" autocomplete="off" >
								@endif
							</th>
							<th>
								@if (!empty($c2))
									<input type="text" name="texto" value="{{$c2}}" form="formfiltro" class="form-control filt" >
								@else
									<input type="text" name="texto" class="form-control filt" form="formfiltro" >
								@endif									
							</th>
							<th>
								@if (!empty($c3))
									<input type="text" name="emisor" value="{{$c3}}" form="formfiltro" class="form-control filt" >
								@else
									<input type="text" name="emisor" class="form-control filt" form="formfiltro" >
								@endif							
							</th>
							<th>
								@if (!empty($c4))
									<input type="text" name="receptor" value="{{$c4}}" form="formfiltro" class="form-control filt" >
								@else
									<input type="text" name="receptor" class="form-control filt" form="formfiltro" >
								@endif														
							</th>
						</tr>
					</thead>
					<tbody>
						@forelse($mensajes as $mensaje)
							<tr>
								<td>{{ $mensaje->fecha }}</td>
								<td>{{ $mensaje->texto }}</td>
								<td>@if ($mensaje->sentido == "Enviado")
                                        {{$mensaje->cuentac->conductor->NOMBRE}}                                    
                                    @else
                                        CRM
                                    @endif
                                </td>
								<td>@if ($mensaje->sentido == "Recibido")
										@if ($mensaje->cuentac != null)
											{{$mensaje->cuentac->conductor->NOMBRE}} 
										@else
											Todos
										@endif                                                                         
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
				@if (method_exists($mensajes, 'links'))
					{{ $mensajes->links() }}
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
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection
