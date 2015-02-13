<?php
class Form_Processor{
    public $send_email;
    public $subscribe;
    public $from_id;
    public $cm_api_key;
    public $cm_list_id;
    public $email_subject;
    private $is_xhr;
    private $validator;

    public function __construct($modx)
    {
        $this->modx = $modx;
        $this->fields = $_POST;
        $this->subscription_option = null;  
        $this->from_id = 0;
        $this->slack_client = new Slack_Client('modx-bot','#dev-ops','#D00000','https://hooks.slack.com/services/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');
        $this->option_templates = explode(',',$this->modx->getOption('cm_subscription_templates'));
        
        //set domain name
        preg_match('/\/\/(.*)\/$/', $this->modx->getOption('site_url'), $matches);
        $this->domain_name = $matches[1];

        //grab the 'from' page if it was passed in the fields
        if(!empty($this->fields['from_page'])){
           $this->from_id =  $this->fields['from_page'];
           unset($this->fields['from_page']);
        }

        $this->recursive_grab($this->from_id); //pull values from the 'from' page and it's ancestors

        $this->grab_system_settings(); //anything null, grab from system settings
    }
    private function _DEBUG($val){
        if(!isset($val)){
            $val = $this;
        }
        $this->modx = null;
        echo "<pre>";
        var_dump($val);
        echo "</pre>";
        die;
    }
    private function grab_system_settings()
    {
        $from_id = $this->modx->getOption('formhander.from_page', null, null);
        $send_email = $this->modx->getOption('formhander.send_email', null, null);
        $subscribe = $this->modx->getOption('formhander.subscribe', null, null);
        $cm_api_key = $this->modx->getOption('formhander.cm_api_key', null, null);
        $cm_list_id = $this->modx->getOption('formhander.cm_list_id', null, null);
        $to_address = $this->modx->getOption('formhander.to_address', null, null);
        $email_subject = $this->modx->getOption('formhander.email_subject', null, null);

        if(is_null($this->from_id) && !is_null($from_id)){
            $this->from_id = $from_id;
        }
        if(is_null($this->send_email) && !is_null($send_email)){
            $this->send_email = $send_email;
        }
        if(is_null($this->subscribe) && !is_null($subscribe)){
            $this->subscribe = $subscribe;
        }
        if(is_null($this->cm_api_key) && !is_null($cm_api_key)){
            $this->cm_api_key = $cm_api_key;
        }
        if(is_null($this->cm_list_id) && !is_null($cm_list_id)){
            $this->cm_list_id = $cm_list_id;
        }
        if(is_null($this->to_address) && !is_null($to_address)){
            $this->to_address = $to_address;
        }
        if(is_null($this->email_subject) && !is_null($email_subject)){
            $this->email_subject = $email_subject;
        }        
    }

    private function recursive_grab($id)
    {
        if($id > 0){
            $tempDoc = $this->modx->getObject('modDocument',$id);
            $this->getValuesFromDoc($id);
            $this->recursive_grab($tempDoc->get('parent'));
        }
    }

    public function validate()
    {
        if(!empty($this->fields)){
            $this->validator = new Field_Validator($this->fields);
            $this->validator->field_rules = array(
                'email_address'=>'email|required'
                );
            if($this->validator->is_valid()){
                return true;
            } else {
                return false;
            }
        }
    }

    private function getValuesFromDoc($id) //we will only add values which are not already set
    {
        if(empty($id)){
            return false;
        }
        $tempDoc = $this->modx->getObject('modDocument', $id);

        if(!is_null($tempDoc)){//we have valid a document
            
            if(empty($this->subscription_option)){
            //if subscription option isn't set, we should check the template type and grab the pagetitle to use as the value
                $template = $tempDoc->get('template');
                if(in_array($template, $this->option_templates)){
                    $this->subscription_option = $tempDoc->get('pagetitle');
                }
            }

            $subscribe  = $tempDoc->getTVValue('fh_subscribe');
            $send_email = $tempDoc->getTVValue('fh_send_email');
            $to_address = $tempDoc->getTVValue('fh_to_address');
            $email_subject = $tempDoc->getTVValue('fh_email_subject');

            if(is_null($this->send_email) && !empty($send_email)){
                $this->send_email = $send_email;
            }
            if(is_null($this->subscribe) && !empty($subscribe)){
                $this->subscribe = $subscribe;
            }
            if(is_null($this->cm_list_id) && !empty($cm_list_id)){
                $this->cm_list_id = $cm_list_id;
            }
            if(is_null($this->to_address) && !empty($to_address)){
                $this->to_address = $to_address;
            }
            if(is_null($this->email_subject) && !empty($email_subject)){
                $this->email_subject = $email_subject;
            }
        }
    }

    public function process()
    {
        //will not reach here if there where any problems with variables unless there were no variables, check that they are set
        if(is_array($this->fields)){

            if($this->subscribe && !empty($this->fields['email_address'])){
                $this->addSubscriber();
            }
            if($this->send_email){
                $this->sendMail();
            }
            //header('Location: '.$this->modx->makeUrl($this->modx->resource->get('id'),'','','full') );
        }
    }

    private function sendMail()
    {
        if(!is_null($this->subscription_option)){
            $this->fields['Subscription Option'] = $this->subscription_option;
        }

        if(is_null($this->email_subject)){
            $this->email_subject = 'Form submission from '.$this->domain_name;
        }
        /////////////////////////
        //send postmark email
        /////////////////////////
        

        // $handle = new Email_Handler($this->modx->getOption('site_name'), $this->domain_name);
        // $handle->sendMail($this->to_address,$this->email_subject, $this->fields);
    }
   
    private function addSubscriber()
    {
        $custEmail = $this->fields['email_address'];
        $custName = $this->fields['name'];
        $fieldOption = $this->subscription_option;
        if(is_null($custName)){
            $custName = '';
        }
        // Check that none of the required variables are null
        try {
            $cm_api = new CM_API(
                $this->cm_api_key,
                $this->cm_list_id
                );
            $cm_api->subscribe($custName, $custEmail, $fieldOption);
        } catch (Exception $e){
            $this->slack_client->addAttachment('Variables',array(
                'Function'=>'addSubscriber',
                'Message'=>$e->getMessage(),
                'custName'=>$custName,
                'custEmail'=>$custEmail,
                'cm_list_id'=>$this->cm_list_id,
                'cm_api_key'=>$this->cm_api_key
                ));
            $this->slack_client->report_error('Error adding subscriber to Email Manager');
        }
        
    }

    private function xhr()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }
}
