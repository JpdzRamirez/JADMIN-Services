@extends('layouts.logeado')

@if ($method == "post")
    @section('sub_title', 'Nuevo pasajero')
@else
    @section('sub_title', 'Actualizar pasajero: ' . $pasajero->identificacion)
@endif

@section('sub_content')
<div class="card">
	<div class="card-body" id="cardb">
        @if($errors->first('sql') != null)
            <div class="alert alert-danger" style="margin:5px 0">
                <h6>{{$errors->first('sql')}}</h6>
            </div>				
        @endif
		{{ Form::model($pasajero, ['url' => $route, 'method' => $method ] ) }}
            {{ Form::hidden('id', null) }}
            <div class="form-group row">
                {{ Form::label('identificacion', 'Identificación', ['class' => 'col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    @if ($method == "post")
                    {{ Form::text('identificacion', null, ['class' => 'form-control ' . ($errors->has('identificacion') ? 'is-invalid' : ''), 'placeholder' => 'Identificación']) }}
                    @else
                    {{ Form::text('identificacion', null, ['readonly', 'class' => 'form-control ' . ($errors->has('identificacion') ? 'is-invalid' : '')]) }}
                    @endif
                    @error('identificacion')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('nombre', 'Nombres', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    @if ($method == "post")
                    {{ Form::text('nombre', null, ['required', 'class' => 'form-control ' . ($errors->has('nombre') ? 'is-invalid' : ''), 'placeholder' => 'Nombres']) }}
                    @else
                    {{ Form::text('nombre', null, ['readonly', 'class' => 'form-control ' . ($errors->has('nombre') ? 'is-invalid' : '')]) }} 
                    @endif
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
			</div>
            @if ($method == "post")
                <div class="form-group row">
                    {{ Form::label('apellidos', 'Apellidos', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::text('apellidos', null, ['required', 'class' => 'form-control ' . ($errors->has('apellidos') ? 'is-invalid' : ''), 'placeholder' => 'Apellidos']) }}
                        @error('apellidos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="form-group row">
                {{ Form::label('sub_cuenta', 'Sub_Cuenta', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('sub_cuenta', null, ['required', 'class' => 'form-control ' . ($errors->has('sub_cuenta') ? 'is-invalid' : ''),'placeholder' => 'Sub_Cuenta']) }}
                    @error('sub_cuenta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('affe', 'Affe', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('affe', null, ['required', 'class' => 'form-control ' . ($errors->has('affe') ? 'is-invalid' : ''),'placeholder' => 'Affe']) }}
                    @error('affe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror 
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('solicitado', 'Solicitado', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('solicitado', null, ['required', 'class' => 'form-control ' . ($errors->has('solicitado') ? 'is-invalid' : ''),'placeholder' => 'Solicitado']) }}
                    @error('solicitado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror 
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('autorizado', 'Autorizado', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('autorizado', null, ['required', 'class' => 'form-control ' . ($errors->has('autorizado') ? 'is-invalid' : ''),'placeholder' => 'Autorizado']) }}
                    @error('autorizado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror  
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('celulares', 'Celular', ['class' => 'col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('celulares', null, [ 'class' => 'form-control ' . ($errors->has('celulares') ? 'is-invalid' : ''), 'placeholder' => 'Celulares']) }}
                    @error('celulares')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror 
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('direccion', 'Dirección', ['class' => ' col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('direccion', null, [ 'class' => 'form-control ' . ($errors->has('direccion') ? 'is-invalid' : ''), 'placeholder' => 'Dirección']) }}
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror 
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('barrio', 'Barrio', ['class' => 'col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {{ Form::text('barrio', null, [ 'class' => 'form-control ' . ($errors->has('barrio') ? 'is-invalid' : ''), 'placeholder' => 'Dirección Complementaria']) }}
                    @error('barrio')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror 
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('municipio', 'Municipio', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                    {!! Form::select("municipio", [
                        "BUCARAMANGA" => "BUCARAMANGA", 
                        "FLORIDABLANCA" => "FLORIDABLANCA", 
                        "GIRÓN" => "GIRÓN", 
                        "PIEDECUESTA" => "PIEDECUESTA", 
                        "LEBRIJA" => "LEBRIJA"
                        ], 
                        $pasajero->municipio,
                        ['class' => 'form-control ' . ($errors->has('municipio') ? 'is-invalid' : '')]
                    ) !!}
                    @error('municipio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group text-center">
                {!! Form::button('Enviar', ['type' => 'submit','class' => 'btn btn-dark pasajero-update']) !!}
            </div>
		{{ Form::close() }}
	</div>
</div>
@endsection


