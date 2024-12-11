@extends('layouts.layoutchat')

@section('style')
    <style>
        p{
            font-size: x-large;
        }

        h3{
            font-size: xx-large;
            color: navy;
        }
    </style>
@endsection

@section('sub_title', 'Información de Interés')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <img src="/img/dia_taxista.jpeg" width="100%" alt="Info">
		</div>
	</div>
@endsection
