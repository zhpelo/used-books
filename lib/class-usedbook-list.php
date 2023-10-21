<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
// CDKEY列表类
class UsedBook_List_Table extends WP_List_Table {
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
            'image'    => '主图',
            'images'    => '相册',
            'weight'    => '重量',
            'in_date'   => '入库时间',
            'out_date'  => '出库时间',
            'operate'   => '操作',
        ];
        return $columns;
    }
    public function prepare_items() {
        global $wpdb;
        // 获取分页参数
        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 20;
        // 查询数据库获取数据（包括分页限制）
        $data = $this->get_data();
        $columns  = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, [], $sortable];
        // 设置总行数
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}used_books`");
    
        // 设置分页参数
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);
        // 设置数据
        $this->items = $data;
    }

    
    // 列表内容输出
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    // 获取数据（示例数据，你需要替换为实际数据获取方法）
    private function get_data() {
        global $wpdb;
        // 获取分页参数
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 20;

        // 计算 OFFSET 值
        $offset = ($paged - 1) * $per_page;

        // 查询数据库获取数据（添加 LIMIT 和 OFFSET 子句）
        $results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}used_books` ORDER BY id DESC LIMIT $per_page OFFSET $offset", ARRAY_A);

        return $results;
    }



    public function column_name( $book ) {
        return "<a href=\"?page=chapters&book_id={$book['id']}\">{$book['name']}</a>";
	}

    public function column_image( $book ) {
        return '<img src="'.$book['image'].'" width="80"/>';
	}

    public function column_images( $book ) {
        $html = "";
        foreach(explode(";",$book['images']) as $image){
            $html .= "<img src=\"$image\" width=\"80\"/>";
        }
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
