<?php
/**
 * You can reference this TV by name in a template "@TVs" parameter
 */
return array(
    'type' => 'autotag', // hidden|text|file|etc...
    'name' => 'fh_cm_variables_to_store', // <-- this must match the filename EXACTLY (minus .php)
    'caption' => 'Variables from form to store in Email Manager',
    'description' => '',
    'editor_type' => 0,
    'display' => '', // default
    'default_text' => '',
    'rank'=>9,
    'properties' => '',
    'input_properties' => '', // serialized
    'output_properties' => '', // serialized
);
// The closing PHP tag is optional, so I leave it off and use an "end of file" (EOF) comment instead.
/*EOF*/