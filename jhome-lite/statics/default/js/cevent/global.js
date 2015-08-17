//自定义插件函数
(function($){
//插件 表格选中，隔行换色
// $(".TableList").SetTableList();
	$.fn.extend({ 
	"SetTableList":function(options){ 
	//设置默认样式值 
		option=$.extend({ 
		odd:"trodd",//奇数行 
		even:"treven",//偶数航 
		selected:"trselected",//选中行 
		over:"trover"//鼠标移动上去时
	},options);
	$("tbody>tr:even",this).addClass(option.even); 
	$("tbody>tr:odd",this).addClass(option.odd); 
	$("tbody>tr",this).live({
        mouseenter: function() {
           $(this).addClass(option.over);
        },
        mouseleave: function() {
			$(this).removeClass(option.over); 
        }
    });
		return this;
	} 
	});
//默认开启效果
})(jQuery);
$(function() {
	//表格换色
	$("table").SetTableList();
    $("#main-nav li ul").hide();
    $("#main-nav li a.current").parent().find("ul").slideToggle("slow").find('li').slideDown(500);
    $("#main-nav li a.nav-top-item").click(function() {
        $(this).parent().siblings().find("ul").slideUp("normal");
        $(this).next().slideToggle("normal").find('li').slideDown(500);
        return false
    });
    $("#main-nav li a.no-submenu").click(function() {
        window.location.href = (this.href);
        return false
    });
    $("#main-nav li .nav-top-item").hover(function() {
        $(this).stop().animate({
            paddingRight: "25px"
        },
        200)
    },
    function() {
        $(this).stop().animate({
            paddingRight: "15px"
        })
    });
    $(".content-box-header h3").css({
        "cursor": "s-resize"
    });
    $(".closed-box .content-box-content").hide();
    $(".closed-box .content-box-tabs").hide();
    $(".content-box-header h3").click(function() {
        $(this).parent().next().toggle();
        $(this).parent().parent().toggleClass("closed-box");
        $(this).parent().find(".content-box-tabs").toggle();
    });
    $('.content-box .content-box-content div.tab-content').hide();
    $('ul.content-box-tabs li a.default-tab').addClass('current');
    $('.content-box-content div.default-tab').show();
    $('.content-box ul.content-box-tabs li a').click(function() {
        $(this).parent().siblings().find("a").removeClass('current');
        $(this).addClass('current');
        var currentTab = $(this).attr('href');
        $(currentTab).siblings().hide();
        $(currentTab).show();
        return false;
    });
    $(".close").click(function() {
        $(this).parent().fadeTo(400, 0, 
        function() {
            $(this).slideUp(400);
        });
        return false;
    });
 //   $('tbody tr:even').addClass("alt-row");
    $('.check-all').click(function() {
        $(this).parent().parent().parent().parent().find("input[type='checkbox']").attr('checked', $(this).is(':checked'));
    });
    $('a[rel*=pop]').facebox();
})
//定时关闭提示
function closeTip(show){
	setTimeout(function(){$(show).fadeOut(300);},2000);
	return false;
}
//时间比较
function compareTime(startDate, endDate) {
	if (startDate.length > 0 && endDate.length > 0) {
		var startDateTemp = startDate.split(" ");
		var endDateTemp = endDate.split(" ");

		var arrStartDate = startDateTemp[0].split("-");
		var arrEndDate = endDateTemp[0].split("-");

		var arrStartTime = startDateTemp[1].split(":");
		var arrEndTime = endDateTemp[1].split(":");

		var allStartDate = new Date(arrStartDate[0], arrStartDate[1], arrStartDate[2], arrStartTime[0], arrStartTime[1], arrStartTime[2]);
		var allEndDate = new Date(arrEndDate[0], arrEndDate[1], arrEndDate[2], arrEndTime[0], arrEndTime[1], arrEndTime[2]);
		if ((allEndDate.getTime() - allStartDate.getTime()) >= 60000) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}