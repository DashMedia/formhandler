<?php

class Content_Generator
{
	private $content;

	public function __construct($fields, $modx)
	{
		$this->fields = $fields;
		$this->modx = $modx;
	}

	public function getHtml(){
		$html_field_tpl = $this->modx->getOption('formhandler.html_field', null, null);
		return $this->generate_content($html_field_tpl, true);
	}

	public function getPlainText(){
		$text_field_tpl = $this->modx->getOption('formhandler.text_field', null, null);

		return $this->generate_content($text_field_tpl, false);
	}

	private function generate_content($field_tpl, $wrap_in_p)
	{	
		$rendered_fields = "";
		foreach ($this->fields as $key => $value) {
			$rendered_fields .= $this->field_html($key,$value,3,$field_tpl,$wrap_in_p);
		}
		return $rendered_fields;
	}

	private function field_html($heading, $value, $heading_level, $field_tpl,$wrap_in_p)
	{
		$return_html = "";
		if(empty($value)){ // return empty if value is blank
			return "";
		}
		if(is_array($value)){
			$nested_values = "";

			foreach ($value as $title => $field) {
				$sub_heading_level = $heading_level + 1;
				$nested_values .= $this->field_html($title, $field, $sub_heading_level, $field_tpl);
			}

			$return_html = $this->modx->getChunk($field_tpl,
				array(
					'h_level'=>$heading_level,
					'title'=>$heading,
					'value'=>$nested_values
					));

		} else {
			if($wrap_in_p){
				$value = '<p>'.$value.'</p>';
			}
			$return_html = $this->modx->getChunk($field_tpl,
				array(
					'h_level'=>$heading_level,
					'title'=>$heading,
					'value'=>$value
					));
		}
		return $return_html;
	}

}