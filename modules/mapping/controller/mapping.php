<?php

class controllerMapping extends router
{
	public function __construct() {

	}
	
	public function page_edit($args)
	{
		$state = parent::$queryLoader->call('_cluster/state', 'GET');
		
		$variables['properties'] = array();
		if(isset($state['metadata']['indices'][$args[0]]['mappings'][$args[1]]['properties']))
		{
			$variables['properties'] = $state['metadata']['indices'][$args[0]]['mappings'][$args[1]]['properties'];
		}
		$variables['name'] = $args[0];
		$variables['document_type'] = $args[1];
		$vars['content'] = $this->renderPart('mapping', $variables);
		$vars['title'] = 'Edit document type: ' . $args[0];
		return $vars;
	}
	
	public function page_view_analyzer($args)
	{
		$state = parent::$queryLoader->call('_cluster/state', 'GET');
		$array = $this->toArray(array($state['metadata']['indices'][$args[0]]['settings']));
		
		if(isset($array['index']['analysis']['analyzer']))
		{
			foreach($array['index']['analysis']['analyzer'] as $name => $value)
			{
				if($name == $args[1])
				{
					$arguments['type'] = $value['type'];
					$arguments['tokenizer'] = $value['tokenizer'];
					if(isset($value['filter']))
					{
						foreach($value['filter'] as $parts)
						{
							$arguments['filter'][$parts] = $parts;
						}
					}
				}
			}
		}
		
		if(isset($array['index']['analysis']['filter']))
		{
			foreach($array['index']['analysis']['filter'] as $name => $value)
			{
				if(isset($arguments['filter'][$name]))
				{
					$arguments['filter'][$name] = $value;
				}
			}
		}

		$vars['content'] = $this->renderPart('mapping_analyzer', $arguments);
		$vars['title'] = 'View analyzer: ' . $args[1];
		return $vars;
	}
	
	public function page_create_analyzer($args)
	{
		$form = new form($this->form_create_analyzer($args));
		
		$form->createForm();
		
		$arguments['field'] = $args[0];
		$arguments['form'] = $form->renderForm();
		$vars['javascript'][] = 'custom/es_analyzer.js';
		$vars['javascript'][] = 'custom/forms.js';			
		$vars['content'] = $this->renderPart('mapping_create_field', $arguments);
		$vars['title'] = 'Create analyzer for index: ' . $args[0];		
		return $vars;
	}
	
	public function page_create_analyzer_post($args)
	{
		$form = new form($this->form_create_field($args));
		$results = $form->getResults();
		
		$mapping = $this->createMapping($results);

		// Close the index
		parent::$queryLoader->call($results['index'] . '/_close', 'POST');
		
		// Change the mapping
		$url = $results['index'] . '/_settings';
		parent::$queryLoader->call($url, 'PUT', json_encode($mapping));
		
		// Open the index
		parent::$queryLoader->call($results['index'] . '/_open', 'POST');
		$this->redirect('index/edit/' . $results['index']);
	}

	public function page_create_field($args)
	{
		$state = parent::$queryLoader->call('_cluster/state', 'GET');
		$array = $this->toArray(array($state['metadata']['indices'][$args[0]]['settings']));
		
		$args['analyzers'] = array();
		if(isset($array['index']['analysis']['analyzer']))
		{
			foreach($array['index']['analysis']['analyzer'] as $name => $value)
			{
				if(!in_array($name, $args['analyzers'])) $args['analyzers'][] = $name;
			}
		}
		
		$form = new form($this->form_create_field($args));
		
		$form->createForm();
		
		$arguments['field'] = $args[0];
		$arguments['form'] = $form->renderForm();
		$vars['javascript'][] = 'custom/es_field.js';
		$vars['javascript'][] = 'custom/forms.js';
		$vars['content'] = $this->renderPart('mapping_create_field', $arguments);
		$vars['title'] = 'Create field in document type: ' . $args[1];
		return $vars;
	}


