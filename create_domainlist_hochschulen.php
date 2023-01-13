<?php

/* 
 * Lade Liste der Hochschulen aus Wikipedia und speichere sie in eine 
 * JSON-Datei die EIngabequelle für weitere analysen sein kann
 */

$config = [
    "wikipedia_index_url" => 'https://de.wikipedia.org/wiki/Liste_der_Hochschulen_in_Deutschland',
    "wikipedia_base_url" => 'https://de.wikipedia.org',
    "output_jsonfile"   => 'current-hochschulen.json',
    "outjson"	=> true,
];
$string_unknown_traeger = 'unbekannt';


// Automatische Laden von Klassen.
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});


$list = get_hochschullist_from_wikipedia();
$leitungstitel = array('geschftsfhrer', 'grndungsprsident', 'rektorkanzler', 
    'rektorprorektor', 'direktor', 'direktorin', 'rektorklanzlerprorektor',
    'rektor','rektorin', 'kanzlerin', 'prsident', 'prsidentin', 'president', 
    'hochschulleitung', 'leitung', 'interimsprsident', 'prsidentinai', 'geschftsfhrenderprsident');

if ($list) {
    $n = 0;
    foreach ($list as $i => $entry) {
	if ($entry['wiki-url']) {
	    $info = get_single_hochschule($entry['wiki-url']);
	    if ($info) {
		foreach ($info as $key => $resdata) {
		    if ($key == 'website') {
			if ($resdata['href']) {
			    $list[$i]['url'] = $resdata['href'];
			} else {
			     $list[$i]['url'] = $resdata['content'];
			}
		    } elseif ($key == 'trgerschaft') {
			$resdata['content'] = sanitize_traeger($resdata['content']);
			$list[$i]['typ']['traeger'] = $resdata['content'];
			$list[$i]['trgerschaft'] = $resdata['content'];
			
		    } elseif ($key == 'name') {
			$thistitle = preg_replace('/[\n\r]+/i', ' ', $resdata['title']);
			$thistitle = remove_refs($thistitle);
			$thistitle = filter_var ( $thistitle, FILTER_SANITIZE_SPECIAL_CHARS);
			$list[$i]['name'] = $thistitle;
		    } elseif (in_array($key, $leitungstitel)) {
			$list[$i]['leitung']['name'] =  filter_var ( $resdata['content'], FILTER_SANITIZE_SPECIAL_CHARS);
			$list[$i]['leitung']['title'] =   filter_var ( $resdata['title'], FILTER_SANITIZE_SPECIAL_CHARS);
		    } elseif ($key == 'logo') {
			$list[$i]['logo-url'] = $resdata['src'];				
		    } elseif ($key == 'ort') {
			$list[$i]['ort'] = filter_var ( $resdata['content'], FILTER_SANITIZE_SPECIAL_CHARS);
		    } elseif ($key == 'bundesland') {
			$list[$i]['bundesland']['name'] = removeDubletten($resdata['content']);	
		    } elseif ($key == 'motto') {
			$list[$i]['motto'] = filter_var ( $resdata['content'], FILTER_SANITIZE_SPECIAL_CHARS);;				
		    } elseif ($key == 'mitarbeiter') {
			$list[$i]['personal']['mitarbeiter'] = $resdata['content'];		
		     } elseif ($key == 'studenten') {
			$list[$i]['personal']['studenten'] = $resdata['content'];	
		     } elseif ($key == 'studierende') {
			$list[$i]['personal']['studenten'] = $resdata['content'];	
			} elseif ($key == 'davonprofessoren') {
			$list[$i]['personal']['prof'] = $resdata['content'];	
		    } elseif ($key == 'professoren') {
			$list[$i]['personal']['prof'] = $resdata['content'];		
		     } elseif ($key == 'grndung') {
			$list[$i]['gruendung'] = $resdata['content'];		
		     } elseif ($key == 'netzwerke') {
			$list[$i]['netzwerke'] = $resdata['content'];				
		     } elseif ($key == 'jahresetat') {
			$list[$i]['jahresetat'] = $resdata['content'];		
		    } elseif (($key == 'aktivitt') || ($key == 'auflsung')) {
			$list[$i]['aktivitaet'] = $resdata['content'];			
		    } elseif ($key == 'land') {	
			//brauch ich nicht
		    } else {
			$newkey = make_attribut($key);
			if (is_string($resdata)) {
			    $list[$i][$newkey] = filter_var ( $resdata, FILTER_SANITIZE_SPECIAL_CHARS);
			} else {
			    $list[$i][$newkey] = $resdata;
			}
		    }
    		}
	    }
	 
	}
	if (isset($list[$i]['trger'])) {
	    if (isset($list[$i]['typ']) && isset($list[$i]['typ']['traeger'])) {
		if (isset($list[$i]['typ']['traeger']) && ($list[$i]['typ']['traeger'] == $entry['trger'])) {
		} else {
		    
		    if ($list[$i]['trger'] == 'privat') {
			$list[$i]['typ']['traeger-text'] = $list[$i]['typ']['traeger'];
			$list[$i]['typ']['traeger'] = 'privat';
		    } elseif ($list[$i]['trger'] == 'konfessionell') {
			$list[$i]['typ']['traeger-text'] = $list[$i]['typ']['traeger'];
			$list[$i]['typ']['traeger'] = 'konfessionell';
			
		    } else {
			 $list[$i]['typ']['traeger2'] = $list[$i]['trger'];
		    }
		   
		    
		}
		 unset($list[$i]['trger']);
	    } else {
		$list[$i]['typ']['traeger'] = $list[$i]['trger'];
		unset($list[$i]['trger']);
	    }
	}
	if (isset($list[$i]['form'])) {
	    if (!empty($entry['form'])) {
		if (isset($list[$i]['typ']['form']) && ($list[$i]['typ']['form'] == $entry['form'])) {
		} else {
		    $list[$i]['typ']['form2'] = $list[$i]['form'];     
		}
		unset($list[$i]['form']);
	    } else {
		$list[$i]['typ']['form'] = $list[$i]['form'];
		unset($list[$i]['form']);
	    }
	}
	if (isset($list[$i]['promotionsrecht'])) {
	    if (!empty($entry['promotionsrecht']) ) {
		if (isset($list[$i]['typ']['promotionsrecht']) && ($list[$i]['typ']['promotionsrecht'] == $entry['promotionsrecht'])) {
		} else {
		    $list[$i]['typ']['promotionsrecht2'] = remove_refs_janein($list[$i]['promotionsrecht']);
		}
		unset($list[$i]['promotionsrecht']);
	    } else {
		$list[$i]['typ']['promotionsrecht'] = remove_refs_janein($list[$i]['promotionsrecht']);
		unset($list[$i]['promotionsrecht']);
	    }
	}
	if (isset($list[$i]['studierende'])) {
	    if (isset($list[$i]['personal']) && isset($list[$i]['personal']['studenten'])) {
		if (isset($list[$i]['studierende']) && ($list[$i]['studierende'] == $list[$i]['personal']['studenten'])) {
		} else {
		    $list[$i]['personal']['studenten2'] = $list[$i]['studierende'];     
		}
		unset($list[$i]['studierende']);
	    } else {
		$list[$i]['personal']['studenten'] = $list[$i]['studierende'];
		unset($list[$i]['studierende']);
	    }
	}
	if (isset($list[$i]['aktivitt'])) {
	    if (!isset($list[$i]['aktivitaet'])) {
		$list[$i]['aktivitaet'] = $list[$i]['aktivitt'];
	    } 
	    unset($list[$i]['aktivitt']);
	}
	if (isset($list[$i]['grndung'])) {
	    if (!isset($list[$i]['gruendung'])) {
		$list[$i]['gruendung'] = $list[$i]['grndung'];
	    } 
	    unset($list[$i]['grndung']);
	}
	if (isset($list[$i]['stand'])) {
	    $list[$i]['stand'] = remove_refs($list[$i]['stand']);
	}
	$n++;
	

	echo $i;
	if (!empty($list[$i]['name'])) {
	    echo ". ".$list[$i]['name'];
	} else {
	    echo ". -KEIN NAME!-";
	}
	if (isset($list[$i]['url'])) {  
	      echo "\t".$list[$i]['url'];
	} else {
	    echo "\t-KEINE URL-";

	}
	 if (isset($list[$i]['aktivitaet'])) {
		echo "\t** UNI AUFGELOEST **";
	 }
	  echo "\n";
	
	sleep(1);
    }
}

