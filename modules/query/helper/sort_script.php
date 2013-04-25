<?php

class sort_script extends query_base_model
{
    protected
        $parents = array(
            'sort'
        ),
        $type = 'sort',
        $named = false,
        $name = 'script';

    protected function constructBody()
    {
        $this->createNestDiv('_script', false, false);
        $this->createLabel('_script');
        $this->createBreakLine();

        $this->createNestDiv('script');
        $this->createLabel('script');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('type');
        $this->createLabel('type');
        $this->createSelect('setval', array(
            'number' => 'number',
            'string' => 'string',
            'date' => 'date'
        ));
        $this->createCloseDiv();

        $this->createNestDiv('order');
        $this->createLabel('order');
        $this->createSelect('setval', array(
            'asc' => 'ascending',
            'desc' => 'descending'
        ));
        $this->createCloseDiv();

        $this->createCloseDiv();
        $this->createMinus();

        return $this->output();
    }

}
