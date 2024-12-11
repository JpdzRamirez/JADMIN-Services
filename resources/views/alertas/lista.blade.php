@extends('layouts.logeado')

@section('sub_title', 'Lista de alertas')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="align-center" style="display: flex">
						<form  method="GET" class="form-inline" action="/alertas/filtrar">
							<select id="estado" name="estado" class="form-control" style="width: 200px">
								<option value="sinfiltro">Sin filtro</option>
								<option value="Pendiente">Pendientes</option>
								<option value="Atendida">Atendidas</option>
							</select>
							<button type="submit" id="btnfiltrar" class="btn btn-sm btn-dark"><i class="fa fa-filter" aria-hidden="true"></i> Filtrar</button>
						</form>
			</div>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Fecha</th>
                            <th>Tipo</th>
                            <th>Placa/Conductor</th>
							<th>Estado</th>
                            <th>Solución</th>
                            
						</tr>
					</thead>
					<tbody>
						@forelse($alertas as $alerta)
							<tr>
								<td>{{ $alerta->fecha }}</td>
                                <td>{{ $alerta->tipo }}</td>
                                <td>{{ $alerta->placa}}/{{ $alerta->cuentac->conductor->NOMBRE}}</td>
								<td>{{ $alerta->estado }}</td>
								<td>{{ $alerta->solucion }}</td>
								<td>@if ($alerta->estado == "Pendiente")
										<a href="{{ route('alertas.gestionar', ['alerta' => $alerta->id]) }}" class="btn btn-warning btn-sm">Gestionar</a>
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
				@if(method_exists($alertas,'links'))
					{{ $alertas->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection