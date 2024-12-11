@extends('layouts.logeado')

@if (isset($filtro))
@section('sub_title', 'Transacciones de '. $cuentae->agencia->valera->nombre . '. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
@section('sub_title', 'Transacciones de '. $cuentae->agencia->valera->nombre)
@endif

@section('sub_content')
<div class="card">
    <div class="card-body">
        @isset($filtro)
        <input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
        @endisset
        <div class="align-center" style="display: inline">
            <button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right"
                onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>
            <p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
        </div>
        <div class="table-responsive" id="listar" style="min-height: 500px">
            <table class="table  table-bordered" id="tab_listar">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo transacción</th>
                        <th>Valor</th>
                        <th>Servicio</th>
                        <th>Comentarios</th>
                    </tr>
                    <tr>
                        <th>
                            <form method="GET" class="form-inline" action="/cuentas_empresas/transacciones/{{$cuentae->id}}/filtrar"><input type="number"
                                    name="id" class="form-control" required></form>
                        </th>
                        <th>
                            <form method="GET" class="form-inline" action="/cuentas_empresas/transacciones/{{$cuentae->id}}/filtrar"><input type="date"
                                    name="fecha" class="form-control" onchange="this.form.submit()" required></form>
                        </th>
                        <th>
                            <form method="GET" class="form-inline" action="/cuentas_empresas/transacciones/{{$cuentae->id}}/filtrar">
                                <select name="tipo" class="form-control" onchange="this.form.submit()">
                                    <option value="sinfiltro"></option>
                                    <option value="Servicio con Vale">Servicio con Vale</option>
                                    <option value="Cobro vales">Cobro vales</option>
                                </select>
                            </form>
                        </th>
                        <th>
                            <form method="GET" class="form-inline" action="/cuentas_empresas/transacciones/{{$cuentae->id}}/filtrar"><input type="number"
                                    name="valor" class="form-control" required></form>
                        </th>
                        <th>
                            
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->id}}</td>
                        <td>{{ $movimiento->fecha}}</td>
                        <td>{{ $movimiento->tipo}}</td>
                        <td>${{ number_format($movimiento->valor)}}</td>
                        <td>@if ($movimiento->vale != null)
                                <a href="/servicios/detalles/{{$movimiento->vale->servicio->id}}" target="_blank" class="btn btn-sm btn-default">Detalle servicio</a>
                             @endif
                        </td>
                        <td>{{ $movimiento->comentarios}}</td>
                    </tr>
                    @empty
                    <tr class="align-center">
                        <td colspan="4">No hay datos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($movimientos,'links'))
            {{ $movimientos->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
<script>

    function toexcel() {

        $.ajax({
            method: "GET",
            url: "/cuentas_empresas/transacciones/{{$cuentae->id}}/exportar",
            data: { 'filtro': $("#filtro").val() }
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

                filename = "Transacciones empresa.xlsx";
                csvFile = new Blob([byteArray], { type: 'application/vnd.ms-excel' });
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