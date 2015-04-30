<?php

/**
* Setup CSS for front-end
* @since 1.0
*/
function wptm_backend_scripts() {
    if(is_admin()){
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'wp-mediaelement-and-player' );
        wp_enqueue_script( 'wp-mediaelement' );

        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_style( 'wp-mediaelement' );
        wp_enqueue_script('media-upload');
        wp_enqueue_style( 'wptm',  SA_DT_DIR_URI . 'css/admin.css', false );
        wp_enqueue_script( 'wptm', SA_DT_DIR_URI . 'js/media.js', false );
        wp_enqueue_script( 'fit-video', SA_DT_DIR_URI . 'js/fit-video.js', false );
        wp_localize_script('wptm','wptm_settings',  array(
            'media_pluginPath' => get_bloginfo( 'url').'/wp-includes/js/mediaelement/'
        ) );
    }
}


/**
 * Hooks JS and CSS
 */
add_action( 'admin_print_scripts-post.php', 'wptm_backend_scripts' , 69 );
add_action( 'admin_print_scripts-post-new.php', 'wptm_backend_scripts' , 69 );




function wptm_scripts(){
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'wp-mediaelement-and-player' );
    wp_enqueue_script( 'fit-video', SA_DT_DIR_URI . 'js/fit-video.js', false );
    wp_enqueue_script( 'owl.carousel', SA_DT_DIR_URI . 'js/owl.carousel.js', false );
    wp_enqueue_script( 'wptm', SA_DT_DIR_URI . 'js/front-end.js', false );
    wp_enqueue_style('dashicons');
    wp_enqueue_style('owl.carousel', SA_DT_DIR_URI.'css/owl.carousel.css');
    wp_enqueue_style('wptm-owl.theme', SA_DT_DIR_URI.'css/owl.theme.css');
    wp_enqueue_style('wptm', SA_DT_DIR_URI.'css/front-end.css');
}

add_action('wp_enqueue_scripts', 'wptm_scripts');