{{ Form::open(['url' => $url, 'method' => 'get', 'class' => 'float-right app-search']) }}
	{{ Form::text('query', $query, ['placeholder' => $placeholder, 'class' => 'form-control']) }}
	{!! Form::button('<i class="fa fa-search"></i>', ['type' => 'submit']) !!}
{{ Form::close() }}