@extends('layouts.logeado')

@section('sub_title', 'Logs ICON')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Parametros</th>
                            <th>Código</th>
                            <th>Mensaje</th>                            
						</tr>
					</thead>
					<tbody>
						@forelse($logs as $log)
							<tr>
								<td>{{ $log->parametros }}</td>
                                <td>{{ $log->codigo }}</td>
								<td>{{ $log->mensaje }}</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="3">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($logs,'links'))
					{{ $logs->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection