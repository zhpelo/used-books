<?php
/*
Template Name: Used Books Template
*/
$id = (int)get_query_var('id');
if ($id) {
    global $wpdb;
    $book = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}used_books` WHERE `id` = $id");
    if (!$book) {
        status_header(404);
        return get_template_part(404);
    }
    //详细内容的 seo 内容
    add_filter('pre_get_document_title', function () use ($book, $post) {
        return "$book->name &#8211; $post->post_title &#8211; 文硕阁";
    });
}

get_header(); ?>

<style>
    .used-books-list {
        display: flex;
        flex-wrap: wrap;
        text-align: center;
        margin-left: -10px !important;
        margin-right: -10px !important;
        width: auto !important;
        max-width: none !important;
    }

    .item {
        width: 25%;
        min-width: 150px;
        padding: 10px;
    }

    .item img {
        width: 100%;
    }

    .item h3 {
        font-size: 14px;
        line-height: 28px;
        height: 28px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .buyBox {
        display: flex;
        flex-wrap: wrap;
    }

    .buyBox .gallery {
        width: 35%;
    }

    .buyBox .gallery img {
        max-width: 100%;
        height: auto;
    }


    .buyBox .summary {
        width: 65%;
        padding-left: 24px;
    }

    .buyBox .summary ul {
        font-size: 16px;
        line-height: 26px;
        padding-left: 18px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    }

    .buyBox .summary .title {
        font-size: 20px;
        line-height: 36px;
        margin: 0;
    }
    .buyBox .summary .point {
        color: crimson;
        font-size: 14px;
        margin: 0;
    }

    .buyBox .summary .buttons {
        display: flex;
        justify-content: center;
    }

    @media (max-width: 690px) {
        .buyBox .summary {
            padding: 20px 0 0 0;
        }

        .buyBox .gallery,
        .buyBox .summary {
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
                <h1 class="page-title" title="<?= $post->post_title; ?>" itemprop="headline"><?= $post->post_title; ?></h1>

                <?php if (is_user_logged_in()) { ?>
                    <span style="line-height: 30px;margin: 0;padding: 8px 20px;">[<a href="/used-orders/">我的订单</a>]</span>
                <?php } ?>

            </header>
        </div>
        <div class="entry-content">

            <div class="ct-widget widget_block widget_search" id="block-2">
                <form role="search" method="get" action="/used-books/" class="wp-block-search__button-inside wp-block-search__icon-button wp-block-search">
                    <label class="wp-block-search__label screen-reader-text" for="wp-block-search__input-1">搜索</label>
                    <div class="wp-block-search__inside-wrapper ">
                        
                        <input class="wp-block-search__input" id="wp-block-search__input-1" placeholder="请输入书名" value="" type="search" name="q" required="">
                    
                        <button aria-label="搜索" class="wp-block-search__button has-icon wp-element-button" type="submit">
                            <svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">
                                <path d="M13 5c-3.3 0-6 2.7-6 6 0 1.4.5 2.7 1.3 3.7l-3.8 3.8 1.1 1.1 3.8-3.8c1 .8 2.3 1.3 3.7 1.3 3.3 0 6-2.7 6-6S16.3 5 13 5zm0 10.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <?php
            if ($id) {
                used_books_show_card($book);
            } else {
                used_books_list_card();
            }
            ?>
        </div>
    </article>
</div>
<?php
get_footer();