	public function page_create_field_post($args)
	{
		$form = new form($this->form_create_field($args));
		$results = $form->getResults();

		$properties[$results['name']]['type'] = $results['type'];
		// If not include in all
		if(!$results['include_in_all'])
		{
			$properties[$results['name']]['include_in_all'] = false;
		}
		
		if(!$results['searchable'])
		{
			$properties[$results['name']]['index'] = 'no';
		}
		// Check if not analyzed, otherwise append a analyzer
		elseif(!$results['not_analyzed_check'])
		{
			$properties[$results['name']]['index'] = 'not_analyzed';
		}
		else
		{
			if($results['analyzer_type'] != 'standard')
			{
				$properties[$results['name']]['analyzer'] = $results['analyzer_type'];
			}
		}
		
		if($results['term_vector'])
		{
			$properties[$results['name']]['term_vector'] = $results['term_vector'];
		}
		
		if($results['boost'])
		{
			$properties[$results['name']]['boost'] = $results['boost'];
		}
		
		if($results['ignore_above'])
		{
			$properties[$results['name']]['ignore_above'] = $results['ignore_above'];
		}
		
		if($results['position_offset_gap'])
		{
			$properties[$results['name']]['position_offset_gap'] = $results['position_offset_gap'];
		}
		
		if($results['null_value'])
		{
			$properties[$results['name']]['null_value'] = $results['null_value'];
		}

		if($results['omit_norm'])
		{
			$properties[$results['name']]['omit_norm'] = $results['omit_norm'];
		}

		if($results['index_options'])
		{
			$properties[$results['name']]['index_options'] = $results['index_options'];
		}
		
		$data[$results['document_type']]['properties'] = $properties;
		
		$url = $results['index'] .'/' . $results['document_type'] . '/_mapping';
		
		parent::$queryLoader->callWithCheck($url, 'PUT', json_encode($data), 'mapping/edit/' . $results['index'] . '/' . $results['document_type']);	
	}
	
	// Function to automate filter formating
	private function createMapping($results)
	{
		$outputmap = array();
		$map = array();
		
		$name = $results['name'];
		unset($results['name']);
		foreach($results as $key => $value)
		{
			$keyparts = explode('_', $key);
			if($keyparts[0] == 'tokenfilter' && end($keyparts) == 'check' && $value)
			{
				unset($keyparts[0]);
				end($keyparts);
				unset($keyparts[key($keyparts)]);
				$map[implode('_', $keyparts)] = array();
			}
			elseif($keyparts[0] == 'tokenfilter')
			{
				$mapname = array();
				$functionname = array();
				$bounded = true;
				unset($keyparts[0]);

				foreach($keyparts as $namingparts)
				{
					if(!$namingparts) $bounded = false;
					
					if($bounded && $namingparts)
					{
						$mapname[] = $namingparts;
					}
					elseif($namingparts)
					{
						$functionname[] = $namingparts;
					}
				}
				$temp_mapname = implode('_', $mapname);
				
				if(isset($map[$temp_mapname]))
				{
					$temp_functioname = implode('_', $functionname);
					$map[$temp_mapname][$temp_functioname] = $value;
				}
			}
		}
		
		// Create all custom solutions
		
		// Stopwords
		if(isset($map['stop']['language']) && count($map['stop']['language']))
		{
			$map['stop']['stopwords'] = $map['stop']['language'];
		}
		unset($map['stop']['language']);
		
		$outputmap['analysis']['analyzer'][$name]['type'] = 'custom';
		$outputmap['analysis']['analyzer'][$name]['tokenizer'] = 'standard';
		foreach($map as $key => $value)
		{
			if(count($value))
			{
				$filtername = $key . time() . 'Filter';
				$outputmap['analysis']['analyzer'][$name]['filter'][] = $filtername;
				$outputmap['analysis']['filter'][$filtername]['type'] = $key;
				foreach($value as $option => $optionvalue)
				{
					if($optionvalue != '')
					{
						$outputmap['analysis']['filter'][$filtername][$option] = $optionvalue;				
					}
				}
			}
			else
			{
				$outputmap['analysis']['analyzer'][$name]['filter'][] = $key;				
			}
		}
				
		return $outputmap;
	}
	
