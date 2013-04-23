<?php

/**
 * Query takes care of all calls to Elasticsearch
 * 
 * @param string $server Elasticsearch server
 * @param string $port Elasticsearch port
 * @author Marcus Johansson
 * @version 0.1
 */
class query extends router
{
	static $es_server;

	/**
	 * Construnction
	 * @param string $server
	 * @param string $port
	 */		
	public function __construct($server = 'http://localhost', $port = '9200') {
		self::$es_server = $server . ':' . $port;
	}
	
	/**
	 * Checks if validation mode is on before making a call
	 * @param string $path
	 * @param string $method
	 * @param string $data
	 * @return array/redirect
	 */
	public function callWithCheck($path, $method = 'POST', $data = '', $redirect = '')
	{
		if($_SESSION['query_validation'])
		{
			$_SESSION['query_path'] = $path;
			$_SESSION['query_method'] = $method;
			$_SESSION['query_query'] = $this->prettyJson($data);
			$_SESSION['query_redirect'] = $redirect;
			$this->redirect('query/query');
		}
		else 
		{
			$return = $this->call($path, $method, $data);
			if(isset($return['error']))
			{
				$_SESSION['query_response'] = $return;
			}
			$this->redirect($redirect);
		}
	}
	
	/**
	 * Makes a call to ES
	 * @param string $path
	 * @param string $method
	 * @param string $data
	 * @return array
	 */	
	public function call($path, $method = 'POST', $data = '')
	{
		$ch = curl_init();
		
		$url = self::$es_server . '/' . trim($path, '/');
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		
		if($data)
		{
			// Trim the data
			$data = $this->trimData($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		$data = curl_exec($ch);
		
		curl_close($ch);
		
		$jsondata = json_decode($data);
		
		// If it is JSON, convert it to array and return
		if($jsondata)
		{
			$jsondata = $this->objectToArray($jsondata);
			return $jsondata;
		}	
		return $data;
	}

	/**
	 * We don't want to be PHP 5.4 dependent so we create a pretify JSON function.
	 * Thanks to camdagr8 who posted this to snipplr
	 * @param string $json
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
	        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
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
	 * Recursive function to create array from object
	 * @param object $object
	 * @return array
	 */
	private function objectToArray($object)
	{
        $array = is_object($object) ? get_object_vars($object) : $object;
        foreach ($array as $key => $value) 
        {
        	$value = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
        	$array[$key] = $value;
        }
        return $array;
	}
	
	/**
	 * Trims the json string from whitespaces, tabs and new lines
	 * @param string $json JSON string
	 * @return string Trimmed JSON string
	 */
	private function trimData($json)
	{
		return str_replace(array("\t","\n", "\r\n", "\r"), "", $json);
	}
}

?>