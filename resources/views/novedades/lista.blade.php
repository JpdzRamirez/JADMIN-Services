@extends('layouts.logeado')

@section('sub_title', 'Novedades del servicio: '. $servicio->id)

@section('sub_content')
	<div class="card">
		<div class="card-body">
				@if ($usuario->roles_id == 1)
				<div class="align-center">
						<a href="{{route('novedades.nuevo', ['servicio' => $servicio->id])}}" class="btn btn-dark btn-sm">Agregar novedad</a>
					</div>
			@else
				@if ($usuario->modulos[0]->pivot->editar == 1)
					<div class="align-center">
						<a href="{{route('novedades.nuevo' , ['servicio' => $servicio->id])}}" class="btn btn-dark btn-sm">Agregar novedad</a>
					</div>
				@endif
			@endif
					
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Tipo de novedad</th>
							<th>Detalle</th>
							<th>Solución</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
						@forelse($novedades as $novedad)
							<tr>
								<td>{{ $novedad->tiponovedad->nombre }}</td>
                                <td>{{ $novedad->detalle }}</td>
								<td>{{ $novedad->solucion }}</td>
								<td>{{ $novedad->estado }}</td>
								<td><a href="/novedades/{{$novedad->id}}/editar" class="btn btn-warning btn-sm">Actualizar</a></td>
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
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection