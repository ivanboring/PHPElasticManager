<?php

class filter_terms extends query_base_model
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
        $name = 'terms';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createPlusMultiple('term');
        $this->createCloseDiv();

        $this->createNestDiv('execution');
        $this->createLabel('execution');
        $this->createSelect('setval', array(
            '' => 'default',
            'plain' => 'plain',
            'bool' => 'bool',
            'and' => 'and',
            'or' => 'or'
        ));
        $this->createCloseDiv();

        $this->createNestDiv('_cache');
        $this->createLabel('_cache');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        return $this->output();
    }

}
