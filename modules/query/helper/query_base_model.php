<?php

abstract class query_base_model extends router
{
    protected
        $parents = array(),
        $index,
        $name,
        $named = true,
        $output = '',
        $type;

    public function __construct($index)
    {
        $this->index = $index;
    }

    abstract protected function constructBody();

    public function getJavascript()
    {
        $output = "\t\t" . 'case "' . $this->type . '_' . $this->name . "\":\n";
        $output .= $this->constructBody();
        $output .= "\t\t\tbreak;\n";

        return $output;
    }

    public function getSelects()
    {
        $output[md5(json_encode($this->parents))]['parents'] = $this->parents;
        $output[md5(json_encode($this->parents))]['child'] = $this->type . '_' . $this->name;

        return $output;
    }

    public function shouldBeNamed()
    {
        return $this->named ? '' : $this->parents;
    }

    protected function createHeader()
    {
        $this->output .= "\t\t\t" . 'output += \'' . $this->name . '\' + "\n";' . "\n";
    }

    protected function createMinus()
    {
        $this->output .= "\t\t\t" . 'output += \'<div class="queryBuilder_minus">[-]</div>\' + "\n";' . "\n";
    }

    protected function createNestDiv($name = '', $inline = false, $nest = true)
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $inline = $inline ? 'queryBuilder_inline' : '';
        $nest = $nest ? 'queryBuilder_nested' : '';
        $this->output .= "\t\t\t" . 'output += \'<div class="queryBuilder_base ' . $nest . ' ' . $inline . '" ' . $name . '>\' + "\n";' . "\n";
    }

    protected function createPlusMultiple($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<div class="queryBuilder_plusMultiple" ' . $name . '">[+]</div>\';' . "\n";
    }

    protected function createPlusMultipleObject($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<div class="queryBuilder_plusMultipleObject" ' . $name . '">[+]</div>\';' . "\n";
    }

    protected function createPlus($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<div class="queryBuilder_plus" ' . $name . '">[+]</div>\';' . "\n";
    }

    protected function createTextInput($name = '', $class = '', $placeholder = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $class = $class ? 'class="' . $class . '"' : '';
        $placeholder = $placeholder ? 'placeholder="' . $placeholder . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<input type="text" ' . $class . ' ' . $name . ' ' . $placeholder . '>\' + "\n";' . "\n";
    }

    protected function createHidden($name = '', $value = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $value = $value ? 'value="' . $value . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<input type="hidden" ' . $name . ' ' . $value . '>\' + "\n";' . "\n";
    }

    protected function createSelect($name = '', $options = array())
    {
        $optionsoutput = '';
        foreach ($options as $value => $option) {
            $optionsoutput .= '<option value="' . $value . '">' . $option . '</option>';
        }
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<select ' . $name . '>' . $optionsoutput . '</select>\' + "\n";' . "\n";
    }

    protected function createLabel($name = '')
    {
        $this->output .= "\t\t\t" . 'output += \'' . $name . ': \' + "\n";' . "\n";
    }

    protected function createGetField($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<select ' . $name . '>\' + getFields() + \'</select>\' + "\n";' . "\n";
    }

    protected function createGetIndexes($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<select ' . $name . '>\' + getIndex() + \'</select>\' + "\n";' . "\n";
    }

    protected function createGetAnalyzer($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<select ' . $name . '>\' + getAnalyzers() + \'</select>\' + "\n";' . "\n";
    }

    protected function createGetTypes($name = '')
    {
        $name = $name ? 'name="' . $name . '"' : '';
        $this->output .= "\t\t\t" . 'output += \'<select ' . $name . '>\' + getTypes() + \'</select>\' + "\n";' . "\n";
    }

    protected function createBreakLine()
    {
        $this->output .= "\t\t\t" . 'output += \'<br>\' + "\n";' . "\n";
    }

    protected function createCloseDiv()
    {
        $this->output .= "\t\t\t" . 'output += \'</div>\' + "\n";' . "\n";
    }

    protected function output()
    {
        return $this->output;
    }

    protected function getFields()
    {
        $state = self::$query_loader->call('_cluster/state', 'GET');
        foreach ($state['metadata']['indices'][$this->index]['mappings'] as $key => $data) {
            $types[] = $key;
            foreach ($data['properties'] as $field => $fielddata) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
