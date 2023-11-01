<?php
/*
Template Name: Used Books Template
*/
$id = (int)get_query_var('id');
if($id){
    global $wpdb;
    $book = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_books` WHERE `id` = $id");
    if(!$book){
        status_header( 404 );
        return get_template_part(404);
    }
    //详细内容的 seo 内容
    add_filter('pre_get_document_title', function () use ($book, $post) {
        return "$book->name &#8211; $post->post_title &#8211; 文硕阁";
    });
}
get_header();?>

<style>
    .used-books-list {
        display:flex;flex-wrap: wrap;text-align: center;
    }
    .item {
        width: 25%;
        min-width: 150px;
        padding: 10px;
    }
    .item img{
        width: 100%;
    }
    .item h3{
        font-size: 14px;
        line-height: 28px;
        height: 28px;
        overflow:hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .buyBox{
        display: flex;
        flex-wrap: wrap; 
    }
    .buyBox .gallery{
        width: 35%;
    }

    .buyBox .gallery img{
        max-width: 100%;
        height: auto;
    }


    .buyBox .summary{
        width: 65%;
        padding-left: 24px;
    }

    .buyBox .summary ul{
        padding-left: 16px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    }

    .buyBox .summary .title{
        font-size: 24px;
    }
    .buyBox .summary .buttons{
        display: flex;
        justify-content: center;
    }
    @media (max-width: 690px){
        .buyBox .summary{
            padding: 20px 0 0 0;
        }
        .buyBox .gallery,.buyBox .summary{
            width: 100%;
        }

        .used-books-list .item {
            width: 50%;
        }

    }
</style>
<div class="ct-container-full" data-content="narrow" data-vertical-spacing="top:bottom">
    <article class=" format-standard hentry category-uncategorized">
        <div class="hero-section" data-type="type-1">
            <header class="entry-header" style="display: flex;">
                <h1 class="page-title" title="<?=$post->post_title;?>" itemprop="headline"><?=$post->post_title;?></h1>

                <?php if(is_user_logged_in()){ ?>
                    <span style="line-height: 30px;margin: 0;padding: 8px 20px;">[<a href="/used-orders/">我的订单</a>]</span>
                <?php } ?>
                
            </header>
        </div>
        <div class="entry-content">
            <?php 
            if($id){
                used_books_show_card($book);
            }else{
                used_books_list_card();
            }
             ?>
		</div>
    </article>
</div>
<?php
get_footer();
