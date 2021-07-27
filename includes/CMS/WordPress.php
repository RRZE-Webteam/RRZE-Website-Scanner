<?php

/* 
 * Getting Infos from a detecting WordPress CMS
 */
namespace CMS;

class WordPress extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'wordpress';
	 $this->cmsurl = 'https://de.wordpress.com';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "WordPress";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	 "generator_meta", "button_css", "api", "scripts"
	);

     
    public function generator_meta($string = '') {
	if (empty($string)) {
	    $string = $this->tags['generator'] ?? '';
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
	    '/^WordPress\s*([0-9\.]+)$/i'
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
    
    /*
     * Get Template (WordPress Theme)
     */
    
    function get_template() {
	$linkarray = $this->linkrels;
	$genversion = $this->version;
	$found = false;
	$res = array();
	foreach ($linkarray as $i => $values) {
	    if (isset($linkarray[$i]['stylesheet'])) {
		if (isset($linkarray[$i]['stylesheet']['href'])) {
		    
		    $href = $linkarray[$i]['stylesheet']['href'];
		    if (preg_match('/themes\/([a-zA-Z0-9\-_]+)\/([a-z0-9\-\/]+)\.css(\?ver=[a-z0-9\.]+)?/i', $href, $output_array)) {
			if (isset($output_array)) {
			    $res['name'] = $output_array[1];
			    $res['url'] = $linkarray[$i]['stylesheet']['href'];
			    if (isset($output_array[3])) {
				$res['version'] = $output_array[3];
				$res['version'] = preg_replace('/(\?ver=)/i', '', $res['version']);
			    }
			    if (isset($genversion) && ($genversion == $res['version'])) {
				// Bei einigen Themes wird die WP-Version an die Theme-URI angehängt. Das ist dann aber nicht die Theme-Version
				$res['version'] = '';
			    }
			    $found = true;
			    break;
			}
		    }
		}
	    }
	}
	if ($found) {
	    return $res;
	} else {
	    return false;
	}
    }
	/**
	 * Check /wp-includes/css/buttons.css content
	 * @return [boolean]
	 */
	public function button_css() {
		if($data = $this->fetch($this->url."/wp-includes/css/buttons.css")) {
			/**
			 * 9th line always has Wordpress-style Buttons
			 */
			$lines = explode(PHP_EOL, $data);
			   
		    if(array_key_exists(8,$lines))   {
			
			return strpos($lines[8], "WordPress-style Buttons") !== FALSE;
		    }
		}

		return FALSE;

	}
	/**
	 * Check for WordPress Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {
			    if (strpos($element, 'wp-includes') !==FALSE)
				    return true;
		    }

		}

		return FALSE;

	}

	/**
	 * Check for WordPress Core API
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'alternate') {
				if (strpos($lc['href'], 'wp-json') !==FALSE)
				    return true;
			    }
			     if ($type == 'dns-prefetch') {
				if (strpos($lc['href'], 's.w.org') !==FALSE)
				    return true;
			    }
			    
			}
		    }

		}

		return FALSE;

	}
}
