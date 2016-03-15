<?php
/**
 * You can reference this TV by name in a template "@TVs" parameter
 */
return array(
    'type' => 'text', // hidden|text|file|etc...
    'name' => 'fh_cm_template_id', // <-- this must match the filename EXACTLY (minus .php)
    'caption' => 'Campaign Monitor transactional template ID',
    'description' => 'Only used if sending via Campaign Monitor',
    'editor_type' => 0,
    'display' => '', // default
    'default_text' => '',
    'rank'=>2,
    'properties' => '',
    'input_properties' => '', // serialized
    'output_properties' => '', // serialized
);
// The closing PHP tag is optional, so I leave it off and use an "end of file" (EOF) comment instead.
/*EOF*/