<?php


use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class Email_Handler
{

	private $site_name;
	private $domain_name;
	private $postmark_server_token;
	private $postmark_client;
	public function __construct($site_name, $site_url, $postmark_sender, $postmark_server_token, $modx)
	{
		$this->site_name = $site_name;
		preg_match('/\/\/(.*)\/$/', $site_url, $matches);
		$this->domain_name = $matches[1];
		$this->postmark_sender = $postmark_sender;
		$this->postmark_server_token = $postmark_server_token;
		$this->modx = $modx;
		$this->postmark_client = new PostmarkClient($postmark_server_token);
	}

	private function generate_content($subject, $email_tpl, $field_tpl, $fields)
	{
		$rendered_fields = "";
		foreach ($fields as $key => $value) {
			$rendered_fields .= $this->field_html($key,$value,3,$field_tpl);
		}
		$rendered_content = $this->modx->getChunk($email_tpl,
			array(
					'subject'=>$subject,
					'fields'=>$rendered_fields
				));
		return $rendered_content;
	}

	private function field_html($heading, $value, $heading_level, $field_tpl)
	{
		$return_html = "";
		if(is_array($value)){
			foreach ($value as $title => $field) {
				$sub_heading_level = $heading_level - 1;
				$return_html .= $this->field_html($title, $field, $sub_heading_level);
			}
		} else {
			$return_html = $this->modx->getChunk($field_tpl,
			 array(
					'h_level'=>$heading_level,
					'title'=>$heading,
					'value'=>'<p>'.$value.'</p>'
				));
		}
		var_dump($return_html);
		return $return_html;
	}

	public function sendMail($formTo, $formSubject, $fields)
	{
		if(is_null($from)){
			$from = 'noreply@'.$this->domain_name;
		}

		$rendered_html = $this->generate_content($formSubject, 'fh_html_email_tpl', 'fh_html_field_tpl', $fields);
		$rendered_text_only = $this->generate_content($formSubject, 'fh_text_email_tpl', 'fh_text_field_tpl', $fields);

		$this->postmark_client->sendEmail($this->postmark_sender, $formTo, $formSubject, $rendered_html, $rendered_text_only);
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

	// private function pageURL(){
	// 	$pageURL = 'http';
	// 	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	// 	$pageURL .= "://";
	// 	if ($_SERVER["SERVER_PORT"] != "80") {
	// 	    $pageURL .= $domain.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	// 	} else {
	// 	    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	// 	}
	// 	return $pageURL;
	// }


}