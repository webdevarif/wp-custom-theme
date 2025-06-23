<?php
    /*
        Template Name: Full Width
    */
    get_header();
?>
<main id="primary" class="site-main w-full">
    <div class="container mx-auto px-4 py-8">
        <?php
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
</main>
<?php
    get_footer();
?>
