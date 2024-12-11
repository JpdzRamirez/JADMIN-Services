@extends('layouts.logeado')

@section('sub_title', 'Lista de conductores')

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<div class="align-center" style="display: flex">
						<form method="GET" action="/conductores/buscar">
							<input type="text" name="identificacion" id="identificacion" class="form-control" style="width: 200px; display: inline" placeholder="Identificación" required>
							<button type="submit" class="btn btn-sm btn-dark"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
						</form>
					</div>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Identificación</th>
							<th>Nombre</th>
							<th>Teléfono</th>
							<th>Estado</th>
							<th>Saldo</th>
						</tr>
					</thead>
					<tbody>
						@forelse($conductores as $conductor)
							<tr>
								<td>{{ $conductor->NUMERO_IDENTIFICACION}}</td>
								<td>{{ $conductor->NOMBRE }}</td>
								<td>{{ $conductor->TELEFONO }}</td>
								<td>{{ $conductor->cuentac->estado }}</td>
								<td>${{ number_format($conductor->cuentac->saldo) }}</td>
								<td>
									<a href="{{ route('conductores.vehiculos', ['conductor' => $conductor->CONDUCTOR]) }}" class="btn btn-success btn-sm">Vehiculos</a>
									@if ($usuario->roles_id == 1)
										<a href="{{ route('conductores.editar', ['conductor' => $conductor->CONDUCTOR]) }}" class="btn btn-warning btn-sm">Actualizar</a>
									@else
										@if ($usuario->modulos[1]->pivot->editar == 1)
											<a href="{{ route('conductores.editar', ['conductor' => $conductor->CONDUCTOR]) }}" class="btn btn-warning btn-sm">Actualizar</a>
										@endif
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
				@if(method_exists($conductores,'links'))
					{{ $conductores->links() }}
				@endif				
			</div>
		</div>
	</div>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection

