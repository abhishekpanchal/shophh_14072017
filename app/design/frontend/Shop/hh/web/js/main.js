require(['jquery', 'jquery.bootstrap'], function($){
  // DOM ready
  $(function(){
    // This function is needed (even if empty) to force RequireJS to load Twitter Bootstrap and its Data API.

    console.log('Loaded Js!');

    $('.level2.active').parent().addClass('display-block');

    if ($('body').hasClass('faqs-index-index')) {
        $('.faq-sidebar li.faq').addClass('active-sidebar');
    }
    else if ($('body').hasClass('contact-index-index')) {
        $('.faq-sidebar li.contact').addClass('active-sidebar');
    }
    else if ($('body').hasClass('cms-privacy-policy')) {
        $('.faq-sidebar li.privacy').addClass('active-sidebar');
    }
    else if ($('body').hasClass('cms-terms-conditions')) {
        $('.faq-sidebar li.terms').addClass('active-sidebar');
    }
    else if ($('body').hasClass('cms-shipping-information')) {
        $('.faq-sidebar li.shipping').addClass('active-sidebar');
    }
    else if ($('body').hasClass('cms-returns')) {
        $('.faq-sidebar li.returns').addClass('active-sidebar');
    }
  });

  jQuery(document).ready(function () {
    jQuery(document).on('click', '.hotspot', function (event) {
      event.preventDefault();
      event.stopPropagation();

      var el = jQuery(this);
      //var hotspotId = el.attr('id');

      var hotspotDesc = $(el).find( ".product-info" ).html();
      $('.hotspot-details').html(hotspotDesc)
    });
  });

});
