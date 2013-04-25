<?php

class filter_term extends query_base_model
{
    protected
        $parents = array(
            'filter',
            'filters',
            'and',
            'not',
            'or',
            'must_filter',
            'must_not_filter',
            'should_filter'
        ),
        $type = 'filter',
        $name = 'term';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('_cache');
        $this->createLabel('_cache');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }

}
