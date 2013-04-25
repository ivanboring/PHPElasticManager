<?php

class query_text extends query_base_model
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
        $name = 'text';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createBreakLine();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('operator');
        $this->createLabel('operator');
        $this->createSelect('setval', array('' => 'no bool', 'and' => 'AND', 'or' => 'OR'));
        $this->createCloseDiv();

        $this->createNestDiv('type');
        $this->createLabel('type');
        $this->createSelect('setval', array('' => 'no type', 'phrase' => 'phrase', 'phrase_prefix' => 'phrase_prefix'));
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }
}
