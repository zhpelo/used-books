<?php
/*
Template Name: Used Books Template
*/
$id =  get_query_var('id');
if($id){
    //详细内容的 seo 内容
}
get_header();?>

<style>
    .used-books-list {
        display:flex;flex-wrap: wrap;text-align: center;
    }
    .item {
        width: 150px;
        padding: 10px;
    }
    .item img{
        width: 130px;
        aspect-ratio: 3/4;
    }
    .item h3{
        font-size: 14px;
        line-height: 28px;
        height: 28px;
        overflow:hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
<div class="ct-container-full" data-content="narrow" data-vertical-spacing="top:bottom">
    <article class=" format-standard hentry category-uncategorized">
        <div class="hero-section">
            <?php 
            if($id){
                used_books_show_card($id);
            }else{
                used_books_list_card();
            }
             ?>
        </div>
    </article>
</div>
<?php
get_footer();
