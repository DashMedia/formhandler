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
		'value'		=>     '',
		'xtype'		=>     'textfield',
		'namespace' => 'formhandler',
		'area' 		=> 'formhandler:email'
	),
	array(
	    'key'  		=>     'formhandler.to_email',
		'value'		=>     '',
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

);
/*EOF*/