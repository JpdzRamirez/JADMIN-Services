@extends('layouts.logeado')
@section('style')
    <link rel="stylesheet" href="https://openlayers.org/en/v6.9.0/css/ol.css" type="text/css">
    <style>
        .ol-popup {
            position: absolute;
            background-color: white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #cccccc;
            bottom: 12px;
            left: -50px;
            min-width: 280px;
        }
        .ol-popup:after, .ol-popup:before {
            top: 100%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }
        .ol-popup:after {
            border-top-color: white;
            border-width: 10px;
            left: 48px;
            margin-left: -10px;
        }
        .ol-popup:before {
            border-top-color: #cccccc;
            border-width: 11px;
            left: 48px;
            margin-left: -11px;
        }
        .ol-popup-closer {
            text-decoration: none;
            position: absolute;
            top: 2px;
            right: 8px;
        }
        .ol-popup-closer:after {
            content: "✖";
        }          
    </style>
@endsection
@section('sub_title', 'Vehiculos conectados')
@section('sub_content')
<div class="card">
    <div class="card-body" style="min-height: 500px;">
        <div class="align-center" style="display: flex">
            {{-- @if (($hora->hour >= 12 && $hora->hour <=15) || $hora->hour >= 18 || $hora->hour <= 3 || $usuario->roles_id == 1) --}}
                <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Mensaje masivo</a>
            {{-- @endif --}}
            <input type="text" value="{{$placa}}" name="placa" id="placa" maxlength="7" class="form-control" style="width: 200px; margin-left: 10px" placeholder="Placa" required>
            <button type="button" class="btn btn-sm btn-dark" onclick="buscar()"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
            @if (!empty($placa))
                <button type="button" class="btn btn-sm btn-dark" onclick="limpiar()" style="margin-left: 5px"><i class="fa fa-refresh" aria-hidden="true"></i> Limpiar</button>
            @endif
        </div>
        <div id="mapa" style="height: 500px;width: 100%;">
        </div>
    </div>
