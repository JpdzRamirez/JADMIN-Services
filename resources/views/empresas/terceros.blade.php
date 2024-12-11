@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de Terceros. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Lista de Terceros')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
					@if($errors->first('sql') != null)
					<div class="alert alert-danger" style="margin:5px 0">
						<h6>{{$errors->first('sql')}}</h6>
					</div>				
					@endif
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Nombre</th>
							<th>Nro. Identificación</th>
							<th>Dirección</th>
							<th>Teléfono</th>
						</tr>
						<tr>
								<th><form method="GET" class="form-inline" action="/terceros/filtrar"><input type="text" name="razon" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/terceros/filtrar"><input type="text" name="nit" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/terceros/filtrar"><input type="text" name="direccion" class="form-control" required></form></th>
								<th><form method="GET" class="form-inline" action="/terceros/filtrar"><input type="text" name="telefono" class="form-control" required></form></th>
						</tr>
					</thead>
					<tbody>
						@forelse($terceros as $tercero)
							<tr>
								<td>{{ $tercero->RAZON_SOCIAL }}</td>
								<td>{{ $tercero->NRO_IDENTIFICACION }}</td>
								<td>{{ $tercero->DIRECCION }}</td>
								<td>{{ $tercero->TELEFONO }}</td>
								<td>
									@if ($usuario->roles_id == 1 || $usuario->modulos[4]->pivot->editar == 1)
										@if ($tercero->users_id == null)
											<a href="{{ route('terceros.editar', ['tercero' => $tercero->TERCERO, 'metodo' => 'post']) }}" class="btn btn-warning btn-sm">Crear Usuario</a>
										@else
											<a href="{{ route('terceros.editar', ['tercero' => $tercero->TERCERO, 'metodo' => 'put']) }}" class="btn btn-info btn-sm">Editar usuario</a>
										@endif
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
				@if(method_exists($terceros,'links'))
					{{$terceros->links()}}
				@endif				
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).on('click', '.btn-success', function(e){
			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});
			var direccion = e.target.value;	
			$.ajax({
                method: "GET",
                url: direccion
            })
            .done(function (data, textStatus, jqXHR) {

				if(data != "Sin valeras"){
					const byteCharacters = atob(data);
                	const byteNumbers = new Array(byteCharacters.length);
                	for (let i = 0; i < byteCharacters.length; i++) {
                    	byteNumbers[i] = byteCharacters.charCodeAt(i);
                	}
                	const byteArray = new Uint8Array(byteNumbers);

                	var csvFile;
                	var downloadLink;

                	filename = "Vales usados.xlsx";
                	csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
                	downloadLink = document.createElement("a");
                	downloadLink.download = filename;
                	downloadLink.href = window.URL.createObjectURL(csvFile);
                	downloadLink.style.display = "none";
                	document.body.appendChild(downloadLink);
                	downloadLink.click();
					
					Swal.close();
				}else{
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No han sido creadas valeras para esta tercero'
					});
				}                            
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Error consultando la base de datos'
				});
            });         		       
		});

		function toexcel(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/terceros/exportar",
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

				filename = "terceros.xlsx";
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
@section('sincro')
	<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection