@extends('layouts.logeado')

@section('sub_title', 'Acuerdos de pago')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="align-center">
				@if ($usuario->roles_id == 1 || $usuario->modulos[10]->pivot->editar == 1)
					<a href="/acuerdos/nuevo" class="btn btn-dark btn-sm">Nuevo acuerdo</a>
				@endif
			</div>
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<form action="/acuerdos/filtrar" method="get" id="formacuerdos"></form>
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Fecha</th>
							<th>Propietario</th>
							<th>Placa</th>
							<th>Pago mensual</th>	
                            <th>Cuotas pagadas</th>
							<th>Cuotas vencidas</th>                  
                            <th>Saldo</th>
                            <th>Estado</th>
						</tr>
						<tr>
							<th></th>
							<th></th>
							<th><input type="text" name="propietario" id="propietario" class="form-control filt" form="formacuerdos"></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th><select name="estado" id="estado" class="form-control" form="formacuerdos" onchange="this.form.submit()">
									<option value=""></option>
									<option value="Vigente">Vigente</option>
									<option value="Pagado">Pagado</option>
									<option value="Proceso">Proceso</option>
								</select>
							</th>
						</tr>
					</thead>
					<tbody>
						@forelse($acuerdos as $acuerdo)
							@if ($acuerdo->vencidas > 1)
								<tr style="background-color: lightcoral">
							@elseif($acuerdo->vencidas == 1)
								<tr style="background-color: orange">
							@else
								<tr>
							@endif
								<td>{{ $acuerdo->id }}</td>
                                <td>{{ $acuerdo->fecha }}</td>
								<td>{{ $acuerdo->propietario->tercero->NRO_IDENTIFICACION }}</td>
								<td>{{ $acuerdo->placa }}</td>
								<td>${{ number_format($acuerdo->pago_mensual, 2, ",", ".") }}</td>
                                <td>{{ $acuerdo->pagadas }} / {{ $acuerdo->cuotas }}</td>   
								<td style="font-size: large;font-weight: 500;text-align: center;">{{ $acuerdo->vencidas }}</td>                  
								<td>${{ number_format($acuerdo->saldo, 2, ",", ".") }}</td>
                                <td>{{ $acuerdo->estado }}</td>
								<td style="background-color: white">
									<a href="/acuerdos/{{ $acuerdo->id }}/cuotas" class="btn btn-primary btn-sm">Cuotas</a><br>
									<button type="button" onclick="mostrarDatos('{{$acuerdo->propietario->tercero->NRO_IDENTIFICACION}}', '{{$acuerdo->propietario->tercero->RAZON_SOCIAL}}', '{{$acuerdo->celular}}', '{{$acuerdo->propietario->tercero->EMAIL}}');" class="btn btn-secondary btn-sm">Propietario</button>
									@if ($acuerdo->estado == "Vigente")
										<button type="button" class="btn btn-danger btn-sm" onclick="confirmarProceso({{$acuerdo->id}});">Proceso</button>
									@endif
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="8">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($acuerdos,'links'))
					{{ $acuerdos->links() }}
				@endif
		</div>
	</div>
@endsection
@section('modal')
	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
		<div class="modal-dialog" style="min-width: 45%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Datos Propietario</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<div class="row form-group">
						<label class="col-md-3">Cédula</label>                         
						<div id="cedula" class="form-control col-md-8"></div>	
					</div>	
					<div class="row form-group">
						<label class="col-md-3">Nombre</label>                         
						<div id="nombre" class="form-control col-md-8"></div>	
					</div>					
					<div class="row form-group">
						<label class="col-md-3">Celular</label>                         
						<div id="celular" class="form-control col-md-8"></div>	
					</div>
					<div class="row form-group">
						<label class="col-md-3">Email</label>                         
						<div id="email" class="form-control col-md-8"></div>	
					</div>
				</div>				
			</div>
		</div>
    </div>
@endsection
@section('script')
	<script>
		function mostrarDatos(cedula, nombre, celular, email) {
			$("#cedula").text(cedula);
			$("#nombre").text(nombre);
			$("#celular").text(celular);
			$("#email").text(email);
			$("#Modal").modal("show");
		}

		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formacuerdos").submit();
    		}
		});

		function confirmarProceso(acuerdo) {
			Swal.fire({
				title: 'Acuerdo de pago a proceso',
				text : '¿Está seguro de que el acuerdo de pago #' + acuerdo + ' inició proceso?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Confirmar',
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if(result.isConfirmed){
					 $(location).attr("href", "/acuerdos/iniciar_proceso/" + acuerdo);               
				}
			});
		}

		$(document).ready(function () {
			@if(isset($propietario))
			    $("#propietario").val("{{$propietario}}");
			@endif

			@if(isset($estado))
			    $("#estado").val("{{$estado}}");
			@endif
		});
	</script>
@endsection
