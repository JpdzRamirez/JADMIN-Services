<div class="modal-dialog">
	<div class="modal-content">
		{{ Form::open(['url' => $url, 'method' => 'delete']) }}
			<div class="modal-body">
				{{ $message }}
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancelar</button>
				{!! Form::button('Eliminar', ['type' => 'submit', 'class' => 'btn btn-primary waves-effect waves-light']) !!}
			</div>
		{{ Form::close() }}
	</div>
</div>