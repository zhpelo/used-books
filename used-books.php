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
                    `order_money` varchar(9) NOT NULL COMMENT '价格',
                    `text` text NOT NULL COMMENT '文字',
                    `sales` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
                    `in_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '入库时间',
                    `out_date` datetime DEFAULT NULL COMMENT '出库时间',
                    `status` tinyint(1) unsigned DEFAULT '1' COMMENT '商品状态',
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
                    `buyer_area` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '买家地区',
                    `buyer_area_id` int(8) NOT NULL COMMENT '买家地区id',
                    `buyer_address` varchar(255) NOT NULL COMMENT '买家地址',
                    `used_book_id` int(11) NOT NULL COMMENT '二手书id',
                    `order_money` varchar(9) NOT NULL COMMENT '订单价格',
                    `buyer_notes` varchar(120) NOT NULL COMMENT '买家备注',
                    `express_company` varchar(60) DEFAULT NULL COMMENT '快递公司',
                    `express_number` varchar(60) DEFAULT NULL COMMENT '快递号码',
                    `create_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
                    `paid_date` datetime DEFAULT NULL COMMENT '付款时间',
                    `delivery_date` datetime DEFAULT NULL COMMENT '发货时间',
                    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}

function custom_rewrite_rule() {
    add_rewrite_rule('^used-books/([0-9]+)/?$', 'index.php?pagename=used-books&id=$matches[1]', 'top');
}
add_action('init', 'custom_rewrite_rule');

//  注册后台管理模块  
function used_books_add_menu()
{
    add_menu_page(
        '二手书籍管理', // 菜单页面的标题
        '二手书籍', // 菜单页面的菜单文本
        'manage_options', // 用户需要具备的权限
        'used_books', // 菜单页面的 slug
        'used_books_page', // 菜单页面的回调函数
        'dashicons-media-document',
        6
    );

    add_submenu_page(
        'used_books', // 父菜单标识
        '二手书籍订单管理', // 页面标题
        '订单管理', // 菜单标题
        'manage_options', // 用户权限
        'used_orders', // 子菜单标识
        'used_orders_page' // 页面回调函数
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
        <hr class="wp-header-end">
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
                case 'edit':
                    $book_id = input('id');
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if(used_books_edit($_POST)){
                            echo '<div id="message" class="updated notice"><p>二手书籍修改完成，<a href="/used-books/'.$book_id.'/">去前台查看</a></p></div>';
                        }else{
                            echo '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
                        }
                    }else{
                        used_books_edit_page($book_id);
                    }
                    break;
                default:
                    echo '<form method="post">';
                    $list_table = new UsedBook_List_Table();
                    $list_table->prepare_items();
                    $list_table->search_box('search', 'search_id');
                    $list_table->display();
                    echo '</form>';
            }
        ?>
    </div>
<?php
}


