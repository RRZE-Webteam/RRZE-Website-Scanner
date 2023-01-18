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
        $this->httpstatus = 200;
     } 
     
     
    function set_url($url) {
        $url = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED|FILTER_FLAG_HOST_REQUIRED);
        if ($url) {
              $this->url = $url;
        }
        return $this->url;
    }
    
    function init($data) {
        if (empty($data['content'])) {
            return false;
        }
        $this->content = $data['content'];
        $doc = new DomDocument();
        @ $doc->loadHTML($data['content']);
        $nodes = $doc->getElementsByTagName('title');
        $this->title = $this->sanitize_string(trim($nodes->item(0)->nodeValue));
        $this->canonical = $this->get_canonical($data['content']);
        $this->httpstatus = $data['meta']['http_code'];

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

            //	echo "FOUND LINK: ".$thislink['href']." - ".$thislink['linktext']."\n";
            }
        }
        $this->links = $link;

        $metas = $doc->getElementsByTagName('meta');
        $tags = array();
        foreach($doc->getElementsByTagName('meta') as $metaTag) {
              if($metaTag->getAttribute('name') != "") {
              $attrbname = strtolower($metaTag->getAttribute('name'));

              if (!isset($tags[$attrbname])) {
                  $tags[$attrbname] = $metaTag->getAttribute('content');
              } elseif (is_array($tags[$attrbname])) {
                  $tags[$attrbname][] = $metaTag->getAttribute('content');
              } else {
                  $oldsingle = $tags[$attrbname];
                  $tags[$attrbname] = array();
                  $tags[$attrbname][] = $oldsingle;
                  $tags[$attrbname][] = $metaTag->getAttribute('content');
              }

              } else {
              if ($metaTag->getAttribute('property') != "") {
                  $attrbname = strtolower($metaTag->getAttribute('property'));
                  $tags['_property'][$attrbname] = $metaTag->getAttribute('content');
              }
              if ($metaTag->getAttribute('http-equiv') != "") {
                  $attrbname = strtolower($metaTag->getAttribute('http-equiv'));
                  $tags['_http-equiv'][$attrbname] = $metaTag->getAttribute('content');
              }
              if ($metaTag->getAttribute('charset') != "") {
                  $tags['_charset'] = $metaTag->getAttribute('charset');
              }
              }
        }
        $this->meta = $tags;
        $this->linkrels = $this->get_meta_link($data['content']); 


        $cms = new CMS($this->url);
        $cms->add_links($this->links);
        $cms->add_linkrel($this->linkrels);
        $cms->add_scripts($this->get_script_link($data['content']));
        $cms->add_header($this->header);
        $cms->get_generator($this->meta,$data['content']);

        if ($cms->name) {
           $this->generator['name'] = $this->sanitize_string($cms->name);
           if (!empty($cms->version)) {
               $ver = $cms->version;
               if (is_array($ver)) {
               $cms->version = implode(',', $ver);  
               }
            if (strlen(trim($cms->version))>0) {
            $this->generator['version'] = $cms->version;
            }
        }
            $this->generator['classname'] = $cms->classname;
            $this->generator['icon'] = $cms->icon;
            $this->generator['url'] = $cms->cmsurl;
        }


        $template = $cms->get_cms_template($this->meta,$data['content']);
        if ($template !== false) {
             $this->template = $template['name'];
             if (isset($template['version'])) {
            $this->template_version = $template['version'];
             }
        }


        $this->favicon = $this->get_favicon($data['content']);
        $this->canonical = $this->get_canonical($data['content']);
        $this->toslinks = $this->find_tos_links();
        $this->logosrc =  $this->find_logo();	
        $this->lang = $this->get_language($data['content']);
        $this->external = $this->find_external_ressources($data['content']);
        $this->scripts = $this->get_script_link($data['content']);
	
    } 
    
    
    public function get_analyse_data() {
	$res = array();
	$res['url'] = $this->url;
	$res['httpstatus'] = $this->httpstatus;
	$res['canonical'] = $this->canonical;
	$res['title'] = $this->title;
	if (isset($this->logosrc)) {
	    $res['logo_src'] = $this->logosrc;
	}
	if (isset($this->favicon['href'])) {
	    $res['favicon_src'] =$this->favicon['href'];
	}
	$res['meta'] =  $this->meta;
//	$res['content']['links'] = $this->links;
	$res['content']['lang'] =  $this->lang;
	$res['content']['tos'] =  $this->toslinks;
	$res['content']['external'] = $this->external;
	
	if (isset($this->generator['name'])) {
	    $res['generator']['name'] =  $this->generator['name']; 
	}
	if (isset($this->generator['version'])) {
	    $res['generator']['version'] =  $this->generator['version'];
	}
	if (isset($this->generator['icon'])) {
	    $res['generator']['icon'] =  $this->generator['icon'];
	}
	if (isset($this->generator['url'])) {
	    $res['generator']['url'] =  $this->generator['url'];
	}
	if (isset($this->generator['classname'])) {
	    $res['generator']['classname'] =  $this->generator['classname']; 
	}
	if (isset($this->template)) {
	    $res['template']['name'] =  $this->template; 
	}
	if (isset($this->template_version)) {
	    $res['template']['version'] =  $this->template_version;  
	}
	

	return $res;
    }
    
    function make_absolute_link($uri) {
        if (empty($uri)) {
            return;
        }

        $url = $uri;

        $p = parse_url($uri);
        if (empty($p['host'])) {
            $baseurl = $this->url;

            $baseurl =preg_replace('/\/$/i', '', $baseurl);
            $p['path'] = preg_replace('/^\//i', '', $p['path']);
            $url = $baseurl.'/'.$p['path'];
            if (!empty($p['query'])) {
            $url .= '?'.$p['query'];
            }
        }
        return $url;

    }
    
    function find_external_ressources($content = '') {
        $res = array();
        if (!isset($this->linkrels)) {
            $this->linkrels =  $this->get_meta_link($content);
        }

        foreach ($this->linkrels as $i => $link) {
             if (isset($link['stylesheet'])) {
               $p = parse_url($link['stylesheet']['href']);

               if ($this->is_same_host($link['stylesheet']['href'])) {
                   // same or relative
                   // do nothing yet...
               } else {
                    $res[] = $link['stylesheet']['href'];
               }
             }
        }

        $scriptsrcs = $this->get_script_link($content);
        foreach ($scriptsrcs as $link) {
               if ($link) {
                if ($this->is_same_host($link)) {
                   // same or relative
                   // do nothing yet...
                } else {
                    $res[] = $link;
                }

               }

        }

        return $res;
    } 
    
    private function is_same_host($someurl) {
	$p = parse_url($someurl);
	$host = '';
	if (!empty($p['host'])) {
	    $host = $p['host'];
	}

	if (empty($host)) {
	    // sounds wrong to answer with true. But an empty host in urls will
	    // result als relative link and therfor it will use the same host
	    return true;
	}
	    
	$baseurl = $this->url;   
	$basehost = parse_url($baseurl)['host'];
	if (empty($basehost)) {
	   
	    return false;
	}
	
	$domainlist = explode(".",$basehost);
	$entries = count($domainlist);
	$basehost = $domainlist[$entries-2].".".$domainlist[$entries-1];
	$basehostalternative = '';
	if ($domainlist[$entries-2] == 'fau') {
	    $basehostalternative = 'uni-erlangen'.".".$domainlist[$entries-1];
	} elseif ($domainlist[$entries-2] == 'uni-erlangen') {
	    $basehostalternative = 'fau'.".".$domainlist[$entries-1];	    
	}
	
	$domainlist = explode(".",$host);
	$entries = count($domainlist);
	$matchhost = $domainlist[$entries-2].".".$domainlist[$entries-1];
	
	// ok, this could have been a simple regexp, but i want to
	// make it so, that anyone can understand the code later :)
	
	if (($matchhost == $basehost) || ($matchhost == $basehostalternative)) {
	    return true;
	}
	return false;
	
	
    }
    
    function get_canonical($content) {
	$canonical = '';
	if (preg_match_all('/<link rel="canonical" href="([^<>"]+)"[^<>]*>/iU', $content, $matches)) {
		if ((isset($matches)) && (isset($matches[1]))) {
		    $canonical = $matches[1][0];
		}
	}
	
	if (empty($canonical)) {
	    $canonical = $this->url;
	}
	
	$canonical = preg_replace('/\/$/i', '', $canonical);
	
	return $canonical;
    }
    function get_favicon($content) {
	if (!isset($this->linkrels)) {
	    $this->linkrels =  $this->get_meta_link($content);
	}
	$res = array();
	$maxw = $maxh = 0;
	$maxhref = '';
	$icontype = '';
	
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
		     $href = $this->make_absolute_link($link[$icontype]['href']);
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
	if (preg_match_all('/<html\s*[^<>]*lang="([a-z\-]+)"[^<>]*>/Ui', $content, $matches)) {
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
	 $res = array();
	 if ($rel) {
	     
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
	    $template = '';
	    if (in_array($this->template, ['FAU-Einrichtungen', 'FAU-Philfak', 'FAU-Natfak', 'FAU-Techfak', 'FAU_RWFak', 'FAU-Medfak'])) {
		$template = 'fau-theme';
	    } else {
		$template = strtolower($this->template);
	    }
	    
	    if (!empty($template) && isset($this->filters[$template])) {
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
		// ausnahmebehandlung fau-theme
		// Wenn das Logo das Default vom Theme ist, dann entferne ist
		if ($template == 'fau-theme') {
		    if (preg_match('/wp\-content\/themes\/FAU\-[a-z]+\/img\//i', $logo)) {
			// Default Logo aus Theme-Verzeichnis verwendet.
			// Dies ist nur erlaubt für die 5 Fakultätsdomains und die Startdomain
			

			if (in_array($this->canonical, ["https://www.fau.de", "https://www.fau.eu", 
			    "https://www.phil.fau.de", "https://www.phil.fau.eu",
			    "https://www.nat.fau.de", "https://www.nat.fau.eu",
			    "https://www.med.fau.de", "https://www.med.fau.eu",
			    "https://www.tf.fau.de", "https://www.tf.fau.eu",
			    "https://www.rw.fau.de", "https://www.rw.fau.eu"])) {
			    // Bin auf einer der Hauptdomains, die einen Default nutzen dürfen
			    
			 } elseif (in_array($this->url, ["https://www.fau.de", "https://www.fau.eu", 
			    "https://www.phil.fau.de", "https://www.phil.fau.eu",
			    "https://www.nat.fau.de", "https://www.nat.fau.eu",
			    "https://www.med.fau.de", "https://www.med.fau.eu",
			    "https://www.tf.fau.de", "https://www.tf.fau.eu",
			    "https://www.rw.fau.de", "https://www.rw.fau.eu"])) {
			    // Bin auf einer der Hauptdomains, die einen Default nutzen dürfen
			     
			} else {
			    $logo = '';
			}
		    }
		}
		$logo = $this->make_absolute_link($logo);
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
	      'uri' => '/impressum[\b\.\/]+/i, /imprint[\b\.\/]+/i',
	      'text' => '/Impressum/Ui, /Imprint/i, /Legal notice/Ui, /Rechtliches/i'	
	  ],
	    "Datenschutz" => [
	      'uri' => '/datenschutz[\b\.\/]+/i, /datenschutzhinweise/i, /privacy[\b\.\/]+/i',
	      'text' => '/Datenschutzerklärung\b/ui, /Datenschutzhinweis/ui, /Datenschutz\b/Ui, /Datenschutzinformation\b/Ui, /Privacy\b/Ui, /Data protection\b/Ui, /Privatsp/Ui, /Rechtliches/i'	
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
    
    function find_link_by_string($uristrings,$textstrings,$absolutelinks = true) {		
	$found = false;
	foreach($this->links as $link) {
	    if (isset($link['href']) && strlen(trim($link['href']))>1) {
		foreach (explode(",",$uristrings)  as $search) {
		   if (preg_match($search, $link['href'], $matches)) {
		       if ($absolutelinks) {
			    $link['href'] = $this->make_absolute_link($link['href']);
		       }
		       $found = $link;
		       break;
		   }
		}
		if ($found) {
		    break;
		}
		if (isset($link['linktext'])) {
		    $link['linktext'] = trim($link['linktext']);
		    foreach (explode(",",$textstrings)  as $search) {
		       if (preg_match($search, $link['linktext'], $matches)) {
			    if ($absolutelinks) {
				$link['href'] = $this->make_absolute_link($link['href']);
			    }
			   $found = $link;
			   break;
		       }
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
	    $file = mb_ereg_replace("([^\w\s\d\-_~,;!\[\]\(\).\/])", '', $name);
	    // Remove any runs of periods (thanks falstro!)
	    $file = mb_ereg_replace("([\.]{2,})", '', $file);
	    return $file;
	} else {
	    return "";
	}
    }
}

