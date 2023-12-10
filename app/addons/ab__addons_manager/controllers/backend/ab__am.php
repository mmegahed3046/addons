<?php



use Tygh\Registry;
use Tygh\ABAManager;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (call_user_func('in_array', call_user_func('\Tygh\Registry::get', 'runtime.mode'), array('install', 'update'))) {
        // $r = call_user_func('\Tygh\ABAManager::i_a', $_REQUEST);
        // if (!empty($r)) call_user_func('fn_set_notification', 'W', call_user_func('__', 'warning'), $r, 'S');
        return array(CONTROLLER_STATUS_OK, 'ab__am.addons');
    }
}
if (!call_user_func('\Tygh\Registry::get', 'runtime.simple_ultimate') and call_user_func('\Tygh\Registry::get', 'runtime.company_id')) {
    Registry::get('view')->assign('go_to_all_stores', true);
} elseif ($mode == 'addons') {
    $d = ABAManager::ch_a(Registry::get('runtime.action') == 'tolko-chtobu-zaputat', call_user_func('\Tygh\Registry::get', 'runtime.action') == 'debug', defined('AJAX_REQUEST'));
    // call_user_func('Tygh\Addons\Ab_addonsManager\TooltipsData::downloadTooltips');
    Registry::get('view')->assign('d', $d);
    Registry::get('view')->assign('abam_events', !empty($d['events']) ? $d['events'] : []);
}
