@extends('layouts.logeado')

@section('sub_title', 'Pagos realizados')

@section('sub_content')
<div class="card">
    <div class="card-body">
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Valor</th>
                        <th>Conductor</th>
                    </tr>
                    <tr>
                        <form method="GET" id="formfiltro" class="form-inline" action="/sucursales/efectivo/movimientos"></form>
                        <th>
                            <input type="date" value="{{$fecha->format('Y-m-d')}}" form="formfiltro" id="fecha" name="fecha" class="form-control" onchange="this.form.submit()">
                        </th>
                        <th></th>
                        <th>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transacciones as $transaccion)
                        <tr>
                            <td>{{ $transaccion->fecha}}</td>
                            <td>
                                ${{ number_format($transaccion->valor)}}</td>                          
                            <td>
                                @if ($transaccion->cuentac != null)
                                    {{$transaccion->cuentac->conductor->NUMERO_IDENTIFICACION}}-{{$transaccion->cuentac->conductor->NOMBRE}}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr class="align-center">
                            <td colspan="3">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($transacciones,'links'))
                {{ $transacciones->links() }}
            @endif
    </div>
</div>
@endsection