	private function form_create_analyzer($args)
	{
		$args[0] = isset($args[0]) ? $args[0] : '';
		
		$form['_init'] = array(
			'name' => 'create_field',
			'action' => 'mapping/create_analyzer_post'
		);
		
		$form['index'] = array(
			'_value' => $args[0],
			'_type' => 'hidden'						  
		);


		$form['tokenfilter'] = array(
			'_type' => 'fieldset',
			'_label' => 'Analyzer settings'
		);
		
		$form['tokenfilter']['name'] = array(
			'_type' => 'textField',
			'_label' => 'Analyzer Name',
			'_description' => 'The name of the analyzer, no whitespace. Name it &quot;default&quot; if you want it to be the default one.',
			'_validation' => array(
				'required'
			)
		);

		$form['tokenfilter']['tokenfilter_standard_check'] = array(
			'_type' => 'checkbox',
			'_value' => true,
			'_label' => 'Standard'
		);

		$form['tokenfilter']['tokenfilter_lowercase_check'] = array(
			'_type' => 'checkbox',
			'_value' => true,
			'_label' => 'Lowercase'
		);

		$form['tokenfilter']['tokenfilter_asciifolding_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'ASCII'
		);

		$form['tokenfilter']['tokenfilter_reverse_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Reverse',
			'_description' => 'A token filter of type reverse that simply reverses the tokens.'
		);


		$form['tokenfilter']['html_strip_charfilter'] = array(
			'_type' => 'checkbox',
			'_label' => 'HTML Strip',
			'_description' => 'A char filter of type html_strip stripping out HTML elements from an analyzed text.'
		);	
		
		$form['tokenfilter']['tokenfilter_trim_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Trim',
			'_description' => 'The trim token filter trims surrounding whitespaces around a token.'
		);

		$form['tokenfilter']['tokenfilter_porterStem_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Porter stemming'
		);

		$form['tokenfilter']['tokenfilter_kstem_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'KStem'
		);
		
		$form['tokenfilter']['snowball_tokenfilter'] = array(
			'_type' => 'fieldset',
		);				
		
		$form['tokenfilter']['snowball_tokenfilter']['tokenfilter_snowball_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Snowball stemmer',
			'_description' => 'A filter that stems words using a Snowball-generated stemmer. The language parameter controls the stemmer with the available values.'
		);
				
		$form['tokenfilter']['snowball_tokenfilter']['tokenfilter_snowball__language'] = array(
			'_type' => 'radios',
			'_options' => array(
				'Armenian' => 'Armenian', 
				'Basque' => 'Basque',
				'Catalan' => 'Catalan',
				'Danish' => 'Danish',
				'Dutch' => 'Dutch',
				'English' => 'English',
				'Finnish' => 'Finnish',
				'French' => 'French',
				'German' => 'German',
				'German2' => 'German2',
				'Hungarian' => 'Hungarian',
				'Italian' => 'Italian',
				'Kp' => 'Kp',
				'Lovins' => 'Lovins',
				'Norwegian' => 'Norwegian',
				'Porter' => 'Porter',
				'Portuguese' => 'Portuguese',
				'Romanian' => 'Romanian',
				'Russian' => 'Russian',
				'Spanish' => 'Spanish',
				'Swedish' => 'Swedish',
				'Turkish' => 'Turkish'
			)
		);
		
		$form['tokenfilter']['stemmer_tokenfilter'] = array(
			'_type' => 'fieldset',
		);				
		
		$form['tokenfilter']['stemmer_tokenfilter']['tokenfilter_stemmer_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Default stemmer',
			'_description' => 'A filter that stems words (similar to snowball, but with more options).'
		);
				
		$form['tokenfilter']['stemmer_tokenfilter']['tokenfilter_stemmer__name'] = array(
			'_type' => 'radios',
			'_options' => array(
				'arabic' => 'Arabic',
				'armenian' => 'Armenian', 
				'basque' => 'Basque',
				'bulgarian' => 'Bulgarian',
				'catalan' => 'Catalan',
				'czech' => 'Czech',
				'danish' => 'Danish',
				'dutch' => 'Dutch',
				'english' => 'English',
				'minimal_english' => 'English (minimal)',
				'possessive_english' => 'English (possessive)',
				'finnish' => 'Finnish',
				'light_finnish' => 'Finnish (light)',
				'french' => 'French',
				'light_french' => 'French (light)',
				'minimal_french' => 'French (minimal)',
				'german' => 'German',
				'light_german' => 'German (light)',
				'minimal_german' => 'German (minimal)',
				'german2' => 'German2',
				'greek' => 'Greek',
				'hindi' => 'Hindi',
				'hungarian' => 'Hungarian',
				'light_hungarian' => 'Hungarian (light)',
				'indonesian' => 'Indonesian',
				'italian' => 'Italian',
				'light_italian' => 'Italian (light)',
				'kp' => 'Kp',
				'kstem' => 'Kstem',
				'lovins' => 'Lovins',
				'latvian' => 'Latvian',
				'norwegian' => 'Norwegian',
				'porter' => 'Porter',
				'portuguese' => 'Portuguese',
				'light_portuguese' => 'Portuguese (light)',
				'minimal_portuguese' => 'Portuguese (minimal)',
				'romanian' => 'Romanian',
				'russian' => 'Russian',
				'light_russian' => 'Russian (light)',
				'spanish' => 'Spanish',
				'light_spanish' => 'Spanish (light)',
				'swedish' => 'Swedish',
				'light_swedish' => 'Swedish (light)',
				'turkish' => 'Turkish'
			)
		);		
		
		$form['tokenfilter']['stemmer_override_tokenfilter'] = array(
			'_type' => 'fieldset',
		);				
		
		$form['tokenfilter']['stemmer_override_tokenfilter']['tokenfilter_stemmer_override_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Override stemming',
			'_description' => 'Overrides stemming algorithms, by applying a custom mapping, then protecting these terms from being modified by stemmers. Will be placed before any stemming filters.

<p>Rules are separted by “=>”</p>'
		);

		$form['tokenfilter']['stemmer_override_tokenfilter']['tokenfilter_stemmer_override__rules'] = array(
			'_type' => 'textField',
			'_label' => 'Rules',
			'_description' => 'A list of mapping rules to use.'
		);

		$form['tokenfilter']['stemmer_override_tokenfilter']['tokenfilter_stemmer_override__rules_path'] = array(
			'_type' => 'textField',
			'_label' => 'Rules path',
			'_description' => 'A path (either relative to config location, or absolute) to a list of mappings. The file must be UTF-8 encoded.'
		);

		$form['tokenfilter']['keyword_marker_tokenfilter'] = array(
			'_type' => 'fieldset',
		);				
		
		$form['tokenfilter']['keyword_marker_tokenfilter']['tokenfilter_keyword_marker_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Keyword Marker',
			'_description' => 'Protects words from being modified by stemmers. Must be placed before any stemming filters.'
		);

		$form['tokenfilter']['keyword_marker_tokenfilter']['tokenfilter_keyword_marker__keywords'] = array(
			'_type' => 'textField',
			'_label' => 'Keywords',
			'_description' => 'A comma separated list of words to use.'
		);

		$form['tokenfilter']['keyword_marker_tokenfilter']['tokenfilter_keyword_marker__keywords_path'] = array(
			'_type' => 'textField',
			'_label' => 'Keywords path',
			'_description' => 'A path (either relative to config location, or absolute) to a list of words.'
		);	

		$form['tokenfilter']['keyword_marker_tokenfilter']['tokenfilter_keyword_marker__ignore_case'] = array(
			'_type' => 'radios',
			'_label' => 'Ignore case',
			'_description' => 'Set to true to lower case all words first. Defaults to false.',
			'_value' => 'false',
			'_options' => array(
				'true' => 'True',
				'false' => 'False'
			)
		);	
		
		$form['tokenfilter']['phonetic_tokenfilter'] = array(
			'_type' => 'fieldset',
		);				
		
		$form['tokenfilter']['phonetic_tokenfilter']['tokenfilter_phonetic_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Phonetic',
			'_description' => 'The Phonetic Analysis plugin integrates phonetic token filter analysis with elasticsearch. This is a plugin that need to be installed.'
		);
				
		$form['tokenfilter']['phonetic_tokenfilter']['tokenfilter_phonetic__encoder'] = array(
			'_type' => 'radios',
			'_label' => 'Encoder',
			'_options' => array(
				'metaphone' => 'Metaphone', 
				'doublemetaphone' => 'Double Metaphone',
				'soundex' => 'Soundex',
				'refinedsoundex' => 'Refined Soundex',
				'caverphone1' => 'Caverphone 1',
				'caverphone2' => 'Caverphone 2',
				'cologne' => 'Cologne',
				'nysiis' => 'New York State Identification and Intelligence System',
				'koelnerphonetik' => 'Kölner Phonetik',
				'haasephonetik' => 'Haase Phonetik',
				'beidermorse' => 'Beider-Morse'
			)
		);
		
				
		$form['tokenfilter']['phonetic_tokenfilter']['tokenfilter_phonetic__replace'] = array(
			'_type' => 'radios',
			'_description' => 'The replace parameter (defaults to true) controls if the token processed should be replaced with the encoded one (set it to true), or added (set it to false).',
			'_label' => 'Replace',
			'_options' => array(
				'true' => 'True', 
				'false' => 'False'
			)
		);
		
		$form['tokenfilter']['word_delimiter'] = array(
			'_type' => 'fieldset',
		);				
		
		$form['tokenfilter']['word_delimiter']['tokenfilter_word_delimiter_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Word delimiter token filter'
		);
		
		$form['tokenfilter']['word_delimiter']['tokenfilter_word_delimiter__options'] = array(
			'_type' => 'checkboxes',
			'_options' => array(
				'generate_word_parts' => 'Generate word parts',
				'generate_number_parts' => 'Generate number parts',
				'catenate_words' => 'Catenate words',
				'catenate_numbers' => 'Catenate numbers',
				'catenate_all' => 'Catenate all',
				'split_on_case_change' => 'Split on case change',
				'preserve_original' => 'Preserve original',
				'split_on_numerics' => 'Split on numerics',
				'stem_english_possessive' => 'Stem English possessive'
			)
		);
		
		$form['tokenfilter']['length_tokenfilter'] = array(
			'_type' => 'fieldset'
		);
		
		$form['tokenfilter']['length_tokenfilter']['tokenfilter_length_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Length',
			'_description' => 'A token filter of type length that removes words that are too long or too short for the stream.'
		);
		
		$form['tokenfilter']['length_tokenfilter']['tokenfilter_length__min'] = array(
			'_type' => 'textField',
			'_label' => 'Minimum number',
			'_description' => 'The minimum number. Defaults to 0.',
			'_value' => 0
		);

		$form['tokenfilter']['length_tokenfilter']['tokenfilter_length__max'] = array(
			'_type' => 'textField',
			'_label' => 'Maximum number',
			'_description' => 'The maximum number. Defaults to Integer.MAX_VALUE.',
			'_value' => 'Integer.MAX_VALUE'
		);				

		$form['tokenfilter']['ngram_tokenfilter'] = array(
			'_type' => 'fieldset'
		);
		
		$form['tokenfilter']['ngram_tokenfilter']['tokenfilter_ngram_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'nGram',
			'_description' => 'A token filter of type nGram.'
		);
		
		$form['tokenfilter']['ngram_tokenfilter']['tokenfilter_ngram__min'] = array(
			'_type' => 'textField',
			'_label' => 'Minimum number',
			'_description' => 'The minimum number. Defaults to 1.',
			'_value' => 1
		);

		$form['tokenfilter']['ngram_tokenfilter']['tokenfilter_ngram__max'] = array(
			'_type' => 'textField',
			'_label' => 'Maximum number',
			'_description' => 'The maximum number. Defaults to 2.',
			'_value' => 2
		);
		
		$form['tokenfilter']['edgengram_tokenfilter'] = array(
			'_type' => 'fieldset'
		);
		
		$form['tokenfilter']['edgengram_tokenfilter']['tokenfilter_edgengram_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'edgeNGram',
			'_description' => 'A token filter of type edgeNGram.'
		);
		
		$form['tokenfilter']['edgengram_tokenfilter']['tokenfilter_edgengram__min'] = array(
			'_type' => 'textField',
			'_label' => 'Minimum number',
			'_description' => 'The minimum number. Defaults to 1.',
			'_value' => 1
		);

		$form['tokenfilter']['edgengram_tokenfilter']['tokenfilter_edgengram__max'] = array(
			'_type' => 'textField',
			'_label' => 'Maximum number',
			'_description' => 'The maximum number. Defaults to 2.',
			'_value' => 2
		);	

		$form['tokenfilter']['edgengram_tokenfilter']['tokenfilter_edgengram__side'] = array(
			'_type' => 'select',
			'_label' => 'Side',
			'_description' => 'Either front or back. Defaults to front.',
			'_value' => 'front',
			'_options' => array(
				'back' => 'Back',
				'front' => 'Front'
			)
		);
		
		$form['tokenfilter']['shingle_tokenfilter'] = array(
			'_type' => 'fieldset'
		);
		
		$form['tokenfilter']['shingle_tokenfilter']['tokenfilter_shingle_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Shingle',
			'_description' => 'A token filter of type shingle that constructs shingles (token n-grams) from a token stream. In other words, it creates combinations of tokens as a single token. For example, the sentence “please divide this sentence into shingles” might be tokenized into shingles “please divide”, “divide this”, “this sentence”, “sentence into”, and “into shingles”.
<p>
This filter handles position increments > 1 by inserting filler tokens (tokens with termtext “_”). It does not handle a position increment of 0.</p>'
		);
		
		$form['tokenfilter']['shingle_tokenfilter']['tokenfilter_shingle__min'] = array(
			'_type' => 'textField',
			'_label' => 'Minimum shingles',
			'_description' => 'The minimum shingle size. Defaults to 2.',
			'_value' => 2
		);

		$form['tokenfilter']['shingle_tokenfilter']['tokenfilter_shingle__max'] = array(
			'_type' => 'textField',
			'_label' => 'Maximum shingles',
			'_description' => 'The maxmimum shingle size. Defaults to 2.',
			'_value' => 2
		);	

		$form['tokenfilter']['shingle_tokenfilter']['tokenfilter_shingle__output_unigrams'] = array(
			'_type' => 'radios',
			'_label' => 'Output unigrams',
			'_description' => 'If true the output will contain the input tokens (unigrams) as well as the shingles. Defaults to true.',
			'_value' => 'true',
			'_options' => array(
				'true' => 'True',
				'false' => 'False'
			)
		);		

		$form['tokenfilter']['shingle_tokenfilter']['tokenfilter_shingle__output_unigrams_if_no_shingles'] = array(
			'_type' => 'radios',
			'_label' => 'Output unigrams if no shingles',
			'_description' => 'If output_unigrams is false the output will contain the input tokens (unigrams) if no shingles are available. Note if output_unigrams is set to true this setting has no effect. Defaults to false.',
			'_value' => 'true',
			'_options' => array(
				'true' => 'True',
				'false' => 'False'
			)
		);

		$form['tokenfilter']['shingle_tokenfilter']['tokenfilter_shingle__separator'] = array(
			'_type' => 'textField',
			'_label' => 'Separator',
			'_description' => 'The string to use when joining adjacent tokens to form a shingle. Defaults to &quot; &quot;.',
			'_value' => ' '
		);
		
		$form['tokenfilter']['synonym_tokenfilter'] = array(
			'_type' => 'fieldset'
		);	
		
		$form['tokenfilter']['synonym_tokenfilter']['tokenfilter_synonym_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Synonym',
			'_description' => 'The synonym token filter allows to easily handle synonyms during the analysis process.'
		);

		$form['tokenfilter']['synonym_tokenfilter']['tokenfilter_synonym__format'] = array(
			'_label' => 'Format',
			'_type' => 'select',
			'_description' => 'As of elasticsearch 0.17.9 two synonym formats are supported: Solr, WordNet.',
			'_options' => array(
				'solr' => 'Solr', 
				'wordnet' => 'WordNet'
			)
		);
		
		$form['tokenfilter']['synonym_tokenfilter']['tokenfilter_synonym__synonyms'] = array(
			'_type' => 'textField',
			'_label' => 'Synonyms',
			'_description' => 'A semicomma separated list of rules to use.'
		);

		$form['tokenfilter']['synonym_tokenfilter']['tokenfilter_synonym__synonyms_path'] = array(
			'_type' => 'textField',
			'_label' => 'Synonyms path',
			'_description' => 'A path (either relative to config location, or absolute) to a synonym rules files. The file must be UTF-8 encoded.'
		);	
		
		$form['tokenfilter']['stop_tokenfilter'] = array(
			'_type' => 'fieldset'
		);
		
		$form['tokenfilter']['stop_tokenfilter']['tokenfilter_stop_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Stop',
			'_description' => 'A token filter of type stop that removes stop words from token streams.'
		);
		
		$form['tokenfilter']['stop_tokenfilter']['tokenfilter_stop__stopwords'] = array(
			'_type' => 'textField',
			'_label' => 'Stopwords',
			'_description' => 'A comma separated list of stop words to use. Defaults to english stop words.'
		);

		$form['tokenfilter']['stop_tokenfilter']['tokenfilter_stop__stopwords_path'] = array(
			'_type' => 'textField',
			'_label' => 'Stopwords path',
			'_description' => 'A path (either relative to config location, or absolute) to a stopwords file configuration. Each stop word should be in its own “line” (separated by a line break). The file must be UTF-8 encoded.'
		);	

		$form['tokenfilter']['stop_tokenfilter']['tokenfilter_stop__position_increments'] = array(
			'_type' => 'radios',
			'_label' => 'Position increments',
			'_description' => 'Set to true if token positions should record the removed stop words, false otherwise. Defaults to true.',
			'_value' => 'true',
			'_options' => array(
				'true' => 'True',
				'false' => 'False'
			)
		);
		
		$form['tokenfilter']['stop_tokenfilter']['tokenfilter_stop__ignore_case'] = array(
			'_type' => 'radios',
			'_label' => 'Ignore case',
			'_description' => 'Set to true to lower case all words first. Defaults to false.',
			'_value' => 'false',
			'_options' => array(
				'true' => 'True',
				'false' => 'False'
			)
		);

		$form['tokenfilter']['stop_tokenfilter']['tokenfilter_stop__language'] = array(
			'_type' => 'checkboxes',
			'_label' => 'Languages',
			'_options' => array(
				'_armenian_' => 'Armenian', 
				'_basque_' => 'Basque',
				'_brazilian_' => 'Brazilian',
				'_bulgarian_' => 'Bulgarian',
				'_catalan_' => 'Catalan',
				'_czech_' => 'Czech',
				'_danish_' => 'Danish',
				'_dutch_' => 'Dutch',
				'_english_' => 'English',
				'_finnish_' => 'Finnish',
				'_french_' => 'French',
				'_galician_' => 'Galician',
				'_german_' => 'German',
				'_greek_' => 'Greek',
				'_hindi_' => 'Hindi',
				'_hungarian_' => 'Hungarian',
				'_indonesian_' => 'Indonesian',
				'_italian_' => 'Italian',
				'_norwegian_' => 'Norwegian',
				'_persian_' => 'Persian',
				'_portuguese_' => 'Portuguese',
				'_romanian_' => 'Romanian',
				'_russian_' => 'Russian',
				'_spanish_' => 'Spanish',
				'_swedish_' => 'Swedish',
				'_turkish_' => 'Turkish'
			)
		);

		$form['tokenfilter']['compund_word_tokenfilter'] = array(
			'_type' => 'fieldset'
		);	
		
		$form['tokenfilter']['compund_word_tokenfilter']['tokenfilter_compund_word_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Compound word',
			'_description' => 'Token filters that allow to decompose compound words. There are two types available: dictionary_decompounder and hyphenation_decompounder.'
		);	

		$form['tokenfilter']['compund_word_tokenfilter']['tokenfilter_compund_word__type'] = array(
			'_label' => 'Type',
			'_type' => 'select',
			'_options' => array(
				'dictionary_decompounder' => 'Dictionary decompounder', 
				'hyphenation_decompounder' => 'Hyphenation decompounder'
			)
		);
		
		$form['tokenfilter']['compund_word_tokenfilter']['tokenfilter_compund_word__word_list'] = array(
			'_type' => 'textField',
			'_label' => 'Word list',
			'_description' => 'A semicomma separated list of words to use.'
		);

		$form['tokenfilter']['compund_word_tokenfilter']['tokenfilter_compund_word__word_list_path'] = array(
			'_type' => 'textField',
			'_label' => 'Word list path',
			'_description' => 'A path (either relative to config location, or absolute) to a word list rules files. The file must be UTF-8 encoded.'
		);

		$form['tokenfilter']['elision_tokenfilter'] = array(
			'_type' => 'fieldset'
		);	
		
		$form['tokenfilter']['elision_tokenfilter']['tokenfilter_elision_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Elision',
			'_description' => 'A token filter which removes elisions. For example, “l’avion” (the plane) will tokenized as “avion” (plane).'
		);	
		
		$form['tokenfilter']['elision_tokenfilter']['tokenfilter_elision__articles'] = array(
			'_type' => 'textField',
			'_label' => 'Word list',
			'_description' => 'A semicomma separated list of stop word articles to use.'
		);
		
		$form['tokenfilter']['truncate_tokenfilter'] = array(
			'_type' => 'fieldset'
		);	
		
		$form['tokenfilter']['truncate_tokenfilter']['tokenfilter_truncate_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Truncate',
			'_description' => 'The truncate token filter can be used to truncate tokens into a specific length. This can come in handy with keyword (single token) based mapped fields that are used for sorting in order to reduce memory usage.'
		);	
		
		$form['tokenfilter']['truncate_tokenfilter']['tokenfilter_truncate__length'] = array(
			'_type' => 'textField',
			'_label' => 'Length',
			'_description' => 'It accepts a length parameter which control the number of characters to truncate to, defaults to 10.',
			'_value' => 10
		);	
		
		$form['tokenfilter']['unique_tokenfilter'] = array(
			'_type' => 'fieldset'
		);	
		
		$form['tokenfilter']['unique_tokenfilter']['tokenfilter_unique_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Unique',
			'_description' => 'The unique token filter can be used to only index unique tokens during analysis. By default it is applied on all the token stream.'
		);	

		$form['tokenfilter']['unique_tokenfilter']['tokenfilter_unique__if_only_same_position'] = array(
			'_label' => 'If only same position',
			'_type' => 'select',
			'_description' => 'If only_on_same_position is set to true, it will only remove duplicate tokens on the same position.',
			'_options' => array(
				'true' => 'True', 
				'false' => 'False'
			)
		);
		
		$form['tokenfilter']['pattern_replace_tokenfilter'] = array(
			'_type' => 'fieldset'
		);	
		
		$form['tokenfilter']['pattern_replace_tokenfilter']['tokenfilter_pattern_replace_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Pattern replace',
			'_description' => 'The pattern_replace token filter allows to easily handle string replacements based on a regular expression.'
		);

		$form['tokenfilter']['pattern_replace_tokenfilter']['tokenfilter_pattern_replace__pattern'] = array(
			'_type' => 'textField',
			'_label' => 'Pattern',
			'_description' => 'The regular expression is defined using the pattern parameter'
		);
		
		$form['tokenfilter']['pattern_replace_tokenfilter']['tokenfilter_pattern_replace__replacement'] = array(
			'_type' => 'textField',
			'_label' => 'Replacement',
			'_description' => 'The replacement string can be provided using the replacement parameter.'
		);
		
		$form['tokenfilter']['submit'] = array(
			'_value' => 'Submit',
			'_type' => 'submit'
		);	
		
		return $form;
	}
	
