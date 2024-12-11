@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Cuentas Corriente de Afiliados. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Cuentas Corriente de Afiliados')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @isset($filtro)
                <input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
            @endisset

            @if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:10px 0">
                    <h6>{{$errors->first('sql')}}
                    </h6>
				</div>				
            @endif

            <div class="align-center" style="display: inline">
                <a href="/cuentas_afiliados/crearlas" class="btn btn-default">Actualizar cuentas</a>
                <button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
                <p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
            </div>
            
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>ID</th>
							<th>Identificación</th>
                            <th>Nombre</th>
                            <th>Último vale</th>
                            <th>Fecha último vale</th>
                            <th>Saldo cuenta</th>
                            <th>Saldo recargas</th>
                            <th>Estado</th>
                        </tr>
                        <tr>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="number" name="id" class="form-control" required></form></th>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="text" name="identificacion" class="form-control" required></form></th>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="text" name="nombre" class="form-control" required></form></th>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="number" name="vale" class="form-control" required></form></th>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="date" name="fechavale" class="form-control" onchange="this.form.submit()" required></form></th>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="number" name="saldovales" class="form-control" required></form></th>
                        <th><form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar"><input type="number" name="recargas" class="form-control" required></form></th>
                        <th>
                            <form method="GET" class="form-inline" action="/cuentas_afiliados/filtrar">
                            <select name="estado" class="form-control" onchange="this.form.submit()">
                                <option value="sinfiltro"></option>
                                <option value="Activo">Activa</option>
                                <option value="Inactivo">Inactiva</option>
                            </select>
                            </form>
                        </th>
                        </tr>
					</thead>
					<tbody>
                        @forelse($cuentas as $cuenta)
							<tr>
                                <td>{{ $cuenta->id}}</td>
                                <td>{{ $cuenta->conductor->NUMERO_IDENTIFICACION}}</td>
                                <td>{{ $cuenta->conductor->NOMBRE}}</td>
                                @php
                                    $tottr = count($cuenta->transacciones);
                                    if($tottr > 0){
                                        //$cuenta->transacciones = $cuenta->transacciones->reverse();
                                        echo "<td>$" . number_format($cuenta->transacciones[$tottr-1]->valor) . "</td>";
                                        echo "<td>" . $cuenta->transacciones[$tottr-1]->fecha . "</td>";
                                    }else{
                                        echo "<td></td>";
                                        echo "<td></td>";
                                    }
                                @endphp                                
								<td>${{ number_format($cuenta->saldovales) }}</td>
								<td>${{ number_format($cuenta->saldo) }}</td>
								<td>@if ($cuenta->estado == "Inactiva")
                                        Inactiva
                                    @else
                                        Activa
                                    @endif
                                </td>
								<td>
                                    @if ($usuario->roles_id == 1 || $usuario->modulos[6]->pivot->editar == 1)
                                        <button type="button" value="/transacciones/nueva/{{$cuenta->id}}_{{$cuenta->conductor->NUMERO_IDENTIFICACION}}" class="btn btn-warning btn-sm open-modal" data-toggle="modal" data-target="#Modal" ><i class="fa fa-plus-circle" aria-hidden="true"></i> Recarga</button>
                                    @endif
                                    @if ($usuario->roles_id == 1)
                                        <button type="button" style="margin-top:1px" value="/transacciones/editarsaldo/{{$cuenta->id}}_{{$cuenta->conductor->NUMERO_IDENTIFICACION}}" class="btn btn-primary btn-sm open-modal" data-toggle="modal" data-target="#Modaltrans" ><i class="fa fa-money" aria-hidden="true"></i> Transacción</button>
                                    @endif
                                    <a href="/historial/transacciones/{{$cuenta->id}}" target="_blank" class="btn btn-success btn-sm" style="margin-top:1px">Historial</a>
                                    <a href="/vales/{{$cuenta->id}}?orden=DESC" class="btn btn-info btn-sm" style="margin-top:1px">Vales</a>   
         
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($cuentas,'links'))
					{{ $cuentas->links() }}
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
						<h4 class="modal-title">Nueva recarga</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form id="formrecarga" action="/transacciones/nueva" method="POST">
                    <div class="modal-body" style="min-height: 200px">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="identificacion" class="label-required">Identificación</label>                         
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="identificacion" id="identificacion" class="form-control" readonly required>	
                            </div>
                        </div>
                        <br>
                        <div class="row">
                                <div class="col-md-4">
                                    <label for="tiporecarga" class="label-required">Tipo</label>                         
                                </div>
                                <div class="col-md-8">
                                    <select name="tiporecarga" id="tiporecarga" class="form-control" required>
                                        <option value="Ingreso">Ingreso</option>
                                        @if(Auth::user()->roles_id == 1)
                                            <option value="Cortesía">Cortesía</option>
                                        @endif
                                        <option value="Egreso">Egreso</option>
                                    </select>
                                </div>
                        </div>
                        <br>
                        <div class="row">
                                <div class="col-md-4">
                                    <label for="valor" class="label-required">Valor</label>                         
                                </div>
                                <div class="col-md-8">
                                    <input type="number" name="valor" id="valor" class="form-control" required>	
                                </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="Comentarios" class="label">Comentarios</label>                         
                            </div>
                            <div class="col-md-8">
                                <textarea name="comentarios" id="comentarios" class="form-control" cols="10" rows="3"></textarea>
                            </div>
                    </div>

					</div>
					<div class="modal-footer">
                        <input type="hidden" name="tipo" value="Recarga">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Recargar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
			</div>
		</div>
    </div>
    
    <div id="Modaltrans" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
			<div class="modal-dialog" style="min-width: 50%">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Realizar transacción</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form id="formtrans" action="/transacciones/editar_saldo" method="POST">
                    <div class="modal-body" style="min-height: 200px">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="identificaciontr" class="label-required">Identificación</label>                         
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="identificaciontr" id="identificaciontr" class="form-control" readonly required>	
                            </div>
                        </div>
                        <br>
                        <div class="row">
                                <div class="col-md-4">
                                    <label for="tipotrans" class="label-required">Cuenta</label>                         
                                </div>
                                <div class="col-md-8">
                                    <select name="tipotrans" id="tipotrans" class="form-control" required>
                                        <option value="Ingreso">Ingreso</option>
                                        <option value="Egreso">Egreso</option>
                                    </select>
                                </div>
                        </div>
                        <br>
                        <div class="row">
                                <div class="col-md-4">
                                    <label for="valortr" class="label-required">Valor</label>                         
                                </div>
                                <div class="col-md-8">
                                    <input type="number" min="0" name="valortr" id="valortr" class="form-control" required>	
                                </div>
                        </div>
                        <br>
                        <div class="row">
                                <div class="col-md-4">
                                    <label for="comentariostr" class="label-required">Comentarios</label>                         
                                </div>
                                <div class="col-md-8">
                                    <textarea name="comentariostr" id="comentariostr" rows="5" class="form-control"></textarea>
                                </div>
                        </div>
					</div>
					<div class="modal-footer">
                        <input type="hidden" name="tipo" value="edicion">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Registrar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
			</div>
		</div>
    </div>
@endsection
@section('script')
    <script>
        $(document).on('click', '.btn-warning', function(e){
            var cuenta = e.target.value.split("_");
			$('#formrecarga').attr('action', cuenta[0]);
            $("#identificacion").val(cuenta[1]);

		});
		
		$(document).on('click', '.btn-primary', function(e){
            var cuenta2 = e.target.value.split("_");
			$('#formtrans').attr('action', cuenta2[0]);
            $("#identificaciontr").val(cuenta2[1]);

		});
		
		$("#tiporecarga").change(function(){
		    if($(this).val() == "Ingreso"){
		        $("#valor").attr("min", "13600");
		    }else{
		        $("#valor").attr("min", "0");
		    }
		});


        function toexcel(){

            Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

            $.ajax({
                method: "GET",
                url: "/cuentas_afiliados/exportar",
                data: { 'filtro': $("#filtro").val()}
            })
            .done(function (data, textStatus, jqXHR) {
                const byteCharacters = atob(data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);

                var csvFile;
                var downloadLink;

                filename = "Cuentas_Afiliados.xlsx";
                csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();

                Swal.close()		
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
        }
    </script>
@endsection