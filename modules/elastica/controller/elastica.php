<?php

class controllerElastica extends router
{
	public function __construct() {

	}
	
	public function page_document($args)
	{
		$state = parent::$queryLoader->call('_cluster/state', 'GET');
		
		if(!isset($state['metadata']['indices'][$args[0]]['mappings'][$args[1]]))
		{
			trigger_error("No mapping exists for " . $args[1], E_USER_ERROR);
		}
		
		$args['mappings'] = $state['metadata']['indices'][$args[0]]['mappings'][$args[1]];
		$args['fields'] = array();
		foreach($args['mappings']['properties'] as $fieldname => $fieldvalue)
		{
			$args['fields'][] = $fieldname;
		}
		// Set the headers
		header('Content-type: text/php');
		header('Content-Disposition: attachment; filename="elasticaDocument_' . $args[1] . '.php"');	
				
		$output = $this->elastica_document_header($args);
		foreach($args['fields'] as $field)
		{
			$output .= $this->elastica_document_function($field);
		}
		$output .= $this->elastica_document_footer($args);
		echo $output;
	}
	
	private function elastica_document_function($field)
	{
		return '	/**
	 * @return string | array Returns the data for the field ' . $field . ' 
	 */	
	public function get' . ucfirst($field) . '()
	{
		return $this->elasticaDocument->get(\'' . $field . '\');
	}
	
	/**
	 * @param string | array $value Sets the data for the field ' . $field . ' 
	 */	
	public function set' . ucfirst($field) . '($value)
	{
		$this->elasticaDocument->set(\'' . $field . '\', $value);
	}	' . "\n\n";		
	}
	
	private function elastica_document_footer($args)
	{
		$examplefield = count($args['fields']) ? $args['fields'][0] : 'null';
		return '}
	
	
/*
	//Example code to create a document
	$test = new elasticaDocument' . ucfirst($args[1]) . '();
	$test->set' . ucfirst($examplefield) . '(\'hello\');
	$test->setDocument();
	$newid = $test->getId();
*/

/*
	//Example code to load a document
	$test = new elasticaDocument' . ucfirst($args[1]) . '($newid);
	$data = $test->getDocument();
*/

/*
	//Example code to update a document
	$data[\'' . $examplefield . '\'] = \'hello world\';
	$test->setDocument($data);
*/ 

/*
	//Example code to delete a document
	$test->deleteDocument(); 
*/

?>';		
	}
	
	private function elastica_document_header($args)
	{
		return '<?php
/**
 * This file creates an elasticsearch document type via the Elastica client
 * that you can use for CRUD commands to that document type. This is mainly
 * done for people that wants a first overview of how Elastica works or
 * for testing purposes. PLEASE DO NOT use this code in producion 
 * enviroments since it\' not optimized. Code yourself.
 * @author: PHPElasticManager
 */

// Only keep this if you haven\'t autoloaded elastica yet
require_once(\'vendor/autoload.php\');


class elasticaDocument' . ucfirst($args[1]) . '
{
	private $elasticaClient = null;
	private $elasticaIndex = null;
	private $elasticaType = null;
	private $elasticaDocument = null;
	private $_id = \'\'; 
	
	/**
	 * @param string $id Give the id if you want to get, delete or update a document 
	 */
    public function __construct($id = \'\')
    {
		$this->elasticaClient = new \Elastica\Client;
		$this->elasticaIndex = $this->elasticaClient->getIndex(\'myindex\');
		$this->elasticaType = $this->elasticaIndex->getType(\'mydocumenttype\');
		if($id)
		{
			$this->elasticaDocument = $this->elasticaType->getDocument($id);
			$this->_id = $id;
		}
		else
		{
			$this->elasticaDocument = new \Elastica\Document();
	        $this->elasticaDocument->setType(\'mydocumenttype\');
	        $this->elasticaDocument->setIndex(\'myindex\');			
		}
    }

	/**
	 * @param string $id Set an id for the document 
	 */	
	public function setId($id)
	{
		$this->elasticaDocument->setId($id);
	}
	
	/**
	 * @return string Returns the id 
	 */	
	public function getId($id)
	{
		$this->elasticaDocument->getId();
	}	
	
	/**
	 * @return elasticaDocument Returns the actual Elastica Document Object
	 */
	public function getRealDocument()
	{
		return $this->elasticaDocument;
	}

	/**
	 * @return array Returns the full document 
	 */	
	public function getDocument()
	{
		return $this->elasticaDocument->getData();
	}

	/**
	 * @param data array The full dataobject to be create/updated
	 */		
	public function setDocument($data = array())
	{
		if(count($data)) $this->elasticaDocument->setData($data);
		$this->elasticaType->addDocument($this->elasticaDocument);
	}
	
	public function deleteDocument()
	{
		$this->elasticaType->deleteById($this->_id);
	}' . "\n\n";
	}
	
	public function page_export($args)
	{
		// Set the headers
		header('Content-type: text/php');
		header('Content-Disposition: attachment; filename="index_' . $args[0] . '.php"');	
		
		$output = $this->elastica_export_header();
		$output .= $this->elastica_export_function($args);
		$output .= $this->elastica_export_footer($args);
		echo $output;
	}
	
	private function elastica_export_header()
	{
		return '<?php
/**
 * This file creates an elasticsearch index via the Elastica client
 * @author: PHPElasticManager
 */

// Only keep this if you haven\'t autoloaded elastica yet
require_once(\'vendor/autoload.php\');' . "\n\n";
	}

	private function elastica_export_function($args)
	{
		$state = parent::$queryLoader->call('_cluster/state', 'GET');
		
		$settings = $state['metadata']['indices'][$args[0]]['settings'];
		$mappings = $state['metadata']['indices'][$args[0]]['mappings'];
		
		$array = $this->toArray(array($settings));
		unset($array['index']['version']);
		
		$json['settings'] = $array['index'];
		
		$json['mappings'] = $mappings;
		
		$output = 'class elasticaIndex' . ucfirst($args[0]) . "\n";
		$output .= "{\n";
		$output .= "\tpublic function create()\n";
		$output .= "\t{\n";
		
		$output .= "\t\t\$elasticaClient = new \Elastica\Client();\n";
		$output .= "\t\t\$elasticaIndex = \$elasticaClient->getIndex('" . $args[0] . "');\n";
		$output .= "\t\t\$elasticaIndex->create(\n";
		
		$rows = explode("\n", var_export($json, true));
		foreach($rows as $row)
		{
			$output .= "\t\t\t" . str_replace('  ', "\t", $row) . "\n";
		}
		
		$output .= "\t\t, true);\n";
		
		$output .= "\t}\n";
		$output .= "}\n";
		
		return $output;
	}
	
	private function elastica_export_footer($args)
	{
		
		return "\n" . '//Create the index' . "\n" . '$test = new elasticaIndex' . ucfirst($args[0]) . ";\n" . '$resultSet = $test->create();
?>';
	}	
	
	public function page_create_query_file($args)
	{
		$newarray = array();
		$parts = json_decode($this->getPost('data'));
		
		foreach($parts as $part)
		{
			$arrayparts = explode('=', $part);

			if(isset($arrayparts[1]) && $arrayparts[1] != '')
			{
				$result = $arrayparts[1];
				$newarray[][$arrayparts[0]] = $result;
			}
		}
		
		$data = $this->toArray($newarray, ';', '[]');
		
		$data = $this->elastica_iterate_findKeys($data);
		
		$keys = array();
		$this->elastica_get_keys($data, $keys);
		
		$keys = $this->fix_array_keys($keys);
		
		$output = $this->elastica_build_header();
		$output .= $this->elastica_create_classname($data);
		$output .= $this->elastica_create_values($keys);
		$output .= $this->elastica_create_main($data, $keys);
		$output .= $this->elastica_create_functions($keys);
		$output .= $this->elastica_create_usage($data, $keys);
		$output .= $this->elastica_build_footer();
		// Set the headers
		header('Content-type: text/php');
		header('Content-Disposition: attachment; filename="create_query_file.php"');
		
		echo $output;
	}

	private function fix_array_keys($keys)
	{
		$newkeys = array();
		$tempvalues = array();
		foreach($keys as $key)
		{
			$parts = explode('|-|', $key);
			if(substr($parts[0], -6) == '_array')
			{
				if(!in_array($parts[0], $newkeys)) $newkeys[] = $parts[0];
				$tempvalues[$parts[0]][] = trim($parts[1], '$');
			}
			else
			{
				$newkeys[] = $key;
			}			
		}
		
		foreach($newkeys as $key => $value)
		{
			if(isset($tempvalues[$value]))
			{
				$newkeys[$key] = $value . '|-|' . implode('/-/', $tempvalues[$value]) . '$';
			}
		}
		
		return($newkeys);
	}
	
	private function elastica_get_keys($data, &$array = array())
	{
		foreach($data as $key => $value)
		{
			if(is_string($value) && substr($value, 0, 1) == '$' && substr($value, -1) == '$')
			{
				if(!in_array($value, $array)) $array[] = $value;
			}
			else
			{
				$this->elastica_get_keys($value, $array);
			}
		}	
	}
		
	private function elastica_create_main($data, $keys)
	{
		$query = new query();
		
		$output = "\t/**\n";
		$output .= "\t * The main searching function\n";
		$output .= "\t * @return resultset A Elastica Resultset Object\n";
		$output .= "\t */\n";
		$output .= "\tpublic function search()\n";
		$output .= "\t{\n";
		
		$json = json_encode($data);
		foreach($keys as $key)
		{
			$parts = explode('|-|', $key);
			if(substr($parts[0], -6) == '_array')
			{
				$vals = explode('/-/', $parts[1]);
				$replacepattern = array();
				foreach($vals as $val)
				{
					if(substr($val, -1) != '$') $val .= '$';
					$replacepattern[] = $parts[0] . '|-|' . $val;
				}
				$key = implode('","', $replacepattern);
				$json = str_replace('["' . $key . '"]', "' . json_encode(\$this->" . str_replace('$', '', $parts[0]) . ") . '", $json);
			}
			else 
			{
				$json = str_replace($key, "' . \$this->" . str_replace('$', '', $parts[0]) . " . '", $json);	
			}
		}
		
		$json = $query->prettyJson($json);
		
		$output .= "\t\t\$json = '$json';\n\n";
		$output .= "\t\t\$queryBuilder = new \Elastica\Query\Builder(\$json);\n\n";
		$output .= "\t\t\$queryBuilder = new \Elastica\Query(\$queryBuilder->toArray());\n\n";
		$output .= "\t\t\$search = new \Elastica\Search(new \Elastica\Client());\n\n";
		$output .= "\t\treturn \$search->search(\$queryBuilder);\n";
		$output .= "\t}\n";

		return $output;	
	}
	
	private function elastica_create_functions($keys)
	{
		$output = '';
		foreach($keys as $key)
		{
			$parts = explode('|-|', $key);
			$trimmedkey = trim($parts[0], '$');
			$output .= "\n\t/**\n";
			$output .= "\t * This function sets $trimmedkey for the search\n";
			if(substr($parts[0], -6) == '_array')
			{
				$output .= "\t * @param array \$value Search value for $trimmedkey\n";
			}
			else
			{
				$output .= "\t * @param string \$value Search value for $trimmedkey\n";	
			}
			$output .= "\t * @return\n";
			$output .= "\t */\n";
			$output .= "\tpublic function set_$trimmedkey(\$value)\n";
			$output .= "\t{\n";
			$output .= "\t\t\$this->$trimmedkey = \$value;\n";
			$output .= "\t}\n";
		}
		
		return $output;		
	}
	
	private function elastica_create_usage($data, $keys)
	{
		$output = "}\n\n";
		$output .= '/**
 * Usage example
 */
$test = new elasticaQuery_' . substr(md5(json_encode($data)), 10) . ";\n";
		
		if(isset($keys[0]))
		{
			$parts = explode('|-|', $keys[0]);
			if(substr($parts[0], -6) == '_array')
			{
				$output .= '$test->set_' . trim($parts[0], '$') . '(array("' .  	str_replace('/-/', '","', trim($parts[1], '$')) . "\"));\n";
			}
			else
			{
				$output .= '$test->set_' . trim($parts[0], '$') . '("' .  	trim($parts[1], '$') . "\");\n";	
			}
		}
		return $output;
	}
	
	private function elastica_create_classname($data)
	{
		return "class elasticaQuery_" . substr(md5(json_encode($data)), 10) . " \n{\n";
	}
	
	private function elastica_create_values($keys)
	{
		$output = '';
		foreach($keys as $key)
		{
			$parts = explode('|-|', $key);
			if(substr($parts[0], -6) == '_array')
			{
				$output .= "\t" . 'private $' . trim($parts[0], '$') . ' = array("' . str_replace('/-/', '","', trim($parts[1], '$')) . "\");\n";
			}
			else 
			{
				$output .= "\t" . 'private $' . trim($parts[0], '$') . ' = "' . trim($parts[1], '$') . "\";\n";	
			}
		}
		
		return $output;
	}
	
	private function elastica_iterate_findKeys($array, $grandkey = '')
	{
		foreach($array as $key => $value)
		{
			if(is_string($value))
			{
				if(is_integer($key))
				{
					$array[$key] = '$' . ucfirst($grandkey) . '_array' . '|-|' . $value . '$';	
				}
				else
				{
					$array[$key] = '$' . ucfirst($grandkey) . ucfirst($key) . '|-|' . $value . '$';						
				}
			}
			else 
			{
				$nextkey = ucfirst($grandkey) . ucfirst($key);
				$array[$key] = $this->elastica_iterate_findKeys($value, $nextkey);	
			}
		}
		return $array;
	}
	
	private function elastica_build_header()
	{
		return '<?php
/**
 * This file searches via the Elastica client
 * @author: PHPElasticManager
 */

// Only keep this if you haven\'t autoloaded elastica yet
require_once(\'vendor/autoload.php\');' . "\n\n";
	}
	
	private function elastica_build_footer()
	{
		return '$resultSet = $test->search();

// Run through the result and print_r it
foreach($resultSet as $result)
{
	print_r($result->getData());	
}
?>';
	}
}