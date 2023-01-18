<?php

namespace CMS;

class Django extends \CMS {

    public $methods = array(
         "generator_header"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'django';
	    $this->cmsurl = 'https://www.django-cms.org/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Django CMS";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
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
	 * Check for Generator header
	 * @return [boolean]
	 */
	public function generator_header() {
		if (isset($this->header) && is_array($this->header)) {
            if (!empty($this->header['set-cookie'])) {
                
                if (is_array($this->header['set-cookie'])) {
                    foreach ($this->header['set-cookie'] as $set => $value) {
                        if (preg_match('/django_language=/i', $value)) {
                            return true;
                         }
                    }
                } elseif (preg_match('/django_language=/i', $this->header['set-cookie'])) {
                    return true;
                }
            }

            
            if ((!empty($this->header['x-divio-app'])) && (is_string($this->header['x-divio-app'])) && (preg_match('/django\-cms/i', $this->header['x-divio-app']))) {
                  return true;
            }
            
		}

		return FALSE;

	}

	


}
