<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<title>
			@yield('title', 'CRM JADMIN')
		</title>
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
		<link href="{{ mix('/css/bootstrap.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ mix('/css/dashboard.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ mix('/css/app.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ mix('/css/micss.css') }}" rel="stylesheet" type="text/css">
		<link href="{{asset('assets/css/jadmin.min.css')}}" rel="stylesheet" type="text/css">
		<link href="{{ mix('/css/jquery-ui.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ mix('/css/daterangepicker.css') }}" rel="stylesheet" type="text/css">
		@yield('style')
	</head>
	<body>
		<div id="preloader"><div id="status"><div class="spinner"></div></div></div>
		@yield('content')
		<div class="load" id="load"></div>
		@yield('modal')
		@yield('modalchat')
		<script src="{{ mix('/js/jquery.min.js') }}"></script>
		<script src="{{ mix('/js/popper.js') }}"></script>
		<script src="{{ mix('/js/bootstrap.min.js') }}"></script>
		<script src="{{ mix('/js/modernizer.min.js') }}"></script>
		<script src="{{ mix('/js/waves.js') }}"></script>
		<script src="{{ mix('/js/jquery.slimscroll.js') }}"></script>
		<script src="{{ mix('/js/jquery.nicescroll.js') }}"></script>
		<script src="{{ mix('/js/jquery.scrollTo.min.js') }}"></script>
		<script src="{{ mix('/js/dashboard.js') }}"></script>
		<script src="{{ mix('/js/app.min.js') }}"></script>
		<script src="{{ mix('/js/jquery-ui.min.js') }}"></script>
		<script src="{{ mix('/js/sweetalert2.all.min.js') }}"></script>
		@yield('sincro')
		@yield('script')
	</body>
</html>