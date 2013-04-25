<?php

class filter_has_parent extends query_base_model
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
        $name = 'has_parent';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('type');
        $this->createLabel('type');
        $this->createGetTypes('setval');
        $this->createCloseDiv();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlus('query');
        $this->createCloseDiv();

        $this->createNestDiv('_scope');
        $this->createLabel('_scope');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        return $this->output();
    }

}
