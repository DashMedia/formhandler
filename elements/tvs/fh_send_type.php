<?php
/**
 * You can reference this TV by name in a template "@TVs" parameter
 */
return array(
    'type' => 'listbox', // hidden|text|file|etc...
    'name' => 'fh_send_type', // <-- this must match the filename EXACTLY (minus .php)
    'caption' => 'Email system to use for form submissions',
    'description' => '',
    'editor_type' => 0,
    'display' => '', // default
    'default_text' => '',
    'properties' => '',
    'rank'=>0,
    'elements' => 'Use system default==||MODX SMTP Send==smtp||Postmark==postmark||CampaignMonitor Transactional==campaignmonitor',
    'input_properties' => '', // serialized
    'output_properties' => '', // serialized
);
// The closing PHP tag is optional, so I leave it off and use an "end of file" (EOF) comment instead.
/*EOF*/