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
	$("tbody>tr",this).on('mouseenter',function() {
           $(this).addClass(option.over);
        });
	$("tbody>tr",this).on('mouseleave',function() {
			$(this).removeClass(option.over); 
        });
		return this;
	} 
	});
//默认开启效果
})(jQuery);
$(function() {
	//表格换色
	$("table").SetTableList();
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
function checkTime(startDate, endDate) {
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
//还原按钮
function resetSubmit(e,text){
	text=text||'保存';
	setTimeout(function(){$(e).attr("disabled",false).text(text).removeClass('disabled')},1500);
}
