<?php

class NavMenu_List extends NavMenu_Abstract_Nav
{

    /**
     * _navOptions
     *
     * @var mixed
     * @access private
     */
    private $_navOptions = null;
    private $_nav_resourse = null;
    private $_nav_menus;
    private $_current_nav;

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);

        $_nav_menus = $this->db->fetchRow($this->select()
            ->where('name = ?', 'navMenus')->limit(1));

        if (!$_nav_menus) {
            // 插入默认数据
            $_nav_menus['name'] = 'navMenus';
            $_nav_menus['value'] = json_encode(['default']);
            $this->insert($this->_nav_menus);
        }

        $this->_nav_menus = json_decode($_nav_menus['value'], true);

        $this->_current_nav = $request->get('current', $this->_nav_menus[0]);

        if (!in_array($this->_current_nav, $this->_nav_menus)) {
            throw new Typecho_Plugin_Exception('你请求的菜单不存在！');
        }

        $this->_nav_resourse = $this->db->fetchRow($this->select()
            ->where('name = ?', 'navMenuOrder')->limit(1));
        if (!$this->_nav_resourse) {
            $this->_nav_resourse['name'] = 'navMenuOrder';
            $this->_nav_resourse['value'] = [];
            foreach ($this->_nav_menus as $nav_menu) {
                $this->_nav_resourse['value'][$nav_menu] = [];
            }

            $this->_nav_resourse['value'] = json_encode($this->_nav_resourse['value']);
            $this->insert($this->_nav_resourse);
        }
        $this->_nav_resourse = json_decode($this->_nav_resourse['value']);
    }

    /**
     * @param string $menu 菜单名称
     * @param null $navOptions 菜单配置
     */
    public function navMenu($menu = 'default', $navOptions = null)
    {
        //初始化一些变量
        $this->_navOptions = Typecho_Config::factory($navOptions);
        $this->_navOptions->setDefault(array(
            'wrapTag' => 'ul',
            'wrapClass' => '',
            'wrapId' => '',
            'itemTag' => 'li',
            'itemClass' => '{has-children}menu-has-children{/has-children}',
            'item' => '<a class="{class}" href="{url}" {target}>{name} {caret}</a>',
            'linkClass' => '',
            'subMenu' => '<ul class="{class}">{content}</ul>',
            'subMenuClass' => 'sub-menu',
            'current' => 'current',
            'caret' => '+',
        ));
        if (isset($this->_nav_resourse->$menu)) {
            $menuObject = $this->_nav_resourse->$menu;
            if ($this->_navOptions->wrapTag) {
                echo '<' . $this->_navOptions->wrapTag . (empty($this->_navOptions->wrapClass) ? ' class="nav-menu"' : ' class="nav-menu ' . $this->_navOptions->wrapClass . '"') . (empty($this->_navOptions->wrapId) ? '' : ' id="' . $this->_navOptions->wrapId . '"') . '>';
                echo self::generateNavItems($menuObject);
                echo '</' . $this->_navOptions->wrapTag . '>';
            } else {
                echo self::generateNavItems($menuObject);
            }
            $this->stack = $this->_map;
        } else _e("菜单【%s】不存在", $menu);
    }

    /** 构建菜单 */
    private function generateNavItems($items, $level = 1): string
    {
        $html = '';
        $archive = Typecho_Widget::widget('Widget_Archive');
        $navOptions = $this->_navOptions;
        if (is_array($items)) {
            foreach ($items as $key => $v) {
                $item = array();
                $item['class'] = ['menu-item'];
                $item['linkClass'] = ['menu-link', $navOptions->linkClass];
                if ($navOptions->itemClass) {
                    $item['class'][] = $navOptions->itemClass;
                }

                if ($v->class) $item['class'][] = $v->class;
                $item['target'] = isset($v->target) && "_blank" == $v->target ? ' target="_blank"' : "";

                $item['name'] = $v->name;

                $isCurrent = false;
                switch ($v->type) {
                    case 'category':
                        $category = Typecho_Widget::widget('Widget_Metas_Category_List')->getCategory($v->id);
                        $item['class'][] = 'menu-category-item';
                        if ($archive->is('category', $category['slug'])) {
                            $isCurrent = true;
                        }
                        $item['url'] = $category['permalink'];
                        break;
                    case 'page':
                        $page = NavMenu_Plugin::widgetById($v->id);
                        $item['class'][] = 'menu-page-item';
                        if ($archive->is('page', $page->slug)) {
                            $isCurrent = true;
                        }
                        $item['url'] = $page->permalink;
                        break;
                    case 'internal':
                        $item['class'][] = 'menu-custom-item';
                        $item['name'] = preg_replace_callback('/\{([_a-z0-9]+)\}/i', function ($matches) {
                            return Helper::options()->{$matches[1]};
                        }, $item['name']);
                        $item['url'] = preg_replace_callback('/\{([_a-z0-9]+)\}/i', function ($matches) {
                            return Helper::options()->{$matches[1]};
                        }, $v->id);
                        if ($archive->request->getRequestUrl() == $item['url']) {
                            $isCurrent = true;
                        }
                        break;
                    case 'custom':
                        $item['class'][] = 'menu-custom-item';
                        $item['url'] = $v->id;
                        if ($archive->request->getRequestUrl() == $item['url']) {
                            $isCurrent = true;
                        }
                        break;
                }

                if ($isCurrent) {
                    $item['class'][] = $navOptions->current;
                }

                $item['caret'] = isset($v->children) && count($v->children) > 0 ? $navOptions->caret : '';

                $itemBegin = '';
                if ($navOptions->itemTag)
                    $itemBegin = '<' . $navOptions->itemTag . ' class="' . implode(" ", $item['class']) . '">';

                $itemHtml = $itemBegin . str_replace(
                        ['{url}', '{name}', '{caret}', '{target}', '{class}', '{current}'],
                        [$item['url'], $item['name'], $item['caret'], $item['target'], implode(" ", $item['linkClass']), $isCurrent ? $navOptions->current : ''],
                        $navOptions->item
                    );

                if (isset($v->children) && count($v->children) > 0) {
                    $itemHtml = preg_replace("/\{has-children\}(.+?)\{\/has-children\}/m", "$1", $itemHtml);
                } else {
                    $itemHtml = preg_replace("/\{has-children\}(.+?)\{\/has-children\}/m", "", $itemHtml);
                }

                $html .= $itemHtml;

                if (isset($v->children) && count($v->children) > 0) {
                    if (!empty($navOptions->subMenu)) {
                        $subMenuClass = $navOptions->subMenuClass . ' level-' . $level;
                        $html .= str_replace(['{class}', '{content}'], [$subMenuClass, self::generateNavItems($v->children, $level + 1)], $navOptions->subMenu);
                    } else {
                        $html .= self::generateNavItems($v->children, $level + 1);
                    }
                }
                if ($navOptions->itemTag) {
                    $html .= '</' . $navOptions->itemTag . '>';
                }
            }
        }
        return $html;
    }
}
