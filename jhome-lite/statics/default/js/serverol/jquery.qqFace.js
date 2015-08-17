// QQ表情插件
var faces = {
		'[微笑]': 'smiley/e100.gif',
		'[撇嘴]': 'smiley/e101.gif',
		'[色]': 'smiley/e102.gif',
		'[发呆]': 'smiley/e103.gif',
		'[得意]': 'smiley/e104.gif',
		'[流泪]': 'smiley/e105.gif',
		'[害羞]': 'smiley/e106.gif',
		'[闭嘴]': 'smiley/e107.gif',
		'[睡]': 'smiley/e108.gif',
		'[大哭]': 'smiley/e109.gif',
		'[尴尬]': 'smiley/e110.gif',
		'[发怒]': 'smiley/e111.gif',
		'[调皮]': 'smiley/e112.gif',
		'[呲牙]': 'smiley/e113.gif',
		'[惊讶]': 'smiley/e114.gif',
		'[难过]': 'smiley/e115.gif',
		'[酷]': 'smiley/e116.gif',
		'[冷汗]': 'smiley/e117.gif',
		'[抓狂]': 'smiley/e118.gif',
		'[吐]': 'smiley/e119.gif',
		'[偷笑]': 'smiley/e120.gif',
		'[可爱]': 'smiley/e121.gif',
		'[白眼]': 'smiley/e122.gif',
		'[傲慢]': 'smiley/e123.gif',
		'[饥饿]': 'smiley/e124.gif',
		'[困]': 'smiley/e125.gif',
		'[惊恐]': 'smiley/e126.gif',
		'[流汗]': 'smiley/e127.gif',
		'[憨笑]': 'smiley/e128.gif',
		'[大兵]': 'smiley/e129.gif',
		'[奋斗]': 'smiley/e130.gif',
		'[咒骂]': 'smiley/e131.gif',
		'[疑问]': 'smiley/e132.gif',
		'[嘘..]': 'smiley/e133.gif',
		'[晕]': 'smiley/e134.gif',
		'[折磨]': 'smiley/e135.gif',
		'[衰]': 'smiley/e136.gif',
		'[骷髅]': 'smiley/e137.gif',
		'[敲打]': 'smiley/e138.gif',
		'[再见]': 'smiley/e139.gif',
		'[擦汗]': 'smiley/e140.gif',
		'[抠鼻]': 'smiley/e141.gif',
		'[鼓掌]': 'smiley/e142gif',
		'[溴大了]': 'smiley/e143.gif',
		'[坏笑]': 'smiley/e144.gif',
		'[左哼哼]': 'smiley/e145.gif',
		'[右哼哼]': 'smiley/e146.gif',
		'[哈欠]': 'smiley/e147.gif',
		'[鄙视]': 'smiley/e148.gif',
		'[委屈]': 'smiley/e149.gif',
		'[快哭了]': 'smiley/e150.gif',
		'[阴险]': 'smiley/e151.gif',
		'[亲亲]': 'smiley/e152.gif',
		'[吓]': 'smiley/e153.gif',
		'[可怜]': 'smiley/e154.gif',
		'[菜刀]': 'smiley/e155.gif',
		'[西瓜]': 'smiley/e156.gif',
		'[啤酒]': 'smiley/e157.gif',
		'[篮球]': 'smiley/e158.gif',
		'[乒乓]': 'smiley/e159.gif',
		'[咖啡]': 'smiley/e160.gif',
		'[饭]': 'smiley/e161.gif',
		'[猪头]': 'smiley/e162.gif',
		'[玫瑰]': 'smiley/e163.gif',
		'[凋谢]': 'smiley/e164.gif',
		'[示爱]': 'smiley/e165.gif',
		'[[爱心]': 'smiley/e166.gif',
		'[心碎]': 'smiley/e167gif',
		'[蛋糕]': 'smiley/e168.gif',
		'[闪电]': 'smiley/e169.gif',
		'[炸弹]': 'smiley/e170.gif',
		'[刀]': 'smiley/e171.gif',
		'[足球]': 'smiley/e172.gif',
		'[瓢虫]': 'smiley/e173.gif',
		'[便便]': 'smiley/e174.gif',
		'[月亮]': 'smiley/e175.gif',
		'[太阳]': 'smiley/e176.gif',
		'[礼物]': 'smiley/e177.gif',
		'[拥抱]': 'smiley/e178.gif',
		'[强]': 'smiley/e179.gif',
		'[弱]': 'smiley/e180.gif',
		'[握手]': 'smiley/e181.gif',
		'[胜利]': 'smiley/e182.gif',
		'[抱拳]': 'smiley/e183.gif',
		'[勾引]': 'smiley/e184.gif',
		'[拳头]': 'smiley/e185.gif',
		'[差劲]': 'smiley/e186.gif',
		'[爱你]': 'smiley/e187.gif',
		'[NO]': 'smiley/e188.gif',
		'[OK]': 'smiley/e189.gif',
		'[爱情]': 'smiley/e190.gif',
		'[飞吻]': 'smiley/e191.gif',
		'[跳跳]': 'smiley/e192.gif',
		'[发抖]': 'smiley/e193.gif',
		'[怄火]': 'smiley/e194.gif',
		'[转圈]': 'smiley/e195.gif',
		'[磕头]': 'smiley/e196.gif',
		'[回头]': 'smiley/e197.gif',
		'[跳绳]': 'smiley/e198.gif',
		'[挥手]': 'smiley/e199.gif'
	};
