<?php

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
        unset($posttypes_array['product']); // exclude 'product' post type as we are providing separate section for products
    }
    return $posttypes_array;
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

/**
 * API Calls general function
 */
function callAPI($url, $category, $asin) {
    try {
        if (empty($url)) {
            throw new Exception('API URL is not configured');
        }

        // Si la URL contiene "aws", solo incluimos el ASIN
        if (strpos($url, '/aws/') !== false) {
            $api_url = $url . '?' . http_build_query(array('asin' => $asin));
        } else {
            // Para otras llamadas, incluimos la categoría
            $api_url = sprintf("%s?%s", $url, http_build_query(array("category" => $category)));
            if($asin) {
                $api_url .= sprintf("&asin=%s", $asin);
            }
        }
        
        error_log('API URL being called: ' . $api_url);

        $api_key = get_option('wp_content_generator_api_key');
        if (empty($api_key)) {
            throw new Exception('API key is not configured');
        }

        $authorization = "Authorization: Bearer " . $api_key;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($authorization, 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        
        if ($result === false) {
            throw new Exception('cURL Error: ' . curl_error($curl));
        }
        
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            throw new Exception('API returned HTTP code ' . $httpCode);
        }
        
        curl_close($curl);
        return $result;
        
    } catch (Exception $e) {
        error_log('API Call Error: ' . $e->getMessage());
        return json_encode(array(
            'error' => true,
            'message' => $e->getMessage()
        ));
    }
 }

/**
* Main function that creates the Standard Posts 
*/
function wp_content_generatorGeneratePosts(
    array $categories,
    $category='software',
    $post_user=1,
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
    $host = get_option('wp_content_generator_api_url');
    $base_url = sprintf("%s/%s", $host, 'post/generate/');
    $get_data = callAPI($base_url, $category, false);

    // Decodifica los datos JSON obtenidos
    $data = json_decode($get_data, true);
    $title = $data['title'];
    $description = $data['description'];

    // Genera la imagen principal del post
    // $rand_num = rand(1,15);
    // $wp_content_generatorPostThumb = WP_PLUGIN_DIR.'/'.plugin_dir_path(wp_content_generator_PLUGIN_BASE_URL) . 'images/posts/'.$rand_num.".jpg";

    // Create post
    $postDate = wp_content_generatorRandomDate($postDateFrom, $postDateTo);
    $wp_content_generatorPostArray = array(
      'post_title' => wp_strip_all_tags( $title ),
      'post_content' => $description,
      'post_status' => 'private',
      'post_author' => $post_user,
      'post_date' => $postDate,
      'post_type' => $posttype,
      'post_category' => $categories
    );

    // Insert the post into the database
    $wp_content_generatorPostID = wp_insert_post( $wp_content_generatorPostArray );
    if($wp_content_generatorPostID){
        update_post_meta($wp_content_generatorPostID, 'wp_content_generator_post', 'true');
        return 'success';
    }else{
        return 'error';
    }
}

/**
* Main function that creates the AWS Posts 
*/
function wp_content_generatorGenerateAWSPosts(
    array $categories,
    $category='software', // Mantener este parámetro ya que se usa para la categoría principal
    $post_user=1,
    $asin='',
    $postDateFrom='',
    $postDateTo=''
){
    if (empty($asin)) {
        return 'error: No ASIN provided';
    }

    $posttype = 'post';
    if($postDateFrom == ''){
        $postDateFrom = date("Y-m-d");
    }
    if($postDateTo == ''){
        $postDateTo = date("Y-m-d");
    }
    $host = get_option('wp_content_generator_api_url');
    $base_url = sprintf("%s/%s", $host, 'post/aws/');
    sleep(2); // Añadimos una pequeña pausa entre llamadas
    $get_data = callAPI($base_url, $category, $asin);
    if (!$get_data) {
        return 'error: API call failed for ASIN: ' . $asin;
    }

    // Decodifica los datos JSON obtenidos
    $data = json_decode($get_data, true);
    if (!$get_data || json_last_error() !== JSON_ERROR_NONE) {
        return 'error: Failed to decode API response for ASIN: ' . $asin;
    }
    if (isset($data['error'])) {
        return 'error: API returned error for ASIN ' . $asin . ': ' . $data['message'];
    }
    $title = $data['title'];
    $description = $data['description'];

    // Asegurarnos de que la categoría principal esté incluida
    $mainCategoryId = get_cat_ID($category);
    if ($mainCategoryId && !in_array($mainCategoryId, $categories)) {
        array_push($categories, $mainCategoryId);
    }

    // Create post
    $postDate = wp_content_generatorRandomDate($postDateFrom,$postDateTo);
    $wp_content_generatorPostArray = array(
      'post_title' => wp_strip_all_tags( $title ),
      'post_content' => $description,
      'post_status' => 'private',
      'post_author' => $post_user,
      'post_date' => $postDate,
      'post_type' => $posttype,
      'post_category' => $categories
    );

    // Insert the post into the database
    $wp_content_generatorPostID = wp_insert_post( $wp_content_generatorPostArray );
    if($wp_content_generatorPostID){
        update_post_meta($wp_content_generatorPostID, 'wp_content_generator_post', 'true');
        return 'success';
    }else{
        return 'error';
    }
}

