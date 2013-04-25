<?php

/**
 * Index pages
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class controllerIndex extends router
{
    /**
     * Refereshes an index
	 * 
     * @param array $args Page arguments
     */	
    public function page_refresh($args)
    {
        self::$query_loader->callWithCheck($args[0] . '/_refresh', 'POST', null, 'index/edit/' . $args[0]);
    }

    /**
     * Flushes an index
	 * 
     * @param array $args Page arguments
     */	
    public function page_flush($args)
    {
        self::$query_loader->callWithCheck($args[0] . '/_flush', 'POST', null, 'index/edit/' . $args[0]);
    }

    /**
     * Gateway snapshots an index
	 * 
     * @param array $args Page arguments
     */	
    public function page_gateway_snapshot($args)
    {
        self::$query_loader->callWithCheck($args[0] . '/_gateway/snapshot', 'POST', null, 'index/edit/' . $args[0]);
    }

    /**
     * Closes an index
	 * 
     * @param array $args Page arguments
     */	
    public function page_close($args)
    {
        self::$query_loader->callWithCheck($args[0] . '/_close', 'POST', null, 'index/edit/' . $args[0]);
    }

    /**
     * Opens an index
	 * 
     * @param array $args Page arguments
     */	
    public function page_open($args)
    {
        self::$query_loader->callWithCheck($args[0] . '/_open', 'POST', null, 'index/edit/' . $args[0]);
    }

    /**
     * Export a mapping structure
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_export($args)
    {
        $vars['content'] = $this->renderPart('index_export', $args);
        $vars['title'] = 'Export index structure ' . $args[0];

        return $vars;
    }

    /**
     * Export a mapping structure in emq format
	 * 
     * @param array $args Page arguments
     */	
    public function page_export_emq($args)
    {
        $state = self::$query_loader->call('_cluster/state', 'GET');

        $settings = $state['metadata']['indices'][$args[0]]['settings'];
        $mappings = $state['metadata']['indices'][$args[0]]['mappings'];

        $array = self::$query_loader->toArray(array($settings));
        unset($array['index']['version']);

        $json['settings'] = $array['index'];

        $json['mappings'] = $mappings;

        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="' . $args[0] . '.emq"');

        echo $args[0];
        echo "\r\n\r\n";
        echo 'POST';
        echo "\r\n\r\n";
        echo json_encode($json);
    }

    /**
     * Export a mapping structure in shell format
	 * 
     * @param array $args Page arguments
     */	
    public function page_export_bash($args)
    {
        $state = self::$query_loader->call('_cluster/state', 'GET');

        $settings = $state['metadata']['indices'][$args[0]]['settings'];
        $mappings = $state['metadata']['indices'][$args[0]]['mappings'];

        $array = self::$query_loader->toArray(array($settings));
        unset($array['index']['version']);

        $json['settings'] = $array['index'];

        $json['mappings'] = $mappings;

        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="create_' . $args[0] . '".sh');

        echo "#!/bin/sh\r\n\r\n";
        echo "curl -XPOST '" . parent::$config['servers']['host'] . ":" . parent::$config['servers']['port'] . "/" . $args[0] . "' -d '" . json_encode($json) . "'";
    }

    /**
     * Create an index page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_create($args)
    {
        $state = self::$query_loader->call('_cluster/state', 'GET');

        $form = new form($this->form_create_index($args));

        $form->createForm();

        $arguments['form'] = $form->renderForm();
        $vars['javascript'][] = 'custom/es_fields.js';
        $vars['javascript'][] = 'custom/forms.js';
        $vars['content'] = $this->renderPart('index_create_index', $arguments);
        $vars['title'] = 'Create index';

        return $vars;
    }

    /**
     * Create an index page post
	 * 
     * @param array $args Page arguments
     */	
    public function page_create_index_post($args)
    {
        $form = new form($this->form_create_index($args));
        $results = $form->getResults();

        $data = array();
        if($results['shards']) $data['settings']['number_of_shards'] = $results['shards'];
        if($results['replicas']) $data['settings']['number_of_replicas'] = $results['replicas'];

        self::$query_loader->callWithCheck($results['name'], 'PUT', json_encode($data), 'index/edit/' . $results['name']);

        $this->redirect('index/edit/' . $results['name']);
    }

    /**
     * Edit an index page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_edit($args)
    {
        $output = '';
        $vars['mapping_types'] = array();

        $state = self::$query_loader->call('_cluster/state', 'GET');

        // If the index does not exist
        if (!isset($state['metadata']['indices'][$args[0]]['settings'])) {
            $vars['content'] = 'You are trying to reach an index that does not exist.';
            $vars['title'] = 'Index ' . $args[0] . ' does not exist.';

            return $vars;
        }

        $vars['state'] = $state['metadata']['indices'][$args[0]]['state'];

        $settings = self::$query_loader->call($args[0] . '/_settings', 'GET');

        // Get analyzers
        $array = self::$query_loader->toArray(array($state['metadata']['indices'][$args[0]]['settings']));

        $vars['analyzers'] = array();
        if (isset($array['index']['analysis']['analyzer'])) {
            foreach ($array['index']['analysis']['analyzer'] as $name => $value) {
                if(!in_array($name, $vars['analyzers'])) $vars['analyzers'][] = $name;
            }
        }

        $mapping = $state['metadata']['indices'][$args[0]]['mappings'];

        // Render each field
        foreach ($mapping as $key => $value) {
            $vars['mapping_types'][] = $key;
        }

        $vars['aliases'] = array();
        if (isset($state['metadata']['indices'][$args[0]]['aliases'])) {
            $vars['aliases'] = $state['metadata']['indices'][$args[0]]['aliases'];
        }

        $vars['name'] = $args[0];

        $output .= $this->renderPart('index_edit', $vars);

        $vars['content'] = $output;
        $vars['title'] = 'Edit ' . $args[0];

        return $vars;
    }

    /**
     * Create document type page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_create_document_type($args)
    {
        // Get all the document types for possible parents
        $state = self::$query_loader->call('_cluster/state', 'GET');

        $mapping = $state['metadata']['indices'][$args[0]]['mappings'];

        $vars['mappings'] = $mapping;
        $vars['name'] = $args[0];

        $vars['types'][''] = 'none';
        foreach ($mapping as $key => $value) {
            $vars['types'][$key] = $key;
        }

        $form = new form($this->form_create_document_type($vars));

        $form->createForm();

        $arguments['form'] = $form->renderForm();
        $vars['javascript'][] = 'custom/es_fields.js';
        $vars['javascript'][] = 'custom/forms.js';
        $vars['content'] = $this->renderPart('index_create_index', $arguments);;
        $vars['title'] = 'Create document type for ' . $args[0];

        return $vars;
    }

    /**
     * Create document type post page
	 * 
     * @param array $args Page arguments
     */	
    public function page_create_document_type_post($args)
    {
        $form = new form($this->form_create_document_type($args));
        $results = $form->getResults();

        $data[$results['name']] = array();
        $data[$results['name']]['_source']['enabled'] = $results['source'];
        if($results['index']) $data[$results['name']]['_index']['enabled'] = $results['index'];
        if($results['type_index']) $data[$results['name']]['_type']['index'] = $results['type_index'];
        if($results['type_store']) $data[$results['name']]['_type']['store'] = $results['type_store'];
        if($results['size_index']) $data[$results['name']]['_size']['index'] = $results['size_index'];
        if($results['size_store']) $data[$results['name']]['_size']['store'] = $results['size_store'];
        if($results['id_analyzed']) $data[$results['name']]['_id']['index'] = $results['id_analyzed'];
        if($results['id_store']) $data[$results['name']]['_id']['store'] = $results['id_store'];
        if($results['id_path']) $data[$results['name']]['_id']['path'] = $results['id_path'];

        if ($results['parent']) {
            $data[$results['name']]['_parent']['type'] = $results['parent'];
        }

        if ($results['timestamp_enable'] == 'yes') {
            $data[$results['name']]['_timestamp']['enabled'] = $results['timestamp_enable'];
            $data[$results['name']]['_timestamp']['store'] = $results['timestamp_store'];
            $data[$results['name']]['_timestamp']['index'] = $results['timestamp_analyzed'];
            $data[$results['name']]['_timestamp']['path'] = $results['timestamp_path'];
            $data[$results['name']]['_timestamp']['format'] = $results['timestamp_format'];
        }

        if ($results['ttl_enable'] == 'yes') {
            $data[$results['name']]['_ttl']['enabled'] = $results['ttl_enable'];
            $data[$results['name']]['_ttl']['store'] = $results['ttl_store'];
            $data[$results['name']]['_ttl']['index'] = $results['ttl_analyzed'];
            $data[$results['name']]['_ttl']['default'] = $results['ttl_default'];
        }

        $url = $args[0] .'/' . $results['name'] . '/_mapping';

        self::$query_loader->callWithCheck($url, 'PUT', json_encode($data), 'index/edit/' . $args[0]);

        $this->redirect('index/edit/' . $args[0]);
    }

    /**
     * Delete index page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_delete($args)
    {
        $_SESSION['delete_' . $args[0]] = true;
        $args['name'] = $args[0];
        $vars['content'] = $this->renderPart('index_delete', $args);
        $vars['title'] = 'Delete ' . $args[0];

        return $vars;
    }

    /**
     * Delete index page confirm
	 * 
     * @param array $args Page arguments
     */	
    public function page_delete_confirm($args)
    {
        if (isset($_SESSION['delete_' . $args[0]])) {
            unset($_SESSION['delete_' . $args[0]]);
            self::$query_loader->callWithCheck($args[0], 'DELETE', '', 'start');
            $this->redirect('start');
        }
        trigger_error('Not correctly done', E_USER_ERROR);
    }

    /**
     * Create alias page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_create_alias($args)
    {
        $form = new form($this->form_create_alias($args));

        $form->createForm();

        $arguments['form'] = $form->renderForm();
        $vars['javascript'][] = 'custom/es_fields.js';
        $vars['javascript'][] = 'custom/forms.js';
        $vars['content'] = $this->renderPart('index_create_index', $arguments);;
        $vars['title'] = 'Create alias for ' . $args[0];

        return $vars;
    }

    /**
     * Create alias post page
	 * 
     * @param array $args Page arguments
     */	
    public function page_create_alias_post($args)
    {
        $form = new form($this->form_create_alias($args));
        $results = $form->getResults();

        $data['actions'][0]['add']['index'] = $args[0];
        $data['actions'][0]['add']['alias'] = $results['name'];

        self::$query_loader->callWithCheck('_aliases', 'POST', json_encode($data), 'index/edit/' . $args[0]);
    }

    /**
     * Delete alias page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_delete_alias($args)
    {
        $_SESSION['delete_alias_' . $args[0]] = true;
        $args['index'] = $args[0];
        $args['name'] = $args[1];
        $vars['content'] = $this->renderPart('index_delete_alias', $args);
        $vars['title'] = 'Delete alias ' . $args[0];

        return $vars;
    }

    /**
     * Delete alias confirm page
	 * 
     * @param array $args Page arguments
     */	
    public function page_delete_alias_confirm($args)
    {
        if (isset($_SESSION['delete_alias_' . $args[0]])) {
            unset($_SESSION['delete_alias_' . $args[0]]);
            $data['actions'][0]['remove']['index'] = $args[0];
            $data['actions'][0]['remove']['alias'] = $args[1];

            self::$query_loader->callWithCheck('_aliases', 'POST', json_encode($data), 'index/edit/' . $args[0]);
        }
        trigger_error('Not correctly done', E_USER_ERROR);
    }

    /**
     * Adds a menu item
	 * 
     * @return array Menu item array
     */	
    protected function menu_items()
    {
        return array(
            'path' => 'index/create',
            'title' => 'Create index',
            'weight' => 1
        );
    }
	
    /**
     * Form for creating an index
	 * 
     * @param array $args Form arguments
	 * 
     * @return array Form array
     */	
    private function form_create_index($args)
    {
        $form['_init'] = array(
            'name' => 'create_index',
            'action' => 'index/create_index_post'
        );

        $form['general'] = array(
            '_type' => 'fieldset',
            '_label' => 'General index settings'
        );

        $form['general']['name'] = array(
            '_label' => 'Name',
            '_validation' => array(
                'required' => true
            ),
            '_type' => 'textField',
            '_description' => 'This is the name of the index. No whitespace allowed.'
        );

        $form['general']['shards'] = array(
            '_label' => 'Shards',
            '_type' => 'textField',
            '_description' => 'This amount of shards. Leave empty for default.'
        );

        $form['general']['replicas'] = array(
            '_label' => 'Replicas',
            '_type' => 'textField',
            '_description' => 'The amount of replicas. Leave empty for default.'
        );

        $form['general']['submit'] = array(
            '_value' => 'Create index',
            '_type' => 'submit'
        );

        return $form;
    }

    /**
     * Form for creating an alias
	 * 
     * @param array $args Form arguments
	 * 
     * @return array Form array
     */	
    private function form_create_alias($args)
    {

        $args[0] = isset($args[0]) ? $args[0] : '';

        $form['_init'] = array(
            'name' => 'create_index',
            'action' => 'index/create_alias_post/' . $args[0]
        );

        $form['general'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['name'] = array(
            '_label' => 'Name',
            '_validation' => array(
                'required' => true
            ),
            '_type' => 'textField',
            '_description' => 'This is the name of the alias. No whitespace allowed.'
        );

        $form['general']['submit'] = array(
            '_value' => 'Create alias',
            '_type' => 'submit'
        );

        return $form;
    }

    /**
     * Form for creating a document type
	 * 
     * @param array $args Form arguments
	 * 
     * @return array Form array
     */	
    private function form_create_document_type($args)
    {
        $args['name'] = isset($args['name']) ? $args['name'] : '';
        $args['mappings'] = isset($args['mappings']) ? $args['mappings'] : array();

        $form['_init'] = array(
            'name' => 'create_index',
            'action' => 'index/create_document_type_post/' . $args['name']
        );

        $form['general'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['name'] = array(
            '_label' => 'Name',
            '_validation' => array(
                'required' => true
            ),
            '_type' => 'textField',
        );

        $form['general']['parent'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['parent']['parent'] = array(
            '_label' => 'Parent',
            '_type' => 'select',
            '_options' => $args['types'],
            '_value' => ''
        );

        $form['general']['index'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['index']['index'] = array(
            '_label' => 'Enable index',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'true' => 'True',
                'false' => 'False'
            ),
            '_value' => ''
        );

        $form['general']['thissource'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['thissource']['source'] = array(
            '_label' => 'Enable source',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'true' => 'True',
                'false' => 'False'
            ),
            '_value' => ''
        );

        $form['general']['timestamp'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['timestamp']['timestamp_enable'] = array(
            '_label' => 'Enable _timestamp',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['timestamp']['timestamp_store'] = array(
            '_label' => 'Store _timestamp',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['timestamp']['timestamp_analyzed'] = array(
            '_label' => 'Analyze _timestamp',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'analyzed' => 'analyzed',
                'not_analyzed' => 'not analyzed'
            ),
            '_value' => ''
        );

        $form['general']['timestamp']['timestamp_path'] = array(
            '_label' => 'Path _timestamp',
            '_type' => 'textField',
        );

        $form['general']['timestamp']['timestamp_format'] = array(
            '_label' => 'Format _timestamp',
            '_type' => 'textField',
        );

        $form['general']['type'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['type']['type_index'] = array(
            '_label' => 'Index _type',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['type']['type_store'] = array(
            '_label' => 'Store _type',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['size'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['size']['size_enabled'] = array(
            '_label' => 'Enable _size',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'true' => 'True',
                'false' => 'False'
            ),
            '_value' => ''
        );

        $form['general']['size']['size_store'] = array(
            '_label' => 'Store _size',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['ttl'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['ttl']['ttl_enable'] = array(
            '_label' => 'Enable _ttl',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['ttl']['ttl_store'] = array(
            '_label' => 'Store _timestamp',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['ttl']['ttl_analyzed'] = array(
            '_label' => 'Analyze _timestamp',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'analyzed' => 'analyzed',
                'not_analyzed' => 'not analyzed'
            ),
            '_value' => ''
        );

        $form['general']['ttl']['ttl_default'] = array(
            '_label' => 'Default _timestamp',
            '_type' => 'textField',
        );

        $form['general']['id'] = array(
            '_type' => 'fieldset'
        );

        $form['general']['id']['id_analyzed'] = array(
            '_label' => 'Analyze _id',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'analyzed' => 'analyzed',
                'not_analyzed' => 'not analyzed'
            ),
            '_value' => ''
        );

        $form['general']['id']['id_store'] = array(
            '_label' => 'Store _id',
            '_type' => 'radios',
            '_options' => array(
                '' => 'default',
                'yes' => 'yes',
                'no' => 'no'
            ),
            '_value' => ''
        );

        $form['general']['id']['id_path'] = array(
            '_label' => 'Path _id',
            '_type' => 'textField',
        );

        $form['general']['submit'] = array(
            '_value' => 'Create document type',
            '_type' => 'submit'
        );

        return $form;
    }
}
