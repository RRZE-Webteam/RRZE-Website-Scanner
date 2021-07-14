<?php

namespace CMS;

class HisInOne extends \CMS
{

    public $methods = array(
        "css",
	"readstatus",
	"content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'his';
	    $this->cmsurl = 'https://www.his.de/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "HisInOne";
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
        
	public function readstatus() {
		   if($data = $this->fetch($this->url."/qisserver/manifest.json")) {

			 return $this->get_info();

		}

		return FALSE;
        }
	/* Check for CSS
	 * @return [boolean]
	 */
	public function css() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
				if (strpos($lc['href'], '/qisserver/javax\.faces\.resource/') !==FALSE)
				    return true;
			    }
			   
			    
			}
		    }

		}

		return FALSE;

	}
	
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/HISinOne ist ein Produkt der HIS eG/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}

}
