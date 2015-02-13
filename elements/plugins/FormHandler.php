<?php
/**
 * @name FormHandler
 * @description This is an example plugin.  List the events it attaches to in the PluginEvents.
 * @PluginEvents OnWebPageInit
 */

// Your core_path will change depending on whether your code is running on your development environment
// or on a production environment (deployed via a Transport Package).  Make sure you follow the pattern
// outlined here. See https://github.com/craftsmancoding/repoman/wiki/Conventions for more info
$core_path = $modx->getOption('formhandler.core_path', null, MODX_CORE_PATH.'components/formhandler/');
include_once $core_path .'vendor/autoload.php';

switch ($modx->event->name) {

    case 'OnWebPageInit':
    		// if(isset($_POST['formhander'])){		
    		if(true){		
    			include_once $core_path .'lib/Form_Processor.php';
    			include_once $core_path .'lib/Email_Handler.php';
    			include_once $core_path .'lib/Field_Validator.php';
    			include_once $core_path .'lib/CM_API.php';
    			include_once $core_path .'lib/Slack_Client.php';

    			$form_processor = new Form_Processor($modx);
    			
    			if($form_processor->validate()){
    				//validation passed, continue to email/subscribe
    				//process inputs
    				$form_processor->process();
    			} else {
    				//validation failed, spit in slack
    				try{
    					//send slack notification of error
    					$slack_client = new Slack_Client('modx-bot','#dev-ops','#D00000','https://hooks.slack.com/services/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');
    					$slack_client->setMessage('Form validation failed: '.
    						$modx->getOption('site_url'));

    					if(!empty($_GET)){
    						$slack_client->addAttachment('$_GET Contents',$_GET);
    					}
    					if(!empty($_POST)){
    						$slack_client->addAttachment('$_POST Contents',$_POST);
    					}
    					if(!empty($_FILES)){
    						$slack_client->addAttachment('$_FILES Contents',$_FILES);
    					}
    					$slack_client->send();

    				} catch(Exception $e){
    					$modx->log(MODX::LOG_LEVEL_ERROR, 'Error sending slack notification: Request='.$e->getRequest().' Response: '.$e->getResponse());
    					$modx->log(MODX::LOG_LEVEL_ERROR, 'Slack Request: '.$e->getRequest());
    					$modx->log(MODX::LOG_LEVEL_ERROR, 'Slack Response: '.$e->getResponse());
    				}
    			}	
    		}
        break;
}