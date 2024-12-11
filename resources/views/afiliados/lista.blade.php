@extends('layouts.logeado')

@if (isset($filtro))
	@section('sub_title', 'Lista de Afiliados. Filtro: ' . $filtro)
@else
	@section('sub_title', 'Lista de Afiliados')
@endif

@section('sub_content')

<div class="card">
    <div class="card-body">
            @isset($filtro)
                <input type="hidden" id="filtroc" value="{{ implode(",", $filtroc)}}">
                <input type="hidden" id="filtrop" value="{{ implode(",", $filtrop)}}">
            @endisset
				<div class="align-center" style="display: inline">
					<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
					<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
				</div>
            <div class="table-responsive" id="listar" style="min-height: 500px">
                    <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th>Perfil</th>
                                <th>Identificación</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Email</th>
                                <th>Estado</th>
                            </tr>
                            <tr>
                                <form method="GET" id="formfiltro" class="form-inline" action="/afiliados/filtrar"></form>
                                    <th>                              
                                        @if (!empty($c1))
                                            <select name="perfil" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                                <option value="">Todos</option>
                                                @if ($c1 == "Propietario")
                                                    <option value="Propietario" selected>Propietario</option>
                                                    <option value="Conductor">Conductor</option>
                                                @else
                                                    <option value="Propietario">Propietario</option>
                                                    <option value="Conductor" selected>Conductor</option>
                                                @endif
                                            </select>
                                        @else
                                            <select name="perfil" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                                <option value="">Todos</option>
                                                <option value="Propietario">Propietario</option>
                                                <option value="Conductor">Conductor</option>
                                            </select>
                                        @endif
                                    </th>
                                    <th>
                                        @if (!empty($c2))
                                            <input type="text" form="formfiltro" value="{{$c2}}" name="identificacion" class="form-control filt" >
                                        @else
                                            <input type="text" form="formfiltro" name="identificacion" class="form-control filt">
                                        @endif                                
                                    </th>
                                    <th>
                                        @if (!empty($c3))
                                            <input type="text" form="formfiltro" value="{{$c3}}" name="nombre" class="form-control filt">
                                        @else
                                            <input type="text" form="formfiltro" name="nombre" class="form-control filt">
                                        @endif
                                    </th>
                                    <th>
                                        @if (!empty($c4))
                                            <input type="text" form="formfiltro" value="{{$c4}}" name="celular" class="form-control filt">
                                        @else
                                            <input type="text" form="formfiltro" name="celular" class="form-control filt">
                                        @endif
                                    </th>
                                    <th>
                                        @if (!empty($c5))
                                            <input type="email" form="formfiltro" value="{{$c5}}" name="email" class="form-control filt" >
                                        @else
                                            <input type="email" form="formfiltro" name="email" class="form-control filt" > 
                                        @endif
                                    </th>
                                    <th>
                                        @if (!empty($c6))
                                            <select name="estado" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                                <option value="">Todos</option>
                                            @if ($c6 == "Activo")
                                                <option value="Activo" selected>Activo</option>
                                                <option value="Inactivo">Inactivo</option>
                                                <option value="Bloqueado">Bloqueado</option>                                             
                                            @elseif($c6 == "Inactivo")
                                                <option value="Activo">Activo</option>
                                                <option value="Inactivo" selected>Inactivo</option>
                                                <option value="Bloqueado">Bloqueado</option>
                                            @else
                                                <option value="Activo">Activo</option>
                                                <option value="Inactivo">Inactivo</option>
                                                <option value="Bloqueado" selected>Bloqueado</option> 
                                            @endif
                                            </select>
                                        @else
                                            <select name="estado" form="formfiltro" class="form-control" onchange="this.form.submit()">
                                                <option value="">Todos</option>
                                                <option value="Activo">Activo</option>
                                                <option value="Inactivo">Inactivo</option>
                                                <option value="Bloqueado">Bloqueado</option>
                                            </select>
                                        @endif              
                                    </th>
                                </tr>
                        </thead>
                        <tbody>
                            @foreach($propietarios as $propietario)
                            @if ($propietario->TERCERO != 0)
                                <tr>
                                    <td>Propietario</td>
                                    <td>{{ $propietario->NRO_IDENTIFICACION }}</td>
                                    <td>{{ $propietario->PRIMER_NOMBRE }} {{ $propietario->PRIMER_APELLIDO }} {{ $propietario->SEGUNDO_APELLIDO }}</td>
                                    <td>{{ $propietario->CELULAR }}</td>
                                    <td>{{ $propietario->EMAIL }}</td>
                                    <td>-</td>
                                    <td>
                                        <a href="{{ route('propietarios.vehiculos', ['propietario' => $propietario->TERCERO]) }}" class="btn btn-info btn-sm">Vehiculos</a>
                                        <a href="{{ route('propietarios.editar', ['propietario' => $propietario->TERCERO]) }}" class="btn btn-warning btn-sm">Editar</a>                        
                                    </td>
                                </tr>
                                
                            @endif
                            @endforeach

                            @foreach($conductores as $conductor)
                                <tr>
                                    <td>Conductor</td>
                                    <td>{{ $conductor->NUMERO_IDENTIFICACION}}</td>
                                    <td>{{ $conductor->NOMBRE }}</td>
                                    <td>{{ $conductor->CELULAR }}</td>
                                    <td>{{ $conductor->EMAIL }}</td>
                                    <td>
                                        @if ($conductor->cuentac != null)
                                            @if ($conductor->cuentac->estado == "Bloqueado")
                                                Bloqueado
                                            @elseif($conductor->cuentac->estado == "Inactivo")
                                                Inactivo
                                            @else
                                                Activo
                                            @endif
                                        @else
                                            Sin cuenta corriente
                                        @endif
                                   
                                    </td>                                
                                    <td>
                                        <a href="{{ route('conductores.vehiculos', ['conductor' => $conductor->CONDUCTOR]) }}" class="btn btn-info btn-sm">Vehiculos</a>
                                         @if ($conductor->cuentac != null)
                                              <a href="{{ route('conductores.editar', ['conductor' => $conductor->cuentac->id]) }}" class="btn btn-warning btn-sm">Editar</a>
                                         @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(method_exists($propietarios,'links'))
                        {{ $propietarios->links() }}
                    @elseif(method_exists($conductores,'links'))
                        {{ $conductores->links() }}
                    @endif
                </div>
    </div>
</div>
	
@endsection

@section('script')
    <script>

        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formfiltro").submit();
    		}
		});

        function toexcel(){
            Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

            $.ajax({
                method: "GET",
                url: "/afiliados/exportar",
                data: { 'filtroc': $("#filtroc").val(), 'filtrop': $("#filtrop").val()}
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

                filename = "Afiliados.xlsx";
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