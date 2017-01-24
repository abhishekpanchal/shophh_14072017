require(['jquery', 'jquery.bootstrap'], function($){
    // DOM ready
    $(function(){
        // This function is needed (even if empty) to force RequireJS to load Twitter Bootstrap and its Data API.

        console.log('Loaded Js!');
        $('.level2.active').parent().addClass('display-block');
    });
});
