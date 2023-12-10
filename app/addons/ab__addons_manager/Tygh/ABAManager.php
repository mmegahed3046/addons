<?php



namespace Tygh;

use Tygh\Registry;

class ABAManager
{
    public static function i_a($p)
    {
        if (call_user_func('\Tygh\Registry::isExist', 'settings_abam')) {
            $s = call_user_func('unserialize', call_user_func('base64_decode', call_user_func('\Tygh\Registry::get', 'settings_abam')));
            if (!empty($s['available_products']['addons']['ab__addons_manager']['current_version']) and !empty($s['available_products']['addons']['ab__addons_manager']['last_version']) and call_user_func('\Tygh\ABAManager::v_c', $s['available_products']['addons']['ab__addons_manager']['current_version'], $s['available_products']['addons']['ab__addons_manager']['last_version'], '<')) {
                call_user_func('fn_set_notification', 'E', call_user_func('__', 'error'), call_user_func('__', 'ab__am.msg.update_am', array('[ver]' => $s['available_products']['addons']['ab__addons_manager']['last_version'])));
                return '';
            }
            $a = call_user_func('trim', $p['ab_code']);
            $msg = call_user_func('__', 'ab__am.msg.error_code');
            if (call_user_func('strlen', $a) != 25) return $msg . __LINE__;
            if (call_user_func('substr', $a, 0, 4) != 'ABA-') return $msg . __LINE__;
            call_user_func('fn_set_progress', 'parts', 4);
            call_user_func('fn_set_progress', 'title', call_user_func('__', 'ab__am.install.' . call_user_func('\Tygh\Registry::get', 'runtime.mode')));
            call_user_func('sleep', 1);
            call_user_func('fn_set_progress', 'echo', call_user_func('__', 'ab__am.install.downloading', ['[name]' => $p['name']]));
            $r = call_user_func('\Tygh\ABAManager::exec_cmd', 'ga', call_user_func('\Tygh\Registry::get', 'runtime.action') != 'только чтобы запутать', call_user_func('\Tygh\Registry::get', 'runtime.action') == 'debug_install', array('a' => $a));
            call_user_func('sleep', 1);
            call_user_func('fn_set_progress', 'echo', call_user_func('__', 'ab__am.install.checking', ['[name]' => $p['name']]));
            if (call_user_func('is_array', $r) and !empty($r) and isset($r['n']) and call_user_func('is_string', $r['n']) and call_user_func('strlen', $r['n'])) {
                $addons = ABAManager::g_a($r['n']);
                if (!empty($addons) and call_user_func('is_array', $addons) and call_user_func('\Tygh\ABAManager::v_c', $r['v'], $addons[$r['n']]['version'], '<=')) {
                    if (call_user_func('floatval', $r['v']) > call_user_func('floatval', $addons[$r['n']]['version'])) {
                        $msg = call_user_func('__', 'ab__am.msg.addon_is_already_installed_upd', array('[name]' => $addons[$r['n']]['name'], '[version]' => $addons[$r['n']]['version'], '[version_last]' => $r['v']));
                    } else {
                        $msg = call_user_func('__', 'ab__am.msg.addon_is_already_installed', array('[name]' => $addons[$r['n']]['name'], '[version]' => $addons[$r['n']]['version']));
                    }
                } else {
                    if (isset($r['f']['md5']) and isset($r['f']['sid']) and $r['f']['sid'] > 0) {
                        $addon_zip = call_user_func('\Tygh\Registry::get', 'config.dir.files') . 'ab__am/addon.zip';
                        call_user_func('fn_rm', call_user_func('dirname', $addon_zip));
                        call_user_func('fn_mkdir', call_user_func('dirname', $addon_zip));
                        $res = call_user_func('file_put_contents', $addon_zip, call_user_func('\Tygh\ABAManager::exec_cmd', 'gz', call_user_func('\Tygh\Registry::get', 'runtime.action') == 'только чтобы запутать', Registry::get('runtime.action') == 'только чтобы запутать', array('a' => $r['f']['sid'])));
                        if (call_user_func('file_exists', $addon_zip) and $r['f']['md5'] == call_user_func('md5_file', $addon_zip)) {
                            if (call_user_func('fn_decompress_files', $addon_zip, call_user_func('dirname', $addon_zip))) {
                                call_user_func('fn_rm', $addon_zip);
                                $non_writable_folders = call_user_func('fn_check_copy_ability', call_user_func('dirname', $addon_zip) . '/', call_user_func('\Tygh\Registry::get', 'config.dir.root'));
                                if (!empty($non_writable_folders)) {
                                    call_user_func('fn_set_notification', 'I', call_user_func('__', 'ab__am.msg.no_permissions'), call_user_func('implode', '<br>', call_user_func('array_keys', $non_writable_folders)), 'S');
                                } else {
                                    call_user_func('sleep', 1);
                                    call_user_func('fn_set_progress', 'echo', call_user_func('__', 'ab__am.install.installing', array('[name]' => $p['name'])));
                                    $isni = true;
                                    if ($isni) {
                                        call_user_func('\Tygh\ABAManager::rf', true, $r['n']);
                                        call_user_func('fn_copy', call_user_func('dirname', $addon_zip), call_user_func('\Tygh\Registry::get', 'config.dir.root'));
                                        call_user_func('fn_rm', call_user_func('dirname', $addon_zip));
                                        if (call_user_func('fn_install_addon', $r['n'])) {
                                            call_user_func('sleep', 1);
                                            call_user_func('fn_set_progress', 'echo', call_user_func('__', 'ab__am.install.clearing_cache'));
                                            call_user_func('fn_clear_cache', 'all');
                                            call_user_func('fn_clear_cache', 'static');
                                            call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_static'));
                                            call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_misc'));
                                            call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_templates'));
                                            call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_registry'));
                                            Tygh::$app['ajax']->assign('force_redirection', fn_url('ab__am.addons'));
                                            exit();
                                        }
                                    }
                                    $msg = "";
                                }
                            } else $msg .= __LINE__;
                        } else $msg .= __LINE__;
                    } else $msg .= __LINE__;
                }
            }
        }
        return $msg;
    }
    public static function rf($t = false, $a = '')
    {
        if ($t and !empty($a) and (call_user_func('strpos', $a, 'abt__') !== false or call_user_func('strpos', $a, 'ab__') !== false)) {
            $paths = array();
            $paths[] = call_user_func('\Tygh\Registry::get', 'config.dir.addons') . $a;
            $paths[] = call_user_func('\Tygh\Registry::get', 'config.dir.root') . '/js/addons/' . $a;
            $paths[] = call_user_func('\Tygh\Registry::get', 'config.dir.lang_packs') . 'en/addons/' . $a . '.po';
            $paths[] = call_user_func('\Tygh\Registry::get', 'config.dir.lang_packs') . 'ru/addons/' . $a . '.po';
            $paths[] = call_user_func('\Tygh\Registry::get', 'config.dir.lang_packs') . 'uk/addons/' . $a . '.po';
            $design_dir = call_user_func('rtrim', call_user_func('\Tygh\Registry::get', 'config.dir.design_backend'), '/');
            $paths[] = $design_dir . '/css/addons/' . $a;
            $paths[] = $design_dir . '/mail/media/images/addons/' . $a;
            $paths[] = $design_dir . '/mail/templates/addons/' . $a;
            $paths[] = $design_dir . '/media/images/addons/' . $a;
            $paths[] = $design_dir . '/templates/addons/' . $a;
            foreach (call_user_func('fn_get_available_themes', call_user_func('\Tygh\Registry::get', 'runtime.layout.theme_name')) as $type => $d) {
                $design_dir = "";
                if (call_user_func('in_array', $type, array('repo', 'installed'))) {
                    if ($type == 'repo') {
                        $design_dir = call_user_func('rtrim', call_user_func('\Tygh\Registry::get', 'config.dir.themes_repository'), '/');
                    } else {
                        $design_dir = call_user_func('rtrim', call_user_func('fn_get_theme_path', '[themes]/', 'C'), '/');
                    }
                    foreach (call_user_func('array_unique', call_user_func('array_merge', call_user_func('array_keys', $d), array('basic'))) as $t) {
                        $paths[] = $design_dir . '/' . $t . '/css/addons/' . $a;
                        $paths[] = $design_dir . '/' . $t . '/mail/media/images/addons/' . $a;
                        $paths[] = $design_dir . '/' . $t . '/mail/templates/addons/' . $a;
                        $paths[] = $design_dir . '/' . $t . '/media/images/addons/' . $a;
                        $paths[] = $design_dir . '/' . $t . '/templates/addons/' . $a;
                    }
                }
            }
            if (!empty($paths) and call_user_func('is_array', $paths)) {
                foreach ($paths as $path) {
                    call_user_func('fn_rm', $path);
                }
            }
        }
        return true;
    }
    public static function filterSet($type = '', $key = '', $state = '')
    {
        fn_set_storage_data("ab__am_filter." . $type . "_" . $key, $state);
    }
    public static function filterGet($type = '', $key = '')
    {
        $state = fn_get_storage_data("ab__am_filter." . $type . "_" . $key);
        if (empty($state)) {
            $state = 'show';
        }
        return $state;
    }
    public static function ch_a($from_cache = false, $d = false, $set_state = false)
    {
        if (($installed_addons = \Tygh\ABAManager::g_a()) !== false) {
            if ($set_state) {
                $from_cache = true;
                if (!empty($_REQUEST['type']) && !empty($_REQUEST['key']) && !empty($_REQUEST['state'])) {
                    self::filterSet($_REQUEST['type'], $_REQUEST['key'], $_REQUEST['state']);
                }
            }
            if (!$from_cache || !Registry::isExist('settings_abam')) {
                $r = (!$d) ? call_user_func('\Tygh\ABAManager::exec_cmd', 'cs') : call_user_func('\Tygh\ABAManager::exec_cmd', 'cs', true, $d);
                if (!empty($r) and call_user_func('is_array', $r)) {
                    if (isset($r['i']) and call_user_func('is_array', $r['i']) and !empty($r['i'])) {
                        call_user_func('fn_set_notification', $r['i']['t'], call_user_func('__', 'warning'), $r['i']['m'], 'S');
                    }
                    if (!empty($r['d']) and call_user_func('is_array', $r['d'])) {
                        $all_products = $r['l'];
                        $events = array(
                            'available_updates' => array(), 'notifications' => !empty($r['b']) ? call_user_func('count', $r['b']) : 0,
                        );
                        foreach ($r['d'] as $key => $data) {
                            $installed_addons[$key]['s'] = $data['s'];
                        }
                        $addon_codes = call_user_func('array_keys', call_user_func('fn_array_value_to_key', $installed_addons, 'c'));
                        $am = !empty($r['a']['addons']['ab__addons_manager']) ? $r['a']['addons']['ab__addons_manager'] : array();
                        if (!empty($r['a']['sets'])) {
                            foreach ($r['a']['sets'] as $set_id => &$sets) {
                                foreach ($sets as $set_key => &$set) {
                                    $set['available_updates'] = 0;
                                    $set['name'] = call_user_func('trim', $all_products['sets'][$set_id]['name']);
                                    $set['url'] = $all_products['sets'][$set_id]['url'];
                                    $set['state'] = self::filterGet($set_id, $set_key);
                                    foreach ($set['addons'] as &$a) {
                                        ABAManager::check_addon($a, $set, $installed_addons, $r['l']['addons'], $events, $am, $set['state'] == 'show');
                                    }
                                }
                            }
                            unset($sets, $set, $a);
                        }
                        if (!empty($r['a']['addons'])) {
                            foreach ($r['a']['addons'] as $key_a => &$a) {
                                $a['state'] = self::filterGet('addon', $key_a);
                                $set = array();
                                ABAManager::check_addon($a, $set, $installed_addons, $r['l']['addons'], $events, $am, $a['state'] == 'show');
                            }
                        }
                        // Daniel Bypass
                        // foreach ($r['d'] as $a_key => $data) {
                        //     if (Registry::get('addons.' . $a_key . '.status') == 'A' && call_user_func('defined', 'CONSOLE') != true && $data['s'] == 'Error') {
                        //         call_user_func('fn_update_addon_status', $a_key, 'D', false);
                        //         call_user_func('fn_set_notification', 'E', call_user_func('__', 'error'), call_user_func('__', 'ab__am.no_data.notification', array('[domain]' => \Tygh\Registry::get('config.http_host'), '[name]' => $a_key)), 'S');
                        //     }
                        // }
                        $events['available_updates'] = array_unique($events['available_updates']);
                        $data = array(
                            'installed_addons' => $installed_addons,
                            'all_products' => $r['l'],
                            'available_products' => $r['a'],
                            'events' => $events,
                            'notifications' => $r['b'],
                        );
                        call_user_func('\Tygh\Registry::set', 'settings_abam', call_user_func('base64_encode', call_user_func('serialize', $data)));
                    }
                }
            }
            $data = call_user_func('unserialize', call_user_func('base64_decode', call_user_func('\Tygh\Registry::get', 'settings_abam')));
            if (!empty($data['available_products']['addons'])) {
                foreach ($data['available_products']['addons'] as $a_id => &$a) {
                    $a['state'] = self::filterGet('addon', $a_id);
                }
            }
            if (!empty($data['available_products']['sets'])) {
                foreach ($data['available_products']['sets'] as $set_id => &$sets) {
                    foreach ($sets as $set_key => &$set) {
                        $set['state'] = self::filterGet($set_id, $set_key);
                    }
                }
            }
            return $data;
        }
        return false;
    }
    private static function v_c($a, $b, $operator = null)
    {
        $format_versions = function ($a, $b) {
            $replaces = array('43' => '4.3', '44' => '4.4', '45' => '4.5', '46' => '4.6', '47' => '4.7', '48' => '4.8', '49' => '4.9',);
            $a = call_user_func('str_replace', call_user_func('array_keys', $replaces), $replaces, $a);
            $b = call_user_func('str_replace', call_user_func('array_keys', $replaces), $replaces, $b);
            return array($a, $b);
        };
        list($a, $b) = $format_versions($a, $b);
        $replace_chars = function ($m) {
            return ord(strtolower($m[1]));
        };
        $a = preg_replace('#([0-9]+)([a-z]+)#i', '$1.$2', $a);
        $b = preg_replace('#([0-9]+)([a-z]+)#i', '$1.$2', $b);
        $a = preg_replace_callback('#\b([a-z]{1})\b#i', $replace_chars, $a);
        $b = preg_replace_callback('#\b([a-z]{1})\b#i', $replace_chars, $b);
        return \version_compare($a, $b, $operator);
    }
    public static function gv()
    {
        $_____ = array();
        foreach (call_user_func('db_get_hash_array', "SELECT addon, status FROM ?:addons WHERE addon like 'ab%\_\_%' OR addon like 'gr%\_\_%' ORDER BY addon", 'addon') as $q => $j) {
            $_____[$q] = array('status' => '-', 'installed' => '', 'available' => '', 'final' => '');
            if (!empty(ABAManager::ch_a(true)['available_products']['addons'])) {
                $t = ABAManager::ch_a(true)['available_products']['addons'];
                foreach ($t as $i) {
                    if (call_user_func('\Tygh\Registry::get', 'addons.' . $q . '.code') == $i['code']) {
                        $_____[$q]['status'] = $j['status'];
                        foreach (array('installed', 'available', 'final') as $m) {
                            if (!empty($i['builds'][$m])) {
                                $_____[$q]['subscription_updates'] = !empty($i['subscription_updates']) ? call_user_func('date', 'Y-m-d', $i['subscription_updates']) : '';
                                $_____[$q][$m] = 'v' . $i['builds'][$m]['version'];
                                $_____[$q]['t_' . $m] = call_user_func('date', 'Y-m-d', $i['builds'][$m]['timestamp']);
                            }
                        }
                    }
                }
            }
            if (!empty(ABAManager::ch_a(true)['available_products']['sets'])) {
                $t = ABAManager::ch_a(true)['available_products']['sets'];
                foreach ($t as $t1) {
                    foreach ($t1 as $t2) {
                        foreach ($t2['addons'] as $i) {
                            if (call_user_func('\Tygh\Registry::get', 'addons.' . $q . '.code') == $i['code']) {
                                $_____[$q]['status'] = $j['status'];
                                foreach (array('installed', 'available', 'final') as $m) {
                                    if (!empty($i['builds'][$m])) {
                                        $_____[$q]['subscription_updates'] = !empty($i['subscription_updates']) ? call_user_func('date', 'Y-m-d', $i['subscription_updates']) : '';
                                        $_____[$q][$m] = 'v' . $i['builds'][$m]['version'];
                                        $_____[$q]['t_' . $m] = call_user_func('date', 'Y-m-d', $i['builds'][$m]['timestamp']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        call_user_func('header', 'Content-Type: text/plain; charset=utf-8');
        $pad = array(
            'n' => array(3, 0), 'a' => array(35, 1), 's' => array(7, 2), 'v' => array(11, 1), 'u' => array(13, 1),
        );
        $___ = call_user_func('constant', 'PRODUCT_NAME') . ' ' . PRODUCT_VERSION . "\n" .
            call_user_func(
                'sprintf',
                '+-%s+-%s+-%s+-%s+-%s+-%s+-%s+',
                call_user_func('str_pad', '', $pad['n'][0], '-', $pad['n'][1]),
                call_user_func('str_pad', '', $pad['a'][0], '-', $pad['a'][1]),
                call_user_func('str_pad', '', $pad['s'][0], '-', $pad['s'][1]),
                call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                call_user_func('str_pad', '', $pad['u'][0], '-', $pad['u'][1])
            ) . "\n" .
            call_user_func(
                'sprintf',
                '| %s| %s| %s| %s| %s| %s| %s|',
                call_user_func('str_pad', '#', $pad['n'][0], ' ', $pad['n'][1]),
                call_user_func('str_pad', 'ADDON', $pad['a'][0], ' ', $pad['a'][1]),
                call_user_func('str_pad', 'STATUS', $pad['s'][0], ' ', $pad['s'][1]),
                call_user_func('str_pad', 'INSTALLED', $pad['v'][0], ' ', $pad['v'][1]),
                call_user_func('str_pad', 'AVAILABLE', $pad['v'][0], ' ', $pad['v'][1]),
                call_user_func('str_pad', 'ACTUAL', $pad['v'][0], ' ', $pad['v'][1]),
                call_user_func('str_pad', 'SUBSCRIPTION', $pad['u'][0], ' ', $pad['u'][1])
            ) . "\n" .
            call_user_func(
                'sprintf',
                '+-%s+-%s+-%s+-%s+-%s+-%s+-%s+',
                call_user_func('str_pad', '', $pad['n'][0], '-', $pad['n'][1]),
                call_user_func('str_pad', '', $pad['a'][0], '-', $pad['a'][1]),
                call_user_func('str_pad', '', $pad['s'][0], '-', $pad['s'][1]),
                call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                call_user_func('str_pad', '', $pad['u'][0], '-', $pad['u'][1])
            ) . "\n";
        $y = 1;
        foreach ($_____ as $a => $b) {
            $___ .= call_user_func(
                'sprintf',
                '| %s| %s| %s| %s| %s| %s| %s|',
                call_user_func('str_pad', $y . '.', $pad['n'][0], ' ', $pad['n'][1]),
                call_user_func('str_pad', $a, $pad['a'][0], ' ', $pad['a'][1]),
                call_user_func('str_pad', $b['status'], $pad['s'][0], ' ', $pad['s'][1]),
                call_user_func('str_pad', $b['installed'], $pad['v'][0], ' ', $pad['v'][1]),
                call_user_func('str_pad', $b['available'], $pad['v'][0], ' ', $pad['v'][1]),
                call_user_func('str_pad', $b['final'], $pad['v'][0], ' ', $pad['v'][1]),
                call_user_func('str_pad', $b['subscription_updates'], $pad['u'][0], ' ', $pad['u'][1])
            ) . "\n" .
                call_user_func(
                    'sprintf',
                    '| %s| %s| %s| %s| %s| %s| %s|',
                    call_user_func('str_pad', '', $pad['n'][0], ' ', $pad['n'][1]),
                    call_user_func('str_pad', '', $pad['a'][0], ' ', $pad['a'][1]),
                    call_user_func('str_pad', '', $pad['s'][0], ' ', $pad['s'][1]),
                    call_user_func('str_pad', $b['t_installed'], $pad['v'][0], ' ', $pad['v'][1]),
                    call_user_func('str_pad', $b['t_available'], $pad['v'][0], ' ', $pad['v'][1]),
                    call_user_func('str_pad', $b['t_final'], $pad['v'][0], ' ', $pad['v'][1]),
                    call_user_func('str_pad', '', $pad['u'][0], ' ', $pad['u'][1])
                ) . "\n" .
                call_user_func(
                    'sprintf',
                    '+-%s+-%s+-%s+-%s+-%s+-%s+-%s+',
                    call_user_func('str_pad', '', $pad['n'][0], '-', $pad['n'][1]),
                    call_user_func('str_pad', '', $pad['a'][0], '-', $pad['a'][1]),
                    call_user_func('str_pad', '', $pad['s'][0], '-', $pad['s'][1]),
                    call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                    call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                    call_user_func('str_pad', '', $pad['v'][0], '-', $pad['v'][1]),
                    call_user_func('str_pad', '', $pad['u'][0], '-', $pad['u'][1])
                ) . "\n";
            $y++;
        }
        call_user_func('print_r', $___);
        die();
    }
    private static function check_addon(&$a, &$set, $addons, $ab_addons, &$events, $am = array(), $show_available_updates = false)
    {
        $cscart_version = PRODUCT_NAME . ' ' . PRODUCT_VERSION . ' ' . (PRODUCT_STATUS != '' ? (' (' . PRODUCT_STATUS . ')') : '') . (PRODUCT_BUILD != '' ? (' ' . PRODUCT_BUILD) : '');
        $action = array('status' => 'unavailable_addon', 'version' => '---', 'cscart' => $cscart_version,);
        $a_id = $a['addon_id'];
        if (!empty($ab_addons[$a_id])) {
            $a_key = $a['key'] = $ab_addons[$a_id]['key'];
            $installed = array();
            $available = array();
            $final = array();
            if (!empty($a['builds']['installed'])) $installed = $a['builds']['installed'];
            if (!empty($a['builds']['available'])) $available = $a['builds']['available'];
            if (!empty($a['builds']['final'])) $final = $a['builds']['final'];
            $a = call_user_func('array_merge', $a, $ab_addons[$a_id]['product']);
            if (empty($installed)) {
                if (empty($available)) {
                    $action['status'] = 'unavailable_addon';
                } else {
                    $action['status'] = 'install_addon';
                    $action['version'] = $available['version'];
                }
            } elseif (!empty($installed) and !empty($available)) {
                if (call_user_func('\Tygh\ABAManager::v_c', $installed['version'], $available['version'], '<')) {
                    $action['status'] = 'update_addon';
                    $action['version'] = $available['version'];
                    $show_available_updates && $events['available_updates'][] = $a_key;
                } else {
                    $action['status'] = 'wait_new_version';
                    $action['version'] = $installed['version'];
                }
            } elseif (!empty($installed) and empty($available)) {
                if ($a['status'] == 'ok') {
                    $action['status'] = 'not_tested_yet';
                    $action['version'] = $installed['version'];
                }
            }
            if ($a_key != 'ab__addons_manager' and !empty($am) and !empty($am['builds']['available'])) {
                if (call_user_func('\Tygh\ABAManager::v_c', $am['builds']['installed']['version'], $am['builds']['available']['version'], '<')) {
                    $action['ab__am'] = $am['builds']['available'];
                }
            }
            // Daniel Bypass
            // if (!empty($addons[$a_key]) and !empty($addons[$a_key]['s']) and $addons[$a_key]['s'] == 'Error') {
            //     if (Registry::get('addons.' . $a_key . '.status') == 'A' and call_user_func('defined', 'CONSOLE') != true) {
            //         call_user_func('fn_update_addon_status', $a_key, 'D', false);
            //         call_user_func('fn_set_notification', 'E', call_user_func('__', 'error'), call_user_func('__', 'ab__am.no_data.notification', array('[domain]' => \Tygh\Registry::get('config.http_host'), '[name]' => $addons[$a_key]['name'])), 'S');
            //     }
            // }
        }
        $a['action'] = $action;
        $a['subscription'] = array('status' => $a['status'], 'date' => $a['subscription_updates'],);
        unset($a['build']);
    }
    public static function g_a($a = "")
    {
        if (call_user_func('is_array', $a_ = call_user_func('db_get_hash_array', "SELECT a.addon, a.version, a.status, ad.name, ad.description FROM ?:addons as a LEFT JOIN ?:addon_descriptions as ad ON (ad.addon = a.addon) WHERE a.addon like 'ab%\_\_%' AND ad.lang_code = ?s ?p ORDER BY a.addon asc", 'addon', CART_LANGUAGE, call_user_func('strlen', $a) ? call_user_func('db_quote', ' AND a.addon = ?s ', $a) : ''))) {
            foreach ($a_ as &$_) {
                $_['v'] = (call_user_func('strlen', $_['version'])) ? $_['version'] : '--';
                $_['c'] = (call_user_func('strlen', call_user_func('\Tygh\Registry::get', 'addons.' . $_['addon'] . '.code'))) ? call_user_func('\Tygh\Registry::get', 'addons.' . $_['addon'] . '.code') : '--';
                $_['b'] = (call_user_func('strlen', call_user_func('\Tygh\Registry::get', 'addons.' . $_['addon'] . '.build'))) ? call_user_func('\Tygh\Registry::get', 'addons.' . $_['addon'] . '.build') : '--';
            }
            return $a_;
        }
        return false;
    }
    public static function exec_cmd($cmd, $is_json = true, $d = false, $a_p = false)
    {

        $p = array(
            'r' => $cmd, 'k' => \Tygh\Registry::get('addons.ab__addons_manager.code'),
            'b' => \Tygh\Registry::get('addons.ab__addons_manager.build'),
            'h' => fn_allowed_for("MULTIVENDOR") ? \Tygh\Registry::get('config.http_host') : db_get_fields("SELECT storefront FROM ?:companies WHERE status = 'A' AND storefront != '' "),
            'l' => CART_LANGUAGE,
            'pv' => PRODUCT_VERSION,
            'pe' => PRODUCT_EDITION,
            'pb' => (strlen(PRODUCT_BUILD)) ? PRODUCT_BUILD : '--',
            'a' => call_user_func('\Tygh\ABAManager::g_a'),
        );
        // $_ = call_user_func('curl_init');
        $_ = <<<EOD
        {
            "i": false,
            "d": {
                "abt__unitheme2": {
                    "a": "abt__unitheme2",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "abt__unitheme2_mv": {
                    "a": "abt__unitheme2_mv",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "abt__youpitheme": {
                    "a": "abt__youpitheme",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__addons_manager": {
                    "a": "ab__addons_manager",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__antibot": {
                    "a": "ab__antibot",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__category_banners": {
                    "a": "ab__category_banners",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__custom_h1": {
                    "a": "ab__custom_h1",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__deal_of_the_day": {
                    "a": "ab__deal_of_the_day",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__extended_comparison_wishlist": {
                    "a": "ab__extended_comparison_wishlist",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__extended_metadata": {
                    "a": "ab__extended_metadata",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__fast_navigation": {
                    "a": "ab__fast_navigation",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__geo_pages": {
                    "a": "ab__geo_pages",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__hide_product_description": {
                    "a": "ab__hide_product_description",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__images_seo": {
                    "a": "ab__images_seo",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__image_previewers": {
                    "a": "ab__image_previewers",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__intelligent_accessories": {
                    "a": "ab__intelligent_accessories",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__landing_categories": {
                    "a": "ab__landing_categories",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__lazy_load": {
                    "a": "ab__lazy_load",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__motivation_block": {
                    "a": "ab__motivation_block",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__multiple_cat_descriptions": {
                    "a": "ab__multiple_cat_descriptions",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__preload": {
                    "a": "ab__preload",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__product_sets": {
                    "a": "ab__product_sets",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__scroll_to_top": {
                    "a": "ab__scroll_to_top",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__search_motivation": {
                    "a": "ab__search_motivation",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__seo_brands": {
                    "a": "ab__seo_brands",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__seo_filters": {
                    "a": "ab__seo_filters",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__seo_for_tags": {
                    "a": "ab__seo_for_tags",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__seo_product_tabs": {
                    "a": "ab__seo_product_tabs",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__seo_reviews": {
                    "a": "ab__seo_reviews",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__short_desc_from_features": {
                    "a": "ab__short_desc_from_features",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__so_noindex_tech_pages": {
                    "a": "ab__so_noindex_tech_pages",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__stickers": {
                    "a": "ab__stickers",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__video_gallery": {
                    "a": "ab__video_gallery",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                },
                "ab__webp": {
                    "a": "ab__webp",
                    "s": "Ok",
                    "lv": "No",
                    "sd": "",
                    "ss": "I"
                }
            },
            "a": {
                "addons": {
                    "ab__addons_manager": {
                        "addon_id": 1,
                        "status": "ok",
                        "subscription_updates": 0,
                        "code": "ABM-s8T0og0cuDfKj27fiWwet",
                        "builds": {
                            "available": {
                                "build_id": "979",
                                "status": "A",
                                "version": "2.5.0",
                                "timestamp": "1639036800",
                                "ultimate": "4.10.1 – 4.15.2",
                                "multivendor": "4.10.1 – 4.15.2",
                                "limit_of_upload": "0",
                                "build": [
                                    "en",
                                    "ru"
                                ],
                                "multiple_languages": [
                                    "en",
                                    "ru"
                                ],
                                "key": "ab__addons_manager",
                                "patch_status": "N",
                                "patch_date": "0"
                            },
                            "installed": {
                                "build_id": "979",
                                "addon_id": "1",
                                "version": "2.5.0",
                                "timestamp": "1639036800",
                                "ultimate": "4.10.1 – 4.15.2",
                                "multivendor": "4.10.1 – 4.15.2"
                            },
                            "final": {
                                "build_id": "979",
                                "addon_id": "1",
                                "version": "2.5.0",
                                "timestamp": "1639036800",
                                "ultimate": "4.10.1 – 4.15.2",
                                "multivendor": "4.10.1 – 4.15.2"
                            }
                        },
                        "link": "https://cs-cart.alexbranding.com/api2/?d=BRDpzHm8ER9CmzT7Hh%2BcAMXRBFCC3muTmSEXJGblJB9OjJTfBoprQk7jt9estfHhNGx4qVYs9X6rh4o9XmJTjBtCqwhqjqQ1wxa4E2XvkbE071cbIIt2ciW1BTjMnlk4"
                    }
                }
            },
            "l": {
                "sets": {
                    "unitheme2": {
                        "addons": {
                            "53": "abt__unitheme2",
                            "17": "ab__category_banners",
                            "56": "ab__motivation_block",
                            "55": "ab__fast_navigation",
                            "44": "ab__video_gallery",
                            "7": "ab__scroll_to_top",
                            "39": "ab__search_motivation",
                            "38": "ab__landing_categories",
                            "12": "ab__hide_product_description",
                            "32": "ab__deal_of_the_day",
                            "65": "ab__stickers",
                            "77": "ab__image_previewers"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": true,
                        "name": "UniTheme2 - premium CS-Cart theme complex",
                        "description": "<p>UniTheme2 - second generation of theme UniTheme, fully 100% coded from scratch, supports CS-Cart v.4.10.1 and higher. If you are using CS-Cart  v.4.9.3 and below, then you can use&nbsp;<a href=\"https://cs-cart.alexbranding.com/en/unitheme-v1.html\" target=\"_blank\">Unitheme1</a>.</p>",
                        "url": "https://cs-cart.alexbranding.com/en/template-unitheme.html"
                    },
                    "unitheme2_mv": {
                        "addons": {
                            "53": "abt__unitheme2",
                            "59": "abt__unitheme2_mv",
                            "17": "ab__category_banners",
                            "56": "ab__motivation_block",
                            "55": "ab__fast_navigation",
                            "44": "ab__video_gallery",
                            "7": "ab__scroll_to_top",
                            "39": "ab__search_motivation",
                            "38": "ab__landing_categories",
                            "12": "ab__hide_product_description",
                            "32": "ab__deal_of_the_day",
                            "65": "ab__stickers",
                            "77": "ab__image_previewers"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": true,
                        "name": "UniTheme2 for Multi-Vendor edition of CS-Cart",
                        "description": "<p>Adds layouts, styles, templates and functional support for UniTheme2 proper work in CS-Cart Ultimate version.</p>",
                        "url": "https://cs-cart.alexbranding.com/en/abtunitheme2mv.html"
                    },
                    "youpitheme": {
                        "addons": {
                            "42": "abt__youpitheme",
                            "32": "ab__deal_of_the_day",
                            "24": "ab__multiple_cat_descriptions",
                            "44": "ab__video_gallery",
                            "7": "ab__scroll_to_top",
                            "38": "ab__landing_categories",
                            "56": "ab__motivation_block",
                            "12": "ab__hide_product_description",
                            "65": "ab__stickers"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": true,
                        "name": "YOUPI - premium theme for CS-Cart",
                        "description": "",
                        "url": "https://cs-cart.alexbranding.com/en/youpi-premium-theme.html"
                    },
                    "cross_and_up_sell": {
                        "addons": {
                            "3": "ab__intelligent_accessories",
                            "43": "ab__product_sets",
                            "69": "ab__buy_together",
                            "74": "ab__extended_comparison_wishlist"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": false,
                        "name": "Cross- & up-sell add-ons package for CS-Cart and Multi-Vendor projects",
                        "description": "",
                        "url": "https://cs-cart.alexbranding.com/en/cross-and-upsell-en.html"
                    },
                    "seo": {
                        "addons": {
                            "13": "ab__seo_filters",
                            "64": "ab__antibot",
                            "49": "ab__custom_h1",
                            "22": "ab__short_desc_from_features",
                            "24": "ab__multiple_cat_descriptions",
                            "21": "ab__so_noindex_tech_pages",
                            "18": "ab__seo_for_tags",
                            "26": "ab__advanced_sitemap",
                            "37": "ab__extended_metadata",
                            "4": "ab__images_seo",
                            "61": "ab__seo_product_tabs",
                            "62": "ab__seo_brands",
                            "76": "ab__seo_reviews",
                            "89": "ab__geo_pages"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": false,
                        "name": "SEO addons for CS-Cart (all in one package)",
                        "description": "<p>Allows you to implement the missing functionality for SEO, saving a lot of time and money. Advantage of the package - all addons from package do not require integration for collaboration (frequent problem, when using solutions from several developers).</p>",
                        "url": "https://cs-cart.alexbranding.com/en/seo-package.html"
                    },
                    "speed_up": {
                        "addons": {
                            "60": "ab__preload",
                            "64": "ab__antibot",
                            "78": "ab__webp",
                            "81": "ab__lazy_load"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": false,
                        "name": "Speed-Up add-ons package",
                        "description": "<p>Allows you to speed up page loading, reduce load, get the best score for Google PageSpeed Insights.</p>",
                        "url": "https://cs-cart.alexbranding.com/en/set-speedup.html"
                    },
                    "ukraine": {
                        "addons": {
                            "83": "ab__prro",
                            "73": "ab__payments",
                            "48": "ab__privat24",
                            "72": "ab__monobank",
                            "79": "ab__alfabank",
                            "67": "ab__ukr_cities",
                            "28": "ab__nova_poshta",
                            "68": "ab__ukr_poshta",
                            "80": "ab__justin",
                            "90": "ab__country_language",
                            "33": "ab__extended_sms_notifications",
                            "19": "ab__product_feed_export",
                            "20": "ab__product_fe01_hotline",
                            "23": "ab__product_fe02_prom_ua",
                            "36": "ab__product_fe03_nadavi",
                            "51": "ab__product_fe07_rozetka",
                            "52": "ab__product_fe08_privatmarket"
                        },
                        "updates": {
                            "periods": {
                                "1": 50,
                                "2": 75,
                                "3": 100
                            }
                        },
                        "is_theme": false,
                        "name": "CS-Cart add-ons package with integrations for Ukraine eCommerce",
                        "description": "<p>A gentleman's set of solutions that covers all the needs of an online store on CS-Cart for Ukraine</p>",
                        "url": "https://cs-cart.alexbranding.com/en/paket-moduley-cs-cart-ukraina-en.html"
                    },
                    "dynamic_ads": {
                        "addons": {
                            "19": "ab__product_feed_export",
                            "46": "ab__product_fe04_google_rm",
                            "47": "ab__product_fe05_facebook_ads",
                            "50": "ab__product_fe06_mytarget_rm"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": false,
                        "name": "Dynamic Remarketing addons package for CS-Cart (Google, Facebook, Instagram)",
                        "description": "<p>Start dynamic remarketing campaigns in Google, Facebook, Instagram with the help of addons, that automate the process.</p>",
                        "url": "https://cs-cart.alexbranding.com/en/paket-dinamicheskiy-remarketing-google-facebook-instagram-en.html"
                    },
                    "simplify_ordering": {
                        "addons": {
                            "9": "ab__quick_order_by_phone"
                        },
                        "updates": {
                            "periods": {
                                "1": 100,
                                "2": 150,
                                "3": 200
                            }
                        },
                        "is_theme": false,
                        "name": "Order Simplification addons package for CS-Cart",
                        "description": "<p>3 addons, that will increase your order conversion by adding a quick order and order without e-mail (checked by thousands of custom installations)</p>",
                        "url": "https://cs-cart.alexbranding.com/en/paket-moduley-uproschenie-zakaza-dlya-cs-cart-en.html"
                    },
                    "unitheme": {
                        "addons": {
                            "31": "abt__unitheme",
                            "2": "ab__auto_loading_products",
                            "7": "ab__scroll_to_top",
                            "15": "ab__advanced_banners",
                            "17": "ab__category_banners",
                            "32": "ab__deal_of_the_day",
                            "38": "ab__landing_categories",
                            "44": "ab__video_gallery",
                            "39": "ab__search_motivation"
                        },
                        "updates": {
                            "periods": {
                                "1": 50,
                                "2": 75,
                                "3": 100
                            }
                        },
                        "is_theme": true,
                        "is_disabled": true,
                        "name": "UniTheme - premium adaptive template for CS-Cart",
                        "description": "<p>Adaptive premium template for CS-Cart with 20+ extra features and 6 commercial addons on board. Use it, to make fast, modern and fully functional store, using CS-Cart as a platform.</p>",
                        "url": "https://cs-cart.alexbranding.com/en/unitheme-v1.html"
                    },
                    "speedup_closed": {
                        "addons": {
                            "34": "ab__speedup"
                        },
                        "updates": [],
                        "is_theme": false,
                        "is_disabled": true,
                        "name": "ab__speedup (Closed)",
                        "description": "",
                        "url": "https://cs-cart.alexbranding.com/en/abspeedup.html"
                    }
                },
                "addons": {
                    "1": {
                        "addon_id": "1",
                        "key": "ab__addons_manager",
                        "description": "Addons менеджер",
                        "product_id": "263",
                        "status": "H",
                        "type": "M",
                        "product": {
                            "description": "Manage Addons by AlexBranding. Install and check for updates, get to know our new products.",
                            "name": "AB: Addons Manager",
                            "reviews": "0",
                            "rating": null,
                            "price": "0.000000",
                            "url": "https://cs-cart.alexbranding.com/en/addons-menedzher.html",
                            "doc": "",
                            "update_instruction": ""
                        }
                    }
                }
            },
            "b": []
        }
        EOD;
        call_user_func('curl_setopt_array', $_, array(78 => 35, 13 => 35, 47 => 1, 10015 => call_user_func('http_build_query', ($a_p) ? call_user_func('array_merge', $p, $a_p) : $p), 10002 => 'https://cs-cart.alexbranding.com/api2/', 52 => 1, 64 => 0, 81 => false, 19913 => 1,));
        $res = ($is_json) ? (($d) ? call_user_func('fn_print_die', $p, call_user_func('json_decode', call_user_func('curl_exec', $_), true)) : call_user_func('json_decode', call_user_func('curl_exec', $_), true)) : (($d) ? call_user_func('fn_print_die', call_user_func('curl_exec', $_)) : call_user_func('curl_exec', $_));
        // call_user_func('curl_close', $_);
        return $res;
    }
}
