/* ==========================================================================
 Scripts voor de frontend
 ========================================================================== */
require(['jquery'], function ($) {
    $(function () {

        $('.c-sidebar').on('click','.o-list .expand, .o-list .expanded', function () {
            var element = $(this).parent('li');

            if (element.hasClass('active')) {
                element.find('ul').slideUp();

                element.removeClass('active');
                element.find('li').removeClass('active');

                element.find('i').removeClass('ion-android-arrow-dropdown').addClass('ion-android-arrow-dropup');
            }

            else {
                element.children('ul').slideDown();
                element.siblings('li').children('ul').slideUp();
                element.parent('ul').find('i').removeClass('mdi mdi-chevron-up').addClass('mdi mdi-chevron-down');
                element.find('> span i').removeClass('mdi mdi-chevron-down').addClass('mdi mdi-chevron-up');

                element.addClass('active');
                element.siblings('li').removeClass('active');
                element.siblings('li').find('li').removeClass('active');
                element.siblings('li').find('ul').slideUp();
            }
        });
    });
});