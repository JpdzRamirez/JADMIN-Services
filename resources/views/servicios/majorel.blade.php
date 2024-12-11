@extends('layouts.logeado')

@section('sub_title', 'Servicios Programados')

@section('style')
    <style>
        .container-fluid{
            padding-right: 0;
            padding-left: 0;
        }
    </style>
@endsection

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" enctype="multipart/form-data" id="formfile">
                        <input type="hidden" name="_token" id="_token" value="{{$token}}">
                        <div class="row form-group">
                            <label for="cliente" class="col-md-3 label-required">Cliente</label>
                            <div class="col-md-9">
                                <select name="cliente" id="cliente" class="form-control" form="formfile">
                                    <option value=""></option>
                                    <option value="3408">Majorel</option>
                                    <option value="14104">TransAmerica</option>
                                    <option value="704">Comfenalco</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group d-none" id="divValera">
                            <label for="valera" class="col-md-3 label-required">Valera</label>
                            <div class="col-md-9">
                                <select name="valera" id="valera" class="form-control">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="archivo" class="col-md-3 label-required">Archivo</label>
                            <div class="col-md-9">
                                <input type="file" class="form-control" name="archivo" id="archivo">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="fechaprog" class="col-md-3 label-required">Fecha</label>
                            <div class="col-md-9">
                                <input type="date" class="form-control" value="{{$hoy->format('Y-m-d')}}" name="fechaprog" id="fechaprog">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-sm btn-dark" style="float: right" onclick="abrirProgramacion();"><i class="fa fa-download"></i> Programación</button>
                </div>
            </div>
            
            <hr>
            <table class="table table-bordered">
                <thead>
                    <th>Programar</th>
                    <th>Ruta</th>
                    <th>Tipo</th>
                    <th>Hora</th>
                    <th>Vehículo</th>
                    <th colspan="2">Usuarios</th>
                </thead>
                <tbody id="rutas">

                </tbody>
            </table>
            <br>
            <div class="text-center">
                <button type="button" id="btnenviar" class="btn btn-dark" onclick="enviar();" disabled>Enviar <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
            </div>
		</div>
	</div>
