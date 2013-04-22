<?php

class options_term extends query_base_model
{
	protected
		$parents = array(
			'term'
		),
		$type = 'option',
		$named = false,
		$name = 'addTerm';

	protected function constructBody()
	{
		$this->createTextInput('setval', 'longtext', 'Term');
		$this->createMinus();
		
		return $this->output();
	}
}