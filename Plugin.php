<?php

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * Typecho 导航菜单插件
 * 
 * @package NavMenu
 * @author Ryan, merdan
 * @version 1.0.1
 * @link https://doufu.ru
 */
class NavMenu_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 启用插件方法,如果启用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        // 初始化数据库
        $db = Typecho_Db::get();
        $options = Typecho_Widget::widget('Widget_Options');
        $navMenus = $db->fetchRow($db->select()->from('table.options')->where('name = ? and user = ?', 'navMenus', 0));
        if (empty($navMenus)) {
            $struct = array(
                'name' => 'navMenus',
                'user' => 0,
                'value' => '["default"]',
            );
            $db->query($db->insert('table.options')->rows($struct));
        }
        $navMenuOrder = $db->fetchRow($db->select()->from('table.options')->where('name = ? and user = ?', 'navMenuOrder', 0));
        if (empty($navMenuOrder)) {
            $struct = array(
                'name' => 'navMenuOrder',
                'user' => 0,
                'value' => '{"default":[{"type":"internal","name":"\u9996\u9875","id":"{siteUrl}","class":"","target":"","children":[]}]}',
            );
            $db->query($db->insert('table.options')->rows($struct));
        }
        Helper::addPanel(3, 'NavMenu/panel/nav-menus.php', _t('菜单'), NULL, 'administrator');
        Helper::addAction('nav-edit', 'NavMenu_Edit');
        Typecho_Plugin::factory('Widget_Archive')->___navbar = ['NavMenu_Plugin', 'navbar'];
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        Helper::removeAction('nav-edit');
        Helper::removePanel(3, 'NavMenu/panel/nav-menus.php');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function isRtl()
    {
        $options = Helper::options();
        return $options->lang == "ug_CN";
    }

    public static function header_scripts($header)
    {

        $panelUrl = Helper::options()->pluginUrl . '/NavMenu/panel';
        if (self::isRtl()) {
            echo $header, '<link rel="stylesheet" href="' . $panelUrl . '/css/nav-menu-rtl.css"/>';
        } else {
            echo $header, '<link rel="stylesheet" href="' . $panelUrl . '/css/nav-menu.css"/>';
        }
    }

    public static function navbar($archive, $menu = 'default', $navOptions = NULL)
    {
        Typecho_Widget::widget('NavMenu_List')->navMenu($menu, $navOptions);
    }
}