if ($config['outjson']) {
    $json = json_encode(array('data' => $list));
    $jsonfile = $config['output_jsonfile'];
    if (file_put_contents($jsonfile, $json)) {
        echo "JSON file $jsonfile created successfully.\n";
    } else {
	echo "Oops! Error creating json file $jsonfile...\n";
    }
}

exit;


function get_hochschullist_from_wikipedia() {
    global $config;
    
        $cc = new cURL();
	$data = $cc->get($config['wikipedia_index_url']);
	
	if ((isset($data) && $data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400)) {
	    $doc = new DomDocument();
	    @ $doc->loadHTML($data['content']);
	    
	    
	    $tables = $doc->getElementsByTagName('table');
	    $k=0;
	    $headtitle = array();
	    
	    foreach ($tables as $table) {
		      $rows = $table->getElementsByTagName('tr');   
		      $r=0;
		      foreach ($rows as $row) {
			 
			  $headcells = $row->getElementsByTagName('th');
			  if ($headcells) {
			      $c=0;
			      foreach ($headcells as $h) {
				  $headtitle[$c] = correctNodebreaks($h->nodeValue);
				  $headattrib[$c] = make_attribut($headtitle[$c]);
				  $c++;
			      }
			  }
			  
			  $contentcells = $row->getElementsByTagName('td');
			   if ($contentcells) {
			      $c=0;
			      foreach ($contentcells as $cell) {
				  $attrib = $headattrib[$c];
				  if ($c==0) {
				      $link = $cell->getElementsByTagName('a');
				      if ($link) {
					  $href = $link->item(0)->getAttribute('href');
					  if (preg_match('/^\//', $href)) {
					      $href = $config['wikipedia_base_url'].$href;
					  }
					  $tablecontent[$r]['wiki-url'] = $href;
				      }   
				      $tablecontent[$r][$attrib] = correctNodebreaks($cell->nodeValue);

				  } elseif ($c==1) {
				       $link = $cell->getElementsByTagName('a');
				        if ($link) {
					  $href = $link->item(0)->getAttribute('href');
					  if (preg_match('/^\//', $href)) {
					      $href = $config['wikipedia_base_url'].$href;
					  }
					  $tablecontent[$r]['bundesland']['href'] = $href;
					  $landttitle = $link->item(0)->getAttribute('title');
					  if ($landttitle) {
					      $landttitle = removeDubletten($landttitle);
					      if (strlen($landttitle) < 4) {
						   $tablecontent[$r]['bundesland']['kurzform'] = $landttitle;
					      } else {
						   $tablecontent[$r]['bundesland']['title'] = $landttitle;
					      }
					     
					  }
					  
				      }
				       
				  } else {
				  
				    if ((!empty($cell->nodeValue)) && (!empty(correctNodebreaks($cell->nodeValue)))) {
				       $tablecontent[$r][$attrib] = correctNodebreaks($cell->nodeValue);
				    }
				  }
				  $c++;
			      }
			   }
			   $r++;
			}
			
		 

	    }
	    
	    return $tablecontent;
	
	    
	    
	} else {
	    echo "Error on reading ".$config['wikipedia_index_url']."\n";
	    return;
	}
}


