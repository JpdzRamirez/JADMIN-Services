  $("#formuser").submit(function (e) {
    var email,nombres,apellidos;
    email = $('#email').val();
    nombres = $('#nombres').val();
    apellidos = $('#apellidos').val();
	var p1 = $('#password').val();
	var p2 = $('#password2').val();
	
	if(p1 != "" && p2 != "") {
		if(email != '' && nombres != '' && apellidos != '' && pasar == 4){
		var p1 = $('#password').val();
		var p2 = $('#password2').val();
		if (p1 === p2) {
		    
		} else {
		  e.preventDefault();
		  Swal.fire({
			icon: 'error',
			title: 'Error',
			text: 'Las contraseñas no coinciden'
		  });
		  $("#load").remove();
		}
	}else{
	  e.preventDefault();
	  Swal.fire({
		icon: 'error',
		title: 'Error',
		text: 'Complete los campos obligatorios'
	  });
	  $("#load").remove();
	}
	}
  });
  
  function mostrarpassword() {
    var cambio = document.getElementById("password");
    if (cambio.type == "password") {
      cambio.type = "text";
      $('#eye').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
    } else {
      cambio.type = "password";
      $('#eye').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
    }
  }
  
  function mostrarpassword2() {
    var cambio = document.getElementById("password2");
    if (cambio.type == "password") {
      cambio.type = "text";
      $('#eye2').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
    } else {
      cambio.type = "password";
      $('#eye2').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
    }
  }
