<?php



use Tygh\Registry;
use Tygh\ABAManager;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
function fn_ab__am()
{
    // call_user_func('\Tygh\ABAManager::ch_a');
    call_user_func('fn_clear_cache', 'all');
    call_user_func('fn_clear_cache', 'static');
    call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_static'));
    call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_misc'));
    call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_templates'));
    call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_registry'));
}
