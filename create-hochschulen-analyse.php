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


if ($outjson) {
    $json = json_encode(array('data' => $json_data));
    if (file_put_contents($jsonfile, $json))
        echo "JSON file $jsonfile created successfully...\n";
    else 
	echo "Oops! Error creating json file $jsonfile...\n";
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
    
    $line = '';
    $table = '';
    $cnt = 0;
    $maxcnt = 20;
    $breakat = 100;
    $breakcnt = 0;
     
    if (isset($index['data'])) {
	$domainindex = $index['data'];
    } else {
	$domainindex = $index;
    }
    
    foreach ($domainindex as $num => $entry) {
	$line = '';
	$json_grunddata = $entry;
	var_dump($entry);
	 
		
		// $json_grunddata['url'] = $entry['url'];


		
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
	$indexurl = 'https://statistiken.rrze.fau.de/webauftritte/domains/hochschulen-index-'.$month.'.'.$year.'.json';
	// echo "Missing current month index file. Trying last: ".$indexurl."\n";
	$data = $index->get($indexurl);
    }
    $res = array();
    
    if ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400) {
	$res = json_decode($data['content'], true);

    }
    return $res;
}

