@extends('layouts.logeado')

@section('sub_title', 'Registros de ' . $tercero->NRO_IDENTIFICACION . ', ' . $tercero->PRIMER_NOMBRE . ' ' . $tercero->PRIMER_APELLIDO)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Placa</th>
							<th>Factura</th>
                            <th>Fecha</th>
                            <th>Vencimiento</th>
                            <th>Saldo</th>
                            <th>Cuenta</th>
						</tr>
					</thead>
					<tbody>
						@forelse($registros as $registro)
							<tr>
								<td>{{ $registro->AFECTA }}</td>
                                <td>{{ $registro->FACTURA }} </td>
                                <td>{{ $registro->FECHA }}</td>
                                <td>{{ $registro->FECHA_VENCIMIENTO }}</td>
                                <td>{{ number_format($registro->SALDO_VENCIDO) }} </td>
                                <td>{{ $registro->CUENTA }}</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if (method_exists($registros, 'links'))
					{{ $registros->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection