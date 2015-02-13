<?php

class Email_Handler
{
	private $site_name;
	private $domain_name;
	public function __construct($site_name, $domain_name)
	{
		$this->site_name = $site_name;
		$this->domain_name = $domain_name;
	}

	function sendMail($formTo, $formSubject, $fields, $from = null)
	{
		if(is_null($from)){
			$from = 'noreply@'.$this->domain_name;
		}
	     /*********************************************/
	     /*                 MAIL CODE                 */
	     /*********************************************/

	      //set 'from' email address if set
	      if(isset($fields['email_address']) && ($fields['email_address'] != "")){
	        $from = $fields['email_address'];
	      } 

	      //create random mime boundary
	      $mime_boundary= "Multipart_Boundary_x".md5(mt_rand())."x";
	      // To send HTML mail, the Content-type header must be set
	      // This is a fix for encodeing html chars 
	      $headers  = "From: $from\r\n" .
	        "Reply-To: $from\r\n" .
	        "Return-Path: $from\r\n" .
	        "MIME-Version: 1.0\r\n" .
	        "Content-Type: multipart/mixed;\r\n" .
	        " boundary=\"{$mime_boundary}\"";
	      $html_header = "--{$mime_boundary}\nContent-Type: text/html; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 7bit\n\n";
	      $msg = $html_header."<html><body><h2>".$formSubject."</h2><br />";
	      foreach($fields as $key => $field){
	            $key = ucwords(str_replace("_", " ", $key));
	            if(is_array($field)){
	                $msg .= "<h3>$key</h3>";
	                foreach ($field as $question => $answer) {
	                    $msg .= "<h4>$question</h4>";
	                    if($answer!='on'){
	                        $msg .= "<p>$answer</p><br/>";
	                    }
	                }
	                $msg .= '<br/>';
	            } else {
	                $msg .= "<h3>$key</h3><p>".str_replace("\n", "<br />", $field)."</p><br/>";
	            }
	      }
	      // close html wrapper elements on message
	      $msg .= "</body></html>\n\n";
	      if(!empty($_FILES)){
	        $msg .= $this->getFiles($mime_boundary);
	      }
	      $msg .= "--{$mime_boundary}--";
	      
	      return mail($formTo, $formSubject, $msg, $headers);
	}

	function getFiles($mime_boundary)
	{
	  $returnVal = "";
	  foreach ($_FILES as $fieldName => $file) {
	    $tmp_name = $_FILES[$fieldName]['tmp_name'];
	    $type = $_FILES[$fieldName]['type'];
	    $file_name = $_FILES[$fieldName]['name'];
	    $size = $_FILES[$fieldName]['size'];

	    if (file_exists($tmp_name)){

	      // Check to make sure that it is an uploaded file and not a system file
	      if(is_uploaded_file($tmp_name)){

	         // Now Open the file for a binary read
	         $file = fopen($tmp_name,'rb');

	         // Now read the file content into a variable
	         $data = fread($file,filesize($tmp_name));

	         // close the file
	         fclose($file);

	         // Now we need to encode it and split it into acceptable length lines
	         $data = chunk_split(base64_encode($data));
	      }

	      $returnVal .= "--{$mime_boundary}\n" .
	         "Content-Type: {$type};\n" .
	         " name=\"{$file_name}\"\n" .
	         // "Content-Disposition: attachment;\n" .
	         "Content-Transfer-Encoding: base64\n\n" .
	         $data . "\n\n";
	    }
	  }
	  return $returnVal;
	}

	private function pageURL(){
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
		    $pageURL .= $domain.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}


}