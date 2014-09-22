// Avoid `console` errors in browsers that lack a console.
(function () {
    var method;
    var noop = function () {
    };
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

// Place any jQuery/helper plugins in here.

function modalIFrame(e) {
    e = $(e);

    if ($(this).parents('#iframe').length) {
        $(this).attr('href', addIframe($(this).attr('href')));
        return true;
    }
    var url = addAttribute(e.attr('data-url') ? e.attr('data-url') : e.attr('href'), 'iframe');
    var height = window.innerHeight - 10;
    var modal = $('<div/>', {class: 'themodal loading'}).html('<iframe id="modal-iframe" src="' + url + '"></iframe>');
    var iframe = modal.find('iframe');
    $('<a href="#close" class="close">✕</a>').insertBefore(iframe);
    modal.on('click', "a[href='#close']", function () {
        modal.modal().close();
        return false;
    });
    modal.modal().open({
        onBeforeClose: function (el, options) {
            console.log(parent.confirmClose);
            if (parent.confirmClose)
                if (confirm("Вы уверены, что хотите закрыть это окно?\nВсе не сохраненные данные будут утеряны!"))
                    parent.modalChange = false;
                else return false;
            parent.confirmClose = false;
            return true;
        },
        onClose: function (el, options) {
            console.log(parent.modalChange);
            if (parent.modalChange) {
                $(".grid-view").each(function () {
                    $(this).yiiGridView.update($(this).attr('id'));
                });
                $('section.main form').each(function () {
                    $(this).submit();
                });
            }
            parent.modalChange = false;
        }
    });
    return false;
}

function addAttribute(url, name) {
    if (url.split('?' + name).length == 1 && url.split('&' + name).length == 1) {
        var symbol = url.split('?').length > 1 ? '&' : '?';
        url += symbol + name + '=1';
    }
    return url;
}

function beforeListViewUpdate(listViewId) {
    var listView = $('#' + listViewId);
    if (listView.length > 0) {
        var listViewTop = listView.offset().top;
        if ($('body').scrollTop() > listViewTop) {
            $('html, body').animate({
                'scrollTop': listViewTop
            });
        }
    }
}

function iFrameAutoResize() {
    $('html,body').css('overflow', 'hidden');
    var contentHeight = 440;
    var modal = parent.$('#modal-iframe').css({'background-color': 'white'});
    var container = modal.parent();
    container.removeClass('loading');
    parent.onresize = function () {
//        centerIFrame();
    };
    function resizeIFrame() {
        var contentMinHeight = parent.innerHeight - 30;
        var top = container.offset().top;
        var iFrameHeight = modal.height();
        contentHeight = $('body').css({height: 'auto', 'min-height': 0}).height();
        if (contentHeight < contentMinHeight) {
            contentHeight = contentMinHeight;
            $('body').height(contentMinHeight);
        }
        if (iFrameHeight != contentHeight) {
            modal.css({height: contentHeight});
//            centerIFrame();
        }
    }

    function centerIFrame() {
        if (parent) var windowHeight = parent.innerHeight;
        if (windowHeight > contentHeight + 20)
            container.css('margin-top', (windowHeight - contentHeight) / 1 / 4);
    }

//    centerIFrame();
    setInterval(resizeIFrame, 1);
}

function fixedHeight(el, minus) {
    var h = $(window).outerHeight();
    var hM = $(minus).outerHeight();
    $(el).height(h - hM).find('>.wrapper').css('overflow', 'auto');
    $(window).resize(function () {
        var h = $(this).outerHeight();
        $(el).height(h - hM);
    });
}


