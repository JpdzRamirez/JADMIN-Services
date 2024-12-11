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
							<th style="width: 250px">Beneficiario</th>
							<th style="width: 150px">Ruta</th>
							<th>Valor</th>
							<th>Centro de costo</th>
                            <th>Estado</th>                   
						</tr>
						<tr>
					        <th></th>
					        <th>
								<form method="GET" class="form-inline" action="/valera/avianca/filtrar/{{$valera->id}}">
									<input type="date" value="{{ isset($filtro) && $filtro[0] == 'FechaInicio' ?  ($filtro[1]) : '' }}" id="fecha_inicio" name="fecha_inicio" class="form-control" onchange="this.form.submit()" required>
								</form>
							</th>
							<th>
								<form method="GET" class="form-inline" action="/valera/avianca/filtrar/{{$valera->id}}">
									<input type="date" value="{{ isset($filtro) && $filtro[0] == 'FechaFin' ?  ($filtro[1]) : '' }}" id="fecha_fin" name="fecha_fin" class="form-control" onchange="this.form.submit()" required>
								</form>
							</th>
							<th>
                                <form method="GET" class="form-inline" action="/valera/avianca/filtrar/{{$valera->id}}"><input type="text" name="codigo" class="form-control" required></form>
                            </th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>
								<form  method="GET" class="form-inline" action="/valera/avianca/filtrar/{{$valera->id}}">
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
								<td>
                                    @if($vale->servicio != null)
                                        {{ $vale->servicio->usuarios }}
                                    @endif
								</td>
								@if ($vale->servicio != null)
									<td>{{ $vale->servicio->unidades}} {{$vale->servicio->cobro}}</td>
									<td>${{ number_format($vale->servicio->valor)}}</td>
								@else
									<td></td>
									<td></td>
								@endif
								<td>
                                    @if ($vale->servicio != null)
                                        @if (count($vale->servicio->usuariosav) > 0)
                                            {{$vale->servicio->usuariosav[0]->centrocosto}}
                                        @else
                                            {{ $vale->centrocosto }}
                                        @endif
                                    @endif                                                             
                                </td>						
								<td>{{ $vale->estado}}</td>
								<td style="background-color: white">
									@if (Auth::user()->roles_id == 4)
										@if($vale->estado == "Usado")
											<a href="/servicios/detalles/{{$vale->servicio->id}}" target="_blank" class="btn btn-info btn-sm">Ir a servicio</a>
										@endif
									@elseif($usuario->roles_id == 1 || $usuario->modulos[5]->pivot->editar == 1)
										@if ($vale->estado == "Usado")
                                            <a href="/servicios/detalles/{{$vale->servicio->id}}" target="_blank" class="btn btn-info btn-sm">Ir a servicio</a>
										@endif
									@elseif($usuario->roles_id == 2)
										@if ($vale->estado == "Usado")
											<a href="/servicios/detalles/{{$vale->servicio->id}}" target="_blank" class="btn btn-info btn-sm">Ir a servicio</a>
										@endif
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
@section('script')
	<script>
	
		function toexcel(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/valera/avianca/exportar/{{$valera->id}}",
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
	</script>
@endsection
