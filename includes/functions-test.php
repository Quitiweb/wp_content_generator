<?php

function wp_content_generatorTest(){
    include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'admin/template/wp_content_generator-test.php');
}

/**
* Main function that creates the Posts 
*/
function wp_content_generatorGenerateTest(
    array $categories,
    $category='software',
    $post_user=1,
    $asin='',
    $wp_content_generatorIsThumbnail='off',
    $wp_content_generatorIsTaxonomies='off',
    $postDateFrom='',
    $postDateTo=''
){
    $posttype = 'post';
    if($postDateFrom == ''){
        $postDateFrom = date("Y-m-d");
    }
    if($postDateTo == ''){
        $postDateTo = date("Y-m-d");
    }
    // $host = 'http://ec2-15-188-189-171.eu-west-3.compute.amazonaws.com';
    $host = 'http://127.0.0.1:8000';
    // $host = 'https://post.quitiweb.com';

    // URL de la API que devuelve el JSON con los datos de la entrada
    // Llamamos al endpoint de Amazon si viene el ASIN del formulario
    if ($asin === null || trim($asin) === ''){
        $base_url = sprintf("%s/%s", $host, 'post/generate/');
        $api_url = sprintf("%s?%s", $base_url, http_build_query(array("category" => $category)));
    }else{
        $base_url = sprintf("%s/%s", $host, 'post/aws/');
        $api_url = sprintf("%s?%s", $base_url, http_build_query(array("category" => $category)));
        $api_url .= sprintf("&asin=%s", $asin);
    }

    // Recupera el Token para autorizar las llamadas a la API
    $api_key = get_option('wp_content_generator_api_key');
    $authorization = "Authorization: Bearer " . $api_key;

    // Obtiene los datos de la API en formato JSON
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Decodifica los datos JSON obtenidos
    $data = json_decode($response, true);
    if ($data['title'] ?? null){
        return 'error';
    }
    $title = $data['title'];
    $description = $data['description'];

    $wp_content_generatorPostTitle = $title;
    $wp_content_generatorPostDescription = $description;

    // Genera la imagen principal del post
    $rand_num = rand(1,15);
    $wp_content_generatorPostThumb = WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'images/posts/'.$rand_num.".jpg";

    // Create post
    $postDate = wp_content_generatorRandomDate($postDateFrom,$postDateTo);
    $wp_content_generatorPostArray = array(
      'post_title' => wp_strip_all_tags( $wp_content_generatorPostTitle ),
      'post_content' => $wp_content_generatorPostDescription,
      'post_status' => 'private',
      'post_author' => $post_user,
      'post_date' => $postDate,
      'post_type' => $posttype,
      'post_category' => $categories
    );

    // Insert the post into the database
    $wp_content_generatorPostID = wp_insert_post( $wp_content_generatorPostArray );
    if($wp_content_generatorPostID){
        update_post_meta($wp_content_generatorPostID, 'wp_content_generator_post','true');
        return 'success';
    }else{
        return 'error';
    }

}

function wp_content_generatorAjaxTest () {
    if ( !current_user_can('manage_options') || !wp_verify_nonce( $_POST['nonce'], 'wpdcg-ajax-nonce' ) ) {
        echo json_encode(array('status' => 'error', 'message' => 'Unauthorized Access.') );
        die();
    }
    $wp_content_generatorIsThumbnail = 'off';
    $wp_content_generatorIsTaxonomies = 'off';
    $category = sanitize_text_field($_POST['wp_content_generator-category']);
    $categories = $_POST['wp_content_generator-categories'];
    $post_user = sanitize_text_field($_POST['wp_content_generator-user']);
    $remaining_posts = sanitize_text_field($_POST['remaining_posts']);
    $post_count = sanitize_text_field($_POST['wp_content_generator-post_count']);

    if($remaining_posts>=2){
        $loopLimit = 2;
    }else{
        $loopLimit = $remaining_posts;
    }

    $postFromDate = sanitize_text_field($_POST['wp_content_generator-post_from']);
    $postToDate = sanitize_text_field($_POST['wp_content_generator-post_to']);

    for ($i=0; $i < $loopLimit ; $i++) {
        $asin = '';
        $generationStatus = wp_content_generatorGenerateTest(
            $categories,
            $category,
            $post_user,
            $asin,
            $wp_content_generatorIsThumbnail,
            $wp_content_generatorIsTaxonomies,
            $postFromDate,
            $postToDate
        );
    }
    if($remaining_posts>=2){
        $remaining_posts = $remaining_posts - 2;
    }else{
        $remaining_posts = 0;
    }
    echo json_encode(array('status' => 'success', 'message' => 'Test Posts generated successfully.', 'remaining_posts' => $remaining_posts));
    die();
}

add_action("wp_ajax_wp_content_generatorAjaxGenTest", "wp_content_generatorAjaxTest");
