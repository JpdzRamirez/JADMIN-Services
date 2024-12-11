@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de vehiculos. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Lista de vehiculos')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
				<div class="align-center" style="display: inline">
						<a href="{{route('vehiculos.ubicar')}}" class="btn btn-dark btn-sm" style="margin-right: 10px">Vehiculos conectados</a>
						<a href="/vehiculos/certificaciones" class="btn btn-dark btn-sm" style="margin-right: 10px">Certificaciones</a>
						<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
						<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
				</div>
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Placa</th>
							<th>Marca</th>
							<th>Modelo</th>
							<th>Propietario</th>
						</tr>
						<tr>
							<th><form method="GET" class="form-inline" action="/vehiculos/filtrar"><input type="text" name="placa" maxlength="8" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/vehiculos/filtrar"><input type="text" name="marca" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/vehiculos/filtrar"><input type="number" name="modelo" maxlength="4" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/vehiculos/filtrar"><input type="text" name="propietario" class="form-control" required></form></th>
						</tr>
					</thead>
					<tbody>
						@forelse($vehiculos as $vehiculo)
							<tr>
								<td>{{ $vehiculo->PLACA }}</td>
								<td>{{ $vehiculo->marca->DESCRIPCION }}</td>
								<td>{{ $vehiculo->MODELO}}</td>
								<td>{{ $vehiculo->propietario->tercero->PRIMER_NOMBRE }} {{ $vehiculo->propietario->tercero->PRIMER_APELLIDO }}</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($vehiculos,'links'))
					{{ $vehiculos->links() }}
				@endif
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>
	
		function toexcel(){

			$.ajax({
				method: "GET",
				url: "/vehiculos/exportar",
				data: { 'filtro': $("#filtro").val()}
			})
			.done(function (data, textStatus, jqXHR) {
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);

				var csvFile;
				var downloadLink;

				filename = "Vehiculos.xlsx";
				csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();
		
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				alert("Error consultando la base de datos");
			});
		}
	</script>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection