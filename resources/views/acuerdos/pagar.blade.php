@extends('layouts.logeado')

@section('style')
    <style>
        #cuotas label{
            margin-top: 1rem;
        }
    </style>
@endsection
@section('sub_title', 'Registrar Pago de Acuerdos')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
            <p style="text-align: center; color: navy; font-size: medium">Digitar cédula del deudor ó placa del vehículo</p>
            <form action="/acuerdos/registrar_pago" method="get" id="formpago">
                <div class="row form-group">
                    <label for="identificacion" class="col-md-2 text-center" style="font-size: large">Identificación:</label>
                    <div class="col-md-4">
                        <input type="number" name="identificacion" id="identificacion" class="form-control">
                    </div>

                    <label for="placa" class="col-md-2 text-center" style="font-size: large">Placa:</label>
                    <div class="col-md-4">
                        <input type="text" name="placa" id="placa" class="form-control">
                    </div>
                </div>
                <br>
                <div class="text-center">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search" aria-hidden="true"></i> Consultar</button>
                </div>
            </form>

            @if (isset($busq))
            <br>
                <div id="cuotas" style="margin-bottom: 50px">           
                    <h4 style="color: navy">Propietario</h4>
                    <hr>
                        <h5 class="form-control col-md-6 col-xs-12">{{ $tercero->PRIMER_NOMBRE }} {{ $tercero->PRIMER_APELLIDO }}</h5>
                        <h5 class="form-control col-md-6 col-xs-12">{{ number_format($tercero->NRO_IDENTIFICACION, 0, ",", ".") }} </h5>
                    
                    <br>
                    <h4 style="color: navy">Acuerdos vigentes</h4>
                    <hr>

                    @foreach ($acuerdos as $acuerdo)
                        <div class="row form-group">
                            <label class="col-md-1">ID Acuerdo</label>
                            <h5 class="col-md-2 form-control">{{ $acuerdo->id }}</h5>
                            <label class="col-md-1">Placa</label>
                            <h5 class="col-md-2 form-control">{{ $acuerdo->placa }}</h5>
                            <label class="col-md-2 text-center">Fecha acuerdo</label>
                            <h5 class="col-md-2 form-control">{{ $acuerdo->fecha }}</h5>
                        </div>
                        <table class="table table-bordered" style="table-layout: fixed">
                            <thead>
                                <tr>
                                    <th># Cuota</th>
                                    <th>Valor</th>
                                    <th>Fecha vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($acuerdo->cuotasAll as $cuota)
                                    @if($cuota->estado == "Vencida")
                                        <tr style="background-color: lightcoral">
                                    @else          
                                        <tr>       
                                    @endif
                                        <td>{{ $cuota->numero }}</td>
                                        <td>${{ number_format($acuerdo->pago_mensual, 2, ",", ".") }}</td>
                                        <td>{{ $cuota->fecha_vencimiento }}</td>
                                        <td style="background-color: white">
                                            <button type="button" class="btn btn-sm btn-success" onclick="confirmarPago({{$acuerdo->pago_mensual}}, {{$cuota->id}})">Pagar cuota</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>                       
                    @endforeach
                </div>
            @endif          
		</div>
	</div>
@endsection
@section('script')
    <script>
        var token;
        @if(isset($busq))
            $(document).ready(function () {
                $("html,body").animate({scrollTop: $("#cuotas").offset().top}, 2000);
                token = "{{csrf_token()}}";
            });
            var ideprop = {{$tercero->NRO_IDENTIFICACION}};
        @endif

        function confirmarPago(valor, cuota) {
            Swal.fire({
                    title: 'Confirmar pago de $ ' + valor.toLocaleString(),
                    showCancelButton: true,
                    confirmButtonText: 'Registrar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/acuerdos/pagar_cuota",
                            data: {'cuota': cuota, 'idpropietario': ideprop, '_token' : token},
                            dataType : 'json'
                        }).done(function(data){
                            Swal.fire({
                                title: data.mensaje,
                                icon: data.estado,
                                confirmButtonText: 'OK',
                                isDismissed: false
                            }).then((result) => {
                                if(result.isConfirmed){
                                    if(data.estado == "success"){
                                        $(location).attr("href", data.direccion);
                                    }                  
                                }
                            });
                        }).fail(function () {  
                            Swal.fire(
                                'Error',
                                'Ha ocurrido un error registrando el pago',
                                'error'
                            );
                        });
                    }
            });
        }     
    </script>
@endsection