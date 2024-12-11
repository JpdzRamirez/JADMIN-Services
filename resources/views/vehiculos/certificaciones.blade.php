@extends('layouts.logeado')

@section('sub_title', 'Certificaciones de vehículos')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <form method="POST" action="/vehiculos/certificaciones">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <p>
                            <label for="modelo">Modelo: </label>
                            <input type="text" name="modelo" id="amount" readonly style="border:0; color:#1967a9; font-weight:bold; font-size: large">
                        </p>
                      <div id="slider-range"></div>
                      
                    </div>
                    <div class="form-group col-md-6">
                      <label for="tipo">Tipo de vehículo</label>
                      <select name="tipo" id="tipo" class="form-control">
                          <option value="Todos">Todos</option>
                          <option value="0">Taxi</option>
                          <option value="1">Servicio especial</option>
                      </select>
                    </div>
                  </div>

                  <hr>

                  <div class="text-center">
                      <button type="submit" class="btn btn-dark">Generar</button>
                  </div>
              </form>
		</div>
	</div>
@endsection
@section('script')
    <script>
        $(function() {
            $( "#slider-range" ).slider({
            range: true,
            min: 1986,
            max: 2021,
            values: [ 1986, 2021 ],
            slide: function( event, ui ) {
                $( "#amount" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
            }
            });

            $("#load").hide();

            $("#amount").val( $( "#slider-range" ).slider( "values", 0 ) + " - " + $( "#slider-range" ).slider( "values", 1 ) );

            });
    </script>
@endsection