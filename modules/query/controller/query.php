<?php

/**
 * Query pages
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class controllerQuery extends router
{
    /**
     * Loads an emq file
	 * 
     * @param array $args Page arguments
     */		
    public function page_load($args)
    {
        $query = explode("\r\n\r\n", file_get_contents($_FILES["file"]["tmp_name"]));
        $_SESSION['query_path'] = $query[0];
        $_SESSION['query_method'] = $query[1];
        $_SESSION['query_query'] = $query[2];

        $this->redirect('query/query');
    }

    /**
     * Saves an emq file
	 * 
     * @param array $args Page arguments
     */		
    public function page_save($args)
    {
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="query.emq"');

        $data = json_decode($this->getPost('data'));

        echo $data->path;
        echo "\r\n\r\n";
        echo $data->method;
        echo "\r\n\r\n";
        echo $data->query;
    }

    /**
     * Custom query page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_query($args)
    {
        $form = new form($this->form_create_query($args));

        $form->createForm();

        $arguments['form'] = $form->renderForm();
        $vars['javascript'][] = 'custom/forms.js';
        $vars['javascript'][] = 'custom/queryform.js';

        $arguments['response'] = '';
        if (isset($_SESSION['query_response'])) {
            $arguments['response'] = self::$query_loader->prettyJson(json_encode($_SESSION['query_response']));
        }

        $vars['content'] = $this->renderPart('query_query', $arguments);
        $vars['title'] = 'Custom query';

        unset($_SESSION['query_response']);

        return $vars;
    }

    /**
     * Custom query post page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_query_post($args)
    {
        $form = new form($this->form_create_query($args));
        $results = $form->getResults();

        $_SESSION['query_response'] = self::$query_loader->call($results['path'], $results['method'], $results['query']);
        $_SESSION['query_path'] = $results['path'];
        $_SESSION['query_method'] = $results['method'];
        $_SESSION['query_query'] = $results['query'];
        if (isset($_SESSION['query_redirect'])) {
            $this->redirect($_SESSION['query_redirect']);
        } else {
            $this->redirect('query/query');
        }
    }

    /**
     * Set query validation page
	 * 
     * @param array $args Page arguments
     */	
    public function page_validation($args)
    {
        if ($args[0]) {
            $_SESSION['query_validation'] = true;
        } else {
            $_SESSION['query_validation'] = false;
        }
    }

    /**
     * Creates javascript needed for query builder
	 * 
     * @param array $args Page arguments
     */	
    public function page_query_builder_js($args)
    {
        $notnamed = array();
        // Run all the helpers
        $dir = scandir('modules/query/helper');
        foreach ($dir as $file) {
            if (substr($file, 0, 1) != '.') {
                $classname = str_replace('.php', '', $file);
                // Do not run the abstract class
                if ($classname != 'query_base_model') {
                    $class = new $classname($args[0]);
                    $outputs[] = $class->getJavascript();
                    $select = $class->getSelects();
                    $selectarray[key($select)]['parents'] = $select[key($select)]['parents'];
                    $selectarray[key($select)]['children'][] = $select[key($select)]['child'];
                    $name = $class->shouldBeNamed();
                    if(is_array($name)) $notnamed = array_merge($name, $notnamed);
                }
            }
        }

        // Create options function
        echo "function getOptions(type)\n";
        echo "{\n";
        echo "\tvar options = new Array();\n";
        echo "\tswitch(type)\n";
        echo "\t{\n";
        foreach ($selectarray as $value) {
            foreach ($value['parents'] as $parent) {
                echo "\t\tcase \"$parent\":\n";
            }

            foreach ($value['children'] as $child) {
                echo "\t\t\toptions.push('$child');\n";
            }

            echo "\t\t\tbreak;\n";
        }
        echo "\t}\n";
        echo "\treturn options;\n";
        echo "}\n\n";

        // Create output function
        echo "function getQuerySelect(value)\n";
        echo "{\n";
        echo "\toutput = '';\n";
        echo "\tswitch(value)\n";
        echo "\t{\n";

        foreach ($outputs as $output) {
            echo $output;
        }

        echo "\t}\n";
        echo "\treturn output;\n";
        echo "}\n\n";

        // Create plus function
        echo "function bindPlus(nestedObject, hide)
{
    if (!dimmed) {
        dimmed = true;
        var type = nestedObject.attr('name');
        var options = getOptions(type);
        var select = '<select id=\"' + nestedObject.attr('name') + '_select\">';
        select += '<option value=\"\">select</option>';
        for (var i = 0; i < options.length; i++) {
            select += '<option value=\"' + options[i] + '\">' + options[i] + '</option>';
        }
        select += '</select><div class=\"queryBuilder_minus\">[-]</div>';

        nestedObject.parent().append('<div class=\"queryBuilder_base queryBuilder_nested\">' + select + '</div>');
        $(nestedObject.parent().find('#' + nestedObject.attr('name') + '_select')).change(function() {
            var addName = true;
            switch ($(this).attr('id')) {";
    foreach ($notnamed as $name) {
        echo "case '" . $name . "_select':\n";
    }

    echo "					addName = false;
                    break;
            }

            bindSelect($(this), addName);
        })
        if (hide) {
            nestedObject.hide();
        }
    }
}";
    }

    /**
     * Query builder page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_query_builder($args)
    {

        $state = self::$query_loader->call('_cluster/state', 'GET');

        if (!isset($state['metadata']['indices'][$args[0]]['mappings'])) {
            trigger_error("No mapping exists for " . $args[1], E_USER_ERROR);
        }

        $fields = array();
        $types = array();
        $analyzers = array('' => 'default');
        $array = self::$query_loader->toArray(array($state['metadata']['indices'][$args[0]]['settings']));

        $indexes = array();
        foreach ($state['metadata']['indices'] as $index => $value) {
            $indexes[] = $index;
        }

        if (isset($array['index']['analysis']['analyzer'])) {
            foreach ($array['index']['analysis']['analyzer'] as $name => $value) {
                if(!in_array($name, $analyzers)) $analyzers[$name] = $name;
            }
        }

        foreach ($state['metadata']['indices'][$args[0]]['mappings'] as $key => $data) {
            $types[] = $key;
            $fields[$key] = self::$query_loader->getValueFields($data);
        }

        foreach ($fields as $key => $value) {
            foreach ($value as $type => $data) {
                foreach ($data as $datafield) {
                    $trimmed = trim($datafield, '.');
                    $newfields[] = $trimmed;
                }
            }
        }

        $args['types'] = json_encode($types);
        $args['fields'] = json_encode($newfields);
        $args['analyzers'] = json_encode($analyzers);
        $args['indexes'] = json_encode($indexes);
        $vars['javascript'][] = 'custom/es_builder.js';
        $vars['content'] = $this->renderPart('query_builder', $args);
        $vars['title'] = 'Searchquery builder';

        return $vars;
    }


    /**
     * Search page
	 * 
     * @param array $args Page arguments
     */	
    public function page_search_json($args)
    {
        $newarray = array();
        $parts = json_decode($this->getPost('value'));
        foreach ($parts as $part) {
            $arrayparts = explode('=', $part);

            if (isset($arrayparts[1]) && $arrayparts[1] != '') {
                $result = $arrayparts[1];
                $newarray[][$arrayparts[0]] = $result;
            }
        }

        $data = self::$query_loader->toArray($newarray, ';', '[]');

        $output['jsonarray'] = $data;
        // Then do the search
        $results = self::$query_loader->call($args[0] . '/_search', 'POST', json_encode($data));

        // Make the id's clickable
        if (isset($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $key => $result) {
                $result['_id'] = '<a href=\'?q=document/edit_document/' .$result['_index'] . '/' . $result['_type'] . '/' . $result['_id'] . '\'>' . $result['_id'] . '</a>';
                $results['hits']['hits'][$key] = $result;
            }
        }

        $output['result'] = $results;

        echo json_encode($output);
    }

    /**
     * Adds a menu item
	 * 
     * @return array Menu item array
     */	
    public function menu_items()
    {
        return array(
            'path' => 'query/query',
            'title' => 'Custom query',
            'weight' => 3
        );
    }
	
    /**
     * Form for creating a query
	 * 
     * @param array $args Form arguments
	 * 
     * @return array Form array
     */	
    private function form_create_query($args)
    {
        $form['_init'] = array(
            'name' => 'query_query',
            'action' => 'query/query_post'
        );

        $form['general'] = array(
            '_type' => 'fieldset',
        );

        $form['general']['path'] = array(
            '_label' => 'Path',
            '_type' => 'textField',
            '_description' => 'The path of the call. Eg /twitter/tweet/1',
            '_value' => isset($_SESSION['query_path']) ? $_SESSION['query_path'] : ''
        );

        $form['general']['method'] = array(
            '_label' => 'Method',
            '_type' => 'select',
            '_description' => 'The method of the call',
            '_options' => array(
                'GET' => 'get',
                'POST' => 'post',
                'PUT' => 'put',
                'DELETE' => 'delete'
            ),
            '_value' => isset($_SESSION['query_method']) ? $_SESSION['query_method'] : ''
        );

        $form['general']['query'] = array(
            '_label' => 'Query',
            '_type' => 'textArea',
            '_description' => 'The json query to append to the call.',
            '_value' => isset($_SESSION['query_query']) ? $_SESSION['query_query'] : ''
        );

        $form['general']['save'] = array(
            '_value' => 'Save query',
            '_type' => 'button'
        );

        $form['general']['load'] = array(
            '_value' => 'Load query',
            '_type' => 'button'
        );

        $form['general']['submit'] = array(
            '_value' => 'Submit query',
            '_type' => 'submit'
        );

        return $form;
    }
}
