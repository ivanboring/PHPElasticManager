<?php

class options_stop extends query_base_model
{
	protected
		$parents = array(
			'stop'
		),
		$named = false,
		$type = 'option',
		$name = 'stopWords';

	protected function constructBody()
	{
		$this->createTextInput('setval', 'longtext', 'Stop word');
		$this->createMinus();
		
		return $this->output();
	}
}