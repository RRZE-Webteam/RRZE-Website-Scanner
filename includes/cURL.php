<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class cURL {
    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;
    
    function cURL($cookies=TRUE,$cookie='cookies.txt',$compression='gzip',$proxy='') {
	$this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
	$this->headers[] = 'Connection: Keep-Alive';
	$this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
	$this->user_agent = 'Mozilla/4.0 (RRZE CheckBot)';
	$this->compression=$compression;
	$this->proxy=$proxy;
	$this->cookies=$cookies;
	if ($this->cookies == TRUE) $this->cookie($cookie);
    }
    
    function cookie($cookie_file) {
	if (file_exists($cookie_file)) {
	    $this->cookie_file=$cookie_file;
	} else {
	    fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
	    $this->cookie_file=$cookie_file;
	    fclose($this->cookie_file);
	}
    }
    function get($url) {
	$process = curl_init($url);
	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
	curl_setopt($process, CURLOPT_HEADER, 0);
	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
	
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
	
	curl_setopt($process,CURLOPT_ENCODING , $this->compression);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	
	if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
	
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
	
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
    function post($url,$data) {
	$process = curl_init($url);
	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
	curl_setopt($process, CURLOPT_HEADER, 1);
	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
	
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
	if ($httpcode>=200 && $httpcode<300) {
	    $res['content'] = $return;	   
	} else {
	    $res['content'] = '';
	}
	
	return $res;
    }
    function error($error) {
	echo "cURL Error: $error";
	die;
    }
}
