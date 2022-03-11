<?php

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
    
$shortopts = "o:i:h:c:j";
$longopts  = array(
    "csv:",
    "out:",
    "json:",
    "input:"    // Optional value
);
$displayhelp = false;
$json_data = array();

$options = getopt($shortopts,$longopts);

$csvfile = '';
if (isset($options['c'])) {
   $csvfile = $options['c'];
} elseif (isset($options['csv'])) {
   $csvfile = $options['csv'];
}


if (!empty($csvfile)) {
    // Lese CSV File und speichere diese als JSON Index
    $csvindex = makeJSONIndexFilebyCSV($csvfile);
    
  // exit;
}
$jsonfile = '';
if (isset($options['j'])) {
   $jsonfile = $options['j'];
} elseif (isset($options['json'])) {
   $jsonfile = $options['json'];
}

$outputfile = '';
if (isset($options['o'])) {
   $outputfile = $options['o'];
} elseif (isset($options['out'])) {
   $outputfile = $options['out'];
} else {
    echo "No HTML Output file\n";
    exit;
}


echo "Output to file: $outputfile\n";


if (empty($outputfile)) {
    $displayhelp = true;
}

$inputfile = '';
if (isset($options['i'])) {
   $inputfile = $options['i'];
} elseif (isset($options['input'])) {
   $inputfile = $options['input'];
}

if (!empty($inputfile)) {
    $index = get_index($inputfile);
} else {
   if (!empty($csvfile)) {
        $index = $csvindex;
   } else {
       echo "No input\n";
   }
    
}

if (isset($options['h'])) {
   $displayhelp = true;
}
if ($displayhelp) {
    echo "Usage: php ".__FILE__." \n";
    echo "\t-c|--csv\n";
    echo "\t\tCSV Datei\n";
    echo "\t\tLiest eine CSV ein, die pro Zeile eine URL und danach einen Namen der Site beinhalten kann. Diese Datei wird danach in eine JSON Indexdatei gespeichert.\n";
    echo "\t-j|--json\n";
    echo "\t\tJSON Ausgabedatei\n";
    echo "\t-i|--out\n";
    echo "\t\tHTML Ausgabedatei\n";
    echo "\t-i|--input\n";
    echo "\t\tJSON Input Index\n";
    exit;
   
} 


$table = create_indextable($index);
// Schreibt den Inhalt in die Datei zurück
file_put_contents($outputfile, $table);

if ($jsonfile) {
    $json = json_encode(array('data' => $json_data));
    if (file_put_contents($jsonfile, $json))
        echo "JSON file $jsonfile created successfully...\n";
    else 
	echo "Oops! Error creating json file $jsonfile...\n";
}

exit;



