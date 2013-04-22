<?php

class sort_field extends query_base_model
{
	protected
		$parents = array(
			'sort'
		),
		$type = 'sort',
		$named = false,
		$name = 'field';
		
	protected function constructBody()
	{
		$this->createGetField();
		$this->createMinus();
		
		$this->createNestDiv('order');
		$this->createLabel('order');
		$this->createSelect('setval', array(
			'asc' => 'ascending', 
			'desc' => 'descending'		
		));
		$this->createCloseDiv();

		$this->createNestDiv('mode');
		$this->createLabel('mode');
		$this->createSelect('setval', array(
			'' => 'default', 
			'min' => 'min',
			'max' => 'max',
			'sum' => 'sum',
			'avg' => 'avg'		
		));
		$this->createCloseDiv();

		$this->createNestDiv('missing');
		$this->createLabel('missing');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('ignore_unmapped');
		$this->createLabel('ignore_unmapped');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
						
		$this->createNestDiv('nested_path');
		$this->createLabel('nested_path');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();
		
		$this->createNestDiv('nested_filter');
		$this->createLabel('nested_filter');
		$this->createPlus('filters');
		$this->createCloseDiv();		
		
		return $this->output();
	}		

}