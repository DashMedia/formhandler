<?php
class Form_Processor{
    public $send_email;
    public $subscribe;
    public $from_id;
    public $cm_api_key;
    public $cm_list_id;
    public $email_subject;
    private $modx;
    private $is_xhr;
    private $two_step_complete_flag;
    private $validator;
    private $slack_client;
    private $email_handler;
    private $postmark_client;

    public function __construct($modx, $slack_client, $postmark_client, $email_handler)
    {
        $this->modx = $modx;
        $this->slack_client = $slack_client;
        $this->email_handler = $email_handler;
        $this->postmark_client = $postmark_client;
        $this->fields = $_POST;
        $this->subscription_option = null;
        $this->from_id = $this->modx->getOption('site_start'); //default
        $this->option_templates = explode(',',$this->modx->getOption('formhandler.subscription_templates'));

        //grab the 'from' page if it was passed in the fields
        if(!empty($this->fields['from_page'])){
           $this->from_id =  $this->fields['from_page'];
           unset($this->fields['from_page']);
        }

        $this->recursive_grab($this->from_id); //pull values from the 'from' page and it's ancestors

        $this->grab_system_settings(); //anything null, grab from system settings
    }

    private function _DEBUG($val)
    {
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
        $from_id = $this->modx->getOption('formhandler.from_page', null, null);
        $send_email = $this->modx->getOption('formhandler.send_email', null, null);
        $subscribe = $this->modx->getOption('formhandler.subscribe', null, null);
        $cm_api_key = $this->modx->getOption('formhandler.cm_api_key', null, null);
        $cm_list_id = $this->modx->getOption('formhandler.cm_list_id', null, null);
        $to_address = $this->modx->getOption('formhandler.to_email', null, null);
        $email_subject = $this->modx->getOption('formhandler.email_subject', null, null);

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
            $to_address = $tempDoc->getTVValue('fh_to_email');
            $email_subject = $tempDoc->getTVValue('fh_email_subject');
            $cm_list_id = $tempDoc->getTVValue('fh_cm_list_id');

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
        if(is_array($this->fields) && $this->two_step_complete_flag !== false){

            if($this->subscribe && !empty($this->fields['email_address'])){
                $this->addSubscriber();
            }
            if($this->send_email){
                $this->sendMail();
            }
        }
        if(is_array($this->fields) && $this->two_step_complete_flag === false){
            if($this->subscribe && !empty($this->fields['email_address'])){
                $this->addSubscriber();
            }
        }
    }

    public function two_step_complete($is_complete)
    {
        $this->two_step_complete_flag = (bool) $is_complete;
    }

    private function sendMail()
    {
        if(!is_null($this->subscription_option)){
            $this->fields['Subscription Option'] = $this->subscription_option;
        }

        if(is_null($this->email_subject)){
            $this->email_subject = 'Form submission from '.$this->modx->getOption('site_url');
        }
        /////////////////////////
        //send postmark email
        /////////////////////////
        try {
            $this->email_handler->sendMail($this->to_address,$this->email_subject, $this->fields);
        } catch (Exception $e) {
            $this->slack_client->addAttachment('Variables',array(
                'Function'=>'sendMail',
                'Message'=>$e->getMessage(),
                'to_address'=>$this->to_address,
                'email_address'=>$this->fields['email_address'],
                'subject'=>$this->email_subject
                ));
            $this->slack_client->report_error('Error sending email', $this->modx);
        }
    }
   
    private function addSubscriber()
    {
        $custEmail = $this->fields['email_address'];
        $custName = $this->fields['name'];
        $fieldOption = $this->subscription_option;
        
        if(is_null($custName)){
            $custName = '';
        }
        try {
            $cm_api = new CM_API(
                $this->cm_api_key,
                $this->cm_list_id
                );

            $this->store_variables_in_cm($cm_api);
            
            //if it's a two step form, set the custom field value
            if(!is_null($this->two_step_complete_flag)){
                $value = ($this->two_step_complete_flag? 'Complete' : 'Incomplete');
                $cm_api->add_custom_field_value('Enquiry Status',$value,'MultiSelectOne');
            }

            // // add the subscription option if it isn't null
            if(!is_null($fieldOption)){
                $cm_api->add_custom_field_value(
                    'Subscriptions',
                    $fieldOption,
                    'MultiSelectMany');
            }

            $cm_api->subscribe($custName, $custEmail);
        } catch (Exception $e){
            $this->slack_client->addAttachment('Variables',array(
                'Function'=>'addSubscriber',
                'Message'=>$e->getMessage(),
                'custName'=>$custName,
                'custEmail'=>$custEmail,
                'cm_list_id'=>$this->cm_list_id,
                'cm_api_key'=>$this->cm_api_key
                ));
            $this->slack_client->report_error('Error adding subscriber to Email Manager',$this->modx);
        }
    }

    private function store_variables_in_cm($cm_api)
    {
        $form_resource = $this->modx->getObject('modDocument',$this->from_id);
        $variables_to_store = $form_resource->getTVValue('fh_cm_variables_to_store');//name of variable containing csv of variables to store in CM
        $variables_to_store = explode(',',$variables_to_store);
        if(is_array($variables_to_store)){
            foreach ($variables_to_store as $key => $value) {
                $value = str_replace(' ','_',$value); // replace space with underscore
                if(!empty($this->fields[$value])){
                    $cm_api->add_custom_field_value(
                        $value,
                        $this->fields[$value]);
                }
            }
        }
    }

    private function xhr()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }
}
