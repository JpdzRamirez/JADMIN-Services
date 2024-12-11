@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Valeras físicas. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Valeras físicas')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
				@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
				@endif

			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Agencia</th>
							<th>Conductores bloqueados</th>
						</tr>
						<tr>
							<form  method="GET" class="form-inline" id="formfiltrar" action="/valeras_fisicas/filtrar"></form>
							<th>
								@if (isset($filtro))
									<input class="form-control" value="{{$filtro[1]}}" type="text" name="agencia" form="formfiltrar" id="agencia"/>
								@else
									<input class="form-control" type="text" name="agencia" form="formfiltrar" id="agencia"/>
								@endif
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($valeras as $valera)
							@if ($valera->cuentae->agencia != null)
								<tr>
									<td>{{ $valera->cuentae->agencia->NOMBRE }}</td>
									<td>{{ count($valera->bloqueados) }}</td>
									<td>
										@if ($usuario->roles_id == 1 || $usuario->modulos[5]->pivot->editar == 1)
											<a href="/valeras_fisicas/{{$valera->id}}/listanegra" class="btn btn-sm" style="background-color: gray; color: white">Lista negra</a>
										@endif									
									</td>
								</tr>
							@endif
						@empty
							<tr class="align-center">
								<td colspan="2">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($valeras,'links'))
					{{$valeras->links()}}
				@endif				
			</div>
		</div>
	</div>
@endsection