function used_books_edit_page($id = null){
    if ($id) {
        $data = used_books_get_book($id);
    }
?>
    <form method="post" enctype="multipart/form-data">
        <?php if ($id) { ?>
            <input type="hidden" name="id" value="<?= $data->id; ?>" />
        <?php } ?>

        <table class="form-table">
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
                        <label for="images">相册</label>
                    </th>
                    <td>
                        <input name="images[]" type="file" class="regular-text" accept="image/*" multiple="multiple" />
                        <br>
                        <?php 
                            if($id){
                                foreach(array_filter( explode(";",$data->images) ) as $image){
                                    echo "<img src=\"".used_books_cdn_image($image,"sm-square") ."\" width=\"100\"/>";
                                }
                            }
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="weight">重量</label>
                    </th>
                    <td>
                        <input name="weight" type="number" id="weight" value="<?= $id ? $data->weight : "350"; ?>" class="regular-text"> g
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="order_money">售价</label>
                    </th>
                    <td>
                        <input name="order_money" type="text" id="order_money" value="<?= $id ? $data->order_money : "9.9"; ?>" class="regular-text"> 元
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="text">Text</label>
                    </th>
                    <td>
                    
                        <textarea name="text" id="text" rows="5" cols="44"></textarea>
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

function used_books_add($post){
    global $wpdb;
    $data = [
        'name'      => trim($post['name']),
        'image'     => used_books_updaload('image'),
        'images'    => used_books_updaload('images'),
        'weight'    => trim($post['weight']),
        'order_money'     => trim($post['order_money']),
        'text'      => trim($post['text']),
        'in_date'   => current_time('mysql'),
    ];
    $wpdb->insert("{$wpdb->prefix}used_books", $data);
    return $wpdb->insert_id;
}

function used_books_edit($post){
    global $wpdb;
    $new_data = [
        'name'      => trim($post['name']),
        // 'image'     => used_books_updaload('image'),
        // 'images'    => $post[''].used_books_updaload('images'),
        'weight'    => trim($post['weight']),
        'order_money'     => trim($post['order_money']),
        'text'      => trim($post['text']),
    ];
    return $wpdb->update("{$wpdb->prefix}used_books", $new_data, ['id' => $post['id']]);
}

function used_books_updaload($key = "image"){
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'].'/used_books'.$upload_dir['subdir'] . '/';
    $url_dir = $upload_dir['baseurl'].'/used_books'.$upload_dir['subdir'] . '/';
    mkdirs($target_dir);
    if(is_string($_FILES[$key]['name'])){
        $file_name = wp_unique_filename($target_dir,  $_FILES[$key]['name']);
        if( move_uploaded_file( $_FILES[$key]['tmp_name'], $target_dir . $file_name) ){ 
            return $url_dir.$file_name;
        }else{
            return false;
        }
    } elseif( is_array( $_FILES[$key]['name']) ){
        $file_array = reArrayFiles($_FILES[$key]);
        $images = "";
        foreach ($file_array as $file) {
            $file_name = wp_unique_filename($target_dir,  $file['name']);
            // 移动文件到目标目录
            if (move_uploaded_file($file['tmp_name'], $target_dir . $file_name)) {
                $images .= $url_dir . $file_name.";";
            }
        }
        return $images;
    }
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
    $per_page = 20;
    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;
    $start_number = ($paged - 1) * $per_page;


    $WHERE = "WHERE `out_date` IS NULL";

    if(isset($_GET['q']) && $_GET['q'] != ''){
        $keyword = esc_sql($_GET['q']);
        $WHERE .= " AND `name` LIKE '%{$keyword}%' ";
    }

    $items_count = $wpdb->get_var("SELECT count(*)  FROM {$wpdb->prefix}used_books $WHERE");
    $total_page = ceil($items_count / $per_page);
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}used_books $WHERE ORDER BY id DESC LIMIT {$start_number},{$per_page}");
    
    if ($results) {
        echo '<div class="used-books-list">';
        foreach ($results as $row) {
            used_books_item_card($row);
        }
        echo '</div>';
    } else {
        echo '<p style="text-align: center;margin: 80px 0;">没有找到您需要的二手书籍！</p>';
    }
    
    echo paginate_links(array(
        'current' => $paged,
        'total' => $total_page
    ));
}

function used_books_item_card($book){
?>
    <div class="item">
        <a href="/used-books/<?=$book->id;?>/">
        
            <img src="<?= str_replace("https://www.wenshuoge.com","https://wenshuoge.oss-accelerate.aliyuncs.com",$book->image) ;?>?x-oss-process=style/w300h400" alt="<?=$book->name;?>" title="<?=$book->name;?>">
            <h3><?=$book->name;?></h3>
        </a>
    </div>
<?php
}

