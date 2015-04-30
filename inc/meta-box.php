<?php

if ( is_admin()) {

    function wptm_meta_boxes() {

        if(  current_theme_supports( 'post-thumbnails' ) ){
            $screens = array( 'post', 'page' );
            foreach( $screens as $screen ){
                 remove_meta_box('postimagediv', $screen, 'normal' );
                 remove_meta_box('postimagediv', $screen,  'side' );
                 remove_meta_box('postimagediv', $screen , 'advanced' );

                add_meta_box(
                    'wpthumbnailmedia',
                    __( 'Featured Media', 'sathemes' ),
                    'wptm_thumbnail_box',
                    $screen,
                    'side'
                );
            }
        }
    }
    add_action( 'add_meta_boxes', 'wptm_meta_boxes',  999999  );
}



function wptm_gallery_item( $args =  array(), $closed = true ){

    $args = wp_parse_args(  $args, array(
        'image_id' => 0 ,
        'title' => '',
        'caption' =>'',
        'url' =>'',
    ) );

    $img = '';
    if( $args['image_id'] > 0 ){
        $img = wptm_get_img_src( $args['image_id'] , 'medium' );
    }
    ?>
    <div class="gallery-item <?php echo $closed ? 'closed' : 'opened'; ?> ">
        <div class="sa-upload-media">
            <div class="g-head">
                <div class="small-thumb media-preview toggle">
                    <?php if( $img ){ ?>
                    <div class="mi">
                        <div class="mid">
                            <img src="<?php echo $img; ?>" alt=""/>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <a class="remove" href="#"><i class="dashicons dashicons-no"></i></a>
                <a class="toggle" href="#"><i class="toggle-icon"></i></a>
            </div>
            <div class="g-content">
                <div class="thumb media-preview sa-upload-button">
                    <?php if( $img ){ ?>
                        <img  alt="" class="" src="<?php echo $img; ?>">
                    <?php } ?>
                </div>

                <a href="#" class="sa-upload-button">Select image</a>
                <input type="hidden" class="sa-media-input"  value="<?php echo esc_attr( $args['image_id'] );  ?>" name="wptm[gallery][image_id][]">
                <p>
                    <label><?php _e('Title','sathemes'); ?><br/>
                        <input type="text" name="wptm[gallery][title][]" value="<?php echo esc_attr( $args['title'] );  ?>" >
                    </label>
                </p>
                <p>
                    <label><?php _e('Caption','sathemes'); ?><br/>
                        <textarea name="wptm[gallery][caption][]"><?php echo esc_attr( $args['caption'] );  ?></textarea>
                    </label>
                </p>
                <p>
                    <label><?php _e('URL','sathemes'); ?><br/>
                        <input type="text" name="wptm[gallery][url][]" value="<?php echo esc_attr( $args['url'] );  ?>" >
                    </label>
                </p>
            </div>
        </div>
    </div>
    <?php
}



function wptm_thumbnail_box( $post  ){
    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'wptm_meta_box', 'wptm_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */

    $data =  get_post_meta(  $post->ID, '_wptm_data' , true );

    $data = wp_parse_args( $data, array(
        'type' =>'image',
        'image_id' => get_post_meta(  $post->ID, '_thumbnail_id', true ) ,
        'gallery' => array() ,
        'gallery_display' => 'gird',
        'gallery_settings' => array(),
        'video' => array() ,
    ) );

    //var_dump($data);

    $types =  array(
        'image' => __('Featured image', 'sathemes'),
        'gallery' => __('Gallery', 'sathemes'),
        'video' => __('Video', 'sathemes'),
    );

    $types = apply_filters( 'wptm_types', $types );

    ?>
    <div class="wptm-box">
        <select class="wptm-type" name="wptm[type]">
            <?php foreach(  $types as $k=> $v ){ ?>
                <option value="<?php  echo $k; ?>" <?php echo selected( $k,  $data['type'] ); ?> ><?php echo $v; ?></option>
            <?php } ?>
        </select>

        <div class="wptm-section wptm-type-image  sa-upload-media">
            <?php
             $img = wptm_get_img_src(  $data['image_id'], 'medium' );

            ?>
            <input type="hidden" class="sa-media-input" value="<?php echo esc_attr( $data['image_id'] ); ?>" name="wptm[image_id]">
            <div class="media-preview"><?php
                if( $img ){
                    ?>
                    <img src="<?php echo $img; ?>" alt=""/>
                    <?php
                }

                ?></div>
            <div class="wptm-buttons">
                <a href="#" class="sa-upload-button wptm-btn button-secondary"><span class="dashicons dashicons-format-image"></span> <?php _e('Select image','sathemes'); ?></a>
                <a href="#" class="wptm-btn remove-media button-secondary" <?php echo ($img) ? '' : ' style="display: none" '; ?>> <?php _e('Remove','sathemes'); ?></a>
            </div>
        </div>

        <div class="wptm-section wptm-type-gallery wptm-gallery-images">
            <div class="gallery-items">
                <?php

                if( !is_array(  $data['gallery'] ) ){
                    $data['gallery']  = array();
                }

                foreach( $data['gallery'] as $item ){
                    wptm_gallery_item( $item );
                }

                ?>
            </div>
            <script type="text/html" id="wptm-gallery-item-tpl">
                <?php
                  wptm_gallery_item( false , false );
                ?>
            </script>
            <a href="#" class="add-new button-secondary"><?php _e('+ New Item','sathemes'); ?></a>

            <div class="gallery-display">
                <?php

                $gallery_settings = wp_parse_args(  $data['gallery_settings'] , array(
                    'display'=>'gird',
                    'numcol' => 3,
                    'highlight' => ''
                ) );

                ?>
                <p>
                    <label><?php _e('Display type', 'sathemes'); ?></label>
                    <select class="wptm-gallery-display" name="wptm[gallery_settings][display]">
                        <?php foreach(  array(
                                            'gird' => __('Gird' , 'sathemes'),
                                            'slider' => __('Slider' , 'sathemes'),
                                            'carousel' => __('Carousel' , 'sathemes'),
                                        ) as $k => $v ){
                            ?>
                            <option value="<?php  echo $k; ?>" <?php echo selected( $k,  $gallery_settings['display'] ); ?> ><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </p>

                <p class="gallery_setting_numcol">
                    <label>Number columns
                        <select class="wptm-gallery-numcol" name="wptm[gallery_settings][numcol]">
                            <?php foreach(  array(
                                                1,2,3,4,6
                                            ) as  $k ){
                                ?>
                                <option value="<?php  echo $k; ?>" <?php echo selected( $k,  $gallery_settings['numcol'] ); ?> ><?php echo $k; ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </p>
                <p class="gallery_setting_highlight">
                    <label>
                        <input type="checkbox" value="1" name="wptm[gallery_settings][highlight]" <?php echo checked( 1,  $gallery_settings['highlight'] ); ?>><?php _e( 'Highlight first item' ,'sathemes' ); ?>
                    </label>
                </p>
            </div>

        </div><!-- /.gallery-image -->

        <div class="wptm-section wptm-type-video ">
            <?php
             //echo do_shortcode( '[video src="'.wp_get_attachment_url( 6 ).'" height="auto"  width="auto"]' );
            $video =  wp_parse_args( $data['video'] , array(
                'type' =>'hosted_video', // || custom_video
                 'video_id' =>'',
                'url'
            ) );

            ?>
            <div class="wptm_video_type hosted_video sa-upload-media" data-type="video">
                <label><input type="radio" value="hosted_video" class="change-video-type" <?php echo checked('hosted_video', $video['type']); ?> name="wptm[video][type]"><?php _e('Media video','sathemes'); ?></label>
                <div class="input">
                    <div class="media-preview"><?php
                            if( $video['video_id'] > 0 ){
                               // echo '<div class="video"><video id="wptm-video-preview" src="'.wp_get_attachment_url( $video['video_id'] ).'" width="306" height="160" ></video></div>';
                                echo do_shortcode( '[video src="'.wp_get_attachment_url( $video['video_id'] ).'" ]' );
                            }
                        ?></div>
                    <input type="hidden" class="sa-media-id" value="<?php echo esc_attr( $video['video_id'] );  ?>" name="wptm[video][video_id]">
                    <div class="wptm-buttons">
                        <a href="#" class="sa-upload-button button-secondary wptm-btn"><span class="dashicons dashicons-format-video"></span> <?php _e('Select video', 'sathemes'); ?></a>
                        <a href="#" class="wptm-btn remove-media button-secondary" <?php echo ( $video['video_id'] > 0) ? '' : ' style="display: none" '; ?>><?php _e('Remove','sathemes'); ?></a>
                    </div>

                </div>
            </div>
            <div class="wptm_video_type custom_video">
                <label><input type="radio" value="custom_video" <?php echo checked('custom_video', $video['type']); ?> class="change-video-type" name="wptm[video][type]"><?php _e('Custom Video','sathemes'); ?></label>
                <div class="input">
                    <div class="media-preview">
                        <?php
                            $v =  wptm_get_video( $video['url'] );
                            if( $v ){
                                echo $v['code'];
                            }
                        ?>
                    </div>
                    <label><?php _e('Enter your video URL','sathemes'); ?><br/>
                        <input type="text" class="custom_video_url" style="width: 100%;" value="<?php echo esc_attr( $video['url'] );  ?>" name="wptm[video][url]"  >
                    </label>
                    <br/>
                    <strong><?php _e('Example:', 'sathemes'); ?></strong>
                    <p class="description">https://www.youtube.com/watch?v=HwXbtZXjbVE</p>
                    <p class="description">http://vimeo.com/13395858</p>
                    <p class="description"> http://yourdomain.com/path-to-file/video-filename.mp4</p>
                </div>
            </div>

        </div>

        <?php
            do_action('wptm_more_settings');
        ?>
    </div>
<?php
}



/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wptm_save_meta_box_data( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['wptm_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['wptm_meta_box_nonce'], 'wptm_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    if(  isset( $_POST['wptm'] ) ){

        $data = $_POST['wptm'];
        $data = wp_parse_args( $data, array(
            'type' =>'image',
            'image_id' => 0 ,
            'gallery' => array() ,
            'gallery_display' => 'gird',
            'gallery_settings' => array(),
            'video' => array() ,
        ) );

        $gallery =  $data['gallery'];

        $tpl_gallery =  array();
        if(  !empty( $gallery ) ){
            foreach( $gallery['image_id'] as $i => $v ){
                $tpl_gallery[  $i ]['image_id'] = $v;
                $tpl_gallery[  $i ]['title'] = $gallery[ 'title' ] [$i ];
                $tpl_gallery[  $i ]['caption'] = $gallery[ 'caption' ] [$i ];
                $tpl_gallery[  $i ]['url'] = $gallery[ 'url' ] [$i ];
            }
        }
        $data['gallery'] =  $tpl_gallery;
        $data['gallery_settings'] = wp_parse_args(  $data['gallery_settings'] , array(
            'display'=>'gird',
            'numcol' => 3,
            'highlight' => ''
        ) );

        update_post_meta(  $post_id, '_wptm_data', $data );
        if(  count($tpl_gallery) ){
            update_post_meta(  $post_id , '_thumbnail_id' ,  $tpl_gallery[0]['image_id'] );
        }else{
            update_post_meta(  $post_id , '_thumbnail_id' ,  $data['image_id'] );
        }
        update_post_meta(  $post_id, '_wptm_thumb_type', $data['type'] );
    }else{
        update_post_meta(  $post_id, '_wptm_data', '' );
        update_post_meta(  $post_id, '_wptm_thumb_type', '' );
    }

}
add_action( 'save_post', 'wptm_save_meta_box_data' );