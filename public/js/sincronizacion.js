var fmsj = 0;
var fale = 0;

function mensajes() {
    if(fmsj == 0){
        fmsj = 1;
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "/mensajes/pendientes",
            })
            .done(function (data, textStatus, jqXHR) {
                if (data.length > 0) {
                    $("#imgsms").attr("src","/img/smsnews.png");
                    for (const key in data) {
                        if($("#menusms").find("#"+data[key].id).length == 0){
                            var txt = data[key].texto;
                            $("#menusms").prepend('<a id="' + data[key].id + '" class="dropdown-item msj" href="/mensajes/chat/' + data[key].cuentasc_id+ '"><b>'+ data[key].placa + ':</b>' + txt.substr(0,15) + '...</a>');
                        }
                    }
                }
                fmsj = 0;
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                fmsj = 0;
            });
    }
    	
}

$("#linksms").click(function () {
    $("#imgsms").attr("src","/img/sms.png");
});

function alertas() {
    if(fale == 0){
        fale = 1;
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "/alertas/pendientes",
        })
        .done(function (data, textStatus, jqXHR) {
            if (data.alertas.length > 0) {
                $("#imgalerta").attr("src","/img/bellnews.png");
                for (const key in data.alertas) {
                    if($("#menualerta").find("#"+data.alertas[key].id).length == 0){
                        $("#menualerta").prepend('<a id="' + data.alertas[key].id + '" class="dropdown-item" href="/alertas/gestionar/' + data.alertas[key].id + '"><b>'+ data.alertas[key].placa + ':</b>' + data.alertas[key].tipo + '</a>');
                        if(data.alertas[key].tipo == "Pánico"){
                            alertaPanico(data.alertas[key].id);
                        }
                    }
                }
            }
            if (data.mensajes.length > 0) {
				let sonar = 0;
                $("#imgsms").attr("src","/img/smsnews.png");
                for (const key in data.mensajes) {
                    if($("#menusms").find("#"+data.mensajes[key].id).length == 0){
                        var txt = data.mensajes[key].texto;
                        $("#menusms").prepend('<a id="' + data.mensajes[key].id + '" class="dropdown-item msj" href="/mensajes/chat/' + data.mensajes[key].cuentasc_id+ '"><b>'+ data.mensajes[key].placa + ':</b>' + txt.substr(0,15) + '...</a>');
						sonar++;
						if(sonar == 1){
							var audioMensaje = document.createElement('audio');;
							audioMensaje.src = "/sounds/plop.mp3";
							audioMensaje.autoplay = true;
							audioMensaje.muted = true;
							document.body.appendChild(audioMensaje);
							audioMensaje.muted = false;
							audioMensaje.play();
						}
                    }
                }
            }
            fale = 0;
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            fale = 0;
        });
    }   	
}

$("#linkalerta").click(function () {
    $("#imgalerta").attr("src","/img/bell.png");
});

$(document).on('click', '.msj', function (ev) {
    ev.preventDefault();
    if(ev.target.href == null){
        cuenta = ev.target.parentElement.href;
        $("#"+ev.target.parentElement.id).remove();
    }else{
        cuenta = ev.target.href;
        $("#"+ev.target.id).remove();
    }
    
    $("#framechat").attr("src", cuenta);
    $("#Modalchat").modal('show');
});

$(document).on('click', '.close-chat', function () {  

    $("#framechat").attr("src", "");
});

function alertaPanico(alerta) {
    var aud = document.createElement('audio');
    aud.src = "/sounds/emergencia.mp3";
    aud.autoplay = true;
    aud.muted = true;
    document.body.appendChild(aud);
    aud.muted = false;
    aud.play(); 
    Swal.fire({
        title: 'Alerta Botón de Pánico',
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Atender'
      }).then((result) => {
        if (result.isConfirmed) {         
            window.open("/alertas/gestionar/"+alerta, "_blank");
        }
      });
}


$(document).ready(function () {
    var alerta = setInterval(alertas, 15000);
});
