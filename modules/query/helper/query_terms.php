<?php

class query_terms extends query_base_model
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
        $name = 'terms';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createPlusMultiple('term');
        $this->createCloseDiv();

        $this->createNestDiv('minimum_match');
        $this->createLabel('minimum_match');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        return $this->output();
    }

}
