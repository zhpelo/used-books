<?php
/*
 * Plugin Name: 二手书交易市场
 * Plugin URI: https://www.wenshuoge.com/
 * Description: 这是一个专为二手书打造的交易市场
 * Version: 1.0
 * Author: 庄朋龙
 * Author URI: https://zhuangpenglong.com/
 */
require_once  'lib/class-usedbook-list.php';
//插件激活挂钩
register_activation_hook(
	__FILE__,
	'usedbooks_initializen'
);

function usedbooks_initializen() {
    //创建数据库
    global $wpdb;
    $table_name = $wpdb->prefix . "used_books";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `{$table_name}` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` int NOT NULL COMMENT '书名',
            `images` int NOT NULL COMMENT '图册',
            `weight` int NOT NULL COMMENT '重量',
            `price` int NOT NULL COMMENT '价格',
            `text` int NOT NULL COMMENT '文字',
            `in_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '入库时间',
            `out_date` datetime NULL COMMENT '出库时间'
          );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


//  注册后台管理模块  
function used_books_add_menu()
{
    add_menu_page(
        '二手书籍管理', // 菜单页面的标题
        '二手书', // 菜单页面的菜单文本
        'manage_options', // 用户需要具备的权限
        'used_books', // 菜单页面的 slug
        'used_books_page', // 菜单页面的回调函数
        'dashicons-media-document',
        6
    );

}
add_action('admin_menu', 'used_books_add_menu');


function used_books_page()
{
    $id = input('id');
    //显示书籍列表
    if (isset($_GET['action'])) {
        $message = '';
        switch ($_GET['action']) {
            case 'create':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if($ebook_id = cs_ebook_add($_POST)){
                        $message = '<div id="message" class="updated notice">
                                        <p><strong>书籍信息修改完成</strong></p>
                                        <p><a href="?page=chapters&ebook_id='.$ebook_id.'">新增章节</a> | <a href="/book/'.$ebook_id.'/">前台查看</a></p>
                                    </div>';
                    }else{
                        $message = '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
                    }
                }else{
                    used_books_edit_page();
                }
                break;
            case 'edit':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if(cs_ebook_edit($id, $_POST)){
                        $message = '<div id="message" class="updated notice">
                                        <p><strong>书籍信息修改完成</strong></p>
                                        <p><a href="?page=chapters&ebook_id='.$id.'">去编辑本书的章节 ----> </a></p>
                                    </div>';
                    }else{
                        $message = '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
                    }
                }
                require_once(CS_DIR . "views/admin/book-edit.php");
                break;
            case 'copy':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    unset($_POST['id']);
                    if($ebook_id = cs_ebook_add($_POST)){
                        $message = '<div id="message" class="updated notice">
                                        <p><strong>书籍复制完成</strong></p>
                                        <p><a href="?page=chapters&ebook_id='.$ebook_id.'">新增章节</a> | <a href="/book/'.$ebook_id.'/">前台查看</a></p>
                                    </div>';
                    }else{
                        $message = '<div id="message" class="notice notice-error"><p><strong>复制出现错误！</strong></p></div>';
                    }
                }
                require_once(CS_DIR . "views/admin/book-edit.php");
                break; 
            case 'import_epub':
                require_once(CS_DIR . "views/admin/book-import-epub.php");
                break; 
            case 'remake_cover':
                if($cover = cs_make_book_cover($id)){
                    echo "<img src=\"$cover\"/>";
                }
                break;
            case 'ban':
                exit("开始下架书籍");
                break;
            case 'make_pdf':
                if(wsg_make_pdf($id)){
                    echo "pdf文件生成 完成";
                }else{
                    echo "pdf文件生成 失败";
                }
                break;
            case 'make_epub':
                if(wsg_make_epub($id)){
                    echo "epub文件生成 完成";
                }else{
                    echo "epub文件生成 失败";
                }
                break;
        }
    } else {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">全部二手书籍</h1>
            <a href="?page=used_books&action=create" class="page-title-action">录入书籍</a>
            <?php
                $list_table = new UsedBook_List_Table();
                $list_table->prepare_items();
                $list_table->display();
            ?>
        </div>
        <?php
    }
}


