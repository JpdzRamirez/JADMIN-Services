@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Vales en la valera: '. $valera->nombre . '. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Vales en la valera: '. $valera->nombre)
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
			<div class="align-center" style="display: inline">
					<button type="button" class="btn btn-dark btn-sm" onclick="plantilla();">Descargar plantilla</button>
					<button type="button" data-toggle="modal" data-target="#Modal" class="btn btn-dark btn-sm">Importar plantilla</button>
						
					<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
					<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
			</div>
			
			<div class="text-center">
				<ul style="list-style-type: none;font-size: x-large;display:inline-block">
					<li style="color: mediumseagreen;float: left;margin-right: 20px">Libres: {{$libres}}</li>
					<li style="color: cornflowerblue;float: left;margin-right: 20px">Asignados: {{$asignados}}</li>
					<li style="color: orange;float: left;margin-right: 20px">Visados: {{$visados}}</li>
					<li style="color: lightcoral;float: left;margin-right: 20px">Usados: {{$usados}}</li>
				</ul>
			</div>
			
				@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
				@endif
				@php
					$count = 1;
				@endphp
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
						    <th>Número</th>
						    <th>Fecha inicio</th>
							<th>Fecha fin</th>
							<th>Código del vale</th>
							<th>Contraseña</th>
							<th>Beneficiario</th>
							<th>Unds/Mins/Ruta</th>
							<th>Valor</th>
							<th>Centro de costo</th>
                            <th>Actividad</th>
                            <th>Estado</th>                   
						</tr>
						<tr>
					        <th></th>
					        <th>
								<form method="GET" class="form-inline" action="/valeras/{{$valera->id}}/vales/filtrar">
									<input type="date" value="{{ isset($filtro) && $filtro[0] == 'FechaInicio' ? ( $filtro[1]) : '' }}" id="fecha_inicio" name="fecha_inicio" class="form-control" onchange="this.form.submit()" required>
								</form>
							</th>
							<th>
								<form method="GET" class="form-inline" action="/valeras/{{$valera->id}}/vales/filtrar">
									<input type="date" value="{{ isset($filtro) && $filtro[0] == 'FechaFin' ?  ($filtro[1]) : '' }}" id="fecha_fin" name="fecha_fin" class="form-control" onchange="this.form.submit()" required>
								</form>
							</th>
							<th><form method="GET" class="form-inline" action="/valeras/{{$valera->id}}/vales/filtrar"><input type="text" name="codigo" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/valeras/{{$valera->id}}/vales/filtrar"><input type="text" name="clave" class="form-control" required></form></th>
							<th>
								<form method="GET" class="form-inline" action="/valeras/{{$valera->id}}/vales/filtrar"><input type="text" name="beneficiario" class="form-control" required></form>
							</th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>
								<form  method="GET" class="form-inline" action="/valeras/{{$valera->id}}/vales/filtrar">
									<select id="estado" name="estado" class="form-control" onchange="this.form.submit()">
										<option value="sinfiltro"></option>
										<option value="Libre">Libre</option>
										<option value="Asignado">Asignado</option>
										<option value="Visado">Visado</option>
										<option value="Usado">Usado</option>
									</select>
								</form>
							</th>
						</tr>
					</thead>
					<tbody>
						@forelse($vales as $vale)
							@if ($vale->estado == "Libre")
								<tr style="background-color: mediumseagreen">
							@elseif($vale->estado == "Usado")
								<tr style="background-color: lightcoral">
							@elseif($vale->estado == "Visado")
							    <tr style="background-color: orange">
							@else
								<tr style="background-color: cornflowerblue">
							@endif
						
						        <td>{{$vales->perPage()*($vales->currentPage()-1)+$count}}</td>
						        @if ($vale->servicio != null)
									<td>
										@if (count($vale->servicio->registros) >= 3)
											{{ $vale->servicio->registros[2]->fecha}}
										@endif
									</td>							
									<td>
										@if (count($vale->servicio->registros) >= 4)
											{{ $vale->servicio->registros[3]->fecha}}
										@endif
									</td>
								@else
									<td></td>
									<td></td>
								@endif
								<td>{{ $vale->codigo }}</td>
								<td>{{ strtoupper($vale->clave) }}</td>
								<td>
								@if($vale->servicio != null)
									@if($vale->beneficiario != null)
										{{ $vale->beneficiario }}
									@else
										{{ $vale->servicio->usuarios }}
									@endif
								@else
									{{ $vale->beneficiario }}
								@endif
								</td>
								@if ($vale->servicio != null)
									<td>{{ $vale->servicio->unidades}} {{$vale->servicio->cobro}}</td>
									<td>${{ number_format($vale->servicio->valor)}}</td>
								@else
									<td></td>
									<td></td>
								@endif
								<td>{{ $vale->centrocosto}}</td>						
								<td>{{ $vale->referenciado }}</td>
								<td>{{ $vale->estado}}</td>
								<td style="background-color: white">
                                    @if ($vale->estado == "Asignado")
                                        <a href="/valeras/{{$valera->id}}/vales/{{$vale->id}}/editar" class="btn btn-warning btn-sm">Editar vale</a>
                                    @elseif($vale->estado == "Usado")
                                        <a href="/servicios/detalles/{{$vale->servicio->id}}" target="_blank" class="btn btn-info btn-sm">Ir a servicio</a>
                                    @endif
                                    @if($vale->estado == "Libre")
                                        <a href="/valeras/{{$valera->id}}/vale/nuevo/{{$vale->id}}" class="btn btn-success btn-sm">Asignar vale</a>
                                    @endif				
								</td>
							</tr>
							@php
								$count++;
							@endphp
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($vales,'links'))
					{{$vales->links()}}
				@endif	
			</div>
		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Importar plantilla</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
        <form action="/valeras/{{$valera->id}}/importar" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
				<div class="row">
					<div class="col-md-4">
						<label for="plantilla">Seleccionar plantilla</label>
					</div>
					<div class="col-md-8">
						<input type="file" name="plantilla" id="plantilla" accept=".xls,.xlsx" required>
					</div>
				</div>
            </div>
            <div class="modal-footer">
                    <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Importar</button>
            </div>
        </form>
    </div>
</div>
</div>

<div id="liberar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">¿Está seguro de liberar este vale?</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
				
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <a id="aliberar" href="#" class="btn btn-success">Continuar</a>
            </div>
    	</div>
	</div>
</div>

<div id="eliminar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">¿Está seguro de eliminar este vale?</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
				
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <a id="aeliminar" href="#" class="btn btn-success">Continuar</a>
            </div>
    	</div>
	</div>
</div>
@endsection
@section('script')
	<script>

		$(document).on('click', '.btn-danger', function(e){
            var vale = e.target.value;
			$('#aliberar').attr('href', '/valeras/{{$valera->id}}/vales/' + vale + '/liberar');
		});

		$(document).on('click', '.valedelete', function(e){
            var vale = e.target.value;
			$('#aeliminar').attr('href', '/valeras/{{$valera->id}}/vales/' + vale + '/eliminar');
		});
	
		function toexcel(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/valeras/{{$valera->id}}/vales/exportar",
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

				filename = "Vales de {{$valera->nombre}}.xlsx";
				csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.close();
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
		}

		function plantilla(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/valeras/{{$valera->id}}/plantilla"
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

				filename = "Plantilla {{$valera->nombre}}.xlsx";
				csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.close();
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
		}
	</script>
@endsection
