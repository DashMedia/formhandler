<?php


class Smtp_Send implements Email_Send
{

	public function __construct($modx)
	{
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
		$from_name = $this->modx->getOption('formhandler.from_name', null, 'No Reply');

		if(empty($from)){
			$from = $this->modx->getOption('formhandler.from_address', null, $from);
		}

		$email_handler = $this;
		$this->modx->invokeEvent('OnFormHanderEmailRender', array(
			'email_handler'=> $email_handler
			));

		$this->modx->getService('mail', 'mail.modPHPMailer');
		
		$mailer = $this->modx->mail;

		$mailer->setHTML(true);
		$mailer->set(modMail::MAIL_BODY, $this->rendered_html);
		$mailer->set(modMail::MAIL_FROM, $from);
		$mailer->set(modMail::MAIL_FROM_NAME, $from_name);
		$mailer->set(modMail::MAIL_SENDER, $from);
		$mailer->set(modMail::MAIL_SUBJECT, $this->subject);
		$mailer->address('to', $to);
		$mailer->address('reply-to', $from);
		if (!$mailer->send()) {
			throw new Exception("Mail not sent, error from mail.modPHPMailer: " . $mailer->mailer->ErrorInfo);
		}
	}
}