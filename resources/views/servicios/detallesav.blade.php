@extends('layouts.logeado')

@section('sub_title', 'Detalles del servicio: ' . $servicio->id)
@section('style')
    <link rel="stylesheet" href="https://openlayers.org/en/v6.9.0/css/ol.css" type="text/css">
@endsection
@section('sub_content')
<div class="card">
	<div class="card-body">
		<div id="container-main">
			<div class="accordion-container">
				<a href="#cliente" class="accordion-titulo">Datos del cliente<span class="toggle-icon"></span></a>
				<div class="accordion-content" id="cliente">
					<div class="row">
						<div class="col-md-4">
							Cliente
						</div>
						<div class="col-md-8">
							@if (Auth::user()->id == 119)
								Provisional administrativo								
							@else
								{{ $servicio->cliente->nombres }}
							@endif
							
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Usuario del servicio
						</div>
						<div class="col-md-8">
							{{str_replace("\n", ", ", $servicio->usuarios)}}
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
				<a href="#servicio" class="accordion-titulo">Datos del servicio<span class="toggle-icon"></span></a>
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
							Asignaciones
						</div>
						<div class="col-md-8">
							@if ($servicio->reasignado == 1)
								Normal, Directo
							@else
								{{ $servicio->asignacion }}	
							@endif						
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
							Ruta
						</div>
						<div class="col-md-8">
							{{$ruta}}
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Fuente del servicio
						</div>
						<div class="col-md-8">
							@if ($servicio->users_id != null)
								@if ($servicio->users_id == 112)
									IVR
								@else
									CRM
								@endif
							@else
								Aplicación cliente
							@endif
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
				<a href="#conductor" class="accordion-titulo">Datos del conductor<span class="toggle-icon"></span></a>
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
							@if ($servicio->valeav_servicio != null)
								Avianca					
							@endif														
						</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							Código de la empresa
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav_servicio != null)
							    {{$servicio->valeav_servicio->valeav->valera->cuentae->agencia->NRO_IDENTIFICACION}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Id vale
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav_servicio != null)
								{{$servicio->valeav_servicio->valeav->id}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Código vale
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav_servicio != null)
									{{$servicio->valeav_servicio->valeav->codigo}}			
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Id valera
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav_servicio != null)
								{{$servicio->valeav_servicio->valeav->valera->id}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Valera
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav_servicio != null)
								{{$servicio->valeav_servicio->valeav->valera->nombre}}
							@endif
						</div>
                    </div>
                    <div class="row">
						<div class="col-md-4">
							Tipo de vale
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav != null)
								{{$servicio->valeav->tipo}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Tipo de servicio
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav != null)
								{{$servicio->valeav->tiposer}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Vuelo
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav != null)
								{{$servicio->valeav->vuelo}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Ruta
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav != null)
								{{$ruta}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Operador responsable
						</div>
						<div class="col-md-8">
							@if ($servicio->responsable != null)
								{{$servicio->responsable->nombres}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Total
						</div>
						<div class="col-md-8">
							@if ($servicio->valeav != null)
								{{number_format($servicio->valor)}}
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
					<div class="row">
                        <div class="col-12">
							<div id="mapa" style="height: 500px;width: 100%; ">
							</div>
						</div>
					</div>	
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
<script src="https://openlayers.org/en/v6.9.0/build/ol.js" type="text/javascript"></script>
<script>
    $(".accordion-titulo").click(function () {

        var contenido = $(this).next(".accordion-content");

        if (contenido.css("display") == "none") { //open		
            contenido.slideDown(250);
            $(this).addClass("open");
        } else { //close		
            contenido.slideUp(250);
            $(this).removeClass("open");
        }
    });

	var infos = [];
	var markers = new Array();
	var vectorSource, vectorLayer, map;

	var icons = {
		Inicio: {
			icon: new ol.style.Icon({
				size: [35,60],
				src: '/img/markerInicio.png'
			}) 
		},
		Fin: {
			icon: new ol.style.Icon({
				size: [35,60],
				src: '/img/markerFin.png'
			})
		}
	};

	let puntos = [
		@foreach($servicio->seguimientos as $seguimiento)
			[{{$seguimiento->longitud}}, {{$seguimiento->latitud}}],
		@endforeach
	];
	let puntosMarcadores;
	if (puntos.length > 1) {
		puntosMarcadores = [puntos[0], puntos[puntos.length-1]];
	}

	map = new ol.Map({ 
		layers: [new ol.layer.Tile({ 
			source: new ol.source.OSM() 
		})], 
		target: 'mapa', 
		view: new ol.View({ 
			center: ol.proj.fromLonLat([-73.1156152, 7.1155167]),
			zoom: 13
		}) 
	});
	
	$(document).ready(function () {
		if (puntos.length > 1) {
			trazarLinea();
			ubicarMarcadores();
		}
	});

	function trazarLinea(){
		for (const key in puntos) {
			puntos[key] = ol.proj.transform(puntos[key], 'EPSG:4326', 'EPSG:3857')
		} 

		var poligono = new ol.Feature(new ol.geom.MultiLineString([puntos]));

		var featureStyle = new ol.style.Style({
			stroke: new ol.style.Stroke({
				color: 'rgb(0, 176, 255)',
				width: 10
			})
		});
		poligono.setStyle(featureStyle);

		vectorSource = new ol.source.Vector({
			features: [poligono]
		});

		vectorLayer = new ol.layer.Vector({
			source: vectorSource
		});
		map.addLayer(vectorLayer);
	}

	function ubicarMarcadores() { 
		markers[0] = new ol.Feature({
			geometry: new ol.geom.Point(ol.proj.fromLonLat(puntosMarcadores[0]))
		});
		markers[0].setStyle(new ol.style.Style({
			image: icons['Inicio'].icon
		}));
		vectorLayer.getSource().addFeature(markers[0]);

		markers[1] = new ol.Feature({
			geometry: new ol.geom.Point(ol.proj.fromLonLat(puntosMarcadores[1]))
		});
		markers[1].setStyle(new ol.style.Style({
			image: icons['Fin'].icon
		}));
		vectorLayer.getSource().addFeature(markers[1]);

	}

	let sumaEntradas = 0;
	const resizeObserver = new ResizeObserver((entries) => {
		sumaEntradas++;
		if(entries.length == sumaEntradas){
			map.updateSize();
			sumaEntradas = 0;
		}
	});
	resizeObserver.observe(document.getElementById("coordenadas"));

</script>
@endsection