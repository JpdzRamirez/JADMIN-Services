@extends('layouts.plantilla')

@section('title', 'JADMIN')
    
@section('content')
<div class="header-bg">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <img src="/img/taxsur.jpg" width="300px" height="150px">	 
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