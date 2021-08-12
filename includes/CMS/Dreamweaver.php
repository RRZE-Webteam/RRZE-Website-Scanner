<?php

/* 
 * Getting Infos from a detecting CleanSlate (WVU)
 */
namespace CMS;

class Dreamweaver extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'dreamweaver';
	 $this->cmsurl = 'https://www.adobe.com/de/products/dreamweaver.html';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Dreamweaver";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	    "content_string"
	);

     
   
    public function get_info() {
	$info = array();
        $info['icon']	    = $this->icon;
	$info['classname']  = $this->classname;	   
	$info['url']	    = $this->url;
	$info['name']	    = $this->name; 
	$info['version']    = $this->version; 
	return $info;
    }
    
    
	
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/<!\-\- InstanceBegin template="\/Templates\/template\.dwt"/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/<!\-\- InstanceBeginEditable name="[a-z0-9\-]+" \-\->/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}
}