<?php
/*
Template Name: Used Books Template
*/

get_header();
$action = $_GET['action'];
?>
<div class="ct-container-full" data-content="narrow" data-vertical-spacing="top:bottom">
    <article class=" format-standard hentry category-uncategorized">
        <div class="hero-section">
            <?php
            if($action == 'buy'){
                ?> 
                <h2>请填写收件人信息</h2>
                <form action="?action=buypost" method="post" style="background-color: #fff;">
                    <input name="used_book_id" type="hidden"  value="<?=$_GET['id'];?>">
                    <table class="form-table" role="presentation" >
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="buyer_name">姓名</label>
                                </th>
                                <td>
                                    <input name="buyer_name" type="text" id="buyer_name" value="" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="buyer_phone">手机号</label>
                                </th>
                                <td>
                                    <input name="buyer_phone" type="text" id="buyer_phone" value="" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="buyer_address">地址</label>
                                </th>
                                <td>
                                    <input name="buyer_address" type="text" id="buyer_address" value="" class="regular-text">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="padding: 20px;">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="支付宝付款">
                    </div>

                </form>

                <?php
            }elseif($action == 'buypost'){
                $data = [
                    'user_id'       => get_current_user_id(),
                    'used_book_id'  => $_POST['used_book_id'],
                    'buyer_name'    => $_POST['buyer_name'],
                    'buyer_phone'   => $_POST['buyer_phone'],
                    'buyer_address' => $_POST['buyer_address'],
                    'create_date'   => current_time('mysql'),
                ];
                $wpdb->insert($wpdb->prefix.'used_orders', $data);
            }
            
            ?>
        </div>
    </article>
</div>
<?php
get_footer();