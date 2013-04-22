<?php

class options_track_scores extends query_base_model
{
	protected
		$parents = array(
			'root'
		),
		$type = 'option',
		$name = 'track_scores';

	protected function constructBody()
	{
		$this->createLabel('track_scores');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createMinus();
					
		return $this->output();
	}
}