/**
 * The AJAX function for the Standard Post Generator
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
    $remaining_posts = sanitize_text_field($_POST['remaining_posts']);

    if($remaining_posts>=1){
        // $wp_content_generatorIsThumbnail = sanitize_text_field($_POST['wp_content_generator-thumbnail']);
        // $wp_content_generatorIsTaxonomies = sanitize_text_field($_POST['wp_content_generator-taxonomies']);

        $postFromDate = sanitize_text_field($_POST['wp_content_generator-post_from']);
        $postToDate = sanitize_text_field($_POST['wp_content_generator-post_to']);

        $generationStatus = wp_content_generatorGeneratePosts(
            $categories,
            $category,
            $post_user,
            $wp_content_generatorIsThumbnail,
            $wp_content_generatorIsTaxonomies,
            $postFromDate,
            $postToDate
        );

        $remaining_posts = $remaining_posts - 1;
    }

    echo json_encode(array('status' => 'success', 'message' => 'Posts generated successfully.', 'remaining_posts' => $remaining_posts));
    die();
}

add_action("wp_ajax_wp_content_generatorAjaxGenPosts", "wp_content_generatorAjaxGenPosts");

/**
 * The AJAX function for the AWS Post Generator
 */
function wp_content_generatorAjaxGenAWSPosts() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'wpdcg-ajax-nonce')) {
        echo json_encode(array('status' => 'error', 'message' => 'Unauthorized Access.'));
        die();
    }

    try {
        // Inicializar variables
        $current_asin = '';
        
        $category = sanitize_text_field($_POST['wp_content_generator-category']);
        $categories = isset($_POST['wp_content_generator-categories']) ? $_POST['wp_content_generator-categories'] : array();
        $post_user = sanitize_text_field($_POST['wp_content_generator-user']);
        $remaining_asins = sanitize_text_field($_POST['remaining_asins']);

        // Validar y procesar ASINs
        $asins_array = array_filter(preg_split('/\s+/', trim($remaining_asins)), 'strlen');
        
        if (empty($asins_array)) {
            throw new Exception('No ASINs provided');
        }

        // NO actualizamos remaining_asins todavía
        $current_asin = $asins_array[0]; // Obtenemos el ASIN actual sin eliminarlo
        
        $postFromDate = sanitize_text_field($_POST['wp_content_generator-post_from']);
        $postToDate = sanitize_text_field($_POST['wp_content_generator-post_to']);

        // Procesar el ASIN actual
        $generationStatus = wp_content_generatorGenerateAWSPosts(
            $categories,
            $category,
            $post_user,
            $current_asin,
            $postFromDate,
            $postToDate
        );

        if (strpos($generationStatus, 'error:') === 0) {
            throw new Exception(substr($generationStatus, 6));
        }

        // Solo DESPUÉS de procesar exitosamente, actualizamos la lista de ASINs restantes
        array_shift($asins_array); // Ahora sí eliminamos el ASIN procesado
        $remaining_asins = implode(" ", $asins_array);
        $remaining_posts = count($asins_array);

        error_log('Successfully processed ASIN: ' . $current_asin);
        error_log('Remaining ASINs: ' . $remaining_asins);
        error_log('Remaining count: ' . $remaining_posts);

        // Preparar respuesta
        $response = array(
            'status' => 'success',
            'message' => 'Post for ASIN ' . $current_asin . ' generated successfully.',
            'remaining_posts' => $remaining_posts,
            'remaining_asins' => $remaining_asins,
            'current_asin' => $current_asin,
            'debug' => array(
                'processed_asin' => $current_asin,
                'remaining_count' => $remaining_posts,
                'remaining_asins' => $asins_array
            )
        );

        echo json_encode($response);

    } catch (Exception $e) {
        error_log('WP Content Generator Error: ' . $e->getMessage());
        echo json_encode(array(
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug' => array(
                'error' => $e->getMessage(),
                'current_asin' => $current_asin,
                'trace' => $e->getTraceAsString()
            )
        ));
    }
    die();
}

add_action("wp_ajax_wp_content_generatorAjaxGenAWSPosts", "wp_content_generatorAjaxGenAWSPosts");

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

/**
 * Action hook to save API Url
 */
add_action( 'admin_post_save_wp_content_generator_api_url', 'save_wp_content_generator_api_url' );

function save_wp_content_generator_api_url() {
    if ( isset( $_POST['wp_content_generator_api_url'] ) ) {
        // Guarda la url de la API en la base de datos
        update_option( 'wp_content_generator_api_url', sanitize_text_field( $_POST['wp_content_generator_api_url'] ) );
    }
    // Redirige de vuelta a la página de administración
    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit;
}

/**
 * Generate a random date between two dates
 *
 * @param string $postDateFrom Starting date
 * @param string $postDateTo Ending date
 * @return string Date in Y-m-d H:i:s format
 */
function wp_content_generatorRandomDate($postDateFrom, $postDateTo, $format = 'Y-m-d H:i:s') {
    $start = strtotime($postDateFrom);
    $end = strtotime($postDateTo);
    $timestamp = mt_rand($start, $end);
    return date($format, $timestamp);
}