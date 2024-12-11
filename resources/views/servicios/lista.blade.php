@extends('layouts.logeado')

@section('sub_title', 'Lista de servicios')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@if ($usuario->roles_id == 1)
				<div class="align-center" style="display: flex">
					<a href="{{route('servicios.nuevo')}}" class="btn btn-dark btn-sm">Nuevo servicio</a>
					<a href="{{route('servicios.ubicar')}}" class="btn btn-dark btn-sm" style="margin-left: 10px">Mapa de servicios</a>
					<form id="formfiltrar" method="GET" style="margin-left: 10px" class="form-inline" action="/servicios/filtrar">
						<select id="estado" name="estado" class="form-control" style="width: 200px">
							<option value="sinfiltro">Sin filtro</option>
							<option value="Pendiente">Pendientes</option>
							<option value="Asignado">Asignados</option>
							<option value="En curso">En curso</option>
							<option value="Finalizado">Finalizados</option>
							<option value="Cancelado">Cancelados</option>
						</select>
						<button type="submit" id="btnfiltrar" class="btn btn-sm btn-dark"><i class="fa fa-filter" aria-hidden="true"></i> Filtrar</button>
					</form>
				</div>
			@else
				@if ($usuario->modulos[4]->pivot->editar == 1)
					<div class="align-center">
						<a href="{{route('servicios.nuevo')}}" class="btn btn-dark btn-sm">Nuevo servicio</a>
						<a href="{{route('servicios.ubicar')}}" class="btn btn-dark btn-sm">Mapa de servicios</a>
					</div>
				@endif
			@endif
			
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Cliente</th>
							<th>Teléfono</th>
							<th>Dir. Origen</th>
							<th>Despacho</th>
							<th>Modo pago</th>
							<th>Asignación</th>
							<th>Conductor</th>
							<th>Vehiculo</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
						@forelse($servicios as $servicio)
							<tr>
								<td>{{ $servicio->fecha }}</td>
								<td>{{ $servicio->cliente->nombres }}</td>
								<td>{{ $servicio->cliente->telefono }}</td>
								<td>{{ $servicio->direccion }}</td>
								<td>@if ($servicio->horaprogramada == null)
										Inmediato
									@else
										{{$servicio->horaprogramada}}
									@endif
								</td>
								<td>{{ $servicio->pago }}</td>
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
								<td> {{$servicio->estado}} </td>
								<td>
									<a href="{{ route('servicios.detalles', ['servicio' => $servicio->id]) }}" class="btn btn-info btn-sm">Detalles</a>
									@if ($servicio->estado == "Finalizado" || $servicio->estado == "Cancelado")
										<a href="{{ route('novedades.listar', ['servicio' => $servicio->id]) }}" class="btn btn-warning btn-sm">Novedades</a>
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
				{{ $servicios->links() }}
			</div>
		</div>
	</div>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection
