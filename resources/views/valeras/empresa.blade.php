@extends('layouts.logeado')

@section('sub_title', 'Valeras')

@section('sub_content')
	<div class="card">
		<div class="card-body">

			@if($errors->first('falla') != null)
			<div class="alert alert-danger" style="margin:5px 0">
				<h6>{{$errors->first('falla')}}</h6>
			</div>				
			@endif

			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Agencia</th>
							<th>Nombre valera</th>
                            <th>Fecha creación</th>
                            <th>Límite inferior</th>
							<th>Límite superior</th>
							<th>Vigencia</th>
                            <th>Estado</th>
						</tr>
					</thead>
					<tbody>
						@forelse($valeras as $valera)
							<tr>
								<td>@if (Auth::user()->id == 119)
										Provisional administrativo								
									@else
										{{ $valera->cuentae->agencia->NOMBRE}}
									@endif
								</td>
								<td>{{ $valera->nombre }}</td>
								<td>{{ $valera->fecha }}</td>
                                <td>{{ $valera->inferior }}</td>
								<td>{{ $valera->superior }}</td>
								<td>{{ $valera->inicio}} / {{ $valera->fin}}</td>
                                <td>@if ($valera->estado == 1)
                                        Activa
                                    @else
                                        Inactiva
                                    @endif
								</td>
								<td>
									@if ($valera->id == 60)
									<a href="/valera/avianca/vales" class="btn btn-success btn-sm">Vales</a>
									@else
										<a href="{{ route('valeras.vales', ['valera' => $valera->id]) }}" class="btn btn-success btn-sm">Vales</a>
										@if ($valera->estado == 1)
											<a href="{{ route('valeras.asignar', ['valera' => $valera->id]) }}" class="btn btn-info btn-sm">Asignar vale</a>
										@endif
										@if (Auth::user()->roles_id == 5)
											<button type="button" onclick="ampliarValera({{$valera->id}}, {{$valera->inferior}}, {{$valera->superior}});" class="btn btn-sm btn-warning">Ampliar</button>
										@endif
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
						<h4 class="modal-title">Ampliar Valera</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="/terceros/valeras/ampliar" method="POST">
						<div class="modal-body">
							<div class="row form-group">
								<div class="col-md-3">
									<label for="Inicio" class="label-required">Inicio</label>                         
								</div>
								<div class="col-md-9">
									<input type="number" name="inicio" id="inicio" class="form-control" readonly>	
								</div>
							</div>	
							<div class="row form-group">
								<div class="col-md-3">
									<label for="Fin" class="label-required">Fin</label>                         
								</div>
								<div class="col-md-9">
									<input type="number" step="1" name="fin" id="fin" class="form-control" required>	
								</div>
							</div>					
						</div>
						<div class="modal-footer">
							<input type="hidden" name="_token" value="{{csrf_token()}}">
							<input type="hidden" name="idValera" id="idValera">
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
		function ampliarValera(valera, inicio, fin){
			$("#idValera").val(valera);
			$("#inicio").val(inicio);
			$("#fin").attr("min", fin);
			$("#fin").val(fin);

			$("#Modal").modal("show");
		}
	</script>
@endsection