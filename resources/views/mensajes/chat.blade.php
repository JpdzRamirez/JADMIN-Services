@extends('layouts.layoutchat')

@section('sub_title', 'Chat con '. $cuentac->conductor->NOMBRE . '(' . $cuentac->placa . ')')

@section('sub_content')
	<div class="card">
		<div class="card-body">
                <div id="divchat" style="height: 300px; overflow-y: scroll;">
                    @foreach ($mensajes as $mensaje)
                        @if ($mensaje->sentido == "Recibido")
                            <div class="row">
                                <div class="col-md-6 chatcrm"><p class="txtcrm">{{$mensaje->texto}}. <b style="color: #134778">({{$mensaje->fecha}})</b></p></div>
                            </div>
                            <br>
                        @else
                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-6 chattaxi"><p class="txttaxi">{{$mensaje->texto}}. <b style="color: #134778">({{$mensaje->fecha}})</b></p></div>
                            </div>
                            <br>
                        @endif
                    @endforeach                          
                </div>
                <input type="text" name="texto" id="texto" placeholder="Mensaje" style="width: 70%; height: 50px">
                <button type="button" id="btntexto" class="btn btn-dark" onclick="enviar()" disabled>Enviar</button>
		</div>
	</div>
@endsection
@section('script')
    <script>

        $(document).ready(function () {
            var objDiv = document.getElementById("divchat");
            objDiv.scrollTop = objDiv.scrollHeight;

            var chat = setInterval(sincrochat, 3000);
        });

        $("#texto").keyup(function (event) {
           var sms = $(this).val();
           if(sms != ""){
                $("#btntexto").attr("disabled", false);
           }else{
                $("#btntexto").attr("disabled", true);
           }
        });

        function enviar(){

            $.ajax({
			type: "POST",
			data:{"texto":$("#texto").val(), "cuentasc_id": {{$cuentac->id}}, "_token": "{{csrf_token()}}"},
			dataType: "json",
			url: "/mensajes/chatcrm",
		})
			.done(function (data, textStatus, jqXHR) {
                var divchat = $("#divchat");
			    var linea = '<div class="chatcrm"><p class="txtcrm">' + data.texto + '</p></div><br><br>';
                divchat.append(linea);
                divchat.scrollTop(divchat.prop('scrollHeight'));
                $("#texto").val("");
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				alert("No se ha podido enviar el mensaje");
			});	

        }

        function sincrochat() {
            
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "/mensajes/pendientes/{{$cuentac->id}}",
            })
            .done(function (data, textStatus, jqXHR) {
                var divchat = $("#divchat");
                for (const key in data) {
                    var linea = '<div class="chattaxi"><p class="txttaxi">' + data[key].texto + '</p></div><br><br>';
                    divchat.append(linea);
                    divchat.scrollTop(divchat.prop('scrollHeight'));
                }		                 
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                
            });	
        }
    </script>
@endsection