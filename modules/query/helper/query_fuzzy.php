<?php

class query_fuzzy extends query_base_model
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
        $name = 'fuzzy';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createBreakLine();

        $this->createNestDiv('value');
        $this->createLabel('value');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('min_similarity');
        $this->createLabel('min_similarity');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('prefix_length');
        $this->createLabel('prefix_length');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('boost');
        $this->createLabel('boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }
}
