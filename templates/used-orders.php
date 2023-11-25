<?php
/*
Template Name: Used Books Oreber Template
*/

get_header();
$action = isset($_GET['action']) ? $_GET['action'] : "";

?>
<style>
    .error {
        padding: 0.5rem 1rem;
        background-color: crimson;
        color: #fff;
        margin-bottom: 1rem;
    }

    .container-full {
        max-width: 750px;
        margin: 0 auto;
        padding-left: 10px;
        padding-right: 10px;
    }

    .order_item {
        padding: 20px;
    }

    .order_item .l {
        flex: none;
    }

    .order_item .l img {
        width: 100px;
        ;
    }

    .order_item .r {
        display: inline-grid;
        margin-left: 10px;
        width: 100%;
    }

    .order_item .r .buyinfo {
        margin-bottom: 20px;
    }

    .order_item .r .bookname {
        font-size: 24px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .order_item .r .button {
        padding: 0 10px;
        min-height: 28px;
        font-size: 14px;
    }

    @media (max-width: 690px) {
        .order_item {
            padding: 10px;
        }

        .order_item .l img {
            width: 80px;
            ;
        }

        .order_item .r .bookname {
            font-size: 16px;
        }
    }

    th,
    td {
        padding: 12px 8px;
    }

    #city_select {
        display: flex;
        margin-bottom: 8px;
    }
