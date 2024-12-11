@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Cuentas Corriente de Sucursales. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Cuentas Corriente de Sucursales')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
			<div class="align-center" style="display: inline">
				@if ($usuario->roles_id == 1 || $usuario->modulos[7]->pivot->editar == 1)
					<a href="{{route('sucursales.nuevo')}}" class="btn btn-dark btn-sm">Nueva sucursal</a>
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
				<table class="table table-bordered" id="tab_listar">
					<thead>
						<tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Nombre</th>
							<th>Estado</th>
                        </tr>
                        <tr>
                                <th><form method="GET" class="form-inline" action="/sucursales/filtrar"><input type="number" name="id" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/sucursales/filtrar"><input type="text" name="empresa" class="form-control" required></form></th>
                                <th><form method="GET" class="form-inline" action="/sucursales/filtrar"><input type="text" name="nombre" class="form-control" required></form></th>
								<th>
									<form  method="GET" class="form-inline" action="/sucursales/filtrar">
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
                        <tr>
                            <td>0</td>
                            <td>JADMIN</td>
                            <td>Sucursal JADMIN</td>
                            <td>Activa</td>
                            <td><a href="/transacciones/sucursal/0" class="btn btn-success btn-sm" style="margin-top:1px">Transacciones</a></td>
                        </tr>
						@forelse($sucursales as $sucursal)
							<tr>
								<td>{{ $sucursal->id }}</td>
                                <td>{{ $sucursal->tercero->RAZON_SOCIAL }}</td>
                                <td>{{ $sucursal->user->nombres}}</td>
                                <td>@if ($sucursal->user->estado == 1)
                                        Activa
                                    @else
                                        Inactiva
                                    @endif
                                </td>
                                <td>
									@if ($usuario->roles_id == 1 || $usuario->modulos[7]->pivot->editar == 1)
										<a href="/sucursales/{{$sucursal->id}}/editar" class="btn btn-warning btn-sm" style="margin-top:1px">Actualizar</a>
									@endif
									<a href="/transacciones/sucursal/{{$sucursal->id}}" class="btn btn-success btn-sm" style="margin-top:1px">Transacciones</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
                </table>
                @if(method_exists($sucursales,'links'))
					{{$sucursales->links()}}
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
			url: "/sucursales/exportar",
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

			filename = "Sucursales.xlsx";
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
				text: 'No se pudo recuperar la información de la base de datos'
			});
		});
	}
	</script>
@endsection