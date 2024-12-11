<html>
    <head></head>
    <body>
        <table style="width: 100%; border: 1px solid; border-collapse: collapse">
            <thead>
                <tr style="background-color: #1967a9; height: 4em;">
                    <th style="color: white; font-size: 2em" colspan="2">Solicitud de Documento</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border: 1px solid">
                    <th>Tipo de Solicitud:</th>
                    <td style="text-align: center">{{$solicitud}}</td>
                </tr>
                <tr style="border: 1px solid">
                    <th>Propietario:</th>
                    @if (isset($cuentac))
                        <td style="text-align: center">{{$cuentac->conductor->NOMBRE}}, {{$cuentac->conductor->NUMERO_IDENTIFICACION}}</td>
                    @elseif(isset($tercero))
                        <td style="text-align: center">{{$tercero->RAZON_SOCIAL}}, {{$tercero->NRO_IDENTIFICACION}}</td>
                    @endif
                    
                </tr>
                <tr style="border: 1px solid">
                    <th>Placa:</th>
                    <td style="text-align: center">{{$placa}}</td>
                </tr>
                @if ($solicitud == "Constancia de Ingresos")
                    <tr style="border: 1px solid">
                        <th>Monto:</th>
                        <td style="text-align: center">$ {{number_format($monto)}}</td>
                    </tr>
                    <tr style="border: 1px solid">
                        <th>Dirigido a:</th>
                        <td style="text-align: center">{{$dirigido}}</td>
                    </tr>
                @endif
                <tr style="border: 1px solid">
                    <th>Email:</th>
                    <td style="text-align: center">{{$email}}</td>
                </tr>
                <tr style="border: 1px solid">
                    <th>Celular:</th>
                    <td style="text-align: center">{{$celular}}</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>