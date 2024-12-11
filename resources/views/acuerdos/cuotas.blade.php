@extends('layouts.logeado')

@section('sub_title', 'Cuotas de acuerdo # ' . $acuerdo->id)

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Número</th>
                            <th>Valor</th>
                            <th>Fecha vencimiento</th>
							<th>Estado</th>
                            <th>Fecha de pago</th>
						</tr>
					</thead>
					<tbody>
						@forelse($acuerdo->cuotasAll as $cuota)
							@if ($cuota->estado == "Vencida")
                                <tr style="background-color: lightcoral">
                            @elseif($cuota->estado == "Pagada")
                                <tr style="background-color: mediumseagreen">
                            @else
                                <tr>
                            @endif
								<td>{{ $cuota->numero }}</td>
                                <td>${{ number_format($acuerdo->pago_mensual, 2, ",", ".") }}</td>
                                <td>{{ $cuota->fecha_vencimiento }}</td>
								<td>{{ $cuota->estado }}</td>
                                <td>{{ $cuota->fecha_pago }} </td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="8">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
		</div>
	</div>
@endsection
