require(['jquery', 'jquery.bootstrap'], function($){

  jQuery.noConflict();

  // DOM ready
  $(function(){
    // This function is needed (even if empty) to force RequireJS to load Twitter Bootstrap and its Data API.

    console.log('Loaded Js!');

    $('.level2.active').parent().addClass('display-block');

    if ($('body').hasClass('faqs-index-index')) {
      $('.faq-sidebar li.faq').addClass('active-sidebar');
      $('.shop-menu').addClass('active-menu');
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

  $('p').each(function() {
    var $this = $(this);
    if($this.html().replace(/\s|&nbsp;/g, '').length == 0)
    $this.remove();
  });

  jQuery(document).ready(function () {

    // register popup tabs 
    $('.tabs-replica li, .create-btn').click(function() {
      event.preventDefault();
      event.stopPropagation();

      if ( $(this).hasClass('tabs-register') || $(this).hasClass('create') ) {
        $('.social-login.authentication').hide();
        $('.social-login.create').show();
      } else {
        $('.social-login.authentication').show();
        $('.social-login.create').hide();
      }
    });

    $('a.action.remind').click(function() {
      $('.social-login.authentication').hide();
      $('.social-login.create').hide();
      $('.social-login.forgot').show();
    });

    $('a.action.back').click(function() {
      $('.social-login.authentication').show();
      $('.social-login.forgot').hide();
    });
    




    jQuery(document).on('click', '.hotspot', function (event) {
      event.preventDefault();
      event.stopPropagation();
      var el = jQuery(this);

      var hotspotDesc = $(el).find( ".product-info" ).html();
      var defaultHotspotIcon = $(el).children('i');
      var numHotspot = $(el).children('.num');
      var numHotspotVal = numHotspot.text();
      //console.log('aaa', numHotspotVal);

      //var hotspotDesc = $(el).find( ".product-info" ).html();

      $(numHotspotVal).appendTo('body');



      $(numHotspot).addClass('hidden').removeClass('visible');


      if ($(defaultHotspotIcon).hasClass('ion-android-search hotspot-inactive')) {
        if ($('.ion-android-close').hasClass('hotspot-active')) {
          $('.ion-android-close').removeClass().addClass('ion-android-search hotspot-inactive');
          $(numHotspot).addClass('visible').removeClass('hidden');
        }
        $('.hotspot-details-placeholder').empty().removeClass('hidden').addClass('visible');
        $('.hotspot-details-placeholder').html(hotspotDesc);
        $('.hotspot-details-placeholder h2 a').prepend('<span>' + numHotspotVal +'.<span> ');
        $('.hotspot-default').removeClass('visible').addClass('hidden');
        $(defaultHotspotIcon).removeClass().addClass('ion-android-close hotspot-active');
        $(numHotspot).addClass('hidden').removeClass('visible');
      } else if($(defaultHotspotIcon).hasClass('ion-android-close hotspot-active')) {
        $('.hotspot-details-placeholder').empty().removeClass('visible').addClass('hidden');
        $('.hotspot-default').removeClass('hidden').addClass('visible');
        $(defaultHotspotIcon).removeClass().addClass('ion-android-search hotspot-inactive');
        $(numHotspot).addClass('visible').removeClass('hidden');
      }


    });
  });

  $(window).scroll(function() {
    "use strict";
    var scroll = $(window).scrollTop();
    if (scroll >= 180) {
      $(".main-nav").addClass("fixed-navbar");
    } else {
      $(".main-nav").removeClass("fixed-navbar");
    }
  });

  $('.close-topbar').click(function(e) {
    e.preventDefault();
    console.log('clicked');
    $('.header-topbar').fadeOut( "slow" );
  });

  // Check viewport width

  var productTitle = $('.catalog-product-view .product-info-main .name')
  var productType = $('.catalog-product-view .new-product');
  var productPrice = $('.product-info-main .product-info-price');
  var searchBox = $('.minisearch');

  $(window).resize(function() {


    var viewportWidth = $(window).width();
    var viewportHeight = $(window).height();

    console.log('Viewport', viewportWidth)

    if (viewportWidth < 600) {
      $(productPrice).prependTo('.catalog-product-view .product.media');
      $(productType).prependTo('.catalog-product-view .product.media');
      $(productTitle).prependTo('.catalog-product-view .product.media');


      $('.footer-subscribe').prependTo('.footer-links');
      $('.footer-social').insertAfter('.footer-subscribe');
      var mobileContent = $('.mobile-content').width();

      // if (mobileContent < 600) {
      //   $('.issue-date').prependTo('.mobile-content');
      //   $('.issue-title').appendTo('.mobile-content');
      // }

      // Search Box
      $(searchBox).prependTo('.mobile-full');
      $(searchBox).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
      });

      // Sidebar Navigation (CMS pages)
      $('ul.faq-sidebar, .sidebar ul').each(function() {
        var $select = $('<select />');

        $(this).find('a').each(function() {
            var $option = $('<option />');
            $option.attr('value', $(this).attr('href')).html($(this).html());
            $select.append($option);
        });
        $(this).replaceWith($select);
      });

      // Redirect on click - select (sidebar)
      $('.sidebar select').change( function() {
        location.href = $(this).val();
      });

    }
  });

  $(document).ajaxComplete(function() {
    var count = $('.review-items').children('li').length;
    if (count !== 0) {
      $('.review-item').slice(3).hide()
      $('.btn-reviews span').text('(' + count + ')');
      $('.btn-reviews').removeClass('display-none');
      $('.btn-reviews').click(function(e) {
        e.preventDefault();
        $('.review-item').slice(3).fadeIn(1000);
        $('.btn-reviews').addClass('display-none');
      });
    }

    $('.full-review').addClass('display-none');

    $('.review-read-more').on('click', function(e) {
      e.preventDefault();
      $(this).next('.full-review').removeClass('display-none');
      $(this).addClass('display-none')
      $(this).parent().next().removeClass('display-none');
    });

    $('.review-read-less').on('click', function(e) {
      e.preventDefault();
      $(this).parent().addClass('display-none');
      $(this).parent().prev().children().removeClass('display-none');
    });

  });


  $(document).ready(function() {
    $('.panel-collapse').on('show.bs.collapse', function () {
      $(this).siblings('.panel-heading').addClass('active');
    });

    $('.panel-collapse').on('hide.bs.collapse', function () {
      $(this).siblings('.panel-heading').removeClass('active');
    });
  });


  $('.btn-toggle-form').click(function(e) {
    e.preventDefault();
    $(this).addClass('display-none');
    $('.review-form').fadeIn( "slow" );
  });




  $('.editor-bio').each(function(event){
    var max_length = 250;
    if($(this).html().length > max_length){
      var short_content   = $(this).html().substr(0, max_length);
      var long_content  = $(this).html().substr(max_length);

      $(this).html(short_content+
             '<span class="editor-readmore block"><a href="#" class="read_more hover-effect">Read More</a></span>'+
             '<span class="more_text" style="display:none;">'+long_content+'</span>');

      $(this).find('a.read_more').click(function(event){
        event.preventDefault();
        $(this).hide();
        $('.editor-readmore').hide();
        $(this).parents('.editor-bio').find('.more_text').show();
      });
    }
  });


    $('.styled-select select').change( function() {
      $('.styled-select').addClass('mynameisabhishek');
      location.href = $(this).val();
    });


});
