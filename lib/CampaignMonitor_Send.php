<?php


class CampaignMonitor_Send implements Email_Send
{

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
		$result = $cm_transactional_send->send($message);
		if(!is_array($result->response)){
			throw new Exception('CampaignMonitor_Send Error: '.$result->response->Message);
		}
		if($result->response[0]->Status != 'Accepted'){
			throw new Exception('CampaignMonitor_Send Error: '.$result->response);
		}
	}
}