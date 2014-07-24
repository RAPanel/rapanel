$.prototype.sidebar = function() {
	var block = $(this);
	$('.wrapper>ul >li', this).click(function () {
		$(this).parent('ul').find('>li.active').not(this).removeClass('active');
		$(this).addClass('active')
	});
	$('.hide', this).click(function () {
		var left;
		if ($(this).hasClass('active')) {
			left = 0;
			$('.resize').draggable({containment: [200, 0, 500, 0]});
			sessionStorage.setItem('asideClosed', 0);
		}
		else {
			left = -block.outerWidth() + 5;
			$('.resize').draggable({containment: [0, 0, 0, 0]});
			sessionStorage.setItem('asideClosed', 1);
		}
		block.animate({'margin-left': left}, 200);
		$(this).toggleClass('active');
		$('section.main').animate({'margin-left': left ? 15 : 0}, 200, function () {
			$(window).trigger('resize');
		});
	});
	$(".resize", this).draggable({
		axis: "x",
		containment: [ 200, 0, 500, 0 ],
		drag: function (event, ui) {
			var w = ui.position.left + 5;
			sessionStorage.setItem("menuWidth", w);
			block.width(w);
			$(window).trigger('resize');
		}
	});
	if (sessionStorage.getItem("menuWidth")) {
		block.width(sessionStorage.getItem("menuWidth"));
	}
	if (sessionStorage.getItem('asideClosed') == 1) {
		$('.hide', block).trigger('click');
	}
}