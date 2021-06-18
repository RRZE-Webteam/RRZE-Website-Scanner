<?php

/* 
 * Getting Infos from a detecting WordPress CMS
 */
namespace CMS;

class WordPress {
    
    
    public function __construct() {
         $this->classname = 'wordpress';
	 $this->url = 'https://de.wordpress.com';
	
     } 
     
     
    public function matchbymeta($string) {
	if (!isset($string)) {
	    return;
	}
	$matches = $this->get_regexp_matches();
	foreach ($matches as $m) {
	    if (preg_match($m, $string, $matches)) {
		$this->name = "WordPress";
		$this->version = $matches[1]; 
		return $this->get_info();
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
    
    
    function get_theme_main_style($linkarray, $genname, $genversion) {
	if (!is_array($linkarray)) {
	    return;
	}

	$res = array();
	foreach ($linkarray as $i => $values) {
	    if (isset($linkarray[$i]['stylesheet'])) {
		if (isset($linkarray[$i]['stylesheet']['href'])) {
		    $href = $linkarray[$i]['stylesheet']['href'];
		    if (preg_match('/themes\/([a-zA-Z0-9\-_]+)\/([a-z0-9\-\/]+)\.css(\?ver=[a-z0-9\.]+)?/i', $href, $output_array)) {
			if (isset($output_array)) {
			    $res['theme'] = $output_array[1];
			    $res['url'] = $linkarray[$i]['stylesheet']['href'];
			    if (isset($output_array[3])) {
				$res['version'] = $output_array[3];
				$res['version'] = preg_replace('/(\?ver=)/i', '', $res['version']);
			    }
			    if (isset($genversion) && ($genversion == $res['version'])) {
				// Bei einigen Themes wird die WP-Version an die Theme-URI angehängt. Das ist dann aber nicht die Theme-Version
				$res['version'] = '';
			    }
			    
			    break;
			}
		    }
		}
	    }
	}
	return $res;
    }

}