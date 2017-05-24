<?php


class CampaignMonitor_Send implements Email_Send
{
	private $files = array();
	public function __construct($modx)
	{
		$this->modx = $modx;
	}
	public function setTemplateId($templateId){
		$this->templateId = $templateId;
	}
	public function setSubject($subject){
		$this->subject = $subject;
	}
	public function setFields($fields){
		$this->fields = $fields;
	}
	private function setFiles(){
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
	         $data = base64_encode($data);

					 $this->files[] = array(
						 'Content'=> $data,
						 'Name'=>$file_name,
						 'Type'=>$type
					 );
	      }
	    }
	  }
	}
	public function setHtmlContent($content){
		$this->rendered_html = $content;
	}
	public function setPlainContent($content){
		$this->rendered_text_only = $content;
	}
	public function send($to, $from)
	{

		$auth = $this->modx->getOption('formhandler.cm_api_key', null, null);
		$templateId = $this->templateId;
		$cm_transactional_send = new CS_REST_Transactional_SmartEmail($templateId, $auth);

		$email_handler = $this;
		$this->modx->invokeEvent('OnFormHanderEmailRender', array(
			'email_handler'=> $email_handler
			));

		$data = $this->fields;
		$data['content'] = $this->rendered_html;

		if(!empty($this->subject)){
			$data['email_subject'] = $this->subject;
		}

		$message = array(
			'To' => $to,
			'Data' => $data
			);

		$this->setFiles();

		if(!empty($this->files)){
			$message['Attachments'] = $this->files;
		}

		$result = $cm_transactional_send->send($message);
		if(!is_array($result->response)){
			throw new Exception('CampaignMonitor_Send Error: '.$result->response->Message);
		}
		if($result->response[0]->Status != 'Accepted'){
			throw new Exception('CampaignMonitor_Send Error: '.$result->response);
		}
	}
}
