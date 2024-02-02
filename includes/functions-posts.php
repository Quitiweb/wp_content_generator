<?php

function debug_to_console($data){
    $output = $data;
    if (is_array($output)){
        $output = implode(',', $output);
	}
    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function slugify($text, string $divider = '-'){
    // replace non letter or digits by divider
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, $divider);

    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

function wp_content_generatorPosts(){
    include( WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'admin/template/wp_content_generator-posts.php');
}

function wp_content_generatorGetUsers(){
    $users_array = array();
    $users = get_users(array(
        'fields' => array('ID', 'display_name'), // Obtener solo el ID y el nombre para mostrar de cada usuario
    ));
    foreach($users as $user) {
        $users_array[$user->ID] = $user->display_name;
    }
    
    return $users_array;
}

function wp_content_generatorGetCategory(){
    $posttypes_array = array();
	$categories = get_categories(array(
		'hide_empty' => 0,
	));
	foreach($categories as $cat) {
		$sname = slugify($cat->name);
		$posttypes_array[$sname] = $cat->name;
	}
	
    return $posttypes_array;
}

function wp_content_generatorGetCategories(){
	$categories = get_categories(array(
        'type'        => 'post',
        'child_of'    => 0,
        'parent'      => '',
        'orderby'     => 'name',
        'order'       => 'ASC',
        'hide_empty'  => false,
        'hierarchical'=> 1,
        'exclude'     => '',
        'include'     => '',
        'number'      => '',
        'taxonomy'    => 'category',
        'pad_counts'  => false
	));
	
    return $categories;
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
/*
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
*/
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

/**
* Main function that creates the Posts 
*/
function wp_content_generatorGeneratePosts(
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
    $host_aws = 'http://ec2-15-188-189-171.eu-west-3.compute.amazonaws.com';
    $host_dh = 'https://post.quitiweb.com';

    // URL de la API que devuelve el JSON con los datos de la entrada
    // Llamamos al endpoint de Amazon si viene el ASIN del formulario
    if ($asin === null || trim($asin) === ''){
        $base_url = sprintf("%s/%s", $host_aws, 'post/generate/');
        $api_url = sprintf("%s?%s", $base_url, http_build_query(array("category" => $category)));
    }else{
        $base_url = sprintf("%s/%s", $host_aws, 'post/aws/');
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
        /*
        if($wp_content_generatorIsThumbnail=='on')
            wp_content_generator_Generate_Featured_Image( $wp_content_generatorPostThumb, $wp_content_generatorPostID );
        if($wp_content_generatorIsTaxonomies=='on')
            wp_content_generator_Generate_TaxTerms( $wp_content_generatorPostID, $posttype );
        */
        return 'success';
    }else{
        return 'error';
    }

}
/*
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
    $res1 = wp_update_attachment_metadata( $attach_id, $attach_data );
    update_post_meta($attach_id, 'wp_content_generator_attachment','true');
    $res2 = set_post_thumbnail( $post_id, $attach_id );
}
*/

function wp_content_generatorAjaxGenPosts () {
    if ( !current_user_can('manage_options') || !wp_verify_nonce( $_POST['nonce'], 'wpdcg-ajax-nonce' ) ) {
        echo json_encode(array('status' => 'error', 'message' => 'Unauthorized Access.') );
        die();
    }
    $wp_content_generatorIsThumbnail = 'off';
    $wp_content_generatorIsTaxonomies = 'off';
    $category = sanitize_text_field($_POST['wp_content_generator-category']);
    $categories = $_POST['wp_content_generator-categories'];
    $post_user = sanitize_text_field($_POST['wp_content_generator-user']);
    $remaining_asins = sanitize_text_field($_POST['remaining_asins']);
    $remaining_posts = sanitize_text_field($_POST['remaining_posts']);
    $post_count = sanitize_text_field($_POST['wp_content_generator-post_count']);

    if($remaining_posts>=2){
        $loopLimit = 2;
    }else{
        $loopLimit = $remaining_posts;
    }

    // $wp_content_generatorIsThumbnail = sanitize_text_field($_POST['wp_content_generator-thumbnail']);
    // $wp_content_generatorIsTaxonomies = sanitize_text_field($_POST['wp_content_generator-taxonomies']);

    $postFromDate = sanitize_text_field($_POST['wp_content_generator-post_from']);
    $postToDate = sanitize_text_field($_POST['wp_content_generator-post_to']);

    for ($i=0; $i < $loopLimit ; $i++) {
        if ($remaining_asins === null || trim($remaining_asins) === ''){
            $asin = '';
        }else{
            $asins_array = explode(' ', $remaining_asins);
            $asin = current($asins_array);
            $asin_key = array_search($asin, $asins_array);
            unset($asins_array[$asin_key]);
            $remaining_asins = implode(" ", $asins_array);
        }
        $generationStatus = wp_content_generatorGeneratePosts(
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
    echo json_encode(array('status' => 'success', 'message' => 'Posts generated successfully.', 'remaining_posts' => $remaining_posts, 'remaining_asins' => $remaining_asins));
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

/**
 * Action hook to save API Key
 */
add_action( 'admin_post_save_wp_content_generator_api_key', 'save_wp_content_generator_api_key' );

function save_wp_content_generator_api_key() {
    if ( isset( $_POST['wp_content_generator_api_key'] ) ) {
        // Guarda la clave API en la base de datos
        update_option( 'wp_content_generator_api_key', sanitize_text_field( $_POST['wp_content_generator_api_key'] ) );
    }
    // Redirige de vuelta a la página de administración
    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit;
}
