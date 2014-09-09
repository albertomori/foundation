@extends('orchestra/foundation::layout.main')

<?php

use Illuminate\Support\Facades\View;
use Orchestra\Support\Facades\HTML; ?>

@section('content')

<div class="row">
	@if (count($panes) > 0)

	<?php $panes->add('mini-profile', '<')->title('Mini Profile')
		->attributes(['class' => 'three columns widget'])
		->content(View::make('orchestra/foundation::components.miniprofile')); ?>

	@foreach ($panes as $id => $pane)
		<div{!! HTML::attributes(HTML::decorate($pane->attributes, ['class' => 'panel'])) !!}>
		@if (! empty($pane->html))
		{!! $pane->html !!}
		@else
		<div class="panel-heading">
			{!! $pane->title !!}
		</div>
		{!! $pane->content !!}
		@endif
		</div>
	@endforeach
	@else
	@include('orchestra/foundation::dashboard._welcome')
	@endif
</div>

@stop
