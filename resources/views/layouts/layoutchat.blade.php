@extends('layouts.plantilla')

@section('content')
<div class="header-bg">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <img src="/img/services.png" width="100px" height="100px">	 
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
@endsection

@section('modal')
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
</div>
@endsection
