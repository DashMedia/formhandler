<?php

class CM_API{
	private $name;
	private $email_address;
	private $api_key;
	private $list_id;
	private $subscription_option;
	private $custom_field_name;
	private $typesWithOptions;
	// private $custom_field_id;
	private $custom_field_obj;
	private $custom_fields;
	private $subscribers_connection;
	private $lists_connection;


	public function __construct($api_key, $list_id)
	{
		$this->$api_key = $api_key;
		$this->$list_id = $list_id;
		$this->typesWithOptions = array(
			'MultiSelectOne', 
			'MultiSelectMany'
			);
		//create subscribers_connection
		$this->subscribers_connection = new CS_REST_Subscribers(
				$list_id, array('api_key'=>$api_key)
			);

		$this->lists_connection = new CS_REST_Lists(
				$list_id, array('api_key'=>$api_key)
			);

	}

	public function subscribe($name, $email_address)
	{
		$this->name = $name;
		$this->email_address = $email_address;
		// $this->subscription_option = $subscription_option;
		// $this->custom_field_name = $subscription_field;
		
		$options = array(
			'Name' => $this->name,
			'EmailAddress' => $this->email_address,
			'Resubscribe' => true
		);

		$user_details = $this->get_subscriber();

		$current_custom_field_values = null;
		if(!is_null($user_details))
		{
			// we have an existing user, grab their custom fields
			$current_custom_field_values = $user_details->CustomFields;
		}
		
		if($this->process_custom_fields($current_custom_field_values)){
			//returns true if there are custom fields associated with our subscriber
			$options['CustomFields'] = $this->custom_fields;
		}

		$api_result = $this->subscribers_connection->add($options);
		if(!$api_result->was_successful()){
			throw new Exception('Unable to add subscriber: '.$api_result->report_error);
		}
	}

	// private function init_custom_field($field, $value, $type)
	// {
	// 	if(!$this->custom_field_exists($field)){
	// 		// $this->custom_field_id = 0;
	// 		$result = $this->lists_connection->create_custom_field(array(
	// 			'FieldName' => $field,
	// 			'DataType' => $type,
	// 			'Options' => array($value),
	// 			'VisibleInPreferenceCenter' => true
	// 		));
	// 		if(!$result->was_successful()){
	// 			throw new Exception('Unable to create required custom field');
	// 		} else {
	// 			$this->custom_field_obj = (object) array('FieldOptions'=>array($this->subscription_option));
	// 			//saves doing another request
	// 		}
	// 	}
	// 	if($type === CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTONE || $type === CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTMANY){
	// 		$this->init_field_option($field,$value);			
	// 	}
	// }

	// private function custom_field_exists($field)
	// {
	// 	// $obj = $this;
	// 	$field_key = false;
	// 	$custom_fields = $this->lists_connection->get_custom_fields();
	// 	foreach ($custom_fields->response as $key => $value) {
	// 		if($value->FieldName == $field){
	// 			return true;
	// 		}
	// 	}
	// 	return false;
	// }

	// private function get_custom_fields()
	// {
	// 	if(is_null($this->custom_fields)){
	// 		$this->custom_fields = $this->lists_connection->get_custom_fields()->response;
	// 	}
	// }

	// private function init_field_option($field, $value)
	// {
	// 	if(!in_array($value, $this->custom_field_obj->FieldOptions)){ // check if the option needs to be created

	// 		$result = $this->lists_connection->update_field_options(
	// 			$this->custom_field_key,
	// 			array($this->subscription_option),
	// 			true //keep existing options
	// 			);

	// 		if(!$result->was_successful()){
	// 			throw new Exception('Unable to create new option for custom field');
	// 		}
	// 	}
	// }

	public function process_custom_fields(&$current_values)
	{
		$existing_custom_fields = $this->lists_connection->get_custom_fields()->response;

		if(is_null($current_values) && is_null($this->custom_fields)){
			//no custom field values to deal with
			var_dump(false);
			return false;
		}

		foreach($this->custom_fields as $key => &$value){
			// iterate through fields, making sure they exist, and that the value is a viable option.
			foreach ($existing_custom_fields as $fieldId => $field) {
				if($value['FieldName'] === $field->FieldName){
					$value['Key'] = $field->Key;
					if(isset($field->FieldOptions)){
						$value['Options'] = $field->FieldOptions;
						$value['FieldOptions'] = &$value['Options'];
					}
					break;
				}
			}
			if(!isset($value['Key'])){ //not an existing custom field, create it
				if(in_array($value['DataType'], $this->typesWithOptions)){//field type has pre-defined options
					$value['Options'] = array($value['Value']);
				}
				$api_result = $this->lists_connection->create_custom_field($value);
				if($api_result->was_successful()){
					$value['Key'] = $api_result->response;
				} else {
					throw new Exception("Error adding new custom field: ".$api_result->response);
				}
			}

			if(in_array($value['DataType'], $this->typesWithOptions) && !in_array($value['Value'],$value['Options'])){//availible options doesn't include our new value, add it

				$api_result = $this->lists_connection->update_field_options(
					$value['Key'],
					array($value['Value']),
					true //keep existing options
					);

				if(!$api_result->was_successful()){
					throw new Exception("Error adding new option to custom field: ".$api_result->response);
				}

				$value['Options'][] = $value['Value'];
			}

			// update the current values array with the new values
			if($value['DataType'] != CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTMANY){ //check if field already has a value, and replace it
				$found = false;
				foreach ($current_values as $current_key => &$current_value) {
					if($current_value->Key == $value['FieldName']){
						$found = true;
						$current_value->Value = $value['Value'];
					}
				}

				if(!$found){ //field doesn't currently have a value
					$current_values[] = (object) array(
						'Key'=>$value['FieldName'],
						'Value'=>$value['Value']
						);
				}
			} else { //multiselect, add the new value as an additional value
				$current_values[] = (object) array(
					'Key'=>$value['FieldName'],
					'Value'=>$value['Value']
					);
			}
		}
		return true;
	}
	/*
	*	
	*	Valid $type input values
	*	CS_REST_CUSTOM_FIELD_TYPE_TEXT = Text
	* 	CS_REST_CUSTOM_FIELD_TYPE_NUMBER = Number
	*  	CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTONE = MultiSelectOne
	*   CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTMANY = MultiSelectMany
	*   CS_REST_CUSTOM_FIELD_TYPE_DATE = Date
	*   CS_REST_CUSTOM_FIELD_TYPE_COUNTRY = Country
	*   
	*/
	public function add_custom_field_value($key, $value, $type = CS_REST_CUSTOM_FIELD_TYPE_TEXT)
	{
		$this->custom_fields[] = array('FieldName' => $key, 'Value' => $value, 'DataType' => $type);
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