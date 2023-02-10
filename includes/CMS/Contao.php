<?php

namespace CMS;

class Contao extends \CMS
{

    public $methods = array(
        "generator_meta", "api", "content_string", "generator_header"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'contao';
	    $this->cmsurl = 'https://contao.org';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Contao";
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
		'/^Contao Open Source CMS/i'
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
	 * Check for Known Link rels
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
                    if (strpos($lc['href'], '/tl_files/') !==FALSE)
                        return true;
                    }
                }
		    }

		}

		return FALSE;

	}
	
	
	public function content_string() {
	    if ($this->content) {
            if (preg_match('/"contao":"http/i', $this->content, $matches)) {
                   return true;
            }
	    }
	    return FALSE;
	}
	
	/**
	 * Check for Generator header
	 * @return [boolean]
	 */
	public function generator_header() {

		if (isset($this->header) && is_array($this->header)) {
            if ($this->is_grepmeta($this->header['vary'],'/Contao\-Page\-Layout/i')) {
                 return $this->get_info();
            }
		}

		return FALSE;

	}

	


}
