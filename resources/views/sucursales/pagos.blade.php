@extends('layouts.logeado')

@section('sub_title', 'Nuevo Pago Electrónico')

@section('sub_content')
	<div class="card">
		<div class="card-body">

			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:10px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
            @endif

            @if($errors->first('bien') != null)
				<div class="alert alert-success" style="margin:10px 0">
					<h6>{{$errors->first('bien')}}</h6>
				</div>				
            @endif
            
            {{ Form::model($transaccion, ['id' => 'pagomovil', 'route' => $route, 'method' => $method] ) }}
                {{ Form::hidden('id', null) }}
                <input type="hidden" name="password" id="password">
				<div class="form-group row {{ $errors->has('identificacion') ? 'form-error': '' }}">
					{{ Form::label('identificacion', 'Identificación', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('identificacion', null, ['required', 'class' => 'form-control']) }}
						{!! $errors->first('identificacion', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div class="form-group row {{ $errors->has('venta') ? 'form-error': '' }}">
						{{ Form::label('venta', 'Venta', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
                            {{ Form::text('venta', "Combustible", ['required', 'readonly', 'class' => 'form-control']) }}
						</div>
                </div>
 
                <div class="form-group row {{ $errors->has('valor') ? 'form-error': '' }}">
                        {{ Form::label('valor', 'Valor', ['class' => 'label-required col-md-2']) }}
                        <div class="col-md-10">
                            {{ Form::number('valor', null, ['required', 'min' => '0', 'class' => 'form-control']) }}
                            {!! $errors->first('valor', '<p class="help-block">:message</p>') !!}
                        </div>
                </div>
				<div class="form-group text-center">
					<button class="btn btn-dark open-modal" type="button" onclick="confirmar();">Pagar</button>
				</div>
			{{ Form::close() }}
			
		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar venta</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <label for="documento" class="col-md-4">Identificación:</label>
                    <div class="col-md-8">
                        <p id="documento"></p>
                    </div>
                </div>
                <div class="row">
                    <label for="txtventa" class="col-md-4">Venta</label>
                    <div class="col-md-8">
                        <p id="txtventa"></p>
                    </div>
                </div>
                <div class="row">
                    <label for="txtvalor" class="col-md-4">Valor:</label>
                    <div class="col-md-8">
                        <p id="txtvalor"></p>
                    </div>
                </div>
                <div class="row">
                    <label for="txtclave" class="col-md-4">Contraseña:</label>
                    <div class="col-md-8">
                        <input type="password" id="txtclave" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnpago" class="btn btn-success" onclick="vender();">Realizar pago</button>
            </div>
            
    </div>
</div>
</div>
@endsection
@section('script')
    <script>

        function confirmar(){

            var ide,tipo,valor;

            ide = $("#identificacion").val();
            venta = $("#venta").val();
            valor = $("#valor").val();

            if(ide != "" && venta != "" && valor != ""){
                $("#documento").text(ide);
                $("#txtventa").text(venta);
                $("#txtvalor").text( '$' + parseInt(valor).toLocaleString());

                $("#Modal").modal('show');
            }else{
                alert("Diligencie todos los campos para recargar");
            }          
        }

        var intentos = 0;

        function vender(){
            var clave = $("#txtclave").val();
            if(clave != ""){
                $("#password").val(clave);
                if(intentos == 0){
                    intentos++;
                    $("#btnpago").attr("disabled", true);
                    $("#pagomovil").submit();
                }             
            }else{
                alert("Ingrese la contraseña del conductor");
            }         
        }
    </script>
@endsection