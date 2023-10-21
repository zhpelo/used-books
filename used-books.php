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
                    if($book_id = used_books_add($_POST)){
                        echo '<div id="message" class="updated notice">
                                        <p><strong>书籍信息修改完成</strong></p>
                                        <p><a href="?page=chapters&ebook_id='.$book_id.'">新增章节</a> | <a href="/book/'.$book_id.'/">前台查看</a></p>
                                    </div>';
                    }else{
                        echo '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
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
            case 'ban':
                exit("开始下架书籍");
                break;
        }
    } else {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">全部二手书籍</h1>
            <a href="?page=used_books&action=create" class="page-title-action">录入书籍</a>
            <style>
                .column-images{
                    width: 500px;
                }
            </style>
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
        <h1 class="wp-heading-inline"><?= $id ? "编辑数据" : "二手书籍入库"; ?></h1>
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
                        <td><input name="name" type="text" id="name" value="<?= $id ? $data->name : ""; ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="image">封面</label>
                        </th>
                        <td>
                            <input name="image" type="file"  accept="image/*" class="regular-text"/>
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
    
                    <tr id="file_list">
                        <th scope="row">
                            <label for="status">相册</label>
                        </th>
                        <td>
                            <input name="images[]" type="file" class="regular-text" accept="image/*" multiple="multiple" />
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
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
        </form>
    </div>
<?php 

}


function used_books_image_upload($key){
	// 检查是否有文件上传
    if (!isset($_FILES[$key]) && $_FILES[$key]) {
		return false;
	}
	$file = $_FILES[$key];
	// 检查上传文件是否有错误
	if ($file['error'] !== UPLOAD_ERR_OK) {
		// echo '文件上传出错：' . $file['error'];
		return false;
	}
	// 设置上传目录
	$upload_dir = wp_upload_dir();
	$target_dir = $upload_dir['path'] . '/';
	// 生成唯一的文件名
	$file_name = wp_unique_filename($target_dir,  $file['name']);
	// 移动文件到目标目录
	if (!move_uploaded_file($file['tmp_name'], $target_dir . $file_name)) {
		return false;
	}
	return $upload_dir['url'] .'/'. $file_name;
}

function used_books_add(){
    global $wpdb;
    $data = [];
    $data['name'] = trim($_POST['name']);

    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'].'/used_books'.$upload_dir['subdir'] . '/';
    $url_dir = $upload_dir['baseurl'].'/used_books'.$upload_dir['subdir'] . '/';
    mkdirs($target_dir);

    $file_name = wp_unique_filename($target_dir,  $_FILES['image']['name']);

    if( move_uploaded_file( $_FILES['image']['tmp_name'], $target_dir . $file_name) ){
        
        $data['image'] = $url_dir . $file_name;
    }

    $file_array = reArrayFiles($_FILES['images']);
    $data['images'] = "";
    foreach ($file_array as $file) {
        $file_name = wp_unique_filename($target_dir,  $file['name']);
        // 移动文件到目标目录
        if (move_uploaded_file($file['tmp_name'], $target_dir . $file_name)) {
            $data['images'] .= $url_dir . $file_name.";";
        }
    }
    $data['in_date'] = current_time('mysql');
    
    $wpdb->insert("{$wpdb->prefix}used_books", $data);
    return $wpdb->insert_id;
}


function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}