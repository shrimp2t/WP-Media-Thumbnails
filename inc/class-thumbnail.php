<?php

class wptm_Thumbnail {

    var  $meta_data =  array();
    var  $post_id = 0;
    var  $size ='thumbnail';
    var  $post_link =  '';

    function __construct(  $post_id = 0 , $size ='thumbnail'){
        $this->init(  $post_id , $size);
    }

    function init( $post_id =0 , $size = 'thumbnail' ){
        if( $post_id > 0 ){
            $this->post_id =  $post_id;
            $this->get_meta_data();
            $this->size =  $size;
            $this->post_link =  get_permalink(  $post_id );
        }
    }

    private function get_meta_data(  ) {
        $this->meta_data =  wptm_get_thumbnail_data( $this->post_id );
    }

    public  function gallery_item(  $item ,  $classes = array() , $size ='' ){
        $item = wp_parse_args(  $item, array(
            'image_id' => 0 ,
            'title' => '',
            'caption' =>'',
            'url' =>'',
        ) );

        $html = '';

        $img = '';
        if( $item['image_id'] > 0 ){
            $img = wptm_get_img_src( $item['image_id'] , $size );
        }
        $html .='<div class="g-item '.join(' ', $classes).'">';

        $html .= '<figure class="effect-marley">';



        if(  $img ){
            $html .= '<img src="'.$img.'"/>';
        }

        $caption = '<figcaption >';

        if( $item['title'] != '' ){
            $caption .= '<h2>'.esc_html( $item['title'] ).'</h2>';
        }

        if( $item['caption'] != '' ){
            $caption .= '<p>'.esc_html( $item['caption'] ).'</p>';
        }

        if( $item['url'] != '' ){
            $caption .='<a href="'.$item['url'].'"></a>';
        }
        $caption .= '</figcaption>';

       $html .= $caption;

        $html.='</figure>';
        $html.='</div>';



        return $html;
    }

    public  function slider_item(  $item ,  $classes = array() , $size ='' ){
        $item = wp_parse_args(  $item, array(
            'image_id' => 0 ,
            'title' => '',
            'caption' =>'',
            'url' =>'',
        ) );

        $html = '';

        $img = '';
        if( $item['image_id'] > 0 ){
            $img = wptm_get_img_src( $item['image_id'] , $size );
        }

        $html .= '<div class="g-item '.join(' ', $classes).'">';

        if( $item['url'] != '' ){
            $html .='<a href="'.esc_url(  $item['url']  ).'">';
        }

        if(  $img ){
            $html .= '<img src="'.$img.'"/>';
        }

        $caption = '';

        if( $item['title'] != '' ){
            $caption .= '<h3 class="item-title">'.esc_html( $item['title'] ).'</h3>';
        }

        if( $item['caption'] != '' ){
            $caption .= '<div class="item-caption">'.esc_html( $item['caption'] ).'</div>';
        }


        if( $caption !='' ){
            $html .= '<div class="item-caption-wrap">'.$caption.'</div>';
        }

        if( $item['url'] != '' ){
            $html .='</a>';
        }

        $html.='</div>';
        return $html;
    }




