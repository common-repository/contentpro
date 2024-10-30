(function($){
	$.fn.cpgallery = function(){
		function debug(array){
			$.each(array, function(index, value) { 
				$('.debug').append(index + ': ' + value + '<br>');
			});
		}	
		function get_values(object){
			var map = {
				'innerHeight': object.innerHeight(),
				'innerWidth': object.innerWidth(),
				'outerHeight': object.outerHeight(),
				'outerWidth': object.outerWidth(false), 
				'outerWidth Margin': object.outerWidth(true), 
			};
			return map
		}
		
	function checkKey(e){
		switch (e.keyCode) {
			case 37:
				previous();
				break;
			case 39:
				next();
				break;
			default:
				//alert(e.keyCode);  
		}      
	}
	
	if ($.browser.mozilla) {
			$(document).keypress (checkKey);
	} else {
			$(document).keydown (checkKey);
	}
		
		var isInt = function(n){
			var reInt = new RegExp(/^-?\d+$/);
			if (!reInt.test(n)) {
					return false;
			}
			return true;
		} 
		//SETTINGS
		var gallery = $(this);
		var wrapper = $('.wrapper', this);
		gallery_values = get_values(gallery);
		wrapper_values = get_values(wrapper);
		//PREPARE LIST
		var items = $(this).find('.wrapper ul li');
		items.each(function(i){
			$(this).addClass('item' + (i + 1));
			$(this).width(gallery_values['innerWidth']);
		});
		var current = 1;
		var count = items.length;
		// PRÜFEN OB HASHTAG EINE ZAHL IST;
		var hash = window.location.hash.substr(1);
		if(hash.match('^(0|[1-9][0-9]*)$')) {
			if(hash <= count){	current = window.location.hash.substr(1); }
		}
		function startup(current){
			$('.debug').text(current);
			display(current);
		}
		var pages = "";	
		for(i=1; i <= count; i++){
			add = '<button class="page page' + i + '" value="' + i + '">' + i + '</button>';
			pages = pages.concat(add);
		}
		wrapper.before('<div class="nav_top"><div class="alignright"><button class="back button">&#9664;</button> <button class="forward button">&#9654;</button></div><div style="clear:both"></div></div>');
		gallery.append('<div class="nav_bottom"><div class="pages"></div><div class="alignright"><button class="back button">&#9664;</button> <button class="forward button">&#9654;</button></div><div style="clear:both"></div></div>');
		$('.contentprogallery .pages').append(pages);
		function display(data){
			move(data);
			resize(data);
		}
		function move(current){
			position = gallery_values['innerWidth']*(current-1);
			$('.wrapper ul').animate({
				left: -position,
			}, 250, function() {});
		}
		function resize(current){
			new_height = $('.item'+current+" .panel").height();
			$('.page').removeClass('active');
			$('.page'+current).addClass('active');
			wrapper.animate({
				height: new_height,
			}, 250, function() {});
		}
		function set_resize(current){
			new_height = $('.item'+current+" .panel").height();
			wrapper.height(new_height);
		}
		$(".wrapper").hover(
			function () {
				$('.contentprogallery .description').css("display","block");
			}, 
			function () {
				$('.contentprogallery .description').css("display","none");
			}
		);
		$('button.forward', this).click(function () {
			next();
		});
		$('button.back', this).click(function () {
			previous();
		});
		
		function next(){
			current++;
			if(current > count) current = 1;
			$('.debug').text(current);
			display(current);			
		}
		function previous(){
			current--;
			if(current < 1) current = count;
			$('.debug').text(current);
			display(current);
		}
		
		$('button.page', this).click(function () {
			display($(this).val());
		});
		startup(current);
		set_resize(current);
	}
})(jQuery);

