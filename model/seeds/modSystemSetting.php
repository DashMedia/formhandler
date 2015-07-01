<?php
/*-----------------------------------------------------------------
 * Lexicon keys for System Settings follows this format:
 * Name: setting_ + $key
 * Description: setting_ + $key + _desc
 -----------------------------------------------------------------*/
return array(

    array(
        'key'  		=>     'formhandler.subscribe',
		'value'		=>     '1',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:subscriptions'
    ),
	array(
		'key'  		=>     'formhandler.subscription_templates',
		'value'		=>     '10',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:subscriptions'
	),
	array(
	    'key'  		=>     'formhandler.cm_api_key',
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:subscriptions'
	),
	array(
	    'key'  		=>     'formhandler.cm_list_id',
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:subscriptions'
	),
	array(
	    'key'  		=>     'formhandler.send_email',
		'value'		=>     '0',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:email'
	),
	array(
	    'key'  		=>     'formhandler.to_email',
		'value'		=>     'jason@dashmedia.com.au',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:email'
	),
	array(
	    'key'  		=>     'formhandler.email_subject',
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:email'
	),
	array(
	    'key'  		=>     'formhandler.postmark_token',
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:postmark'
	),
	array(
	    'key'  		=>     'formhandler.postmark_sender',
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:postmark'
	),
	array(
	    'key'  		=>     'formhandler.slack_bot_name',
		'value'		=>     'modx-bot',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:slack'
	),
	array(
	    'key'  		=>     'formhandler.slack_channel',
		'value'		=>     '#error-logs',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:slack'
	),
	array(
	    'key'  		=>     'formhandler.slack_webhook_url',
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:slack'
	),
	array(
	    'key'  		=>     'formhandler.html_email',
		'value'		=>     'fh_html_email_template',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:chunks'
	),
	array(
	    'key'  		=>     'formhandler.html_field',
		'value'		=>     'fh_html_field_template',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:chunks'
	),
	array(
	    'key'  		=>     'formhandler.text_email',
		'value'		=>     'fh_text_email_template',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:chunks'
	),
	array(
	    'key'  		=>     'formhandler.text_field',
		'value'		=>     'fh_text_field_template',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:chunks'
	),

);
/*EOF*/