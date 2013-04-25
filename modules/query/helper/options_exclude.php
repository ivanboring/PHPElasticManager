<?php

class options_exclude extends query_base_model
{
    protected
        $parents = array(
            'exclude'
        ),
        $type = 'option',
        $named = false,
        $name = 'excludeTerm';

    protected function constructBody()
    {
        $this->createTextInput('setval', 'longtext', 'Term to exclude');
        $this->createMinus();

        return $this->output();
    }
}
