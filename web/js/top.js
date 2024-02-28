$(function () {

    var offset = 300,
        duration = 500,
        top_section = $('.tothetop'),
        toTopButton = $('a#top');
    // showing and hiding button according to scroll amount (in pixels)
    $(window).scroll(function () {
        if ($(this).scrollTop() > offset) {
            $(top_section).fadeIn(duration);
        } else {
            $(top_section).fadeOut(duration);

        }
    });

    // activate smooth scroll to top when clicking on the button

    $(toTopButton).click(function (e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 700);
    });

});
