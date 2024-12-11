@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de valeras. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Lista de valeras')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
				<div class="align-center" style="display: inline">
					@if ($usuario->roles_id == 1 || $usuario->modulos[5]->pivot->editar == 1)
						<a href="{{route('valeras.nuevo')}}" class="btn btn-dark btn-sm" style="margin-right: 20px">Nueva valera</a>
					@endif
					<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
					<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
				</div>

				@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
				@endif

			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Agencia</th>
							<th>Nombre valera</th>
                            <th>Fecha creación</th>
                            <th>Límite inferior</th>
							<th>Límite superior</th>
							<th>Vigencia</th>
                            <th>Estado</th>
						</tr>
						<tr>
								<th><form method="GET" class="form-inline" action="/valeras/filtrar"><input type="text" name="empresa" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/valeras/filtrar"><input type="text" name="nombre" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/valeras/filtrar"><input type="date" name="fecha" class="form-control" onchange="this.form.submit()" required></form></th>
								<th><form method="GET" class="form-inline" action="/valeras/filtrar"><input type="number" name="inferior" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/valeras/filtrar"><input type="number" name="superior" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/valeras/filtrar"><input type="date" name="vigencia" class="form-control" onchange="this.form.submit()" required></form></th>
								<th>
									<form  method="GET" class="form-inline" action="/valeras/filtrar">
										<select id="estado" name="estado" class="form-control" onchange="this.form.submit()">
											<option value="sinfiltro"></option>
											<option value="1">Activa</option>
											<option value="0">Inactiva</option>
										</select>
									</form>
								</th>
						</tr>
					</thead>
					<tbody>
						@forelse($valeras as $valera)
							<tr>
								<td>{{ $valera->cuentae->agencia->NOMBRE}}</td>
								<td>{{ $valera->nombre }}</td>
								<td>{{ $valera->fecha }}</td>
                                <td>{{ $valera->inferior }}</td>
								<td>{{ $valera->superior }}</td>
								<td>{{ $valera->inicio}} / {{ $valera->fin}}</td>
                                <td>@if ($valera->estado == 1)
                                        Activa
                                    @else
                                        Inactiva
                                    @endif
								</td>
								<td>
									@if ($valera->cuentae->agencia->TERCERO != 435)
										<a href="{{ route('valeras.vales', ['valera' => $valera->id]) }}" class="btn btn-success btn-sm">Vales</a>
									@else
										<a href="/valera/avianca/{{$valera->id}}/vales" class="btn btn-success btn-sm">Vales</a>
									@endif
									@if ($usuario->roles_id == 1 || $usuario->modulos[5]->pivot->editar == 1)
										<a href="{{ route('valeras.editar', ['valera' => $valera->id]) }}" class="btn btn-warning btn-sm">Actualizar</a>
										@if ($valera->estado == 1)
											<a href="{{ route('valeras.asignar', ['valera' => $valera->id]) }}" class="btn btn-info btn-sm">Asignar vale</a>
										@endif
										<a href="/valeras/{{$valera->id}}/listanegra" class="btn btn-sm" style="background-color: gray; color: white">Lista negra</a>
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
				@if(method_exists($valeras,'links'))
					{{$valeras->links()}}
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
				url: "/valeras/exportar",
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

				filename = "Valeras.xlsx";
				csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.close()		
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: textStatus
				});
			});
		}
	</script>
@endsection