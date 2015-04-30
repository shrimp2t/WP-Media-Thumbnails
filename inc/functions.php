<?php

/**
 * Get content form file
 * @param $file
 * @param array $data
 * @return string
 * @since 1.0
 */
function wptm_get_content_form_file( $full_file_path, $data = array() ){
    ob_start();
    $old_file_content =  ob_get_contents();
    ob_end_clean();
    ob_start();

    if( !empty( $data ) ){
        extract( $data, EXTR_OVERWRITE );
    }

    if( is_file( $full_file_path ) ){
        include $full_file_path;
    }

    $file_content = ob_get_contents();
    ob_end_clean();
    echo $old_file_content;
    $file_content =  str_replace( array("\n","\t\t\t\t", "\t\t\t", "\t\t", "\t", "\r\n", "\r") , ' ', $file_content );
    return $file_content;
}



/**
 *
 * Get image src
 *
 * @param $image_id
 * @param string $size
 * @return bool|mixed
 */
function wptm_get_img_src(  $image_id , $size = 'thumbnail' ){
    $key = 'wptm_get_img_src'.$image_id.$size;

    $src =  wp_cache_get( $key );
    if( $src ){
        return $src;
    }

    $img =  wp_get_attachment_image_src( $image_id,  $size );
    if(  $img ){
        $src=  $img[0];
        wp_cache_add( $key,  $src);
    }

    return $src;

}

function wptm_get_video( $url ){
    $url_lower = strtolower($url);
    $return = false;

    if(strpos($url_lower,'youtube')){
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$url,$id);

        if($id[1]==''){
            return false;
        }

        $return['type']='youtube';
        $return['video_id']=$id[1];
        $return['code'] = '<iframe width="306" height="160" src="http://www.youtube.com/embed/'.$id[1].'?wmode=transparent" frameborder="0"></iframe>';
        $return['thumb'] = 'http://img.youtube.com/vi/'.$id[1].'/0.jpg';

    }else if (strpos($url_lower,'youtu.be') ){
        preg_match('/youtu.be\/([^\\?\\&]+)/', $url, $id);

        if($id[1]==''){
            return false;
        }

        $return['type']='youtube';
        $return['video_id']=$id[1];
        $return['code'] = '<iframe width="306" height="160" src="http://www.youtube.com/embed/'.$id[1].'?wmode=transparent" frameborder="0"></iframe>';
        $return['thumb'] = 'http://img.youtube.com/vi/'.$id[1].'/0.jpg';

    }else if( strpos( $url_lower,'vimeo.com') ){

        preg_match_all('/(https?:\/\/)?(www.)?(player.)?vimeo.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/', $url, $id );
        if($id[1]==''){
            return false;
        }

        $return['type']='vimeo';
        $return['video_id'] = is_array( $id[5] ) ? $id[5][0] : $id[5] ;
        $return['code'] = '<iframe width="306" height="160" src="http://player.vimeo.com/video/'.$return['video_id'].'?title=0&amp;byline=0&amp;portrait=0" frameborder="0"></iframe>';
       // wp_remote_get('vimeo.com/api/v2/video/'.$return['video_id'].'.json');
        $data = json_decode ( wp_remote_retrieve_body( wp_remote_get('http://vimeo.com/api/v2/video/6271487.json') ) );
        $return['thumb'] = $data[0]->thumbnail_medium;;

    }else{
        $check = wp_check_filetype( $url );

        $videos =   array(
            'asf' => 'video/x-ms-asf',
            'asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov' => 'video/quicktime',
            'qt' => 'video/quicktime',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp' => 'video/3gpp', // Can also be audio
            '3gpp' => 'video/3gpp', // Can also be audio
            '3gp2' => 'video/3gpp2', // Can also be audio
        );

        if( ! isset( $videos[  $check['ext'] ] ) ){
            return false;
        }

        $return['type']= 'custom';
        $return['video_id'] = '';
        $return['code'] = do_shortcode( '[video src="'.esc_attr( $url ).'"]' );
        $return['thumb'] = '';

    }

    return apply_filters( 'wptm_get_video', $return) ;
}


