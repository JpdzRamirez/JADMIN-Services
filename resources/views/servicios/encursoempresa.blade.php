@extends('layouts.logeado')

@section('sub_title', 'Servicios en Curso')

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