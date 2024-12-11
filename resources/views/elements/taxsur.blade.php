@extends('layouts.layout_taxsur')

@section('sub_title', 'Información General de la Empresa')

@section('sub_content')
	<div class="card" style="min-height: 500px">
		<div class="card-body">
            <div id="container-main">
                <div class="accordion-container">
                    <a href="#politicas" class="accordion-titulo">Políticas de la empresa<span class="toggle-icon"></span></a>
                    <div class="accordion-content" id="politicas">
                        <h5>Política Integrada</h5>
                        <hr>
                        <p>
                            TAXSUR S.A presta el servicio de transporte de pasajeros de forma segura, ágil y confiable, cumpliendo los requisitos legales aplicables, manteniendo un compromiso con la calidad del servicio, el mejoramiento continuo,  la prevención de enfermedades laborales y lesiones personales , para esto TAXSUR S.A. cuenta con un talento humano competente, con una  tecnología adecuada a sus procesos y con la infraestructura vehicular requerida, para  lograr así la satisfacción de las necesidades y expectativas de sus clientes.
                        </p>
                        <br>
                        <h5>Política del No Consumo de Alcohol, Tabaco y Sustancias Psicoactivas</h5>
                        <hr>
                        <p>
                            TAXSUR S.A., a través de la Gerencia General, reafirma su compromiso con la seguridad integral al interior de la empresa, promoviendo una cultura de no consumo  de  alcohol  y  sustancias  psicoactivas,  ya  que  éste  puede  afectar  el estado mental y fisiológico e influir negativamente en el desempeño laboral de los empleados y contratistas.
                        </p>
                        <p>
                            Esta política tendrá los siguientes objetivos:
                        </p>
                        <br>
                        <ul>
                            <li>Prohibir a los trabajadores y personal contratista de TAXSUR S.A., el consumo de   bebidas alcohólicas   y   drogas   psicoactivas   durante   la   ejecución   de actividades laborales, sean éstas en oficina o en campo.</li>
                            <li>Resaltar la responsabilidad de cada uno de los empleados de ejercer un mutuo control con los compañeros de trabajo e informar a su jefe inmediato a la mayor brevedad sobre el consumo de alcohol y/o de sustancias psicoactivas durante las actividades laborales, en la medida en que puede ponerse en riesgo la vida y seguridad de sus compañeros en el desarrollo de estas actividades.</li>
                            <li>Prohibir a los trabajadores y personal contratista que realicen actividades laborales bajo los efectos de sustancias psicoactivas o en estado de embriaguez.</li>
                            <li>Realizar campañas y actividades de prevención del tabaquismo, consumo de alcohol y sustancias psicoactivas, a través de programas de promoción de estilos de vida saludables.</li>                          
                        </ul>
                        <p>Esta política es coherente con la legislación vigente y el reglamento interno de trabajo, por lo tanto, cualquier conducta que vaya en contra de su cumplimiento se considera falta grave. Debe ser difundida entre el personal y contratistas de la empresa.</p>
                        <br>
                        <h5>Política de Seguridad Vial</h5>
                        <hr>
                        <p>Es compromiso de TAXSUR S.A., establecer actividades de promoción y prevención de incidentes en vía pública, por ello todos sus trabajadores y contratistas, son responsables en la participación de las actividades que desarrolle la empresa con el fin de disminuir la probabilidad de ocurrencia de incidentes que puedan afectar la integridad física, mental y social de los contratistas y sus trabajadores, la comunidad en general y el medio ambiente.</p>
                        <p>Para cumplir este propósito TAXSUR S.A., se basa en los siguientes parámetros:</p>
                        <br>
                        <ul>
                            <li>Cumplir con la reglamentación establecida en el Código Nacional de Tránsito Terrestre según la Ley 1383 de 2010, que se enmarca en principios de seguridad, calidad, la preservación de un ambiente sano y la protección del espacio público.</li>
                            <li>TAXSUR S.A., vigilará la responsabilidad de los trabajadores y contratistas en el mantenimiento preventivo y correctivo, con el objeto de mantener un desempeño óptimo de sus vehículos, estableciendo las medidas de control para evitar la ocurrencia de incidentes que puedan generar daños al individuo o a terceros.</li>
                            <li>Establecer estrategias de concientización a los trabajadores y contratistas, a través de capacitaciones de orientación a la prevención de incidentes de tránsito y respeto por las señales de tránsito vehicular, que permitan la adopción de conductas proactivas frente al manejo defensivo.</li>
                            <li>Los  trabajadores  y  contratistas  son  responsables  de  la  aplicación  de  las disposiciones establecidas y divulgadas por TAXSUR S.A., en el manual de seguridad vial.</li>
                            <li>La gerencia destinará los recursos financieros, humanos y técnicos necesarios para dar cumplimiento a la política.</li>
                        </ul>
                    </div>
                </div>

                <div class="accordion-container">
                    <a href="#trabajo" class="accordion-titulo">Reglamento interno de trabajo<span class="toggle-icon"></span></a>
                    <div class="accordion-content" id="trabajo">
                        Descarga el <a href="/taxsur/informacion_empresa/reglamento_interno_trabajo" target="_blank">Reglamento Interno de Trabajo <i class="fa fa-download" aria-hidden="true"></i></a>.
                    </div>
                </div>

                <div class="accordion-container">
                    <a href="#seguridad" class="accordion-titulo">Reglamento higiene y seguridad industrial<span class="toggle-icon"></span></a>
                    <div class="accordion-content" id="seguridad">
                        Descarga el <a href="/taxsur/informacion_empresa/reglamento_seguridad_higiene" target="_blank">Reglamento higiene y seguridad industrial <i class="fa fa-download" aria-hidden="true"></i></a>.
                    </div>
                </div>

            </div>
		</div>
	</div>
@endsection
@section('script')
    <script>
        $(".accordion-titulo").click(function() {

            var contenido = $(this).next(".accordion-content");

            if (contenido.css("display") == "none") { //open		           
                $(this).addClass("open");
                contenido.slideDown(500);
            } else { //close		              
                $(this).removeClass("open");
                contenido.slideUp(500);
            }
        });
    </script>
@endsection
