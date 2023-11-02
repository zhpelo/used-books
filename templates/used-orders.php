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
</style>
<div class="ct-container-full" data-content="narrow" data-vertical-spacing="top:bottom">
    <article class=" format-standard hentry category-uncategorized">

        <div class="hero-section" data-type="type-1">
            <header class="entry-header" style="display: flex;">
                <h1 class="page-title" title="<?=$post->post_title;?>" itemprop="headline"><?=$post->post_title;?></h1>
                
                <span style="line-height: 30px;margin: 0;padding: 8px 20px;">[<a href="/contact-us/">联系客服</a>]</span>

                <?php if($action){?>
                    <span style="line-height: 30px;margin: 0;padding: 8px 0">[<a href="/used-orders/">返回订单列表</a>]</span>
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
                <form action="" method="post" style="background-color: #fff;">
                    <input name="used_book_id" type="hidden"  value="<?=$_GET['id'];?>">
                    <table class="form-table" role="presentation" >
                        <tbody>
                            <tr>
                                <th scope="row">
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
                        </tbody>
                    </table>

                    <div style="padding: 20px;">
                        <button type="submit" name="submit" id="submit" class="button button-primary">
                            <svg t="1698084617596" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1453" width="24" height="24"><path d="M1023.795 853.64v6.348a163.807 163.807 0 0 1-163.807 163.807h-696.18A163.807 163.807 0 0 1 0 859.988v-696.18A163.807 163.807 0 0 1 163.807 0h696.181a163.807 163.807 0 0 1 163.807 163.807V853.64z" fill="#009FE9" p-id="1454"></path><path d="M844.836 648.267c-40.952-14.333-95.623-34.809-156.846-57.128a949.058 949.058 0 0 0 90.094-222.573H573.325V307.14h245.711v-43.41l-245.71 2.458V143.33H472.173c-18.223 0-21.704 20.476-21.704 20.476v102.38H204.759v40.952h245.71v61.427H245.712v40.952h409.518a805.522 805.522 0 0 1-64.909 148.246c-128.384-42.795-266.186-77.604-354.233-55.08a213.564 213.564 0 0 0-112.003 63.27c-95.418 116.917-26.21 294.034 175.274 294.034 119.989 0 236.087-67.366 325.771-177.73 134.322 65.932 398.666 176.297 398.666 176.297V701.3s-32.352-4.095-178.96-53.033z m-563.702 144.97c-158.893 0-204.759-124.699-126.336-194.112a191.86 191.86 0 0 1 90.913-46.276c93.575-10.238 189.811 35.629 293.624 86.614-74.941 94.598-166.674 153.774-258.2 153.774z" fill="#FFFFFF" p-id="1455"></path></svg>
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
            }else{ ?>
            

            <div style="background-color: #fff; ">
                <table>
                    <thead>
                        <tr>
                            <th>订单ID</th>
                            <th>购买商品</th>
                            <th>收货信息</th>
                            <th>订单状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 获取数据并循环显示每一行
                        $data = $wpdb->get_results("SELECT {$wpdb->prefix}used_orders.*,{$wpdb->prefix}used_books.image
                        FROM {$wpdb->prefix}used_orders
                        JOIN {$wpdb->prefix}used_books ON {$wpdb->prefix}used_orders.used_book_id = {$wpdb->prefix}used_books.id
                        WHERE {$wpdb->prefix}used_orders.user_id = '".get_current_user_id()."' ORDER BY `id` DESC LIMIT 50");
                        foreach ($data as $row) {
                            echo '<tr>';
                            echo '<td>' . $row->id . '</td>';
                            echo '<td><a href="/used-books/'.$row->used_book_id.'/"><img src="' . $row->image . '" width="100" /></a></td>';
                            echo "<td>$row->buyer_name  $row->buyer_phone<br>$row->buyer_address</td>";

                            echo "<td>".used_books_order_status_display($row->status)."</td>";

                            if($row->status == 0){
                                echo '<td><a href="?action=qrcode_pay&order_id='.$row->id.'">去付款</a></td>';
                            }else{
                                echo '<td><a href="?action=details&order_id='.$row->id.'">详细信息</a></td>';
                            }
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <?php
            
            }
            
            ?>
        </div>
    </article>
</div>
<?php
get_footer();



