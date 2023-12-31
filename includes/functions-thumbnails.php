<?php

function wp_content_generatorThumbnails(){
    include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'admin/template/wp_content_generator-thumbnails.php');
}

function wp_content_generatorGetFakeThumbnailsList(){
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'attachment',
        'order' => 'DESC',
        'post_status' => 'inherit',
        'meta_query' => array(
            array(
                'key' => 'wp_content_generator_attachment',
                'value' => 'true',
                'compare' => '='
            ),
        )
    );
    $wp_content_generatorQueryData = new WP_Query( $args );
    return $wp_content_generatorQueryData;
}

// wp_content_generatorDeleteFakeThumbnails
function wp_content_generatorDeleteFakeThumbnails(){
    $wp_content_generatorQueryData = wp_content_generatorGetFakeThumbnailsList();
    if ($wp_content_generatorQueryData->have_posts()) {
        while ( $wp_content_generatorQueryData->have_posts() ) :
            $wp_content_generatorQueryData->the_post();
            wp_delete_post(get_the_ID());
        endwhile;
    }
    wp_reset_postdata();
}

function wp_content_generatorDeleteThumbnails () {
    if ( !current_user_can('manage_options') || ! wp_verify_nonce( $_POST['nonce'], 'wpdcg-ajax-nonce' ) ) {
        echo json_encode(array('status' => 'error', 'message' => 'Un Authorized Access.') );
    die();
    }
    wp_content_generatorDeleteFakeThumbnails();
    echo json_encode(array('status' => 'success', 'message' => 'Data deleted successfully.') );
    die();
}

add_action("wp_ajax_wp_content_generatorDeleteThumbnails", "wp_content_generatorDeleteThumbnails");

/**
* Action hook to delete thumbnails 
*/
add_action('admin_post_wp_content_generator_deletethumbnails', 'wp_content_generator_deletethumbnails');

function wp_content_generator_deletethumbnails(){
    $request  = $_REQUEST;
    if ( !current_user_can('manage_options') || ! wp_verify_nonce( $request['nonce'], 'wpdcg-ajax-nonce' ) ) {
        wp_redirect("admin.php?page=wp_content_generator-thumbnails&status=error");
    }
    wp_content_generatorDeleteFakeThumbnails();
    wp_redirect("admin.php?page=wp_content_generator-thumbnails&status=success");
}
