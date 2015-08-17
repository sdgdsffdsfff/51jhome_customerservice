// JavaScript Document
var scrollContent = null; //滚动条

jQuery.noConflict();

function getCreditState() {
	jQuery.post(credit_url, {"uid":uid}, function(result){
        if(result.status==1) {
            jQuery('#task_credit_total').text(result.data.task_credit_total);
            jQuery('#task_coin_total').text(result.data.task_coin_total);
            jQuery('#read_total').text(result.data.read_total);
            jQuery('#credit_total').text(result.data.credit_total);
            jQuery('#coin_total').text(result.data.coin_total);
        }
    }, 'JSON');
}

$(document).ready(function() {

	$(".panel_list").panel({
		contentWrap: $(".panel_content")
	});
	
	$(".bt-menu").click(function() {
		$(".panel_list").panel("toggle", 'overlay', 'left');
		getCreditState();
	});
	
	$('.bt-back').click(function(){
		$(".panel_list").panel('close');
	});
	
	$('body').swipeRight(function(){
		$(".panel_list").panel("toggle", 'overlay', 'left');
		getCreditState();
	});
	
	$('body').swipeLeft(function(){
		$(".panel_list").panel('close');
	});
	
	
	$("#explain").click(function(){
		$(".explain").show();
		
		document.ontouchmove = function(e) {
			e.preventDefault();
			var b_height = $(window).height();
			$('#explain-text').height(b_height - 150);
		}
		
		scrollContent = new iScroll('explain-text',{scrollbarClass: "myScrollbar"});
		
		$('#explain-bg').click(function(){
			$(".explain").hide();
		});
		
	});
	
	function initPage(){
		var w_height = $(window).height(),
			p_height = $('.panel_container').height();
		
		if(w_height > p_height){
			$('.panel_container').height(w_height)
		}
	}
	
	initPage();
});
