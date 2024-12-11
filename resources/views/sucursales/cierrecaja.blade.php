@extends('layouts.logeado')

@section('sub_title', 'Cierre de Caja')

@section('sub_content')
	<div class="card">
		<div class="card-body">

			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:10px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
            @endif

            @if($errors->first('bien') != null)
				<div class="alert alert-success" style="margin:10px 0">
					<h6>{{$errors->first('bien')}}</h6>
				</div>				
            @endif
            
            <div class="table-responsive" id="listar">
				<table class="table table-bordered" id="tab_listar">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Transacción</th>
							<th>Valor</th>
						</tr>
					</thead>
					<tbody>
						@forelse($transacciones as $transaccion)
							<tr>
								<td>{{ $transaccion->fecha }}</td>
                                <td>{{ $transaccion->tipo }}</td>
								<td>@if ($transaccion->tipo == "Egreso")
                                    $-{{number_format($transaccion->valor) }}</td>
                                @else
                                    ${{number_format($transaccion->valor) }}</td>
                                @endif
                                   
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				{{ $transacciones->links() }}
            </div>
            <div class="text-center">
                    <h4 style="color: red">Totales</h4>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Recargas: ${{number_format($caja->totalrecargas)}} <span class="badge badge-primary badge-pill">{{$recargas}}</span> </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Ventas: ${{number_format($caja->totalventas)}} <span class="badge badge-primary badge-pill">{{$ventas}}</span></li>
                    </ul>
                    <br>
                    <button type="button" class="btn btn-dark" class="form-control" onclick="confirmar();">Cerrar Caja</button>
                    
            </div>
            
            
		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="color: red">Confirmar Cierre de Caja</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
        <form action="/sucursales/cierrecaja" method="POST">
            <input type="hidden" name="caja" id="caja" value="{{$caja->id}}">
            <input type="hidden" name="sucursal" id="sucursal" value="{{$sucursal->id}}">
            <div class="modal-body">

                    <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Recargas: ${{number_format($caja->totalrecargas)}} <span class="badge badge-primary badge-pill">{{$recargas}}</span> </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Ventas: ${{number_format($caja->totalventas)}} <span class="badge badge-primary badge-pill">{{$ventas}}</span></li>
                    </ul>
                    <br>
                    <div class="row">
                        <label for="usuario" class="col-md-4 label-required">Quién realiza el cierre:</label>
                        <div class="col-md-8">
                            <input type="text" id="responsable" name="responsable" class="form-control" required>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <label for="usuario" class="col-md-4 label-required">Usuario:</label>
                        <div class="col-md-8">
                            <input type="text" id="usuario" name="usuario" class="form-control" required>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                            <label for="password" class="col-md-4 label-required">Contraseña:</label>
                            <div class="col-md-8">
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                    </div>
            </div>
            <div class="modal-footer">
                    <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Cerrar Caja</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
@section('script')
    <script>

        function confirmar(){
            $("#Modal").modal('show'); 
        }


    </script>
@endsection