function used_books_show_card($book){
?>
    <div class="buyBox">
        <div class="gallery">
            <img src="<?=str_replace("https://www.wenshuoge.com","https://wenshuoge.oss-accelerate.aliyuncs.com",$book->image);?>?x-oss-process=style/w600h800">
        </div>
        <div class="summary">
            <h1 class="title"><?=$book->name;?></h1>
            <p class="point"><?=(int)$book->price <= 10 ?"新人首单仅4.9元，包邮到家！":"全网低价好书，一件也包邮！";?></p>
            <ul>
                <li>
                    <b>价&nbsp;&nbsp;&nbsp;&nbsp;格</b>：<span style="color: red;font-size: 35px;
    line-height: 35px;font-style: italic;"><?=used_books_calculate_price($book->price);?></span> 元
                </li>
                <li>
                    <b>成&nbsp;&nbsp;&nbsp;&nbsp;色</b>：全新
                </li>
                <li>
                    <b>运&nbsp;&nbsp;&nbsp;&nbsp;费</b>：包邮
                </li>
                <li>
                    <b>重&nbsp;&nbsp;&nbsp;&nbsp;量</b>：<?=$book->weight;?> g
                </li>
                <li>
                    <b>限&nbsp;&nbsp;&nbsp;&nbsp;制</b>：新疆，西藏，内蒙古地区不发货！
                </li>
                <li>
                    <b>发货地</b>：浙江省 义乌市
                </li>
                <li>
                    <b>时&nbsp;&nbsp;&nbsp;&nbsp;效</b>：下单后 12 小时内发货！
                </li>
            </ul>
            <div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" href="/used-orders/?action=buy&id=<?=$book->id;?>" style="background-color: red;">
                
                    <svg t="1698762558636" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="7094" width="20" height="20"><path d="M866.24 291.2a32 32 0 0 0-32-30.4H704v-7.36a207.36 207.36 0 0 0-54.72-136 189.44 189.44 0 0 0-139.2-51.84 185.92 185.92 0 0 0-139.84 52.48A197.76 197.76 0 0 0 320 249.28v11.52H189.44a32 32 0 0 0-32 30.4L128 924.8a32 32 0 0 0 8.96 23.68A32 32 0 0 0 160 960h704a32 32 0 0 0 23.04-9.92 32 32 0 0 0 8.96-25.28zM384 248.32a131.52 131.52 0 0 1 32-86.08 124.8 124.8 0 0 1 93.44-32A120.64 120.64 0 0 1 640 253.44v7.36h-256zM193.6 896l26.56-569.6H320v92.8h64v-92.8h256v92.8h64v-92.8h99.84L832 896z" p-id="7095" fill="#ffffff"></path></svg>
                    
                    &nbsp;&nbsp;立即购买</a>
                
                </div>
                
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" style="background-color: white; color:black" href="/contact-us/">
                    <svg t="1698762234706" class="icon" viewBox="0 0 1242 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1582" width="20" height="20"><path d="M276.743688 31.082747C186.540651 64.041687 104.812048 125.270062 54.361358 208.677492 8.606987 283.867611-11.893734 347.488616 10.231619 433.531224 32.369608 529.051077 99.026894 610.711245 178.527378 664.192112 163.562333 708.268342 158.599331 746.176838 153.241979 793.661668 213.726318 769.97044 236.727901 752.630478 286.778456 725.702122 347.169339 745.28871 428.587568 754.515188 491.973145 752.448047 424.470537 427.943582 695.89773 324.409217 893.269788 329.56092 865.623248 208.677497 796.986424 118.654356 700.75188 64.648162 573.142548-8.2271 414.082616-19.21199 276.743688 31.082747ZM360.220315 263.951829C351.85832 304.755434 297.895893 324.439586 266.378822 297.080385 229.885788 270.971544 240.087502 206.129635 283.035409 193.107378 325.063457 176.651653 372.868378 219.964023 360.220315 263.951829L360.220315 263.951829ZM664.517052 251.807704C664.874055 298.842577 603.140014 328.155857 568.407425 296.214323 532.389618 270.084732 542.599953 206.406934 584.893106 193.173093 622.711803 178.087803 667.444489 210.451949 664.517052 251.807704L664.517052 251.807704Z" fill="#30C730" p-id="1583"></path><path d="M1068.665237 401.293428C950.759528 344.063296 796.155145 340.080632 670.146638 401.29343 586.791846 441.785774 530.910016 508.798605 511.412699 601.876298 495.367042 670.357375 511.943042 746.322966 547.738475 806.091779 601.106738 895.778572 671.0012 944.89232 771.252416 963.294669 843.857258 978.25156 934.764331 970.486807 1001.696707 949.896938 1043.898815 966.595443 1090.349546 1002.543127 1131.305913 1022.439486 1120.797806 986.53707 1109.586126 950.777341 1097.45152 915.382722 1143.316357 882.161971 1185.133568 840.933997 1210.28018 789.025525 1247.669004 716.759062 1249.90803 627.269511 1216.947591 552.929899 1186.342062 482.635866 1136.546388 434.242191 1068.665237 401.293428ZM796.071025 576.285972C784.874375 612.115777 733.365318 622.928991 708.616704 595.576855 681.240669 570.392793 691.88864 517.566914 727.758071 506.350864 767.651159 489.305001 813.548508 535.875601 796.071025 576.285972L796.071025 576.285972ZM1047.444295 581.227295C1034.168173 613.595632 987.769102 621.04848 964.867247 596.014907 953.772668 585.573654 951.073986 569.750547 946.628332 555.739558 952.560216 530.537944 968.652355 504.29175 996.052013 503.156339 1033.972617 497.73815 1067.238973 545.816053 1047.444295 581.227295L1047.444295 581.227295Z" fill="#30C730" p-id="1584"></path></svg>
                    &nbsp;&nbsp;联系客服</a>
                </div>
            </div>
        </div>
    </div>
    <div class="gallery-list" style="margin-top: 2rem;">
        <p><?=$book->text;?></p>
        <img src="<?=$book->image;?>"/>
        <?php
           foreach(explode(";",$book->images) as $image){
                echo  "<img src=\"$image\"/>";
            }
        ?>

    </div>
<?php
}


