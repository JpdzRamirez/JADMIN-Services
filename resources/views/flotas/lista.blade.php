@extends('layouts.logeado')

@section('sub_title', 'Lista de flotas')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="align-center">
				@if ($usuario->roles_id == 1 || $usuario->modulos[3]->pivot->editar == 1)
					<a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Nueva flota</a>
				@endif
			</div>
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
							<th>Descripción</th>
							<th>Vehiculos</th>
						</tr>
					</thead>
					<tbody>
						@forelse($flotas as $flota)
							<tr>
								<td>{{ $flota->id }}</td>
								<td>{{ $flota->descripcion }}</td>
								<td>{{ count($flota->vehiculos)}}</td>
								<td>
									<a href="{{ route('flotas.vehiculos', ['flota' => $flota->id]) }}" class="btn btn-info btn-sm">Vehiculos</a>
									@if ($flota->id != 1 || $flota->id != 2)
										<a href="/flotas/{{$flota->id}}/borrar" class="btn btn-danger btn-sm">Borrar</a>
									@endif
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Nueva Flota</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="/flotas/nuevo" method="POST">
					<div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="descripcion" class="label-required">Descripción</label>                         
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="descripcion" id="descripcion" class="form-control" required>	
                            </div>
                        </div>					
					</div>
					<div class="modal-footer">
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
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
	@endsection