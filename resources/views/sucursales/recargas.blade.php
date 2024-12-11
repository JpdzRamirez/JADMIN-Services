@extends('layouts.logeado')

@section('sub_title', 'Nueva recarga')

@section('sub_content')
	<div class="card">
		<div class="card-body">

			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:10px 0">
                    <h6>{{$errors->first('sql')}}
                    </h6>
				</div>				
            @endif

            @if($errors->first('bien') != null)
				<div class="alert alert-success" style="margin:10px 0">
                    <h6>{{$errors->first('bien')}}</h6>
                    
				</div>				
            @endif
            
            {{ Form::model($transaccion, ['id' => 'recargamovil', 'route' => $route, 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}
				<div class="form-group row {{ $errors->has('identificacion') ? 'form-error': '' }}">
					{{ Form::label('identificacion', 'Identificación', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('identificacion', null, ['required', 'class' => 'form-control']) }}
						{!! $errors->first('identificacion', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div class="form-group row {{ $errors->has('tiporecarga') ? 'form-error': '' }}">
						{{ Form::label('tiporecarga', 'Tipo recarga', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<select name="tiporecarga" id="tiporecarga" class="form-control">
                                <option value="Ingreso">Ingreso</option>
                                <option value="Egreso">Egreso</option>
                            </select>
						</div>
                </div>
                <div class="form-group row {{ $errors->has('valor') ? 'form-error': '' }}">
                        {{ Form::label('valor', 'Valor', ['class' => 'label-required col-md-2']) }}
                        <div class="col-md-10">
                            {{ Form::number('valor', null, ['required', 'min' => '13600', 'class' => 'form-control']) }}
                            {!! $errors->first('valor', '<p class="help-block">:message</p>') !!}
                        </div>
                </div>
				<div class="form-group text-center">
					<button class="btn btn-dark open-modal" type="button"  onclick="confirmar();">Recargar</button>
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
                <h4 class="modal-title">Confirmar recarga</h4>
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
                    <label for="txttipo" class="col-md-4">Tipo recarga:</label>
                    <div class="col-md-8">
                        <p id="txttipo"></p>
                    </div>
                </div>
                <div class="row">
                    <label for="txtvalor" class="col-md-4">Valor:</label>
                    <div class="col-md-8">
                        <p id="txtvalor"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnrecarga" class="btn btn-success"  onclick="recargar();">Efectuar recarga</button>
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
            tipo = $("#tiporecarga").val();
            valor = $("#valor").val();

            if(ide != "" && tipo != ""){
                $("#documento").text(ide);
                $("#txttipo").text(tipo);

                if(valor >= 13600){
                    $("#txtvalor").text( '$' + parseInt(valor).toLocaleString());
                    $("#Modal").modal('show');
                }else{
                    Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las recargas deben ser mínimo de $13600'
                });
                }              
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Diligencie todos los campos para recargar'
                });
            }          
        }

        var intentos = 0;

        function recargar(){
            if(intentos == 0){
                intentos++;
                $("#btnrecarga").attr("disabled", true);
                $("#recargamovil").submit();
            }          
        }

    </script>
@endsection