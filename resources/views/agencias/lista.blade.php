@extends('layouts.logeado')

@section('sub_title', 'Agencias de '. $empresa->RAZON_SOCIAL)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table  table-bordered" id="tab_listar">
					<thead>
						<tr>
							<th>Nombre</th>
							<th>Identificación</th>
                            <th>Dirección</th>
							<th>Teléfono</th>
						</tr>
					</thead>
					<tbody>
						@forelse($agencias as $agencia)
							<tr>
								<td>{{ $agencia->NOMBRE }}</td>
								<td>{{ $agencia->NRO_IDENTIFICACION}}</td>
								<td>{{ $agencia->DIRECCION }}</td>
								<td>{{ $agencia->TELEFONO }}</td>
								<td>
									@if ($usuario->roles_id == 1)
										<a href="{{ route('agencias.editar', ['agencia' => $agencia->TERCERO . '_' . $agencia->CODIGO]) }}" class="btn btn-warning btn-sm">Editar</a>
										@if ($agencia->sivalera == 1)
											<button value="{{ route('agencias.valesxagencia', ['agencia' => $agencia->TERCERO . '_' . $agencia->CODIGO]) }}" class="btn btn-success btn-sm">Exportar vales</button>
										@endif
									@else
										@if ($usuario->modulos[5]->pivot->editar == 1)
											<a href="{{ route('agencias.editar', ['agencia' => $agencia->TERCERO . '_' . $agencia->CODIGO]) }}" class="btn btn-warning btn-sm">Editar</a>
											@if ($agencia->sivalera == 1)
												<button value="{{ route('agencias.valesxagencia', ['agencia' => $agencia->TERCERO . '_' . $agencia->CODIGO]) }}" class="btn btn-success btn-sm">Exportar vales</button>
											@endif
										@endif
									@endif
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="4">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).on('click', '.btn-success', function(e){
			var direccion = e.target.value;	
			$.ajax({
                method: "GET",
                url: direccion
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

                filename = "Vales.xlsx";
                csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();
                
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert("Error consultando la base de datos");
            });         		       
		});

	</script>
@endsection