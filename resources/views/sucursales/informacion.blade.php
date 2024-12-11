@extends('layouts.logeado')

@section('sub_title', 'Información Sucursal: '. $sucursal->user->nombres)

@section('sub_content')
	<div class="card" id="divinfo" style="filter: blur(4px)">
		<div class="card-body">

            <div class="text-center">
                    <h4 style="color: red">Datos de Caja</h4>
                    <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Recargas hoy: ${{number_format($caja->totalrecargas)}} </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Ventas hoy: ${{number_format($caja->totalventas)}} </li>
                    </ul>
                    <br>
                    <h4 style="color: red">Datos Sucursal</h4>
                    <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Saldo Recargas: ${{number_format($sucursal->saldorecargas)}} </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 14pt">Saldo Ventas: ${{number_format($sucursal->saldoventas)}} </li>
                    </ul>
            </div>
            
            
		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="min-width: 50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Consultar Información</h4>
            </div>
            <input type="hidden" name="caja" id="caja" value="{{$caja->id}}">
            <input type="hidden" name="sucursal" id="sucursal" value="{{$sucursal->id}}">
            <div class="modal-body">
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
                    <a href="javascript: history.go(-1)" class="btn btn-default">Cancelar</a>
                    <button type="button" class="btn btn-success" onclick="validarinfo();">Solicitar</button>
            </div>
    </div>
</div>
</div>
@endsection
@section('script')
    <script>

        $(document).ready(function () {
            $('#Modal').modal({backdrop: 'static', keyboard: false});
            $("#Modal").modal("show");
        });

        function validarinfo(){

            var usuario, clave;

            usuario = $("#usuario").val();
            clave = $("#password").val();

            if(usuario != "" && clave != ""){
                $.ajax({
				type: "GET",
				url: "/sucursales/validarinfo",
                data: {"usuario": usuario, "password": clave, "sucursal": {{$sucursal->id}}, "caja": {{$caja->id}}}
				})
				.done(function (data, textStatus, jqXHR) {
					if(data == "Correcto"){
                        $("#Modal").modal("hide");
                        $("#divinfo").removeAttr("style");
                    }else{
                        alert(data);
                    }
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					alert("Error recuperando datos de la base de datos");
				});
            }else{
                alert("Debe completar los campos usuario y contraseña");
            }         	
        }
    </script>
@endsection