<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Intereses</title>
        <style>
            
            table, table th, table td{
                border: 1px solid black;
                border-collapse: collapse;
                text-align: center;
                height: 30px;
            }

            @page {
                margin-top: 1em;
                margin-left: 1.6em;
            }
        </style>
    </head>
    <body>
        <div style="margin-top: 50px; margin-bottom: 50px">
            <p style="display: inline; margin: 30px"><b>PLACA: </b>{{$placa}}</p>&#09
            <p style="display: inline; margin: 30px"><b>PROPIETARIO: </b>{{$propietario}}</p>
        </div>
        
        <br>

        <table style="width: 100%">
            <thead>
                <tr>
                    <th>MES</th>
                    <th>CAPITAL</th>
                    <th>INTERES</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totcap = 0;
                    $totint = 0;
                    $total = 0;
                @endphp
                @foreach ($cuotas as $cuota)
                    @php
                        $totcap = $totcap + $cuota->capital;
                        $totint = $totint + $cuota->interes;
                        $total = $total + $cuota->capital + $cuota->interes;
                    @endphp
                    <tr>
                        <td><b>{{$cuota->mes}}, {{$cuota->concepto}}</b></td>
                        <td>${{number_format($cuota->capital, 2)}}</td>
                        <td>${{number_format($cuota->interes, 2)}}</td>
                        <td>${{ number_format($cuota->capital + $cuota->interes, 2)}}</td>
                    </tr>
                @endforeach
                
                <tr>
                    <td><b>TOTAL:</b></td>
                    <td><b>${{ number_format($totcap, 2)}}</b></td>
                    <td><b>${{ number_format($totint, 2)}}</b></td>
                    <td><b>${{ number_format($total, 2)}}</b></td>
                </tr>
                <tr style="background-color: #b4c6e7">
                    <td colspan="2"><b>TOTAL, DEUDA CAPITAL + INTERESES</b></td>
                    <td colspan="2"><b>${{ number_format($total, 2)}}</b></td>
                </tr>
                <tr style="background-color: #b4c6e7">
                    @php
                        $hon = $totcap*0.1;
                    @endphp
                    <td colspan="2"><b>TOTAL HONORARIOS PROFESIONALES</b></td>
                    <td colspan="2"><b>${{ number_format($hon, 2)}}</b></td>
                </tr>
                <tr style="background-color: #b4c6e7">
                    <td colspan="2"><b>TOTAL</b></td>
                    <td colspan="2"><b>${{ number_format($total+$hon, 2)}}</b></td>
                </tr>
            </tbody>
        </table>          
    </body>
</html>