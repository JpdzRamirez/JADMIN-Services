@extends('layouts.logeado')

@section('sub_title', 'Actualizar propietario ' . $propietario->PRIMER_NOMBRE . " " . $propietario->PRIMER_APELLIDO . " " . $propietario->SEGUNDO_APELLIDO )

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($propietario, ['route' => $route, 'id'=>'formprop', 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}
				
					<div class="form-group row {{ $errors->has('DIRECCION') ? 'form-error': '' }}">
							{{ Form::label('DIRECCION', 'Dirección', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								{{ Form::text('DIRECCION', null, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
								{!! $errors->first('DIRECCION', '<p class="help-block">:message</p>') !!}
							</div>
						</div>
				
					<div class="form-group row {{ $errors->has('CELULAR') ? 'form-error': '' }}">
							{{ Form::label('CELULAR', 'Celular', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								{{ Form::text('CELULAR', null, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
								{!! $errors->first('CELULAR', '<p class="help-block">:message</p>') !!}
							</div>
					</div>

				<div class="form-group row {{ $errors->has('TELEFONO') ? 'form-error': '' }}">
						{{ Form::label('TELEFONO', 'Teléfono', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('TELEFONO', null, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
							{!! $errors->first('TELEFONO', '<p class="help-block">:message</p>') !!}
						</div>
				</div>

				<div class="form-group row {{ $errors->has('EMAIL') ? 'form-error': '' }}">
						{{ Form::label('EMAIL', 'E-mail', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('EMAIL', null, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
							{!! $errors->first('EMAIL', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				
				<div class="form-group text-center">
					<button type="button" class="btn btn-dark" onclick="actualizar();">Enviar</button>
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection
@section('script')
	<script>
		function actualizar() {
			Swal.fire({
				title: '<strong>Enviando...</strong>',
				html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
				showConfirmButton: false,
			});
			$.ajax({
				type: "post",
				url: $("#formprop").attr("action"),
				data: $("#formprop").serialize()
			}).done(function (data) {  
				Swal.close();
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);
				var csvFile;
				var downloadLink;

				filename = "Plantilla.xlsx";
				csvFile = new Blob([byteArray], { type: 'application/vnd.ms-excel' });
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.fire({
					title: "Actualización realizada",
					text: "Los datos se actualizaron correctamente",
					icon: "success",
					confirmButtonText: 'OK',
				}).then((result) => {
					location.reload();
				});
			}).fail(function (jqXHR, textStatus, errorThrown) {  
				Swal.close();
				Swal.fire(
					'Error enviando los datos',
					textStatus,
					'error'
				);
			});
		}
	</script>
@endsection

