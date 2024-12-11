@extends('layouts.logeado')

@section('sub_title', 'Editar vale en valera: '. $valera->nombre. ' de la empresa ' . $valera->cuentae->agencia->NOMBRE)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($vale, ['route' => $route, 'method' => $method] ) }}
                {{ Form::hidden('id', null) }}
                <input type="hidden" name="valeras_id" id="valeras_id" value="{{$valera->id}}">
                
                <div class="form-group row {{ $errors->has('codigo') ? 'form-error': '' }}">
                        {{ Form::label('codigo', 'Código del vale', ['class' => 'label-required col-md-2']) }}
                        <div class="col-md-10">
                            {{ Form::number('codigo', null, ['required', 'readonly', 'class' => 'form-control col-md-6 col-sm-10']) }}
                            {!! $errors->first('codigo', '<p class="help-block">:message</p>') !!}
                        </div>
                </div>

                <div class="form-group row {{ $errors->has('clave') ? 'form-error': '' }}">
                        {{ Form::label('clave', 'Contraseña del vale', ['class' => 'label-required col-md-2']) }}
                        <div class="col-md-10">
                            {{ Form::text('clave', null, ['required', 'readonly', 'class' => 'form-control col-md-6 col-sm-10']) }}
                            {!! $errors->first('clave', '<p class="help-block">:message</p>') !!}
                        </div>
                </div>
                
                @if ($vale->estado == "Asignado")
					<div class="form-group row {{ $errors->has('beneficiario') ? 'form-error': '' }}">
							{{ Form::label('beneficiario', 'Beneficiario del vale', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								{{ Form::text('beneficiario', null, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
								{!! $errors->first('beneficiario', '<p class="help-block">:message</p>') !!}
							</div>
					</div>
					
					<div class="form-group row {{ $errors->has('centrocosto') ? 'form-error': '' }}">
						{{ Form::label('centrocosto', 'Centro de Costo', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('centrocosto', null, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
							{!! $errors->first('centrocosto', '<p class="help-block">:message</p>') !!}
						</div>
					</div>

                    <div class="form-group row {{ $errors->has('referenciado') ? 'form-error': '' }}">
							{{ Form::label('referenciado', 'Actividad a realizar', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								{{ Form::text('referenciado', null, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
								{!! $errors->first('referenciado', '<p class="help-block">:message</p>') !!}
							</div>
                    </div>

					<div class="form-group row {{ $errors->has('destino') ? 'form-error': '' }}">
						{{ Form::label('destino', 'Dirección destino', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('destino', null, ['required', 'class' => 'form-control col-md-6 col-sm-10']) }}
							{!! $errors->first('destino', '<p class="help-block">:message</p>') !!}
						</div>
					</div>

						<div class="form-group row {{ $errors->has('estado') ? 'form-error': '' }}">
							{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								<select name="estado" id="estado" class="form-control col-md-6 col-sm-10">
									<option value="{{$vale->estado}}" selected>{{$vale->estado}}</option>
									<option value="Libre">Libre</option>
								</select>
								{!! $errors->first('estado', '<p class="help-block">:message</p>') !!}
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
		$("#estado").change(function () {
			if($(this).val() == "Libre"){
				$("#centrocosto").attr("required", false);
				$("#beneficiario").attr("required", false);
				$("#referenciado").attr("required", false);
				$("#destino").attr("required", false);
				$("#fecha").attr("required", false);
			}else{
				$("#centrocosto").attr("required", true);
				$("#beneficiario").attr("required", true);
				$("#referenciado").attr("required", true);
				$("#destino").attr("required", true);
				$("#fecha").attr("required", false);
			}
		});
	</script>
@endsection