//タブセット用クラス
var compTabset = Class.create();
/*
	//template
	topエレメント以下に
	class=comp_tabsetを１つ作成し
	その下に一意のclass_nameを定義する
	<div> --top_element
		<div class=comp_tabset></div>
		<div class=comp_tabset_content>
			<div class=tab1></div>
			<div class=tab2></div>
			<div class=tab3></div>
		</div>
	</div>
	
	//呼び出し側(js_class)
	var tabset = new compTabset(top_el);
	//callbackfunc()は、タブクリックして表示したときにはしるコールバック関数
	//callbackInit()は、初期表示時に一回だけはしる初期処理関数
	tabset.addTabset("タブ1",callbackfunc, callbackInit);
	tabset.addTabset("タブ2",callbackfunc);	
	tabset.addTabset("タブ3",callbackfunc);
	tabset.setActiveIndex(2);	//デフォルトの設定タブ位置を変更したい場合、指定(0から指定）　デフォルト１番目のタブが押下された状態
	tabset.render();
*/

compTabset.prototype = {
	initialize: function(top_el) {
		this.top_el = top_el;
		this.tab_el = null;
		this.tabs = new Array;
		this._activeIndex = 0;
	},
	/*	タブの追加						*/
	/*	@param:	id_name or el<div>		*/
	/*			caption label			*/
	addTabset: function(label, callbackfunc, initfunc) {
		var tab_object = new Object;
		tab_object.caption   = label;
		tab_object.callbackfunc   = callbackfunc;
		tab_object.initfunc   = initfunc;
		tab_object.tabLen = this.tabs.length;
		this.tabs[tab_object.tabLen] = tab_object;
	},
	render: function() {
		var tab_el = Element.getChildElementByClassName(this.top_el,"comp_tabset");
		this.tab_el = tab_el;
		if (tab_el) {
			tab_el.innerHTML = this.renderTab();
			//イベント追加
			var tableList = tab_el.getElementsByTagName("table");
			//var count = 0;
			for (var i = 0; i < tableList.length; i++){
				if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
					Event.observe(tableList[i],"click",this.tabClick.bindAsEventListener(this),true, this.top_el);	
					//count++;
					//if(Element.hasClassName(tableList[i],"comptabset_active")) {
					//	this._activeEl = tableList[i];
					//}
				}
			}
		}
	},
	renderTab: function() {
		var tabset_summary = (commonLang.tabset_summary != undefined) ? commonLang.tabset_summary : "Tabset";
		var ret = "";
		ret = '<table cellspacing="0" cellpadding="0" class="comptabset_tabs" summary="'+tabset_summary+'"><tr class="comptabset_tabs_tr">';
		ret += '<td class="comptabset_linespace"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_linespace" /></td>';
		//ret += '<div style="width:2px;min-width:2px; display:inline;"></div>';
		var content_el = this.tab_el.nextSibling;
		for (var i = 0; i < content_el.childNodes.length; i++) {
			var container_el = content_el.childNodes[i];
			
		//for (var i = 0; i < this.tabs.length; i++) {
		//	var container_el = Element.getChildElementByClassName(content_el,this.tabs[i].class_name);
			if(container_el == null) {
				continue;
			}
			if(!this.tabs[i] || this.tabs[i].caption == null) {
				if(!this.tabs[i] || typeof this.tabs[i] != 'object') {
					this.tabs[i] = new Object;
					this.tabs[i].callbackfunc   = null;
					this.tabs[i].initfunc   = null;
					this.tabs[i].tabLen = i;
				}
				var label_el = Element.getChildElement(container_el);
				if(Element.hasClassName(label_el,"comptabset_caption")) {
					this.tabs[i].caption   = label_el.innerHTML;
				} else {
					this.tabs[i].caption   = "Tab"+ (i+1);
				}
			}
			if (i == this._activeIndex) {
				var class_name = 'comptabset_active';
				//Init関数
				if(this.tabs[i].initfunc) {
					this.tabs[i].initfunc();
					this.tabs[i].initfunc = null;
				}
				//コールバック関数
				//if(this.tabs[i].callbackfunc) {
				//	this.tabs[i].callbackfunc();
				//}
				this.tabClick(null, container_el);
				//this.tabClick(null, this.tabs[i]);
			} else {
				var class_name = '';
				//if (this.tabs[i].class_name) {
					commonCls.displayNone(container_el);
				//}
			}
			//ret += '<li ' + '" class="comptabset_tabset ' + class_name + '"><span>' + this.tabs[i].caption + '</span></li>';
			ret += '<td >'+
				'<table' + ' class="comptabset_tabset ' + class_name + '" border="0" summary="">'+
				  '<tr>'+
				    '<td class="comptabset_upperleft"></td>'+
				    '<td class="comptabset_upper"></td>'+
				    '<td class="comptabset_upperright"></td>'+
				  '</tr>'+
				  '<tr>'+
				    '<td class="comptabset_left"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_sidespace" /></td>'+
				    '<td class="comptabset_content"><a class="link" href="#" onclick="return false;">' + this.tabs[i].caption+ '</a></td>'+
				    '<td class="comptabset_right"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_sidespace" /></td>'+
				  '</tr>'+
				'</table>'+
				'</td><td class="comptabset_linespace"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_linespace" /></td>';
		}
		ret += '<td class="comptabset_line"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_linespace" /></td></tr></table>';
		
		return ret;
	},
	tabClick: function(event,el) {
		var tableList = this.tab_el.getElementsByTagName("table");
		var targetEl = (event == undefined || event == null) ? el : Event.element(event);
		if(!Element.hasClassName(targetEl,"comptabset_tabset")) {
			targetEl = Element.getParentElementByClassName(targetEl,"comptabset_tabset");
		}
		var count = 0;
		for (var i = 0; i < tableList.length; i++){
			if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
				if(targetEl == tableList[i] || targetEl.parentNode == tableList[i]) {
					//Element.removeClassName(tableList[i],"comptabset_inactive");
					Element.addClassName(tableList[i],"comptabset_active");
					this._activeIndex = count;
				} else {
					Element.removeClassName(tableList[i],"comptabset_active");
					//Element.addClassName(tableList[i],"comptabset_inactive");
				}
				count++;
			}
		}

		var content_el = this.tab_el.nextSibling;
		for (var i = 0; i < content_el.childNodes.length; i++) {
			var container_el = content_el.childNodes[i];
		//for (var i=0; i<this.tabs.length; i++) {
		//	var container_el = Element.getChildElementByClassName(this.top_el,this.tabs[i].class_name);
			if (!container_el) continue;
			if (i == this._activeIndex) {
				//Init関数
				if(this.tabs[i].initfunc) {
					this.tabs[i].initfunc();
					this.tabs[i].initfunc = null;
				}
				commonCls.displayVisible(container_el);
				//コールバック関数
				if(this.tabs[i].callbackfunc) {
					this.tabs[i].callbackfunc();
				}
				//commonCls.displayVisible(container_el);
			} else {
				commonCls.displayNone(container_el);
			}
		}
		//this._activeEl = targetEl;
	},
	setActiveIndex: function(activeIndex) {
		this._activeIndex = activeIndex;
	},
	getActiveIndex: function() {
		return this._activeIndex;
	},
	refresh: function() {
		var tableList = this.tab_el.getElementsByTagName("table");
		var count = 0;
		var activeEl = null;
		for (var i = 0; i < tableList.length; i++){
			if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
				if(this._activeIndex == count) {
					activeEl = tableList[i];
					break;
				}
				count++;
			}
		}
		this.tabClick(null,activeEl);
	}
}