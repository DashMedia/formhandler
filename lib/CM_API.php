<?php

class CM_API{
	private $name;
	private $email_address;
	private $api_key;
	private $list_id;
	private $subscription_option;
	private $custom_field_name;
	// private $custom_field_id;
	private $custom_field_obj;
	private $custom_fields;
	private $subscribers_connection;
	private $lists_connection;

	function __construct($api_key, $list_id)
	{
		$this->$api_key = $api_key;
		$this->$list_id = $list_id;

		//create subscribers_connection
		$this->subscribers_connection = new CS_REST_Subscribers(
				$list_id, array('api_key'=>$api_key)
			);

		$this->lists_connection = new CS_REST_Lists(
				$list_id, array('api_key'=>$api_key)
			);

	}

	public function subscribe($name, $email_address, $subscription_option = null, $subscription_field = "Subscriptions")
	{
		$this->name = $name;
		$this->email_address = $email_address;
		$this->subscription_option = $subscription_option;
		$this->custom_field_name = $subscription_field;

		$options = array(
			'Name' => $this->name,
			'EmailAddress' => $this->email_address,
			'Resubscribe' => true
		);
		$user_details = $this->get_subscriber();
		$custom_fields = array();
		if(!is_null($user_details))
		{
			// we have an existing user, grab their custom fields
			$custom_fields = $user_details->CustomFields;
		}

		if(!is_null($subscription_option))
		{
			//we have a new subscription option, add it to custom fields
			$custom_fields[] = array(
				'Key'=>$subscription_field,
				'Value'=>$subscription_option
				);

			//add custom fields to options
			$options['CustomFields'] = $custom_fields;

			//make sure the custom field exists
			$this->init_custom_field();
			if(!is_null($this->subscription_option)){
				$this->init_field_option();
			}

		}

		$api_result = $this->subscribers_connection->add($options);
		if(!$api_result->was_successful()){
			throw new Exception('unable to add subscriber: '.$api_result->report_error);
		}
	}

	private function init_custom_field($field, $value, $type)
	{
		if(!$this->custom_field_exists($field)){
			// $this->custom_field_id = 0;
			$result = $this->lists_connection->create_custom_field(array(
				'FieldName' => $field,
				'DataType' => $type,
				'Options' => array($value),
				'VisibleInPreferenceCenter' => true
			));
			if(!$result->was_successful()){
				throw new Exception('Unable to create required custom field');
			} else {
				$this->custom_field_obj = (object) array('FieldOptions'=>array($this->subscription_option));
				//saves doing another request
			}
		}
		if($type === CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTONE || $type === CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTMANY){
			$this->init_field_option($field,$value);			
		}
	}

	private function custom_field_exists($field)
	{
		// $obj = $this;
		$field_key = false;
		$custom_fields = $this->lists_connection->get_custom_fields();
		foreach ($custom_fields->response as $key => $value) {
			if($value->FieldName == $field){
				return true;
			}
		}
		return false;
	}

	// private function get_custom_fields()
	// {
	// 	if(is_null($this->custom_fields)){
	// 		$this->custom_fields = $this->lists_connection->get_custom_fields()->response;
	// 	}
	// }

	private function init_field_option($field, $value)
	{
		if(!in_array($value, $this->custom_field_obj->FieldOptions)){ // check if the option needs to be created

			$result = $this->lists_connection->update_field_options(
				$this->custom_field_key,
				array($this->subscription_option),
				true //keep existing options
				);

			if(!$result->was_successful()){
				throw new Exception('Unable to create new option for custom field');
			}
		}
	}

	private function process_custom_fields()
	{
		$existing_custom_fields = $this->lists_connection->get_custom_fields()->response;
		foreach($this->custom_fields as $key => $value){
			// iterate through fields, making sure they exist, and that the value is a viable option.
		}
	}

	public function add_custom_field_value($key, $value, $type = CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTONE)
	{
		$this->custom_fields[] = array($key, $value, $type);
	}

	private function get_subscriber()
	{
		$result = $this->subscribers_connection->get($this->email_address);
		if($result->was_successful()){
			return $result->response;
		} else {
			return null;
		}
	}
}