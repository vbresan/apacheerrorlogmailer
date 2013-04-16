<?php

/******************************************************************************/
/* Set up working directories first                                           */
/******************************************************************************/

$workingDirectory = getcwd();
chdir(dirname(__FILE__));

/******************************************************************************/
/* Classes                                                                    */
/******************************************************************************/

/**
 * 
 *
 */
class Timestamps {
	
	private static $fileName = "timestamps.json";
	
	private static $instance = null;
	
	private $timestamps = null;

	private $isUpdated  = false;	
	
	/**
	 * 
	 */
	private function __construct() {
		
		@$jsonArray = file_get_contents(Timestamps::$fileName);
		@$array     = json_decode($jsonArray, true);
		
		if ($array) {
			$this->timestamps = $array;
		} else {
			// file doesn't exist, do nothing
		}
	}
	
	/**
	 * @return Timestamps
	 */
	private static function getInstance() {
		
		if (Timestamps::$instance == null) {
			Timestamps::$instance = new Timestamps();
		} 
		
		return Timestamps::$instance;
	}	
	
	/**
	 * 
	 */
	public static function get() {
		
		$instance = Timestamps::getInstance();
		return $instance->timestamps;
	}

	/**
	 * 
	 * @param string $sectionName
	 * @param int $timestamp
	 */
	public static function updateTimestamp($sectionName, $timestamp) {

		$instance = Timestamps::getInstance();
		$instance->timestamps[$sectionName]['LastModified'] = $timestamp;
		$instance->isUpdated = true;
	}

	/**
	 * 
	 */
	public static function save() {
		
		$instance = Timestamps::getInstance();
		
		if ($instance->isUpdated) {

			$jsonArray = json_encode($instance->timestamps);
			file_put_contents(Timestamps::$fileName, $jsonArray);
		}
	}	
}

/**
 * 
 *
 */
class Properties {
	
	private static $fileName = "properties.ini";
	
	private static $instance = null;
	
	private $properties = null;
	
	/**
	 * 
	 */
	private function __construct() {
		
		$this->properties = parse_ini_file(Properties::$fileName, true);
	}
	
	/**
	 * @return Properties
	 */
	private static function getInstance() {
		
		if (Properties::$instance == null) {
			Properties::$instance = new Properties();
		} 
		
		return Properties::$instance;
	}	
	
	/**
	 * 
	 */
	public static function get() {
		
		$instance = Properties::getInstance();
		return $instance->properties;
	}
}


/******************************************************************************/
/* Functions                                                                  */
/******************************************************************************/

/**
 * 
 * @param string $file
 * @param int $tailLines
 * @return string
 */
function getTail($file, $tailLines) {
	
	$tail = "";
	
	if (! $tailLines) {
		$tailLines = 20;
	}
	
	$fp = fopen($file, 'r');
	if ($fp) {
		
		$bytesRead = 0;
		while ($tailLines >= 0) {
			
			if (fseek($fp, 0 - ++$bytesRead, SEEK_END) == -1) {
				break;
			}
			
			if (false === ($char = fgetc($fp))) {
				break;
			}
			
			$tail .= $char;
			
			if ($char == "\n") {
				$tailLines--;
			}
		}
		
		fclose($fp);
	}
	
	return strrev($tail);
}

/**
 * 
 * @param string $file
 * @param int $timestamp
 * @param int $tailLines
 * @param string $mailTo
 * @param string $mailFrom 
 */
function mailLogChanges($file, $timestamp, $tailLines, $mailTo, $mailFrom) {
	
	$logBody   = "";
	$filemtime = filemtime($file);
	
	if ((! $timestamp) || ($filemtime > $timestamp)) {
		
		$subject = "[Apache Error Log Mailer] Log file (" . $file . ") has changed!";
		$message = getTail($file, $tailLines);
		$headers = "From: " . $mailFrom . "\r\n" .
    		   	   "Reply-To: " . $mailFrom . "\r\n" .
    		   	   "Return-Path: " . $mailFrom . "\r\n" .
    		       'Content-Type: text/plain; charset=UTF-8; format=flowed' . "\r\n" .
    		       'MIME-Version: 1.0' . "\r\n" .
    		       'Content-Transfer-Encoding: 8bit' . "\r\n" .
    		       'X-Mailer: PHP/' . phpversion();

		$message = "Last " . $tailLines . " lines:\n\n" . $message;
		
		mail($mailTo, $subject, $message, $headers);
		echo "Mail regarding the file change (" . $file . ") sent successfully.\n";
	}

	return $filemtime;
}

/******************************************************************************/
/* Main                                                                       */
/******************************************************************************/

$properties = Properties::get();
$timestamps = Timestamps::get();
foreach ($properties as $name => $section) {
	
	$file      = $section['File'];
	$tailLines = $section['TailLines'];
	$mailTo    = $section['MailTo'];
	$mailFrom  = $section['MailFrom'];
	$timestamp = $timestamps[$name]['LastModified'];
	
	if (! ($file && $mailTo && $mailFrom)) {
		continue;
	}
	
	if (! file_exists($file)) {
		echo "File does not exist (yet): " . $file . "\n";
		continue;
	}
		
	$filemtime = mailLogChanges($file, $timestamp, $tailLines, $mailTo, $mailFrom);
	if ($filemtime > $timestamp) {
		Timestamps::updateTimestamp($name, $filemtime);
	}
}

Timestamps::save();

chdir($workingDirectory);

?>