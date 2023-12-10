<?php


if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
if ($mode == 'login') {
    if (!empty($_SESSION['auth']['user_id'])) {
        // call_user_func('Tygh\ABAManager::ch_a');
    }
}
