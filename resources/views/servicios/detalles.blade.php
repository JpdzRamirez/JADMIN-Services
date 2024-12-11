@extends('layouts.logeado')
@section('style')
    <link rel="stylesheet" href="https://openlayers.org/en/v6.9.0/css/ol.css" type="text/css">
	<style>
		.ceco {
			border: 0;
			background: #0087cc;
			border-radius: 4px;
			box-shadow: 0 5px 0 #006599;
			color: #fff;
			cursor: pointer;
			font: inherit;
			margin: 0;
			outline: 0;
			transition: all .1s linear;
			}
			.ceco:active {
			box-shadow: 0 2px 0 #006599;
			transform: translateY(3px);
			}
	</style>
@endsection
@section('sub_title', 'Detalles del servicio: ' . $servicio->id)

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
							Celular
						</div>
						<div class="col-md-8">
							{{ $servicio->contacto}}
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

			@if ((Auth::user()->idtercero == 784 || Auth::user()->roles_id == 1) &&  count($servicio->pasajeros) > 0)
				<div class="accordion-container">
					<a href="#pasajeros" class="accordion-titulo">Datos de pasajeros<span class="toggle-icon"></span></a>
					<div class="accordion-content" id="pasajeros">
						@php
							$datagroupid=1;
						@endphp
						@foreach ( $servicio->pasajeros as $pasajero)

							<div class="row" data-group-id="{{$datagroupid}}">
								<div class="col-md-2">
									<b>Pasajero:</b>
								</div>
								<div class="col-md-4 font-weight-normal">
									{{$pasajero->nombre}}
								</div>
								<div class="col-md-4 font-weight-normal">
									<p data-toggle="tooltip" data-placement="top" 
									title="Centro de Costo"
									class="centro-costo">
										{{'Sub_Cuenta: '.$pasajero->pivot->sub_cuenta.' Affe: '. $pasajero->pivot->affe }}
									</p>
								</div>
								<div class="col-md-2 font-weight-normal">
									<button type="button"
									class="badge ceco badge-primary badge-pill shadow rounded"
									style="cursor: pointer; border:0;" 
									data-toggle="modal"
									data-target="#modalCCosto"
									data-form-action="/pasajero/{{$pasajero->id}}/{{$servicio->id}}/actualizar/CECO"
									data-modal-title="Centro de Costo Pasajero: {{$pasajero->nombre}}"
									data-sub-cuenta="{{$pasajero->pivot->sub_cuenta}}"
									data-affe="{{$pasajero->pivot->affe}}">Actualizar CECO</button>	
								</div>
							</div>
						@php
							$datagroupid+=1;
						@endphp
						@endforeach
					</div>
				</div>
			@endif

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
							Unidades
						</div>
						<div class="col-md-8">
							{{$servicio->unidades}}
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
							Operador cambio conductor
						</div>
						<div class="col-md-8">
							@if ($servicio->operador_asignacion != null)
								{{$servicio->operador_asignacion->nombres}}
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
							@if ($servicio->vale_servicio != null)
								@if (Auth::user()->id == 119)
									Provisional administrativo								
								@else
									{{$servicio->vale_servicio->vale->valera->cuentae->agencia->NOMBRE}}
								@endif						
							@endif														
						</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							Código de la empresa
						</div>
						<div class="col-md-8">
							@if ($servicio->vale_servicio != null)
							{{$servicio->vale_servicio->vale->valera->cuentae->agencia->NRO_IDENTIFICACION}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Id vale
						</div>
						<div class="col-md-8">
							@if ($servicio->vale_servicio != null)
								{{$servicio->vale_servicio->vale->id}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Código vale
						</div>
						<div class="col-md-8">
							@if ($servicio->vale_servicio != null)
								{{$servicio->vale_servicio->vale->codigo}}
							@endif
						</div>
					</div>
					@if (Auth::user()->roles_id == 1)
						<div class="row">
							<div class="col-md-4">
								Liberado por
							</div>
							<div class="col-md-8">
								@if ($servicio->vale_servicio && $servicio->vale_servicio->usuario)
									{{$servicio->vale_servicio->usuario->nombres}}
								@endif
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								Fecha liberación
							</div>
							<div class="col-md-8">
								@if ($servicio->vale_servicio)
									{{$servicio->vale_servicio->fecha_edicion}}
								@endif
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								Observación de Liberación
							</div>
							<div class="col-md-8">
								@if ($servicio->vale_servicio)
									{{$servicio->vale_servicio->ultima_observacion}}
								@endif
							</div>
						</div>
					@endif
					<div class="row">
						<div class="col-md-4">
							Id valera
						</div>
						<div class="col-md-8">
							@if ($servicio->vale_servicio != null)
								{{$servicio->vale_servicio->vale->valera->id}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Valera
						</div>
						<div class="col-md-8">
							@if ($servicio->vale_servicio != null)
								{{$servicio->vale_servicio->vale->valera->nombre}}
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							Unidades
						</div>
						<div class="col-md-8">
							@if ($servicio->vale_servicio != null)
								{{$servicio->unidades}}
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
							@if ($servicio->vale_servicio != null)
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
				<div class="accordion-content" id="coordenadas">
                    <div class="row">
                        <div class="col-12">
                            <div id="mapaCoordenadas" style="height:500px; width: 100%;">
                            </div>
                        </div>
                    </div>
				</div>
			</div>
		</div>
        
	</div>
	@endsection
	@section('modal')	
		<!-- modalCCosto -->
	<div id="modalCCosto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalCCostoLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="min-width: 50%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="modalCCostoTitulo"></h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<form id="modalCCostoCambiar" action="">
					@csrf
					<div class="modal-body d-flex flex-column" style="gap:1em;">
						<div class="row">
							<div class="col-md-4">
								<label for="sub_cuenta" class="label-required">Sub-Cuenta</label>
							</div>
							<div class="col-md-8">
								<input name="sub_cuenta" id="sub_cuenta" type="text" value="" class="form-control" required>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label for="affe" class="label-required">Affe</label>
							</div>
							<div class="col-md-8">
								<input name="affe" id="affe" type="text" value="" class="form-control" required>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark ccosto-submit">Guardar</button>
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
		$(".accordion-titulo").click(function() {

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
			target: 'mapaCoordenadas', 
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

		$('button[data-toggle="modal"]').on('click', function() {
			// Obtener datos del botón
			let modalId = $(this).data('modal-id');
			let formAction = $(this).data('form-action');
			let modalTitle = $(this).data('modal-title');
			let subCuenta = $(this).attr('data-sub-cuenta');
			let affe = $(this).attr('data-affe');
			//Recuperamos y Guardamos el id del input group en el modal para actualizarlo luego
			let groupId = $(this).closest('.row').data('group-id');
			
			$('#modalCCosto').data('group-id', groupId);

			// Actualizar el contenido del modal
			$('#modalCCosto #modalCCostoTitulo').text(modalTitle);
			$('#modalCCosto #modalCCostoCambiar').attr('action', formAction);
			$('#modalCCosto #sub_cuenta').val(subCuenta);
			$('#modalCCosto #affe').val(affe);
		});

		$('.ccosto-submit').click(function(e) {
			e.preventDefault();
			
			// Recoge el formulario
			let form = $('#modalCCostoCambiar');
			let groupId = $('#modalCCosto').data('group-id');

			// Limpia los errores previos
			form.find('.is-invalid').removeClass('is-invalid');
			form.find('.invalid-feedback').remove();
			
			// Envía el formulario
			$.ajax({
				url: form.attr('action'), // URL de destino
				method: 'POST',
				data: form.serialize(),
				success: function(response) {
					if (response.message) {
						// Mostrar mensaje de éxito
						Swal.fire({
							icon: 'success',
							title: 'Éxito',
							text: response.message,
							footer: response.footer
						});
						
						// Reflejar cambios: Actualiza el campo input del centro de costo en el input-group correspondiente
						let valueText="Sub_Cuenta: "+response.sub_cuenta+" Affe: "+ response.affe ;
						let textField= $(`div[data-group-id="${groupId}"] .centro-costo`);
						textField.text(valueText); 
						textField.attr('title',  valueText); 
						//Actualizamo la información del boton modal
						let buttonModal= $(`div[data-group-id="${groupId}"] .ceco`);
						buttonModal.attr('data-sub-cuenta', response.sub_cuenta);
						buttonModal.attr('data-affe', response.affe);

						// Limpiamos y cierramos el modal IMPORTANTE LIMPIARLO PARA QUE NO QUEDE CON DATOS
						form[0].reset();
						$('#modalCCosto').modal('hide');
					} else {
						// Caso por defecto
						Swal.fire({
							icon: 'warning',
							title: 'Atención',
							text: 'La respuesta no fue como se esperaba. Por favor, verifique la acción nuevamente.',
						});
					}
				},
				error: function(xhr) {
						// Limpia los errores anteriores
						form.find('.invalid-feedback').remove();
						form.find('.is-invalid').removeClass('is-invalid');
						// mensaje por defecto
						let errorMessage = 'Algo salió mal, por favor intenta nuevamente.';

						//Todos los mensajes traen mensajes por defecto para evitar rupturas
						if (xhr.status === 422) {
							// Errores de validación / sintaxis
							let errors = xhr.responseJSON.errors;
							
							// Muestra los errores en el formulario
							$.each(errors, function(key, errorMessages) {
								let input = form.find(`[name="${key}"]`);
								input.addClass('is-invalid');
								input.after(`<div class="invalid-feedback">${errorMessages[0]}</div>`);
							});
						} else if (xhr.status === 404) {
							// Manejo de error 404 sin registros de pasajero
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							} else {
								errorMessage = 'El registro solicitado no fue encontrado.';
							}
						} else if (xhr.status === 500) {
							// Manejo de error 500 trae el error del catch
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							} else {
								errorMessage = 'Ocurrió un error interno del servidor. Por favor, intenta de nuevo más tarde.';
							}
						}
						// Muestra el mensaje de error
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMessage,
						});
				}
			});
		});
	</script>
	@endsection