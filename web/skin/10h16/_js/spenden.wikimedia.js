var formInitialized = false;

$(function() {
	/* country-specific validation patterns for zip codes */
	var countrySpecifics = {
		generic: {
			'post-code': {
				pattern: '{1,}',
				placeholder: 'z. B. 10117',
				title: 'Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Berlin'
			},
			email: {
				placeholder: 'z. B. name@domain.com'
			}
		},
		DE: {
			'post-code': {
				pattern: '\\s*[0-9]{5}\\s*',
				placeholder: 'z. B. 10117',
				title: 'Fünfstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Berlin'
			},
			email: {
				placeholder: 'z. B. name@domain.de'
			}
		},
		AT: {
			'post-code': {
				pattern: '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 4020',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Linz'
			},
			email: {
				placeholder: 'z. B. name@domain.at'
			}
		},
		CH: {
			'post-code': {
				pattern: '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 3556',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Trub'
			},
			email: {
				placeholder: 'z. B. name@domain.ch'
			}
		}
	};

    if ($(".amount-custom :text").val() !== "") {
      $(".display-amount").text($(".amount-custom :text").val());
    }

    if (($('#membership-type-2').length > 0) && $("#membership-type-2").is(':checked')) {
      $("#address-type-2").parent().hide();
      $("#address-type-1").trigger('click');
    }

    /* slide toggle */
    function initSlideToggle() {
      $('a.slide-toggle').click(function (e) {
        var $toggle = $(this);

        if ($toggle.hasClass('active')) {
          $($toggle.attr('data-slide-rel'))
              .removeClass('opened')
              .slideUp(600, checkInvisibleInput)
              .animate(
                  {opacity: 0},
                  {queue: false, duration: 600}
              );

          $toggle.removeClass('active');
        } else {
          $($toggle.attr('data-slide-rel'))
              .addClass('opened')
              .slideDown(600, checkInvisibleInput)
              .animate(
                  {opacity: 1},
                  {queue: false, duration: 600}
              );

          $toggle.addClass('active');
        }

        e.preventDefault();
      });
    }


    /* check invisible input elems */
    function checkInvisibleInput() {
      // remove required attribute for hidden inputs
      $(':input.required:hidden').removeAttr('required');
      $(':input.required:visible').prop('required', true);
    }


    /* tab toggle */
    /* Was only used in some old forms, may be deleted if not used by March 2016 */
    function initTabToggle() {
      $('a.tab-toggle').click(function (e) {

        $($(this).attr('data-tab-group-rel')).find('.tab').addClass('no-display');
        $($(this).attr('data-tab-rel')).removeClass('no-display');

        checkInvisibleInput();

        e.preventDefault();
      });
    }


    /* tooltip */
    function initToolTip() {
      /* tooltip */
      $('.tooltip').tooltip({position: {my: "right-15 center", at: "left center"}});
      $('.tooltip').click(function (e) {
        e.preventDefault();
      });
    }

    /* radio button toggle */
    function initRadioBtnToggle() {
      $(':radio').change(function (e) {
        var slides = [];
        $(':radio.slide-toggle').each(function () {
          slides.push($(this).attr('data-slide-rel'));
        });

        $(':radio.slide-toggle').each(function () {
          var $slide = $($(this).attr('data-slide-rel'));

          if ($(this).is(':checked') == !$(this).hasClass('slide-toggle-invert')) {

            // show child if child is slide in another slide, prevent flickering / blopping slide children
            $.each(slides, function (index, item) {
              if ($slide.has($(item)).length > 0 && $(':radio[data-slide-rel="' + item + '"]').is(':checked') && !$slide.hasClass('opened')) {
                $(item).stop().clearQueue().show().removeAttr('style');
              }
            });

            // open
            $slide
                .addClass('opened')
                .slideDown(600, checkInvisibleInput)
                .animate(
                    {opacity: 1},
                    {queue: false, duration: 600}
                );
          } else {

            //close
            $slide
                .removeClass('opened')
                .slideUp(600, checkInvisibleInput)
                .animate(
                    {opacity: 0},
                    {queue: false, duration: 600}
                );
          }

        });

        $(':radio.tab-toggle').each(function () {
          if ($(this).is(':checked')) {
            $($(this).attr('data-tab-rel')).removeClass('no-display');
          } else {
            $($(this).attr('data-tab-rel'))
                .addClass('no-display');
          }

          checkInvisibleInput();
        });
        $(':radio.tab-toggle:checked').each(function () {
          $($(this).attr('data-tab-rel'))
              .removeClass('no-display');

          checkInvisibleInput();
        });

      });

    }


    /* styled select boxes */
    function initStyledSelect() {
      $('select').selectmenu({
            positionOptions: {
              collision: 'none'
            }
          })
          .on('change', function (evt, params) {
            var $option = $(this).find('[value="' + $(this).find('option:selected').val() + '"]');

            if ($option.attr('data-behavior') == 'placeholder') {
              $('#' + $(this).attr('id') + '-button').addClass('placeholder');
            } else {
              $('#' + $(this).attr('id') + '-button').removeClass('placeholder');
            }
          })
          .change();

      // adjust position, margins & dimension
      $('.ui-selectmenu').each(function () {
        var newWidth = $(this).width() * 2 - $(this).outerWidth();
        $(this).width(newWidth);
      });
      $('.ui-selectmenu-menu').each(function () {
        var $dropDown = $(this).find('.ui-selectmenu-menu-dropdown');
        $dropDown.width($dropDown.width() - 2);
      });
    }

    $('#country').selectmenu({
      change: function () {
        var countryCode = 'generic';
        if (countrySpecifics[$(this).val()]) {
          countryCode = $(this).val();
        }

        $.each(countrySpecifics[countryCode], function (id, values) {
          var $field = $('#' + id);
          $.each(values, function (key, value) {
            $field.attr(key, value);
          });
        });
      }
    });

    /* amount-list */
    $('.amount-list').each(function () {
      var $container = $(this);

      $container.find(':radio').change(function (e) {
        $('.display-amount').text($container.find(':radio:checked').val());
      });

      $container.find('.amount-custom :text').on('load change keyup paste focus', function () {
        var val = $.trim($(this).val());
        if (val == '') val = 0;
        //val = isNaN(parseInt(val)) ? 0 : parseInt(val);
        $('.display-amount').text(val);
      });
    });


    /* donation-payment */
    $('#donation-payment').each(function () {
      var $container = $(this);

      /* change title and show related content */
      $container.find('.payment-type-list :radio').change(function (e) {

        $container.find('.section-title .h2').addClass('no-display');
        $container.find('.section-title .display-' + $(this).attr('id')).removeClass('no-display');

        $container.find('.tab-group .payment-type .tab').addClass('no-display');
        $container.find('.section-title .display-' + $(this).attr('id')).removeClass('no-display');
      });
    });


    /* iOS fix - label onclick, see http://stackoverflow.com/questions/7358781/tapping-on-label-in-mobile-safari */
    if (navigator.userAgent.match(/Safari/)) {
      $('label').click(function (evt) {
        evt.stopPropagation();
      });
    }


    initSlideToggle();
    initTabToggle();
    initToolTip();
    initWLightbox();
    initRadioBtnToggle();
    initStyledSelect();

    formInitialized = true;

  //additional methods for form controlling

  $(".interval-radio").click(function () {
    $("#interval-hidden").val($("input[name='recurring']:checked").val());
  });

    /* periode-1 */
    $('#periode-1').change(function(e){
      if ( e.target.checked ) {
        $('.interval-radio').prop('checked', false);
        $('#interval-display').text($( "label[for='periode-1']" ).text());
      }
    });

    /* periode-2-list */
    $('.periode-2-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        if ( e.target.checked ) {
          $('#interval-display').text($( "label[for='" + $container.find(':radio:checked').attr('id') + "']" ).text());
        }
      });
    });

    /* periode-2-list */
    $('.payment-type-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        if ( $container.find( ':radio:checked' ).length > 0 ) {
            $('#payment-display').text(" per " + $( "label[for='" + $container.find(':radio:checked').attr('id') + "']" ).text());
        }
      });
    });


});






