$.fn.sidebar = function() {
	var block = $(this);
	var minMenuWidth = $('.wrapper>ul').width();
	var initialMenuWidth = block.width();

	function state() {
		return {
			closed: sessionStorage.getItem('sidebarClosed') == 1,
			minimized: $('.wrapper>ul>li.active', block).length == 0
		};
	}

	// Width for applying to `block` element
	function widths() {
		var minimized =  parseInt($('.wrapper>ul', block).width()) + 5;
		return {
			minimized: minimized,
			maximized: parseInt(sessionStorage.getItem('menuWidth')) + minimized,
			initial: initialMenuWidth,
			maximum: 500 + minimized,
			current: parseInt(sessionStorage.getItem("maxMenuWidth")) + minimized
		};
	}

	function updateWidth() {
		var currentWidth;
		if(state().minimized) {
			currentWidth = widths().minimized;
		}
		else if (sessionStorage.getItem("maxMenuWidth")) {
			currentWidth = widths().current;
		}
		else
			currentWidth = widths().initial;
		if(currentWidth > widths().maximum)
			currentWidth = widths().maximum;
		block.width(currentWidth);
		if(state().closed)
			block.css({'margin-left' : -block.outerWidth() + 5});
		var resizer = $('.resize', block);
		if(resizer.hasClass('ui-draggable'))
			if(!state().closed && !state().minimized)
				resizer.draggable("enable");
			else
				resizer.draggable("disable");
		$('.resize', block).css('left', false);
	}

	function resetState() {
		$('.wrapper>ul', this).find('li.active').removeClass('active');
		$('.wrapper>ul', this).find('li.selected').addClass('active');
		updateWidth();
		if(state().minimized && state().closed) {
			block.css('margin-left', -widths().minimized + 5);
		}
	}

	function moveSidebar(leftMargin, callback) {
		callback = callback || function() {};
		block.animate({'margin-left': leftMargin}, 200, callback);
		$('section.main').animate({'margin-left': leftMargin ? 15 : 0}, 200, function () {
			$(window).trigger('resize');
			updateWidth();
		});
	}

	function hideSidebar() {
		sessionStorage.setItem('sidebarClosed', 1);
		$('.hide', block).addClass('active');
		moveSidebar(-block.outerWidth() + 5, resetState);
	}

	function initialHideSidebar() {
		block.css({'margin-left': -block.outerWidth() + 5});
		$('section.main').css({'margin-left': 15});
		$('.hide', block).addClass('active');
	}

	function showSidebar() {
		sessionStorage.setItem('sidebarClosed', 0);
		$('.hide', block).removeClass('active');
		moveSidebar(0);
	}

	$('.wrapper>ul >li', this).click(function () {
		$(this).parent('ul').find('>li.active').not(this).removeClass('active');
		$(this).addClass('active');
		updateWidth();
	});
	updateWidth();
	$('.hide', this).click(function () {
		if(state().closed)
			showSidebar();
		else
			hideSidebar();
	});
	if(state().closed)
		initialHideSidebar();
	$('.resize', this).draggable({
		axis: "x",
		containment: [ 200, 0, 500, 0 ],
		disabled: true,
		drag: function (event, ui) {
			var w = ui.position.left + 5;
			sessionStorage.setItem("maxMenuWidth", w - minMenuWidth);
			block.width(w);
			$(window).trigger('resize');
		}
	});
	if(!state().closed && !state().minimized)
		$('.resize', this).draggable("enable");
}