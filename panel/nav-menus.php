<?php
Typecho_Plugin::factory('admin/header.php')->header = array("NavMenu_Plugin", "header_scripts");
include 'header.php';
include 'menu.php';
Typecho_Widget::widget('Widget_Options')->to($options);
Typecho_Widget::widget('Widget_Metas_Tag_Admin')->to($tags);
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main typecho-post-area">
            <div id="edit-secondary" class="col-mb-12 col-tb-4" role="complementary">
                <div class="typecho-table-wrap mb-20">
                    <h2 class="panel-title"><?php _e("新增菜单"); ?></h2>
                    <p><?php _e("因数据库有关，菜单名称建议用英文名字"); ?></p>
                    <?php Typecho_Widget::widget('NavMenu_Edit')->menuForm()->render(); ?>
                </div>
                <div class="typecho-table-wrap" id="menu-list">
                    <h2 class="panel-title"><?php _e("菜单项"); ?></h2>
                    <?php ?>
                    <ul id="menu-item-cat" class="typecho-option-tabs clearfix">
                        <li class="col-mb-6 col-wd-3 active"><a href="#category"><?php _e('分类') ?></a></li>
                        <li class="col-mb-6 col-wd-3"><a href="#page" id="tab-files-btn"><?php _e('独立页面') ?></a></li>
                        <li class="col-mb-6 col-wd-3"><a href="#internal" id="tab-files-btn"><?php _e('内置链接') ?></a></li>
                        <li class="col-mb-6 col-wd-3"><a href="#custom" id="tab-files-btn"><?php _e('自定义') ?></a></li>
                    </ul>
                    <div id="category" class="tab-content">
                        <section class="typecho-post-option category-option">
                            <?php Typecho_Widget::widget('Widget_Metas_Category_List')->to($category); ?>
                            <ul>
                                <?php while ($category->next()) : ?>
                                    <li><?php echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $category->levels); ?><input type="checkbox" name="cat" id="category-<?php $category->mid(); ?>" value="<?php $category->mid(); ?>" data-type="cat" data-name="<?php $category->name(); ?>" data-url="<?php $category->permalink() ?>" />
                                        <label for="category-<?php $category->mid(); ?>"><?php $category->name(); ?></label>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </section>
                    </div>
                    <div id="page" class="tab-content hidden">
                        <section class="typecho-post-option category-option">
                            <ul>
                                <?php Typecho_Widget::widget('Widget_Contents_Page_List')->to($pages); ?>
                                <?php while ($pages->next()) : ?>
                                    <li><input type="checkbox" name="page" id="page-<?php $pages->cid(); ?>" value="<?php $pages->cid(); ?>" data-type="page" data-name="<?php $pages->title(); ?>" data-url="<?php $pages->permalink(); ?>" />
                                        <label for="page-<?php $pages->cid(); ?>"><?php $pages->title(); ?></label>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </section>
                    </div>
                    <div id="internal" class="tab-content hidden">
                        <section class="typecho-post-option category-option">
                            <ul>
                                <li><input type="checkbox" name="index" id="index" value="{siteUrl}" data-type="custom" data-name="<?php _e("首页"); ?>" data-url="{siteUrl}" />
                                    <label for="index"><?php _e("首页"); ?></label>
                                </li>
                                <li><input type="checkbox" name="admin" id="admin" value="{adminUrl}" data-type="custom" data-name="<?php _e("后台"); ?>" data-url="{adminUrl}" />
                                    <label for="index"><?php _e("后台"); ?></label>
                                </li>
                            </ul>
                        </section>
                    </div>
                    <div id="custom" class="tab-content hidden">
                        <section class="typecho-post-option">
                            <label for="order" class="typecho-label"><?php _e('链接名称') ?></label>
                            <p><input type="text" id="link_name" name="link_name" value="" class="w-100"></p>
                        </section>
                        <section class="typecho-post-option">
                            <label for="order" class="typecho-label"><?php _e('链接地址') ?></label>
                            <p><input type="text" id="link_url" name="link_url" value="" placeholder="http://" class="w-100"></p>
                        </section>
                    </div>
                    <button class="btn primary" id="add_to_menu" data-type="#category"><?php _e('添加') ?></button>
                </div>
            </div>
            <div class="col-mb-12 col-tb-8" role="main">
                <div class="col-mb-12 typecho-list">
                    <div class="typecho-table-wrap">
                        <h2><?php _e('菜单') ?></h2>
                        <p><?php _e('请使用左侧的分类, 页面及自定义链接功能添加菜单项至此。点击每一项后面的箭头可以看到该菜单项的设置.') ?></p>
                        <ul class="typecho-option-tabs clearfix ">
                            <?php Typecho_Widget::widget('NavMenu_Edit')->menuList(); ?>
                        </ul>
                    </div>
                    <div class="typecho-table-wrap">
                        <h2><?php _e('菜单结构') ?></h2>
                        <p><?php _e('请使用左侧的分类, 页面及自定义链接功能添加菜单项至此。点击每一项后面的箭头可以看到该菜单项的设置.') ?></p>
                        <ul id="selectedItems">
                            <?php Typecho_Widget::widget('NavMenu_Edit')->generateMenuList(); ?>
                        </ul>
                        <?php Typecho_Widget::widget('NavMenu_Edit')->form()->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'nav-menus-js.php';
?>

<?php include 'footer.php'; ?>