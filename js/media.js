
function mediaUpload( content ){
    jQuery('.sa-upload-media .remove-media', content).click( function(){
        var p   = jQuery(this).parents('.sa-upload-media');
        jQuery('.sa-media-input, .sa-media-id',p).val('');
        jQuery(this).hide();
        jQuery('.media-preview',p).html('');
        jQuery('.sa-media-input',p).trigger({
            'type': 'sa_media_remove'
        });
        return false;
    });

    // open upload box
    jQuery('.sa-upload-media .sa-upload-button',content).click(function() {

        var p   = jQuery(this).parents('.sa-upload-media');
        var input = jQuery('.sa-media-input',p);
        var mediaType = p.attr('data-type') || '';
        if(typeof (mediaType)==='undefined'){
            mediaType = '';
        }

        var current_val =  input.val();


        if(mediaType=='gallery'){
            //---------------------------------------------------
            // thanks : http://shibashake.com/wordpress-theme/how-to-add-the-wordpress-3-5-media-manager-interface-part-2
            // see: in wp-includes/js/media-editor.js
            // console.debug(wp.media.view.l10n);
            var  media_select = function(current_val) {
                if(typeof(current_val)==='undefined' ||  current_val==''){
                    return {};
                }else{
                    current_val = '[sa-media ids="'+current_val+'"]';
                }

                var shortcode = wp.shortcode.next( 'sa-media', current_val),
                    defaultPostId = wp.media.gallery.defaults.id,
                    attachments, selection;

                // Bail if we didn't match the shortcode or all of the content.
                if ( ! shortcode )
                    return;

                // Ignore the rest of the match object.
                shortcode = shortcode.shortcode;

                if ( _.isUndefined( shortcode.get('id') ) && ! _.isUndefined( defaultPostId ) )
                    shortcode.set( 'id', defaultPostId );

                attachments = wp.media.gallery.attachments( shortcode );
                selection = new wp.media.model.Selection( attachments.models, {
                    props:    attachments.props.toJSON(),
                    multiple: true
                });

                selection.gallery = attachments.gallery;

                // Fetch the query's attachments, and then break ties from the
                // query to allow for sorting.
                selection.more().done( function() {
                    // Break ties with the query.
                    selection.props.set({ query: false });
                    selection.unmirror();
                    selection.props.unset('orderby');
                });

                return selection;
            };

            var state = 'gallery-library';
            if(typeof(current_val)==='undefined' ||  current_val==''){
                var s =  media_select(current_val);
            }else{
                var s =  media_select(current_val);
                state= 'gallery-edit';
            }

            frame = wp.media({
                id: 'sa-media-gallery',
                //className: 'st-media',
                frame:      'post',
                state:      state, // gallery-library | gallery-edit
                title:      wp.media.view.l10n.editGalleryTitle,
                library : { type : 'image'},
                editing:    true,
                multiple:   true,
                displayUserSettings: true,
                selection:  s
            });

            frame.on( 'select update insert',function(){
                var media_attachment = frame.state().get('selection').toJSON();
                var controller = frame.states.get('gallery-edit');
                var gallery = controller.get('library');
                // Need to get all the attachment ids for gallery
                var ids = gallery.pluck('id');
                // console.debug(gallery);
                var attrs ={};
                attrs.ids = gallery.pluck('id');
                /* attrs.ids = '1,2,3,4,5';
                 var st = new wp.shortcode({
                 tag:    'gallery',
                 attrs:  attrs,
                 type:   'single'
                 });
                 */
                input.val(attrs.ids.join(','));

                var preview =  '';
                var image_urls = [];
                gallery.forEach( function (item  ){
                    // console.debug(item);
                    var img_url;
                    if(typeof (item.attributes.sizes.thumbnail)!=='undefined'){
                        img_url = item.attributes.sizes.thumbnail.url;
                    }else{
                        img_url = item.attributes.sizes.full.url;
                    }

                    preview += ' <div class="mi"><div class="mid"><img src="'+img_url+'" alt=""></div></div>';
                    image_urls.push(img_url);
                } );

                jQuery('.media-preview',p).html(preview);
                jQuery('.remove-media',p).show();

                input.trigger({
                    'type': 'sa_media_gallery_change',
                    'gallery': image_urls
                });

            });

            frame.open();

            // ------------------------------------------------------

        }else { // image

            var frame = wp.media({
                title : wp.media.view.l10n.addMedia,
                multiple : false,
                library : { type : mediaType },
                button : { text : 'Insert' }
            });

            //  console.debug(frame.view.settings);

            frame.on('close',function() {
                // get selections and save to hidden input plus other AJAX stuff etc.
                var selection = frame.state().get('selection');
                // console.log(selection);
            });

            frame.on('select', function(){
                // Grab our attachment selection and construct a JSON representation of the model.
                var media_attachment = frame.state().get('selection').first().toJSON();
                //  console.debug(media_attachment);
                // console.debug(media_attachment);
                // media_attachment= JSON.stringify(media_attachment);

                if(mediaType !== 'audio' && mediaType !== 'video') {
                    input.val(media_attachment.id);
                    var  preview, img_url;


                    if(typeof (media_attachment.sizes.medium) !== 'undefined'){
                        img_url = media_attachment.sizes.medium.url;
                    }else{
                        img_url = media_attachment.sizes.full.url;
                    }

                    preview = ' <div class="mi"><div class="mid"><img src="'+img_url+'" alt=""></div></div>';

                    jQuery( '.media-preview',p ).html(preview);
                    jQuery( '.remove-media',p ).show();

                } else {
                    // media_attachment.id
                    if( mediaType === 'video' ){
                        // calculation width and height

                        jQuery( '.media-preview', p ).html('<div class="wp-video"><video  style="width:100%;height:100%;" width="100%" height="100%" src="' + ( media_attachment.url ) + '"></video></div>');
                        jQuery( '.media-preview', p ).fitVids();

                        jQuery('video', p ).mediaelementplayer({
                            pluginPath : wptm_settings.media_pluginPath
                        });



                    }
                    jQuery('.sa-media-id').val( media_attachment.id );
                    jQuery('.remove-media',p).show();
                    input.val( media_attachment.url );
                }

                input.trigger({
                    'type': 'sa_media_image_change',
                    'image': img_url
                });
            });

            frame.on('open',function() {

            });
            frame.open();

        }
        /// end media action
        return false;
    });
}



