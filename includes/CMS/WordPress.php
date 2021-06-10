<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace CMS;

class WordPress {
    function get_theme_main_style($linkarray, $genname, $genversion) {
	if (!is_array($linkarray)) {
	    return;
	}

	$res = array();
	foreach ($linkarray as $i => $values) {
	    if (isset($linkarray[$i]['stylesheet'])) {
		if (isset($linkarray[$i]['stylesheet']['href'])) {
		    $href = $linkarray[$i]['stylesheet']['href'];
		    if (preg_match('/themes\/([a-zA-Z0-9\-_]+)\/([a-z0-9\-\/]+)\.css(\?ver=[a-z0-9\.]+)?/i', $href, $output_array)) {
			if (isset($output_array)) {
			    $res['theme'] = $output_array[1];
			    $res['url'] = $linkarray[$i]['stylesheet']['href'];
			    if (isset($output_array[3])) {
				$res['version'] = $output_array[3];
				$res['version'] = preg_replace('/(\?ver=)/i', '', $res['version']);
			    }
			    if (isset($genversion) && ($genversion == $res['version'])) {
				// Bei einigen Themes wird die WP-Version an die Theme-URI angehängt. Das ist dann aber nicht die Theme-Version
				$res['version'] = '';
			    }
			    
			    break;
			}
		    }
		}
	    }
	}
	return $res;
    }
}