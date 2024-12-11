@extends('layouts.logeado')

@section('sub_title', 'Vehiculos del propietario '. $tercero->RAZON_SOCIAL)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<h3>Propietario principal</h3>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Placa</th>
							<th>Marca</th>
							<th>Modelo</th>
						</tr>
					</thead>
					<tbody>
                        @forelse($tercero->propietario->vehiculospri as $vehiculo)
							<tr>
								<td>{{ $vehiculo->PLACA }}</td>
								<td>{{ $vehiculo->marca->DESCRIPCION }}</td>
								<td>{{ $vehiculo->MODELO }}</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>


			<h3>Propietario asociado</h3>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar">
					<thead>
						<tr>
							<th>Placa</th>
							<th>Marca</th>
							<th>Modelo</th>
						</tr>
					</thead>
					<tbody>
                        @forelse($tercero->propietario->vehiculos as $vehiculo)
							<tr>
								<td>{{ $vehiculo->PLACA }}</td>
								<td>{{ $vehiculo->marca->DESCRIPCION }}</td>
								<td>{{ $vehiculo->MODELO }}</td>
							</tr>
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