@extends('layouts.logeado')

@if (isset($filtro))
    @section('sub_title', 'Historial cajas. Filtro: ' . $filtro)
@else
    @section('sub_title', 'Historial cajas')
@endif

@section('sub_content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive" id="listar" style="min-height: 500px">
            <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Fecha apertura</th>
                        <th>Fecha cierre</th>
                        <th>Total recargas</th>
                        <th>Total ventas</th>
                        <th>Usuario cierre</th>
                    </tr>
                    <tr>
                        <form method="GET" id="formfiltro" class="form-inline" action="/sucursales/cajas/filtrar"></form>
                        <th>
                            @if (!empty($c1))
                                <input type="date" autocomplete="off" value="{{$c1}}" form="formfiltro" id="apertura" name="apertura" class="form-control" onchange="this.form.submit()">
							@else
							    <input type="date" autocomplete="off" form="formfiltro" name="apertura" id="apertura" class="form-control" onchange="this.form.submit()">
							@endif
                        </th>
                        <th>
                            @if (!empty($c2))
                                <input type="date" autocomplete="off" value="{{$c2}}" form="formfiltro" id="cierre" name="cierre" class="form-control" onchange="this.form.submit()">
							@else
							    <input type="date" autocomplete="off" form="formfiltro" name="cierre" id="cierre" class="form-control" onchange="this.form.submit()">
							@endif
                        </th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cajas as $caja)
                        <tr>
                            <td>{{ $caja->apertura}}</td>
                            <td>{{ $caja->cierre}}</td>
                            <td>{{ number_format($caja->totalrecargas) }} </td>
                            <td>{{ number_format($caja->totalventas) }} </td>
                            <td>{{ $caja->responsable }}</td>
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="4">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($cajas,'links'))
                {{ $cajas->links() }}
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
</script>
@endsection
