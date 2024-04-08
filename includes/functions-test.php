<?php

function wp_content_generatorTest(){
    include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'admin/template/wp_content_generator-test.php');
}

function wp_content_generatorAjaxTest () {
    if ( !current_user_can('manage_options') || !wp_verify_nonce( $_POST['nonce'], 'wpdcg-ajax-nonce' ) ) {
        echo json_encode(array('status' => 'error', 'message' => 'Unauthorized Access.') );
        die();
    }
    $remaining_posts = sanitize_text_field($_POST['remaining_posts']);

    if($remaining_posts>=1){
        sleep(3);  // Simulates the function call
        $remaining_posts = $remaining_posts - 1;
    }

    echo json_encode(array('status' => 'success', 'message' => 'Test Posts generated successfully.', 'remaining_posts' => $remaining_posts));
    die();
}

add_action("wp_ajax_wp_content_generatorAjaxTest", "wp_content_generatorAjaxTest");
