<?php

/* 
 * Analyse Website Content
 */

class Analyse {
    var $content;
    var $url;
    var $title;
    var $linkrels;
    var $meta;
    var $links;
    var $template;
    var $filterConfig = 'content-filter.ini';
    var $filters = array();
    var $generator = array();
    var $toslinks = array();
    var $logosrc;
    var $template_version;
    var $lang;
    var $favicon;
    var $external;
    var $canonical;
    
     public function __construct($url) {
         $this->content = '';
         $this->url = $url;
	 $this->canonical = $url;
	 $this->get_content_filter();
     } 
     
    function set_url($url) {
	  $url = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED|FILTER_FLAG_HOST_REQUIRED);
	  if ($url) {
	        $this->url = $url;
	  }
	  return $this->url;
    }
    function init($data) {
	
	$this->content = $data['content'];
	$doc = new DomDocument();
	@ $doc->loadHTML($data['content']);
	$nodes = $doc->getElementsByTagName('title');
	$this->title = $this->sanitize_string(trim($nodes->item(0)->nodeValue));

	$this->canonical = $this->get_canonical($data['content']);

	$link = array();
	foreach($doc->getElementsByTagName('a') as $linkTag) {
	    $thislink = array();
	    if ($linkTag->nodeValue) {
		$thislink['linktext']  = trim($linkTag->nodeValue);
	    }  
	    if($linkTag->getAttribute('href') != "") {
		$thislink['href'] = $linkTag->getAttribute('href');
	    }
	    if($linkTag->getAttribute('name') != "") {
		$thislink['name'] =  $linkTag->getAttribute('name');
	    }
	    if($linkTag->getAttribute('title') != "") {
		$thislink['title'] =  trim($linkTag->getAttribute('title'));
	    }
	    if($linkTag->getAttribute('aria-label') != "") {
		$thislink['aria-label'] =  trim($linkTag->getAttribute('aria-label'));
	    }
	    if (!empty($thislink)) {
		$link[] = $thislink;
	    }
	}
	$this->links = $link;
	
	$metas = $doc->getElementsByTagName('meta');
	$tags = array();
	foreach($doc->getElementsByTagName('meta') as $metaTag) {
	      if($metaTag->getAttribute('name') != "") {
		  
		  if (!isset($tags[$metaTag->getAttribute('name')])) {
		      $tags[$metaTag->getAttribute('name')] = $metaTag->getAttribute('content');
		  } elseif (is_array($tags[$metaTag->getAttribute('name')])) {
		      $tags[$metaTag->getAttribute('name')][] = $metaTag->getAttribute('content');
		  } else {
		      $oldsingle = $tags[$metaTag->getAttribute('name')];
		      $tags[$metaTag->getAttribute('name')] = array();
		      $tags[$metaTag->getAttribute('name')][] = $oldsingle;
		      $tags[$metaTag->getAttribute('name')][] = $metaTag->getAttribute('content');
		  }
		
	      }
	      elseif ($metaTag->getAttribute('property') != "") {
		$tags[$metaTag->getAttribute('property')] = $metaTag->getAttribute('content');
	      }
	}
	$this->meta = $tags;
	
	$cms = new CMS();
	$cms->get_generator($this->meta,$data['content']);

	
	if ($cms->name) {
	   $this->generator['name'] = $this->sanitize_string($cms->name);
	    if (strlen(trim($cms->version))>0) {
		$this->generator['version'] = $cms->version;
	    }
	}
	

	$this->linkrels = $this->get_meta_link($data['content']); 

	if (in_array( $cms->name, ["WordPress"])) {
	    $controller = 'CMS\\'.$cms->name;
	    $cmsdata = new $controller;
	    $cmsinfo = $cmsdata->get_theme_main_style($this->linkrels,$cms->name, $cms->version);
	    if (!empty($cmsinfo)) {
		$this->template = $cmsinfo['theme'];
		if ($cmsinfo['version']) {
		    $this->template_version = $cmsinfo['version'];
		}
	    }
	}
	
	$this->favicon = $this->get_favicon($data['content']);
	$this->canonical = $this->get_canonical($data['content']);
	$this->toslinks = $this->find_tos_links();
	$this->logosrc =  $this->find_logo();	
	$this->lang = $this->get_language($data['content']);
	$this->external = $this->find_external_ressources($data['content']);
	
	
    } 
    
    function make_absolute_link($uri) {
	if (empty($uri)) {
	    return;
	}
	
	$url = $uri;
	preg_match('/^\//',  $uri, $matches);
	if ($matches) {
		$baseurl = $this->url;
		$canonical = $this->get_canonical($content);
		if (!empty($canonical)) {
		    $baseurl = $canonical;
		}
		$baseurl =preg_replace('/\/$/i', '', $baseurl);
		
		$url = $baseurl.$uri;
	}
	return $url;
    }
    
    function find_external_ressources($content) {
	$res = array();
	if (!isset($this->linkrels)) {
	    $this->linkrels =  $this->get_meta_link($content);
	}
	
	$baseurl = $this->url;
	$canonical = $this->get_canonical($content);
	if (!empty($canonical)) {
	    $baseurl = $canonical;
	}
	$escapedUrl = preg_quote($baseurl, '/');
	$regex = '/^' . $escapedUrl . '/';
	
	
	foreach ($this->linkrels as $i => $link) {
	     if (isset($link['stylesheet'])) {
		   $href = $link['stylesheet']['href'];
		   preg_match('/^\//',  $href, $matches);
		   if ($matches) {
			   // relative url, ignore
		    } else {
		   
		   
			$url = filter_var($href, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED|FILTER_FLAG_HOST_REQUIRED);
			if ($url) {



			    preg_match($regex, $url, $matches);
			     if ($matches) {
				 // internal link. ignore yet
			     } else {
				  $res[] = $url;
			     }
		       }
		   }
	     }
	}
	
	$scriptsrcs = $this->get_script_link($content);
	foreach ($scriptsrcs as $link) {
	       if ($link) {
		   preg_match('/^\//',  $link, $matches);
		   if ($matches) {
			   // relative url, ignore
		    } else {
			preg_match($regex, $link, $matches);
			if ($matches) {
			    // internal link. ignore yet
			} else {
			     $res[] = $link;
			}
		    }
	       }
	    
	}
	
	return $res;
    } 
    
    function get_canonical($content) {
	$canonical = $this->url;
	if (preg_match_all('/<link rel="canonical" href="([^<>"]+)"[^<>]*>/iU', $content, $matches)) {
		if ((isset($matches)) && (isset($matches[1]))) {
		    $canonical = $matches[1][0];
		}
	}
	return $canonical;
    }
    function get_favicon($content) {
	if (!isset($this->linkrels)) {
	    $this->linkrels =  $this->get_meta_link($content);
	}
	$res = array();
	$maxw = $maxh = 0;
	$maxhref = '';
	foreach ($this->linkrels as $i => $link) {

	    if ((isset($link['icon'])) || (isset($link['shortcut icon']))|| (isset($link['apple-touch-icon']))) {
		$icontype = 'icon';
		if (isset($link['shortcut icon'])) {
		    $icontype = 'shortcut icon';
		}
		if (isset($link['apple-touch-icon'])) {
		    $icontype = 'apple-touch-icon';
		}
	    }
	    if ($icontype) {
		
		$width = $height = 0;
		$href = $sizes = '';
		 if (isset($link[$icontype]['href'])) {
		     $href = $link[$icontype]['href'];
		 }
		  if (isset($link[$icontype]['sizes'])) {
		     $sizes = $link[$icontype]['sizes'];
		     list($width, $height) = explode("x", $sizes);
		 }
		
		 if (empty($sizes)) {
		     // no sizes => use this as main favicon
		     $res['href'] = $href;
		     $res['sizes'] = '';
		     return $res;
		 } else {
		     if ($width > $maxw) {
			$maxw = $width;
			$maxh = $height;
			$maxhref = $href;
		     }

		 }
		 
	    }
	   
	}
	 if (!empty($maxhref)) {
		$res['href'] = $maxhref;
		$res['sizes'] = $maxw.'x'.$maxh;
		return $res;
	    }
    }
    function get_language($content) {
	$lang = '';
	if (preg_match_all('/<html\s*[^<>]*lang="([a-z\-]+)"[^<>]*>/Umi', $content, $matches)) {
		if ((isset($matches)) && (isset($matches[1]))) {
		    $lang = $matches[1][0];
		}
	}
	return $lang;
    }
    
    function get_meta_link($content) {
	$linkrels = array();
	if (preg_match_all('/<link ([^<>]+)\s*\/?>/mi', $content, $matches)) {
		foreach ($matches[0] as $line) {
		   $plink = $this->parse_content_link($line);
		   if ($plink) {
		       $linkrels[] = $plink; 
		   }
		}
	}
	return $linkrels;
    }


    function parse_content_link($string) {
	 if (empty($string)) return;
	 $rel = false;
	 if (preg_match('/rel=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
	     $rel = $matches[1]; 
	 }
	 if ($rel) {
	     $res = array();
	    if (preg_match('/href=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
		$res[$rel]['href'] = $matches[1]; 
	    }
	    if (preg_match('/sizes=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
		$res[$rel]['sizes'] = $matches[1]; 
	    }
	    if (preg_match('/id=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
		$res[$rel]['id'] = $matches[1]; 
	    }
	    if (preg_match('/type=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
		$res[$rel]['type'] = $matches[1]; 
	    }
	    if (preg_match('/title=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
		$res[$rel]['title'] = $matches[1]; 
	    }
	    if (preg_match('/media=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
		$res[$rel]['media'] = $matches[1]; 
	    }

	 }
	 return $res;
    }

    function get_script_link($content) {
	$scripturls = array();
	if (preg_match_all('/<script ([^<>]+)>/i', $content, $matches)) {
		foreach ($matches[0] as $line) {
		   $plink = $this->parse_content_script($line);
		   if ($plink) {
		       $scripturls[] = $plink; 
		   }
		}
	}
	return $scripturls;
    }


    function parse_content_script($string) {
	 if (empty($string)) return;
	 $src = false;
	 if (preg_match('/src=[\'"]([^"\']*)[\'"]/i', $string, $matches)) {
	     $src = $matches[1]; 
	 }
	 return $src;
    }
    
    
    function find_logo() {
	$logo = '';
	if ($this->template) {

	    if (in_array($this->template, ['FAU-Einrichtungen', 'FAU-Philfak', 'FAU-Natfak', 'FAU-Techfak', 'FAU_RWFak', 'FAU-Medfak'])) {
		$template = 'fau-theme';
	    } else {
		$template = strtolower($this->template);
	    }
	    
	    if ($this->filters[$template]) {
		$logofilter = $this->filters[$template]['logo'];
		
		if (preg_match_all($logofilter, $this->content, $matches)) {
		    if ($this->filters[$template]['matchpos']) {
			 
			if (isset($matches[$this->filters[$template]['matchpos']])) {
			    if (isset($this->filters[$template]['matchpossub'])) {
				$logo = $matches[$this->filters[$template]['matchpos']][$this->filters[$template]['matchpossub']];
			    } else {
				 $logo = $matches[$this->filters[$template]['matchpos']];
			    }
			}
			
		    } else {
			$logo = $matches[1][0];
			
		    }
		}
		return $logo;
		
	    } else {
		return;
	    }
	} else {
	    return;
	}
    }
    
    
    
    function find_tos_links() {
	$tospages = array(
	  "Impressum" => [
	      'uri' => '/impressum[\b\.\/]+/, /imprint[\b\.\/]+/',
	      'text' => '/Impressum\b/Ui, /Imprint\b/Ui, /Legal notice\b/Ui'	
	  ],
	    "Datenschutz" => [
	      'uri' => '/datenschutz[\b\.\/]+/, /privacy[\b\.\/]+/',
	      'text' => '/Datenschutz\b/Ui, /Privacy\b/Ui, /Data protection\b/Ui'	
	  ],
	     "Barrierefreiheit" => [
	      'uri' => '/barrierefreiheit[\b\.\/]+/, /accessibility[\b\.\/]+/, /a11y[\b\.\/]+/',
	      'text' => '/Barrierefreiheit\b/Ui, /Barrierefreiheitserklärung\b/Ui, /A11y\b/Ui, /Accessibility\b/Ui, /Zugänglichkeit\b/Ui'	
	  ]
	    
	);
	$toslink = array();
	foreach ($tospages as $name => $tos) {
	    $fund = $this->find_link_by_string($tos['uri'],$tos['text']);
	    if ($fund) {
		$toslink[$name] = $fund;		
	    } else {
		$fund['href'] = '';
		$fund['linktext'] = '';
		$toslink[$name] = $fund;
	    }
	}
	return $toslink;
    }
    
    function find_link_by_string($uristrings,$textstrings) {		
	$found = false;
	foreach($this->links as $link) {
	    if (isset($link['href']) && strlen(trim($link['href']))>1) {
		foreach (explode(",",$uristrings)  as $search) {
		   if (preg_match($search, $link['href'], $matches)) {
		       
		       $link['href'] = $this->make_absolute_link($link['href']);
		       $found = $link;
		       break;
		   }
		}
		if ($found) {
		    break;
		}
		foreach (explode(",",$textstrings)  as $search) {
		   $link['linktext'] = trim($link['linktext']);
		   if (preg_match($search, $link['linktext'], $matches)) {
		       $link['href'] = $this->make_absolute_link($link['href']);
		       $found = $link;
		       break;
		   }
		}
		if ($found) {
		    break;
		}
	   }
	}
	return $found;
    }
    
    
   
    
    function get_content_filter() {
	if (isset($this->filterConfig)) {
	    // Mit Gruppen analysieren
	    $this->filters = parse_ini_file($this->filterConfig, TRUE, INI_SCANNER_RAW);
	    $this->filters = array_change_key_case($this->filters, CASE_LOWER);
	}
    }
    function sanitize_string($name) {
    // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
	if ((isset($name)) && (!empty(trim($name)))) {
	    $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $name);
	    // Remove any runs of periods (thanks falstro!)
	    $file = mb_ereg_replace("([\.]{2,})", '', $file);
	    return $file;
	} else {
	    return "";
	}
    }
}

