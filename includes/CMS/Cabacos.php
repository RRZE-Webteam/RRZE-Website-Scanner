<?php

/* 
 * Getting Infos from a detecting Cabocos CMS
 */
namespace CMS;

class Cabacos extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'cabacos';
	 $this->cmsurl = 'https://www.cabacos-cms.de/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Cabacos";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	 "generator_meta", "api"
	);

     
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
		    if (isset($matches[1])) {
			$this->version = $matches[1]; 
		    }
		    return $this->get_info();
		}
	    }
	}
	return false;
	
    }
     
    private function get_regexp_matches() {
	$match_reg = [
	    '/^Cabacos CMS \(Version ([0-9\.]+)\)/i',
	    '/^Cabacos CMS/i',
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
    
    
	/**
	 * Check for Core API
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
				if ((preg_match('/Cabacos_formats\.css/i', $lc['href'], $matches)))
				    return true;
				
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/Cabacos CMS Templateinformation/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}

}