function create_indextable($index, $wppagebreaks = true) {
    global $json_data;
    
    if (!isset($index)){
	return;
    }
    
    $line = '';
    $table = '';
    $cnt = 0;
    $maxcnt = 20;
    $breakat = 100;
    $breakcnt = 0;
    $failstr = '<span class="fail">-</span>'; 
    $okstr = '<span class="success">Ok</span>'; 
    
    foreach ($index as $num => $entry) {
	$line = '';
	$json_grunddata = array();


		$json_grunddata['url'] = $entry['url'];
		$json_grunddata['redirect'] = '';
		
		if ($cnt > $maxcnt) {
		    break;
		}
		$cnt = $cnt +1;
		
	        $cc = new cURL();
		$data = $cc->get($entry['url']);
		$locationchange = $cc->is_url_location_host(true);
		$certinfo = $cc->get_ssl_info();
		
		echo $cc->url;
		
		$json_grunddata['httpstatus'] = $data['meta']['http_code'];
		
		
		if ($locationchange &&  $data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 500) {
		   
		    $analyse = new Analyse($cc->url);
		    $analyse->header = $cc->header;
		    @ $analyse->init($data);
    
		    echo " \t Ok\n";
		    $line .= '<tr>';
		   
		    $line .= '<td class="title">';
		    if (isset($analyse->lang)){
			$line .= '<span lang="'.$analyse->lang.'">';
		    }
		    
		    $line .= $analyse->title;
		    if (isset($analyse->lang)){
			$line .= '</span>';
		    }
		    $line .=  '<span class="url"><a href="'.$analyse->url.'">'.$analyse->url.'</a></span></td>';

		    if (isset($analyse->logosrc) && !empty($analyse->logosrc)) {
			$line .= '<td class="logo"><img class="borderless noshadow" src="'.$analyse->logosrc.'" style="max-width: 240px; max-height: 65px;" alt=""></td>';
		    } else {
			$line .= '<td class="logo"></td>';
		    }
		    if (isset($analyse->favicon) && !empty($analyse->favicon['href'])) {
			$line .= '<td class="favicon center"><img class="borderless noshadow" src="'.$analyse->favicon['href'].'" style="width: 32px; height: 32px;" alt=""></td>';
		    } else {
			$line .= '<td class="favicon center"></td>';
		    }

		    if ($analyse->toslinks) {

			 if (($analyse->toslinks['Impressum']) && (!empty($analyse->toslinks['Impressum']['href']))) {
			     $line .= '<td class="center">';
			     $line .= '<a title="Impressum von '.$analyse->url.'" href="'.$analyse->toslinks['Impressum']['href'].'">'.$okstr.'</a>';
			     $line .= '</td>';
			 } else {
			     $line .= '<td class="center">';
			     $line .= $failstr;
			     $line .= '</td>';
			 }
			if (($analyse->toslinks['Datenschutz']) && (!empty($analyse->toslinks['Datenschutz']['href']))) {
			     $line .= '<td class="center">';
			     $line .= '<a title="Datenschutzerklärung von '.$analyse->url.'"  href="'.$analyse->toslinks['Datenschutz']['href'].'">'.$okstr.'</a>';
			     $line .= '</td>';
			 } else {
			      $line .= '<td class="center">';
			     $line .= $failstr;
			     $line .= '</td>';
			 }
			if (($analyse->toslinks['Barrierefreiheit']) && (!empty($analyse->toslinks['Barrierefreiheit']['href']))) {
			     $line .= '<td class="center">';
			     $line .= '<a title="Barrierefreiheitserklärung von '.$analyse->url.'"  href="'.$analyse->toslinks['Barrierefreiheit']['href'].'">'.$okstr.'</a>';
			     $line .= '</td>';
			 } else {
			      $line .= '<td class="center">';
			     $line .= $failstr;
			     $line .= '</td>';
			 }
			
		    } else {
			$line .= '<td class="center">'.$failstr.'</td><td class="center">'.$failstr.'</td><td class="center">'.$failstr.'</td>';
		    }
		    
		    
		    if ((isset($analyse->generator)) && (!empty($analyse->generator['name']))) {
		       $line .= '<td class="generator">';
		       $line .= '<span class="'.$analyse->generator['classname'].'">'.$analyse->generator['name'].'</span>';

			if (isset($analyse->generator['version'])) {
			     $line .= " (".$analyse->generator['version'].")";
			}
			
			
			
		       $line .= '</td>';

		    } else {
			$line .= '<td class="generator"></td>';
		    }
		    
		
		    
		   if ($analyse->external) {
		     $line .= '<td class="external">';
			 foreach ($analyse->external as $link) {
			    $line .=  "\t".$link."<br>\n";
			 }
			$line .= '</td>';
		    
		    
		    }else {
			$line .= '<td class="external"></td>';
		    }
		     $line .= '</tr>'."\n";
		     $analysedata = $analyse->get_analyse_data();  
		     
		     $jsonadd =  array_merge($json_grunddata, $analysedata);
		     $json_data[] = $jsonadd;
	     
	        } elseif (!$locationchange) {
		    echo "\t wird umgelenkt auf: ".$cc->header['location']."\n";
		    $json_grunddata['redirect'] = $cc->header['location'];
		    $json_data[] = $json_grunddata;
		    
		} else {
		    echo " \t Status Error (".$data['meta']['http_code'].")\n";
		    $json_data[] = $json_grunddata;
		}
		sleep(1);
	   


	if (!empty($line)) {
	    $table .= $line."\n";
	    $tablecell[] = $line;
	    
	}
    }
    if (!empty($table)) {
	$head = '<table class="sorttable">';
	$head .= '<thead>';
	$head .= '<tr class="center">';
	
	$head .= '<th scope="col" rowspan="2">Titel / URL</th>';
	$head .= '<th scope="col" rowspan="2">Logo</th>';
	$head .= '<th scope="col" rowspan="2">Favicon</th>';
	$head .= '<th scope="col" colspan="3">Rechtstexte</th>';
	$head .= '<th scope="col" rowspan="2">CMS</th>';
	$head .= '<th scope="col" rowspan="2">Externe Ressourcen</th>';
	$head .= '</tr>';
	$head .= '<tr class="center">';
	$head .= '<td class="small vertical">Impressum</td>';
	$head .= '<td class="small vertical">Datenschutz</td>';
	$head .= '<td class="small vertical">Barrierefreiheit</td>';
	$head .= '</tr>';	
	$head .= '</thead>'."\n";
	$output = $head;
	
	if ($wppagebreaks) {
	   $output .= '<tbody>';
	    foreach ($tablecell as $cell) {
		
		$breakcnt = $breakcnt + 1;
		if ($breakcnt == $breakat) {
		    $breakcnt = 0;
		    $output .= '</tbody>';
		    $output .= '</table>';
		    
		   $output .= '<!--nextpage-->'."\n";
		   
		   $output .= $head;
		    $output .= '<tbody>';
		}
		$output .= $cell;

	    }
	    $output .= '</tbody>';
	} else {
	    $output .= '<tbody>';
	    $output .= $table;
	    $output .= '</tbody>';
	}
	
	$output .= '</table>';
	return $output;
	
    }
    
    return $table;
    
}



