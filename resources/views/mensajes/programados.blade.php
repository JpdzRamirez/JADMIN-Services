@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Mensajes programados. Filtro: ' . $filtro)
@else
	@section('sub_title', 'Mensajes programados')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>ID</th>
							<th>Mensaje</th>
							<th>Duración</th>
                            <th>Intervalo (Minutos)</th>
                            <th>Estado</th>
						</tr>
					</thead>
					<tbody>
						@forelse($smsprogramados as $sms)
							<tr>
								<td>{{ $sms->id }}</td>
								<td>{{ $sms->mensaje }}</td>
								<td>{{ $sms->duracion }}</td>
                                <td>{{ $sms->intervalo }}</td>
                                <td>
                                    @if ($sms->estado == "1")
                                        Activo
                                    @else
                                        Inactivo
                                    @endif
                                </td>
                                <td><button class="btn btn-warning btn-sm">Editar</button>
                                    @if ($sms->estado == "1")
                                        <a href="/mensajes/programados/{{$sms->id}}/inactivar" class="btn btn-danger btn-sm">Inactivar</a>
                                    @else
                                        <a href="/mensajes/programados/{{$sms->id}}/activar" class="btn btn-success btn-sm">Activar</a>
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
				@if (method_exists($smsprogramados, 'links'))
					{{ $smsprogramados->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Mensaje</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formsms" action="/mensajes/programados/editar" method="POST">
            <div class="modal-body" style="min-height: 200px">
                <div class="row">
                    <div class="col-md-4">
                        <label for="mensaje" class="label-required">Mensaje</label>                         
                    </div>
                    <div class="col-md-8">
                        <textarea name="mensaje" id="mensaje" class="form-control" rows="5" required readonly></textarea>	
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="duracion" class="label-required">Duración</label>                         
                    </div>
                    <div class="col-md-8">
                        <input type="datetime-local" name="duracion" id="duracion" class="form-control" required>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="intervalo" class="label-required">Intervalo (Minutos)</label>                         
                    </div>
                    <div class="col-md-8">
                        <input type="number" name="intervalo" id="intervalo" class="form-control" required>	
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="idsms" id="idsms">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <button type="submit" class="btn btn-success">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
            </form>
    </div>
</div>
</div>
@endsection
@section('script')
    <script>
        $(document).on("click", ".btn-warning", function (e) {
            let sms = $(this).closest('td').siblings();
            $("#mensaje").val(sms[1].innerText);
            $("#duracion").val(sms[2].innerText.replace(" ", "T").substring(0, 16));
            $("#intervalo").val(sms[3].innerText);
            $("#idsms").val(sms[0].innerText);

            $("#Modal").modal("show");
        });
    </script>
@endsection