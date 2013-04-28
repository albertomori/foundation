<?php namespace Orchestra\Services\Html;

use Illuminate\Support\Facades\Html;
use Orchestra\Support\Facades\Table;

class ResourcePresenter {

	/**
	 * Table View Generator for Orchestra\Resources.
	 *
	 * @static
	 * @access public
	 * @param  Orchestra\Resources  $model
	 * @return Orchestra\Table
	 */
	public static function table($model)
	{
		return Table::of('orchestra.resources: list', function ($table) use ($model)
		{
			// attach the list
			$table->with($model, false);

			$table->column('name', function ($column)
			{
				$column->escape(false);
				$column->value(function ($row)
				{
					$link = Html::link(handles("orchestra/foundation::resources/{$row->id}"), e($row->name));
					return Html::create('strong', Html::raw($link));
				});
			});
		});
	}
}
