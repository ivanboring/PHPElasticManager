<?php

class query_indices extends query_base_model
{
    protected
        $parents = array(
            'query',
            'queries',
            'must',
            'must_not',
            'should',
            'positive',
            'no_match_query',
            'negative'
        ),
        $type = 'query',
        $name = 'indices';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('indices');
        $this->createLabel('indices');
        $this->createPlusMultiple('index');
        $this->createCloseDiv();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlus('query');
        $this->createCloseDiv();

        $this->createNestDiv('no_match_query');
        $this->createLabel('no_match_query');
        $this->createPlus('no_match_query');
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }

}
