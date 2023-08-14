<?php

function wp_content_generatorPosts(){
    include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'admin/template/wp_content_generator-posts.php');
}

function wp_content_generatorGetPostTypes(){
    $args=array(
        'public'                => true,
        'exclude_from_search'   => false,
        '_builtin'              => false
    ); 
    $output = 'names'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'
    $regPostTypes = get_post_types($args,$output,$operator);
    $posttypes_array = array();
    $posttypes_array['post'] = 'Posts';
    foreach ($regPostTypes  as $post_type ) {
        $wp_content_generator_pt = get_post_type_object( $post_type );
        $wp_content_generator_pt_name = $wp_content_generator_pt->labels->name;
        $posttypes_array[$post_type] = $wp_content_generator_pt_name;
    }
    if ( class_exists( 'WooCommerce' ) ) {
        unset($posttypes_array['product']); //exclude 'product' post type as we are providing separate section for products
    } 
    return $posttypes_array;
}

function wp_content_generator_Generate_TaxTerms( $wp_content_generatorPostID,$posttype){
    $taxonomies = wp_content_generatorGetTaxonomies($posttype);
    if(!empty($taxonomies)){
        foreach ($taxonomies as $taxonomieskey => $taxonomiesvalue) {
            $terms = get_terms( array(
                'taxonomy' => $taxonomiesvalue,
                'hide_empty' => false,
            ) );
            if(!empty($terms) && (sizeof($terms)>=5)){
                // no need to generate terms. Use the existing terms and assign to the post
                // Randomize Term Array
                shuffle( $terms );
                $random_terms = array_slice( $terms, 0, 1 );
                $termID = array($random_terms[0]->term_id);
                wp_set_post_terms( $wp_content_generatorPostID, $termID, $taxonomiesvalue );
            }else{
                wp_content_generator_generateFiveTerms($taxonomiesvalue);
                $terms = get_terms( array(
                    'taxonomy' => $taxonomiesvalue,
                    'hide_empty' => false,
                ) );
                shuffle( $terms );
                $random_terms = array_slice( $terms, 0, 1 );
                $termID = array($random_terms[0]->term_id);
                wp_set_post_terms( $wp_content_generatorPostID, $termID, $taxonomiesvalue );
            }
        }
    }
}

function wp_content_generator_generateFiveTerms($taxonomiesvalue){
    // $faker->words(5);
    include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'Faker-main/vendor/autoload.php');
    $wp_content_generatorFaker = Faker\Factory::create();
    $dummyTermNamesArr = $wp_content_generatorFaker->words(5);
    foreach ($dummyTermNamesArr as $dummyTermNamesArr_key => $dummyTermNamesArr_value) {
        wp_insert_term($dummyTermNamesArr_value,$taxonomiesvalue);
    }
}

function wp_content_generatorGetTaxonomies($post_type='post'){
    $args = array(
        'object_type' => array($post_type)
      ); 
    $output = 'names'; // or objects
    $operator = 'and'; // 'and' or 'or'
    $taxonomies = get_taxonomies( $args, $output, $operator );
    if(isset($taxonomies['post_format'])){
        unset($taxonomies['post_format']);
    } 
    return $taxonomies;
}

function wp_content_generatorGeneratePosts(
    $posttype='post',
    $wp_content_generatorIsThumbnail='off',
    $wp_content_generatorIsTaxonomies='off',
    $postDateFrom='',
    $postDateTo=''
){

    if($postDateFrom == ''){
        $postDateFrom = date("Y-m-d");
    }
    if($postDateTo == ''){
        $postDateTo = date("Y-m-d");
    }

    // include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'Faker-main/vendor/autoload.php');
    
    // use the factory to create a Faker\Generator instance
    // $wp_content_generatorFaker = Faker\Factory::create();

    $wp_content_generatorPostTitle = "Test Title v1";
    $wp_content_generatorPostDescription = "<html><head></head><body><h2><strong>¿Qué es la arqueología?</strong></h2><p>La <em>arqueología</em> es una ciencia social que estudia los cambios que se han producido en sociedades pasadas a través del análisis de lo que estas han dejado atrás, principalmente restos materiales como herramientas, cerámica, edificios, restos humanos, etc.</p></body></html>";
    // $wp_content_generatorPostDescription .= $wp_content_generatorFaker->text($maxNbChars = 700);
    $rand_num = rand(1,15);
    $wp_content_generatorPostThumb = WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'images/posts/'.$rand_num.".jpg";

    // create post
    $postDate = wp_content_generatorRandomDate($postDateFrom,$postDateTo);
    $wp_content_generatorPostArray = array(
      'post_title'    => wp_strip_all_tags( $wp_content_generatorPostTitle ),
      'post_content'  => $wp_content_generatorPostDescription,
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_date'   => $postDate,
      'post_type' => $posttype
    );

    // insert the post into the database
    $wp_content_generatorPostID = wp_insert_post( $wp_content_generatorPostArray );
    if($wp_content_generatorPostID){
        update_post_meta($wp_content_generatorPostID,'wp_content_generator_post','true');
        if($wp_content_generatorIsThumbnail=='on')
        wp_content_generator_Generate_Featured_Image( $wp_content_generatorPostThumb,$wp_content_generatorPostID);
        if($wp_content_generatorIsTaxonomies=='on')
        wp_content_generator_Generate_TaxTerms( $wp_content_generatorPostID,$posttype);
        return 'success';
    }else{
        return 'error';
    }

}

