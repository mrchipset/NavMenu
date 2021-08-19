<?php

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * Typecho 导航菜单插件
 *
 * @package NavMenu
 * @author Ryan, merdan
 * @version 1.0.3
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
     * @throws Typecho_Db_Exception|Typecho_Exception
     */
    public static function activate()
    {
        // 初始化数据库
        $db = Typecho_Db::get();
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
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Db_Exception|Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        if (Helper::options()->plugin('NavMenu')->isDrop == 1) {
            $db = Typecho_Db::get();
            $db->query($db->delete('table.options')->where('table.options.name = ? and table.options.user = ?', 'navMenus', 0));
            $db->query($db->delete('table.options')->where('table.options.name = ? and table.options.user = ?', 'navMenuOrder', 0));
        }
        Helper::removeAction('nav-edit');
        Helper::removePanel(3, 'NavMenu/panel/nav-menus.php');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $edit = new Typecho_Widget_Helper_Form_Element_Radio(
            'isDrop',
            array('1' => '删除', '0' => '不删除'), '0',
            '彻底卸载(<b style="color:red">请慎重选择</b>)',
            '请选择是否在禁用插件时，删除数据表');
        $form->addInput($edit);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 判断是否从右到左
     * @return bool
     */
    public static function isRtl()
    {
        $options = Helper::options();
        return in_array($options->lang, ["ug_CN"]);
    }

    /**
     * 输出 CSS
     * @param $header
     */
    public static function header_scripts($header)
    {

        $panelUrl = Helper::options()->pluginUrl . '/NavMenu/panel';
        if (self::isRtl()) {
            echo $header, '<link rel="stylesheet" href="' . $panelUrl . '/css/nav-menu-rtl.css"/>';
        } else {
            echo $header, '<link rel="stylesheet" href="' . $panelUrl . '/css/nav-menu.css"/>';
        }
    }

    /**
     * 根据 cid 获取 Widget
     * @param $id
     * @return Widget_Abstract_Contents
     * @throws Typecho_Db_Exception
     */
    public static function widgetById($id)
    {
        $className = "Widget_Abstract_Contents";
        $db = Typecho_Db::get();
        $widget = new $className(Typecho_Request::getInstance(), Typecho_Widget_Helper_Empty::getInstance(), null);

        $db->fetchRow(
            $widget->select()->where("cid = ?", $id)->limit(1),
            array($widget, 'push')
        );
        return $widget;
    }

    /**
     * 对象转数组
     * @param $obj
     * @return array
     */
    public static function objectToArray($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr = [];
        foreach ($_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? self::objectToArray($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
}
