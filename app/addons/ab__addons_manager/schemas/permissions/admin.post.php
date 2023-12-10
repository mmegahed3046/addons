<?php
/*******************************************************************************************
if (!defined('BOOTSTRAP')){die('Access denied');}
$schema['ab__am'] = array(
'modes' => array(
'addons' => array (
'permissions' => 'ab__addons_manager.data.manage',
),
'install' => array (
'permissions' => 'ab__addons_manager.data.manage',
),
),
);
return $schema;
