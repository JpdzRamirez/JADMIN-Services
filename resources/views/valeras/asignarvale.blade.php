@extends('layouts.logeado')

@if ($method == "post")
	@section('sub_title', 'Asignar vale en valera: '. $valera->cuentae->agencia->NOMBRE)
@else
@section('sub_title', 'Editar vale en valera: '.  $valera->cuentae->agencia->NOMBRE)
@endif


@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($vale, ['route' => $route, 'method' => $method] ) }}
                {{ Form::hidden('id', null) }}
                <input type="hidden" name="valeras_id" id="valeras_id" value="{{$valera->id}}">
				<input type="hidden" name="pasajero" id="pasajero">
                
                <div class="form-group row">
                        {{ Form::label('codigo', 'Código del vale', ['class' => 'label-required col-md-2']) }}
                        <div class="col-md-10">
                            {{ Form::number('codigo', null, ['required', 'readonly', 'class' => 'form-control col-md-6 col-sm-10']) }}
                        </div>
                </div>

                <div class="form-group row">
                        {{ Form::label('clave', 'Contraseña del vale', ['class' => 'label-required col-md-2']) }}
                        <div class="col-md-10">
                            {{ Form::text('clave', null, ['required', 'readonly', 'class' => 'form-control col-md-6 col-sm-10']) }}
                        </div>
                </div>
				
					<div class="form-group row">
						{{ Form::label('beneficiario', 'Beneficiario del vale', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<input type="text" name="beneficiario" id="beneficiario" class="form-control col-md-6 col-sm-10" autocomplete="off">
						</div>
					</div>
					
					<div class="form-group row">
						{{ Form::label('centrocosto', 'Centro de Costo', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('centrocosto', $valera->centro, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
						</div>
					</div>

                    <div class="form-group row">
							{{ Form::label('referenciado', 'Actividad a realizar', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								{{ Form::text('referenciado', null, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
							</div>
                    </div>

					<div class="form-group row">
						{{ Form::label('destino', 'Dirección destino', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('destino', null, ['class' => 'form-control col-md-6 col-sm-10']) }}
						</div>
					</div>

					@if ($valera->cuentae->agencia->TERCERO == "14468")<!--Mayorautos-->
					<div class="form-group row">
						{{ Form::label('nombreasigna', 'Nombre quien asigna', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('nombreasigna', null, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
						</div>
					</div>
					@endif
					
					@if ($vale->estado == "Asignado")
						<div class="form-group row">
							{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								<select name="estado" id="estado" class="form-control col-md-6 col-sm-10">
									<option value="{{$vale->estado}}" selected>{{$vale->estado}}</option>
									<option value="Libre">Libre</option>
								</select>
							</div>
						</div>
					@endif

					@if ($vale->estado == "Usado")
						<div class="form-group row">
							{{ Form::label('unidades', 'Unidades', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								<input type="number" name="unidades" id="unidades" class="form-control col-md-6 col-sm-10" required>
							</div>
						</div>

						<div class="form-group row">
								{{ Form::label('valor', 'Valor', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-10">
									<input type="number" name="valor" id="valor" class="form-control col-md-6 col-sm-10" required>
								</div>
						</div>
					@endif
					<div class="form-group row">
						{{ Form::label('fecha', 'Fecha de Asignación', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::date('fecha', $fecha , ['required', 'readonly', 'class' => 'form-control col-md-6 col-sm-10']) }}
						</div>
					</div>

				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection
@section('script')
	<script>
		$("#beneficiario").autocomplete({
      		source: function( request, response ) {
        	$.ajax({
          		url: "/pasajeros/autocomplete",
          		dataType: "json",
          		data: {identificacion: request.term},
          	success: function( data ) {
				response( $.map(data, function (item) {
					var objeto = new Object();
					objeto.label = item.identificacion + "---" + item.nombre;
					objeto.value = item.nombre;
					objeto.id = item.id;
					return objeto;
				}) );
          		}
        	});
      	},
		minLength: 4,
		select: function (event, ui) {
			$("#pasajero").val(ui.item.id);
		}
    });



	</script>
@endsection