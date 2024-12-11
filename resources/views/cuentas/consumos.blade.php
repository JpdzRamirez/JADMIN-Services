@extends('layouts.logeado')

@section('sub_title', 'Historial de '. $cuenta->conductor->NOMBRE)

@section('sub_content')
<div class="card">
    <div class="card-body">
        <div class="align-center" style="display: inline">
            <button data-toggle="modal" data-target="#Modal" class="btn open-modal" style="background-color: #00965e; margin-left: 5px; float: right">
                <i class="fa fa-file-excel-o" aria-hidden="true"></i>
            </button>
            <p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
        </div>
        <br>
        <br>
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="/historial/recargas/{{$cuenta->id}}">Historial de Recargas</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="/historial/consumos/{{$cuenta->id}}">Historial de Consumos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/historial/transacciones/{{$cuenta->id}}">Historial de Transacciones</a>
              </li>
        </ul>

        <div class="tab-content">
            <div id="home" class="tab-pane fade" style="min-height: 500px"><br>
                    
             </div>
            <div id="menu1" class="tab-pane active" style="min-height: 500px"><br>
                <div class="table-responsive" id="listar">
                    <table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha de consumo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transacciones as $transaccion)
                            <tr>
                                <td>{{ $transaccion->id}}</td>
                                <td>{{ $transaccion->fecha}}</td>
                                <td>$-{{ number_format($transaccion->valor)}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(method_exists($transacciones,'links'))
                        {{ $transacciones->links() }}
                    @endif
                </div>
            </div>
            <div id="menu2" class="tab-pane fade" style="min-height: 500px"><br>
                
             </div>
          </div>
        </div> 
    </div>
</div>
@endsection

@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="width: 40%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Exportar</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row form-group">
                    <label for="desde" class="col-md-3 label-required">Desde</label>                         
                    <div class="col-md-9">
                        <input type="date" name="desde" id="desde" class="form-control">	
                    </div>
                </div>
                <div class="row form-group">
                    <label for="hasta" class="col-md-3 label-required">Hasta</label>                         
                    <div class="col-md-9">
                        <input type="date" name="hasta" id="hasta" class="form-control">
                    </div>
                </div>					
            </div>
            <div class="modal-footer">
                <button type="button" onclick="toexcel();" class="btn btn-success">Enviar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>

    function toexcel() {

        if($("#desde").val() != ""){
            $.ajax({
                method: "GET",
                url: "/transacciones/{{$cuenta->id}}/exportar",
                data: { 'tipo': 'Consumos',     
                        'desde': $("#desde").val(),
                        'hasta': $("#hasta").val()
                    }
                }).done(function (data, textStatus, jqXHR) {
                    const byteCharacters = atob(data);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);

                    var csvFile;
                    var downloadLink;

                    filename = "Historial de Consumos.xlsx";
                    csvFile = new Blob([byteArray], { type: 'application/vnd.ms-excel' });
                    downloadLink = document.createElement("a");
                    downloadLink.download = filename;
                    downloadLink.href = window.URL.createObjectURL(csvFile);
                    downloadLink.style.display = "none";
                    document.body.appendChild(downloadLink);
                    downloadLink.click();

                    $("#Modal").modal("hide");

            }).fail(function (jqXHR, textStatus, errorThrown) {
                Swal.fire(
                    'Error',
                    textStatus,
                    'error'
                );
            });
        }else{
            Swal.fire(
                'Error',
                'Debe ingresar una fecha de inicio',
                'error'
            );
        }
    }
</script>
@endsection