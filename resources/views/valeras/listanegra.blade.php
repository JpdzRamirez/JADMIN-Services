@extends('layouts.logeado')

@section('sub_title', 'Lista negra de la valera: ' . $valera->nombre )

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="align-center" style="display: inline">
                @if ($usuario->roles_id == 1 || $usuario->modulos[5]->pivot->editar == 1)
                    <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Agregar conductor</a>
					<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
					<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
                @endif
            </div>
				@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Identificación</th>
							<th>Nombre</th>
							<th>Fecha de bloqueo</th>
							<th>Razón de bloqueo</th>
							<th>Fecha de desbloqueo</th>
							<th>Razón de desbloqueo</th>
						</tr>
					</thead>
					<tbody>
						@forelse($valera->bloqueados as $conductor)
							<tr>
								<td>{{ $conductor->NUMERO_IDENTIFICACION }}</td>
								<td>{{ $conductor->NOMBRE }}</td>
								<td>{{ $conductor->pivot->bloqueo }}</td>
								<td>{{ $conductor->pivot->razon_bloqueo }}</td>
								<td>{{ $conductor->pivot->desbloqueo }}</td>
								<td>{{ $conductor->pivot->razon_desbloqueo }}</td>
								@if ($conductor->pivot->estado == "Bloqueado")
									<td><button type="button" class="btn btn-sm btn-warning" onclick="RemoverConductor({{$conductor->CONDUCTOR}}, {{$conductor->NUMERO_IDENTIFICACION}})">Desbloquear</button></td>
								@endif
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="2">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
			<div class="modal-dialog"  style="min-width: 50%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Agregar conductores</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<form action="/valeras/listanegra/agregar" method="POST">
					<div class="modal-body">
						<div class="row form-group">
							<div class="col-md-3">
								<label for="conductor" class="label-required">Identificación conductor</label>                         
							</div>
							<div class="col-md-9">
								<input type="text" id="conductor" name="conductor" class="form-control" required>                                  
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-3">
								<label for="razon" class="label-required">Razón bloqueo</label>                         
							</div>
							<div class="col-md-9">
								<input type="text" id="razon" name="razon" class="form-control" required>                                  
							</div>
						</div>			
					</div>
					<div class="modal-footer">
						<input type="hidden" name="valera" value="{{$valera->id}}">
						<input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Guardar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
    </div>
	<div id="ModalRemover" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
		<div class="modal-dialog"  style="min-width: 50%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Desbloquear conductor</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<form action="/valeras/listanegra/remover" method="POST">
					<div class="modal-body">
						<div class="row form-group">
							<div class="col-md-3">
								<label for="ccconductor" class="label-required">Identificación conductor</label>                         
							</div>
							<div class="col-md-9">
								<input type="text" id="ccconductor" name="ccconductor" class="form-control" readonly required>                                  
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-3">
								<label for="razondes" class="label-required">Razón desbloqueo</label>                         
							</div>
							<div class="col-md-9">
								<input type="text" id="razondes" name="razondes" class="form-control" required>                                  
							</div>
						</div>			
					</div>
					<div class="modal-footer">
						<input type="hidden" name="valera" value="{{$valera->id}}">
						<input type="hidden" name="itconductor" id="itconductor">
						<input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Guardar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>

		function RemoverConductor(conductor, identificacion) {
			$("#itconductor").val(conductor);
			$("#ccconductor").val(identificacion);
			$("#ModalRemover").modal("show");
		}

		function toexcel(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/valeras/{{$valera->id}}/listanegra/exportar"
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

				filename = "Bloqueados {{$valera->nombre}}.xlsx";
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