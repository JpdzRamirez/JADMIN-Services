@extends('layouts.logeado')

@section('sub_title', 'Pasajeros Avianca')

@section('sub_content')
	<div class="card">
		<div class="card-body">
                <div class="align-center" style="display: inline">
                    <a href="{{route('pasajeros.nuevo')}}" class="btn btn-dark btn-sm">Nuevo Pasajero</a>
                </div>
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Identificación</th>
							<th>Nombre</th>
                            <th>Tipo</th>
							<th>Centro de costo</th>
						</tr>
						<tr>
							<th><form method="GET" class="form-inline" action="/pasajeros/buscar"><input type="number" value="{{$identificacion}}" name="identificacion" class="form-control" required></form></th>
                            <th></th>
                            <th></th>
                            <th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($usuariosav as $avuser)
							<tr>
								<td>{{ $avuser->identificacion }}</td>
								<td>{{ $avuser->nombres }} {{ $avuser->apellidos }}</td>
                                <td>{{ $avuser->tipo }}</td>         
								<td>{{ $avuser->centrocosto }}</td>
								<td>
                                    <a href="/pasajeros/{{$avuser->id}}/editar" class="btn btn-warning btn-sm">Actualizar</a>								
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
                @if(method_exists($usuariosav,'links'))
					{{ $usuariosav->links() }}
				@endif	
			</div>
		</div>
	</div>
@endsection