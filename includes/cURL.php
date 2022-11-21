<?php

/* 
 * Grundlegende Funktionen zum Abruf einer URL
 */

class cURL {
    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;
    
    
     public function __construct($cookies = true, $cookie = 'cookies.txt', $compression = 'gzip', $proxy='') {

	$this->headers = array(
	    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	    'Connection: Keep-Alive',
	    'Content-type: application/x-www-form-urlencoded;charset=UTF-8'
	);
	$this->follow_html_redirection = false;
	$this->follow_html_redirection_on_samehost = true;
	$this->user_agent   = 'Mozilla/4.0 (RRZE CheckBot)';
	$this->compression  = $compression;
	$this->proxy	    = $proxy;
	$this->cookies	    = $cookies;
	$this->header	    = array();
	$this->body	    = '';
	if ($this->cookies) {
		$this->cookie($cookie);
	}
     } 

    public function cookie($cookie_file) {
	if (file_exists($cookie_file)) {
	    $this->cookie_file=$cookie_file;
	} else {
	    fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
	    $this->cookie_file=$cookie_file;
	    fclose($this->cookie_file);
	}
    }
    public function get($url) {
	$res = array(
	    "content" => '',
	    "meta" => array(
		"http_code" => 0
	    ),
	);
	
	if (!$this->is_valid_url($url)) {
	    $res['meta']['http_code'] = -1;
	    return $res;
	}
	$process = curl_init($url);
	$this->url = $url;
	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
	curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_HEADER, 1);
	
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
	
	curl_setopt($process,CURLOPT_ENCODING , $this->compression);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	
	if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
	curl_setopt($process, CURLOPT_POSTREDIR, 7);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
	
	
	
	$response = curl_exec($process);
	$header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
	

	
	$header = substr($response, 0, $header_size);
	$this->body = substr($response, $header_size);	
	$this->parse_header($header);
	
	$res['meta'] = curl_getinfo($process);
	
	
	$location = curl_getinfo($process, CURLINFO_EFFECTIVE_URL);
	if ($location !== $url) {
	    if (isset($this->header['location'])) {
		if ($location !== $this->header['location']) {
		    $this->header['location'] = $location;
		}
		
	    } else {
		$this->header['location'] = $location;
	    }
	    
	}
	$this->recheck_location_with_body();

	if  (!empty($this->header['_http_equiv-redirection'])) {
	    if ($this->follow_html_redirection)  {
		$newurl = $this->header['_http_equiv-redirection'];
		$oldheader = $this->header;
		$oldurl =  $this->url;
		$this->header = array();

		$newdata = $this->get($newurl);
		$this->header['_http_equiv_from'] = $oldurl;
		$this->header['_former_location'] = $oldheader['location'];
		$newdata['header'] = $this->header;
		return $newdata;
	    } elseif (($this->follow_html_redirection_on_samehost) && $this->is_same_domain($this->url, $this->header['_http_equiv-redirection']) ) { 
		$newurl = $this->header['_http_equiv-redirection'];
		$oldheader = $this->header;
		$oldurl =  $this->url;
		$this->header = array();

		$newdata = $this->get($newurl);
		$this->header['_http_equiv_from'] = $oldurl;
		$this->header['_former_location'] = $oldheader['location'];
		$newdata['header'] = $this->header;
		return $newdata;
	    }
	}
	$res['header'] = $this->header;
	
