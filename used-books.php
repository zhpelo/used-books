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
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL COMMENT '书名',
                    `image` varchar(255) NOT NULL,
                    `images` text NOT NULL COMMENT '图册',
                    `weight` int(11) NOT NULL COMMENT '重量',
                    `price` int(11) NOT NULL COMMENT '价格',
                    `text` int(11) NOT NULL COMMENT '文字',
                    `in_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '入库时间',
                    `out_date` datetime DEFAULT NULL COMMENT '出库时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "used_orders";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `{$table_name}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单 id',
                    `user_id` int(11) NOT NULL COMMENT '用户 id',
                    `buyer_name` varchar(20) NOT NULL COMMENT '买家姓名',
                    `buyer_phone` varchar(18) NOT NULL COMMENT '买家手机号',
                    `buyer_address` varchar(255) NOT NULL COMMENT '买家地址',
                    `used_book_id` int(11) NOT NULL COMMENT '二手书id',
                    `create_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
                    `paid_date` datetime NOT NULL COMMENT '付款时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
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
    $action = input('action');
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">二手书籍</h1>
        <a href="?page=used_books&action=create" class="page-title-action">录入书籍</a>
        <a href="?page=used_books" class="page-title-action">返回列表</a>
        <style>
            .column-images{
                width: 500px;
            }
        </style>
        <?php
            switch ($action) {
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if($book_id = used_books_add($_POST)){
                            echo '<div id="message" class="updated notice"><p>二手书籍信息录入完成，<a href="/used-books/'.$book_id.'/">去前台查看</a></p></div>';
                        }else{
                            echo '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
                        }
                    }else{
                        used_books_edit_page();
                    }
                    break;
                case 'ban':
                    $id = input('id');
                    exit("开始下架书籍");
                    break;
                default:
                    $list_table = new UsedBook_List_Table();
                    $list_table->prepare_items();
                    $list_table->display();
            }
        ?>
    </div>
<?php
}


function used_books_edit_page($id = null){
    if ($id) {
        $data = cs_get_ebook_item($id);
        $data->tags = implode(",", array_column(cs_get_ebook_tags($id), 'name'));
    }
?>
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
                        <input name="pubdate" type="text" id="pubdate" value="<?= $id ? $data->pubdate : "240"; ?>" class="regular-text"> g
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
    </form>
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


function used_books_page_template($template) {
    if (is_page('used-books')) {
        $new_template = plugin_dir_path(__FILE__) . 'templates/used-books.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    if (is_page('used-orders')) {
        //判断是否登录
        if (!is_user_logged_in()) {
            //如果没有登陆的用户访问，就重定向到登陆页面
            return auth_redirect();
        }
        $new_template = plugin_dir_path(__FILE__) . 'templates/used-orders.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('page_template', 'used_books_page_template');


function used_books_list_card(){
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}used_books");

    echo '<div class="used-books-list">';
    if ($results) {
        foreach ($results as $row) {
            used_books_item_card($row);
        }
    } else {
        echo 'No used books found.';
    }
    echo '</div>';
}

function used_books_item_card($book){
?>
    <div class="item">
        <a href="/used-books/<?=$book->id;?>/">
            <img src="<?=$book->image;?>" alt="<?=$book->name;?>" title="<?=$book->name;?>">
            <h3><?=$book->name;?></h3>
        </a>
    </div>
<?php
}

function used_books_show_card($id){
    global $wpdb;
    $book = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_books` WHERE `id` = $id");
    if(!$book){
        echo "<p>未查询到此书籍，请稍后重试！</p>";
        return false;
    }
?>
    <div class="buyBox" style="display: flex;">
        <div style="flex-shrink:0;">
            <img src="<?=$book->image;?>" width="200">
        </div>
        <div style="padding: 20px;">
            <h1><?=$book->name;?></h1>
            <p>价格：<span style="color: red;font-size: xxx-large;font-style: italic;">9.9</span> 元</p>
            <p>运费：<b style="color: red;">包邮</b><span style="font-size: small;font-style: italic;">（新疆，西藏，内蒙古地区除外）</span></p>
            <div class="wp-block-buttons is-layout-flex">
                <a class="wp-block-button__link wp-element-button"  href="/used-orders/?action=buy&id=2">
                    立即购买
                </a>
            </div>
        </div>
    </div>
    <div class="gallery" style="margin-top: 2rem;">
    <img src="<?=$book->image;?>"/>
        <?php
           foreach(explode(";",$book->images) as $image){
                echo  "<img src=\"$image\"/>";
            }
        ?>

    </div>
<?php
}


function custom_rewrite_rule() {
    add_rewrite_rule('^used-books/([^/]+)/?', 'index.php?pagename=used-books&id=$matches[1]', 'top');
}
add_action('init', 'custom_rewrite_rule');


function used_books_qrcode_pay($order_id)
{
	$arr = array(
		"pid" => CS_PAY_PID,
		"type" => "alipay",
		"notify_url" => home_url()."/epay/notify/",
		"return_url" => home_url()."/epay/return/",
		"out_trade_no" => md5($order_id).'-'.$order_id,
		"name" => "购买二手书$order_id",
		"money" => '9.9',
		"sign_type" => "MD5"
	);
	$payurl= "http://7-pay.cn/submit.php?pid=".CS_PAY_PID."&type={$arr['type']}&notify_url={$arr['notify_url']}&return_url={$arr['return_url']}&out_trade_no={$arr['out_trade_no']}&name={$arr['name']}&money={$arr['money']}&sign_type={$arr['sign_type']}&sign=".cs_get_sign($arr,CS_PAY_KEY);

    echo "<script type=\"text/javascript\">window.location.href=\"$payurl\";</script>";
}



function used_books_process_order_post() {
    global $wpdb;
    $error_message = [];
    if(!isset($_POST['buyer_name'])){
        $error_message[] = "姓名格式不正确！";
    }
    if(strlen($_POST['buyer_phone']) != 11){
        $error_message[] = "手机号格式不正确！";
    }
    if(strlen($_POST['buyer_address']) < 10){
        $error_message[] = "收货地址格式不正确！";
    }
    if( $error_message){
        foreach($error_message as $msg){
            echo '<div class="error">' . $msg . '</div>';;
        }
    }else{
        $data = [
            'user_id'       => get_current_user_id(),
            'used_book_id'  => $_POST['used_book_id'],
            'buyer_name'    => $_POST['buyer_name'],
            'buyer_phone'   => $_POST['buyer_phone'],
            'buyer_address' => $_POST['buyer_address'],
            'create_date'   => current_time('mysql'),
        ];
        $wpdb->insert($wpdb->prefix.'used_orders', $data);
        $order_id = $wpdb->insert_id;
        if($order_id){
            used_books_qrcode_pay($order_id);
        }else{
            echo "<p>ERROR：订单提交失败</p>";
        }
    }
}