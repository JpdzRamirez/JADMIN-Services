@extends('layouts.logeado')



@section('sub_title', 'Dashboard')



@section('sub_content')

	<div class="row">

			<div class="col-md-3">

				<div class="card text-center m-b-30">

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

		

	</div>



	<div class="row" style="margin-top:30px">

		<div class="col-md-6">

			<div class="card">

				<div class="card-body">

					<h4 class="mt-0 m-b-30 header-title" style="color: darkblue">Servicios del Mes</h4>

					<div id="distribucion"></div>

				</div>

			</div>

		</div>	



	<div class="col-md-6">

			<div class="card">

				<div class="card-body">

				<h4 class="mt-0 m-b-30 header-title" style="color: darkblue">Top Conductores</h4>

				<ol class="activity-feed mb-0">

					@foreach($cuentasc as $cuentac)

						<li class="feed-item">

                            <div class="row">

                                <div class="col-md-8">

                                        <b>{{$cuentac->conductor->NOMBRE}}</b><br>

                                        <span class="activity-text">

                                            @for ($i = 0; $i < $cuentac->amarillas; $i++)

                                                <span style="color: yellow; font-size: 20pt">★</span>

                                            @endfor

                                            @for ($i = 0; $i < $cuentac->grises; $i++)

                                                <span style="color: gray; font-size: 20pt">★</span>

                                            @endfor

                                        </span><br>

                                        <span class="activity-text">Servicios: {{ $cuentac->topservicios }}</span>

                                </div>

                                <div class="col-md-4">

                                    @if ($cuentac->foto != null)

                                        <img src="data:image/*;base64, {{$cuentac->foto}}" alt="Foto" width="50%">

                                    @else

                                        <img src="/img/foto.png" alt="Foto" width="50%">

                                    @endif

                                   

                                </div>

                            </div>

							

						</li>

					@endforeach	

				</ol>

				</div>

			</div>

	</div> 

</div>



<!-- <div class="row" style="margin-top:30px">

    <div class="col-md-7">

        <div class="card">

            <div class="card-body">

                <h4 class="mt-0 m-b-30 header-title">Novedades abiertas</h4>

                <div class="table-responsive">

                    <table class="table m-t-20 mb-0 table-vertical">

                        <tbody>							

                            @foreach($novedades as $novedad)

                                <tr>

                                    <td>

                                        {{$novedad->detalle}}

                                    </td>

                                    <td>

                                        {{ $novedad->tiponovedad->nombre}}<br>

                                        <span class="txtgris">Tipo novedad</span>

                                    </td>

                                    <td>

                                        {{$novedad->servicios_id}}<br>

                                        <span class="txtgris">ID servicio</span>				

                                    </td>

                                    <td>

                                        @if ($novedad->servicio->cuentac != null)

                                            {{$novedad->servicio->cuentac->conductor->NOMBRE}}

                                        @else

                                            No asignado

                                        @endif

                                        <br>

                                        <span class="txtgris">Conductor</span>				

                                    </td>

                                    <td>

                                        <a href="/novedades/{{$novedad->id}}/editar" class="btn btn-sm btn-info">Revisar <span class="fa fa-eye"></span></a><br>

                                        <span class="txtgris">Atender</span>

                                    </td>

                                </tr>

                            @endforeach					

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div> -->

<div class="row" style="margin-top: 30px">

	<div class="col-md-12">

	<div class="card">

		<div class="card-body">

			<h4 class="mt-0 m-b-30 header-title" style="color: darkblue">Historial de servicios</h4>

			<div id="historial"></div>

		</div>

	</div>

</div>

</div>

@endsection



@section('script')

    <script src="{{ mix('/js/apexchart/apexcharts.js') }}"></script>

	<script>



		$(document).ready(function () {

			var options = {

            chart: {

                width: 450,

                type: 'pie',

            },

            labels: ['Efectivo', 'Vale electrónico', 'Vale físico'],

            series: [{{implode(",", $valores)}}],

            responsive: [{

                breakpoint: 480,

                options: {

                    chart: {

                        width: 200

                    },

                    legend: {

                        position: 'bottom'

                    }

                }

            }]

        }



        var chart = new ApexCharts(

            document.querySelector("#distribucion"),

            options

        );



        chart.render();

		});



		var meses = "{{implode(",", $meses)}}";



		var options = {

            chart: {

                height: 350,

                type: 'line',

                zoom: {

                    enabled: false

                }

            },

            series: [{

                name: "Servicios",

                data: [{{implode(",", $vmeses)}}],

            }],

            dataLabels: {

                enabled: false

            },

            stroke: {

                curve: 'straight'

            },

            grid: {

                row: {

                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns

                    opacity: 0.5

                },

            },

            xaxis: {

                categories: meses.split(','),

            }

        }



        var chart = new ApexCharts(

            document.querySelector("#historial"),

            options

        );



        chart.render();

		

	</script>

@endsection