<?php
/*******************************************************************************
 *
 * (C) 2009, Binary Solutions
 *
 * END USER LICENSE AGREEMENT
 * Software License Agreement for Apache Error Log Mailer
 * 
 * IMPORTANT- PLEASE READ CAREFULLY: BY INSTALLING THE SOFTWARE 
 * (AS DEFINED BELOW) AND/OR COPYING THE SOFTWARE, YOU (EITHER ON BEHALF OF 
 * YOURSELF AS AN INDIVIDUAL OR ON BEHALF OF AN ENTITY AS ITS AUTHORIZED 
 * REPRESENTATIVE) AGREE TO ALL OF THE TERMS OF THIS END USER LICENSE 
 * AGREEMENT ('AGREEMENT') REGARDING YOUR USE OF THE SOFTWARE.  IF YOU DO NOT 
 * AGREE WITH ALL OF THE TERMS OF THIS AGREEMENT, YOU MUST REMOVE AND DESTROY 
 * ALL COPIES OF THE SOFTWARE.
 * 
 * 1. GRANT OF LICENSE: Subject to the terms below, Binary Solutions hereby 
 * grants you a non-exclusive, non-transferable license to install and to use 
 * Apache Error Log Mailer ('Software').
 * Under this license, you may: (i) install and use the Software on a single 
 * computer for your personal, internal use (ii) copy the Software for back-up 
 * or archival purposes. (iii)You may not distribute the software to others 
 * without first obtaining the required licenses, where applicable.
 * Whether you are licensing the Software as an individual or on behalf of an 
 * entity, you may not: (i) modify, or create derivative works based upon, the 
 * Software in whole or in part without the express written consent of Binary 
 * Solutions; (ii) distribute copies of the Software; (iii) remove any 
 * proprietary notices or labels on the Software; (iv) resell, lease, rent, 
 * transfer, sublicense, or otherwise transfer rights to the Software.
 * 
 *  2. Apache Error Log Mailer: You acknowledge that no title to the 
 *  intellectual property in the Software is transferred to you. Title, 
 *  ownership, rights, and intellectual property rights in and to the Software 
 *  shall remain that of Binary Solutions. The Software is protected by 
 *  copyright.
 *  
 *  3. DISCLAIMER OF WARRANTY:
 *  YOU AGREE THAT Binary Solutions HAS MADE NO EXPRESS WARRANTIES, ORAL OR 
 *  WRITTEN, TO YOU REGARDING THE PRODUCTS AND THAT THE PRODUCTS ARE BEING 
 *  PROVIDED TO YOU 'AS IS' WITHOUT WARRANTY OF ANY KIND.  Binary Solutions 
 *  DISCLAIMS ANY AND ALL OTHER WARRANTIES, WHETHER EXPRESSED, IMPLIED, OR 
 *  STATUTORY. YOUR RIGHTS MAY VARY DEPENDING ON THE STATE IN WHICH YOU LIVE.
 *  Binary Solutions SHALL NOT BE LIABLE FOR INDIRECT, INCIDENTAL, SPECIAL, 
 *  COVER, RELIANCE, OR CONSEQUENTIAL DAMAGES RESULTING FROM THE USE OF THIS 
 *  PRODUCT.
 *  
 *  4. LIMITATION OF LIABILITY: You use this program solely at your own risk.
 *  IN NO EVENT SHALL Binary Solutions BE LIABLE TO YOU FOR ANY DAMAGES, 
 *  INCLUDING BUT NOT LIMITED TO ANY LOSS, OR OTHER INCIDENTAL, INDIRECT OR 
 *  CONSEQUENTIAL DAMAGES OF ANY KIND ARISING OUT OF THE USE OF THE SOFTWARE, 
 *  EVEN IF Binary Solutions HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH 
 *  DAMAGES. IN NO EVENT WILL Binary Solutions BE LIABLE FOR ANY CLAIM, 
 *  WHETHER IN CONTRACT, TORT, OR ANY OTHER THEORY OF LIABILITY, EXCEED THE 
 *  COST OF THE SOFTWARE. THIS LIMITATION SHALL APPLY TO CLAIMS OF PERSONAL 
 *  INJURY TO THE EXTENT PERMITTED BY LAW.
 *  
 *  5. TERMINATION: This Agreement shall terminate automatically if you fail 
 *  to comply with the limitations described in this Agreement. No notice shall 
 *  be required to effectuate such termination. Upon termination, you must 
 *  remove and destroy all copies of the Software.
 *  
 *  6. MISCELLANEOUS:
 *  
 *  Severability.
 *  In the event of invalidity of any provision of this Agreement, the parties 
 *  agree that such invalidity shall not affect the validity of the remaining 
 *  portions of this Agreement.
 *  Entire Agreement.
 *  You agree that this is the entire agreement between you and Binary 
 *  Solutions, which supersedes any prior agreement, whether written or oral, 
 *  and all other communications between Binary Solutions and you relating to 
 *  the subject matter of this Agreement.
 *  Reservation of rights.
 *  All rights not expressly granted in this Agreement are reserved by Binary 
 *  Solutions.
 * 
 ******************************************************************************/

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