    private function galley(){
        $html = '';
        if(  count( $this->meta_data['gallery'] ) ){

            $gallery_settings = wp_parse_args(  $this->meta_data['gallery_settings'] , array(
                'display'=>'gird',
                'numcol' => 3,
                'highlight' => ''
            ) );

            switch(  $gallery_settings['display'] ){
                case 'slider': case 'carousel':
                $i = 1;
                $html = '';

                $gallery_settings['numcol'] =  intval(  $gallery_settings['numcol'] );
                $col = 3;
                if(  $gallery_settings['numcol'] >0 ){
                    $col =  12/ $gallery_settings['numcol'];
                }

                foreach(  $this->meta_data['gallery'] as $k => $item ){
                        $classes =  array();
                        if(  $gallery_settings['display']!='slider' ){
                            $classes[] = 'g-col col-'.$col;
                        }

                        $classes[] = 'item-'.($i);



                        if(  $i == $gallery_settings['numcol']  ){
                            $classes[] = 'last';
                        }

                    $html .= $this->gallery_item( $item, $classes, $this->size );

                }

                $html = '<div class="carousel" data-type="'. $gallery_settings['display'].'" data-column="'.$gallery_settings['numcol'].'">'.$html.'</div>';



                break;
                default:

                    $gallery_settings['numcol'] =  intval(  $gallery_settings['numcol'] );
                    $col = 3;
                    if(  $gallery_settings['numcol'] >0 ){
                        $col =  12/ $gallery_settings['numcol'];
                    }

                    if(  $gallery_settings['highlight'] == 1 ){
                        $classes = array();
                        $classes[] = 'g-col col-12';
                        $classes[] = 'item-highlight';
                        $classes[] = 'item-1st';
                        $html .= $this->gallery_item( $this->meta_data['gallery'][0], $classes, $this->size );
                        unset( $this->meta_data['gallery'][0] );
                    }

                    $i = 1;
                    foreach(  $this->meta_data['gallery'] as $k => $item ){
                        $classes =  array();


                        $classes[] = 'g-col col-'.$col;
                        $classes[] = 'item-'.($i);

                        if(  $i == 1 ){
                            $classes[] = 'first';
                        }

                        if(  $i == $gallery_settings['numcol']  ){
                            $classes[] = 'last';
                        }

                        $html .= $this->gallery_item( $item, $classes, 'medium' );

                        if(  $i >= $gallery_settings['numcol'] ){
                            $i = 1;
                        }else{
                            $i ++;
                        }

                    }


                    if(  $html !='' ){
                        $html = '<div class="galler-gird'.( $gallery_settings['highlight'] == 1  ? ' highlight' : '' ).' g-row">'.$html.'</div>';
                    }

                   break;
            }

        }// end count gallery

        return $html;

    }


    private function video(){
        $html = '';
        $video =  wp_parse_args( $this->meta_data['video'] , array(
            'type' =>'hosted_video', // || custom_video
            'video_id' =>'',
            'url' =>'',
            'thumb' =>''
        ) );

        switch(  $video['type'] ){
            case 'hosted_video':
                //$r  =  $video['video_id'];
                $html =  do_shortcode( '[video src="'.wp_get_attachment_url( $video['video_id'] ).'" ]' );
                break;
            default:
                if( $video['url'] != '' ){
                    $video = wptm_get_video( $video['url'] );
                    $html = $video['code'] ;
                }
        }

        return $html;
    }

    private function default_thumb(){
        $html =  false;

        if( $this->meta_data['image_id'] > 0 ){
            $img = wp_get_attachment_image_src(  $this->meta_data['image_id'] , $this->size );
            if( $img ){
                $html = '<img src="'.$img[0].'" alt=""/>';
            }
        }else{
            $img = wp_get_attachment_image_src( get_post_thumbnail_id( $this->post_id ) , $this->size );
            if( $img ){
                $html = '<img src="'.$img[0].'" alt=""/>';
            }
        }
        return  $html;
    }

    function get_html( $post_id  = 0 ){
        $this->init(  $post_id );
        $html = '' ;
        switch(  $this->meta_data['type'] ){
            case 'gallery':
                    $html =  $this->galley();
                break;
            case 'video':
                    $html =  $this->video();
            break;
            default:
                $html .=  $this->default_thumb();
        }

        return ($html !='' )?  '<div class="wpmt-thumbnail wptm-'. $this->meta_data['type'].' post-thumbnail ">'.$html.'</div>' : $html ;
    }

    function get_first_src( $post_id  = 0, $size = 'thumbnail'  ){
        $this->init(  $post_id );
        $html = false;

        switch(  $this->meta_data['type'] ){
            case 'gallery':
                if(  count( $this->meta_data['gallery'] ) ){
                    $item = wp_parse_args(   $this->meta_data['gallery'][0], array(
                        'image_id' => 0 ,
                    ) );

                    if( $item['image_id'] > 0 ){
                        $html =  wptm_get_img_src( $item['image_id'] , $this->size  );
                    }
                }
             break;
            case 'video':
                    $video =  wp_parse_args( $this->meta_data['video'] , array(
                        'type' =>'hosted_video', // || custom_video
                        'video_id' =>'',
                        'url' =>'',
                    ) );
                    $video =  wptm_get_video($video['url']);
                    $html = $video['thumb'];
            break;
            default:

                if( $this->meta_data['image_id'] > 0 ){
                    $img = wp_get_attachment_image_src(  $this->meta_data['image_id'] , $this->size );
                    if( $img ){
                        $html = $img[0];
                    }
                }else{
                    $img = wp_get_attachment_image_src( get_post_thumbnail_id( $this->post_id ) , $this->size );
                    if( $img ){
                        $html = $img[0];
                    }
                }
        }

        return $html;
    }

}