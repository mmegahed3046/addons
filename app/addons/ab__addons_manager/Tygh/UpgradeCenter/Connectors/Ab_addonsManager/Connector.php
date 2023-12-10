<?php



namespace Tygh\UpgradeCenter\Connectors\Ab_addonsManager;

use Tygh\Addons\SchemesManager;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\Url;
use Tygh\UpgradeCenter\Connectors\BaseAddonConnector;
use Tygh\UpgradeCenter\Connectors\IConnector;
use Tygh\ABAManager;

class Connector extends BaseAddonConnector implements IConnector
{
    //
    protected $addon_id;
    protected $addon;
    protected $manager;
    protected $s;
    protected $m;
    protected $url;
    public function __construct()
    {
        parent::__construct();
        $this->addon_id = 'ab__addons_manager';
        $this->s = 'settings';
        $this->m = 'ab__addons_manager';
        $this->u = 'http://localhost/api2/';
        $this->manager = \Tygh\ABAManager::g_a($this->m);
        $this->addon = \Tygh\ABAManager::g_a($this->addon_id);
        if (\Tygh\Registry::get('addons.' . $this->m . '.build') == 26) {
            $this->addon[$this->addon_id]['v'] = $this->addon[$this->addon_id]['version'];
            $this->addon[$this->addon_id]['c'] = (strlen(\Tygh\Registry::get('addons.' . $this->addon_id . '.code'))) ? \Tygh\Registry::get('addons.' . $this->addon_id . '.code') : '--';
            $this->addon[$this->addon_id]['b'] = (strlen(\Tygh\Registry::get('addons.' . $this->addon_id . '.build'))) ? \Tygh\Registry::get('addons.' . $this->addon_id . '.build') : '--';
            $this->manager[$this->m]['v'] = $this->manager[$this->m]['version'];
            $this->manager[$this->m]['c'] = (strlen(\Tygh\Registry::get('addons.' . $this->m . '.code'))) ? \Tygh\Registry::get('addons.' . $this->m . '.code') : '--';
            $this->manager[$this->m]['b'] = (strlen(\Tygh\Registry::get('addons.' . $this->m . '.build'))) ? \Tygh\Registry::get('addons.' . $this->m . '.build') : '--';
        }
    }
    public function getConnectionData()
    {
        Http::$logging = false;
        return array(
            'method' => 'post', 'url' => $this->u,
            'data' => array(
                'r' => 'uc.cs', 'k' => $this->manager[$this->m]['c'],
                'b' => $this->manager[$this->m]['b'],
                'h' => fn_allowed_for('MULTIVENDOR') ? \Tygh\Registry::get('config.http_host') : db_get_fields("SELECT storefront FROM ?:companies WHERE status = 'A' AND storefront != ''"), 'l' => CART_LANGUAGE, 'pv' => PRODUCT_VERSION, 'pe' => PRODUCT_EDITION, 'pb' => PRODUCT_BUILD, 'a' => $this->addon,
            ),
        );
    }
    public function processServerResponse($response, $show_upgrade_notice)
    {
        $pd = array();
        $rd = json_decode($response, true);
        if (!empty($rd) and !empty($rd['file'])) {
            $pd = $rd;
            $pd['name'] = $this->addon[$this->addon_id]['name'] . $pd['name'];
            if ($show_upgrade_notice) {
                fn_set_notification('W', __('notice'), __('text_upgrade_available', array(
                    '[product]' => '<b>' . $pd['name'] . '</b>',
                    '[link]' => fn_url('upgrade_center.manage')
                )), 'S');
            }
        }
        return $pd;
    }
    public function downloadPackage($schema, $package_path)
    {
        $r = array(false, __('text_uc_cant_download_package'));
        $schema['type'] = $schema['id'];
        if (!empty($schema['key'])) {
            Http::$logging = false;
            $res = fn_put_contents($package_path, \Tygh\Http::post($this->u, array('r' => 'uc.ga', 'k' => $schema['key'],), array('timeout' => 15,)));
            if (!$res || strlen($error = \Tygh\Http::getError())) {
                fn_rm($package_path);
            } else {
                fn_put_contents(\Tygh\Registry::get('config.dir.upgrade') . 'packages/' . $schema['id'] . '/schema.json', json_encode($schema));
                $r = array(true, '');
            }
        }
        return $r;
    }
    public function onSuccessPackageInstall($content_schema, $information_schema)
    {
        parent::onSuccessPackageInstall($content_schema, $information_schema);
        $s_id = db_get_field("SELECT section_id FROM ?:settings_sections WHERE name = ?s AND type = 'ADDON'", $this->addon_id);
        if ($s_id) {
            $st_id = db_get_field('SELECT section_id FROM ?:settings_sections WHERE parent_id = ?i AND name = ?s', $s_id, $this->s);
            if ($st_id) {
                $b = db_get_field('SELECT value FROM ?:settings_objects WHERE section_id = ?i AND section_tab_id = ?i AND name = ?s', $s_id, $st_id, 'build');
                $b and db_query('UPDATE ?:settings_objects SET value = ?s WHERE section_id = ?i AND section_tab_id = ?i AND name = ?s', $information_schema['build'], $s_id, $st_id, 'build');
            }
        }
    }
}