function wp_content_generator_Generate_Featured_Image( $image_url, $post_id ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = "wp_content_generator_".$post_id.".jpg";
    if(wp_mkdir_p($upload_dir['path'])){
        $file = $upload_dir['path'] . '/' . $filename;
    }
    else{
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents($file, $image_data);
    $wp_filetype = wp_check_filetype($filename, null ); 
    $attachment = array(
        'post_mime_type' => 'image/jpg',
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    update_post_meta($attach_id, 'wp_content_generator_attachment','true');
    $res2= set_post_thumbnail( $post_id, $attach_id );
}


function wp_content_generatorAjaxGenPosts () {
    if ( !current_user_can('manage_options') || !wp_verify_nonce( $_POST['nonce'], 'wpdcg-ajax-nonce' ) ) {
        echo json_encode(array('status' => 'error', 'message' => 'Un Authorized Access.') );
        die();
    }
    $wp_content_generatorIsThumbnail = 'off';
    $post_type = sanitize_text_field($_POST['wp_content_generator-posttype']);
    $remaining_posts = sanitize_text_field($_POST['remaining_posts']);
    $post_count = sanitize_text_field($_POST['wp_content_generator-post_count']);

    if($remaining_posts>=2){
        $loopLimit = 2;
    }else{
        $loopLimit = $remaining_posts;
    }


    $wp_content_generatorIsThumbnail = sanitize_text_field($_POST['wp_content_generator-thumbnail']);
    $wp_content_generatorIsTaxonomies = sanitize_text_field($_POST['wp_content_generator-taxonomies']);

    $postFromDate = sanitize_text_field($_POST['wp_content_generator-post_from']);
    $postToDate = sanitize_text_field($_POST['wp_content_generator-post_to']);
    $counter = 0;
    for ($i=0; $i < $loopLimit ; $i++) { 
        $generationStatus = wp_content_generatorGeneratePosts($post_type,$wp_content_generatorIsThumbnail,$wp_content_generatorIsTaxonomies,$postFromDate,$postToDate);
        if($generationStatus == 'success'){
            $counter++;
        }
    }
    if($remaining_posts>=2){
        $remaining_posts = $remaining_posts - 2;
    }else{
        $remaining_posts = 0;
    }
    echo json_encode(array('status' => 'success', 'message' => 'Posts generated successfully.','remaining_posts' => $remaining_posts) );
    die();
}

add_action("wp_ajax_wp_content_generatorAjaxGenPosts", "wp_content_generatorAjaxGenPosts");

function wp_content_generatorGetFakePostsList(){
    $postsArr = wp_content_generatorGetPostTypes();
    $allPostTypes = array();
    foreach ($postsArr as $key => $value) {
        array_push($allPostTypes, $key);
    }
    $args = array(
        'posts_per_page' => -1,
        'post_type' => $allPostTypes,
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => 'wp_content_generator_post',
                'value' => 'true',
                'compare' => '='
            ),
        )
    );
    $wp_content_generatorQueryData = new WP_Query( $args );
    return $wp_content_generatorQueryData;
}

function wp_content_generatorDeleteFakePosts(){
    $wp_content_generatorQueryData = wp_content_generatorGetFakePostsList();
    if ($wp_content_generatorQueryData->have_posts()) {
        while ( $wp_content_generatorQueryData->have_posts() ) :
            $wp_content_generatorQueryData->the_post();
            wp_delete_post(get_the_ID());
        endwhile;
    }
    wp_reset_postdata();
}

function wp_content_generatorDeletePosts () {
    if ( !current_user_can('manage_options') || ! wp_verify_nonce( $_POST['nonce'], 'wpdcg-ajax-nonce' ) ) {
        echo json_encode(array('status' => 'error', 'message' => 'Un Authorized Access.') );
    die();
    }
    wp_content_generatorDeleteFakePosts();
    echo json_encode(array('status' => 'success', 'message' => 'Data deleted successfully.') );
    die();
}

add_action("wp_ajax_wp_content_generatorDeletePosts", "wp_content_generatorDeletePosts");

/**
* Action hook to delete posts 
*/
add_action('admin_post_wp_content_generator_deleteposts', 'wp_content_generator_deleteposts');

function wp_content_generator_deleteposts(){
    $request  = $_REQUEST;
    if ( !current_user_can('manage_options') || ! wp_verify_nonce( $request['nonce'], 'wpdcg-ajax-nonce' ) ) {
        wp_redirect("admin.php?page=wp_content_generator-posts&tab=view_posts&status=error");
    }
    wp_content_generatorDeleteFakePosts();
    wp_redirect("admin.php?page=wp_content_generator-posts&tab=view_posts&status=success");
}