function get_single_hochschule($wikiurl) {
    if (empty($wikiurl)) {
	return;
    }
    $hochschule = new cURL();
    $data = $hochschule->get($wikiurl);
    if ((isset($data) && $data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400)) {
	$doc = new DomDocument();
	    @ $doc->loadHTML($data['content']);
	    
	    $xpath = new DOMXPath($doc);
	   $table =$xpath->query("//*[@id='Vorlage_Infobox_Hochschule']")->item(0);
	   if ($table) {
		$rows = $table->getElementsByTagName("tr");

		 foreach ($rows as $row) {
		     $th = $row->getElementsByTagName('th');
		     $cells = $row->getElementsByTagName('td');
		     $htitle = $cellcontent = '';
		     
		     if (isset($th) && ($th->item(0))) {
			$htitle = $th->item(0)->nodeValue;
		     }
		     if (isset($cells) && ( $cells->item(0))) {
			$cellcontent = $cells->item(0)->nodeValue;
		     }
		     
		 
		     
		     if (($th->item(0)) && ($th->item(0)->getAttribute('colspan') == "2") && (empty($cellcontent))) {
			 // Überschrift
			 $res['name']['title'] = correctNodebreaks($htitle);


		     } elseif ($cells->item(0)->getAttribute('colspan') == "2") {
			 // Logo
			 if ($cells->item(0)->getElementsByTagName('img')) {
			     $img = $cells->item(0)->getElementsByTagName('img');
			     $res['logo']['src'] = $img->item(0)->getAttribute('src');
			 }
		     } elseif (trim($htitle) == 'Website') {
			   $res['website']['title'] = correctNodebreaks($htitle);
			   $res['website']['content'] = remove_refs(correctNodebreaks($cellcontent));
			    $link = $cells->item(0)->getElementsByTagName('a');
			    if ($link) {
				$res['website']['href'] = $link->item(0)->getAttribute('href');
			    }
			  
		
		     } elseif (!empty($htitle)) {
			 $attrib = make_attribut($htitle);
			 $res[$attrib]['title'] = correctNodebreaks($htitle);
			 $res[$attrib]['content'] = remove_refs(correctNodebreaks($cellcontent));

		     }

		 }
		return $res;
	   }
    }
    return;
    
}
function remove_refs_janein($string) {
     if (!empty($string)) {
	$string = preg_replace('/[0-9]+/i', '', $string);
     }
     return $string;
}

