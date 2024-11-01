<?php
class pw_yahoo_boss_results {
	var $pw_search;
	
	var $response_code;
	
	var $query;
	
	var $results_per_page = 10;
	
	var $page = 0;
	
	var $result_counter = 0;
	
	var $totalhits = 0;
	
	var $deephits = 0;
	
	var $start = 0;
	
	var $count = 0;
	
	var $url;
	
	var $app_id;
	
	var $format;
	
	var $params;
	
	var $nextpage;
	
	var $prevpage;
	
	var $results;
	
	/**
	* PHP 4 Compatible Constructor
	*/
	function pw_yahoo_boss_results($boss){
		$this->__construct($boss);
	}
	
	/**
	* PHP 5 Constructor
	*/
	function __construct($boss) {
		$this->pw_search = $boss;
		
		// Main part of BOSS query URL - note the "web/" for web pages only
		$this->url = 'http://boss.yahooapis.com/ysearch/web/v1/';
		
		// Yahoo! App ID
		$this->app_id = $this->pw_search->get_yahoo_app_id();
			
		// Set the current result page. Specific page may be requested from a Next or Prev link
		if (isset($_GET['page'])) {
			$this->page = intval($_GET['page']);
		}
		if ($this->page < 1) {
			$this->page = 1;
		}
		
		// the format of the results
		$this->format = $this->pw_search->get_format();
		$this->results_per_page = $this->pw_search->options['pw_search_results_per_page'];
		
		// save the query
		$this->query = urlencode($_GET['q']);
		
		$this->result_counter = ($this->page - 1) * $this->results_per_page + 1;
		
		// set the query parameters
		$this->setParams();
		
		$this->executeQuery();
		
		if ($this->format == 'xml') {
			$this->parseXml();
		} else if ($this->format == 'json') {
			$this->parseJson();
		}
	}
	
	function parseJson() {
		$data = json_decode($this->raw_data);
		$data = $data->ysearchresponse;
		
		$this->response_code = $data->responsecode;
		$this->totalhits = intval($data->totalhits);
		$this->deephits = intval($data->deephits);
		$this->start = $data->start;
		$this->count = $data->count;
		
		if (isset($data->nextpage)) {
			$this->nextpage = $data->nextpage;
		}
		if (isset($data->prevpage)) {
			$this->prevpage = $data->prevpage;
		}
		
		if (empty($data->resultset_web)) {
			return;
		}
		
		foreach ($data->resultset_web as $result) {
			$index = $this->result_counter - 1;
			$this->results[$index]['abstract'] = $result->abstract;
			$this->results[$index]['clickurl'] = $result->clickurl;
			$this->results[$index]['date']     = $result->date;
			$this->results[$index]['dispurl']  = $result->dispurl;
			$this->results[$index]['size']     = $result->size;
			$this->results[$index]['size_f']   = $this->format_size($result->size);
			$this->results[$index]['title']    = $result->title;
			$this->results[$index]['url']      = $result->url;

			// Increment result counter
			$this->result_counter++;
		}
	}
	
	function parseXml() {
		// Create an XML parser
		$parser = xml_parser_create();
		
		// Setup our handler functions so the parser calls them when it finds tags and data
		xml_set_element_handler($parser, array(&$this, 'start_tag'), array(&$this, 'end_tag'));
		xml_set_character_data_handler($parser, array(&$this, 'tag_data'));
		
		// Parse the search results XML. The results will be printed by the functions above
		// Remember, $xml is the variable where the XML data was stored from the CURL response
		xml_parse($parser, $this->raw_data);
	}
	
	function executeQuery() {
		$url = $this->url . $this->query . '?appid=' . $this->app_id . $this->params;
		$http = new WP_Http();
		$response = $http->request( $url );
		
		$this->raw_data = $response['body'];
		// could also use $response['headers'], $response['response'], $response['cookies']
	}
	
	// Partial query paramaters.
	function setParams() {
		$p = array(
			'format' => $this->format,
			'sites'  => $this->pw_search->get_domains(),	// comma-delimited list of domains
			'count'  => $this->results_per_page,
			'start'  => (($this->page - 1) * $this->results_per_page),
		);
		
		$this->params = '';
		foreach ($p as $parameter => $value) {
			$this->params .= '&'.$parameter.'='.$value;
		}
	}
	
	// The parser found a start tag for an element
	function start_tag($parser, $name, $attribs) {
		// Save the current tag name so we know where we are
		$this->curtag = $name;
		// Get the attributes from the <resultset_web> tag
		if ($this->curtag == 'RESULTSET_WEB') {
			$this->count     = intval($attribs['COUNT']);
			$this->start     = intval($attribs['START']);	// why doesn't this work?
			$this->totalhits = intval($attribs['TOTALHITS']);
			$this->deephits  = intval($attribs['DEEPHITS']);
		} else if ($this->curtag == 'YSEARCHRESPONSE') {
			$this->response_code = $attribs['RESPONSECODE'];
		}
	}
	
	// The parser found some character data inside an element
	function tag_data($parser, $data) {
		if (isset($this->curtag)) {
			if (!isset($this->tag[$this->curtag])) {
				$this->tag[$this->curtag] = null;
			}
			$this->tag[$this->curtag] .= $data;
		}
	}
	
	// The parser found an end tag for an element
	function end_tag($parser, $name) { 
		// If this is the end of a <result> element
		if ($name == 'RESULT') {
			$result = array(
				"abstract" => $this->tag['ABSTRACT'],
				"clickurl" => $this->tag['CLICKURL'],
				"date"     => $this->tag['DATE'],
				"dispurl"  => $this->tag['DISPURL'],
				"size"     => $this->tag['SIZE'],
				"size_f"   => $this->format_size($this->tag['SIZE']),
				"title"    => $this->tag['TITLE'],
				"url"      => $this->tag['URL'],
			);
			unset($this->tag);

			$this->results[$this->result_counter - 1] = $result;

			// Increment result counter
			$this->result_counter++;
		} else if ($name == 'NEXTPAGE') {
			$this->nextpage = $this->tag['NEXTPAGE'];
		} else if ($name == 'PREVPAGE') {
			$this->prevpage = $this->tag['PREVPAGE'];
		}
		// Reset the current tag name
		unset($this->curtag); 
	}
	
	function format_size($size) {
		$size_label = array("Byte", "K", "M", "G", "T");

		for ($i = 0; $size > 1024; $i++) {
			$size /= 1024;
		}
		
		$size = round($size);
		
		return("$size".$size_label[$i]);
	}

}
?>