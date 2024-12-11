@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de Usuarios. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Lista de Usuarios')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
                <div class="align-center" style="display: inline">
						<a href="{{route('users.nuevo')}}" class="btn btn-dark btn-sm">Nuevo usuario</a>
						<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
						<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
                </div>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Usuario</th>
							<th>Nombre</th>
                            <th>Estado</th>
							<th>Rol</th>
						</tr>
						<tr>
								<th><form method="GET" class="form-inline" action="/users/filtrar"><input type="text" name="usuario" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/users/filtrar"><input type="text" name="nombres" class="form-control" required></form></th>
								<th>
									<form  method="GET" class="form-inline" action="/users/filtrar">
										<select id="estado" name="estado" class="form-control" onchange="this.form.submit()">
											<option value="sinfiltro"></option>
											<option value="1">Activo</option>
											<option value="0">Inactivo</option>
										</select>
									</form>
							</th>
							<th>
									<form  method="GET" class="form-inline" action="/users/filtrar">
										<select id="rol" name="rol" class="form-control" onchange="this.form.submit()">
											<option value="sinfiltro"></option>
											<option value="1">Administrador</option>
											<option value="2">Usuario</option>
											<option value="3">Sucursal</option>
											<option value="4">Empresa</option>
										</select>
									</form>
							</th>
						</tr>
					</thead>
					<tbody>
						@forelse($users as $user)
							<tr>
								<td>{{ $user->usuario }}</td>
								<td>{{ $user->nombres }}</td>
                                <td>@if ($user->estado == 1)
                                        Activo
                                    @else
                                        Inactivo
                                    @endif
                                </td>         
								<td>{{ $user->rol->nombre }}</td>
								<td>
                                    <a href="{{ route('users.editar', ['user' => $user->id]) }}" class="btn btn-warning btn-sm">Actualizar</a>
                                    @if ($user->roles_id == 2)
                                        <a href="{{ route('users.permisos', ['user' => $user->id]) }}" class="btn btn-info btn-sm">Permisos</a>
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
@section('script')
<script>
	
		function toexcel(){

			$.ajax({
				method: "GET",
				url: "/users/exportar",
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

				filename = "Usuarios.xlsx";
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