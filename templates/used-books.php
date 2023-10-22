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
        display: grid;
        grid-template-columns: repeat(6, 16%);
        justify-items: center;
        justify-content: space-between;
        margin: 0 -10px;
    }
    .item img{
        width: 100%;
        box-shadow: 3px 3px 20px #adadad;
        border-radius: 5px;
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
