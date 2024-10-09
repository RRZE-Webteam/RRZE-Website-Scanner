<?php


		

$outputfile = "";
$prefix_outhtmlfile = "hochschulen-analyse";
$prefix_outjsonfile = "hochschulen-analyse";
$outjson = true;
$json_data = array();


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
    
$shortopts = "o:j:h:";
$longopts  = array(
    "output:",
    "json:"    // Optional value
);
$displayhelp = false;

// Script example.php
$options = getopt($shortopts,$longopts);




if (isset($options['j'])) {
   $jsonfile = $options['j'];
} elseif (isset($options['json'])) {
   $jsonfile = $options['json'];
} else {
    $jsonfile = $prefix_outjsonfile.'.json';
}
if (isset($options['h'])) {
   $displayhelp = true;
}
if ($displayhelp) {
    echo "Usage: php create-hochschulen-analyse.php -o output-file.html\n";
    
    echo "\tmit:\n";
    echo "\t-j|--json\n";
    echo "\t\tJSON Ausgabedatei\n";
    echo "\t\tDefault: $jsonfile\n";
    exit;
   
} 



echo "JSONFIle: \"$jsonfile\"\n";



$index = get_index();


$table = create_indextable($index);
if ($table) {
    $table = utf8ize($table);

    if ($outjson) {
	$json = json_encode(  array('data' => $table) );
       if($json === false || is_null($json)){
	    echo "JSON Encoding schlug fehl.\n";

	     //Get the last JSON error.
	    $jsonError = json_last_error();    
	    //If an error exists.
	    if($jsonError != JSON_ERROR_NONE){
		$error = 'Could not decode JSON! ';

		//Use a switch statement to figure out the exact error.
		switch($jsonError){
		    case JSON_ERROR_DEPTH:
			$error .= 'Maximum depth exceeded!';
		    break;
		    case JSON_ERROR_STATE_MISMATCH:
			$error .= 'Underflow or the modes mismatch!';
		    break;
		    case JSON_ERROR_CTRL_CHAR:
			$error .= 'Unexpected control character found';
		    break;
		    case JSON_ERROR_SYNTAX:
			$error .= 'Malformed JSON';
		    break;
		    case JSON_ERROR_UTF8:
			 $error .= 'Malformed UTF-8 characters found!';
		    break;
		    default:
			$error .= 'Unknown error!';
		    break;
		}
		throw new Exception($error);
	    }

	    exit;
       }
	if (file_put_contents($jsonfile, $json))
	    echo "JSON file $jsonfile created successfully...\n";
	else 
	    echo "Oops! Error creating json file $jsonfile...\n";
    }
} else {
    echo "Ausgabe-Table leer\n";
}
exit;

function sanitize_filename($name) {
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

function create_indextable($index, $wppagebreaks = true) {
    global $json_data;
    
    if (!isset($index)){
        return;
    }
    
    $cnt = 0;
    $maxcnt = 2000;
     
    if (isset($index['data'])) {
        $domainindex = $index['data'];
    } else {
        $domainindex = $index;
    }
    
    foreach ($domainindex as $num => $entry) {
	$line = '';
//	$json_grunddata = array();
	$json_grunddata = $entry;
	// Notice: Bei allen Hochschulen wird die JSON zum speichern zu gross. Daher hier nur die Analysedaten-Ergebnisse
	    if ($cnt > $maxcnt) {
            break;
	    }
	    $cnt = $cnt +1;
	     
	
	    if (isset($entry['aktivitaet'])) {
            // diese Hochschule ist inaktiv, wird uebersprungen
            echo "Skipping  ".$entry['name']." (".$entry['wiki-url'].") \t\tInaktiv\n";
            continue;
	    }
        if ( (!isset($entry['name'])) || (!isset($entry['url'])) ) {
            echo "Skipping $num entry without Name or URL (".$entry['wiki-url'].") \t\tMissing Data\n";
            continue;
        }
	   
	    $json_grunddata['name'] = $entry['name'];
	    $json_grunddata['wiki-url'] = $entry['wiki-url'];
	    
	    if (isset($entry['url'])) {
            $cc = new cURL();
            $data = $cc->get($entry['url']);
            $locationchange = $cc->is_url_location_host(true);
            $certinfo = $cc->get_ssl_info();

            echo $cc->url;
            $json_grunddata['url'] = $entry['url'];
            $json_grunddata['httpstatus'] = $data['meta']['http_code'];


            if ($locationchange &&  $data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 500) {

                $analyse = new Analyse($cc->url);
                $analyse->header = $cc->header;
                @ $analyse->init($data);

                echo " \t Ok\n";

                 $analysedata = $analyse->get_analyse_data();  
                 
                 $jsonadd =  array_merge($json_grunddata, $analysedata);
                 $json_data[] = $jsonadd;
                
                 if ((!isset($analysedata['title'])) || (empty($analysedata['title']))) {
                     echo "\t\tWARNING: Analysedata missing for Entry URL ".$cc->url." : \n";
                     print_r($analysedata);
                 }

             } elseif (!$locationchange) {
                echo "\t (".$entry['wiki-url'].") wird umgelenkt auf: ".$cc->header['location']."\n";

                $json_grunddata['redirect'] = $cc->header['location'];
                $json_data[] = $json_grunddata;

            } else {
                echo " \t Status Error (".$data['meta']['http_code'].")  bei ".$entry['name']." (".$entry['wiki-url'].")\n";
                $json_data[] = $json_grunddata;
            }
            sleep(1);
	    } else {
            echo " \t Keine URL bei ".$entry['name']." (".$entry['wiki-url']."). ";
            if (isset($entry['aktivitaet'])) {
                echo "Aktivität: ".$entry['aktivitaet'];
            } else {
                echo "Kein Eintrag bei Aktivität.";
            }
            echo "\n";
           $json_data[] = $json_grunddata;
	    }
    }
    return $json_data;
    
}



function get_index() {
    global $ignore_domains;
/*
 * Statistikdatei:
 * 
 * URI:  www.statistiken.rrze.fau.de/webauftritte/hochschulen/
 * Index-Name:
 *    hochschulen-index-$Monat.$Jahr.json
 *   mit $Monat = Nummer des Monats mit führender Null
 *   mit $Jahr = Letzten beiden Ziffern des Jahres
 */
    
    $month = date("m");
    $year = date("y");
    $indexurl = 'https://statistiken.rrze.fau.de/webauftritte/hochschulen/hochschulen-index-'.$month.'.'.$year.'.json';
    $index = new cURL();
    $data = $index->get($indexurl);

    if ($data['meta']['http_code'] == 404 ) {
	// try previous month
	$month = date("m") -1;
	if ($month == 0) {
	    $month = 12;
	    $year = date("y") -1;	    
	}
	if (($month <10) && (strlen($month) < 2)) {
	    $month = '0'.$month;
	}
	$indexurl = 'https://statistiken.rrze.fau.de/webauftritte/hochschulen/hochschulen-index-'.$month.'.'.$year.'.json';
	// echo "Missing current month index file. Trying last: ".$indexurl."\n";
	echo "Lese ".$indexurl."\n";
        $data = $index->get($indexurl);
    }
    $res = array();
    
    if ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400) {
        $res = json_decode($data['content'], true);
    }
    return $res;
}

function oldutf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = oldutf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
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