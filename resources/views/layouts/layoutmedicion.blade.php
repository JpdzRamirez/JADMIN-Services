@extends('layouts.plantilla')

@section('content')
<div class="header-bg">
    @include('elements.headermedicion')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <img src="/img/kompremas.jpg" width="100px" height="100px">	 
                        @yield('sub_title', 'Kompremas')</h4>
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