function get_index($inputfile) {
    $data = file_get_contents($inputfile);
    $res = json_decode($data, true);

    return $res;
}



 function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                unset($d[$k]);
		$d[utf8ize($k)] = utf8ize($v);
            }
        } else if (is_object($d)) {
	    $objVars = get_object_vars($d);
	    foreach($objVars as $key => $value) {
	    $d->$key = utf8ize($value);
        }       
    } else if (is_string ($d)) {
	 return mb_convert_encoding($d, "UTF-8", "UTF-8");
    }
    return $d;
}

function sanitize_url($url) {
    if (!empty($url)) {
	$url = filter_var ( $url, FILTER_SANITIZE_URL);
	if (preg_match('/^http/i', $url, $matches)) {  
	    // starts with protokoll, ok
	} elseif  (preg_match('/^\/\//i', $url, $matches)) {    
	    // starts with double backslash
	     $url = 'http:'.$url;
	} else {
	    $url = 'http://'.$url;
	}
	$url = trim($url,"/");
	return $url;
    }
}

function makeJSONIndexFilebyCSV($filename) {
       $data = file_get_contents($filename);
       $lines = explode("\n",$data);
       $c = 0;
       
       foreach ($lines as $line) {
	   $zeile = ltrim($line);
	   $zeile = trim($zeile);
	   $url = $name = '';
	  
	   if (!empty($zeile)) {
	       $parts = preg_split("/[\s,\t;]+/", $zeile);
	       $c++;
	       if (isset($parts[0])) {
		   $res['url'] = sanitize_url($parts[0]);
	       }
	       if (isset($parts[1])) {
		   $res['name'] = $parts[1];
	       }
	       $result[] = $res;
	       
	   }
	   
       }
       
       $jfile = preg_replace('/\.csv/', '.json', $filename);
       $json = json_encode($result);
       if (file_put_contents($jfile, $json))
	    echo "JSON file $jfile created successfully...\n";
	else 
	    echo "Oops! Error creating json file $jfile...\n";
  
       
       return $result;
}