	private function form_create_field($args)
	{
		
		$args[0] = isset($args[0]) ? $args[0] : '';
		$args[1] = isset($args[1]) ? $args[1] : '';
		$args['analyzers'] = isset($args['analyzers']) ? $args['analyzers'] : array();
		
		$form['_init'] = array(
			'name' => 'create_field',
			'action' => 'mapping/create_field_post'
		);
		
		$form['document_type'] = array(
			'_value' => $args[1],
			'_type' => 'hidden'						  
		);
		
		$form['index'] = array(
			'_value' => $args[0],
			'_type' => 'hidden'						  
		);		

		$selectarray = array(
			'' => 'Choose one',
			'string' => 'String',
			'integer' => 'Integer',
			'long' => 'Long',
			'short' => 'Short',
			'byte' => 'Byte',
			'float' => 'Float',
			'double' => 'Double',
			'date' => 'Date',
			'boolean' => 'Boolean',
			'binary' => 'Binary',
			'nested' => 'Nested',
			'object' => 'Object',
			'ip' => 'ipv4',
			'geo_point' => 'Geo Point',
			'geo_shape' => 'Geo Shape',
			'attachment' => 'Attachment'
		);

		$form['general'] = array(
			'_type' => 'fieldset',
			'_label' => 'Field settings'
		);

		$form['general']['name'] = array(
			'_label' => 'Name',
			'_validation' => array(
				'required' => true
			),
			'_type' => 'textField',
			'_description' => 'This is the name of the field. No whitespace allowed.'
		);

		$form['general']['type'] = array(
			'_label' => 'Type',
			'_type' => 'select',
			'_validation' => array(
				'required' => true
			),			
			'_options' => $selectarray,
			'_description' => 'This is the core type of the field. Read more here about the types.'
		);

		$form['general']['store'] = array(
			'_type' => 'checkbox',
			'_label' => 'Store',
			'_description' => 'Set to yes to store actual field in the index, no to not store it. Defaults to no (note, the JSON document itself is stored, and it can be retrieved from it).'
		);

		$form['general']['include_in_all'] = array(
			'_type' => 'checkbox',
			'_value' => true,
			'_label' => 'Include in "all searches"',
			'_description' => 'Should the field be included in the _all field (if enabled). Defaults to true or to the parent object type setting.'
		);
		
		$form['general']['na'] = array(
			'_type' => 'fieldset'
		);

		$form['general']['na']['searchable'] = array(
			'_type' => 'checkbox',
			'_label' => 'Searchable',
			'_value' => true
		);
				
		$form['general']['na']['not_analyzed_check'] = array(
			'_type' => 'checkbox',
			'_label' => 'Analyzed',
			'_value' => true
		);
		
		$options = array(
			'default' => 'Default',
			'standard' => 'Standard',
			'simple' => 'Simple',
			'whitespace' => 'Whitespace',
			'stop' => 'Stop',
			'keyword' => 'Keyword',
			'pattern' => 'Pattern',
			'language' => 'Language',
			'snowball' => 'Snowball'
		);
		
		foreach($args['analyzers'] as $name)
		{
			$options[$name] = $name;
		}
		
		$form['general']['na']['analyzer_type'] = array(
			'_type' => 'select',
			'_label' => 'Analyzer',
			'_options' => $options
		);		

		$form['general']['term_vector'] = array(
			'_label' => 'Term vector',
			'_type' => 'select',
			'_options' => array(
				'' => '(es default)',
				'no' => 'No',
				'yes' => 'Yes',
				'with_offsets' => 'With offsets',
				'with_positions' => 'With positions',
				'with_positions_offsets' => 'With positions offsets'
			)
		);	

		$form['general']['boost'] = array(
			'_label' => 'Boost',
			'_type' => 'textField'
		);

		$form['general']['ignore_above'] = array(
			'_label' => 'Ignore above',
			'_type' => 'textField',
			'_value' => ''
		);

		$form['general']['position_offset_gap'] = array(
			'_label' => 'Position offset gap',
			'_type' => 'textField',
			'_value' => ''
		);

		$form['general']['null_value'] = array(
			'_label' => 'Null Value (default)',
			'_type' => 'textField'
		);

		$form['general']['omit_norm'] = array(
			'_label' => 'Omit norms',
			'_type' => 'select',
			'_options' => array(
				'' => '(es default)',
				'false' => 'False', 
				'true' => 'True'
			)
		);

		$form['general']['index_options'] = array(
			'_label' => 'Indexing options',
			'_type' => 'select',
			'_options' => array(
				'' => '(es default)',
				'docs' => 'Only doc numbers', 
				'freqs' => 'Doc numbers and term frequencies',
				'positions' => 'Doc numbers, term frequencies and positions'
			)
		);

		$form['general']['submit'] = array(
			'_value' => 'Create field',
			'_type' => 'submit'
		);
					
		return $form;
	}
}
?>