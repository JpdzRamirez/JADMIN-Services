@extends('layouts.logeado')

@section('sub_title', 'Pasajeros')
@section('style')
    <style>
        .popover-header {
            color: #ffff;
            background: #3A393B;
            font-size: 15px;
        }

        #importButton:disabled {
            pointer-events: none;
            opacity: 0.65;
        }

        #importButton:disabled:hover {
            background-color: #28a745;
        }
    </style>
@endsection
@section('sub_content')
    <div class="card">
        <div class="card-body">
			@if (session('actualizacion'))
				<div class="alert alert-success">
					<h5>{{ session('actualizacion')}}</h5>
				</div>
			@endif
			@if($errors->first('sql') != null)
			<div class="alert alert-danger" style="margin:5px 0">
				<h6>{{$errors->first('sql')}}</h6>
			</div>				
			@endif
            <div class="align-center" style="display: inline">
                <a href="/pasajeros/CRM/nuevo" class="btn btn-dark btn-sm">Nuevo Pasajero</a>
            </div>
            <div class="align-center" style="display: inline">
                @if (Auth::user()->roles_id == 4)
                    <button type="button" class="btn btn-dark btn-sm" onclick="plantilla();">Descargar plantilla</button>
                    <button type="button" data-toggle="modal" id="popoverModalButton" data-target="#modalImportarPasajeros" class="btn btn-dark btn-sm">Importar plantilla</button>
                @elseif ($usuario->roles_id == 1 || $usuario->modulos[9]->pivot->editar == 1)
                    <button type="button" class="btn btn-dark btn-sm" onclick="plantilla();">Descargar plantilla</button>
                    <button type="button" data-toggle="modal" id="popoverModalButton" data-target="#modalImportarPasajeros" class="btn btn-dark btn-sm">Importar plantilla</button>
                @endif
            </div>
            <div class="table-responsive" id="listar">
                <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                    <thead>
                        <tr>
                            <th>Identificación</th>
                            <th>Nombre</th>
                            <th>Celulares</th>
                            <th>Centro de costo</th>
                        </tr>
                        <tr>
                            <th>
                                <form method="GET" class="form-inline" action="/pasajeros/CRM/listar">
                                    <input type="number" value="{{ $identificacion }}" name="identificacion"
                                        class="form-control">
                                </form>
                            </th>
                            <th>
                                <form method="GET" class="form-inline" action="/pasajeros/CRM/listar">
                                    <input type="text" value="{{ $nombre }}" name="nombre" class="form-control">
                                </form>
                            </th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pasajeros as $pasajero)
                            <tr>
                                <td>{{ $pasajero->identificacion }}</td>
                                <td>{{ $pasajero->nombre }}</td>
                                <td>{{ $pasajero->celulares }}</td>
                                <td>Sub_Cuenta: {{ $pasajero->sub_cuenta }} Affe: {{ $pasajero->affe }}</td>
                                <td>
                                    <a href="/pasajeros/CRM/{{ $pasajero->id }}/editar"
                                        class="btn btn-warning btn-sm">Actualizar</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="align-center">
                                <td colspan="4">No hay datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if (method_exists($pasajeros, 'links'))
                    {{ $pasajeros->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
@section('modal')
    <div id="modalImportarPasajeros" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="min-width: 50%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Importar Pasajeros</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/pasajeros/CRM/importar" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="plantilla">Seleccionar plantilla</label>
                            </div>
                            <div class="col-md-8">
                                <input type="file" class="form-control" name="plantilla" id="plantilla"
                                    accept=".xls,.xlsx" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="importButton" disabled>Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('#popoverModalButton').popover({
            title: "Información",
            placement: 'top',
            content: "Importe aquí la plantilla actualizada",
            trigger: 'hover'
        });

        $('#modalImportarPasajeros').on('hidden.bs.modal', function() {
            const form = $(this).find("form");
            $("#importButton").attr("disabled", true);
            form.trigger('reset');
        });

        $("#plantilla").on('change', function() {
            $("#importButton").removeAttr("disabled");
        });

        function plantilla() {

            Swal.fire({
                title: '<strong>Exportando...</strong>',
                html: '<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
                showConfirmButton: false,
            });

            $.ajax({
                    method: "GET",
                    url: "/pasajeros/CRM/planilla"
                })
                .done(function(data, textStatus, jqXHR) {
                    const byteCharacters = atob(data);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);

                    let csvFile;
                    let downloadLink;

                    filename = "Plantilla_Pasajeros.xlsx";
                    csvFile = new Blob([byteArray], {
                        type: 'application/vnd.ms-excel'
                    });
                    downloadLink = document.createElement("a");
                    downloadLink.download = filename;
                    downloadLink.href = window.URL.createObjectURL(csvFile);
                    downloadLink.style.display = "none";
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    window.URL.revokeObjectURL(csvFile);

                    Swal.close();
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
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
