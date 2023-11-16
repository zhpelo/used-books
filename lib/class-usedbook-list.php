<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class UsedBook_List_Table extends WP_List_Table {

    private $total_items;
    // 构造函数
    public function __construct() {
        parent::__construct([
            'singular' => 'item',
            'plural'   => 'items',
            'ajax'     => false
        ]);
    }

    // 列表列设置
    public function get_columns() {
        $columns = [
            'cb'        => '<input type="checkbox" />',
            'id'        => 'ID',
            'name'     => '书名',
            'weight'    => '重量',
            'sales'     => '销量',
            'in_date'   => '入库时间',
            'operate'   => '操作',
        ];
        return $columns;
    }

    protected function get_sortable_columns()
    {
        $sortable_columns = array(
                'id'  => array('id', false),
                'weight' => array('weight', false),
                'sales'   => array('sales', true)
        );
        return $sortable_columns;
    }

    public function prepare_items() {
        ?>
        <style type="text/css">
        .wp-list-table #id {
            width: 60px;
        }
        .wp-list-table #name {
            width: 50%;
        }
        </style>
        <?php
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'desc';
        $filter = isset($_REQUEST['filter']) ? sanitize_text_field($_REQUEST['filter']) : '';
        $paged = isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : 1;
        $per_page = isset($_REQUEST['per_page']) ? absint($_REQUEST['per_page']) : 20;
        
        $args = array(
            'search' => $search,
            'orderby' => $orderby,
            'order' => $order,
            'filter' => $filter,
            'paged' => $paged,
            'per_page' => $per_page,
        );

        $columns  = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, [], $sortable];
        $this->items = $this->get_data($args);
        $this->set_pagination_args([
            'total_items' =>  $this->total_items,
            'per_page'    => 20,
        ]);
    }

    private function get_data($args) {
        global $wpdb;
        $offset = ($args['paged'] - 1) * $args['per_page'];
        $table_name = $wpdb->prefix."used_books";
        $where = "";
        if (!empty($args['search'])) {
            $where = " WHERE `name` LIKE '%{$args['search']}%'";
        }
        $orderby = " ORDER BY {$args['orderby']} {$args['order']}";
        $limit = " LIMIT {$args['per_page']} OFFSET $offset";
        $this->total_items = $wpdb->get_var("SELECT COUNT(*) FROM ". $table_name . $where);
        $results = $wpdb->get_results("SELECT * FROM ".$table_name . $where . $orderby . $limit, ARRAY_A);
        return $results;
    }


    // 列表内容输出
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    public function column_name( $book ) {

        $html = "<div style=\"width: 100%;overflow: hidden;\"><p><a href=\"/used-books/{$book['id']}/\">{$book['name']}</a></p>";
        $html .= "<div style='display: inline-flex;'>";
        $html .= '<img src="'.used_books_cdn_image($book['image'],"sm-square").'" width="80"/>';
        foreach(array_filter( explode(";",$book['images']) ) as $image){
            $html .= "<img src=\"".used_books_cdn_image($image,"sm-square") ."\" width=\"80\"/>";
        }
        $html .= "</div></div>";
        return $html;
	}

    public function column_operate( $book ) {
        $html = "[<a href=\"?page=books&action=edit&id={$book['id']}\">下架</a>]"; 
		return $html;
	}

    /**
     * 添加勾选框列
     *
     * @param object $item 当前行的数据项
     * @return string 勾选框列的 HTML 内容
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />',
            $item['id']
        );
    }

 
	public function no_items() {
		_e( 'No Used Book found.' );
	}
}


class UsedOrders_List_Table extends WP_List_Table {

    private $total_items;
    // 构造函数
    public function __construct() {
        parent::__construct([
            'singular' => 'item',
            'plural'   => 'items',
            'ajax'     => false
        ]);
    }

    /**
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'id'            => array( 'id', false),
            'date'     => array( 'paid_date', false),
		);
	}

    // 列表列设置
    public function get_columns() {
        $columns = [
            'cb'        => '<input type="checkbox" />',
            'id'        => 'ID',
            'book'      => '购买商品',
            'user_id'   => '所属用户',
            'buyer'     => '收货信息',
            'date'      => '时间',
            'status'    => "订单状态",
            'operate'   => '操作',
        ];
        return $columns;
    }
    public function prepare_items() {
        ?>
        <style type="text/css">
        .wp-list-table #id {
            width: 60px;
        }
        .wp-list-table #name {
            width: 50%;
        }
        </style>
        <?php
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'desc';
        $filter = isset($_REQUEST['filter']) ? sanitize_text_field($_REQUEST['filter']) : '';
        $paged = isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : 1;
        $per_page = isset($_REQUEST['per_page']) ? absint($_REQUEST['per_page']) : 20;
        
        $args = array(
            'search' => $search,
            'orderby' => $orderby,
            'order' => $order,
            'filter' => $filter,
            'paged' => $paged,
            'per_page' => $per_page,
        );

        $columns  = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, [], $sortable];
        $this->items = $this->get_data($args);
        $this->set_pagination_args([
            'total_items' =>  $this->total_items,
            'per_page'    => 20,
        ]);
    }
    // 列表内容输出
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    private function get_data($args) {
        global $wpdb;
        $offset = ($args['paged'] - 1) * $args['per_page'];
        $table_name = $wpdb->prefix."used_orders";
        $where = "";
        if (!empty($args['search'])) {
            $where = " WHERE  (`buyer_name` LIKE '%{$args['search']}%' OR `buyer_phone` LIKE '%{$args['search']}%' OR `buyer_address` LIKE '%{$args['search']}%' OR `express_number` LIKE '%{$args['search']}%') ";
        }
        $orderby = " ORDER BY {$args['orderby']} {$args['order']}";
        $limit = " LIMIT {$args['per_page']} OFFSET $offset";
        $this->total_items = $wpdb->get_var("SELECT COUNT(*) FROM ". $table_name . $where);
        $results = $wpdb->get_results("SELECT * FROM ".$table_name . $where . $orderby . $limit, ARRAY_A);
        return $results;
    }

    public function column_buyer( $book ) {
        $html = $book['buyer_name']."<br>";
        $html .= $book['buyer_phone']."<br>";
        $html .= $book['buyer_area'];
        return $html;
	}

    public function column_book( $book ) {
        global $wpdb;
        $used_book =  $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_books` WHERE `id` = '{$book['used_book_id']}'");
        return "<a href=\"/used-books/{$used_book->id}/\"><img src=\"".used_books_cdn_image( $used_book->image ,"sm-square")."\" width=\"80\"/></a>";
	}

    public function column_date( $book ) {
        $html = $book['create_date']."</br>";
        $html .= $book['paid_date']."</br>";
        return $html;
	}

    /**
     * 添加勾选框列
     *
     * @param object $item 当前行的数据项
     * @return string 勾选框列的 HTML 内容
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />',
            $item['id']
        );
    }

    public function column_operate( $book ) {
        $html = "[<a href=\"?page=used_orders&action=delivery&id={$book['id']}\">订单详情</a>]</br>";
        switch ($book['status']) {
            case 1:
                $html .= "[<a href=\"?page=used_orders&action=set_paid&id={$book['id']}\">已收款</a>]";
                break;
            case 2:
                $html .= "[<a href=\"?page=used_orders&action=delivery&id={$book['id']}\">去发货</a>]";
                break;
            case 3:
                $html .= "[<a href=\"?page=used_orders&action=delivery&id={$book['id']}\">修改发货信息</a>]";
                break;
        }
		return $html;
	}

    public function column_status( $book ) {
        return used_books_order_status_display($book['status']);
	}
 
	public function no_items() {
		_e( 'No Used Ordes found.' );
	}
}


function used_books_order_status_display($status){
    switch ($status) {
        case 1:
            $html = "<span style=\"color: gray;\">未付款</span>";
            break;
        case 2:
            $html = "<span style=\"color: red;\">已付款</span>";
            break;
        case 3:
            $html = "<span style=\"color: green;\">已发货</span>";
            break;
        case 4:
            $html = "已收货";
            break;
        case 5:
            $html = "已完成";
            break;
        default :
            $html = "<span style=\"color: gray;\">未知状态</span>";
            break;
    }
    return $html;
}
