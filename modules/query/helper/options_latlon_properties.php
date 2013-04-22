<?php

/**
 * Fix multiple lat and lon
 */
class options_latlon_properties extends query_base_model
{
	protected
		$parents = array(
			'top_left',
			'bottom_right',
			'geo_type'
		),
		$type = 'option',
		$named = false,
		$name = 'latlon_properties';

	protected function constructBody()
	{
		$this->createNestDiv('lat', true, false);
		$this->createLabel('lat');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('lon', true, false);
		$this->createLabel('lon');
		$this->createTextInput('setval');
		$this->createCloseDiv();
				
		return $this->output();
	}
}