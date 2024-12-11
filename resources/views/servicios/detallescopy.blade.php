@extends('layouts.logeado')

@section('sub_title', 'Detalles del servicio: ' . $servicio->id)

@section('sub_content')
<div class="card">
        <div class="card-body">
                <div id="container-main">
                        <div class="accordion-container">
                                <a href="#cliente" class="accordion-titulo">Datos del cliente<span
                                                class="toggle-icon"></span></a>
                                <div class="accordion-content" id="cliente">
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Cliente
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->cliente->nombres }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Usuario del servicio
                                                </div>
                                                <div class="col-md-8">
                                                        {{$servicio->usuarios}}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Teléfono
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->cliente->telefono }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Dirección de origen
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->direccion }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Complemento de origen
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->adicional }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Dirección de destino
                                                </div>
                                                <div class="col-md-8">

                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Complemento de destino
                                                </div>
                                                <div class="col-md-8">

                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div class="accordion-container">
                                <a href="#servicio" class="accordion-titulo">Datos del servicio<span
                                                class="toggle-icon"></span></a>
                                <div class="accordion-content" id="servicio">
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Id
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->id }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Estado
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->estado }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Modo de asignación
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->asignacion }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Fecha del servicio
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->fecha }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Fecha programada
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->fechaprogramada == null)
                                                            
                                                        @else
                                                            {{$servicio->fechaprogramada}}
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Método de pago
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->pago }}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Fecha arribo del vehiculo
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->registros != null)
                                                                @foreach ($servicio->registros as $registro)
                                                                    @if ($registro->evento == "Arribo")
                                                                        {{$registro->fecha}}
                                                                        @break
                                                                    @endif
                                                                @endforeach 
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Fecha de inicio
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->registros != null)
                                                        @foreach ($servicio->registros as $registro)
                                                            @if ($registro->evento == "Inicio")
                                                                {{$registro->fecha}}
                                                                @break
                                                            @endif
                                                        @endforeach 
                                                @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Fecha de finalización
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->registros != null)
                                                        @foreach ($servicio->registros as $registro)
                                                            @if ($registro->evento == "Fin")
                                                                {{$registro->fecha}}
                                                                @break
                                                            @endif
                                                        @endforeach 
                                                @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Unidades
                                                </div>
                                                <div class="col-md-8">
                                                        {{$servicio->unidades}}
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Operador
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->operador != null)
                                                                {{$servicio->operador->nombres}}
                                                        @endif                                                     
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                       Observaciones
                                                </div>
                                                <div class="col-md-8">
                                                        {{ $servicio->observaciones }}
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div class="accordion-container">
                                <a href="#conductor" class="accordion-titulo">Datos del conductor<span
                                                class="toggle-icon"></span></a>
                                <div class="accordion-content" id="conductor">
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Conductor
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->cuentac != null)
                                                                {{ $servicio->cuentac->conductor->NOMBRE }} 
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Identificación
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->cuentac != null)
                                                                {{ $servicio->cuentac->conductor->NUMERO_IDENTIFICACION }} 
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Teléfono conductor
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->cuentac != null)
                                                                {{ $servicio->cuentac->conductor->TELEFONO }} 
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Placa vehículo
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->placa != null)
                                                                {{ $servicio->placa }}
                                                        @endif
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div class="accordion-container">
                                <a href="#vale" class="accordion-titulo">Datos del vale<span class="toggle-icon"></span></a>
                                <div class="accordion-content" id="vale">
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Empresa
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                            {{$servicio->vale->valera->cuentae->agencia->NOMBRE}}
                                                        @endif
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="col-md-4">
                                                        Código de la empresa
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->vale->valera->cuentae->agencia->NRO_IDENTIFICACION}}
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Id vale
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->vale->id}}
                                                        @endif
                                                </div>
                                        </div>
                                       <div class="row">
                                                <div class="col-md-4">
                                                        Código vale
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->vale->codigo}}
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Id valera
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->vale->valera->id}}
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Valera
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->vale->valera->nombre}}
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Unidades
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->unidades}}
                                                        @endif
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-md-4">
                                                        Total
                                                </div>
                                                <div class="col-md-8">
                                                        @if ($servicio->vale != null)
                                                                {{$servicio->valor}}
                                                        @endif
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div class="accordion-container">
                                <a href="#registros" class="accordion-titulo">Registros<span class="toggle-icon"></span></a>
                                <div class="accordion-content" id="registros">
                                        <table class="table table-bordered" style="table-layout: fixed">
                                                <thead>
                                                        <tr>
                                                                <th>Fecha</th>
                                                                <th>Acción</th>
                                                                <th>Conductor</th>
                                                                <th>Vehiculo</th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        @forelse ($servicio->registros as $registro)
                                                                <tr>
                                                                        <td>{{$registro->fecha}}</td>
                                                                        <td>{{$registro->evento}}</td>
                                                                        <td>{{$servicio->cuentac->conductor->NOMBRE}}</td>
                                                                        <td>{{$servicio->placa}}</td>
                                                                </tr>
                                                        @empty
                                                                <tr class="align-center">
                                                                        <td colspan="4">No hay registros</td>
                                                                </tr>
                                                        @endforelse
                                                </tbody>
                                        </table>
                                </div>
                        </div>

                        <div class="accordion-container">
                                        <a href="#coordenadas" class="accordion-titulo">Coordenadas<span class="toggle-icon"></span></a>
                                        <div class="accordion-content" id="coordenadas" style="min-height: 500px">
                                                <div id="mapa" style="height: 500px;width: 100%; ">
                                        </div>
                        </div>
                </div>
        </div>
