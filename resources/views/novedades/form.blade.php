@extends('layouts.logeado')

@if($method == "post")
    @section('sub_title', 'Novedad en servicio: '. $servicio->id)
@else
    @section('sub_title', 'Atender novedad en servicio: '. $novedad->servicios_id)
@endif


@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($novedad, ['route' => $route, 'method' => $method] ) }}
                {{ Form::hidden('id', null) }}
                @if($method == "put")
                    {{ Form::hidden('servicios_id', $novedad->servicios_id) }}
                @else
                    {{ Form::hidden('servicios_id', $servicio->id) }}
                @endif

				<div class="form-group row {{ $errors->has('tipo') ? 'form-error': '' }}">
						{{ Form::label('tipo', 'Tipo de novedad', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-4">
                            <select name="tipo" id="tipo" class="form-control" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                                @endforeach
                            </select>
							{!! $errors->first('cuenta', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-dark open-modal" data-toggle="modal" data-target="#Modal"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuevo tipo</button>
                        </div>
				</div>
					<div class="form-group row {{ $errors->has('estado') ? 'form-error': '' }}">
							{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-4">
								<select name="estado" id="estado" class="form-control" required>
                                    <option value="Abierta">Abierta</option>
                                    <option value="Cerrada">Cerrada</option>
                                </select>
								{!! $errors->first('estado', '<p class="help-block">:message</p>') !!}
							</div>
                    </div>
                    <div class="form-group row {{ $errors->has('detalle') ? 'form-error': '' }}">
                            {{ Form::label('detalle', 'Detalle novedad', ['class' => 'label-required col-md-2']) }}
                            <div class="col-md-10">
                                @if($method == "post")
                                    <textarea name="detalle" id="detalle" cols="50" rows="5" class="form-control" required></textarea>
                                @else
                                    <textarea name="detalle" id="detalle" cols="50" rows="5" class="form-control" required>{{ $novedad->detalle }}</textarea>
                                @endif
                                {!! $errors->first('detalle', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div>
                    <div class="form-group row {{ $errors->has('solucion') ? 'form-error': '' }}">
                            {{ Form::label('solucion', 'Solución/Gestión', ['class' => 'col-md-2']) }}
                            <div class="col-md-10">
                                @if($method == "post")
                                    <textarea name="solucion" id="solucion" cols="50" rows="5" class="form-control"></textarea>
                                @else
                                    <textarea name="solucion" id="solucion" cols="50" rows="5" class="form-control">{{ $novedad->solucion }}</textarea>
                                @endif
                                {!! $errors->first('solucion', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div>
				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection
@section('modal')
	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Nuevo Tipo de Novedad</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="/novedades/nueva" method="POST">
					<div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="nombre" class="label-required">Descripción</label>                         
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="nombre" id="nombre" class="form-control" required>	
                            </div>
                        </div>					
					</div>
					<div class="modal-footer">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        @if($method == "post")
                            <input type="hidden" name="servicios_id" value="{{$servicio->id}}">
                        @else
                            <input type="hidden" name="servicios_id" value="{{$novedad->servicios_idd}}">
                        @endif
						<button type="submit" class="btn btn-success">Continuar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
			</div>
		</div>
    </div>
	</div>
@endsection
@section('script')
		@if ($method == 'put')
		<script>
		$(document).ready(function(){			
				$('#tipo option[value="{{$novedad->tiposnovedad_id}}"]').attr("selected", "selected");
				$('#estado option[value="{{$novedad->estado}}"]').attr("selected", "selected");			 					
			});
		</script>
		@endif
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection