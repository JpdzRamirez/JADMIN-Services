@extends('layouts.logeado')
@section('style')
    <link rel="stylesheet" href="https://openlayers.org/en/v6.9.0/css/ol.css" type="text/css">
@endsection
@section('sub_title', 'Gestionar alerta')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row">
            <div class="col-md-6">
			{{ Form::model($alerta, ['route' => $route, 'method' => $method] ) }}
                {{ Form::hidden('id', null) }}

                <div class="form-group row {{ $errors->has('idalerta') ? 'form-error': '' }}">
                        {{ Form::label('idalerta', 'ID Alerta:', ['class' => 'col-md-3']) }}
                        <div class="col-md-7">
                            {{$alerta->id}}
                        </div>
                </div>

                <div class="form-group row {{ $errors->has('fecha') ? 'form-error': '' }}">
                        {{ Form::label('fecha', 'Fecha:', ['class' => 'col-md-3']) }}
                        <div class="col-md-7">
                                {{$alerta->fecha}}
                        </div>
                </div>

                <div class="form-group row {{ $errors->has('conductor') ? 'form-error': '' }}">
                        {{ Form::label('conductor', 'Conductor:', ['class' => 'col-md-3']) }}
                        <div class="col-md-7">
                                {{$alerta->cuentac->conductor->NOMBRE . ", " . $alerta->placa}}
                        </div>
                </div>

				<div class="form-group row {{ $errors->has('tipo') ? 'form-error': '' }}">
                    {{ Form::label('tipo', 'Tipo de alerta', ['class' => 'col-md-3']) }}
                    <div class="col-md-7">
                        {{$alerta->tipo}}
                    </div>
                </div>
                
				<div class="form-group row {{ $errors->has('descripcion') ? 'form-error': '' }}">
                    {{ Form::label('descripcion', 'Descripción', ['class' => 'label-required col-md-3']) }}
                    <div class="col-md-7">
                        <textarea name="descripcion" id="descripcion" cols="50" rows="5"></textarea>
                    </div>
                </div>
                
                <div class="form-group row {{ $errors->has('solucion') ? 'form-error': '' }}">
                    {{ Form::label('solucion', 'Solución', ['class' => 'label-required col-md-3']) }}
                    <div class="col-md-7">
                        <textarea name="solucion" id="solucion" cols="50" rows="5"></textarea>
                        {!! $errors->first('solucion', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                
				<div class="form-group text-center">
					{!! Form::button('Guardar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
                    <button type="button" class="btn btn-success" onclick="compartir();"><i class="fa fa-share-square-o" aria-hidden="true"></i> Compartir</button>
				</div>
            {{ Form::close() }}
        </div>
        <div class="col-md-6">
            <div id="mapa" style="height: 450px;width: 100%"></div>
        </div>   
    </div>      
		</div>
	</div>
@endsection
@section('script')
<script src="https://openlayers.org/en/v6.9.0/build/ol.js" type="text/javascript"></script>
<script>

    var strcompartir = "Fecha y hora: {{$alerta->fecha}}\n" + "Conductor: {{$alerta->cuentac->conductor->NOMBRE}}\n" + "Placa: {{$alerta->placa}}\n" +
        "https://www.google.com/maps?q={{$alerta->latitud}},{{$alerta->longitud}}&z=17&hl=es";

    let map = new ol.Map({ 
        layers: [new ol.layer.Tile({ 
            source: new ol.source.OSM() 
        })], 
        target: 'mapa', 
        view: new ol.View({ 
            center: ol.proj.fromLonLat([{{$alerta->longitud}}, {{$alerta->latitud}}]),
            zoom: 16
        }) 
    });

    var marcador = new ol.style.Icon({
        size: [28,40],
        src: '/img/marker.png'
    });

    let marker;
    vectorLayer = new ol.layer.Vector({
        source: new ol.source.Vector()
    });
    map.addLayer(vectorLayer);

    $(document).ready(function () {
        marker = new ol.Feature({
            geometry: new ol.geom.Point(ol.proj.fromLonLat([{{$alerta->longitud}}, {{$alerta->latitud}}]))
        });
        marker.setStyle(new ol.style.Style({
            image: marcador
        }));
        vectorLayer.getSource().addFeature(marker);
    });
    
    function compartir(){
        navigator.clipboard.writeText(strcompartir)
        .then(() => {
            Swal.fire(
                'Info Copiada',
                'Los detalles de la alerta han sido copiados al portapapeles',
                'success'
            );
        })
        .catch(err => {
            Swal.fire(
                'Error',
                'Los detalles de la alerta no pudieron ser copiados al portapapeles',
                'error'
            );
        })
    }
</script>
@endsection