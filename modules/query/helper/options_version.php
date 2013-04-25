<?php

class options_version extends query_base_model
{
    protected
        $parents = array(
            'root'
        ),
        $type = 'option',
        $name = 'version';

    protected function constructBody()
    {
        $this->createLabel('version');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createMinus();

        return $this->output();
    }
}