@endsection
@section('modal')
    <div id="ModalProg" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="min-width: 50%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Programación Majorel</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="formDescarga" action="/servicios/majorel/descargar_programacion" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="fechamaj" class="label-required">Cliente</label>                         
                        </div>
                        <div class="col-md-8">
                            <select name="cliente" id="cliente" class="form-control">
                                <option value="Majorel">Majorel</option>
                                <option value="TransAmerica">TransAmerica</option>
                            </select>
                        </div>
                        
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="fechamaj" class="label-required">Fecha</label>                         
                        </div>
                        <div class="col-md-8">
                            <input type="date" name="fechamaj" id="fechamaj" class="form-control" required>	
                        </div>
                    </div>
                    <br>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="submitDescarga" class="btn btn-dark">Descargar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let rutas;
        let idValera = 0;
        $("#archivo").change(function () {
            Swal.fire({
            html: '<h3>Subir el archivo con fecha de: </h3><br><h2>' + $("#fechaprog").val() + '</h2><br><h2>Cliente: ' + $("#cliente").val() + '</h2>',
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Volver',
            }).then((result) => {
                if (result.isConfirmed) {
                    let cliente = $("#cliente").val();
                    let urlWs = "";
                    if(cliente == "3408"){
                        idValera = $("#valera").val();
                        urlWs = "/servicios/majorel/archivo";
                    }else if(cliente == "14104"){
                        urlWs = "/servicios/transamerica/archivo";
                    }else if(cliente == "704"){
                        idValera = $("#valera").val();
                        urlWs = "/servicios/comfenalco/archivo";
                    }
                    Swal.close();
                    Swal.fire({
                        title: '<strong>Importando...</strong>',
                        html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                        showConfirmButton: false,
                    });
                    let formData = new FormData($("#formfile")[0]);
                    $("#rutas").empty();
                    $.ajax({
                        type: "post",
                        url: urlWs,
                        data: formData,
                        cache:false,
                        contentType: false,
                        processData: false,
                        dataType: 'json'
                    }).done(function (data) {
                        Swal.close();
                        if(!$.isEmptyObject(data)){
                            rutas = data;
                            let filas = '';
                            let span = 1;
                            let adicional = '';
                            for (let i = 0; i < data.length; i++) {
                                span = data[i].pasajeros.length + 1;
                                filas = filas + '<tr><td rowspan="' + span + '"> <input type="checkbox" class="form-control" checked id="' + i + '-programar"></td>';
                                filas = filas + '<td rowspan="' + span + '">' + data[i].numero + '</td>';
                                filas = filas + '<td rowspan="' + span + '">' + data[i].tipoRuta + '</td>';
                                filas = filas + '<td rowspan="' + span + '"><input type="datetime-local" id="' + i + '-hora" value="' +  data[i].fecha + 'T' + data[i].hora + '" class="form-control"/> <input type="hidden" id="' + i + 'estimado"  value="' + data[i].estimado + '"/></td>';
                                if(data[i].cuentac != null){
                                    filas = filas + '<td rowspan="' + span + '"><input type="text" placeholder="Placa" id="' + i + '-placa" class="form-control placa" value="' + data[i].placa + '"/><input type="hidden" id="' + i + 'cuentac"  value="' + data[i].cuentac + '"/></td></tr>';
                                }else{
                                    filas = filas + '<td rowspan="' + span + '"><input type="text" placeholder="Placa" id="' + i + '-placa" class="form-control placa"/><input type="hidden" id="' + i + 'cuentac"/></td></tr>';
                                }
                                for (let j = 0; j < data[i].pasajeros.length; j++) {
                                    if(j == 0){
                                        filas = filas + '<tr style="border-top: 2px solid black"><td>' + data[i].pasajeros[j].nombre + '</td>';
                                    }else{
                                        filas = filas + '<tr><td>' + data[i].pasajeros[j].nombre + '</td>';
                                    }
                                    if(cliente == "3408"){
                                        if(data[i].tipoRuta == "Entrada"){
                                            filas = filas + '<td>' + data[i].pasajeros[j].direccion + ' ' + data[i].pasajeros[j].barrio + '</td></tr>';
                                        }else{
                                            filas = filas + '<td>Zona franca anillo vial ' + data[i].pasajeros[j].barrio + '</td></tr>';
                                        }   
                                    }else{
                                        filas = filas + '<td>' + data[i].pasajeros[j].direccion + ' ' + data[i].pasajeros[j].barrio + '</td></tr>';
                                    }              
                                } 
                            }
                            $("#rutas").append(filas);
                            $("#btnenviar").attr("disabled", false);
                        } 
                    }).fail(function (jqXHR, textStatus, errorThrown) {  
                        $("#archivo").val('');
                        Swal.close();
                        Swal.fire('Error', jqXHR.responseText, 'error');
                    });         
                } else {
                    $("#archivo").val('');
                    Swal.close();
                }
            });
        });

        $('body').on('focus', '.placa', function (e) {  
            $(this).autocomplete({
                source: function( request, response ) {
                $.ajax({
                    url: "/getconductores_placa",
                    dataType: "json",
                    data: {placa: request.term},
                    success: function( data ) {
                        response(
                            $.map(data, function (item) {
                                return{
                                    label : item.placa + "_" + item.nombre,
                                    value : item.placa,
                                    cuenta : item.cuenta		
                                }
                            }));
                        }
                    });
                },
                minLength: 3,
                change: function (event, ui) {
                    if(ui.item != null){
                        let ruta = $(this).attr('id').split("-");
                        $("#"+ruta[0]+"cuentac").val(ui.item.cuenta);
                    }else{
                        $(this).val("");
                    }
                }
            });
        });

        function abrirProgramacion() {
			$("#load").hide();
            $("#submitDescarga").attr("disabled", false);
			$("#ModalProg").modal("show");
		}

        $("#formDescarga").submit(function (ev) {
            $("#submitDescarga").attr("disabled", true);
        });

        let terceroWithValeras = [3408,704];
        $("#cliente").change(function(){
            let tercero = parseInt($(this).val());
            if(terceroWithValeras.includes(tercero)){
                $("#divValera").removeClass("d-none");
                $.ajax({
				type: "GET",
				dataType: "json",
				url: "/servicios/getvaleras/" + tercero,
				})
				.done(function (data, textStatus, jqXHR) {
					if(data.length > 0 ){
                        $("#valera").empty();
                        $("#valera").append('<option value=""></option>');
						$("#divValera").removeClass("d-none");
                        for (const key in data) {
                            $("#valera").append('<option value="' + data[key].id + '">' + data[key].nombre + '</option>');
                        }
						if(idValera != 0){
							$("#valera").val(idValera);

						}else{
							$("#valera").trigger("change");
						}					
					}else{
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Esta empresa no posee valeras activas'
						});
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});
            }else{
                $("#divValera").addClass("d-none");
            }
        });

        async function enviar() {
            let cliente = $("#cliente").val();
            let urlWs = "";
            if(cliente == "3408"){
                urlWs = "/servicios/majorel/programar";
            }else if(cliente == "14104"){
                urlWs = "/servicios/transamerica/programar";
            }else if(cliente == "704"){
                urlWs = "/servicios/comfenalco/programar";
            }
            Swal.fire({
                title: '<strong>Enviando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
            for (let i = 0; i < rutas.length; i++) {
                rutas[i].cuentac = $("#"+i+"cuentac").val();
                rutas[i].placa = $("#"+i+"-placa").val();
                rutas[i].horaRecogida = $("#"+i+"-hora").val();
                if($("#"+i+"-programar").is(":checked")){
                    rutas[i].programar = 1;
                }else{
                    rutas[i].programar = 0;
                }
            }
            $.ajax({
                type: "post",
                url: urlWs,
                data: { rutas: JSON.stringify(rutas), 'fechaprog': $("#fechaprog").val(), 'valera': idValera, _token: "{{csrf_token()}}"}
            }).done(function (data) {  
                Swal.close();
                if(data == "Listo"){
                    location.href = "/servicios/en_curso";   
                }else{
                    Swal.fire('Error', data, 'error');
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {  
                Swal.close();
                Swal.fire('Error', jqXHR.responseText, 'error');
            });
        }
    </script>
@endsection

