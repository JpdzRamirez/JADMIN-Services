@extends('layouts.logeado')

@section('sub_title', 'Tarifas por Hora y Ruta para '. $contrato->tercero->RAZON_SOCIAL)

@section('sub_content')
	<div class="card">
		<div class="card-body">
		    <h5 style="margin-bottom: 30px">Tarifas por hora en el contrato {{$contrato->NUMERO_CONTRATO}} con vigencia {{$contrato->FECHA_INICIO}} / {{$contrato->FECHA_FIN}}</h5>
		    <div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<tr>
					    <th>Cobro hora movimiento</th>
					    <td>${{ number_format($contrato->TARIFA_COBRO_RECORRIDO) }}</td>
					</tr>
					<tr>
					    <th>Pago hora movimiento</th>
					    <td>${{ number_format($contrato->TARIFA_PAGO_RECORRIDO) }}</td>
					</tr>
					<tr>
					     <th>Cobro hora espera</th>
					    <td>${{ number_format($contrato->TARIFA_COBRO_ESPERA) }}</td>
					</tr>
					<tr>
					     <th>Pago hora espera</th>
					    <td>${{ number_format($contrato->TARIFA_PAGO_ESPERA) }}</td>
					</tr>
				</table>
			</div>
		        
		    <div class="align-center mt-4 mb-4">
				<h5 style="margin-bottom: 30px; margin-top: 30px; display: inline;">Rutas en el contrato {{$contrato->NUMERO_CONTRATO}} con vigencia {{$contrato->FECHA_INICIO}} / {{$contrato->FECHA_FIN}}</h5>
				<button id="exportButton" type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" ><i class="fa fa-file-excel-o" aria-hidden="true"></i></button> 
			</div>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar_rutas" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Secuencia</th>
							<th>Origen</th>
							<th>Destino</th>
							@if ($usuario->roles_id == 1)
							<th>Tarifa pago</th>
							@endif
							<th>Tarifa cobro</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
						@forelse($rutas as $ruta)
							<tr>
								<td>{{ $ruta->SECUENCIA }}</td>
								<td>{{ $ruta->ORIGEN }}</td>
								<td>{{ $ruta->DESTINO }}</td>
								@if ($usuario->roles_id == 1 )
								<td>${{ number_format($ruta->TARIFA_PAGO) }}</td>
								@endif
								<td>${{  number_format($ruta->TARIFA_COBRO) }}</td>
								<td>@if($ruta->SW_ACTIVO == "1")
								        Activa
								    @else
								        Inactiva
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
			</div>
		</div>
	</div>
@endsection
@section('sincro')
		<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
		<script>
			$("#exportButton").click(function (e) { 
				let table = document.getElementById('tab_listar_rutas');
				let wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
				XLSX.writeFile(wb, 'Tabla_Rutas_Exportada.xlsx');
			});
		</script>
@endsection