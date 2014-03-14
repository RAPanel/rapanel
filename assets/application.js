var modalChange = false;
var confirmClose = false;

$(function () {
    $('.datePicker').each(function () {
        var time = $(this).val() ? $(this).val() * 1000 : Date.now();
        var d = new Date(time);
        var date = [ d.getDate(), d.getMonth() + 1, d.getFullYear() ];
        for (var i = 0; i < 2; i++) {
            if (date[i] < 10) {
                date[i] = "0" + date[i];
            }
        }
        $(this).val(date.join("."));
    });
    $('.contentLoading').hide();
    var block = $('aside.main');
    var oldTime, time = 200;
    $('.wrapper>ul >li', block).click(function () {
        $(this).parent('ul').find('>li.active').not(this).removeClass('active');
        $(this).addClass('active')
    });
    $('.hide', block).click(function () {
        var l = -block.outerWidth() + 5;
        if ($(this).hasClass('active')) l = 0;
        block.animate({'margin-left': l}, time);
        sessionStorage.setItem("menuLeft", l);
        $(this).toggleClass('active');
        $('section.main').animate({'margin-left': l ? 15 : 0}, time, function () {
            $(window).trigger('resize')
        });
        time = oldTime;
    });
    $(".resize").draggable({
        axis: "x",
        containment: [ 200, 0, 500, 0 ],
        drag: function (event, ui) {
            var w = ui.position.left + 5;
            sessionStorage.setItem("menuWidth", w);
            block.width(w);
            $(window).trigger('resize')
        }
    });
    if (sessionStorage.getItem("menuWidth")) {
        block.width(sessionStorage.getItem("menuWidth"));
    }
    if (sessionStorage.getItem("menuLeft") < 0) {
        time = 0;
        $('.hide', block).trigger('click');
    }

    $('#iframe').find('#edit-form').each(function () {
        $(this).find('input, select, textarea').change(function () {
            parent.modalChange = true;
            parent.$.modal({
                onBeforeClose: function (el, options) {
                    if (confirm("Вы уверены, что хотите закрыть это окно?\nВсе не сохраненные данные будут утеряны!")) {
                        parent.modalChange = false;
                        return true;
                    } else return false;
                }
            });
        });
        $(this).submit(function () {
            parent.confirmClose = false;
            parent.modalChange = true;
            parent.$.modal({
                onBeforeClose: function (el, options) {
                    return true;
                }
            });
        });
    });
});

function viewMenu(e) {
    $(e).nextAll('.hiddenMenu').slideToggle(100);
}

function sortableTable() {
    var block = '.grid-view tbody';
    $(block).sortable({
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        },
        stop: function (event, ui) {
            var id = [];
            var i = 0;
            $(block).find('tr').each(function () {
                var newClass = (i++ % 2 == 0) ? 'odd' : 'even';
                id[i] = $(this).removeClass('odd').removeClass('even').addClass(newClass)
                    .find('.checkbox-column input').val();
            });
            $.post($('.grid-view').attr('data-url'), {id: id});
        }
    }).disableSelection();
}


function fastChange(e) {
    e = $(e);
    var form = e.parents('form');
    var data = form.serializeArray();
    $.post(form.attr('action'), data, function (data) {
        if (parseInt(data) > 0) $('body').yiiGridView.update(form.find('.grid-view').attr('id'));
        e.val(null);
    });
    return false;
}
/*
 $(document).ready(function () {
 sortableTable();
 $(document).ajaxComplete(function () {
 sortableTable();
 });*/


/* var modulesGrid = $('#modules-grid');
 modulesGrid.find('tbody td').each(function () {
 $(this).css('width', $(this).css('width'));
 });
 if(modulesGrid.length) {
 modulesGrid.find('tbody').sortable({
 update: function (event, ui) {
 var ids = [];
 $(this).find('tr').each(function () {
 ids.push($(this).find('td').first().text());
 });
 var url = modulesGrid.attr('data-url');

 $.ajax({
 url: url,
 type: 'post',
 data: 'ModuleOrder=' + ids.join(','),
 success: function (data) {
 ui.item.addClass('info');
 ui.item.find('td').animate(
 {
 'background-color': '#FAFFFA'
 },
 {
 duration: 800,
 complete: function () {
 $(this).css({'background-color': ''}).parent().removeClass('info');
 }
 }
 );
 }
 });
 }
 });
 }


 var sortableCheckboxes = $('.sortable-checkboxes');
 if(sortableCheckboxes.length)
 sortableCheckboxes.sortable();

 var iframe = $('#iframe');
 if(iframe.length == 0) {
 $(document).on('click', 'a.modal-trigger', function () {
 $.makeModal($(this).attr('href'), function() {
 var grid = $('.grid-view');
 if(grid.length > 0) {
 grid.each(function() {
 $(this).yiiGridView.update($(this).attr('id'));
 });
 }
 }, function() {
 var iframe = $('#modal-iframe').parent();
 if(iframe.hasClass('modified'))
 return confirm("Are you sure?");
 return true;
 });
 return false;
 });
 } else {
 $(document).on('click', 'a.modal-trigger', function() {
 $(this).attr('href', addIframe($(this).attr('href')));
 return true;
 });
 $(document).on('submit', 'form', function() {
 var form = $(this);
 if(!form.hasClass('in-modal-form')) {
 form.attr('target', '_parent');
 }
 return true;
 });
 }


 var viewLink = $('a.newWindow');
 if(viewLink.length > 0) {
 var newWindow = window.open(viewLink.attr('href'), 'viewWindow');
 newWindow.focus();
 }

 var editForm = $('form#edit-form');
 if(editForm.length > 0) {
 editForm.on('change', 'input, select', function(e) {
 var iframe = $('#iframe');
 if(iframe.length) {
 parent.$('#modal-iframe').parent().addClass('modified');
 }
 });
 editForm.on('click', 'input[type="submit"]', function(e) {
 if($(this).attr('name') == 'saveView') {
 editForm.attr('target', '_blank');
 } else {
 editForm.attr('target', '');
 }
 return true;
 });
 }

 $('input.enter-submit').keypress(function(e) {
 if(e.charCode == 13) {
 $(this).closest('form').submit();
 return false;
 }
 });

 function addIframe(url)
 {
 if (url.split('?iframe').length == 1 && url.split('&iframe').length == 1) {
 var symbol = url.split('?').length > 1 ? '&' : '?';
 url += symbol + 'iframe';
 }
 return url;
 }

 $.makeModal = function(url, onClose, beforeClose) {
 var bf = beforeClose || function() { return true; }
 var url = addIframe(url);
 var height = window.innerHeight - 50;
 var modal = $('<div/>', {class: 'themodal loading'}).html('<iframe name="modal" id="modal-iframe" src="' + url + '"></iframe>');
 var iframe = modal.find('iframe');
 iframe.height(height);
 $('<a href="#close" class="close">?</a>').insertBefore(iframe);
 modal.on('click', "a[href='#close']", function() {
 modal.modal().close();
 return false;
 });
 modal.modal().open({
 onClose: onClose,
 beforeClose: bf
 });
 }
 });*/