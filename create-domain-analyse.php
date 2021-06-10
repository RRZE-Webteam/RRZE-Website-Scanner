<?php

$servertypen = [
                1       => "RRZE-Webdienst-Server",
                2       => "RRZE Server fuer Spezialdienste",
                3       => "RRZE Webserver (nicht Webteam)",
                4       => "RRZE Virtual Serverhousing",
                5       => "Externer Server in FAU",
                6       => "Externer Server",
                14      => "Housing Server",
                15      => "ZUV-Webserver",
                18      => "RRZE CMS Server"
      ];


$ignore_domains = [
    '/cms\.rrze\.uni\-erlangen\.de$/',
    '/[a-z0-9\-]+\.cms\.rrze\.de/',
    '/[0-9]+\.kurse.rrze\.fau\.de$/',
    '/[a-z0-9\-]+\.kurse\.rrze\uni\-erlangen\.de/',
    '/\.webspace.rrze\.fau\.de$/',
    '/webserver\-default\.uni\-erlangen\.de/',
    '/infoload\.rrze\.uni\-erlangen\.de/',
    '/real\-name\-harbour\.rrze\.uni\-erlangen\.de/',
    '/cmslb\.rrze\.uni\-erlangen\.de/',
    '/dev[0-9\-]+\.fau\.tv/',
    '/dev[a-z0-9\-\.]*\.rrze\.uni\-erlangen\.de/',
    '/info[0-9\-]+\.rrze\.uni\-erlangen\.de/',
    '/zuv[0-9\-]+\.fau\.info/',
    '/[a-z0-9\-]+\.test\.rrze\.uni\-erlangen\.de/',
    '/[a-z0-9\-]+\.webhummel\.rrze\uni\-erlangen\.de/',
    '/berta\.wmp\.rrze\uni\-erlangen\.de/',
];

		




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
    


$outputfile = "domain-analyse.html";

if ($argc !== 2) {
    echo "Usage: php create-domain-analyse.php Servertyp [output-file.html]\n";
    
    echo "Servertypen: ";
    foreach ($servertypen as $num => $val) {
	echo "\t".$num."\t".$val."\n";
    }
    exit;
} else {
    
    if (isset($argv) && isset($argv[1])) {
	$servertyp = intval($argv[1]);
    }
    if (isset($argv) && isset($argv[2])) {
	$outputfile = sanitize_filename($argv[2]);
    }
    if (empty($outputfile)) {
	$outputfile = "domain-analyse.html";
    }

    echo "Output to: ".$outputfile."\n";
}

$index = get_index();

$table = create_indextable($index,4,$servertyp);
// Schreibt den Inhalt in die Datei zurück
file_put_contents($outputfile, $table);

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

function create_indextable($index, $refstatus = 4, $refserver = 1) {
    if (!isset($index)){
	return;
    }
    
    $line = '';
    $table = '';
    $cnt = 0;
    $maxcnt = 1500;
     
    foreach ($index as $num => $entry) {
	$line = '';
	
	if (($refstatus==-1) || (($refstatus > -1) && ($entry['wmp_refstatus'] == $refstatus))) {
	    // Status ok
	    
	    
	    if (($refserver==-1) || ($entry['wmp_refservertyp'] == $refserver)) {
	    // Servertyp ok
		
		if ($cnt > $maxcnt) {
		    break;
		}
		$cnt = $cnt +1;
		
	        $cc = new cURL();
		$data = $cc->get($entry['url']);
		echo $entry['url']."\n";
		
		if ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400) {
		    $line .= '<tr>';
		    $analyse = new Analyse($entry['url']);
		    //$analyse->set_url($url);
		    $analyse->init($data);

		    if (isset($analyse->favicon)) {;
			$line .= '<td class="favicon"><img src="'.$analyse->favicon['href'].' style="width: 32px; height: 32px;" alt=""></td>';
		    } else {
			$line .= '<td class="favicon"></td>';
		    }

		    $line .= '<td class="title">';
		    if (isset($analyse->lang)){
			$line .= '<span lang="'.$analyse->lang.'">';
		    }
		    
		    $line .= $analyse->title;
		    if (isset($analyse->lang)){
			$line .= '</span>';
		    }
		    $line .=  '<br><span class="url"><a href="'.$analyse->canonical.'">'.$analyse->canonical.'</a></span></td>';

		    if (isset($analyse->logosrc)) {
			$line .= '<td class="logo"><img src="'.$analyse->logosrc.' style="max-width: 240px;" alt=""></td>';
		    } else {
			$line .= '<td class="logo"></td>';
		    }
		    
		    if ($analyse->toslinks) {

			 if (($analyse->toslinks['Impressum']) && (!empty($analyse->toslinks['Impressum']['href']))) {
			     $line .= '<td>';
			     $line .= '<a href="'.$analyse->toslinks['Impressum']['href'].'"><span class="success">Ok</span></a>';
			     $line .= '</td>';
			 } else {
			     $line .= '<td>';
			     $line .= '<span class="fail">-</span>';
			     $line .= '</td>';
			 }
			if (($analyse->toslinks['Datenschutz']) && (!empty($analyse->toslinks['Datenschutz']['href']))) {
			     $line .= '<td>';
			     $line .= '<a href="'.$analyse->toslinks['Datenschutz']['href'].'"><span class="success">Ok</span></a>';
			     $line .= '</td>';
			 } else {
			      $line .= '<td>';
			     $line .= '<span class="fail">-</span>';
			     $line .= '</td>';
			 }
			if (($analyse->toslinks['Barrierefreiheit']) && (!empty($analyse->toslinks['Barrierefreiheit']['href']))) {
			     $line .= '<td>';
			     $line .= '<a href="'.$analyse->toslinks['Barrierefreiheit']['href'].'"><span class="success">Ok</span></a>';
			     $line .= '</td>';
			 } else {
			      $line .= '<td>';
			     $line .= '<span class="fail">-</span>';
			     $line .= '</td>';
			 }
			
		    } else {
			$line .= '<td><span class="fail">-</span></td><td><span class="fail">-</span></td><td><span class="fail">-</span></td>';
		    }
		    
		    
		    if (isset($analyse->generator)){
		       $line .= '<td class="generator">';
		       $line .= $analyse->generator['name'];

			if (isset($analyse->generator['version'])) {
			     $line .= " (".$analyse->generator['version'].")";
			}
			
			
			if (isset($analyse->template)) {
			    $line .= '<br><span class="template">Template: '.$analyse->template;
			    if (isset($analyse->template_version)) {
				$line .=  " (".$analyse->template_version.")";
			    }
			     $line .= '</span>';
			}
			
			
		       $line .= '</td>';

		    } else {
			$line .= '<td class="generator"></td>';
		    }
	
		    
		     $line .= '</tr>'."\n";
		}
		sleep(1);
	    }
	}  
	if (!empty($line)) {
	    $table .= $line."\n";
	}
    }
    if (!empty($table)) {
	$head = '<table class="tablesorter">';
	$head .= '<thead>';
	$head .= '<tr>';
	$head .= '<th scope="col" rowspan="2">Favicon</th>';
	$head .= '<th scope="col" rowspan="2">Titel / URL</th>';
	$head .= '<th scope="col" rowspan="2">Logo</th>';
	$head .= '<th scope="col" colspan="3">Rechtstexte</th>';
	$head .= '<th scope="col" rowspan="2">CMS</th>';
	$head .= '</tr>';
	$head .= '<tr>';
	$head .= '<th>Impressum</th>';
	$head .= '<th>Datenschutz</th>';
	$head .= '<th>Barrierefreiheit</th>';
	$head .= '</tr>';	
	$head .= '</thead>';
	$output = $head;
	 
	$output .= '<tbody>';
	$output .= $table;
	$output .= '</tbody>';
	$output .= '</table>';
	return $output;
	
    }
    
    return $table;
    
}



