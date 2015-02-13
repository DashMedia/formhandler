<?php
/**
 * You can reference this TV by name in a template "@TVs" parameter
 */
return array(
    'type' => 'listbox', // hidden|text|file|etc...
    'name' => 'fh_subscribe', // <-- this must match the filename EXACTLY (minus .php)
    'caption' => 'Add form submissions from this page to Email Manager?',
    'description' => '',
    'editor_type' => 0,
    'display' => '', // default
    'default_text' => '',
    'rank'=>3,
    'properties' => '',
    'elements' => 'Yes==1||No==0',
    'input_properties' => '', // serialized
    'output_properties' => '', // serialized
);
// The closing PHP tag is optional, so I leave it off and use an "end of file" (EOF) comment instead.
/*EOF*/