(function($){  
	$.fn.qqFace = function(options){
		var defaults = {
			id : 'facebox',
			path : 'images/',
			assign : 'content',
			tip : 'em_'
		};
		var option = $.extend(defaults, options);
		var assign = $('#'+option.assign);
		var id = option.id;
		var path = option.path;
		var tip = option.tip;
		
		if(assign.length<=0){
			alert('缺少表情赋值对象。');
			return false;
		}
		
		$(this).click(function(e){
			var strFace, labFace;
			if($('#'+id).length<=0){
				strFace = '<div id="'+id+'" style="position:absolute;display:none;z-index:1000;" class="qqFace">' +
							  '<table border="0" cellspacing="0" cellpadding="0"><tr>';
				var i = 1;
				for(var face in faces){
					labFace = face;
					strFace += '<td><img width="24" height="24" src="'+path+faces[face]+'" onclick="$(\'#'+option.assign+'\').setCaret();$(\'#'+option.assign+'\').insertAtCaret(\'' + labFace + '\');" /></td>'
					if(i++ % 15 == 0) strFace += '</tr><tr>';
				}
				// for(var i=1; i<=75; i++){
				// 	labFace = '['+tip+i+']';
				// 	strFace += '<td><img src="'+path+i+'.gif" onclick="$(\'#'+option.assign+'\').setCaret();$(\'#'+option.assign+'\').insertAtCaret(\'' + labFace + '\');" /></td>';
				// 	if( i % 15 == 0 ) strFace += '</tr><tr>';
				// }
				strFace += '</tr></table></div>';
			}
			$(this).parent().append(strFace);
			var offset = $(this).position();
			var top = offset.top + $(this).outerHeight();
			$('#'+id).css('top',top);
			$('#'+id).css('left',offset.left);
			$('#'+id).show();
			e.stopPropagation();
		});

		$(document).click(function(){
			$('#'+id).hide();
			$('#'+id).remove();
		});
	};


})(jQuery);

jQuery.extend({ 
unselectContents: function(){ 
	if(window.getSelection) 
		window.getSelection().removeAllRanges(); 
	else if(document.selection) 
		document.selection.empty(); 
	} 
}); 
jQuery.fn.extend({ 
	selectContents: function(){ 
		$(this).each(function(i){ 
			var node = this; 
			var selection, range, doc, win; 
			if ((doc = node.ownerDocument) && (win = doc.defaultView) && typeof win.getSelection != 'undefined' && typeof doc.createRange != 'undefined' && (selection = window.getSelection()) && typeof selection.removeAllRanges != 'undefined'){ 
				range = doc.createRange(); 
				range.selectNode(node); 
				if(i == 0){ 
					selection.removeAllRanges(); 
				} 
				selection.addRange(range); 
			} else if (document.body && typeof document.body.createTextRange != 'undefined' && (range = document.body.createTextRange())){ 
				range.moveToElementText(node); 
				range.select(); 
			} 
		}); 
	}, 

	setCaret: function(){ 
		if(!$.browser.msie) return; 
		var initSetCaret = function(){ 
			var textObj = $(this).get(0); 
			textObj.caretPos = document.selection.createRange().duplicate(); 
		}; 
		$(this).click(initSetCaret).select(initSetCaret).keyup(initSetCaret); 
	}, 

	insertAtCaret: function(textFeildValue){ 
		var textObj = $(this).get(0); 
		if(document.all && textObj.createTextRange && textObj.caretPos){ 
			var caretPos=textObj.caretPos; 
			caretPos.text = caretPos.text.charAt(caretPos.text.length-1) == '' ? 
			textFeildValue+'' : textFeildValue; 
		} else if(textObj.setSelectionRange){ 
			var rangeStart=textObj.selectionStart; 
			var rangeEnd=textObj.selectionEnd; 
			var tempStr1=textObj.value.substring(0,rangeStart); 
			var tempStr2=textObj.value.substring(rangeEnd); 
			textObj.value=tempStr1+textFeildValue+tempStr2; 
			textObj.focus(); 
			var len=textFeildValue.length; 
			textObj.setSelectionRange(rangeStart+len,rangeStart+len); 
			textObj.blur(); 
		}else{ 
			textObj.value+=textFeildValue; 
		} 
	} 
});