function used_books_qrcode_pay($order_id ,$type = "alipay")
{
    $order = used_books_get_order($order_id);
    if($order->status != 1){
        echo "订单状态异常，可能已经支付成功，请返回<a href=\"/used-orders/\">订单列表</a>查看";
    }
    if($order->payment_method == "alipay"){
        $arr = array(
            "pid" => CS_PAY_PID,
            "type" => $type,
            "notify_url" => home_url()."/wsg/used-books-payment-callback/",
            "return_url" => home_url()."/wsg/used-books-payment-return/",
            "out_trade_no" => 'wsg-'.$order_id,
            "name" => "【文硕阁】购买二手书籍",
            "money" => $order->order_money,
            "sign_type" => "MD5"
        );
        $payurl= "http://7-pay.cn/submit.php?pid=".CS_PAY_PID."&type={$arr['type']}&notify_url={$arr['notify_url']}&return_url={$arr['return_url']}&out_trade_no={$arr['out_trade_no']}&name={$arr['name']}&money={$arr['money']}&sign_type={$arr['sign_type']}&sign=".cs_get_sign($arr,CS_PAY_KEY);
        echo "<p>正在跳转支付...   如何没有自动跳转请<a href=\"$payurl\">点击这里</a>手动跳转</p>";
        echo "<script type=\"text/javascript\">window.location.href=\"$payurl\";</script>";

    }else{
        require_once WSG_DIR . 'includes/wxpay.class.php';
        $wxPay = new WxpayService();
        $wxPay->pay_init(WSG_MCHID, WSG_APPID, WSG_APIKEY, WSG_KEYPEM, WSG_SERIALNUMBER);
        $wxPay->setTotalFee( 1);
        $wxPay->setOutTradeNo('wsg-'.$order_id);
        $wxPay->setOrderName("【文硕阁】购买二手书籍");
        $wxPay->setNotifyUrl( home_url() . "/wsg/used-books-payment-wxpay-notify/");
        $result = $wxPay->doPay();
        if (isset($result['code'])) {
            echo $result['code'] . ':' . $result['message'];
            exit();
        }
        $code_url = 'https://wenhairu.com/static/api/qr/?size=300&text=' . $result['code_url'];
        echo <<<EOT
        
        <div id="center_info"><img src="{$code_url}"/></div>
        

        <script>
            timename = setInterval("check()", 3000);
            function check() {
                $.ajax({
                    url: '/wsg/check_order/?order_number=wsg-<?=$order->id;?>',
                    type: 'get',
                    success: function(data) {
                        console.log(data);
                        if (data == "paided") {
                            $("#center_info").html("<div style=\"text-align:center;width:300px\"><img src=\"/wp-content/plugins/wsg/assets/img/success.png\"><br>支付成功，3秒后将跳转到商户网站...</div>");
                            setTimeout(function() {
                                top.location.href = "/vip/";
                            }, 3000);

                        }
                    }
                });
            }
        </script>

        EOT;
        die();
    }
	
}

