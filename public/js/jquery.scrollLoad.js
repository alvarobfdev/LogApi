/**
 * Created by alvarobanofos on 7/8/16.
 */

(function ( $ ) {
    $.fn.scrollLoad = function(options) {


        function activateScrollHandler(data) {
            data.container.on('scroll', data, bindScroll);
        }


        function bindScroll(event) {
            var container = event.data.container;
            var content = event.data.content;
            if(container.scrollTop() + container.height() > content.height() - settings.distanceFromBottom) {
                container.off('scroll', bindScroll);
                settings.loaderFunction(event.data, activateScrollHandler);
            }
        }


        if(options == 'destroy') {
            return this.each(function() {
                $(this).off('scroll');
            });
        }
        var settings = $.extend({
            // These are the defaults.
            distanceFromBottom: 500,
            loaderFunction:function() {},
            accessRowFunction:function() {},
            content: $(document)
        }, options );


        return this.each(function() {
            $(this).on('scroll', {container:$(this), content:settings.content}, bindScroll);
        });

    };

}( jQuery ));