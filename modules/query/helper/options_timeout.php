<?php

class options_timeout extends query_base_model
{
    protected
        $parents = array(
            'root'
        ),
        $type = 'option',
        $name = 'timeout';

    protected function constructBody()
    {
        $this->createLabel('timeout');
        $this->createTextInput('setval');
        $this->createMinus();

        return $this->output();
    }
}
