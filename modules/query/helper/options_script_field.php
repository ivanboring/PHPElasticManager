<?php

class options_script_field extends query_base_model
{
    protected
        $parents = array(
            'script_field'
        ),
        $type = 'option',
        $named = false,
        $name = 'script_field';

    protected function constructBody()
    {
        $this->createTextInput('', 'longtext');
        $this->createNestDiv('script');
        $this->createLabel('script');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();
        $this->createMinus();

        return $this->output();
    }
}
