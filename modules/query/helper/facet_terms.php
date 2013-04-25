<?php

class facet_terms extends query_base_model
{
    protected
        $parents = array(
            'facets'
        ),
        $named = false,
        $type = 'facet',
        $name = 'terms';

    protected function constructBody()
    {
        $this->createTextInput('', 'longtext', 'Facet name');
        $this->createMinus();

        $this->createNestDiv('terms');
        $this->createLabel('terms');

        $this->createNestDiv('fields');
        $this->createLabel('fields');
        $this->createPlusMultiple('fields');
        $this->createCloseDiv();

        $this->createNestDiv('size');
        $this->createLabel('size');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('order');
        $this->createLabel('order');
        $this->createSelect('setval', array(
                '' => 'default',
                'count' => 'count',
                'term' => 'term',
                'reverse_count' => 'reverse_count',
                'reverse_term' => 'reverse_term'
            )
        );
        $this->createCloseDiv();

        $this->createNestDiv('all_terms');
        $this->createLabel('all_terms');
        $this->createSelect('setval', array(
                '' => 'default',
                'false' => 'false',
                'true' => 'true'
            )
        );
        $this->createCloseDiv();

        $this->createNestDiv('exclude');
        $this->createLabel('exclude');
        $this->createPlusMultiple('exclude');
        $this->createCloseDiv();

        $this->createNestDiv('global');
        $this->createLabel('global');
        $this->createSelect('setval', array(
                '' => 'default',
                'false' => 'false',
                'true' => 'true'
            )
        );
        $this->createCloseDiv();

        return $this->output();
    }

}
