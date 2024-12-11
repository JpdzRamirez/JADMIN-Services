@extends('layouts.logeado')

@section('sub_title', 'Vehiculos conectados')

@section('sub_content')
<div class="card">
    <div class="card-body" style="min-height: 500px;">
        <div class="align-center" style="display: flex">
            <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Mensaje masivo</a>
            <input type="text" value="{{$placa}}" name="placa" id="placa" maxlength="7" class="form-control" style="width: 200px; margin-left: 10px" placeholder="Placa" required>
            <button type="button" class="btn btn-sm btn-dark" onclick="buscar()"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
            @if (!empty($placa))
                <button type="button" class="btn btn-sm btn-dark" onclick="limpiar()" style="margin-left: 5px"><i class="fa fa-refresh" aria-hidden="true"></i> Limpiar</button>
            @endif
        </div>
        <div id="mapa" style="height: 500px;width: 100%; ">

        </div>
    </div>
</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Mensaje masivo</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="/mensajes/nuevo">
                <input type="hidden" name="masivo" value="1">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">Tipo</div>
                        <div class="col-md-9">
                            <select name="tiposms" id="tiposms"class="form-control">
                                <option value="Unico" selected>Único</option>
                                <option value="Programado">Programado</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div id="bloqueprogramado" style="display: none">
                        <div class="row">
                            <div class="col-md-3">
                                Intervalo:
                            </div>
                            <div class="col-md-5" style="padding-right: 0">
                                <input type="number" disabled name="intervalo" class="form-control" id="intervalo"/>
                            </div>
                            <div class="col-md-4" style="padding-left: 0">
                                <select name="undinter" id="undinter" class="form-control" disabled>
                                    <option value="Horas">Horas</option>
                                    <option value="Minutos">Minutos</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                Duración (Días):
                            </div>
                            <div class="col-md-9">
                                <input type="number" name="duracion" class="form-control" id="duracion" disabled/>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            Texto:
                        </div>
                        <div class="col-md-9">
                            <textarea name="texto" class="form-control" id="texto" cols="50" rows="5"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Enviar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')

