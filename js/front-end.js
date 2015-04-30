jQuery( document ).ready( function( ){

    jQuery( 'body' ).fitVids();


    /*
    jQuery('video' ).mediaelementplayer({
        pluginPath : wptm_settings.media_pluginPath
    });
    */

    // course post
    // Slider
    jQuery( ".wpmt-thumbnail .carousel" ).each( function(){
        var c = jQuery( this );

        var t = c.attr( 'data-type' ) || 'carousel';
        var n =  c.attr( 'data-column' );
        n = parseInt( n );
        if( isNaN( n ) ){
            n = 4;
        }

        c.addClass( 'type-'+t );

        if( t === 'slider' ){
            c.owlCarousel({
                items : 1,
                singleItem:true,
                responsive:true,
                navigation: true,
                paginationNumbers: true,
                itemsDesktop : [1170,1],
                navigationText: ['<i class="dashicons dashicons-arrow-left-alt2"></i>','<i class="dashicons dashicons-arrow-right-alt2"></i>'],
                //navigationText: ["<i class=\"fa fa-angle-left\"></i>","<i class=\"fa fa-angle-right\"></i>"],
                autoHeight : true,
                transitionStyle:"fade"
            });


        } else {
            c.owlCarousel({
                items : n,
                navigation: true,
                pagination: false,
                navigationText: ['<i class="dashicons dashicons-arrow-left-alt2"></i>','<i class="dashicons dashicons-arrow-right-alt2"></i>'],
                //navigationText: ["<i class=\"fa fa-angle-left\"></i>","<i class=\"fa fa-angle-right\"></i>"],
                itemsDesktop : [1170,3],
                itemsDesktopSmall : [979,3]
            });

        }

    } );


} );