function used_books_process_order_post() {
    global $wpdb;
    $error_message = [];

    $used_book = used_books_get_book((int)$_POST['used_book_id']);
    if(!$used_book){
        $error_message[] = "请求处理失败，请重试！";
    }

    if(!isset($_POST['buyer_name'])){
        $error_message[] = "姓名格式不正确！";
    }
    if(strlen($_POST['buyer_phone']) != 11){
        $error_message[] = "手机号格式不正确！";
    }
    if(strlen($_POST['buyer_address']) < 10){
        $error_message[] = "收货地址格式不正确！";
    }
    if(strlen($_POST['buyer_area']) <  6){
        $error_message[] = "地区不正确";
    }

    if(strlen($_POST['buyer_area_id']) < 6){
        $error_message[] = "地区id不正确";
    }

    if(!isset($_POST['payment_method'])){
        $error_message[] = "支付方式未选择！";
    }elseif(!in_array($_POST['payment_method'], ['weixin','alipay']) ){
        $error_message[] = "此支付方式未启用！";
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
            'buyer_area'    => $_POST['buyer_area'],
            'buyer_area_id' => $_POST['buyer_area_id'],
            'buyer_address' => $_POST['buyer_address'],
            'order_money'   => used_books_calculate_price($used_book->price),
            'buyer_notes'   => $_POST['buyer_notes'],
            'payment_method' => $_POST['payment_method'],
            'status'        => 1,
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


function used_orders_page(){
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">订单管理</h1>
        <a href="?page=used_books&action=create" class="page-title-action">录入书籍</a>
        <a href="?page=used_orders" class="page-title-action">返回列表</a>

        <hr class="wp-header-end">
        <style>
            .column-images{
                width: 500px;
            }
        </style>
        <?php
            $action = isset($_GET['action'])? $_GET['action'] : "";
            if($action == "set_paid"){
                if(used_books_order_set_paid((int)$_GET['id'])){
                    echo '<div id="message" class="updated notice"><p>订单状态修改完成！</p></div>';
                }else{
                    echo '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
                }
                
            } elseif($action == "delivery"){
                used_books_delivery_form();
            } else{
                $list_table = new UsedOrders_List_Table();
                echo '<form method="post">';
                $list_table->prepare_items();
                $list_table->search_box('search', 'search_id');
                $list_table->display();
                echo '</form>';
            }
        ?>
    </div>
<?php
}



function used_books_order_set_paid($order_id){
    global $wpdb;

    return $wpdb->update(
        $wpdb->prefix . 'used_orders',
        array(
            'paid_date' => current_time('mysql'),
            'status'    => 2,
        ),
        array(
            'id' => $order_id
        )
    );

}

function used_books_delivery_form() {
    global $wpdb;

    $order_id = (int)$_GET['id'];

    $order = used_books_get_order($order_id);
    $used_book = used_books_get_book($order->used_book_id);


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $result = $wpdb->update(
            $wpdb->prefix . 'used_orders',
            array(
                'express_company'   => $_POST['express_company'],
                'express_number'    => $_POST['express_number'],
                'delivery_date'     => current_time('mysql'),
                'status'            => 3,
            ),
            array(
                'id' => (int)$_GET['id']
            ),
        );

        if($result){
            echo '<div id="message" class="updated notice"><p>订单发货成功！</p></div>';
        }else{
            echo '<div id="message" class="notice notice-error"><p><strong>出现错误！</strong></p></div>';
        }
    }else{
?>

    <h3>订单信息</h3>
    <ul>
        <li>订单编号：<i>wsg-<?=$order->id;?></i></li>
        <li>支付金额：¥ <?=$order->order_money;?></li>
        <li>支付方式：支付宝</li>
        <li>下单时间：<?=$order->create_date;?></li>
        <li>订单状态：<?=used_books_order_status_display($order->status);?></li>
    </ul>

    
    <h3>商品信息</h3>
    <p><?=$used_book->name;?> [<a target="_blank" href="/used-book/<?=$used_book->id;?>/">前台查看</a>]</p>
    <p><?=$used_book->weight;?> g</p>
    <p><?=$used_book->order_money;?> 元</p>
    <p><img src="<?=used_books_cdn_image($used_book->image,"sm");?>" width="100"/></p>
    <p>
    <?php foreach(array_filter( explode(";",$used_book->images) ) as $image){ ?>
        <img src="<?=used_books_cdn_image($image,"sm");?>" width="100"/>
    <?php } ?>

    </p>
    <hr>
    <h3>收货信息</h3>
    <ul>
        <li><b>姓名：</b><?=$order->buyer_name;?></li>
        <li><b>手机：</b><?=$order->buyer_phone;?></li>
        <li><b>地址：</b><?=$order->buyer_area." ".$order->buyer_address;?></li>
    </ul>

    <hr>
    <h3>发货信息</h3>
    <form method="post" enctype="multipart/form-data">
        <p>
            <select name="express_company" id="express_company">
                <option value="韵达快递">韵达快递</option>
                <option value="中通快递">中通快递</option>
                <option value="申通快递">申通快递</option>
                <option value="圆通快递">圆通快递</option>
                <option value="邮政快递">邮政快递</option>
                <option value="极兔快递">极兔快递</option>
                <option value="顺丰快递">顺丰快递</option>
                <option value="京东快递">京东快递</option>
            </select>
            
            <input name="express_number" style="vertical-align: top;" type="text" id="express_number" value="" class="regular-text">
        </p>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="立即发货"></p>
    </form>
<?php
    }
}


function used_books_get_order($order_id){
    global $wpdb;
    return $wpdb->get_row(" SELECT * FROM `{$wpdb->prefix}used_orders` WHERE `id` = '{$order_id}' LIMIT 1");
}

function used_books_calculate_price($order_money){
    if($order_money > 10) return $order_money;
    
    if(is_user_logged_in()){
        global $wpdb;
        $isBuy = $wpdb->get_row(" SELECT * FROM `{$wpdb->prefix}used_orders` WHERE `user_id` = '".get_current_user_id()."' AND `status` > 1 LIMIT 1");
        if(!$isBuy){
            $order_money = 4.9;
        }
    }else{
        $order_money = 4.9;
    }

    return $order_money;
}

function used_books_get_book($book_id){
    global $wpdb;
    return $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_books` WHERE `id` = '{$book_id}' LIMIT 1");
}


function used_books_payment_callback(){
    sscanf($_GET["out_trade_no"], "wsg-%d", $order_id);
    if(wsg_check_payment_callback()){
        $order = used_books_get_order($order_id);
        if($order->status == 1){
            if(!used_books_order_set_paid($order_id)){
                echo 'error';die;
            }
        }
    }
    echo 'success';die;
}


function used_books_payment_return(){
    sscanf($_GET["out_trade_no"], "wsg-%d", $order_id);
    if(wsg_check_payment_callback()){

        wp_redirect("/used-orders/?action=details&order_id=$order_id");
       
    }else{
        wp_die("支付结果验证失败，请联系网站客服处理！");
    }
}

function used_books_cdn_image($url,$spec = "w300h400"){
    return str_replace("https://www.wenshuoge.com","https://wenshuoge.oss-accelerate.aliyuncs.com",$url).'?x-oss-process=style/'.$spec;
}