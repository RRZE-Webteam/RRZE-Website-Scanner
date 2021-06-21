<?php

namespace CMS;

class DokuWiki extends \CMS
{

    public $methods = array(
        "generator_header",
        "css"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'dokuwiki';
	    $this->cmsurl = 'https://www.dokuwiki.org/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "DokuWiki";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     
	public function generator_header() {
	    $string = $this->tags['generator'];

	    if (empty($string)) {
		return false;
	    }

	    $matches = $this->get_regexp_matches();
	    foreach ($matches as $m) {
		if (preg_match($m, $string, $matches)) {

		    $this->version = $matches[1]; 
		    return $this->get_info();
		}
	    }
	    return false;

	}
	 private function get_regexp_matches() {
	    $match_reg = [
		'/^DokuWiki\s*([0-9\.\-a-z\s&;]*)/iu'
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
        
  

   
	
	 /* Check for CSS
	 * @return [boolean]
	 */
	public function css() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
				if (strpos($lc['href'], '/lib/exe/css.php') !==FALSE)
				    return true;
			    }
			   
			    
			}
		    }

		}

		return FALSE;

	}

}
