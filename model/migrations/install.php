<?php
if (!defined('MODX_CORE_PATH')) {return;}

$core_path = $modx->getOption('formhandler.core_path', null, MODX_CORE_PATH.'components/formhandler/');

$event = $modx->newObject('modEvent');
$event->set('name', 'OnFormHanderEmailRender');
$event->set('service',1); 
$event->set('groupname', 'FormHandler');
$event->save();