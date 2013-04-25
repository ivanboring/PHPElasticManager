<?php

class filter_query extends query_base_model
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
        $name = 'query';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlus('query');
        $this->createCloseDiv();

        $this->createNestDiv('_cache');
        $this->createLabel('_cache');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        return $this->output();
    }

}
