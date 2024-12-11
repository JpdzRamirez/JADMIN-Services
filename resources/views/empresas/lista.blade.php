@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de Agencias. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Lista de Agencias')
@endif
@section('style')
	<style>
	.popover-header {
		color: #ffff;
		background: #3A393B;
		font-size: 15px;
	}
	</style>
@endsection

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
			<div class="align-center" style="display: inline">
				<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
				<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
			</div>
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
							<th><form method="GET" class="form-inline" action="/empresas/filtrar"><input type="text" name="razon" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/empresas/filtrar"><input type="text" name="nit" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/empresas/filtrar"><input type="text" name="direccion" class="form-control" required></form></th>
							<th><form method="GET" class="form-inline" action="/empresas/filtrar"><input type="text" name="telefono" class="form-control" required></form></th>
						</tr>
					</thead>
					<tbody>
						@forelse($empresas as $empresa)
							<tr>
								<td>{{ $empresa->NOMBRE }}</td>
								<td>{{ $empresa->NRO_IDENTIFICACION }}</td>
								<td>{{ $empresa->DIRECCION }}</td>
								<td>{{ $empresa->TELEFONO }}</td>
								<td>
									@if ($usuario->roles_id == 1 || $usuario->modulos[4]->pivot->editar == 1)
										<a href="{{ route('empresas.editar', ['agencia' => $empresa->TERCERO . "_" . $empresa->CODIGO]) }}" class="btn btn-warning btn-sm">Actualizar</a>
									@endif
									@php
										$contratos = count($empresa->tercero->contratovale);
									@endphp
									<a href="{{ route('empresas.rutas', ['contrato' => $empresa->tercero->contratovale[$contratos-1]->CONTRATO_VALE]) }}" class="btn btn-primary btn-sm">Tarifas</a>
									@if ($empresa->tercero->TERCERO == 435)
										<button value="/empresa/avianca/exportarvales/{{$empresa->TERCERO}}_{{$empresa->CODIGO}}" class="btn btn-success btn-sm">Exportar vales</button>
										<button class="btn btn-sm btn-dark" onclick="abrirFormato();">Formato General</button>
									@else
										<button value="{{ route('empresas.valesxagencia', ['agencia' => $empresa->TERCERO . "_" . $empresa->CODIGO]) }}" class="btn btn-success btn-sm">Exportar vales</button>							
									@endif	
									@if ($empresa->tercero->TERCERO == 784)
										        <button type="button"
												class="btn btn-sm btn-dark"
												data-toggle="modal" 
												data-target="#modalPetro"
												data-form-action="/empresas/{{ $empresa->TERCERO }}/{{ $empresa->CODIGO }}/exportar/registros"
												data-modal-title="Exportar Vales -> {{ $empresa->NOMBRE }}">
												<i class="fa fa-car" aria-hidden="true"></i> Exportar Registros
											</button>
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
				@if(method_exists($empresas,'links'))
					{{$empresas->links()}}
				@endif				
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div id="aviancaModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="aviancaModal" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="min-width: 50%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Formato General Avianca</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<form id="formtrans" action="/servicios/avianca/formato_general" method="POST">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4">
							<label for="fechaini" class="label-required">Fecha Inicial</label>                         
						</div>
						<div class="col-md-8">
							<input type="date" name="fechaini" id="fechaini" class="form-control" required>	
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-4">
							<label for="fechafin" class="label-required">Fecha final</label>                         
						</div>
						<div class="col-md-8">
							<input type="date" name="fechafin" id="fechafin" class="form-control" required>
						</div>
					</div>
					<br>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="_token" value="{{csrf_token()}}">
					<button type="submit" class="btn btn-dark">Guardar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
				</form>
			</div>
		</div>
	</div>
	<div id="modalPetro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalPetroLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="min-width: 50%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-sm-center" id="modalPetroTitulo">Exportar Vales</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<form id="modalPetroExportForm" action="">
					@csrf
					<div class="modal-body">
						<div class="row">
							<div class="col-md-4">
								<label for="fechaInicio" class="label-required">Fecha Inicio</label>
							</div>
							<div class="col-md-8">
								<input name="fechaInicio" id="fechaInicio" 
								type="date" class="form-control" required>						
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label for="fechaFin" class="label-required">Fecha Fin</label>
							</div>
							<div class="col-md-8">
								<input name="fechaFin" id="fechaFin" 
								type="date" class="form-control" required>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="input-group mb-3">
							<div class="input-group-prepend align-items-baseline">
							  <div class="input-group-text">							
								<input
								 type="checkbox" 
								 name="exportarTodo" id="exportarTodo" value="false"
								 aria-label="Checkbox para exportar todos los registros"
								 data-toggle="popover" title="Información"  data-placement="left"
								 data-content="Seleccione para exportar todos los registros de todas las valeras">
							  </div>
							  <label for="exportarTodo">Exportar Todo</label>	
							</div>
						  </div>
						<button type="button" class="btn btn-dark ajax-submit">Guardar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					</div>
				</form>
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
			let direccion = e.target.value;	
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

                	let csvFile;
                	let downloadLink;

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
						text: 'No han sido creadas valeras para esta empresa'
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
				url: "/empresas/exportar",
				data: { 'filtro': $("#filtro").val()}
			})
			.done(function (data, textStatus, jqXHR) {
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);

				let csvFile;
				let downloadLink;

				filename = "Empresas.xlsx";
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


		function abrirFormato() {
				$("#load").hide();
				$("#aviancaModal").modal("show");
		}
		// actualiza información del modalPetro según sea la valera seleccionada

			$('button[data-toggle="modal"]').on('click', function() {
				// Obtener datos del botón
				let modalId = $(this).data('modal-id');
				let formAction = $(this).data('form-action');
				let modalTitle = $(this).data('modal-title');
				// Actualizar el contenido del modal
				$('#modalPetro #modalPetroTitulo').text(modalTitle);
				$('#modalPetro #modalPetroExportForm').attr('action', formAction);
			});


			//funcion para limpiar formulario al cerrar
			$('#modalPetro').on('hidden.bs.modal', function () {
				// Limpiar el formulario cuando el modal se cierre
				const form = $(this).find("form");
				form.trigger('reset');  // Restablecer el formulario a su estado inicial
			});


		//funcion para exportar excel petrosantander 
			$(".ajax-submit").on("click", function() {
				const form = $("#modalPetroExportForm");
				const formData = new FormData(form[0]);
				const actionUrl = form.attr("action");

				$.ajax({
					type: "POST",
					url: actionUrl,
					data: formData,
					processData: false,
					contentType: false,
					xhrFields: {
						responseType: 'blob' // Indica que la respuesta será un blob
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
					},
					success: function(blob, status, xhr) {
						if (xhr.status === 200) {
							const disposition = xhr.getResponseHeader('Content-Disposition');
							const filename = disposition && disposition.split('filename=')[1].replace(/"/g, '');
							const url = window.URL.createObjectURL(blob);
							const a = document.createElement('a');
							a.href = url;
							a.download = filename || 'archivo.xlsx';
							document.body.appendChild(a);
							a.click();
							a.remove();
							window.URL.revokeObjectURL(url);

							Swal.fire({
								icon: 'success',
								title: 'Exportación exitosa',
								text: 'Los registros se han exportado correctamente.',
							});
							form.trigger('reset');
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						let errorMessage = 'No se pudo procesar la solicitud.';
						let icon='error';
						let title='Error';
						// Verifica si la respuesta es JSON
						if (jqXHR.status === 422) {
							errorMessage = 'Errores de formulario, seleccione las fechas.';
						} else if (jqXHR.status === 404) {
							errorMessage = 'No hay registros disponibles.';
							icon = 'info';
							title = 'Información';
						} else if (jqXHR.status === 500) {
							errorMessage = 'Error interno del servidor.';
							icon = 'warning';
							title = 'Contacte al Administrador';
						}

					Swal.fire({
						icon: icon,
						title: title,
						text: errorMessage,
					});
					}
				});
			});
			$('[data-toggle="popover"]').popover({
				trigger: 'manual', // No usar el disparador por defecto
				animation: true
			}).on('mouseenter', function () {
				let _this = this;
				$(this).popover('show');
				$('.popover').on('mouseleave', function () {
					$(_this).popover('hide');
				});
			}).on('mouseleave', function () {
				let _this = this;
				setTimeout(function () {
					if (!$('.popover:hover').length) {
						$(_this).popover('hide');
					}
				}, 300);
			});
	</script>
@endsection