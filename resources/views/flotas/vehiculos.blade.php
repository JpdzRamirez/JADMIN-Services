@extends('layouts.logeado')

@section('sub_title', 'Vehiculos en la Flota ' . $flota->descripcion)

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<div class="align-center" style="display: inline">
					@if ($usuario->roles_id == 1 || $usuario->modulos[3]->pivot->editar == 1)
						<a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Agregar vehiculos</a>
						@if ($flota->id == "1" || $flota->id == "2")
							<a href="/flotas/actualizar/{{$flota->id}}" class="btn btn-dark btn-sm">Actualizar flota</a>
						@endif
					@endif
				</div>
				@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>Las siguientes placas no pudieron ser agregadas: {{$errors->first('sql')}}. Ya se encuentran en la flota o la placa no existe.</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Placa</th>
							<th>Marca</th>
							<th>Modelo</th>
							<th>Propietario</th>
						</tr>
					</thead>
					<tbody>
						@forelse($vehiculos as $vehiculo)
							<tr>
								<td>{{ $vehiculo->PLACA }}</td>
								<td>{{ $vehiculo->marca->DESCRIPCION }}</td>
								<td>{{ $vehiculo->MODELO}}</td>
								<td>{{ $vehiculo->propietario->tercero->PRIMER_NOMBRE }} {{ $vehiculo->propietario->tercero->PRIMER_APELLIDO }}</td>
								<td><a href="/flotas/{{$flota->id}}/remover/{{$vehiculo->VEHICULO}}" class="btn btn-sm btn-danger">Remover</a></td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($vehiculos,'links'))
					{{ $vehiculos->links() }}
				@endif
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
			<div class="modal-dialog"  style="min-width: 50%">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Agregar vehiculos</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="/flotas/vehiculos/agregar" method="POST">
					<div class="modal-body">
                            <div class="row">
                                    <div class="col-md-3">
                                        <label for="vehiculo" class="label-required">Vehiculos para agregar</label>                         
                                    </div>
                                    <div class="col-md-9">
                                        <select name="vehiculo" id="vehiculo" class="form-control">
                                            @foreach ($novehiculos as $noveh)
                                                <option value="{{$noveh->PLACA}}">{{$noveh->PLACA}}</option>
                                            @endforeach
                                        </select>                                    
                                    </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-dark" onclick="addplaca();" style="float: right"><i class="fa fa-plus" aria-hidden="true"></i> Agregar</button>
                            <br>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-md-3">
                                <label for="placas" class="label-required">Placas</label>                         
                            </div>
                            <div class="col-md-9">
                                <textarea name="placas" id="placas" class="form-control" cols="30" rows="5" placeholder="Escribir placas separadas por coma"></textarea>	
                            </div>
                        </div>					
					</div>
					<div class="modal-footer">
						<input type="hidden" name="flota" value="{{$flota->id}}">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Guardar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
			</div>
		</div>
    </div>
	</div>
@endsection
@section('script')
	<script>

        function addplaca() {
            var placas = $("#placas").val();
            if(placas == ""){
                placas = $("#vehiculo").val();
            }else{
                placas = placas + "," + $("#vehiculo").val();
            }         
            $("#placas").val(placas);
            
        }
	</script>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection