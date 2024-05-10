//lenders-slider
jQuery('.lenders-slider1').slick({
  slidesToShow: 6,
  slidesToScroll: 1,
  arrows: false,
  dots: false,
  autoplay: true,
  autoplaySpeed: 0,
  speed: 6000,
  cssEase: 'linear', 
  focusOnSelect: false,
  pauseOnFocus: false,
  pauseOnHover: false,
  touchMove: false,
   responsive: [
    {
      breakpoint: 1025,
      settings: {
        slidesToShow: 4
      }
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 3
      }
    }
  ]
});

jQuery('.lenders-slider2').slick({
  slidesToShow: 5,
  slidesToScroll: 1,
  arrows: false,
  dots: false,
  autoplay: true,
  autoplaySpeed: 0,
  speed: 6000,
  cssEase: 'linear', 
  focusOnSelect: false,
  pauseOnFocus: false,
  pauseOnHover: false,
  touchMove: false,
   responsive: [
    {
      breakpoint: 1025,
      settings: {
        slidesToShow: 4
      }
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 3
      }
    }
  ]
}); 

//awards-slider
jQuery('.awards-slider1').slick({
  slidesToShow: 10,
  slidesToScroll: 1,
  arrows: false,
  dots: false,
  autoplay: true,
  autoplaySpeed: 0,
  speed: 3000,
  cssEase: 'linear', 
  focusOnSelect: false,
  pauseOnFocus: false,
  pauseOnHover: false,
  touchMove: false,
   responsive: [
    {
      breakpoint: 1025,
      settings: {
        slidesToShow: 6
      }
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 4
      }
    }
  ]
});

jQuery('.awards-slider2').slick({
  slidesToShow: 10,
  slidesToScroll: 1,
  arrows: false,
  dots: false,
  autoplay: true,
  autoplaySpeed: 0,
  speed: 3000,
  cssEase: 'linear', 
  focusOnSelect: false,
  pauseOnFocus: false,
  pauseOnHover: false,
  touchMove: false,
   responsive: [
    {
      breakpoint: 1025,
      settings: {
        slidesToShow: 6
      }
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 4
      }
    }
  ]
});

//testimonial-slider
jQuery('.htestimonials-slider').slick({
  slidesToShow: 3,
  slidesToScroll: 1,
  dots: false,
  autoplay: true,
    autoplaySpeed: 2000,
   responsive: [
    {
      breakpoint: 1024,
      settings: {
        arrows: false
      }
    },
    {
      breakpoint: 768,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        arrows: false
      }
    },
    {
      breakpoint: 481,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false
      }
    }
  ]
});

//back-top-top
jQuery(".footer-social-col-back a").on('click', function(e) {
    e.preventDefault();
    jQuery('html, body').animate({
        scrollTop: 0
    }, '900');
});

//header-sticky
jQuery(window).bind('scroll', function () {
    if (jQuery(window).scrollTop() > 1) {
        jQuery('.header-sec').addClass('sticky');
    } else {
        jQuery('.header-sec').removeClass('sticky');
    }
});