function used_books_edit_page($id = null){
    if ($id) {
        $data = cs_get_ebook_item($id);
        $data->tags = implode(",", array_column(cs_get_ebook_tags($id), 'name'));
    }
?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?= $id ? "编辑数据" : "新增书籍"; ?></h1>
        <a href="?page=books" class="page-title-action">返回列表</a>
        <hr class="wp-header-end">
    
        <form method="post" enctype="multipart/form-data">
            <?php if ($id) { ?>
                <input type="hidden" name="id" value="<?= $data->id; ?>" />
            <?php } ?>
    
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="title">书名</label></th>
                        <td><input name="title" type="text" id="title" value="<?= $id ? $data->title : ""; ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="image">封面</label>
                        </th>
                        <td>
                            <input name="image" type="text" id="image" value="<?= $id ? $data->image : ""; ?>" class="regular-text">
                            <input name="books_image" type="file" id="books_image" class="regular-text">
                            
                            <br>
                            <?php 
                                if($id){
                                    if($data->image){
                                        echo "<img src =\"$data->image\" width=\"100\"/>";
                                    }else{
                                        echo "<img src =\"/uploads/book_covers/{$id}.png\" width=\"100\"/>";
                                    }
                                    
                                }
                            ?>
                        </td>
                    </tr>
    
                    <tr>
                        <th scope="row">
                            <label for="summary">Text</label>
                        </th>
                        <td>
                            <textarea name="summary" class="regular-text" id="summary" rows="10" cols="40"><?= $id ? $data->summary : ""; ?></textarea>
                        </td>
                    </tr>
    
                    
    
                    <tr>
                        <th scope="row">
                            <label for="pubdate">重量</label>
                        </th>
                        <td>
                            <input name="pubdate" type="text" id="pubdate" value="<?= $id ? $data->pubdate : ""; ?>" class="regular-text"> g
                        </td>
                    </tr>
    
                    <tr id="file_list">
                        <th scope="row">
                            <label for="status">附属文件</label>
                        </th>
                        <td>
                            <?php
                                if($id){
                                    foreach(wsg_get_books_files($id) as $file){
                                        echo "<p>
                                                <input type=\"text\" value=\"{$file->fileurl}\" class=\"regular-text\" readonly=\"true\"/>
                                                <span class=\"button action\" onClick=\"del_file({$file->id})\">删除</span>
                                            </p>";
                                    }
                                }
                            ?>
                            <p>
                                <input name="fileurl[]" type="text" class="regular-text" />
                                <span class="button button-primary" id="add_file">+ 添加</span>
                            </p>
                            <div id="more_file">
    
                            </div>
                            
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="status">状态</label>
                        </th>
                        <td>
                            <select name="status" id="status">
                                <option value="normal" <?= $id && $data->status == "normal" ? 'selected="selected"' : ""; ?>>正常</option>
                                <option value="draft" <?= $id && $data->status == "draft" ? 'selected="selected"' : ""; ?>>草稿</option>
                                <option value="hidden" <?= $id && $data->status == "hidden" ? 'selected="selected"' : ""; ?>>隐藏</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
        </form>
    
    </div>
    <script>
        function type_change() {
            var select = document.getElementById("type").value;
            if (select == "pdf") {
                jQuery("#file_list").show();
                
            } else {
                jQuery("#file_list").hide();
            }
        }
        function del_file(id) {
            alert(id);
        }
    
        jQuery("#add_file").click(function(){
            jQuery("#more_file").append('<p><input name="fileurl[]" type="text" class="regular-text" /> <span class="button action" onclick="jQuery(this).parent().remove();">删除</span></p>');
        });
    
    </script>

<?php 

}