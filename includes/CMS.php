<?php

/* 
 * Detect CMS or try it
 * In parts adapted by https://github.com/Krisseck/Detect-CMS
 */

class CMS {
    var $name;
    var $version;
    var $info;
    var $content;
    var $classname;
    var $icon;
    var $url; 
    var $cmsurl;
    
    public $systems = [
        "WordPress",
	"Webbaukasten"

    ];
    private $common_methods = ["matchbymeta"];
    
    
    public function __construct($url) {
         $this->name = '';
         $this->version = '';
	 $this->url = $url;
     } 
     
     

    public function get_generator($tags,$content) {

        /*
         * Common, easy way first: check for Generator metatags or Generator headers
         */

        foreach ($this->systems as $system_name) {
            $system_class = 'CMS\\' . $system_name;
            $system = new $system_class($this->url, $tags, $content);

            foreach ($this->common_methods as $method) {
                if (method_exists($system, $method)) {
                    if ($system->$method()) {
			$this->name = $system->name;
			$this->version = $system->version;
			$this->classname = $system->classname;
			$this->icon = $system->icon;
			$this->cmsurl= $system->cmsurl;
                        return $this->name;
                    }
                }

            }

        }

        /*
         * Didn't find it yet, let's just use regular tricks
         */

        foreach ($this->systems as $system_name) {

            $system_class = 'CMS\\' . $system_name;
            $system = new $system_class($this->url, $tags, $content);

            foreach ($system->methods as $method) {
                if (!in_array($method, $this->common_methods)) {
                    if ($system->$method()) {
			$this->name = $system->name;
			$this->version = $system->version;
			$this->classname = $system->classname;
			$this->icon = $system->icon;
			$this->cmsurl= $system->cmsurl;
                        return $this->name;
                    }

                }

            }

        }
	
	// Didnt find anything till yet. If meta tag filled with a string, return this.
	
	if (isset($tags)) {
	    $genatorstring = trim($tags['generator']);
	    $this->name = $genatorstring;
	    
	    if ((isset($genatorstring)) && (!is_array($genatorstring))) {
		preg_match('/^([\wa-zA-Z\s\-;&]+)\(?([\s\d\.\/]*)\)?$/iu', $genatorstring, $output_array);
		if (isset($output_array)) {
		    $this->name = trim($output_array[1]);
		    if (isset($output_array[2])) {
			$this->version = trim($output_array[2]);
		    }
		    return $this->name;
		}

	    } elseif (is_array($genatorstring)) {
		
		foreach ($genatorstring as $i => $value) {
		    preg_match('/^([\wa-z0-9A-Z\s\-;&]+)\(?([\s\d\.\/]*)\)?$/iu', $value, $output_array);
		    if (isset($output_array)) {
			$this->name = $output_array[1];
			if (isset($output_array[2])) {
			    $this->version = $output_array[2];
			}
			break;
		    }
		}
	    }
	    return $this->name;
	}


        return false;

    }

     
    protected function fetch($url = null)  {

        $ch = curl_init();

        if ($url == null) {
            $url = $this->url;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        $return = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 404) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $return;

    }

    protected function fetchBodyAndHeaders()  {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 404) {
            curl_close($ch);
            return false;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $header_array = [];

        foreach (explode("\r\n", $header) as $line) {
            if ($line == '') {
                continue;
            }

            $array = explode(': ', $line);
            if (array_key_exists(1, $array)) {
                list ($key, $value) = $array;
                $header_array[$key] = $value;
                continue;
            }

            $header_array['http_code'] = $line;
        }

        curl_close($ch);

        return [$header_array, $body];

    }
   
   
}