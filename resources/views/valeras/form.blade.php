@extends('layouts.logeado')

@if ($method == "post")
	@section('sub_title', 'Nueva valera')
@else
	@section('sub_title', 'Editar valera '.$valera->nombre)
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">

			@if (session('status'))
				<div class="alert alert-danger" style="margin:5px 0">
        			{{ session('status') }}
    			</div>
			@endif

			{{ Form::model($valera, ['route' => $route, 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}
				@if ($method == "post")
				<div class="form-group row {{ $errors->has('empresa') ? 'form-error': '' }}">
					{{ Form::label('empresa', 'Empresa', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<select name="empresa" id="empresa" class="form-control" style="width: 50%" required>
								<option value="0">Seleccionar empresa</option>
								@foreach ($empresas as $empresa)
									<option value="{{$empresa->TERCERO}}">{{$empresa->RAZON_SOCIAL}}</option>
								@endforeach
							</select>
							{!! $errors->first('empresa', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group row {{ $errors->has('agencia') ? 'form-error': '' }}">
					{{ Form::label('agencia', 'Agencia', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<select name="agencia" id="agencia" class="form-control" style="width: 50%" required>

							</select>
							{!! $errors->first('agencia', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				@else
					<div class="form-group row {{ $errors->has('empresa') ? 'form-error': '' }}">
						{{ Form::label('empresa', 'Empresa', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								<select name="empresa" id="empresa" class="form-control" style="width: 50%" disabled required>
									<option value="{{$valera->cuentae->agencia->tercero->TERCERO}}">{{$valera->cuentae->agencia->tercero->RAZON_SOCIAL}}</option>
								</select>
								{!! $errors->first('empresa', '<p class="help-block">:message</p>') !!}
							</div>
					</div>
					<div class="form-group row {{ $errors->has('agencia') ? 'form-error': '' }}">
						{{ Form::label('agencia', 'Agencia', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								<select name="agencia" id="agencia" class="form-control" style="width: 50%" disabled required>
									<option value="{{$valera->cuentae->agencia->CODIGO}}">{{$valera->cuentae->agencia->NOMBRE}}</option>
								</select>
								{!! $errors->first('agencia', '<p class="help-block">:message</p>') !!}
							</div>
					</div>
				@endif			

				<div class="form-group row {{ $errors->has('nombre') ? 'form-error': '' }}">
					{{ Form::label('nombre', 'Nombre de la valera', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						@if ($method == "post")
							{{ Form::text('nombre', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}	
						@else
							{{ Form::text('nombre', $valera->nombre, ['required', 'readonly', 'class' => 'form-control', 'style' => 'width:50%']) }}
						@endif			
							{!! $errors->first('nombre', '<p class="help-block">:message</p>') !!}
					</div>
                </div>
                    
                    <div class="form-group row {{ $errors->has('estado') ? 'form-error': '' }}">
							{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								<select name="estado" id="estado" class="form-control" style="width: 50%">
									@if ($method == "put")
										@if ($valera->estado == 1)
											<option value="1" selected>Activa</option>
											<option value="0">Inactiva</option>
										@else
											<option value="1">Activa</option>
                                    		<option value="0" selected>Inactiva</option>
										@endif
									@else
										<option value="1">Activa</option>
                                    	<option value="0">Inactiva</option>
									@endif                                 
								</select>
								{!! $errors->first('estado', '<p class="help-block">:message</p>') !!}
							</div>
                    </div>

                    <div class="form-group row {{ $errors->has('inferior') ? 'form-error': '' }}">
							{{ Form::label('inferior', 'Limite inferior', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								@if ($method == "put")
									{{ Form::number('inferior', null, ['required', 'readonly', 'class' => 'form-control', 'style' => 'width:50%']) }}
								@else
									{{ Form::number('inferior', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
								@endif
                                
								{!! $errors->first('inferior', '<p class="help-block">:message</p>') !!}
							</div>
                    </div>

                    <div class="form-group row {{ $errors->has('superior') ? 'form-error': '' }}">
							{{ Form::label('superior', 'Limite superior', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								@if ($method == "post")
									{{ Form::number('superior', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
								@else
									{{ Form::number('superior', null, ['required', 'min' => $valera->superior, 'class' => 'form-control', 'style' => 'width:50%']) }}
								@endif
								{!! $errors->first('superior', '<p class="help-block">:message</p>') !!}
							</div>
					</div>
					
					<div class="form-group row {{ $errors->has('inicio') ? 'form-error': '' }}">
						{{ Form::label('inicio', 'Inicio vigencia', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							@if ($method == "put")
								<input type="date" name="inicio" id="inicio" value="{{$valera->inicio}}" class="form-control" style="width: 50%" required>
							@else
								<input type="date" name="inicio" id="inicio" class="form-control" style="width: 50%" required>
							@endif
							{!! $errors->first('inicio', '<p class="help-block">:message</p>') !!}
						</div>
				</div>

				<div class="form-group row {{ $errors->has('fin') ? 'form-error': '' }}">
					{{ Form::label('fin', 'Fin vigencia', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						@if ($method == "put")
							<input type="date" name="fin" id="fin" value="{{$valera->fin}}" class="form-control" style="width: 50%" required>
						@else
							<input type="date" name="fin" id="fin" class="form-control" style="width: 50%" required>
						@endif
						
						{!! $errors->first('fin', '<p class="help-block">:message</p>') !!}
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
		$("#empresa").change(function () {
				var tercero = $(this).val();
				if(tercero != "0"){
					$.ajax({
            		type: "GET",
            		dataType: "json",
            		url: "/empresas/getagencias/"+tercero,
        			})
        			.done(function( data, textStatus, jqXHR ) {       
            			$('#agencia').empty();
						if($.isEmptyObject(data)){
							$('#agencia').append('<option value="0">Sin agencias</option>');
						}else{
							for (let i in data) {
                				$('#agencia').append('<option value="' + data[i].CODIGO + '">' + data[i].NOMBRE + '</option>');
            				}
						}         
        			})
        			.fail(function( jqXHR, textStatus, errorThrown ) {
            			alert("Error recuperando las agencias");
        			});
				}else{
					$("#agencia").empty();	
				}     		
		});
        		
    		
	</script>
@endsection