</style>
<div class="container-full" data-content="narrow" data-vertical-spacing="top:bottom">
    <div class="hero-section" data-type="type-1">
        <header class="entry-header" style="display: flex;">
            <h1 class="page-title" title="<?= $post->post_title; ?>" itemprop="headline"><?= $post->post_title; ?></h1>

            <span style="line-height: 46px;margin: 0;padding: 0 16px;">
                <a href="/contact-us/">联系客服</a>
            </span>

            <?php if ($action) { ?>
                <span style="line-height: 46px;margin: 0;">
                    <a href="/used-orders/">订单列表</a>
                </span>
            <?php } ?>

        </header>
    </div>

    <div class="entry-content" style="background-color: #fff;padding: 1rem;">

        <?php
        if ($action == 'buy') {
            global $wpdb;
            $last_order = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_orders` WHERE `user_id` = '" . get_current_user_id() . "' ORDER BY `id` DESC LIMIT 1");

            $default_buyer_name = isset($_POST['buyer_name']) ? $_POST['buyer_name'] : ($last_order ? $last_order->buyer_name : '');

            $default_buyer_phone = isset($_POST['buyer_phone']) ? $_POST['buyer_phone'] : ($last_order ? $last_order->buyer_phone : '');

            $default_buyer_address = isset($_POST['buyer_address']) ? $_POST['buyer_address'] : ($last_order ? $last_order->buyer_address : '');

            $default_buyer_area = isset($_POST['buyer_area']) ? $_POST['buyer_area'] : ($last_order ? $last_order->buyer_area : '');

            $default_buyer_area_id = isset($_POST['buyer_area_id']) ? $_POST['buyer_area_id'] : ($last_order ? $last_order->buyer_area_id : "0");

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                used_books_process_order_post();
            }
        ?>
            <h2>请填写收件人信息</h2>
            <form action="" method="post">
                <input name="used_book_id" type="hidden" value="<?= $_GET['id']; ?>">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 5rem;">
                                <label for="buyer_name"><b>姓&nbsp;&nbsp;&nbsp;名</b></label>
                            </th>
                            <td>
                                <input name="buyer_name" type="text" id="buyer_name" value="<?= $default_buyer_name; ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="buyer_phone"><b>手机号</b></label>
                            </th>
                            <td>
                                <input name="buyer_phone" type="number" id="buyer_phone" value="<?= $default_buyer_phone; ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="buyer_address"><b>收货地址</b></label>
                            </th>
                            <td>
                                <div id="city_select"></div>

                                <input name="buyer_area" type="hidden" id="buyer_area" value="<?= $default_buyer_area; ?>">

                                <input name="buyer_area_id" type="hidden" id="buyer_area_id" value="<?= $default_buyer_area_id; ?>">

                                <input name="buyer_address" type="text" id="buyer_address" value="<?= $default_buyer_address; ?>" class="regular-text" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="buyer_notes"><b>订单备注</b></label>
                            </th>
                            <td>
                                <input name="buyer_notes" type="text" id="buyer_notes" value="" class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="buyer_notes"><b>支付方式</b></label>
                            </th>
                            <td>
                                <label>
                                    <input type="radio" name="payment_method" value="weixin" checked/>
                                    <img src="/wp-content/plugins/wsg/assets/img/weixin.png" alt="微信支付" />
                                </label>

                                <label>
                                    <input type="radio" name="payment_method" value="alipay" />
                                    <img src="/wp-content/plugins/wsg/assets/img/alipay.png" alt="支付宝付款" />
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 1rem;">
                    <button type="submit" name="submit" id="submit" class="button button-primary" style="background-color: #00a0e9;padding: 9px 14px;">
                        立即付款
                    </button>
                </div>

                <script src="<?= plugin_dir_url(__FILE__); ?>../assets/js/area_format_js.level3.js"></script>
                <script>
                    BuildCitySelect("#city_select", <?= $default_buyer_area_id; ?>, set_buyer_area);

                    function set_buyer_area(id, hasChild, cityData) {

                        if (!hasChild) {
                            id1 = id.toString().substring(0, 2);
                            id2 = id.toString().substring(0, 4);
                            jQuery("#buyer_area").val(cityData[id1]['name'] + " " + cityData[id2]['name'] + " " + cityData[id]['name']);
                            jQuery("#buyer_area_id").val(id);
                        }

                        console.log(id, cityData[id]['name']);
                    }
                </script>
            </form>

        <?php
        } elseif ($action == 'qrcode_pay') {
            used_books_qrcode_pay($_GET['order_id']);
        } elseif ($action == 'details') {

            $order = used_books_get_order($_GET['order_id']);
            $used_book = used_books_get_book($order->used_book_id);
        ?>
            <style>
                .order-info {
                    background-color: #fff;
                    padding: 20px;
                }

                .order-info p {
                    margin-bottom: 0;
                    line-height: 36px;
                    font-size: 18px;
                }
            </style>

            <div class="order-info">

                <p>订单编号：<i>wsg-<?= $order->id; ?></i></p>
                <p>购买商品：<a href="/used-books/<?= $order->used_book_id; ?>/"><?= $used_book->name; ?></a></p>
                <p>支付金额：¥ <?= $order->order_money; ?></p>
                <p>支付方式：支付宝</p>
                <p>下单时间：<?= $order->create_date; ?></p>
                <p>订单状态：<?= used_books_order_status_display($order->status); ?></p>
                <?php if ($order->status >= 2) { ?>
                    <p>付款时间：<?= $order->paid_date; ?></p>
                <?php } ?>
                <?php if ($order->status >= 3) { ?>
                    <p>发货时间：<?= $order->delivery_date; ?></p>
                    <p>快递公司：<?= $order->express_company; ?></li>
                    <p>快递单号：<?= $order->express_number; ?></li>
                    <p style="margin-top: 40px;">
                        <img src="https://free-barcode.com/cn/barcode.asp?bc1=<?= $order->express_number; ?>&bc2=10&bc3=3.5&bc4=1.2&bc5=1&bc6=1&bc7=Arial&bc8=15&bc9=1" alt="微信扫一扫查看物流信息">
                    </p>
                    <p>用微信扫描上方条形码，可查看物流信息</p>
                <?php } ?>
                <br>
                <hr>

                <div style="margin-top: 100px;">
                    <p style="text-align: center;"><b>对此订单有任何问题，请联系下方微信客服处理！</b></p>

                    <p style="text-align: center; margin-top: 20px;">
                        <img src="https://www.wenshuoge.com/wp-content/uploads/2022/11/%E4%BC%A0%E7%A1%95%E5%85%AC%E7%89%88%E4%B9%A6-%E5%BE%AE%E4%BF%A1%E7%A0%81-751x1024.png" width="300">
                    </p>
                </div>

            </div>

        <?php
        } else {
            $status = isset($_GET['status']) ? (int)$_GET['status'] : 0;
        ?>

            <div class="wp-block-buttons " style="display: flow-root;">
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?= $status == 0 ? 'style="background-color: crimson;"' : ""; ?> href="?status=0">全部</a>
                </div>
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?= $status == 1 ? 'style="background-color: crimson;"' : ""; ?> href="?status=1">待付款</a>
                </div>

                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?= $status == 2 ? 'style="background-color: crimson;"' : ""; ?> href="?status=2">待发货</a>
                </div>

                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?= $status == 3 ? 'style="background-color: crimson;"' : ""; ?> href="?status=3">待收货</a>
                </div>
            </div>


            <div style="background-color: #fff; ">

                <?php

                $WHERE = "WHERE user_id = '" . get_current_user_id() . "' ";
                if ($status) {
                    $WHERE .= " AND status = $status";
                }
                // 获取数据并循环显示每一行
                $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}used_orders $WHERE ORDER BY `id` DESC LIMIT 50");

                foreach ($data as $row) {
                    $used_book = used_books_get_book($row->used_book_id);
                ?>

                    <div class="order_item" style="display: flex;">

                        <div class="l">
                            <a href="/used-books/<?= $row->used_book_id; ?>/"><img src="<?= str_replace("https://www.wenshuoge.com", "https://wenshuoge.oss-accelerate.aliyuncs.com", $used_book->image); ?>?x-oss-process=style/w300h400" /></a>
                        </div>
                        <div class="r">
                            <div class="bookname"><?= $used_book->name; ?></div>
                            <div class="buyinfo">
                                ¥ <b style="color:red"><?= $row->order_money; ?></b> / <?= used_books_order_status_display($row->status); ?>
                            </div>

                            <div class="buttons">
                                <?php if ($row->status == 1) { ?>
                                    <a class="button" style="background-color: tomato;" href="?action=qrcode_pay&order_id=<?= $row->id; ?>">立即支付</a>
                                <?php } ?>

                                <?php if ($row->status == 2) { ?>
                                    <a class="button" style="background-color: tomato;" href="?action=details&order_id=<?= $row->id; ?>">查看物流</a>
                                <?php } ?>

                                <a class="button" href="?action=details&order_id=<?= $row->id; ?>">查看订单</a>

                            </div>

                        </div>

                    </div>

                    <hr>

                <?php } ?>
            </div>

        <?php

        }

        ?>
    </div>
</div>
<?php
get_footer();
