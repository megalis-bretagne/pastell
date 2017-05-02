<?php

function get_hecho($message,$quote_style=ENT_QUOTES){
	return htmlentities($message,$quote_style,"utf-8");
	//return htmlentities($message,$quote_style,"iso-8859-1");
}

function hecho($message,$quot_style=ENT_QUOTES){
	echo get_hecho($message,$quot_style); 
}

function getDateIso($value){
	if ( ! $value){
		return "";
	}
	return preg_replace("#^(\d{2})/(\d{2})/(\d{4})$#",'$3-$2-$1',$value);
}


function rrmdir($dir) {	
	if (! is_dir($dir)) {
		return;
	}
	foreach ( scandir($dir) as $object) {
		if (in_array($object,array(".",".."))) {
			continue;
		}
		if (is_dir("$dir/$object")){
			rrmdir("$dir/$object");
		} else {
			unlink("$dir/$object");
		}
	}
	rmdir($dir);
}


function get_argv($num_arg) {
	global $argv;
	if (empty($argv[$num_arg])){
		return false;
	}
	return $argv[$num_arg];
};

function exceptionToJson(Exception $ex) {
    $json = array(
        'date' => date('d/m/Y H:i:s'),
        'code' => $ex->getCode(),
        'file' => $ex->getFile(),
        'line' => $ex->getLine(),
        'message' => $ex->getMessage(),
        // utf8_encode_array non applicable sur getTrace() car peut contenir des "resources"
        'trace' => explode("\n", $ex->getTraceAsString())
    );
    $json = json_encode($json);
    return $json;
}

function date_iso_to_fr($date){
	return date("d/m/Y",strtotime($date));
}

function time_iso_to_fr($datetime){
	return date("d/m/Y H:i:s",strtotime($datetime));
}

function date_fr_to_iso($date){
	return preg_replace("#^(\d{2})/(\d{2})/(\d{4})$#",'$3-$2-$1',$date);	
}

function throwIfFalse($result, $message = false) {
    if ($result === false) {
        throwLastError($message);
    }
    return $result;
}

function throwLastError($message = false) {
    $last = error_get_last();
    $cause = $last['message'];
    if ($message) {
        $ex = $message . ' Cause : ' . $cause;
    } else {
        $ex = $cause;
    }
    throw new Exception($ex);
}

function header_wrapper($str){
	if (TESTING_ENVIRONNEMENT){
		echo "$str\n";
	} else {
		header($str);
	}
}

function exit_wrapper($code = 0){
	if (TESTING_ENVIRONNEMENT){
		throw new Exception("Exit called with code $code");
	} else {
		exit($code);
	}
}

function move_uploaded_file_wrapper($filename,$destination){
	if (TESTING_ENVIRONNEMENT) {
		return rename($filename,$destination);
	} else {
		return move_uploaded_file($filename, $destination);
	}
}

function wl_basename($file) {
	$fileInArray = explode(DIRECTORY_SEPARATOR, $file);
	return end($fileInArray);
}

function tick(){
    static $tick;
    $microtime = microtime(true);
    if ($tick){
        echo intval(($microtime - $tick)*1000);
        echo "\n";
    }
    $tick = $microtime;

}