	$httpcode = curl_getinfo($process, CURLINFO_HTTP_CODE);
	curl_close($process);
	$res['meta']['http_code'] = $httpcode;
	if ($httpcode>=200 && $httpcode<500) {
	    $res['content'] =   $this->body;	   
	  
	} else {
	    $res['content'] = '';
	}
	
	
	// if ((empty($res['content'])) && ($httpcode == 303) && ($res['meta']['redirect_url']) && ($res['meta']['redirect_url'] !== $url)) {
	//     return $this->get($res['meta']['redirect_url']);
	// }
	return $res;
    }
     
    public function post($url,$data) {
	$res = array(
	    "content" => '',
	    "meta" => array(
		"http_code" => 0
	    ),
	);
	
	if (!$this->is_valid_url($url)) {
	    $res['meta']['http_code'] = -1;
	    return $res;
	}
	$process = curl_init($url);
	$this->url = $url;

	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
	curl_setopt($process, CURLOPT_HEADER, 1);
	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
	curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
	
	curl_setopt($process, CURLOPT_ENCODING , $this->compression);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	
	if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
	
	curl_setopt($process, CURLOPT_POSTFIELDS, $data);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($process, CURLOPT_POST, 1);
	$return = curl_exec($process);
	$res['meta'] = curl_getinfo($process);
	$httpcode = curl_getinfo($process, CURLINFO_HTTP_CODE);
	curl_close($process);
	if ($httpcode>=200 && $httpcode<500) {
	    $res['content'] = $return;	   
	} else {
	    $res['content'] = '';
	}
	
	return $res;
    }
    private function error($error) {
	echo "cURL Error: $error";
	die;
    }
    public function get_ssl_info() {
	if (!$this->is_valid_url($this->url)) {
	    return false;
	}
	
	$contextOptions = array(
	    'ssl' => array(
		'verify_peer' => false, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
		'cafile' => '/etc/ssl/certs/cacert.pem',
		'CN_match' => 'fau.de', // Change this to your certificates Common Name (or just comment this line out if not needed)
		'ciphers' => 'HIGH:!SSLv2:!SSLv3',
		'disable_compression' => true,
		"capture_peer_cert"=> true
	    )
	);

	
	$orignal_parse = parse_url($this->url, PHP_URL_HOST);
	$get = stream_context_create($contextOptions);
	@ $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
	if ($read) {
	    $cert = stream_context_get_params($read);
	    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
	    
	    $p = parse_url($this->url);
	    if ($p['scheme'] == 'http' ) {
		$this->url = preg_replace('/^http:/i', 'https:', $this->url);
	    }
	    
	    return $certinfo;
	}
	return false;
	
    }
    public function is_valid_url($urlinput) {
	$url = filter_var($urlinput, FILTER_VALIDATE_URL);

	if (empty($url) || (strlen($url) != strlen($urlinput))) {
	    return false;
	}
	return true;
    }

    public function parse_header($header) {
	 if (isset($header)) {
	    $haderlines = preg_split('/[\n\r]+/',$header);
	    if (!empty($haderlines)) {
		foreach ($haderlines as $line) {
		    $cur = trim($line);
		    if (!empty($cur)) {
			if (strpos($cur, ': ')) { 
			    list($name, $value) = explode(": ", $cur);
			    if ((!empty($name)) && (!empty($value))) {
				$name = strtolower($name);
				if ((isset($this->header[$name])) && (is_string($this->header[$name]))) {
				    if ($value !== $this->header[$name]) {
					// Nur wenn der neue Wert nicht gleich dem alten ist...
					$oldval = $this->header[$name];
					$this->header[$name] = array();
					$this->header[$name][] = $oldval;
					$this->header[$name][] = $value;
				    }
				} elseif  ((isset($this->header[$name])) && (is_array($this->header[$name]))) {   
				    $this->header[$name][] = $value;
				} else {
				    $this->header[$name] = $value;
				}
			    }
			}
		    }
		}
	    }
	    return true;
	 }
	 $this->header = array();
	 return false;
     }
     
     public function is_url_location_host($setnew = false) {
	 $location = '';
	 if (!empty($this->header['location'])) {
	     if (is_array($this->header['location'])) {
		 $location = end($this->header['location']);
	     } elseif (is_string($this->header['location'])) {
		 $location = $this->header['location'];
	     }
	 }
	 
	 
	 if (!empty($location)) {
	     $lu = parse_url($location);     
	     $lo = parse_url($this->url);
 
	     
	     if (!isset($lu['host'])) {
		 // Kein Host angegeben => relativer Link, Umleitung bleibt beim selben Host
		 return true;
	     }
	     $wwwlu = 'www.'.$lu['host'];
	     $wwwlo = 'www.'.$lo['host'];
	     if ($lu['host'] == $lo['host']) {
		 return true;

	     } else {
		 
		 if ($lu['host'] == $wwwlo )  {
		     // Ist Umlenkung auf Domainhost ohne www, es bleibt also dieselbe Domain
		    if ($setnew) {
			$this->url = $this->header['location']; 
		    }
		    return true;
		 }
		 if ($wwwlu == $lo['host'] )  {
		     // Ist Umlenkung auf Domainhost mit www, es bleibt also dieselbe Domain
		    if ($setnew) {
		       $this->url = $this->header['location']; 
		    }
		     return true;
		 }
		 
		 return false;
	     }
	 }
	 return true;
     }
     
     // checks if two urls are the same by the host 
     public function is_same_domain($url1 = '', $url2 = '') {
	if ((empty($url1)) || (empty($url2))) {
	    return false;
	} 
	
	if ($url1 == $url2) {
	    return true;
	}
	
	
	$purl1 = parse_url($url1);     
	$purl2 = parse_url($url2);
 
	if ((!isset($purl1['host'])) && (!isset($purl2['host']))) {
	    // beide relativ
	    return true;
	} else {
	    // einer ist relativ, der andere nicht
	    return false;
	}
	if ($purl1['host'] == $purl2['host']) {
	    return true;
	}
	
	// vielleicht hat einer der beiden eine Umleitung auf die Subdomain, der andere nicht
	if ((preg_match('/^www\./i',$purl1['host'])) && (!preg_match('/^www\./i',$purl2['host']))) {
	   // url1 beginnt mit www.
	    $clipwww =  preg_replace('/^www\./i', '', $purl1['host']);
	   if ($clipwww == $purl2['host']) {
	       return true;
	   }	
	}
	if ((preg_match('/^www\./i',$purl2['host'])) && (!preg_match('/^www\./i',$purl1['host']))) {
	   // url2 beginnt mit www.
	    $clipwww =  preg_replace('/^www\./i', '', $purl2['host']);
	   if ($clipwww == $purl1['host']) {
	       return true;
	   }	
	}
	return false;
     }
     
     
     // checks if there is a redirection by the HTML Meta HTTP-EQUIV Tag
     // if so, it returns the target, otherwise false
     public function is_htmlmeta_redirection($content = '') {
	 if ((empty($content)) && (!empty($this->body))) {
	     $content = $this->body;
	 }
	
	 if (!empty($content)) {
	     // first look for a <meta http-equiv="Refresh" content="0; url='TARGET'" />
	     preg_match_all('/<meta\s*[^<>]*\s*http\-equiv\s*=\s*["\']refresh["\']+\s*content=\s*["\']+([0-9]+);\s+url=["\']*([:a-z0-9\-\/\.]+)["\']*\s*[^<>]*>/i', $content, $output_array);
	     if (!empty($output_array)) {
		 if (isset($output_array[2][0])) {
		     
		     if (preg_match_all('/^[a-z]+:\/\//i', $output_array[2][0], $absmatch)) {
			 // absolute URL
			 return $output_array[2][0];
		     } else {
			 $uri = preg_replace('/^\//i', '', $output_array[2][0]);
			 $input = preg_replace('/\/$/i', '', $this->url);
			 $abs = $input.'/'.$uri;
			 return $abs;
		     }
		     
		     
		 }
	     } 
	 }
	 
	 return false;
     }
     
     // sets the redirect location if need
     private function set_redirect_location($url) {
	 if (!empty($url))  {
	     $this->header['_http_equiv-redirection'] = $url;
	     return true;
	 }
	 return false;
     }
     
     private function recheck_location_with_body() {
	 $htmlredir = $this->is_htmlmeta_redirection();
	 if ($htmlredir) {
	     return $this->set_redirect_location($htmlredir);
	 }
	 return false;
     }
     
     private function same_url($url1, $url2) {
	 
	 if ((empty($url1)) && (!empty($url2))) {
	     return false;
	 }
	 if ((!empty($url1)) && (empty($url2))) {
	     return false;
	 }
	 $url1 = preg_replace('/\/$/i', '', $url1);
	 $url2 = preg_replace('/\/$/i', '', $url2);
	 
	 if (strtolower($url1) == strtolower($url2)) {
	     return true;
	 }
	 return false;
     }
}
