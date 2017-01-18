
'use strict';
define([
    'jquery',
    'jquery/ui'
], function($) {
    "use strict";

    $.widget('evince.impress', {
        options: {
            url: '',
            slider_id: '',
        },

        _create: function() {
            var o = this.options;
            $.ajax({
                url: o.url,
                type: 'POST',
                dataType: 'html',
                data: {
                    slider_id: o.slider_id
                },
            });
        },
    });
    return $.evince.impress;
});
