<?php

class Slack_Client{
	private $message;
	private $attachments;
	private $username;
	private $channel;
	private $color;
	private $pretext;

	public function __construct($username, $channel, $color, $webHookUrl)
	{
		$this->username = $username;
		$this->channel = $channel;
		$this->color = $color;
		$this->webHookUrl = $webHookUrl;
		$this->attachments = array();
	}

	public function setMessage($message)
	{
		$this->message = $message;
	}

	public function addAttachment($title, $fields)
	{
		$this->attachments[$title] = $fields;
	}
	public function addPretext($pretext)
	{
		$this->pretext = $pretext;
	}

	private function getPayload()
	{
		$obj = $this;
		$payload = array(
			'username'=>$obj->username,
			'channel'=>$obj->channel,
			'text'=>$obj->message
			);
		if(!empty($obj->attachments)){
			//go through all attachments, and add to fields
			$formattedFields = array();
			$formattedFields[] = array(
				'title'=>'URL',
				'value'=>'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}",
				'short'=>false
				);
			$formattedFields[] = array(
				'title'=>'REFERER',
				'value'=>$_SERVER['HTTP_REFERER'],
				'short'=>false
				);
			foreach ($obj->attachments as $title => $fields) {
				$formattedFields[] = array(
					'title'=>':: '.(string) $title,
					'value'=>'',
					'short'=>false
					);
				foreach ($fields as $key => $value) {
					$formattedFields[] = array(
						'title'=> (string) $key,
						'value'=> (string) $value,
						'short'=> true,
						);
				}
			}
			//add the attchments array to the payload
			$payload['attachments'] = array(array(
				'fallback'=> json_encode($formattedFields),
				'color'=> $obj->color,
				'fields'=> $formattedFields,
				'pretext'=>$obj->pretext
				));
		}
		return json_encode($payload);
	}

	public function report_error($pretext,$modx)
	{
		$this->setMessage('Error report for: '.
			$modx->getOption('site_url'));
		$this->pretext = $pretext;
		if(!empty($_GET)){
			$this->addAttachment('$_GET Contents',$_GET);
		}
		if(!empty($_POST)){
			$this->addAttachment('$_POST Contents',$_POST);
		}
		if(!empty($_FILES)){
			$this->addAttachment('$_FILES Contents',$_FILES);
		}
		$this->send();
	}

	public function send() //throws exception
	{
		$obj = $this;
		//send email/slack notification
		$client = new GuzzleHttp\Client();
	    $request = $client->createRequest('POST', $obj->webHookUrl);
	    $postBody = $request->getBody();
	    $postBody->setField('payload',$obj->getPayload());
	    $response = $client->send($request);
	    
	    $this->attachments = array();
	    $this->message = "";
	}
}