<html>
    <head></head>
    <body>
        <table style="width: 100%">
            <thead>
                <tr style="background-color: #1967a9; height: 2em;">
                    <th colspan="2" style="color: white; font-size: 2em">Vale para transporte asignado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Beneficiario</th>
                    <td>{{$vale->beneficiario}}</td>
                </tr>
                <tr>
                    <th>Código</th>
                    <td>{{$vale->codigo}}</td>
                </tr>
                <tr>
                    <th>Contraseña</th>
                    <td>{{$vale->clave}}</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>