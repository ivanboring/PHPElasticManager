<?php

class query_constant_score extends query_base_model
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
        $name = 'constant_score';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlusMultiple('query');
        $this->createCloseDiv();

        $this->createNestDiv('filter');
        $this->createLabel('filter');
        $this->createPlusMultiple('filter');
        $this->createCloseDiv();

        $this->createNestDiv('boost');
        $this->createLabel('boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        return $this->output();
    }

}