function remove_refs($string) {
     if (!empty($string)) {
	$string = preg_replace('/\[[0-9]+\]/i', '', $string);
     }
     return $string;
}
function make_attribut($string) {
    $res = trim($string);
    $string = strtolower($res);
    $string = preg_replace('/[^a-z0-9_\-]+/i', '', $string);

  return $string;
}
function correctNodebreaks($string) {
    if (isset($string)) {
	$res = trim($string);
	$string = preg_replace('/\-\s+/', '', $res);
	return $string;
    }
    return;
}

function removeDubletten($string) {
    if (isset($string)) {
	$res = trim($string);
	$string = preg_replace('/(\b[\-\w\x{0080}-\x{FFFF}]+\b)\W+\1/iu', '$1', $res);
	return $string;
    }
    return; 
}

function sanitize_traeger($traeger = '') {
    global $string_unknown_traeger;
    
    $valid = array("privat", "staatlich", "konfessionell");
    
    
    if (!empty($traeger)) {	
	if ($traeger == 'öffentlich-rechtlich') {
	    $traeger = "staatlich";
	}
	if ($traeger == 'kirchlich') {
	    $traeger = "konfessionell";
	}
	$traeger = preg_replace('/[^a-z]+/i', '', $traeger);
	$traeger = strtolower($traeger);
    }
    
    
    if (in_array($traeger, $valid)) {
	return $traeger;
    }
   
    $found = $string_unknown_traeger;
    foreach ($valid as $search) {
	$sgrep = '/'.$search.'/i';
	if (preg_match ($sgrep, $traeger, $m)) {
	    $found = $search;
	    break;
	}
    }
    return $found;
}