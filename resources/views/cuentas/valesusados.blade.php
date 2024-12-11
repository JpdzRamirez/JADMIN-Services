@extends('layouts.logeado')

@section('sub_title', 'Vales usados ' . $valera->nombre)

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<div class="align-center" style="display: inline">
						<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
						<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
				</div>
			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Valera</th>
							<th>Código vale</th>
							<th>Beneficiarios</th>
                            <th>Usuarios</th>
                            <th>Conductor</th>
                            <th>Placa</th>
                            <th>Unidades</th>
                            <th>Valor</th>
                            <th>Estado</th>
						</tr>
					</thead>
					<tbody>                       
                        @forelse ($valera->vales as $vale)
                            @if ($vale->estado == "Usado")
                            <tr>
								<td>{{ $vale->servicio->fecha }}</td>
								<td>{{ $vale->valera->nombre }}</td>
								<td>{{ $vale->codigo }}</td>
								<td>{{ $vale->beneficiario }}</td>
                                <td>{{ $vale->servicio->usuarios}}</td>
                                <td>{{ $vale->servicio->cuentac->conductor->NOMBRE}}</td>
                                <td>{{ $vale->servicio->placa}}</td>
                                <td>{{ $vale->servicio->unidades}}</td>
                                <td>{{ $vale->valor}}</td>
                                <td>{{ $vale->estado}}</td>
							</tr>
                            @endif							
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
    function toexcel(){

$.ajax({
    method: "GET",
    url: "/cuentas_empresas/exportarusados",
    data: { 'empresa': {{$tercero}}, 'agencia': "{{$codigo}}"}
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

    filename = "Vales usados.xlsx";
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
}
    </script>
@endsection