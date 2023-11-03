<?php
/*
Template Name: Used Books Oreber Template
*/

get_header();
$action = isset($_GET['action'])? $_GET['action'] : "";

?>
<style>
    .error{
        padding: 0.5rem 1rem;
        background-color: crimson;
        color: #fff;
        margin-bottom: 1rem;
    }

    .container-full{
        max-width: 750px;
        margin: 0 auto;
        padding-left: 10px;
        padding-right: 10px;
    }
    .order_item{
        padding: 20px;
    }
    .order_item .l {
        flex: none;
    }

    .order_item .l img{
        width: 100px;;
    }
    .order_item .r{
        display: inline-grid;
        margin-left: 10px;
        width: 100%;
    }
    .order_item .r .buyinfo{
        margin-bottom: 20px;
    }
    .order_item .r .bookname{
        font-size: 24px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap; 
    }
    .order_item .r .button{
        padding: 0 10px;
        min-height: 28px;
        font-size: 14px;
    }
    @media (max-width: 690px){
        .order_item{
            padding: 10px;
        }
        .order_item .l img{
            width: 80px;;
        }
        .order_item .r .bookname{
            font-size: 16px;
        }
    }
</style>
<div class="container-full" data-content="narrow" data-vertical-spacing="top:bottom">
        <div class="hero-section" data-type="type-1">
            <header class="entry-header" style="display: flex;">
                <h1 class="page-title" title="<?=$post->post_title;?>" itemprop="headline"><?=$post->post_title;?></h1>
                
                <span style="line-height: 46px;margin: 0;padding: 0 16px;">
                    <a href="/contact-us/">联系客服</a>
                </span>

                <?php if($action){?>
                    <span style="line-height: 46px;margin: 0;">
                        <a href="/used-orders/">订单列表</a>
                    </span>
                <?php } ?>
                
            </header>
        </div>

        <div class="entry-content">
            <?php
            if($action == 'buy'){
                global $wpdb;
                $last_order = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_orders` WHERE `user_id` = '".get_current_user_id()."' ORDER BY `id` DESC LIMIT 1");

                $default_buyer_name = isset($_POST['buyer_name']) ? $_POST['buyer_name']: ($last_order ? $last_order->buyer_name : '');

                $default_buyer_phone = isset($_POST['buyer_phone']) ? $_POST['buyer_phone'] : ($last_order ? $last_order->buyer_phone : '');

                $default_buyer_address = isset($_POST['buyer_address']) ? $_POST['buyer_address']: ($last_order ? $last_order->buyer_address : '');

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    used_books_process_order_post();
                }
                ?> 
                <h2>请填写收件人信息</h2>
                <form action="" method="post">
                    <input name="used_book_id" type="hidden"  value="<?=$_GET['id'];?>">
                    <table class="form-table" role="presentation"  style="background-color: #fff;">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 5rem;">
                                    <label for="buyer_name">姓名</label>
                                </th>
                                <td>
                                    <input name="buyer_name" type="text" id="buyer_name" value="<?=$default_buyer_name;?>" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="buyer_phone">手机号</label>
                                </th>
                                <td>
                                    <input name="buyer_phone" type="number" id="buyer_phone" value="<?=$default_buyer_phone;?>" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="buyer_address">地址</label>
                                </th>
                                <td>
                                    <input name="buyer_address" type="text" id="buyer_address" value="<?=$default_buyer_address;?>" class="regular-text" required>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="buyer_notes">备注</label>
                                </th>
                                <td>
                                    <input name="buyer_notes" type="text" id="buyer_notes" value="" class="regular-text" required>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="padding: 20px; text-align: center;">
                        <button type="submit" name="submit" id="submit" class="button button-primary" style="background-color: #00a0e9;padding: 9px 14px;">
                            
                        <svg t="1699031614474" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6118" width="28" height="28"><path d="M492.343 777.511c-67.093 32.018-144.129 51.939-227.552 32.27-83.424-19.678-142.626-73.023-132.453-171.512 10.192-98.496 115.478-132.461 202.07-132.461 86.622 0 250.938 56.122 250.938 56.122s13.807-30.937 27.222-66.307c13.405-35.365 17.21-63.785 17.21-63.785H279.869v-35.067h169.995v-67.087l-211.925 1.526v-44.218h211.925v-100.63h111.304v100.629H788.35v44.218l-227.181 1.524v62.511l187.584 1.526s-3.391 35.067-27.17 98.852c-23.755 63.783-46.061 96.312-46.061 96.312L960 685.279V243.2C960 144.231 879.769 64 780.8 64H243.2C144.231 64 64 144.231 64 243.2v537.6C64 879.769 144.231 960 243.2 960h537.6c82.487 0 151.773-55.806 172.624-131.668L625.21 672.744s-65.782 72.748-132.867 104.767z" p-id="6119" data-spm-anchor-id="a313x.search_index.0.i7.77693a81qkyCSI" class="selected" fill="#ffffff"></path><path d="M297.978 559.871c-104.456 6.649-129.974 52.605-129.974 94.891s25.792 101.073 148.548 101.073c122.727 0 226.909-123.77 226.909-123.77s-141.057-78.842-245.483-72.194z" p-id="6120" data-spm-anchor-id="a313x.search_index.0.i8.77693a81qkyCSI" class="selected" fill="#ffffff"></path></svg>

                            &nbsp; 使用支付宝付款
                        </button>
                    </div>

                </form>

                <?php
            } elseif($action == 'qrcode_pay'){
                used_books_qrcode_pay($_GET['order_id']);
            }elseif($action == 'details'){

                $order = used_books_get_order( $_GET['order_id']);

                if(!$order->express_number){
                    echo "<p><b>等待发货...</b></p>";
                }else{

                ?>
                    <p>快递公司：<?=$order->express_company;?></p>
                    <p>快递单号：<?=$order->express_number;?></p>
                    <div style="padding: 20px;background-color: white;">
                        <img src="https://free-barcode.com/cn/barcode.asp?bc1=<?=$order->express_number;?>&bc2=10&bc3=3.5&bc4=1.2&bc5=1&bc6=1&bc7=Arial&bc8=15&bc9=1" alt="微信扫一扫查看物流信息">
                    </div>
                    <p>使用微信扫一扫上面的条形码，可以查看订阅物流信息</p>

                <?php
                }
            }else{ 
                $status = isset($_GET['status']) ? (int)$_GET['status'] : 0;
                ?>
            
            <div class="wp-block-buttons " style="display: flow-root;">
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?=$status==0?'style="background-color: crimson;"':"";?> href="?status=0">全部</a>
                </div>
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?=$status==1?'style="background-color: crimson;"':"";?> href="?status=1">待付款</a>
                </div>

                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?=$status==2?'style="background-color: crimson;"':"";?> href="?status=2">待发货</a>
                </div>

                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" <?=$status==3?'style="background-color: crimson;"':"";?> href="?status=3">待收货</a>
                </div>
            </div>


            <div style="background-color: #fff; ">

                <?php
                    
                    $WHERE = "WHERE user_id = '".get_current_user_id()."' ";
                    if($status){
                        $WHERE .= " AND status = $status";
                    }
                    // 获取数据并循环显示每一行
                    $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}used_orders $WHERE ORDER BY `id` DESC LIMIT 50");

                    foreach ($data as $row) { 
                        $used_book = used_books_get_book($row->used_book_id);
                        ?>

                    <div class="order_item" style="display: flex;">

                        <div class="l">
                            <a href="/used-books/<?=$row->used_book_id;?>/"><img src="<?= str_replace("https://www.wenshuoge.com","https://wenshuoge.oss-cn-shanghai.aliyuncs.com",$used_book->image) ;?>?x-oss-process=style/w300h400"/></a>
                        </div>
                        <div class="r">
                            <div class="bookname"><?=$used_book->name;?></div>
                            <div class="buyinfo">
                                ¥ <b style="color:red"><?=$row->price;?></b>  / <?=used_books_order_status_display($row->status);?>
                            </div>
                            
                            <div class="buttons">
                                <?php if($row->status == 1){ ?>
                                    <a class="button" style="background-color: tomato;" href="?action=qrcode_pay&order_id=<?=$row->id;?>">立即支付</a>
                                <?php } ?>

                                <?php if($row->status == 2){ ?>
                                    <a class="button" style="background-color: tomato;" href="?action=details&order_id=<?=$row->id;?>">查看物流</a>
                                <?php } ?>

                                <a class="button" href="?action=details&order_id=<?=$row->id;?>">查看订单</a>

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