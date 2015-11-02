<?php
if (!defined('MODX_CORE_PATH')) {return;}

$core_path = $modx->getOption('formhandler.core_path', null, MODX_CORE_PATH.'components/formhandler/');

$event = $modx->getObject('modEvent',array('name'=>'OnFormHanderEmailRender'));
if(!is_null($event)){
	$event->remove();
}
