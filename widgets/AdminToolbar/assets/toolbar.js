;
(function ($) {

    function getCookie() {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + "admin_toolbar_expanded".replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    function setCookie(value) {
        var date = new Date(new Date().getTime() + 30 * 24 * 3600);
        document.cookie = "admin_toolbar_expanded=" + value + "; path=/; expires=" + date.toUTCString();
    }

    function expand($toolbar) {
        if($toolbar.hasClass('expanded')) {
            $toolbar.removeClass('expanded');
            $toolbar.animate({
                top: -1 * $toolbar.outerHeight()
            });
            setCookie('0');
        } else {
            $toolbar.addClass('expanded');
            $toolbar.animate({
                top: 0
            });
            setCookie('1');
        }
    }

    $.extend($.fn, {
        adminToolbar: function () {
            var toolbar = this;
            var expanded = getCookie() == '1';
            if(expanded) {
                toolbar.addClass('expanded');
            } else {
                toolbar.css('top', -1 * toolbar.height());
            }
            toolbar.fadeIn();
            toolbar.find('.toolbar-toggler').click(function() {
                expand(toolbar);
                return false;
            });
        }
    });

})(jQuery);