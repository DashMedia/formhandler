<?php
/**
 * @name FormHandler
 * @description $_POST['fh_2step'] must be set to '1' on the first step and '2' on the second step
 * @PluginEvents OnWebPageInit
 */

// Your core_path will change depending on whether your code is running on your development environment
// or on a production environment (deployed via a Transport Package).  Make sure you follow the pattern
// outlined here. See https://github.com/craftsmancoding/repoman/wiki/Conventions for more info
$core_path = $modx->getOption('formhandler.core_path', null, MODX_CORE_PATH.'components/formhandler/');
include_once $core_path .'vendor/autoload.php';
switch ($modx->event->name) {

    case 'OnWebPageInit':
    		if(isset($_POST['formhandler'])){		
    		// if(true){		//debugging, always run
    			// include_once $core_path .'lib/Form_Processor.php';
    			// include_once $core_path .'lib/Email_Handler.php';
    			// include_once $core_path .'lib/Field_Validator.php';
    			// include_once $core_path .'lib/CM_API.php';
    			// include_once $core_path .'lib/Slack_Client.php';

                //grab system settings
                $bot_name = $modx->getOption('formhandler.slack_bot_name',null);
                $channel = $modx->getOption('formhandler.slack_channel',null);
                $slack_url = $modx->getOption('formhandler.slack_webhook_url',null);
                $channel = $modx->getOption('formhandler.slack_channel',null);
                // $postmark_server_token = $modx->getOption('formhandler.postmark_token',null);
                // $postmark_sender = $modx->getOption('formhandler.postmark_sender',null);

                //setup clients
                $slack_client = new Slack_Client($bot_name, $channel,$slack_url);

    			$form_processor = new Form_Processor($modx, $slack_client, $postmark_client, $email_handler);

                if(isset($_POST['fh_2step'])){
                    if($_POST['fh_2step'] == 2){
                        // value set to step 2
                        $form_processor->two_step_complete(true);
                    }
                    if($_POST['fh_2step'] == 1){
                        // value set to step 1
                        $form_processor->two_step_complete(false);
                    }
                }

    			if($form_processor->validate()){
    				//validation passed, continue to email/subscribe
    				//process inputs
    				$form_processor->process();
    			} else {
                    echo "invalid input";
    				//validation failed, spit in slack
    				try{
    					//send slack notification of error
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