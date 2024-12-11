@extends('layouts.logeado')

@section('sub_title', 'Cartera')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Identificación</th>
							<th>Nombre</th>
                            <th>Saldo</th>
						</tr>
						<tr>
							<form id="formfiltro" method="GET" class="form-inline" action="/carteras/filtrar"></form>
							<th>
								@if (!empty($c1))
									<input type="text" id="identificacion" value="{{$c1}}" name="identificacion" form="formfiltro" class="form-control filt">
								@else
									<input type="text" id="identificacion" name="identificacion" class="form-control filt" form="formfiltro">
								@endif
							</th>
							<th>
								@if (!empty($c2))
									<input type="text" name="nombre" id="nombre" value="{{$c2}}" form="formfiltro" class="form-control filt" >
								@else
									<input type="text" name="nombre" id="nombre" class="form-control filt" form="formfiltro" >
								@endif									
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($terceros as $tercero)
							<tr>
								<td>{{ $tercero->NRO_IDENTIFICACION }}</td>
								<td>{{ $tercero->PRIMER_NOMBRE }} {{ $tercero->SEGUNDO_NOMBRE }} {{ $tercero->PRIMER_APELLIDO }} {{ $tercero->SEGUNDO_APELLIDO }}</td>
								@php
									$deuda = 0;
									foreach ($tercero->cartera as $cartera) {
										$deuda = $deuda + $cartera->SALDO_VENCIDO;
									}
								@endphp
								<td>{{ number_format($deuda) }} </td>
								<td><a href="/carteras/{{ $tercero->TERCERO }}/registros" class="btn btn-info">Facturas</a>
									@if ($usuario->roles_id == 1 || $usuario->modulos[8]->pivot->editar == 1)
										<a href="/carteras/{{ $tercero->TERCERO }}/demanda" class="btn btn-success">Exportar demanda</a>	
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
				@if (method_exists($terceros, 'links'))
					{{ $terceros->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {

            $(".filt").keydown(function (event) {
                var keypressed = event.keyCode || event.which;
                if (keypressed == 13) {
                    $("#formfiltro").submit();
                }
            });
        });

		$("#formfiltro").submit(function (ev) {
			if($("#identificacion").val() == "" && $("#nombre").val() == ""){
				console.log("vacio");
				ev.preventDefault();
			}
		})
    </script>
@endsection
