<?php



use Tygh\Registry;
use Tygh\Languages\Languages;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
function ab_____($_)
{
    $__ = '';
    for ($____ = 0; $____ < strlen($_); $____++) {
        $___ = ord($_[$____]);
        $__ .= chr(--$___);
    }
    return $__;
}
if (AREA == 'A') {
    call_user_func('\Tygh\Registry::registerCache', 'settings_abam', 86400, call_user_func('\Tygh\Registry::cacheLevel', 'time'));
}
function fn_ab__am_install()
{
    fn_ab__am_install_add_ab_quick_menu();
    fn_ab__ab_migrate_v250_v240();
}
function fn_ab__ab_migrate_v250_v240()
{
    db_query("CREATE TABLE IF NOT EXISTS ?:ab__am_tooltips (
dispatch varchar(100) NOT NULL,
addon varchar(100) NOT NULL,
version_min varchar(10) NOT NULL,
version_max varchar(10) NOT NULL,
item varchar(100) NOT NULL,
item_data mediumtext NOT NULL,
KEY dispatch_addon (dispatch,addon)
) DEFAULT CHARSET=utf8;");
}
function fn_ab__am_install_add_ab_quick_menu()
{
    $auth = Tygh::$app['session']['auth'];
    $isset_ab_menu = db_get_field('SELECT menu_id FROM ?:quick_menu WHERE url = ?s AND user_id = ?i', 'ab__am.addons', $auth['user_id']);
    if (!$isset_ab_menu) {
        $data = [
            'user_id' => $auth['user_id'],
            'url' => '',
            'parent_id' => 0,
            'position' => -100,
        ];
        $section_id = db_query('INSERT INTO ?:quick_menu ?e', $data);
        $data['object_holder'] = 'quick_menu';
        $data['object_id'] = $section_id;
        foreach (Languages::getAll() as $data['lang_code'] => $v) {
            $data['description'] = __('ab__addons', [], $data['lang_code']);
            db_query('INSERT INTO ?:common_descriptions ?e', $data);
        }
        $data = [
            'user_id' => $auth['user_id'],
            'url' => 'ab__am.addons', 'parent_id' => $section_id,
            'position' => -100,
        ];
        $menu_id = db_query('INSERT INTO ?:quick_menu ?e', $data);
        $data['object_holder'] = 'quick_menu';
        $data['object_id'] = $menu_id;
        foreach (Languages::getAll() as $data['lang_code'] => $v) {
            $data['description'] = __('ab__addons', [], $data['lang_code']);
            db_query('INSERT INTO ?:common_descriptions ?e', $data);
        }
        call_user_func('fn_clear_cache', 'all');
        call_user_func('fn_clear_cache', 'static');
        call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_static'));
        call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_misc'));
        call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_templates'));
        call_user_func('fn_rm', call_user_func('\Tygh\Registry::get', 'config.dir.cache_registry'));
    }
}
function fn_ab__am_get_menu($addon)
{
    $list = [];
    $schema = fn_get_schema('menu', 'menu');
    if (!empty($schema['central']['ab__addons']['items'][$addon]['subitems'])) {
        foreach ($schema['central']['ab__addons']['items'][$addon]['subitems'] as $k => $v) {
            $list[] = [
                'text' => __($k), 'href' => $v['href'],
            ];
        }
    }
    return $list;
}
function fn_ab__am_get_channels()
{
    return fn_get_schema('ab__addons_manager', 'channels');
}
if (!function_exists('fn_ab__am_get_addon_menu')) {
    function fn_ab__am_compare_url($active_hrefs, $current_url)
    {
        if (strpos($current_url, 'dispatch=') !== false) {
            list(, $current_url) = explode('dispatch=', $current_url);
        }
        $current_url_array = (array) explode('&', str_replace('?', '&', $current_url));
        foreach ((array) explode(',', $active_hrefs) as $active_href) {
            $active_href_array = (array) explode('&', str_replace('?', '&', $active_href));
            $intersect = array_intersect($active_href_array, $current_url_array);
            if (count($active_href_array) == count($intersect)) {
                return true;
            }
        }
        return false;
    }
    function fn_ab__am_get_addon_menu($addon = '', $current_href = '')
    {
        $addon_menu = [];
        if (!empty($addon)) {
            $menu = call_user_func('fn_get_schema', 'menu', 'menu');
            if (!empty($menu['central']['ab__addons']['items'][$addon]['subitems'])) {
                $addon_menu = $menu['central']['ab__addons']['items'][$addon]['subitems'];
                uasort($addon_menu, function ($a, $b) {
                    return ($a['position'] < $b['position']) ? -1 : 1;
                });
                $current_url = !empty($current_href) ? $current_href : Registry::get('config.current_url');
                if (!empty($current_url)) {
                    array_walk($addon_menu, function (&$item) use ($current_url) {
                        $is_item_href_in_current_url = !empty($item['href']) ? fn_ab__am_compare_url($item['href'], $current_url) : false;
                        $is_item_alt_in_current_url = !empty($item['alt']) ? fn_ab__am_compare_url($item['alt'], $current_url) : false;
                        if ($is_item_href_in_current_url || $is_item_alt_in_current_url) {
                            $item['active'] = 'Y';
                        }
                        if (!empty($item['attrs']['href'])) {
                            $item['attrs'] = $item['attrs']['href'];
                        }
                    });
                }
            }
        }
        return $addon_menu;
    }
}
function fn_ab__addons_manager_dispatch_assign_template()
{
    $device = fn_ab__am_get_device_type();
    Registry::set('settings.ab__device', $device);
    Registry::set('settings.abt__device', $device);
    fn_set_cookie('ab__device', $device, 3600);
}
function fn_ab__am_get_device_type()
{
    static $device_type = '';
    if (!empty($device_type)) {
        return $device_type;
    }
    if (defined('CONSOLE') || !isset($_SERVER['HTTP_USER_AGENT']) || !isset($_SERVER['HTTP_ACCEPT'])) {
        $device_type = 'desktop';
        return $device_type;
    }
    if (empty($device_type)) {
        $tablet_browser = 0;
        $mobile_browser = 0;
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $mobile_agents = [
                'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi',
                'avan', 'benq', 'bird', 'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-',
                'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi',
                'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp',
                'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki',
                'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage',
                'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-',
                'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
                'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi',
                'wapp', 'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-',
            ];
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/', $http_user_agent)) {
                $tablet_browser++;
            }
            if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/', $http_user_agent)) {
                $mobile_browser++;
            }
            if (in_array(substr($http_user_agent, 0, 4), $mobile_agents)) {
                $mobile_browser++;
            }
            if (strpos($http_user_agent, 'opera mini') > 0) {
                $mobile_browser++;
                $stock_ua = isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : '');
                if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/', strtolower($stock_ua))) {
                    $tablet_browser++;
                }
            }
        }
        if (!empty($_SERVER['HTTP_ACCEPT'])) {
            if (
                strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0
                || isset($_SERVER['HTTP_X_WAP_PROFILE'])
                || isset($_SERVER['HTTP_PROFILE'])
            ) {
                $mobile_browser++;
            }
        }
        $device_type = ($tablet_browser ? 'tablet' : ($mobile_browser ? 'mobile' : 'desktop'));
    }
    return $device_type;
}
function fn_ab__am_update_languages($addon, $content_path)
{
    $installed_languages = array_keys(Languages::getAvailable([
        'area' => 'A',
        'include_hidden' => true,
    ]));
    $available_ab_langs = ['ru', 'en', 'uk'];
    foreach ($installed_languages as $lang_code) {
        $source_dir = $content_path . 'ab/package/var/langs/';
        $destin_dir = Registry::get('config.dir.lang_packs');
        $po = "{$lang_code}/addons/{$addon}.po";
        if (in_array($lang_code, $available_ab_langs) && file_exists($source_dir . $po)) {
            fn_copy($source_dir . $po, $destin_dir . $po);
            Languages::installLanguagePack($destin_dir . $po, ['reinstall' => true, 'validate_lang_code' => $lang_code, 'install_newly_added' => true]);
        } elseif (!in_array($lang_code, $available_ab_langs) && file_exists($source_dir . "en/addons/{$addon}.po")) {
            Languages::installLanguagePack($source_dir . "en/addons/{$addon}.po", ['reinstall' => true, 'force_lang_code' => $lang_code, 'install_newly_added' => true]);
        }
    }
}
