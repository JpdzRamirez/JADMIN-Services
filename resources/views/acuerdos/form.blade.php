@extends('layouts.logeado')
@section('style')
<style>
    #Modal .form-control{
        font-size: x-large;
        font-weight: 500;
        height: auto;
        text-align: center;
    }
</style>
    
@endsection

@section('sub_title', 'Nuevo Acuerdo de Pago')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			
            <form action="/acuerdos/nuevo" method="POST" id="formacuerdo" accept-charset="UTF-8">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <input type="hidden" name="tercero" id="tercero">

                <div class="form-group row">
                    {{ Form::label('propietario', 'Propietario', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="text" name="propietario" id="propietario" class="form-control" autocomplete="off" required>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('nombre', 'Nombre', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="text" name="nombre" id="nombre" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('placa', 'Placa', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <select name="placa" id="placa" class="form-control" required>
                            
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('fecha', 'Fecha del Acuerdo', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="date" name="fecha" value="{{date('Y-m-d')}}" id="fecha" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('deuda', 'Deuda', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="number" name="deuda" min="10000" id="deuda" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('inicial', 'Cuota Inicial', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="number" name="inicial" min="0" value="0" id="inicial" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('cuotas', 'Plazo', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="number" name="cuotas" min="2" id="cuotas" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('celular', 'Celular', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="text" name="celular" maxlength="15" id="celular" class="form-control" required>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-dark">Simular</button>
                </div>
               
            </form>
		</div>
	</div>
@endsection
@section('modal')
	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
			<div class="modal-dialog" style="min-width: 45%">
				<div class="modal-content">
					<div class="modal-header">
                        <h4 class="modal-title">Confirmar Acuerdo de Pago</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
					<div class="modal-body">
                        <div class="row form-group">
                            <label for="saldo" class="label-required col-md-3">Saldo:</label>                         
                            <div class="col-md-8 form-control" id="saldo">

                                <!--<input type="number" name="saldo" id="saldo" class="form-control" readonly>	-->
                            </div>
                        </div>	
                        <div class="row form-group">
                            <label for="ncuotas" class="label-required col-md-3">Número de cuotas:</label>                         
                            <div class="col-md-8 form-control" id="ncuotas">
                               <!-- <input type="number" name="ncuotas" id="ncuotas" class="form-control" readonly>	-->
                            </div>
                        </div>	
                        <div class="row form-group">
                            <label for="pago" class="label-required col-md-3">Valor cuota:</label>                         
                            <div class="col-md-8 form-control" id="pago">
                                <!--<input type="number" name="pago" id="pago" class="form-control" readonly>	-->
                            </div>
                        </div>          			
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" onclick="enviar();">Confirmar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
			</div>
		</div>
    </div>
	</div>
@endsection
@section('script')
    <script>
        var validacion = 0;
        $("#propietario").autocomplete({
      		source: function( request, response ) {
                $.ajax({
                    url: "/acuerdos/buscar_propietario/" + request.term,
                    dataType: "json",
                    success: function(data) {
                        response( $.map(data, function (item) {
                            return{
                                label : item.NRO_IDENTIFICACION + "_" + item.RAZON_SOCIAL,
                                value : item.NRO_IDENTIFICACION,
                                nombre : item.RAZON_SOCIAL,
                                tercero : item.TERCERO,
                                placas: item.placas
                            }
                        }) );
                    }
                });
            },
            minLength: 4,
            select: function (event, ui) {
                $("#nombre").val(ui.item.nombre);
                $("#tercero").val(ui.item.tercero);
                let placas = ui.item.placas;
                $("#placa").empty();
                if(placas.length > 0){
                    for (const key in placas) {
                        $("#placa").append('<option value="' + placas[key].PLACA + '">' + placas[key].PLACA + '</option>');
                    }
                }
            }
        });

        $("#formacuerdo").submit(function (e) { 
            if(validacion == 0){
                e.preventDefault();
                $("#load").hide();
                let saldo = $("#deuda").val() - $("#inicial").val();
                let cuota = saldo / $("#cuotas").val();

                $("#saldo").text("$ "+saldo.toLocaleString());
                $("#ncuotas").text($("#cuotas").val());
                $("#pago").text("$ "+cuota.toLocaleString());

                $("#Modal").modal("show");
            }      
        });

        function enviar() {
            $("#Modal").modal("hide");
            Swal.fire({
				title: '<strong>Registrando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});
            $.ajax({
                type: "post",
                url: "/acuerdos/nuevo",
                data: $("#formacuerdo").serialize()
            }).done(function (data, textStatus, jqXHR) {
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);

				var csvFile;
				var downloadLink;

				filename = "Acuerdo " + $("#placa").val() + ".docx";
				csvFile = new Blob([byteArray], {type:'application/msword'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.close();

                $(location).attr("href", "/acuerdos/filtrar?propietario=" + $("#propietario").val());		
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: textStatus
				});
			});
            //validacion = 1;
            //$("#load").show();
            //$("#formacuerdo").submit();
        }
    </script>
@endsection