function get_index() {
    global $ignore_domains;
/*
 * Statistikdatei:
 * 
 * URI:  www.statistiken.rrze.fau.de/webauftritte/domains/
 * Index-Name:
 *    domains-index-$Monat.$Jahr.csv
 *   mit $Monat = Nummer des Monats mit führender Null
 *   mit $Jahr = Letzten beiden Ziffern des Jahres
 * CSV Spalten der domain-index-Datei:
 * 
 *  1. Fortlaufende Nummer
 *  2. URL
 *  3. Fachbereich (aus URL)
 *  4. DocRoot (leer)
 *  5. WMP Id  
 *  6. WMP RefStatus
 *  7. WMP RefServertyp 
 */
    
    $month = date("m");
    $year = date("y");
    $indexurl = 'https://statistiken.rrze.fau.de/webauftritte/domains/domains-index-'.$month.'.'.$year.'.csv';
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
	$indexurl = 'https://statistiken.rrze.fau.de/webauftritte/domains/domains-index-'.$month.'.'.$year.'.csv';
	// echo "Missing current month index file. Trying last: ".$indexurl."\n";
	$data = $index->get($indexurl);
    }
    $res = array();
    
    if ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400) {

	$lines = explode("\n",$data['content']);
	foreach ($lines as $line) {
	    if ((!empty($line)) && (!empty(trim($line)))) {
		list($num, $url, $fachbereich, $docroot, $wmpid, $wmprefstatus, $wmprefservertyp) = explode("\t",$line);
		$addthis = true;
		if ($ignore_domains) {
		    
		    foreach ($ignore_domains as $ignore) {
			if (preg_match($ignore, $url)) {
		//	    echo "IGNORE ".$url."\n";
			    $addthis = false;
			}
		    }
		}
		if ($addthis) {
		    $res[$num]['url'] = $url;
		    $res[$num]['fachbereich'] = $fachbereich;
		    $res[$num]['wmp_id'] = intval($wmpid);
		    $res[$num]['wmp_refstatus'] = intval($wmprefstatus);
		    $res[$num]['wmp_refservertyp'] = intval($wmprefservertyp);
		}
	    }
	}
    }
    return $res;
}

function get_servertyp_by_id($id) {
   
    global $servertypen;
   if (($id) && (isset($servertypen[$id]))) { 
       return $servertypen[$id];
    }
    return;

}

function get_status_by_id($id) {
    $refstatus = [
	0 => "Unbekannt",
	1 => "Beantragt",
	2 => "Reserviert",
	3 => "Einrichtungsphase",
	4 => "Aktiv",
	5 => "Deaktiviert",
	6   => "Gesperrt",
	7   => "Wartet auf Autorisierung",
	8   => "Autorisierung erfolgt",
	9   => "Weggezogen",
	10  => "In Betrieb mit Warnung",
	12  => "Domainname reserviert",
	11  => "Deaktiviert durch Bot"
    ];
     if (($id) && (isset($refstatus[$id]))) { 
       return $refstatus[$id];
    }
    return;
}