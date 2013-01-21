var clsBlockstyle = Class.create();

clsBlockstyle.prototype = {
	initialize: function() {
		this.confirm_mes = null;
		//this.id = null;
		//this.page_id = null;
		//this.block_id = null;
		//this.parent_id = null;
		//this.theme_name = null;
		this.colorclick_flag = new Object();
		this.color_params = new Object();
		//this.color_params['color'] = new Object();
		//this.defaultcolor_flag = false;
		this.change_color = new Object();
		this.refresh_flag = new Object();
	},
	init: function(id, active_tab, blocktheme_name, lang_style, lang_theme, lang_coloration) {
		var top_el = $(id);
		this.refresh_flag[id] = false;
		if(blocktheme_name) {
			//スタイルシート追加処理
			commonCls.addBlockTheme(blocktheme_name);
		}
		/* タブ */
		tabset = new compTabset(top_el);
		tabset.setActiveIndex(valueParseInt(active_tab));
		tabset.addTabset(lang_theme);
		tabset.addTabset(lang_style,blockstyleCls.clkStyle.bind($(id)));
		tabset.addTabset(lang_coloration, blockstyleCls.clkCustomColor.bind($(id)), blockstyleCls.initCustomColor.bind($(id)));
		tabset.render();
	},
	clkStyle: function() {
		var top_el = this;
		var form = top_el.getElementsByTagName("form")[0];
		commonCls.focus(form.block_name);
	},
	clkCustomColor: function() {
		//this.page_id = page_id;
		//this.block_id = block_id;
		//this.parent_id = parent_id;
		//this.theme_name = theme_name;
		//var id = this.id;
		var el = $("_blockstyle_custom_top" + this.id);
		if(el) commonCls.focus(el);
	},
	initCustomColor: function() {
		var id = this.id;
		var samplefields = Element.getElementsByClassName($(id), "_blockstyle_custom_sample");
		samplefields.each(function(coloration_el) {
			var preview_el = Element.getChildElement(coloration_el);
			var content_el = coloration_el.nextSibling;
			var propertyfields = Element.getElementsByClassName(content_el, "_blockstyle_custom_property");
			propertyfields.each(function(field) {
				var child_el = Element.getChildElement(field);
				var property_name = child_el.value;
				if(pagestyleCls.setHighlightColor(preview_el, field, property_name)) {
					return;
				}
			}.bind(this));
		}.bind(this));
		
		var checkboxfields = Element.getElementsByClassName($(id), "_blockstyle_autocheckbox");
		checkboxfields.each(function(checkbox_el) {
			checkbox_el.checked = true;
			blockstyleCls.chkAutoClick(checkbox_el);
		}.bind(this));
		
	},
	/*カスタム画面-カラー自動設定チェックボックスクリック*/
	chkAutoClick: function(this_el) {
		var input_el = this_el.parentNode.parentNode.previousSibling;
		if(this_el.checked) {
			input_el.disabled = true;
		} else {
			input_el.disabled = false;
			input_el.focus();
			input_el.select();
		}
	},
	/* カスタム画面-配色クリック */
	colorClick: function(top_id, id, id_name, theme_name, property_name, color, this_el, count_color) {
		count_color = (count_color == undefined || count_color == null) ? 0 : count_color;
		var top_el = $(id);
		if(!top_el) {
			return;
		}
		if(this_el.tagName.toLowerCase() != "select" && (color != "transparent" && (Element.hasClassName(this_el,"highlight") || (color == null || color.length != 7 || color.indexOf('#') != 0)))) {
			return;
		}
		
		this.colorclick_flag[top_id] = true;
		
		var blockstyle_all_apply_el = $("blockstyle_all_apply" + top_id);
		var append_class_name = "";
		if( this.change_color[top_id]) {
			var old_change_color = this.change_color[top_id];
		} else {
			var old_change_color = "";
		}
		if(!blockstyle_all_apply_el || !blockstyle_all_apply_el.checked) {
			append_class_name = ".blockstyle" + top_id + " ";
			this.change_color[top_id] = "once";
		} else {
			this.change_color[top_id] = "all";
		}
		if(old_change_color != "" && this.change_color[top_id] != old_change_color) {
			this.refresh_flag[top_id] = true;
		}	
		//if(property_name == "backgroundColor" && color == null) color = this_el.title;
		//else if(color == null && this_el.title == "transparent")  color = "0px none";
		//else if(color == null) color = "1px solid " + this_el.title;
		//if(typeof class_name == "string") {
		//	var el = Element.getChildElementByClassName(document.body, class_name);
		//	var send_name = class_name+"_"+property_name;
		//} else {
		//	var el = class_name;
		//	var send_name = "body"+"_"+property_name;
		//}
		var el = $(id_name);
		////var style = new Object();
		////style[property_name] = color;
		
		////Element.setStyle(el, style);
		
		//head内をスタイルタグに追加
		var class_arr = el.className.split(" ");
		var class_name = "";
		class_arr.each(
			function(value){
				class_name += "." + value + " ";
			}
		);
		class_name = append_class_name + class_name;
		//var style = $("_blockstyle_custom_style" + theme_name);
		var style = null;
		var styleList = document.getElementsByTagName("style");
		for (var i = 0; i < styleList.length; i++){
			if (styleList[i].title == "_blockstyle_custom_style" + theme_name){
				style = styleList[i];
				break;
			}
		}
		
		if(!style) {
			if(typeof document.createStyleSheet != 'undefined') {
				var style=document.createStyleSheet();
			} else {
				var style=document.createElement('STYLE');
				style.appendChild(document.createTextNode(''));
				style.type="text/css";
				
				var oHEAD=document.getElementsByTagName('HEAD').item(0);
				oHEAD.appendChild(style);
			}
			style.title = "_blockstyle_custom_style" + theme_name;
			//style.id="_blockstyle_custom_style" + theme_name;	//id属性は、IEでエラー
		}
		if(typeof style.addRule != 'undefined') {
			style.addRule(class_name, property_name + ":" + color);
			if(property_name == "backgroundColor" || property_name == "background-color") {
				// backgroundクリア
				style.addRule(class_name, "background-image:none;");
			}
		} else if(typeof style.styleSheet != 'undefined' && typeof style.styleSheet.addRule != 'undefined') {
			style.styleSheet.addRule(class_name, property_name + ":" + color);
			if(property_name == "backgroundColor" || property_name == "background-color") {
				// backgroundクリア
				style.styleSheet.addRule(class_name, "background-image:none;");
			}
		} else {
			if(property_name == "background" && color == "") {
				color = "none";
			}
			if(typeof style.sheet.insertRule != 'undefined') {
				style.sheet.insertRule(class_name + "{" + property_name + ":" + color + "}", style.sheet.cssRules.length);
				if(property_name == "backgroundColor" || property_name == "background-color") {
					// backgroundクリア
					style.sheet.insertRule(class_name + "{background-image:none;}", style.sheet.cssRules.length);
				}
			} else {
				style.innerHTML = style.innerHTML + class_name + "{" + property_name + ":" + color + "}\n";
				if(property_name == "backgroundColor" || property_name == "background-color") {
					// backgroundクリア
					style.innerHTML = style.innerHTML + class_name + "{background-image:none;}\n";
				}
			}
		}
		//var oHEAD=document.getElementsByTagName('HEAD').item(0);
		//oHEAD.appendChild(style);
		
		//Tableであり、borderであれば、再描画
		if(browser.isGecko) {
			if(property_name.match(/^border/)) {
				var samplefields = Element.getElementsByClassName(document, class_arr[class_arr.length-1]);
				samplefields.each(function(class_el) {
					if(class_el.tagName == "TABLE" || class_el.tagName == "TR" || class_el.tagName == "TD") {
						commonCls.displayChange(class_el);
						setTimeout(function(){commonCls.displayChange(class_el);}.bind(this), 100);
					}
				}.bind(this));
			}
		}

		//set params
		
		if(!this.color_params[theme_name]) {
			this.color_params[theme_name] = new Object();
			this.color_params[theme_name]['color'] = new Object();	
		}
		if(!this.color_params[theme_name]['color'][el.className]) {
			this.color_params[theme_name]['color'][el.className] = new Object();	
		}
		this.color_params[theme_name]['color'][el.className][property_name] = color;
		if(property_name == "background-color" && this.color_params[theme_name]['color'][el.className]['background']) {
			
			this.color_params[theme_name]['color'][el.className]['background'] = null;
			this.color_params[theme_name]['color'][el.className]['background-image'] = "none";
		}
		var rgb = commonCls.getRGBtoHex(color);
		var hsl = commonCls.getHSL(rgb.r,rgb.g,rgb.b);
		var set_color_flag = false;
		
		var inputList = top_el.getElementsByTagName("input");
		for (var i = 0; i < inputList.length; i++){
			if(inputList[i].disabled == true) {
				//自動
				var ref_class_name_el = inputList[i].previousSibling;
				var ref_class_name = ref_class_name_el.value;
				var same_flag = false;
				//same:であれば、指定されたクラスと同じ色
				if(ref_class_name.match(/^same:/)) {
					same_flag = true;
					ref_class_name = ref_class_name.replace(/^same:/, "");
				}
				var ref_property_name_el = ref_class_name_el.previousSibling;
				var current_el = Element.getChildElement(inputList[i].parentNode);
				var current_property_name = current_el.value;
				//ref_property_name_el.previousSibling.value;
//				
				if(el.className == ref_class_name &&
					property_name == ref_property_name_el.value) {
					var buf_count_color = count_color;
					var change_flag = false;
					if(buf_count_color) {
						//規定値に戻す
						while(buf_count_color > 0) {
							if(current_el) {
								current_el = current_el.nextSibling;
								buf_count_color--;
							} else {
								break;
							}
						}
						if(current_el && Element.hasClassName(current_el,"_blocktheme_box")) {
							current_el.onclick();
							change_flag = true;
						}
					}
					if(!change_flag) {
						//色自動選択
						if(same_flag) {
							//同じ色
							var set_color = color;
						} else if(current_property_name == "background-color") {
							//背景の場合は、淡く
							//var new_hsl_h = hsl.h+6;
							//var new_hsl_s = hsl.s+141;
							//var new_hsl_l = 242;
							var new_hsl_s = hsl.s;//+80;
							var new_hsl_l = 240;
							var new_rgb = commonCls.getRBG(hsl.h, new_hsl_s, new_hsl_l);
							var set_color = commonCls.getHex(new_rgb.r,new_rgb.g,new_rgb.b);
						} else {
							//それ以外は、濃く
							//var new_hsl_l = hsl.l*1.1;
							//var new_hsl_h = hsl.h+18;
							//var new_hsl_s = hsl.s+58;
							//var new_hsl_l = 124;
							var new_hsl_s = hsl.s;
							var new_hsl_l = 124;
							var new_rgb = commonCls.getRBG(hsl.h,new_hsl_s,new_hsl_l);
							var set_color = commonCls.getHex(new_rgb.r,new_rgb.g,new_rgb.b);
						}
						inputList[i].value = set_color;
						inputList[i].onchange();
					}
				}
			}
		}
		//eval("el.style."+property_name + "='" + color+"';");
		//if(!(browser.isIE) && property_name.match("border")) {
		//	//firefoxのborderの場合、すぐに反映されないため
		//	$("_container").style.display = "none";
		//}
		
		pagestyleCls.setHighlight(this_el);
	},
	/* ブロックデザインOK、適用ボタン */
	blockstyleSubmit: function(id, block_id, inside_flag, current_theme_name, confirm_mes, winclose_flag) {
		this.confirm_mes = confirm_mes;
		var winclose_flag = (winclose_flag == undefined || winclose_flag == null) ? true : winclose_flag;	
		var top_el = $(id);
		if(!top_el) {
			return;
		}
		var form = top_el.getElementsByTagName("form")[0];
		var param = "action=dialog_blockstyle_action_edit_init&block_id="+ block_id + "&"+ Form.serialize(form);
		var current_queryParams = param.parseQuery();
	
		//配色-カスタム
		if(this.color_params[current_theme_name] &&
			this.color_params[current_theme_name]['color']) {
			current_queryParams = Object.extend(current_queryParams, this.color_params[current_theme_name]);
		}
		//テーマ設定
		//変更があれば登録処理へ
		if((current_queryParams['pre_block_name'] != current_queryParams['block_name'] ||
			current_queryParams['pre_theme_kind'] != current_queryParams['theme_kind'] ||
			current_queryParams['pre_template_kind'] != current_queryParams['template_kind'] ||
			current_queryParams['pre_minwidthsize'] != current_queryParams['minwidthsize'] ||
			current_queryParams['pre_topmargin'] != current_queryParams['topmargin'] ||
			current_queryParams['pre_rightmargin'] != current_queryParams['rightmargin'] ||
			current_queryParams['pre_bottommargin'] != current_queryParams['bottommargin'] ||
			current_queryParams['pre_leftmargin'] != current_queryParams['leftmargin']) ||
			current_queryParams['color']) {
			//パラメータ取得
			var theme_params = new Object();
			
			var return_param = new Object();
			return_param['id'] = id;
			return_param['winclose_flag'] = winclose_flag;
			return_param['param'] = current_queryParams;
			return_param['block_name'] = current_queryParams['block_name'];
			return_param['pre_block_name'] = current_queryParams['pre_block_name'];
			return_param['topmargin'] = current_queryParams['topmargin'];
			return_param['rightmargin'] = current_queryParams['rightmargin'];
			return_param['bottommargin'] = current_queryParams['bottommargin'];
			return_param['leftmargin'] = current_queryParams['leftmargin'];
			
			return_param['pre_theme_kind'] = current_queryParams['pre_theme_kind'];
			return_param['pre_template_kind'] = current_queryParams['pre_template_kind'];
			return_param['theme_kind'] = current_queryParams['theme_kind'];
			return_param['template_kind'] = current_queryParams['template_kind'];
			return_param['pre_minwidthsize'] = current_queryParams['pre_minwidthsize'];
			return_param['minwidthsize'] = current_queryParams['minwidthsize'];
			return_param['inside_flag'] = inside_flag;
				
			theme_params["method"] = "post";
			theme_params["param"] = current_queryParams;
			//theme_params["param"] = param;
			//var blockstyle_token = Element.getChildElementByClassName(top_el,"blockstyle_token");
			//if(blockstyle_token) theme_params["token"] = blockstyle_token.value;
			theme_params["top_el"] = top_el;
			theme_params["loading_el"] = top_el;
			theme_params["callbackfunc"] = function(return_param,res){this.themeChangeComplete(return_param,res);}.bind(this);
			theme_params["callbackfunc_error"] = function(return_param,res){this.themeChangeComplete(return_param,res);}.bind(this);
			theme_params["func_param"] = return_param;
			theme_params["func_error_param"] = return_param;

			commonCls.send(theme_params);
		} else {
			if(winclose_flag) {
				//閉じる
				form.cancel.onclick();
				//commonCls.removeBlock(id);
			}
		}	
	},
	/* ブロックテーマ カスタム画面登録 */
	/*
	customSubmit: function(id, confirm_mes) {
		var top_el = $(id);
		
		var form = top_el.getElementsByTagName("form")[0];
		var param = "action=dialog_blockstyle_action_admin_customcolor&"+ Form.serialize(form);
		var current_queryParams = param.parseQuery();
		//if(!this.colorclick_flag && 
		//	current_queryParams['blocktheme_name'] == current_queryParams['pre_blocktheme_name']) {
		if(!this.colorclick_flag) {
			this.cancelClick(id);
			return;
		}
		if(!commonCls.confirm(confirm_mes)) return;
		//登録処理
		current_queryParams = Object.extend(current_queryParams, this.color_params);

		var custom_params = new Object();
		custom_params["method"] = "post";
		custom_params["param"] = current_queryParams;
		custom_params["top_el"] = top_el;
		custom_params["callbackfunc"] =  function(){
											this.refresh();
										}.bind(this);
		commonCls.send(custom_params);
	},
	*/
	/*
	refresh: function() {
		var jump_url = _nc_base_url + _nc_index_file_name + "?action="+ this.pages_action +"&page_id="+this.page_id+
												"&block_id="+this.block_id+
												"&parent_id="+this.parent_id+"&inside_flag=1&active_tab=1"+
												"&blocktheme_name="+this.theme_name+
												"&active_action=dialog_blockstyle_view_edit_init";
		if(jump_url == _nc_current_url) {
			location.reload(true);
		} else {
			location.href = jump_url+ "#_" + this.block_id;
		}
	},
	*/
	themeChangeComplete: function(return_param, res) {
		var top_el = $(return_param['id']);
		var parent_el = Element.getChildElementByClassName(top_el,"blockstyle_parent_id_name");
		if(parent_el && parent_el.value) {
			var parent_top_el = $(parent_el.value);
			var parent_id = parent_el.value;
		} else {
			var parent_top_el = null;
		}
		var form = top_el.getElementsByTagName("form")[0];	
				
		if(res == "") {
			var queryParams = return_param['param'];//.parseQuery();
			//var refresh_flag = false;
			if(parent_id) {
				var url = commonCls.cutParamByUrl(commonCls.getUrl(parent_id)).parseQuery();
				url["active_tab"] = 1;
				if(url['action'] == "pages_action_grouping") {
					url['action'] = "pages_view_grouping";
				}
			}
			//表示アドレス変更
			//var url_el = Element.getChildElementByClassName(top_el,"dialog_property_url");
			//url_el.innerHTML = url.escapeHTML();		
			if(parent_top_el) {
				//マージン設定
				if(parent_top_el.parentNode.tagName != "BODY") {
					parent_top_el.parentNode.style.padding = return_param['topmargin'] + "px" + " " + return_param['rightmargin'] + "px" + " " + return_param['bottommargin'] + "px" + " " + return_param['leftmargin'] + "px";
				}
				
				form.pre_block_name.value = return_param['block_name'];
				form.pre_theme_kind.value = return_param['theme_kind'];
				form.pre_template_kind.value = return_param['template_kind'];
				form.pre_minwidthsize.value = return_param['minwidthsize'];
				form.pre_topmargin.value = return_param['topmargin'];
				form.pre_rightmargin.value = return_param['rightmargin'];
				form.pre_bottommargin.value = return_param['bottommargin'];
				form.pre_leftmargin.value = return_param['leftmargin'];
				
				if(return_param['pre_block_name'] != return_param['block_name'] ||
				    return_param['pre_theme_kind'] != return_param['theme_kind'] ||
					return_param['pre_template_kind'] != return_param['template_kind'] ||
					return_param['pre_minwidthsize'] != return_param['minwidthsize']) {

					if(parent_top_el.parentNode.tagName == "BODY" || this.refresh_flag[return_param['id']] == true) {
						//グループ化しているブロックのみの表示ならば再描画
						location.reload();
						return false;
					} else if(return_param['pre_template_kind'] != return_param['template_kind']) {
						//テンプレート変更
						//if(commonCls.confirm(this.confirm_mes)) {
							location.reload();
						//}
						return false;
					}
					
					
					if(return_param['inside_flag']) {
						if(return_param['winclose_flag'] == true) {
							form.cancel.onclick();
						} else {
							//親ウィンドウ再読み込み処理
							commonCls.sendView(return_param['id'], url, null, true);
						}
					} else {
						var win_params = new Object();
						win_params["method"] = "get";
						win_params["param"] = url;
						win_params["loading_el"] = parent_top_el;
						win_params["target_el"] = parent_top_el.parentNode;
						win_params["callbackfunc"] = function(top_el){commonCls.moveVisibleHide(top_el);}.bind(this);
						win_params["func_param"] = top_el;
						commonCls.send(win_params);
						if(return_param['winclose_flag'] == true) {
							form.cancel.onclick();
						}
					}
				} else {
					if(this.refresh_flag[return_param['id']] == true) {
						location.reload();
						return true;
					}
					if(return_param['winclose_flag'] == true) {
						form.cancel.onclick();
					}
				}
			}
			/*
			var theme_name = return_param['theme_kind'];
			var styleList = document.getElementsByTagName("style");
			for (var i = 0; i < styleList.length; i++){
				if (styleList[i].title == "_blockstyle_custom_style" + theme_name){
					Element.addClassName(styleList[i], "_links_commit");
					//break;
				}
			}
			if(return_param['winclose_flag'] == true) {
				//閉じる
				form.cancel.onclick();
				//commonCls.removeBlock(return_param['id']);
			} else {
				//hiddenタグ書き換え処理
				var elements = Form.getElements(form);
				var re_pre = new RegExp("pre_", "i");
    			for (var i = 0; i < elements.length; i++) {
      				if (elements[i]) {
      					if(elements[i].name != "prefix_id_name" && elements[i].name != "blockstyle_parent_id_name") {
	      					if(elements[i].type == "hidden") {
	      						var key = elements[i].name.replace(re_pre,"");
	      						
	      						if(key == "block_name") {
	      							return_param[key] = decodeURIComponent(return_param[key]);
	      						}
	      						elements[i].value = return_param[key];
	      					}
	      				}
      				}
    			}
			}
			*/
		}else {
			if(res.match(":")) {
				var mesArr = res.split(":");
				var alert_res = "";
				for(var i = 1; i < mesArr.length; i++) {
					alert_res += mesArr[i];
				}			
				var elements = Form.getElements(form);
    			for (var i = 0; i < elements.length; i++) {
      				if (elements[i]) {
      					if(elements[i].name == mesArr[0]) {
      						try {
      							commonCls.alert(mesArr[1]);
          						elements[i].focus();
	      						if(elements[i].type == "text")
									elements[i].select();
        					} catch (e) {}
							break;
      					}
      				}
    			}
			} else {
				commonCls.alert(res);
			}
		}
		
	},
	themeClick: function(id, inside_flag, this_el, theme_name) {
		var blocktheme_top = Element.getChildElementByClassName($(id), "_blocktheme_top");
		var themefields = Element.getElementsByClassName(blocktheme_top, "_blocktheme");
		
		var return_flag = false;
		var highlight_flag = false;
		themefields.each(function(field) {
			if(Element.hasClassName(field,"highlight")) {
				highlight_flag = true;
				if(field == this_el) {
					/* 変更なし */
					return_flag = true;
					return;
				} else {
					Element.removeClassName(field,"highlight");
				}
			}
		}.bind(this));
		if(return_flag || (theme_name == "_auto" && highlight_flag == false)) {
			return;
		}
		
		Element.addClassName(this_el,"highlight");
		
		var top_el = $(id);
		if(!inside_flag) {
			var parent_el = Element.getChildElementByClassName(top_el,"blockstyle_parent_id_name");
			if(parent_el && parent_el.value) {
				var parent_top_el = $(parent_el.value);
				var parent_id = parent_el.value;
			}
		} else {
			var parent_id = id;
		}
		//theme_kind更新
		//var form = top_el.getElementsByTagName("form")[0];
		//form.theme_kind.value = theme_name;
		if(!$(parent_id)) {
			return;
		}
		var url = commonCls.cutParamByUrl(commonCls.getUrl(parent_id)).parseQuery();
		if(url['action'] == "pages_action_grouping") {
			url['action'] = "pages_view_grouping";
		}
		url['blocktheme_name'] = theme_name;
		////url['_layoutmode_onetime'] = "off";
		url['active_tab'] = 0;
		
		//スタイルシート追加処理
		////commonCls.addBlockTheme(theme_name);
		var send_params = new Object();
		send_params["callbackfunc"] = function(res){
			if(browser.isGecko) {
				//FireFoxでは、CSSが動的に読み込まれた後にすぐスタイルを適用してくれないため
				Element.addClassName(Element.getChildElement(send_params["target_el"]), "collapse_separate");
				setTimeout(function(){
					Element.removeClassName(Element.getChildElement(this), "collapse_separate");
				}.bind(send_params["target_el"]), 100);
			}
		}.bind(this);
		
		if(!inside_flag) {
			var popup_url = commonCls.cutParamByUrl(commonCls.getUrl(id)).parseQuery();
			if(popup_url['action'] == "pages_action_grouping") {
				popup_url['action'] = "pages_view_grouping";
			}
			popup_url['blocktheme_name'] = theme_name;
			popup_url['active_tab'] = 0;
			//commonCls.sendView(id, popup_url);
			var top_el = $(id);
			var theme_params = new Object();
			theme_params["method"] = "get";
			theme_params["param"] = popup_url;
			theme_params["top_el"] = top_el;
			theme_params["target_el"] = top_el.parentNode;
			theme_params["loading_el"] = top_el;
			theme_params["callbackfunc"] =  function(){
												url['theme_name'] = theme_name;
												//親ウィンドウ再読み込み処理
												commonCls.sendView(parent_id, url, send_params, true);
											}.bind(this);
			commonCls.send(theme_params);
		} else {
			//親ウィンドウ再読み込み処理
			commonCls.sendView(parent_id, url, send_params, true);
		}
		/* send */
		//var theme_params = new Object();
		//theme_params["method"] = "post";
		//theme_params["param"] = {"action":"dialog_blockstyle_action_edit_change","page_id":this.page_id,"_pagetheme_flag":1,"theme_name": theme_name};
		//theme_params["callbackfunc"] =  function(){
		//									this.refresh();
		//								}.bind(this);
		//commonCls.send(theme_params);
	},
	/*
	cancelClick: function(id) {
		this.colorclick_flag = false;
		delete this.color_params;
		this.color_params = new Object();
		this.color_params['color'] = new Object();
		commonCls.removeBlock(id);
		if(this.defaultcolor_flag) {
			this.refresh();
		}
		this.defaultcolor_flag = false;
		
	},
	*/
	/* 規定値に戻す */
	defaultColorClick: function(id, theme_name) {
	    var top_el = $(id);
	    this.refresh_flag[id] = false;
		//var form = top_el.getElementsByTagName("form")[0];
		
		/* send */
		var defultcolor_params = new Object();
		defultcolor_params["method"] = "post";
		defultcolor_params["param"] = {"action":"dialog_blockstyle_action_admin_setdefault","theme_name":theme_name};
		defultcolor_params["top_el"] = $(id);
		defultcolor_params["callbackfunc"] =  function(){
											location.reload();
										}.bind(this);
		commonCls.send(defultcolor_params);
	},
	delStyleDef: function(theme_name) {
		location.href = decodeURIComponent(_nc_current_url).replace("&amp;","&");
		//var style = $("_blockstyle_custom_style" + theme_name);
		
		/*
		var style = null;
		var styleList = document.getElementsByTagName("style");
		for (var i = 0; i < styleList.length; i++){
			if (styleList[i].title == "_blockstyle_custom_style" + theme_name){
				style = styleList[i];
				break;
			}
		}
		if(style && !Element.hasClassName(style, "_links_commit")) {
			Element.remove(style);
		}
		*/
	}
}

blockstyleCls = new clsBlockstyle();