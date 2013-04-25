<?php

/**
 * Query takes care of all calls to Elasticsearch
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class Query extends router
{
    /**
     * Construnction
	 * 
     * @param string $server
     * @param string $port
     */
    public function __construct($server = 'http://localhost', $port = '9200')
    {
        $this->es_server = $server . ':' . $port;
    }

    /**
     * Checks if validation mode is on before making a call
	 * 
     * @param string $path The path to call
     * @param string $method The method to use
     * @param string $data The data to pass
	 * 
     * @return array/redirect
     */
    public function callWithCheck($path, $method = 'POST', $data = '', $redirect = '')
    {
        if ($_SESSION['query_validation']) {
            $_SESSION['query_path'] = $path;
            $_SESSION['query_method'] = $method;
            $_SESSION['query_query'] = $this->prettyJson($data);
            $_SESSION['query_redirect'] = $redirect;
            $this->redirect('query/query');
        } else {
            $return = $this->call($path, $method, $data);
            if (isset($return['error'])) {
                $_SESSION['query_response'] = $return;
            }
            $this->redirect($redirect);
        }
    }

    /**
     * Makes a call to ES
	 * 
     * @param string $path The path to call
     * @param string $method The method to use
     * @param string $data The data to pass
	 * 
     * @return array
     */
    public function call($path, $method = 'POST', $data = '')
    {
        $ch = curl_init();

        $url = $this->es_server . '/' . trim($path, '/');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            // Trim the data
            $data = $this->trimData($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $data = curl_exec($ch);

        curl_close($ch);

        $jsondata = json_decode($data);

        // If it is JSON, convert it to array and return
        if ($jsondata) {
            $jsondata = $this->objectToArray($jsondata);

            return $jsondata;
        }

        return $data;
    }

    /**
     * We don't want to be PHP 5.4 dependent so we use a pretify JSON function.
     * Thanks to camdagr8 who posted this to snipplr. Works great!
	 * 
     * @param string $json
	 * 
     * @return string
     */
    public function prettyJson($json)
    {

        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
            } elseif (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }

    /**
     * Recursive function get the value fields from a elasticsearch
	 * mapping array
	 * 
     * @param array $properties The mapping array
	 * @param array $array An array passed/returned by reference to keep the key/value mapping
	 * @param integer $level The level of recursion
	 * @param string $parentname The parents name
	 * 
     * @return array
     */
    public function getValueFields($properties, &$array = array(), $level = 0, $parentname = '')
    {
        $nested = array();

        if (isset($properties['properties'])) {
            foreach ($properties['properties'] as $name => $values) {
                if (isset($values['properties']) || $values['type'] == 'nested' || $values['type'] == 'object') {
                    $array[$name]['name'] = $name;
                    $this->getValueFields($values, $array, 1, $name);
                } elseif ($values['type'] == 'multi_field') {
                    foreach ($values['fields'] as $mfkey => $mfvalue) {
                        $array[$name]['fields'][] = array(
                            'name' => $name == $mfkey ? '' : $mfkey,
                            'type' => $mfvalue['type']
                        );
                    }
                } else {
                    $array[$parentname]['fields'][] = array('name' => $name, 'type' => $values['type']);
                }
                if (!$level) {
                    $prefix = '';
                    foreach ($array as $key => $value) {
                        $prefix .= $key . '.';
                        if (isset($value['fields'])) {
                            foreach ($value['fields'] as $field) {
                                $nested[$field['type']][] = $prefix . $field['name'];
                            }
                        }

                    }
                    unset($array);
                }
            }
        }

        return $nested;
    }

    /**
     * Recursive function to help nest mapping
	 * 
     * @param array $properties The mapping properties
	 * @param array $array An array passed/returned by reference to keep the key/value mapping
	 * 
     * @return array The cleaned property
     */
    public function nestMapping($properties, $array)
    {
        if (count($array) == 1) {
            return $properties['properties'][$array[0]];
        } else {
            $key = array_shift($array);

            return $this->nestMapping($properties['properties'][$key], $array);
        }
    }
	
    /**
     * Function to create array from a elasticsearch
	 * settings notation.
	 * 
     * @param string $esnotation elasticsearch settings notation
	 * @param string $explodeval Value to explode on
	 * @param string $arrayval Value what should be noted as array
	 * 
     * @return array The notation in array format
     */
    public function toArray($esnotation, $explodeval = '.', $arrayval = '')
    {
        $newarray = array();
        foreach ($esnotation as $partarray) {
            foreach ($partarray as $key => $value) {
                $parts = explode($explodeval, $key);

                $array = $this->iterateToArray($parts, $value, $arrayval);
                $newarray = array_merge_recursive($array, $newarray);
            }
        }

        $endarray = $this->stupidIterateMakeNormalArray($newarray);

        return $endarray;
    }
	
    /**
     * Recursive function to create real array from assoc array
	 * 
     * @param array $results Results from elasticsearch
	 * 
     * @return array Assoc array
     */
    public function nonAssocArrays($results)
    {
        $i = 0;
        if (is_array($results)) {
            foreach ($results as $key => $value) {
                if (is_integer($key)) {
                    $output[$i] = $this->realArrays($value);
                    $i++;
                } else {
                    $output[$key] = $this->realArrays($value);
                }
            }
        } else {
            $output = $results;
        }

        return $output;
    }

    /**
     * Output array result as string result
	 * 
	 * @param array $value Values from elasticsearch
     * @param array $source Results from elasticsearch
	 * 
     * @return string|array In the end returns a comma separated string
     */
    public function findResult($value, $source)
    {
        if (count($value) == 1) {
            if (isset($source[$value[0]])) {
                return $source[$value[0]];
            } else {
                $output = array();
                foreach ($source as $key => $val) {
                    $output[] = $val[$value[0]];
                }

                return implode(', ', $output);
            }
        } else {
            $key = array_shift($value);

            return $this->findResult($value, $source[$key]);
        }
    }	

    /**
     * Outputs mapping structure
	 * 
	 * @param array $props Properties
	 * 
     * @return string Mapping structure
     */
    public function mappingStructure($props)
    {
        $output = '';
        foreach ($props as $key => $value) {
            $output .= '<li><strong>' . $key . '</strong><ul>';

            foreach ($value as $formkey => $formvalue) {
                if ($formkey != 'properties') {
                    $output .= '<li><strong>' . $formkey . ':</strong>' . $formvalue . '</li>';
                }
            }

            if (isset($value['properties'])) {
                $output .= $this->mappingStructure($value['properties']);
            }

            $output .= '</ul></li>';
        }

        return $output;
    }

    /**
     * Recursive function to put nested objects
	 * 
	 * @param array $array An array passed/returned by reference to keep the key/value mapping
	 * @param array $properties elasticsearch results
	 *  
     * @return array Nested structure
     */
    public function putNested($array, $properties)
    {
        if (count($array) == 1) {
            $output[$array[0]]['properties'] = $properties;
        } else {
            $part = array_shift($array);
            $output[$part]['properties'] = $this->putNested($array, $properties);
        }

        return $output;
    }

    /**
     * Recursive function to get nested objects
	 * 
	 * @param array $properties elasticsearch results
	 * @param array $array An array passed/returned by reference to keep the key/value mapping
	 * @param integer $level level of iteration
	 *  
     * @return array Nested structure
     */
    public function getNested($properties, &$array = array(), $level = 0)
    {
        $nested = array();

        if(!$level) $nested[''] = '_root';

        if (isset($properties['properties'])) {
            foreach ($properties['properties'] as $name => $values) {
                if (isset($values['properties']) || $values['type'] == 'nested' || $values['type'] == 'object' || $values['type'] == 'multi_field') {
                    $array[] = $name;
                    $this->getNested($values, $array, 1);
                }
                if (!$level) {
                    $prefix = '';
					if(isset($array))
					{
	                    foreach ($array as $key) {
	                        $nested[$prefix . $key . '---' . $values['type']] = $prefix . $key;
	                        $prefix .= $key . '.';
	                    }
					}
                    unset($array);
                }
            }
        }

        return $nested;
    }

    /**
     * Function to automate filter formating
	 * 
	 * @param array $results elasticsearch results
	 *  
     * @return array Mapping structure
     */
    public function createMapping($results)
    {
        $outputmap = array();
        $map = array();

        $name = $results['name'];
        unset($results['name']);
        foreach ($results as $key => $value) {
            $keyparts = explode('_', $key);
            if ($keyparts[0] == 'tokenfilter' && end($keyparts) == 'check' && $value) {
                unset($keyparts[0]);
                end($keyparts);
                unset($keyparts[key($keyparts)]);
                $map[implode('_', $keyparts)] = array();
            } elseif ($keyparts[0] == 'tokenfilter') {
                $mapname = array();
                $functionname = array();
                $bounded = true;
                unset($keyparts[0]);

                foreach ($keyparts as $namingparts) {
                    if(!$namingparts) $bounded = false;

                    if ($bounded && $namingparts) {
                        $mapname[] = $namingparts;
                    } elseif ($namingparts) {
                        $functionname[] = $namingparts;
                    }
                }
                $temp_mapname = implode('_', $mapname);

                if (isset($map[$temp_mapname])) {
                    $temp_functioname = implode('_', $functionname);
                    $map[$temp_mapname][$temp_functioname] = $value;
                }
            }
        }

        // Create all custom solutions

        // Stopwords
        if (isset($map['stop']['language']) && count($map['stop']['language'])) {
            $map['stop']['stopwords'] = $map['stop']['language'];
        }
        unset($map['stop']['language']);

        $outputmap['analysis']['analyzer'][$name]['type'] = 'custom';
        $outputmap['analysis']['analyzer'][$name]['tokenizer'] = 'standard';
        foreach ($map as $key => $value) {
            if (count($value)) {
                $filtername = $key . time() . 'Filter';
                $outputmap['analysis']['analyzer'][$name]['filter'][] = $filtername;
                $outputmap['analysis']['filter'][$filtername]['type'] = $key;
                foreach ($value as $option => $optionvalue) {
                    if ($optionvalue != '') {
                        $outputmap['analysis']['filter'][$filtername][$option] = $optionvalue;
                    }
                }
            } else {
                $outputmap['analysis']['analyzer'][$name]['filter'][] = $key;
            }
        }

        return $outputmap;
    }
		
    /**
     * Recursive function to create array from object
	 * 
     * @param object $object Object to make to array
	 * 
     * @return array
     */
    private function objectToArray($object)
    {
        $array = is_object($object) ? get_object_vars($object) : $object;
        foreach ($array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
            $array[$key] = $value;
        }

        return $array;
    }
	
    /**
     * Recursive function to help toArray function
	 * 
     * @param array $array The notations
	 * @param string $value Value of the notation 
	 * @param string $arrayval If the values are array
	 * 
     * @return array The cleaned up array
     */
    private function iterateToArray($array, $value, $arrayval)
    {
        $i = 0;
        $make_array = false;

        foreach ($array as $string) {
            if (!$i) {
                if ($arrayval) {
                    if (substr($string, 0, strlen($arrayval)) == $arrayval) {
                        $make_array = true;
                        $newstring = substr($string, strlen($arrayval));
                        $parts = explode('_', $newstring);
                        $nr = (int) $parts[0];
                        unset($parts[0]);
                        $string = implode('_', $parts);
                    }
                }

                array_shift($array);
                if (count($array)) {
                    if ($make_array) {
                        $output['array_' . $nr][$string] = $this->iterateToArray($array, $value, $arrayval);
                    } else {
                        $output[$string] = $this->iterateToArray($array, $value, $arrayval);
                    }

                } else {
                    if ($make_array) {
                        if ($string) {
                            $output[$nr][$string] = $value;

                        } else {
                            $output[$nr] = $value;
                        }
                    } else {
                        $output[$string] = $value;
                    }
                }
            }
            $i++;
        }

        return $output;
    }
	
    /**
     * Recursive function to help toArray function to reverse
	 * the array
	 * 
     * @param array $newarray The cleaned up array
	 * 
     * @return array The reversed array
     */
    private function stupidIterateMakeNormalArray($newarray)
    {
        if (is_array($newarray)) {
            $newarray = array_reverse($newarray);
            $endarray = array();

            foreach ($newarray as $key => $value) {
                if (substr($key, 0, 6) == 'array_') {
                    $key = (int) substr($key, 6);
                }
                $endarray[$key] = $this->stupidIterateMakeNormalArray($value);
            }
        } else {
            $endarray = $newarray;
        }

        return $endarray;
    }
	
    /**
     * Trims the json string from whitespaces, tabs and new lines
	 * 
     * @param string $json JSON string
	 * 
     * @return string Trimmed JSON string
     */
    private function trimData($json)
    {
        return str_replace(array("\t","\n", "\r\n", "\r"), "", $json);
    }	
}
