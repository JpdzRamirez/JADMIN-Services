@extends('layouts.logeado')

@section('sub_title', 'Vehiculos del conductor: '. $conductor->NOMBRE)

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<h3>Vehiculo activo</h3>
				<div class="table-responsive" id="listar">
						<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
							<thead>
								<tr>
									<th>Placa</th>
									<th>Marca</th>
									<th>Modelo</th>
									<th>Estado</th>
									<th>Propietario</th>
								</tr>
							</thead>
							<tbody>
								@forelse($conductor->vehiculos as $vehiculo)
									@if ($vehiculo->pivot->SW_ACTIVO_NUEVO_CRM == 1)
										<tr>
											<td>{{ $vehiculo->PLACA }}</td>
											<td>{{ $vehiculo->marca->DESCRIPCION }}</td>
											<td>{{ $vehiculo->MODELO }}</td>
											<td>
												@if ($vehiculo->pivot->SW_ACTIVO_NUEVO_CRM == 1)
													Activo
												@else
													Inactivo
												@endif
											</td>
											
											<td>{{ $vehiculo->propietario->tercero->PRIMER_NOMBRE}} {{ $vehiculo->propietario->tercero->PRIMER_APELLIDO}}
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
					</div>

			<h3>Historial de vehiculos</h3>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Placa</th>
							<th>Marca</th>
							<th>Modelo</th>
							<th>Estado</th>
							<th>Propietario</th>
						</tr>
					</thead>
					<tbody>
						@forelse($conductor->vehiculos as $vehiculo)
						@if ($vehiculo->pivot->SW_ACTIVO_NUEVO_CRM == 0)
							<tr>
								<td>{{ $vehiculo->PLACA }}</td>
								<td>{{ $vehiculo->marca->DESCRIPCION }}</td>
								<td>{{ $vehiculo->MODELO }}</td>
								<td>
									@if ($vehiculo->pivot->SW_ACTIVO_NUEVO_CRM == 1)
										Activo
									@else
										Inactivo
									@endif
								</td>
								
								<td>{{ $vehiculo->propietario->tercero->PRIMER_NOMBRE}} {{ $vehiculo->propietario->tercero->PRIMER_APELLIDO}}
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
			</div>
		</div>
	</div>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection