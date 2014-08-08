function adminToolbar() {
    var t = 150;
    $('nav.rp_userMenu')
        .on('click', '.rp_menu > a', function () {
            var e = $(this).parent('li');
            $('>ul', e).slideToggle(t);
            return false;
        })
        .on('dblclick', '.rp_menu > a', function () {
            window.location.href = $(this).attr('href');
            return false;
        })
        .on('click', '.rp_editMode > a', function () {
            var e = $(this).parent('li');
            e.toggleClass('active');
            if (!e.hasClass('active')) {
                $.cookie('editMode', 0, {expires: 1, path: '/'});
                $('span', e).text('(выключен)');
            } else {
                $.cookie('editMode', 1, {expires: 1, path: '/'});
                $('span', e).text('(включен)');
            }
            window.location.reload();
            return false;
        })
        .on('click', '.rp_turn > a', function () {
            $.cookie('userMenu', 0, {expires: 1, path: '/'});
            var e = $(this).parents('nav.rp_userMenu');
            e.removeClass('active');
            $('.rp_menu', e).find('ul').slideUp(t);
            return false;
        })
        .on('click', '.rp_exit > a, .rp_reset > a', function () {
            if ($(this).parent().hasClass('loading'))
                return false;
            $(this).parent().addClass('loading');
            $.get($(this).attr('href'), {ajax: 1, callback: 'int'}, function () {
                window.location.reload();
            });
            return false;
        })
        .on('click', '.rp_editSite > a', function () {
            modalIFrame(this);
            return false;
        });

    $('.ra-panel').click(function () {
        $('nav.rp_userMenu').addClass('active');
        $.cookie('userMenu', 1, {expires: 1, path: '/'});
        return false;
    });

}