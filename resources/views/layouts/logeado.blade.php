@extends('layouts.plantilla')

@section('content')
	<div class="header-bg">
		@if (Auth::user()->roles_id == 3)
			@include('elements.headersucursal')
		@elseif(Auth::user()->roles_id == 4 || Auth::user()->roles_id == 5)
			@include('elements.headerempresa')
		@elseif(Auth::user()->roles_id == 6)
			@include('elements.headerSucursalOf')
		@else
			@include('elements.header')
		@endif		
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-12">
					<div class="page-title-box">
						@if(isset($buscar))
							@include('elements.buscar', ['url' => $url, 'placeholder' => $placeholder])
						@endif
						<h4 class="page-title">
							@if (Auth::user()->roles_id == 3 || Auth::user()->roles_id == 6)
								<img src="/img/eds.jpg" width="100px" height="100px">
							@else
								<img src="/img/services.png" width="100px" height="100px">
							@endif				 
							@yield('sub_title', 'JADMIN')</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="container-fluid">
			@yield('sub_content')
		</div>
	</div>
	<footer class="footer">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					© 2019 JADMIN
				</div>
			</div>
		</div>
	</footer>
@endsection
@if (Auth::user()->roles_id == 1 || Auth::user()->roles_id == 2)
@section('modalchat')
<div id="Modalchat" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
		<div class="modal-dialog" style="min-width: 70%">
			<div class="modal-content" style="min-height: 600px">
				<div class="modal-header">
					<button type="button" class="close close-chat" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" style="min-height: 550px">
					<iframe id="framechat" width="100%" height="550px" src="" frameborder="0">
					</iframe>				
				</div>
		</div>
	</div>
</div>
@endsection
@endif
