var modalChange = true;
var confirmClose = false;

$(function () {
    // TODO доработать систему ajax смены контента страницы
    /*$('#modules-menu a').click(function(){
     $.get($(this).attr('href'), {}, function(data){
     $('section.main .wrapper').html(data);
     });
     return false;
     });*/

    // Правило для модалки для уведомлений о закрытии и обновлении грида
    $('#iframe').find('form').each(function () {
        var el = $(this).find('input, select, textarea');
        if (el.length) parent.modalChange = false;
        el.change(function () {
            parent.modalChange = true;
            parent.confirmClose = true;
        });
        $(this).submit(function () {
            parent.confirmClose = false;
            parent.modalChange = true;
        });
    });

    // сброс кэша на ajax
    $('.clearMenu ul a').click(function () {
        $.get(this.href, {back: 0}, function () {
            window.location.reload();
        });
        return false;
    });
    $('.clearMenu >a').dblclick(function () {
        $.get(this.href, {back: 0}, function () {
            window.location.reload();
        });
    });

    // Правило для грида для показа пакетных действий
    var form = $('.wrapper > form').on('change', 'input[type="checkbox"]', function () {
        if ($(':checked', form).length)
            $('.gridActions').show();
        else
            $('.gridActions').hide();
        $(window).trigger('resize');
        return true;
    });

    // Модификация для datePicker в формах модалки
    // @todo интегрировать в виджет!
    $('.datePicker').each(function () {
        if(!$(this).val() && !$(this).hasClass('autoNow')) return;
        var time = $(this).val() ? $(this).val() * 1000 : Date.now();
        var d = new Date(time);
        var date = [d.getDate(), d.getMonth() + 1, d.getFullYear()];
        for (var i = 0; i < 2; i++) {
            if (date[i] < 10) {
                date[i] = "0" + date[i];
            }
        }
        $(this).val(date.join("."));
    });

    // @todo сделать обход!
    $('.contentLoading').hide();

    // Функционал работы сайдбара
    $('aside.main').sidebar();

    // Показ меню
    $('.dropdown').dropdown();
});

// Действие при зовершении загрузки фото
function uploadComplete(event, status, fileName, response) {
    var container = $(event.currentTarget);
    var formContainer = container.find('.uploaded-photos');
    if (formContainer.length == 0) {
        formContainer = $('<ul class="uploaded-photos thumbnails" />').prependTo(container);
    }
    $(response.content).appendTo(formContainer);
}

function beforeView(e) {
    window.open($(e).attr('href'));
}

function beforeEdit(e) {
    modalIFrame(e);
}

function beforeClone(e) {
    modalIFrame(e);
}

function beforeConfig(e) {
    modalIFrame(e);
}

function beforeDelete(e) {
    if (confirm('Вы уверены, что хотите удалить эту запись?')) {
        modalChange = true;
        modalIFrame(e);
    }
    else
        return false;
}

function beforeNote(e) {
    var data = $('<div class="noteForm">');
    $('<textarea>').appendTo(data).keypress(function (event) {
        if ((event.ctrlKey) && ((event.keyCode == 0xA) || (event.keyCode == 0xD)))
            $.colorbox.close();
    });
    $.colorbox({
        html: data,
        fixed: true,
        speed: 0,
        onClosed: function () {
            $.post(e.href, {text: $('textarea', data).val()});
        }
    });
    return false;
}

// @todo Построитель баннеров вынести в отдельный Виджет
function bannerCreate() {
    var block;
    if (block = $('.gridBanners')) {
        var col;
        var h = 51;
        var bw = 0;
        $('.mounth-block > ul > li', block).each(function () {
            bw += $(this).outerWidth()
        });
        var lines = $('<div class="lines">');
        lines.css('width', bw);
        $('.mounth-block').css('width', bw);

        var offset = $('.mounth-block .active').position().left;
        var line = $('<div class="line">');
        line.css('left', offset + 14);
        line.appendTo(lines);

        block.scrollLeft(offset - block.width() / 3);

        var greenLine = $('<div class="greenLine">');
        greenLine.appendTo(lines);
        block.mousemove(function (e) {
            var x = e.pageX;
            greenLine.css('left', block.scrollLeft() + x - block.offset().left - 2);
        });

        $.each(bannerData, function (key, val) {
            var item = $('<a href="http://no-job.ru/rapanel/content/edit.html?url=banner&id=' + val.id + '" class="item" onclick="modalIFrame(this);return false;">').css('background', val.color).attr('title', val.name);
            var ico = $('<img>');
            var title = $('<span class="title">').text(val.name);
            if (val.ico && val.ico != 'false') ico.attr('src', val.ico).appendTo(item);
            title.appendTo(item);
            item.appendTo(lines);
            var k = bw / (val.total);
            item.css({
                top: h * val.line + 17,
                left: k * val.from + k / 2,
                width: k * (val.to - val.from)
            });
            col = key;
        });
        lines.css('height', h * (col + 1)).appendTo(block);
        lines.mouseover(function () {
            greenLine.css('display', 'block');
        });
        lines.mouseout(function () {
            greenLine.css('display', 'none');
        });
        setTimeout(function () {
            var formH = $('section.main form').height();
            var rowH = $('.row.top').outerHeight();
            block.css('height', formH - rowH);
        }, 1)
    }
}

(function ($) {
    $.each(['show', 'hide'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);

(function ($) {
    $.fn.extend({
        onShow: function (callback, unbind) {
            return this.each(function () {
                var _this = this;
                var bindopt = (unbind == undefined) ? true : unbind;
                if ($.isFunction(callback)) {
                    if ($(_this).is(':hidden')) {
                        var checkVis = function () {
                            if ($(_this).is(':visible')) {
                                callback.call(_this);
                                if (bindopt) {
                                    $('body').unbind('click keyup keydown', checkVis);
                                }
                            }
                        }
                        $('body').bind('click keyup keydown', checkVis);
                    }
                    else {
                        callback.call(_this);
                    }
                }
            });
        }
    });
})(jQuery);