<script>
    var fbuscar = 0;
    var markers = new Array();
    var infos = new Array();
    var borrar = new Array();
    var icons = {
        Libre: {
            icon: '/img/iconolibre.png'
        },
        Ocupado: {
            icon: '/img/iconocupado.png'
        },
        "Ocupado propio": {
            icon: '/img/iconopropio.png'
        }
    };

    @if(empty($placa))
        var placa = "";
    @else
        var placa = "{{$placa}}";
    @endif

    var currWindow = false;
    var map, vehiculo, fbuscar = 0;

    function initMap() {
        var bucaramanga = {
            lat: 7.122270,
            lng: -73.125769
        };
        map = new google.maps.Map(
        document.getElementById('mapa'), {
            zoom: 13,
            center: bucaramanga,
            gestureHandling: 'greedy',
            zoomControl: true,
            mapTypeControl: false,
            scaleControl: true,
            streetViewControl: false,
            rotateControl: true,
            fullscreenControl: true
        });
    }

    $(document).ready(function() {
        initaxis();
    });

    $("#tiposms").change(function () {
        if($(this).val() == "Programado"){
            $("#bloqueprogramado").css("display", "block");
            $("#bloqueprogramado :input").attr('disabled', false);
            $("#bloqueprogramado :input").attr('required', true);
        }else{
            $("#bloqueprogramado").css("display", "none");
            $("#bloqueprogramado :input").attr('disabled', true);
            $("#bloqueprogramado :input").attr('required', false);
        }
    });

    function initaxis() {
        $.ajax({
            type: "GET",
            dataType: "json",
            data: {
                "placa": placa
            },
            url: "/vehiculos/ubicar",
        })
        .done(function(data, textStatus, jqXHR) {
            if (data.vehiculos,length > 0) {
                vehiculo = 0;
                for (let j in data.vehiculos) {
                        markers[vehiculo] = new google.maps.Marker({
                        position: {
                            lat: parseFloat(data.vehiculos[j].latitud),
                            lng: parseFloat(data.vehiculos[j].longitud)
                        },
                        icon: icons[data.vehiculos[j].estado].icon,
                        map: map,
                        arreglo: vehiculo,
                        cuenta: data.vehiculos[j].id
                    });
                    infos[vehiculo] = new google.maps.InfoWindow({
                        content: data.vehiculos[j].conductor.NOMBRE + '<br>Placa: ' + data.vehiculos[j].placa + '<br>Teléfono: ' + data.vehiculos[j].conductor.TELEFONO + '<br><a target="_blank" href="/conductores/' + data.vehiculos[j].id + '">Celular: ' + data.vehiculos[j].conductor.CELULAR + ' </a><br>' + '<a class="msj" href="/mensajes/chat/' + data.vehiculos[j].id + '" >Chatear</a>'
                    });
                    markers[vehiculo].addListener('click', function () {
                        if (currWindow) {
                            currWindow.close();
                        }
                        currWindow = infos[this.arreglo];
                        currWindow.open(map, markers[this.arreglo]);
                    });
                    vehiculo++;
                }
            } 

            if (placa == "") {
                setInterval(ubicar, 15000);
            } else {
                setInterval(ubicar, 15000, placa);
            }                       
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La consulta de vehículos falló'
			});
        });
    }

    function ubicar(placa = "") {
        if (fbuscar == 0) {
            fbuscar = 1;

            $.ajax({
                type: "GET",
                dataType: "json",
                data: {
                    "placa": placa
                },
                url: "/vehiculos/ubicar",
            })
            .done(function(data, textStatus, jqXHR) {
                if (data.vehiculos.length > 0) {
                    borrar = [];
                    if(placa == ""){
                        for (let a in markers) {
                            google.maps.event.clearListeners(markers[a], 'click');
                            let found = false;
                            for (var b in data.vehiculos) {
                                if(markers[a].cuenta == data.vehiculos[b].id){
                                    data.vehiculos[b].mapeado = 1;
                                    markers[a].setPosition(new google.maps.LatLng(data.vehiculos[b].latitud, data.vehiculos[b].longitud));
                                    markers[a].setIcon(icons[data.vehiculos[b].estado].icon);
                                    found = true;
                                    break;
                                }
                            }
                            if(found == false){
                                markers[a].setMap(null);
                                borrar.push(a);                        
                            }
                        }
                    }else{
                        for (let k in markers) {
                            markers[k].setMap(null);
                            google.maps.event.clearListeners(markers[k], 'click');
                        }
                        markers = [];
                        infos = [];
                    }
                        
                    var size = markers.length;
                    for (let key in data.vehiculos) {
                        if (!data.vehiculos[key].hasOwnProperty('mapeado')){
                            markers[size] = new google.maps.Marker({
                            position: {
                                lat: data.vehiculos[key].latitud,
                                lng: data.vehiculos[key].longitud
                            },
                            icon: icons[data.vehiculos[key].estado].icon,
                            map: map,
                            arreglo: size,
                            cuenta: data.vehiculos[key].id
                            });
                            infos[size] = new google.maps.InfoWindow({
                                content: data.vehiculos[key].conductor.NOMBRE + '<br>Placa: ' + data.vehiculos[key].placa + '<br>Teléfono: ' + data.vehiculos[key].conductor.TELEFONO + '<br><a target="_blank" href="/conductores/' + data.vehiculos[key].id + '">Celular: ' + data.vehiculos[key].conductor.CELULAR + ' </a><br>' + '<a class="msj" href="/mensajes/chat/' + data.vehiculos[key].id + '" >Chatear</a>'
                            });
                            size++;
                        }
                    }                  
                } else {
                    for (let k in markers) {
                        markers[k].setMap(null);
                        google.maps.event.clearListeners(markers[k], 'click');
                    }
                    markers = [];
                    infos = [];
                }  
                borrarMarcadores();                           
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                fbuscar = 0;
            });
        }
    }

    function borrarMarcadores() {
        for (let key in borrar) {
            markers.splice(borrar[key], 1);
            infos.splice(borrar[key], 1);
        }
        borrar = [];
        for (let key in markers) {          
            markers[key].addListener('click', function () {
                if (currWindow) {
                    currWindow.close();
                }
                currWindow = infos[key];
                currWindow.open(map, markers[key]);
            });
        }
        fbuscar = 0;
    }

    function buscar() {
        placa = $("#placa").val();
        if (placa != "") {

            location.href = "/vehiculos/ubicar?placa="+ placa;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe ingresar placa'
			});
        }
    }

    function limpiar() {

        location.href = "/vehiculos/ubicar";     
    }
    
    $(document).on('click', '.msj', function (ev) {
        ev.preventDefault();
        if(ev.target.href == null){
            cuenta = ev.target.parentElement.href;
        }else{
            cuenta = ev.target.href;
        }
          
        $("#framechat").attr("src", cuenta);
        $("#Modalchat").modal('show');
    });

    $(document).on('click', '.close-chat', function () {  
        $("#framechat").attr("src", "");
    });
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyByzEj0ALxdktnognv3gr6XCIeN3DtMw1U&callback=initMap" async defer></script>
@endsection
@section('sincro')
	<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection