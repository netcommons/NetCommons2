var clsPagestyle = Class.create();

clsPagestyle.prototype = {
	initialize: function() {
		this.id = null;
		this.top_el = null;
		
		this.lang_down_arrow = null;
		this.lang_right_arrow = null;
		
		this.lang_cancel_confirm = null;
		
		this.themefields = null;
		this.theme_name = null;
		this.header_flag = null;
		this.leftcolumn_flag = null;
		this.rightcolumn_flag = null;
		
		this.header_el = null;
		this.header_id_el = null;
		this.leftcolumn_el = null;
		this.centercolumn_el = null;
		this.rightcolumn_el = null;
		this.footer_el = null;
		
		this.colorclick_flag = false;
		
		this.chg_flag = false;
		this.initColorFlag = false;
		this.initStr = "";
		this.tabset = null;
	},
	init: function(id, page_id, theme_name, header_flag, leftcolumn_flag, rightcolumn_flag, active_tab, change_flag, lang_cancel_confirm, lang_style, lang_theme, lang_layout, lang_coloration, lang_down_arrow, lang_right_arrow, pages_action, permalink_prohibition, permalink_prohibition_replace) {	
		this.id = id;
		this.page_id = page_id;
		this.theme_name = theme_name;
		this.header_flag = header_flag;
		this.leftcolumn_flag = leftcolumn_flag;
		this.rightcolumn_flag = rightcolumn_flag;
		
		this.pages_action = pages_action;
		this.permalink_prohibition = permalink_prohibition;
		this.permalink_prohibition_replace = permalink_prohibition_replace;

		var top_el = $(id);
		this.top_el = top_el;
		this.header_id_el = $("__headercolumn");
		this.header_el = $("_headercolumn");
		if(this.header_el) {
			this.header_add_module_el = Element.getChildElementByClassName(this.header_el, "headercolumn_addmodule");
		}
		this.leftcolumn_el = $("_leftcolumn");
		this.centercolumn_el = $("_centercolumn");
		this.rightcolumn_el =$("_rightcolumn");
		this.footer_el = $("_footercolumn");
		
		/* タブ */
		tabset = new compTabset(top_el);
		tabset.addTabset(lang_theme, null);
		tabset.addTabset(lang_style,pagestyleCls.clkStyle.bind($(id)));
		tabset.addTabset(lang_layout);
		tabset.addTabset(lang_coloration, null, pagestyleCls.clkColor.bind(this));	
		
		tabset.setActiveIndex(valueParseInt(active_tab));
		
		tabset.render();
		if(change_flag != "") {
			this.chg_flag = true;
		} else {
			this.chg_flag = false;
		}
		this.tabset = tabset;
		this.lang_cancel_confirm = lang_cancel_confirm;
		this.lang_down_arrow = lang_down_arrow;
		this.lang_right_arrow = lang_right_arrow;
		
		//Initに時間がかかる処理は後回し
		//setTimeout(pagestyleCls.InitTimer.bind(this), 0);
	},
	clkStyle: function() {
		var top_el = this;
		var form = top_el.getElementsByTagName("form")[0];
		commonCls.focus(form.page_name);
	},
	clkColor: function() {
		this.initColorFlag = true;
		this.initStr = "";
		var coloration_el =$("_pagestyle_color");
		if(coloration_el != null) {
			//配色取得
			this.setHighlightColor(document.body, "_pagestyle_body","backgroundColor");
			this.setHighlightColor(this.header_el, "_pagestyle_headercolumn","backgroundColor");
			this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn","backgroundColor");
			this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn","backgroundColor");
			this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn","backgroundColor");
			this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn","backgroundColor");
			
			this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_top_color","borderTopColor");
			this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_right_color","borderRightColor");
			this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_bottom_color","borderBottomColor");
			this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_left_color","borderLeftColor");
			
			this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_top_color","borderTopColor");
			this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_right_color","borderRightColor");
			this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_bottom_color","borderBottomColor");
			this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_left_color","borderLeftColor");
			
			this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_top_color","borderTopColor");
			this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_right_color","borderRightColor");
			this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_bottom_color","borderBottomColor");
			this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_left_color","borderLeftColor");
			
			this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_top_color","borderTopColor");
			this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_right_color","borderRightColor");
			this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_bottom_color","borderBottomColor");
			this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_left_color","borderLeftColor");
			
			this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_top_color","borderTopColor");
			this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_right_color","borderRightColor");
			this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_bottom_color","borderBottomColor");
			this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_left_color","borderLeftColor");
		}
		this.initColorFlag = false;
		if(this.initStr != "") {
			var color_params = new Object();
			color_params["method"] = "post";
			color_params["param"] = "dialog_pagestyle_action_edit_change&page_id="+this.page_id+"&_pagestyle_flag=1"+this.initStr;
			commonCls.send(color_params);
		}
	},
	setHighlightColor: function(el, child_class_name, property_name) {
		if(el) {
			//background-image対応
			var bgImageStyle = "none";
			if(property_name == "backgroundColor" || property_name == "background-color") {
				bgImageStyle = Element.getStyle(el, "backgroundImage");
			}

			var color = commonCls.getColorCode(el,property_name);
			var column_el = $(child_class_name);
			////var highlight_flag = false;
			var count = 0;
			for (var i = 0,column_len = column_el.childNodes.length; i < column_len; i++) {
				var child_el = column_el.childNodes[i];
				if(child_el.nodeType == 1) {
					if(child_el.tagName.toLowerCase() == "a" && child_el.title == color && bgImageStyle == "none") {
						Element.addClassName(child_el, "highlight");
						//if(count != 0) {
							child_el.onclick();
						//}
						break;
					} else if(child_el.tagName.toLowerCase() == "select") {
						//background-image
						if(bgImageStyle != "none") {
							var selected_flag = false;
							var select_el = child_el;
							// urlの中身だけ残す
							if(bgImageStyle.match("^url[(]{1}\"")) {
								var repBgImageStyle = bgImageStyle.replace(_nc_base_url, "").replace("../", "").replace("url(\"", "").replace("\")", "");
							} else {
								var repBgImageStyle = bgImageStyle.replace(_nc_base_url, "").replace("../", "").replace("url(", "").replace(")", "");
							}
							for (var j = 0, option_len = select_el.childNodes.length; j < option_len; j++) {
								var option_el = select_el.childNodes[j];
								if(option_el.value.match(repBgImageStyle)) {
									option_el.selected = "selected";
									selected_flag = true;
									select_el.onchange();
									break;
								}
							}
						}
					} else if(child_el.tagName.toLowerCase() == "input" && child_el.type != "hidden") {
						if(color != "transparent") {
							child_el.value = color;
							child_el.onchange();
						}
						//break;
					}
					count++;
				}
			}
		
		} else {
			//レイアウトで表示していないカラム
			commonCls.displayNone($(child_class_name));
			var sub_el = $(child_class_name + "_border");
			if(sub_el) {
				commonCls.displayNone(sub_el);
			}
		}
	},
	displayChange: function(this_el, el) {
		var img_el = Element.getChildElement(this_el);
		if(img_el.src.match("down_arrow.gif")) {
			img_el.src = img_el.src.replace("down_arrow.gif","right_arrow.gif");
			img_el.alt = this.lang_right_arrow;
			this_el.title = this.lang_right_arrow;
		} else {
			img_el.src = img_el.src.replace("right_arrow.gif","down_arrow.gif");
			img_el.alt = this.lang_down_arrow;
			this_el.title = this.lang_down_arrow;
		}
		if(el == null || el == undefined) {
			var next_el = this_el.nextSibling;
			if(Element.hasClassName(next_el,"_blockstyle_custom_sample")) {
				next_el = next_el.nextSibling;
			}
			commonCls.displayChange(next_el);
		} else {
			commonCls.displayChange($(el));
		}
	},
	themeClick: function(this_el, theme_name) {
		if(!this.themefields) {
			var pagestyle_top = $("_pagestyle_top");
			this.themefields = Element.getElementsByClassName(pagestyle_top, "_pagestyle");
		}
		var return_flag = false;
		this.themefields.each(function(field) {
			if(Element.hasClassName(field,"highlight")) {
				if(field == this_el) {
					/* 変更なし */
					return_flag = true;
					return;
				} else {
					Element.removeClassName(field,"highlight");
				}
			}
		}.bind(this));
		if(return_flag) {
			return;
		}
		Element.addClassName(this_el,"highlight");
		
		/* send */
		var all_apply = 0;
		var defultcolor_params = new Object();
		
		defultcolor_params["method"] = "post";
		defultcolor_params["param"] = {"action":"dialog_pagestyle_action_edit_setdefault","page_id":this.page_id,"_pagestyle_flag":1,"all_apply":all_apply,"sesson_only":1};
		defultcolor_params["callbackfunc"] =  function(){
											var theme_params = new Object();
											theme_params["method"] = "post";
											theme_params["param"] = {"action":"dialog_pagestyle_action_edit_change","page_id":this.page_id,"_pagestyle_flag":1,"theme_name": theme_name};
											theme_params["callbackfunc"] =  function(){
																				this.refresh(0);
																			}.bind(this);
											commonCls.send(theme_params);
										}.bind(this);
		commonCls.send(defultcolor_params);
	},
	/* 配色クリック */
	colorClick: function(class_name, property_name, color, this_el) {
		if(this_el.tagName.toLowerCase() != "select" && (color == "" || color =="1px solid " || this.colorclick_flag == true || (color.length == 7 && color.indexOf('#') != 0))) {
			return;
		}
		if(property_name == "backgroundColor" && color == null) color = this_el.title;
		else if(color == null && this_el.title == "transparent")  color = "0px none";
		else if(color == null) color = "1px solid " + this_el.title;
		if(typeof class_name == "string") {
			var el = Element.getChildElementByClassName(document.body, class_name);
			var send_name = class_name+"_"+property_name;
		} else {
			var el = class_name;
			var send_name = "body"+"_"+property_name;
		}
		if(!this.initColorFlag) {
			this.colorclick_flag = true;
			if(property_name == "backgroundColor") {
				// backgroundクリア
				el.style.background = "none" ;
			}
			if(property_name == "background" && color == "") {
				color = "none";
			}
			eval("el.style."+property_name + "=\"" + color+"\";");
			if(!(browser.isIE) && property_name.match("border")) {
				//firefoxのborderの場合、すぐに反映されないため
				$("_container").style.display = "none";
			}
			var color_params = new Object();
			color_params["method"] = "post";
			color_params["param"] = "dialog_pagestyle_action_edit_change&page_id="+this.page_id+"&_pagestyle_flag=1&"+send_name+"="+encodeURIComponent(color);
			color_params["callbackfunc"] =  function(){
												this.colorclick_flag = false;
												if(!(browser.isIE) && property_name.match("border") ) {
													$("_container").style.display = "table";
												}
											}.bind(this);
			commonCls.send(color_params);
			this.setHighlight(this_el);
			this.chg_flag = true;
		} else {
			//pagestyle_list初期化文字列作成
			this.initStr += "&"+send_name+"="+encodeURIComponent(color);
		}
	},
	setHighlight: function(this_el) {
		for (var i = 0; i < this_el.parentNode.childNodes.length; i++) {
			var child_el = this_el.parentNode.childNodes[i];
			if(child_el.nodeType != 1) continue;
			if(Element.hasClassName(child_el,"highlight")) {
				Element.removeClassName(child_el,"highlight");
			} else if(this_el.tagName.toLowerCase() != "select" && child_el.tagName.toLowerCase() == "select") {
				child_el.selectedIndex = 0;
			} else if(this_el.tagName.toLowerCase() != "input" && child_el.tagName.toLowerCase() == "input" && child_el.type != "hidden") {
				child_el.value = "";
			}
		}
		if(this_el.tagName.toLowerCase() != "input" && this_el.tagName.toLowerCase() != "select") {
			Element.addClassName(this_el,"highlight");
		}
	},
	/* 規定値に戻す */
	defaultColorClick: function() {
	    var top_el = $(this.id);
		var form = top_el.getElementsByTagName("form")[0];
		if(form.pagestyle_all_apply) {
			if(form.pagestyle_all_apply.checked) {
				var all_apply = 1;
			} else {
				var all_apply = 0;
			}
		} else {
			var all_apply = 0;
		}
		/* send */
		var defultcolor_params = new Object();
		
		defultcolor_params["method"] = "post";
		defultcolor_params["param"] = {"action":"dialog_pagestyle_action_edit_setdefault","page_id":this.page_id,"_pagestyle_flag":1,"all_apply":all_apply};
		defultcolor_params["callbackfunc"] =  function(){
											this.refresh(3);
										}.bind(this);
		commonCls.send(defultcolor_params);
	},
	/* レイアウトクリック */
	layoutClick: function(el , header_flag, leftcolum_flag, rightcolumn_flag) {
		if(Element.hasClassName(el,"highlight")) {
			//変更なし
			return;
		}
		this.chg_flag = true;
		var pagestyle_layout_el = Element.getParentElementByClassName(el, "_pagestyle_layout");
		var highlight_el = Element.getChildElementByClassName(pagestyle_layout_el, "highlight");
		Element.removeClassName(highlight_el,"highlight");
		Element.addClassName(el,"highlight");
		var refresh_flag = false;
		if(header_flag) {
			if(this.header_id_el) {
				if(this.header_add_module_el) commonCls.displayVisible(this.header_add_module_el);
				commonCls.displayVisible(this.header_id_el);
			} else {
				refresh_flag = true;
			}
			this.header_flag = 1;
		} else {
			if(this.header_add_module_el) commonCls.displayNone(this.header_add_module_el);
			if(this.header_id_el)  commonCls.displayNone(this.header_id_el);
			this.header_flag = 0;
		}
		var colspan = 1;
		if(leftcolum_flag) {
			colspan++;
			if(this.leftcolumn_el) {
				commonCls.displayVisible(this.leftcolumn_el);
			} else {
				refresh_flag = true;
			}
			this.leftcolumn_flag = 1;
		} else {
			if(this.leftcolumn_el) commonCls.displayNone(this.leftcolumn_el);
			this.leftcolumn_flag = 0;
		}
		if(rightcolumn_flag) {
			colspan++;
			if(this.rightcolumn_el) {
				commonCls.displayVisible(this.rightcolumn_el);
			} else {
				refresh_flag = true;
			}
			this.rightcolumn_flag = 1;
		} else {
			if(this.rightcolumn_el) commonCls.displayNone(this.rightcolumn_el);
			this.rightcolumn_flag = 0;
		}
		if(this.header_id_el) {
			this.header_id_el.colspan = colspan;
		}
		if(this.footer_el) {
			this.footer_el.colspan = colspan;
		}
		var layout_params = new Object();
		layout_params["method"] = "post";
		layout_params["param"] = {"action":"dialog_pagestyle_action_edit_change","_pagestyle_flag":1,"page_id":this.page_id,"header_flag": this.header_flag,"leftcolumn_flag": this.leftcolumn_flag,"rightcolumn_flag": this.rightcolumn_flag};
		layout_params["callbackfunc"] =  function(){
											if(refresh_flag != "" || !(browser.isIE)) {
												this.refresh(2);
											}
										}.bind(this);
		commonCls.send(layout_params);
	},
	refresh: function(active_tab, append_str) {
		var top_el = $(this.id);
		append_str = (append_str == undefined || append_str == null) ? "" : append_str;
		var str = "&_layoutmode=off";
		if(active_tab != undefined && active_tab != null) {
			str += "&active_tab="+active_tab;
		}
		location.href = _nc_base_url + _nc_index_file_name + "?action="+ this.pages_action+"&page_id="+this.page_id+
						"&_pagestyle_flag=1&pagestyle_x="+top_el.parentNode.style.left+"&pagestyle_y="+top_el.parentNode.style.top+
						str + append_str;
	},
	//行揃え変更
	chgGeneral: function(el) {
		var top_el = $(this.id);
		var form = top_el.getElementsByTagName("form")[0];
		var name = el.name;
		var hidden_el = el.nextSibling;
		if(hidden_el.value == el.value) {
			// 変更なし
			return;
		}
		this.chg_flag = true;
		var container_el = $("_container");
		if(name == "align") {
			if(el.value == "center") {
				form.leftmargin.value = "0";
				form.rightmargin.value = "0";
				container_el.style.marginRight = "";
				container_el.style.marginLeft = "";
				var buf_hidden_el = form.leftmargin.nextSibling;
				buf_hidden_el.value = "0";
				var buf_hidden_el = form.rightmargin.nextSibling;
				buf_hidden_el.value = "0";
			}
			container_el.align = el.value;
			container_el.style.textAlign = el.value;
		} else if(name == "topmargin") {
			container_el.style.marginTop = el.value + "px";
		} else if(name == "rightmargin") {
			container_el.style.marginRight = el.value + "px";
		} else if(name == "bottommargin") {
			container_el.style.marginBottom = el.value + "px";
		} else if(name == "leftmargin") {
			container_el.style.marginLeft = el.value + "px";
		}
		if((name == "leftmargin" || name == "rightmargin") && el.value != "0" && form.align.value == "center") {
			form.align.value = "left";
			container_el.align = "left";
			container_el.style.textAlign = "left";
			var buf_hidden_el = form.align.nextSibling;
			buf_hidden_el.value = "left";
		}
		if(name == "page_name") {
			var robj = new RegExp(this.permalink_prohibition, "ig");
			if(hidden_el.value.replace(robj, this.permalink_prohibition_replace) == form.permalink.value.replace(robj, this.permalink_prohibition_replace)) {
				form.permalink.value = el.value.replace(robj, this.permalink_prohibition_replace);
			}
		}
		hidden_el.value = el.value;
		//session情報に追記
		var general_params = new Object();
		general_params["method"] = "post";
		general_params["param"] = {"action":"dialog_pagestyle_action_edit_change","_pagestyle_flag":1,"page_id":this.page_id};
		general_params["param"][name] = el.value;
		commonCls.send(general_params);
	},
	/*OK*/
	okClick: function(id) {
		var top_el = $(this.id);
		var form = top_el.getElementsByTagName("form")[0];
		if(form.pagestyle_all_apply) {
			if(form.pagestyle_all_apply.checked) {
				var all_apply = 1;
			} else {
				var all_apply = 0;
			}
		} else {
			var all_apply = 0;
		}
		if(this.chg_flag) {
			setTimeout(function() {
				var top_el = $(this.id);
				var theme_params = new Object();
				if(typeof form.titletag != "undefined") {
					var titletag = form.titletag.value;
				} else {
					var titletag = null;
				}
				if(typeof form.permalink != "undefined") {
					var permalink = form.permalink.value;
				} else {
					var permalink = null;
				}
				
				theme_params["method"] = "post";
				theme_params["top_el"] = $(this.id);
				theme_params["param"] = {"action":"dialog_pagestyle_action_edit_init","page_id":this.page_id,"page_name":form.page_name.value,"permalink":permalink,"titletag":titletag,"meta_description":form.meta_description.value,"meta_keywords":form.meta_keywords.value,"_pagestyle_flag":1,"all_apply":all_apply,"prefix_id_name":"dialog_pagestyle"};
				theme_params["callbackfunc"] =  function(res){
													if(form.permalink_url && form.permalink.value != undefined && form.permalink.value != '') {
														location.href = form.permalink_url.value + form.permalink.value + '/';
													} else {
														location.href = _nc_base_url + _nc_index_file_name + "?action=" + this.pages_action + "&page_id="+this.page_id;
													}
												}.bind(this);
				theme_params["callbackfunc_error"] =  function(res){
													commonCls.alert(res);
													this.tabset.setActiveIndex(1);
													this.tabset.refresh();
												}.bind(this);
				commonCls.send(theme_params);
			}.bind(this),300);
		} else {
			//変更なし
			commonCls.removeBlock(id);
		}
	},
	/*キャンセル*/
	cancelClick: function(id) {
		if(this.chg_flag) {
			if(!commonCls.confirm(this.lang_cancel_confirm))return false;
			location.href = _nc_base_url + _nc_index_file_name + "?action=pages_view_main&page_id="+this.page_id;
		} else {
			//変更なし
			commonCls.removeBlock(id);
		}
		//location.href = _nc_base_url + _nc_index_file_name + "?action=pages_view_main&page_id="+this.page_id;
	}
}

pagestyleCls = new clsPagestyle();