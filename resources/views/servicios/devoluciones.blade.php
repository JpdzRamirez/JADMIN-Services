@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Devoluciones. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Devoluciones')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
				<div class="align-center" style="display: inline">
					<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
					<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
				</div>
			
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>Fecha</th>
                            <th>ID servicio</th>
                            <th>Conductor</th>
                            <th>Justificación conductor</th>
                            <th>Operador</th>
							<th>Justificación operador</th>
						</tr>
						
						<tr>
							<th><form id="formfecha"  method="GET" class="form-inline" action="/servicios/devoluciones/filtrar"><input type="text" name="fecha" id="fecha" class="form-control" autocomplete="off" required></form></th>
							<th></th>
							<th><form method="GET" class="form-inline" action="/servicios/devoluciones/filtrar"><input type="text" name="conductor" class="form-control" required></form></th>
                            <th></th>
                            <th><form method="GET" class="form-inline" action="/servicios/devoluciones/filtrar"><input type="text" name="operador" class="form-control" required></form></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($devoluciones as $devolucion)
							<tr>
                                <td>{{ $devolucion->fecha }}</td>
                                <td>{{ $devolucion->servicios_id }}</td>
                                <td>{{ $devolucion->servicio->cuentac->conductor->NUMERO_IDENTIFICACION}}, {{ $devolucion->servicio->cuentac->conductor->NOMBRE}}</td>
                                <td>{{ $devolucion->razon }}</td>
                                <td>{{ $devolucion->user->identificacion}}, {{ $devolucion->user->nombres}}</td>
								<td>{{ $devolucion->justificacion }}</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($devoluciones,'links'))
					{{ $devoluciones->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script type="text/javascript" src="/js/moment.min.js"></script>
	<script type="text/javascript" src="/js/daterangepicker.js"></script>
	<script>

		$(document).ready(function () {
			$("#fecha").daterangepicker({
				autoUpdateInput: false,
    			timePicker: true,
				timePicker24Hour: true,			
    			locale: {
          			cancelLabel: 'Clear'
     			}				
  			});
		});

		$("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formfecha").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});
	
		function toexcel(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/servicios/devoluciones/exportar",
				data: { 'filtro': $("#filtro").val()}
			})
			.done(function (data, textStatus, jqXHR) {
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);

				var csvFile;
				var downloadLink;

				filename = "Devoluciones.xlsx";
				csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.close()		
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
		}
	</script>
@endsection
@section('sincro')
    <script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection