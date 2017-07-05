require(['jquery', 'jquery.bootstrap', 'mage/select2'], function($){

  jQuery.noConflict();

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

  $('p').each(function() {
    var $this = $(this);
    if($this.html().replace(/\s|&nbsp;/g, '').length == 0)
    $this.remove();
  });

  jQuery(document).ready(function () {

    // register popup tabs 
    $('.tabs-replica li, .create-btn').click(function(event) {
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


      if ($(defaultHotspotIcon).hasClass('ion-android-search hotspot-inactive')) {
        if ($('.ion-android-close').hasClass('hotspot-active')) {
          $('.lookbookslider-container .ion-android-close').removeClass().addClass('ion-android-search hotspot-inactive');
          $(numHotspot).addClass('visible').removeClass('hidden');
        }
        $('.hotspot-details-placeholder').empty().removeClass('hidden').addClass('visible');
        $('.hotspot-details-placeholder').html(hotspotDesc);
        $('.hotspot-details-placeholder h2 a').prepend('<span>' + numHotspotVal +'.<span> ');
        $('.hotspot-default').removeClass('visible').addClass('hidden');
        $(defaultHotspotIcon).removeClass().addClass('ion-android-close hotspot-active');
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


    if (viewportWidth < 600) {
      // Search Box
      $(searchBox).prependTo('.mobile-full');
      $(searchBox).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
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


  /*

  $(document).ready(function() {
    $('.panel-collapse').on('show.bs.collapse', function () {
      $(this).siblings('.panel-heading').addClass('active');
    });

    $('.panel-collapse').on('hide.bs.collapse', function () {
      $(this).siblings('.panel-heading').removeClass('active');
    });
  });

  */


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


     $('.tags-index-view .styled-select select').change( function() {
      location.href = $(this).val();
    });






  // Adding dynamic form key to modal add to wishlist button
  $('.js-add-formKey').click(function(e) {
    var theKey = $('input[name=form_key]').val();
    e.originalEvent.currentTarget.href = $(this).attr('href') + theKey;
  });


  // menu launch hover effect on click
  $('#awemenu .am-tabs .am-tab.shop-menu > a').click(function(e) {
    e.preventDefault();
    $(e.target).parent().toggleClass('am-tab-hover');
  });


  // close homepage popup on click outside
  $("body").click(function(e) {
    var checkclass = e.target.className ;  
      if (checkclass.indexOf("_show") >= 0){
        $( ".action-close" ).trigger( "click" );
      }
    }
  );

  //dropdown qty selectors on product details page & cart pages
  jQuery(document).ready(function () {
    $('.product-details-select').select2();
    $('#form-validate .table-wrapper .qty select').select2();
    $('#form-validate .table-wrapper .qty select').on('select2:select', function (evt) {
      $('#form-validate .update').trigger("click");
    }
    );
  });

    //checkout login flow - needs to be localized to just that page
  $(document).ready(checkDOMChange());
  function checkDOMChange()
  {
    if ($(".checkout-index-index").length) {
      if ($(".checkout-progress-bar").length) {
        $('#guest-checkout-btn').click(function() {
          var email = $("input[name='guest-checkout-email']").val();
          $("#customer-email").val(email);
          $('.checkout-login-container').hide();
          $('.checkout-login-container').siblings().not(".opc-estimated-wrapper").show();
        });
        $("input[name='guest-checkout-email']").keydown(function(event){
          if(event.keyCode == 13){
            $("#guest-checkout-btn").click();
          }
        });
        if (!window.isCustomerLoggedIn) {
          $('.checkout-login-container').siblings().hide();
          //listen for a response from the login request, so we can catch the error message & display it
					(function() {
						var origOpen = XMLHttpRequest.prototype.open;
						XMLHttpRequest.prototype.open = function() {
							this.addEventListener('load', function() {
								if (this.responseURL.includes("shophh/customer/ajax")) {
                  var resp = $.parseJSON(this.responseText);
                  if(resp.errors) {
                    $("#checkout-login-messages").show();
                    $("#checkout-login-messages").html(resp.message);
                  }
								}
							});
							origOpen.apply(this, arguments);
						};
					})();
        }
      }
      else {
        setTimeout( checkDOMChange, 100 );
      }
    }
  }
  // mobile nav dropdown
  $('.mobile-nav .nav-tabs.nav-justified .title a').click(function(e) {
    $('.mobile-nav').toggleClass('expanded');
  });
  // Live Chat Toggle
  $(document).ready(function() {
    $("#live-chat").click(function(e) {
      e.preventDefault();
      e.stopPropagation();
      $("#launcher").show();
      $("#launcher").contents().find(".src-component-Launcher-wrapper").click();
    });
  });
});

// Live Chat Code
window.zEmbed || function(e, t) {
    var n, o, d, i, s, a = [],
        r = document.createElement("iframe");
    window.zEmbed = function() {
        a.push(arguments)
    }, window.zE = window.zE || window.zEmbed, r.src = "javascript:false", r.title = "", r.role = "presentation", (r.frameElement || r).style.cssText = "display: none", d = document.getElementsByTagName("script"), d = d[d.length - 1], d.parentNode.insertBefore(r, d), i = r.contentWindow, s = i.document;
    try {
        o = s
    } catch (e) {
        n = document.domain, r.src = 'javascript:var d=document.open();d.domain="' + n + '";void(0);', o = s
    }
    o.open()._l = function() {
        var e = this.createElement("script");
        n && (this.domain = n), e.id = "js-iframe-async", e.src = "https://assets.zendesk.com/embeddable_framework/main.js", this.t = +new Date, this.zendeskHost = "hhmedia.zendesk.com", this.zEQueue = a, this.body.appendChild(e)
    }, o.write('<body onload="document._l();">'), o.close()
}();