function wptm_get_thumbnail_data( $post_id ){
    $key = '_wptm_data_'.$post_id;
    $data = wp_cache_get( $key );
    if( $data ){
        return  $data;
    }

    $data =  get_post_meta(  $post_id, '_wptm_data' , true );
    $data = wp_parse_args( $data, array(
        'type' =>'image',
        'image_id' => '' ,
        'gallery' => array() ,
        'gallery_display' => 'gird',
        'gallery_settings' => array(),
        'video' => array() ,
    ) );

    wp_cache_add( $key, $data );

    return $data;
}

///wptm_get_video( '', $r );
// has_post_thumbnail();
// $check = apply_filters( "get_{$meta_type}_metadata", null, $object_id, $meta_key, $single );

function wptm_get_post_metadata_thumbnail_id(  $unknown, $object_id, $meta_key, $single  ){

    if( is_admin() || $meta_key !== '_thumbnail_id' ){
        return $unknown;
    }
    $data =  wptm_get_thumbnail_data( $object_id );

    // get_post_meta( $object_id , '_thumbnail_id', true )

    $r = false;

    switch(  $data['type'] ){
        case 'gallery':
            $r = ( count( $data['gallery'] ) ) ?  1 : false;
        break;
        case 'video':
            $video =  wp_parse_args( $data['video'] , array(
                'type' =>'hosted_video', // || custom_video
                'video_id' =>'',
                'url'
            ) );

            switch(  $video['type'] ){
                case 'hosted_video':
                    $r  =  $video['video_id'];
                break;
                default:
                    if( $video['url'] != '' ){
                        $r = true;

                    }
            }

        break;
        default:
            if( $data['image_id'] > 0 ){
                $r = $data['image_id'];
            }else{
                //$r  = get_post_meta( )

                $meta_cache = wp_cache_get($object_id,   'post_meta');

                if ( !$meta_cache ) {
                    $meta_cache = update_meta_cache( 'post', array( $object_id ) );
                    $meta_cache = $meta_cache[$object_id];
                }
                if ( isset($meta_cache[$meta_key]) ) {
                     $r =  maybe_unserialize( $meta_cache[$meta_key][0] );
                }

            }
    }

    if( $r ){
        if( ! $single ){
            $r = array( $r );
        }
    }

    return $r;
}


/**
 *  Get Post Thumbnail.
 *
 * @param $html
 * @param $post_id
 * @param $post_thumbnail_id
 * @param $size
 * @param $attr
 * @return string
 */
function wptm_get_post_thumnail(  $html, $post_id, $post_thumbnail_id, $size, $attr  ){

    $key = 'wptm_get_thumbnail'.$post_id.$post_thumbnail_id.$size;

    $wptm_thumb =  wp_cache_get( $key );
    if(  $wptm_thumb ){
        return $wptm_thumb;
    }
    $thumb = new wptm_Thumbnail( $post_id , $size );
    $wptm_thumb = $thumb->get_html();
    if($wptm_thumb  && $wptm_thumb !='' ){
        wp_cache_add( $key,  $wptm_thumb );
        return $wptm_thumb;
    }else{
        wp_cache_delete(  $key );
    }

    return $html;
}

/**
 *  Get First Thumbnail src
 *
 * @param $post_id
 * @param $size
 * @return bool|string
 */
function wptm_get_first_thumnail(  $post_id, $size  ){

    $key = 'wptm_get_first_thumbnail'.$post_id.$size;
    $wptm_thumb =  wp_cache_get( $key );
    if(  $wptm_thumb ){
        return $wptm_thumb;
    }
    $thumb = new wptm_Thumbnail( $post_id , $size );
    $wptm_thumb = $thumb->get_first_src();
    if($wptm_thumb  && $wptm_thumb !='' ){
        wp_cache_add( $key,  $wptm_thumb );
        return $wptm_thumb;
    }else{
        wp_cache_delete(  $key );
    }

    return $html;
}




/**
 * REMOVE :WP  Provide a No-JS Flash fallback as a last resort for audio / video
 */
function wptm_remove_fallback(  $html_code ){
      return '';
}

add_filter( 'wp_mediaelement_fallback', 'wptm_remove_fallback' );
add_filter( 'get_post_metadata', 'wptm_get_post_metadata_thumbnail_id' , 69 , 4 );
add_filter( 'post_thumbnail_html', 'wptm_get_post_thumnail' , 69 , 5 );









