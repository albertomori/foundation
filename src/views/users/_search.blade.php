<?php

use Illuminate\Support\Facades\Input;
use Orchestra\Support\Facades\Form; ?>

<div class="navbar user-search">
	<form class="navbar-form">
		{!! Form::text('q', Input::get('q', ''), ['placeholder' => 'Search keyword...', 'role' => 'keyword']) !!}
		{!! Form::select('roles[]', $roles, Input::get('roles', []), ['multiple' => true, 'placeholder' => 'Roles', 'role' => 'roles']) !!}
		{!! Form::submit(trans('orchestra/foundation::label.search.button'), ['class' => 'btn btn-primary']) !!}
	</form>
</div>
