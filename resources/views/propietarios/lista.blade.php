@extends('layouts.logeado')

@section('sub_title', 'Lista de propietarios')

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<div class="align-center" style="display: flex">
						<form method="GET" action="/propietarios/buscar" id="formbuscar">
							<input type="text" name="identificacion" id="identificacion" class="form-control" style="width: 200px; display: inline" placeholder="Identificación" required>
							<button type="submit" class="btn btn-sm btn-dark"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
						</form>
					</div>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Identificación</th>
							<th>Nombres</th>
							<th>Apellidos</th>
							<th>Teléfono</th>
							<th>Email</th>
						</tr>
					</thead>
					<tbody>
						@forelse($propietarios as $propietario)
						@if ($propietario->TERCERO != 0)
							<tr>
								<td>{{ $propietario->NRO_IDENTIFICACION }}</td>
								<td>{{ $propietario->PRIMER_NOMBRE }} {{ $propietario->SEGUNDO_NOMBRE }}</td>
								<td>{{ $propietario->PRIMER_APELLIDO }} {{ $propietario->SEGUNDO_APELLIDO }}</td>
								<td>{{ $propietario->TELEFONO }}</td>
								<td>{{ $propietario->EMAIL }}</td>
								<td>
									<a href="{{ route('propietarios.vehiculos', ['propietario' => $propietario->TERCERO]) }}" class="btn btn-success btn-sm">Vehiculos</a>
									@if ($usuario->roles_id == 1)
										<a href="{{ route('propietarios.editar', ['propietario' => $propietario->TERCERO]) }}" class="btn btn-warning btn-sm">Actualizar</a>
									@else
										@if ($usuario->modulos[1]->pivot->editar == 1)
											<a href="{{ route('propietarios.editar', ['propietario' => $propietario->TERCERO]) }}" class="btn btn-warning btn-sm">Actualizar</a>
										@endif
									@endif
									
								</td>
							</tr>
							
						@endif
							
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($propietarios,'links'))
					{{ $propietarios->links() }}
				@endif
			</div>
		</div>
	</div>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection


