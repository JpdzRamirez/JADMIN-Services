@extends('layouts.layoutchat')

@section('sub_title', 'Restablecimiento de contraseña')

@section('sub_content')
	<div class="card">
		<div class="card-body">
		    
		    @if($errors->first('sql') != null)
				<div class="alert alert-info" style="margin:10px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
            @endif
            
            @if($errors->first('sql') == null || $errors->first('sql') == "Las contraseñas no coinciden")
                <form method="POST" action="/clientes/newpass">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="clienteid" value="{{ $cliente->id }}">

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">Email</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ $cliente->email }}" readonly required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Nueva contraseña</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Confirmar contraseña</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password-confirm" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Restablecer contraseña
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
		</div>
	</div>
@endsection