</div>
<div id="popup" class="ol-popup">
    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
    <div id="popup-content"></div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="card text-center m-b-15">
            <div class="mb-2 card-body text-muted">
                <h3><a href="#"  class="text-success">{{$cuentas}}</a></h3>
                    Taxis operando
            </div>
        </div>
    </div>
    <div class="col-md-3">
            <div class="card text-center m-b-30">
                <div class="mb-2 card-body text-muted">
                    <h3><a href="#" class="text-warning">{{$curso}}</a></h3>
                        Servicios en curso
                </div>
            </div>
    </div>
    <div class="col-md-3">
            <div class="card text-center m-b-30">
                <div class="mb-2 card-body text-muted">
                    <h3><a href="#" class="text-default">{{$finalizados}}</a></h3>
                        Servicios finalizados hoy
                </div>
            </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center m-b-30">
            <div class="mb-2 card-body text-muted">
                <h3><a href="#" class="text-danger">{{$cancelados}}</a></h3>
                    Servicios cancelados hoy
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
<script src="https://openlayers.org/en/v6.9.0/build/ol.js" type="text/javascript"></script>
<script>
    var fbuscar = 0;
    var markers = new Array();
    var infos = new Array();
    var borrar = new Array();
    var container = document.getElementById('popup');
    var content = document.getElementById('popup-content');
    var closer = document.getElementById('popup-closer');
    var vectorSource, vectorLayer;

    var icons = {
        Libre: {
            icon: new ol.style.Icon({
                size: [40,28],
                src: '/img/iconolibre.png'
            }) 
        },
        Ocupado: {
            icon: new ol.style.Icon({
                size: [40,28],
                src: '/img/iconocupado.png'
            })
        },
        "Ocupado propio": {
            icon: new ol.style.Icon({
                size: [40,28],
                src: '/img/iconopropio.png'
            })
        }
    };

    const overlay = new ol.Overlay({
        element: container,
        autoPan: true,
        autoPanAnimation: {
            duration: 250,
        }
    });

    closer.onclick = function () {
        overlay.setPosition(undefined);
        closer.blur();
        return false;
    };

    @if(empty($placa))
        var placa = "";
    @else
        var placa = "{{$placa}}";
    @endif

    
    var map, vehiculo, fbuscar = 0;

    map = new ol.Map({ 
        layers: [ 
            new ol.layer.Tile({ 
                source: new ol.source.OSM() 
            })], 
        target: 'mapa', 
        view: new ol.View({ 
            center: ol.proj.fromLonLat([-73.12, 7.122270]),
            zoom: 13
        }) 
    });

    $(document).ready(function() {
        vectorLayer = new ol.layer.Vector({
            source: new ol.source.Vector()
        });
        map.addLayer(vectorLayer);
        map.addOverlay(overlay);

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
            if (data.vehiculos.length > 0) {
                vehiculo = 0;
                for (let j in data.vehiculos) {
                    markers[vehiculo] = new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([data.vehiculos[j].longitud, data.vehiculos[j].latitud])),
                        posicion: vehiculo,
                        cuenta: data.vehiculos[j].id
                    });
                    markers[vehiculo].setStyle(new ol.style.Style({
                        image: icons[data.vehiculos[j].estado].icon
                    }));
                    vectorLayer.getSource().addFeature(markers[vehiculo]);

                    infos[vehiculo] = data.vehiculos[j].conductor.NOMBRE + '<br>Placa: ' + data.vehiculos[j].placa + '<br>Teléfono: ' + data.vehiculos[j].conductor.TELEFONO + '<br><a target="_blank" href="/conductores/' + data.vehiculos[j].id + '">Celular: ' + data.vehiculos[j].conductor.CELULAR + ' </a><br>' + '<a class="msj" href="/mensajes/chat/' + data.vehiculos[j].id + '" >Chatear</a>';
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

    map.on("click", function (evt) {
        var feature = map.forEachFeatureAtPixel(evt.pixel,
            function(feature) {
                return feature;
            }
        );

        if (feature) {
            var coordinates = this.getCoordinateFromPixel(evt.pixel);
            content.innerHTML = infos[feature.A.posicion];
            overlay.setPosition(coordinates);
        }
    });


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
                    if(placa = ""){
                        for (let a in markers) {
                            let found = false;
                            for (var b in data.vehiculos) {
                                if(markers[a].A.cuenta == data.vehiculos[b].id){
                                    data.vehiculos[b].mapeado = 1;
                                    markers[a].getGeometry().setCoordinates(new ol.geom.Point(ol.proj.fromLonLat(data.vehiculos[b].longitud, data.vehiculos[b].latitud)));
                                    markers[a].setStyle(new ol.style.Style({
                                        image: icons[data.vehiculos[b].estado].icon
                                    }));
                                    found = true;
                                    break;
                                }
                            }
                            if(found == false){
                                vectorLayer.getSource().removeFeatures(markers[a]);
                                borrar.push(a);                        
                            }
                        }
                    }else{
                        vectorLayer.getSource().clear();
                        markers = [];
                        infos = [];
                    }

                    var size = markers.length;
                    for (let key in data.vehiculos) {
                        if (!data.vehiculos[key].hasOwnProperty('mapeado')){
                            markers[size] = new ol.Feature({
                                geometry: new ol.geom.Point(ol.proj.fromLonLat([data.vehiculos[key].longitud, data.vehiculos[key].latitud])),
                                posicion: size,
                                cuenta: data.vehiculos[key].id
                            });
                            markers[size].setStyle(new ol.style.Style({
                                image: icons[data.vehiculos[key].estado].icon
                            }));
                            vectorLayer.getSource().addFeature(markers[size]);
                           
                            infos[size] = data.vehiculos[key].conductor.NOMBRE + '<br>Placa: ' + data.vehiculos[key].placa + '<br>Teléfono: ' + data.vehiculos[key].conductor.TELEFONO + '<br><a target="_blank" href="/conductores/' + data.vehiculos[key].id + '">Celular: ' + data.vehiculos[key].conductor.CELULAR + ' </a><br>' + '<a class="msj" href="/mensajes/chat/' + data.vehiculos[key].id + '" >Chatear</a>';
                            size++;
                        }
                    }  
                }else {
                    vectorLayer.getSource().clear();
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

<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD0rqSIP9AEZwZ4e0drcvZT9vjholUzDY4&callback=initMap" async defer></script>-->
@endsection
@section('sincro')
	<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection