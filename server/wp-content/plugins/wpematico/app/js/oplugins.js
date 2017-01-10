jQuery(document).ready(function($){
	$(".wpeplugname a.wpelinks").mouseover(function(){
		$(this).parent().children('div').fadeIn();
	});
	$(".wpeplugname").children('div').mouseout(function(){
		$(this).fadeOut();
	});
	$("a.wpeopenover").unbind('hover');
});