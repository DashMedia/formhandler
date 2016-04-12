<?php


use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class Postmark_Send implements Email_Send
{

	private $site_name;
	private $domain_name;
	private $postmark_server_token;
	private $postmark_client; 

	public function __construct($modx)
	{
		$this->postmark_sender = $modx->getOption('formhandler.postmark_sender',null);
		$this->postmark_server_token = $modx->getOption('formhandler.postmark_token',null);
		$this->postmark_client = new PostmarkClient($this->postmark_server_token);

		$this->modx = $modx;
	}

	public function setSubject($subject){
		$this->subject = $subject;
	}
	public function setFields($fields){
		$this->fields = $fields;
	}
	public function setHtmlContent($content){
		$html_email_tpl = $this->modx->getOption('formhandler.html_email', null);
		$content = $this->modx->getChunk($html_email_tpl, array(
			'subject'=>$this->subject,
			'fields'=> $content
			));
		$this->rendered_html = $content;
	}
	public function setPlainContent($content){
		$text_email_tpl = $this->modx->getOption('formhandler.text_email', null);
		$content = $this->modx->getChunk($html_email_tpl, array(
			'subject'=>$this->subject,
			'fields'=> $content
			));
		$this->rendered_text_only = $content;
	}
	public function send($to, $from)
	{

		$postmark_send = $this;

		$this->modx->invokeEvent('OnFormHanderEmailRender', array(
			'email_handler'=> $postmark_send
			));

		$this->postmark_client->sendEmail($this->postmark_sender, $to, $this->subject, $this->rendered_html, $this->rendered_text_only);

	}
}