</div>
@endsection

@section('script')
<script>
        $(".accordion-titulo").click(function () {

                var contenido = $(this).next(".accordion-content");

                if (contenido.css("display") == "none") { //open		
                        contenido.slideDown(250);
                        $(this).addClass("open");
                }else { //close		
                        contenido.slideUp(250);
                        $(this).removeClass("open");
                }
        });

        //var posiciones = new Array();
        function initMap() {
                //var directionsService = new google.maps.DirectionsService;
                //var directionsDisplay = new google.maps.DirectionsRenderer({preserveViewport: true});
                var bucaramanga = {lat: 7.122270, lng: -73.125769};
                var map = new google.maps.Map(
                document.getElementById('mapa'), {zoom: 16, 
                center: bucaramanga,
                gestureHandling: 'greedy',
                zoomControl: true,
                mapTypeControl: true,
                scaleControl: true,
                streetViewControl: false,
                rotateControl: true,
                fullscreenControl: true});

                /*directionsDisplay.setMap(map);

                @php
                        /*$i = 0;
                        $total = count($servicio->seguimientos);
                        if($total > 27){
                                $seguimientos = [];
                                $seguimientos[0] = $servicio->seguimientos[0];
                                $seguimientos[26] = $servicio->seguimientos[$total-1];
                                $salto = (int) ($total-2)/25;

                                $ind = $salto;
                                for ($j=1; $j<=25; $j++) { 
                                     $seguimientos[$j] = $servicio->seguimientos[$ind-1];
                                     $ind = $ind + $salto;   
                                }
                        }else{
                                $seguimientos = $servicio->seguimientos;
                        }*/
                @endphp */
				

                /*@foreach($servicio->seguimientos as $key => $seguimiento)

                      /*  posiciones.push({location: new google.maps.LatLng({{$seguimiento->latitud}}, {{$seguimiento->longitud}}), stopover: true});
                        @php
                                $i++;
                        @endphp */
                @endforeach*/

                @foreach($servicio->seguimientos as $key => $seguimiento)
					new google.maps.Marker({
						position: {lat: {{$seguimiento->latitud}}, lng: {{$seguimiento->longitud}}},
						map: map,
						title:"Punto " + {{$key}}
					});
                @endforeach

                /*var inicio = posiciones[0].location;
                var fin = posiciones[{{$i-1}}].location;

                posiciones.pop();
                posiciones.splice(0,1);
                map.setCenter(inicio);

                directionsService.route({
                        origin: inicio,
                        destination: fin,
                        waypoints: posiciones,
                        optimizeWaypoints: true,
                        travelMode: 'DRIVING'
                }, function(response, status) {
                        if (status === 'OK') {
                                directionsDisplay.setDirections(response);
                        } else {
                                window.alert('Directions request failed due to ' + status);
                        }
                });*/
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD0rqSIP9AEZwZ4e0drcvZT9vjholUzDY4&callback=initMap" async defer></script>
@endsection
@section('sincro')
    <script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection