<?php
namespace CMS;

class Drupal extends \CMS {

	public $methods = array(
		"readme_d6",
		"changelog",
		"changelog_d8",
		"generator_meta",
		"node_css"
	);

	
	public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'drupal';
	    $this->cmsurl = 'https://www.drupal.org';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Drupal";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     
    public function generator_meta($string = '') {
	if ((empty($string)) && isset($this->tags['generator'])) {
	    $string = $this->tags['generator'];
	}
	
	if (empty($string)) {
	    return false;
	}
	
	if (is_array($string)) {
	    foreach ($string as $line) {
		 $ret = $this->generator_meta($line);
		 if ($ret !== false) {
		     return $ret;
		 }
	    }
	} else {
	    $matches = $this->get_regexp_matches();
	    foreach ($matches as $m) {
		if (preg_match($m, $string, $matches)) {

		    $this->version = $matches[1]; 
		    return $this->get_info();
		}
	    }
	}
	return false;
	
    }
	 private function get_regexp_matches() {
	    $match_reg = [
		'/^Drupal ([0-9\.]+) /i'
	    ];
	    return $match_reg;
	}   
	public function get_info() {
	    $info = array();
	    $info['icon']	    = $this->icon;
	    $info['classname']  = $this->classname;	   
	    $info['url']	    = $this->url;
	    $info['name']	    = $this->name; 
	    $info['version']    = $this->version; 
	    return $info;
	}
        
	public function readme_d6() {
        /*
         * See if README.TXT exists, and check for Drupal 6.xx
         * @return [boolean]
         */
                if($data = $this->fetch($this->url."/sites/all/README.txt")) {
                    $lines = explode(PHP_EOL, $data);
                    for($i=0;$i<count($lines);$i++) {
                        if(strpos($lines[$i], "Drupal") !== FALSE) {
                            return TRUE;
                        }
                    }
                return FALSE;
                }
        }
	/**
	 * See if CHANGELOG.TXT exists, and check for Drupal
	 * @return [boolean]
	 */
	public function changelog() {

		if($data = $this->fetch($this->url."/CHANGELOG.txt")) {

			/**
			 * Changelog always starts from the second line
			 */
			$lines = explode(PHP_EOL, $data);
            if(array_key_exists(1,$lines))
            {
                return strpos($lines[1], "Drupal") !== FALSE;
            }


		}

		return FALSE;

	}

	/**
	 * See if core/CHANGELOG.TXT exists, and check for Drupal
	 * @return [boolean]
	 */
	public function changelog_d8() {

		if($data = $this->fetch($this->url."/core/CHANGELOG.txt")) {

			/**
			 * Changelog always starts from the second line
			 */
			$lines = explode(PHP_EOL, $data);

			return strpos($lines[0], "Drupal") !== FALSE;
		}

		return FALSE;

	}



	/**
	 * Check modules/node/node.css content
	 * @return [boolean]
	 */
	public function node_css() {

		if($data = $this->fetch($this->url."/modules/node/node.css")) {

			/**
			 * Second line always has .node-* css
			 */

			$lines = preg_split("/\\r\\n|\\r|\\n/",$data);

            if(array_key_exists(1,$lines))
            {
			    return strpos($lines[1], ".node-") !== FALSE;
            }
		}

		return FALSE;

	}

	
}
