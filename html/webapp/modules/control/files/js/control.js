var clsControl = Class.create();

clsControl.prototype = {
	initialize: function() {
	},
	controlInit: function() {
	
		//イベント追加
		var divList = document.body.getElementsByTagName("div");
		for (var i = 0; i < divList.length; i++){
			if(Element.hasClassName(divList[i],"control_system_icon")) {
				Event.observe(divList[i],"click",this.moduleClick.bindAsEventListener(divList[i]),true);
				//Event.observe(divList[i],"dblclick",this.moduleDblClick.bindAsEventListener(divList[i]),true);
			}
		}
	},
	moduleClick: function(event) {
		controlCls.cancelSelectModule();
		//選択状態へ
		var str = Element.getChildElement(this).src ;
		Element.getChildElement(this).src = str.replace(/\.gif$/,"_select.gif");
		Element.addClassName(Element.getChildElementByClassName(this, "mod_title"),"select_module");
		
		//Clickで画面表示
		commonCls.sendPopupView(event,this.id, {'center_flag':true});
	},
	//選択キャンセル
	cancelSelectModule: function() {
		var divList = document.body.getElementsByTagName("div");
		for (var i = 0; i < divList.length; i++){
			if(Element.hasClassName(divList[i],"select_module")) {
				Element.removeClassName(divList[i], "select_module");
			}
		}
		var imgList = document.body.getElementsByTagName("img");
		var re = new RegExp("_select\.gif$", "i");
		for (var i = 0; i < imgList.length; i++){
			var str = imgList[i].src ;
			if(str.match(re)) {
				imgList[i].src = str.replace(/_select\.gif$/,".gif");
			}
		}
	},
	/*
	moduleDblClick: function(event) {
		commonCls.sendPopupView(event,this.id, {'center_flag':true});
	},
	*/
	//親表示：非表示
	displayPanelChange: function(id_name) {
		var block_el = $("_" + id_name);
		//ブロック内容
		var content = Element.getChildElementByClassName(block_el,"content");
		commonCls.displayChange(Element.getParentElement(content));
		
		commonCls.moveVisibleHide(block_el);
	}
}

controlCls = new clsControl();