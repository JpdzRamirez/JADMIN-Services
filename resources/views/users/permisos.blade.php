@extends('layouts.logeado')

@section('sub_title', 'Permisos del usuario: '. $user->usuario)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($user, ['route' => $route, 'method' => $method] ) }}
                {{ Form::hidden('id', null) }}
                <div class="form-group row" style="text-align: center">
					<div class="col-md-2" style="border: 1px solid black">
                        <label class="label">Módulo</label>
                    </div>
					<div class="col-md-2" style="border: 1px solid black">
						<label class="label">Ver</label> 
                    </div>
                    <div class="col-md-2" style="border: 1px solid black">
                        <label class="label">Editar</label>  
                    </div>
                </div>
                @foreach ($user->modulos as $modulo)
                    <div class="form-group row" style="text-align: center">
                    <div class="col-md-2">{{$modulo->nombre}}</div>
					<div class="col-md-2">
                        @if ($modulo->pivot->ver == 1)
                            <input type="checkbox" id="{{$modulo->id}}v" name="{{$modulo->id}}[]" style="transform: scale(1.5)" checked>
                        @else
                            <input type="checkbox" id="{{$modulo->id}}v" name="{{$modulo->id}}[]" style="transform: scale(1.5)">
                        @endif				
                    </div>
                    <div class="col-md-2">
                        @if ($modulo->pivot->editar == 1)
                            <input type="checkbox" id="{{$modulo->id}}e" name="{{$modulo->id}}[]" style="transform: scale(1.5)" checked> 
                        @else
                            <input type="checkbox" id="{{$modulo->id}}e" name="{{$modulo->id}}[]" style="transform: scale(1.5)"> 
                        @endif
                    </div>
                </div>
                @endforeach
			
				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection

@section('script')
	<script>

        $("input[type=checkbox]").change(function (e) {

            var ide = e.target.id;
            var modu = ide.substr(0,ide.length-1);
            if(ide.substr(-1) == 'e'){
                $("#"+modu+"v").prop("checked", true);
            }else{
                $("#"+modu+"e").prop("checked", false);
            }           
        });

	</script>
@endsection