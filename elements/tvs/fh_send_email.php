<?php
/**
 * You can reference this TV by name in a template "@TVs" parameter
 */
return array(
    'type' => 'listbox', // hidden|text|file|etc...
    'name' => 'fh_send_email', // <-- this must match the filename EXACTLY (minus .php)
    'caption' => 'Should form submissions from this page send emails?',
    'description' => '',
    'editor_type' => 0,
    'display' => '', // default
    'default_text' => '',
    'properties' => '',
    'rank'=>0,
    'elements' => 'No==0||Yes==1',
    'input_properties' => '', // serialized
    'output_properties' => '', // serialized
);
// The closing PHP tag is optional, so I leave it off and use an "end of file" (EOF) comment instead.
/*EOF*/