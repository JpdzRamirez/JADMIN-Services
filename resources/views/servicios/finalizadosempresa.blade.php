@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Servicios Finalizados. Filtro: ' . $filtro)
@else
	@section('sub_title', 'Servicios Finalizados')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">			
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>ID</th>
							<th>Fecha</th>
							<th>Cliente</th>
							<th>Teléfono</th>
							<th>Dir. Origen</th>
							<th>Despacho</th>
							<th>Asignación</th>
							<th>Conductor</th>
							<th>Vehiculo</th>
						</tr>
						<tr>
							<form method="GET" id="formfiltro" class="form-inline" action="/servicios/filtrar_empresa">
								<input type="hidden" name="_token" value="{{csrf_token()}}">
							</form>
								<th>
									@if (!empty($c1))
										<input type="number" value="{{$c1}}" name="id" form="formfiltro" class="form-control filt">
									@else
										<input type="number" name="id" class="form-control filt" form="formfiltro">
									@endif
									
								</th>
								<th>	
									@if (!empty($c2))
										<input type="text" id="fecha" value="{{$c2}}" name="fecha" class="form-control" form="formfiltro" autocomplete="off" onchange="this.form.submit()">
									@else
										<input type="text" id="fecha" name="fecha" class="form-control" form="formfiltro" autocomplete="off" onchange="this.form.submit()">
									@endif					
								</th>
								<th>
									@if (!empty($c3))
										<input type="text" value="{{$c3}}" name="cliente" class="form-control filt" form="formfiltro">
									@else
										<input type="text" name="cliente" class="form-control filt" form="formfiltro">
									@endif										
								</th>
								<th>
									@if (!empty($c4))
										<input type="text" value="{{$c4}}" name="telefono" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="telefono" class="form-control filt" form="formfiltro">
									@endif
								</th>
								<th>
									@if (!empty($c5))
										<input type="text" value="{{$c5}}" name="direccion" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="direccion" class="form-control filt" form="formfiltro">
									@endif
								</th>
								<th>	
									@if (!empty($c6))
										@if ($c6 == "inmediato")
											<select name="fechaprogramada" id="fechaprogramada" form="formfiltro" class="form-control" onchange="this.form.submit()">
												<option value="">Todos</option>
												<option value="inmediato" selected>Inmediato</option>
												<option value="programado">Programado</option>
											</select>
										@else
										<select name="fechaprogramada" id="fechaprogramada" form="formfiltro" class="form-control" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="inmediato">Inmediato</option>
											<option value="programado" selected>Programado</option>
										</select>
										@endif
									@else
										<select name="fechaprogramada" id="fechaprogramada" form="formfiltro" class="form-control" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="inmediato">Inmediato</option>
											<option value="programado">Programado</option>
										</select>
									@endif																
								</th>
								<th>
									@if (!empty($c8))
										<select name="asignacion" id="asignacion" class="form-control" form="formfiltro" onchange="this.form.submit()">
											<option value="">Todos</option>
										@if ($c8 == "Normal")
											<option value="Normal" selected>Normal</option>
											<option value="Directo">Directo</option>											
										@else
											<option value="Normal">Normal</option>
											<option value="Directo" selected>Directo</option>
										@endif
										</select>
									@else
										<select name="asignacion" id="asignacion" class="form-control" form="formfiltro" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="Normal">Normal</option>
											<option value="Directo">Directo</option>
										</select>
									@endif
								</th>
								<th>
									@if (!empty($c9))
										<input type="text" value="{{$c9}}" name="conductor" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="conductor" class="form-control filt" form="formfiltro">
									@endif
								</th>
								<th>
									@if (!empty($c10))
										<input type="text" name="vehiculo" value="{{$c10}}" maxlength="7" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="vehiculo" maxlength="7" class="form-control filt" form="formfiltro">
									@endif
								</th>
						</tr>
					</thead>
					<tbody>
						@forelse($servicios as $servicio)
							<tr>
                                <td>{{ $servicio->id }}</td>
								<td>{{ $servicio->fecha }}</td>
								@if ($servicio->vale != null)  <!-- servicio con vales-->
									<td>														
										{{ $servicio->vale_servicio->vale->valera->cuentae->agencia->NOMBRE }}
									</td>
								@else 	<!-- servicio normal-->
									<td>									
										{{ $servicio->cliente->nombres}}
									</td>
								@endif
								<td>{{ $servicio->cliente->telefono }}</td>
								<td>{{ $servicio->direccion }}</td>
								<td>@if ($servicio->fechaprogramada == null)
										Inmediato
									@else
										{{$servicio->fechaprogramada}}
									@endif
								</td>
								<td>{{ $servicio->asignacion}}</td>
								<td>@if ($servicio->cuentac == Null)
										Sin asignar
									@else
										{{ $servicio->cuentac->conductor->NOMBRE}}
									@endif
								</td>
								<td>@if ($servicio->placa == null)
										Sin asignar
									@else
										{{$servicio->placa}}
									@endif
								</td>
								<td>
									<a href="/servicios/detalles/{{$servicio->id}}" class="btn btn-info btn-sm">Detalles</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($servicios,'links'))
					{{ $servicios->links() }}
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
	</script>
@endsection