function get_vimeo_id(  url ){
    //see: http://stackoverflow.com/questions/13286785/get-video-id-from-vimeo-url
    var regExp = /https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/;
    var match = url.match(regExp);

    if (match){
        return match[3]
    }else{
        return false
    }

}

function get_youtube_id(  url ){
    // see: http://stackoverflow.com/questions/3452546/javascript-regex-how-to-get-youtube-video-id-from-url
    var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;

    var match = url.match( regExp );
    if ( match ){
        return match[2];
    }else{
        return false
    }
}


jQuery( document ).ready( function( ){
    var box =  jQuery('.wptm-box');

    jQuery('.wptm-type').change(  function(){
         var t = jQuery(this).val();
        // wptm-featured-image
        jQuery('.wptm-section', box).hide();
        jQuery('.wptm-type-'+t, box).show();
    } );

    jQuery('.wptm-type').trigger('change');


    mediaUpload( box );

    jQuery('.gallery-items', box).sortable();

    var gallery_item_action =  function(  item ){

        if(  !item.hasClass('opened')  && !item.hasClass('closed')  ){
            item.addClass('opened');
        }

        jQuery('.remove', item).click(function(){
            item.remove();
            return false;
        });

        jQuery('.toggle', item).click(function(){
            if( item.hasClass( 'opened' ) ){
                jQuery('.g-content', item).slideUp( 300 , function (){
                    item.removeClass('opened');
                    item.addClass('closed');
                }  );

            }else{
                item.addClass('opened');
                jQuery('.g-content', item).slideDown( 300 , function(){
                    item.removeClass('closed');

                } );

            }

            return false;
        });

    }

    // toggle box
    jQuery('.gallery-item' ,  box).each(  function (){
         var item = jQuery(  this );

        gallery_item_action( item );

    } );

    jQuery('.wptm-gallery-images .add-new' , box).click(function( ){
         var  p =  jQuery( this).parents( '.wptm-gallery-images' );
         var tpl = '';

        if( jQuery('#wptm-gallery-item-tpl').length > 0){
            tpl =  jQuery('#wptm-gallery-item-tpl').html();
            tpl = jQuery( tpl );
            jQuery('.gallery-items').append( tpl );
            gallery_item_action( tpl );
            mediaUpload( tpl );
        }

        return  false;
    });

    // for gallery settings
    jQuery('.wptm-gallery-display',  box).change( function(){
        var gt = jQuery(this).val();
        switch ( gt ){
            case 'slider' :
                jQuery('.gallery_setting_numcol', box).hide();
                jQuery('.gallery_setting_highlight', box).hide();
            break;
            case 'carousel' :
                jQuery('.gallery_setting_highlight', box).hide();
                jQuery('.gallery_setting_numcol', box).show();
            break;
            default :
                jQuery('.gallery_setting_numcol', box).show();
                jQuery('.gallery_setting_highlight', box).show();

        }
    });
    jQuery('.wptm-gallery-display',  box).trigger('change');






    // video type change
    var video_wrap =  jQuery( '.wptm-type-video', box );
    jQuery('.change-video-type').change(function(){
        var v = jQuery(  this).val();
        if( v!='' ){
            jQuery('.wptm_video_type .input').hide();
            jQuery('.'+ v +' .input').show();
        }
    });
    jQuery('input.change-video-type:checked').trigger('change');


    jQuery( '.media-preview', video_wrap ).fitVids();

    /*
    jQuery('video', video_wrap ).mediaelementplayer({
        pluginPath : wptm_settings.media_pluginPath
    });
    */


    // custom video changed
    jQuery('.wptm_video_type .custom_video_url').bind( 'keyup blur', function(){
        var p = jQuery( this).parents( '.wptm_video_type ' );
        var v = jQuery(  this).val();

        var w = p.innerWidth( );
        var h =  w / ( 16/9 )  ;

        var video_code = '';
        var id = get_youtube_id( v );

         if( id ){
             video_code = '<iframe  src="http://www.youtube.com/embed/'+id+'?wmode=transparent" frameborder="0"></iframe>';
         }else{
             id = get_vimeo_id( v );
             if( id ) {
                 video_code = '<iframe  src="http://player.vimeo.com/video/'+id+'?title=0&amp;byline=0&amp;portrait=0" frameborder="0"></iframe>';
             }
         }

        if( video_code !== '' ){
            jQuery( '.media-preview', p ).html( video_code );
            jQuery( '.media-preview', p ).fitVids();
        }else{
            if( v !='' ){
                jQuery( '.media-preview', p ).html( '<div class="wp-video"><video  style="width:100%;height:100%;" width="100%" height="100%" src="" ></video></div>' );
                jQuery( '.media-preview video', p).attr('src', v );
                jQuery( '.media-preview', p ).fitVids();

                jQuery('video', p ).mediaelementplayer({
                    pluginPath : wptm_settings.media_pluginPath
                });
            }else{
                jQuery( '.media-preview', p ).html('');
            }

        }
    } );



});
