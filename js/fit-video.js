/*global jQuery */
/*jshint multistr:true browser:true */
/*!
 * FitVids 1.0
 *
 * Copyright 2011, Chris Coyier - http://css-tricks.com + Dave Rupert - http://daverupert.com
 * Credit to Thierry Koblentz - http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
 * Released under the WTFPL license - http://sam.zoy.org/wtfpl/
 *
 * Date: Thu Sept 01 18:00:00 2011 -0500
 */

(function( $ ){

    "use strict";

    $.fn.fitVids = function( options ) {
        var settings = {
            customSelector: null,
            ratio: '16:9'
        };

        var div = document.createElement('div'),
            ref = document.getElementsByTagName('base')[0] || document.getElementsByTagName('script')[0];

        div.className = 'fit-vids-style';
        div.innerHTML = '&shy;<style>         \
      .fluid-width-video-wrapper {        \
         width: 100%;                     \
         position: relative;              \
         padding: 0;                      \
      }                                   \
                                          \
      .fluid-width-video-wrapper iframe,  \
      .fluid-width-video-wrapper object,  \
      .fluid-width-video-wrapper video,   \
      .fluid-width-video-wrapper .wp-video, \
      .fluid-width-video-wrapper embed {  \
         position: absolute;              \
         top: 0;                          \
         left: 0;                         \
         width: 100%;                     \
         height: 100%;                    \
      }                                   \
    </style>';

        ref.parentNode.insertBefore(div,ref);

        if ( options ) {
            $.extend( settings, options );
        }

        return this.each(function(){
            var selectors = [
                "iframe[src*='player.vimeo.com']",
                "iframe[src*='www.youtube.com']",
                "iframe[src*='www.youtube-nocookie.com']",
                "iframe[src*='www.kickstarter.com']",
                "object",
                "embed",
                ".wp-video"
            ];

            if ( settings.customSelector ) {
                selectors.push(settings.customSelector);
            }

            var $allVideos = $(this).find(selectors.join(','));

            $allVideos.each(function(){
                var $this = $(this);
                this.style.height ='';

                if (this.tagName.toLowerCase() === 'embed' && $this.parent('object').length || $this.parent('.fluid-width-video-wrapper').length) {
                    return;
                }

                var ratio = $this.attr('ratio') ||  settings.ratio;
                if( typeof ratio === undefined || ratio ==''){
                    ratio = '16:10';
                }

                ratio =ratio.split(':');
                ratio[0] = !isNaN( parseInt( ratio[0]  ) ) ?  parseInt( ratio[0] ) :  1 ;
                ratio[1] = !isNaN( parseInt( ratio[1] ) ) ? parseInt( ratio[1] ) : 1 ;
                if( ratio[0] <=0 ){
                    ratio[0] =1;
                }
                if( ratio[1] <=0 ){
                    ratio[1] =1;
                }

                var aspectRatio = ratio[1] / ratio[0];
                if(!$this.attr('id')){
                    var videoID = 'fitvid' + Math.floor(Math.random()*999999);
                    $this.attr('id', videoID);
                }
                $this.wrap('<div class="fluid-width-video-wrapper"></div>').parent('.fluid-width-video-wrapper').css('padding-top', (aspectRatio * 100)+"%");
                $this.removeAttr('height').removeAttr('width').removeAttr('style');
            });
        });
    };
})( jQuery );
