function adminToolbar() {
    var t = 150;
    $('nav.userMenu')
        .on('click', '.menu > a', function () {
            var e = $(this).parent('li');
            $('>ul', e).slideToggle(t);
            return false;
        })
        .on('dblclick', '.menu > a', function () {
            window.location.href = $(this).attr('href');
            return false;
        })
        .on('click', '.editMode > a', function () {
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
        .on('click', '.turn > a', function () {
            $.cookie('userMenu', 0, {expires: 1, path: '/'});
            var e = $(this).parents('nav.userMenu');
            e.removeClass('active');
            $('.menu', e).find('ul').slideUp(t);
            return false;
        })
        .on('click', '.exit > a, .reset > a', function () {
            if ($(this).parent().hasClass('loading'))
                return false;
            $(this).parent().addClass('loading');
            $.get($(this).attr('href'), {ajax: 1, callback: 'int'}, function () {
                window.location.reload();
            });
            return false;
        })
        .on('click', '.editSite > a', function () {
            modalIFrame(this);
            return false;
        });

    $('.ra-panel').click(function () {
        $('nav.userMenu').addClass('active');
        $.cookie('userMenu', 1, {expires: 1, path: '/'});
        return false;
    });

}