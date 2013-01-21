/*
 * NC TableBoder 0.0.0.1
 * 
 *                      wysiwyg         : nc_wysiwyg object
 *                      active_tab      : Active Tab Name (table or row or col or cell)
 *                      table_el        : 選択table element
 *						pos_title       : 位置のタイトル
 *                      sel_els         : 選択エレメント(table or tr or td)															 },
 *                      borderWidthList : array 線の太さの値を配列で設定。default ['1px', '2px', '3px', '4px', '5px']
 * 	            	　　borderStyleList : array 線のスタイルリストボックスの値を配列で設定。default ['solid','double','dashed','dotted','inset','outset']
 * 	                    borderColor     : string 線の色のデフォルト値を設定。default "#666666"
 */
 var compTableBorder = Class.create();
compTableBorder.prototype = {
	options           : {},
	active_tab        : null,
	property          : {},
	listWidth         : null,
	listStyle         : null,
	
	initialize : function(options) 
	{
		var t = this;
		t.options = $H({wysiwyg         : null,
						active_tab      : null,
						table_el        : null,
						pos_title       : '',
						sel_els         : null,
						borderWidthList : ['1px', '2px', '3px', '4px', '5px'],
						borderStyleList : ['solid','double','dashed','dotted','inset','outset'],
						borderColor     : "#000000"
	        		}).merge($H(options));
				
		// trならば、tdにsel_elsを変換
		if(t.options.sel_els[0].nodeName.toLowerCase() == "tr") {
			var sel_els = [];
			for (var k = 0; k < t.options.sel_els.length; ++k) {
				var v = t.options.sel_els[k];
				for (var i = 0; i < v.childNodes.length; i++) {
					if(v.childNodes[i].nodeName.toLowerCase() == "td") {
						sel_els.push(v.childNodes[i]);
					}
				}
			}
			t.options.sel_els = sel_els;
		}
		t.active_tab = t.options.active_tab;
		t.property = {};
		t.listWidth = null;
		t.listStyle = null;
		var c = commonCls.getColorCode(t.options.sel_els[0],"borderTopColor");
		t.property = {
			width			: (t.options.sel_els[0].getAttribute("_nc_wysiwyg_border_top")) ? "" : Element.getStyle(t.options.sel_els[0], 'borderTopWidth'),
			style			: (t.options.sel_els[0].getAttribute("_nc_wysiwyg_border_top")) ? "" : Element.getStyle(t.options.sel_els[0], 'borderTopStyle'),
			color			: (c == "transparent" || t.options.sel_els[0].getAttribute("_nc_wysiwyg_border_top")) ? t.options.borderColor : c,
			border          : {Top: true, Right: true, Bottom: true, Left: true}
		};
		
		return t;
	},

	showTableBorder : function( self ) {
		var t = this, table, div, li, list, borderstyle, bordercolor, buttons;
			
		var borderWidth = t.property.width;
		var borderStyle = t.property.style;
		var borderColor = t.property.color;
		
		div = document.createElement('div');
		Element.setStyle(div, {clear:'left'});
		self.appendChild(div);
		
		table = document.createElement('ul');
		Element.addClassName(table,"nc_wysiwyg_tableboder");
		div.appendChild(table);
		
		li = document.createElement('li');
		li.innerHTML = '<dl><dt>'+ compTableBorderLang[t.active_tab] +'</dt>' + 
			((t.active_tab != 'table') ? '<dd>'+ compTableBorderLang['separator'] + t.options.pos_title +'</dd>' : '') + '</dl>';
		Element.addClassName(li,"nc_wysiwyg_tableborder_title");
		table.appendChild(li);

		_createBoderPreview();
		
		borderwidth = document.createElement('li');
		Element.addClassName(borderwidth,"nc_wysiwyg_tableborder_content");
		Element.addClassName(borderwidth,"float-left");
		table.appendChild(borderwidth);
				
		list = _initListMenu('border_width', t.options.borderWidthList, 'border-bottom:', ' solid;');
		t.listWidth = t.options.wysiwyg.appendCommonList(borderwidth, list, "nc_wysiwyg_tableborder_border",_borderWidthEvent,[]);
		t.options.wysiwyg.chgList(t.listWidth, borderWidth);
		
		borderstyle = document.createElement('li');
		Element.addClassName(borderstyle,"nc_wysiwyg_tableborder_content");
		Element.addClassName(borderstyle,"float-left");
		table.appendChild(borderstyle);
		
		list = _initListMenu('border_style', t.options.borderStyleList, 'border-bottom:3px ', ';');
		t.listStyle = t.options.wysiwyg.appendCommonList(borderstyle, list, "nc_wysiwyg_tableborder_border",_borderStyleEvent,[]);
		t.options.wysiwyg.chgList(t.listStyle, borderStyle);

		bordercolor = document.createElement('li');
		bordercolor.id = 'nc_wysiwyg_tableboder_borderColor';
		bordercolor.innerHTML = '<dl>'+
									'<dt>'+ compTableBorderLang['color'] + compTableBorderLang['separator'] + '</dt>'+
									'<dd>'+
										'<input type="text" name="bordercolor" class="nc_wysiwyg_tableborder_color_input" value="'+ borderColor +'" maxlength="7"  />'+
										'<a href="javascript:;" class="nc_wysiwyg_tableborder_color">'+
											'<img style="background-color:' + (borderColor ? borderColor : t.options.borderColor) + ';"' + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + "" + '" alt="' + "" + '" />'+
										'</a>'+
									'</dd>'+
								'</dl>';
		Element.addClassName(bordercolor,"nc_wysiwyg_tableborder_content");
		Element.addClassName(bordercolor,"float-left");
		table.appendChild(bordercolor);

		Event.observe($("nc_wysiwyg_tableboder_icon_none"),"click", function(e){
			t.property.border = {Top: false, Right: false, Bottom: false, Left: false};
			t.setPreview("none");
			Event.stop(e);
			return false;
		});
		
		Event.observe($("nc_wysiwyg_tableboder_icon_outer"),"click", function(e){
			t.property.border = {Top: true, Right: true, Bottom: true, Left: true};
			_chgList();
			t.setPreview();
			Event.stop(e);
		    return false;
		});
		
		Event.observe($("nc_wysiwyg_tableboder_icon_top"),"click", function(e){
			t.property.border['Top'] = (t.property.border['Top']) ? false : true;
			if(t.property.border['Top'])
				_chgList();
			t.setPreview("Top");
			Event.stop(e);
		    return false;
		});
		
		Event.observe($("nc_wysiwyg_tableboder_icon_bottom"),"click", function(e){
			t.property.border['Bottom'] = (t.property.border['Bottom']) ? false : true;
			if(t.property.border['Bottom'])
				_chgList();
			t.setPreview("Bottom");
			Event.stop(e);
		    return false;
		});
		
		Event.observe($("nc_wysiwyg_tableboder_icon_left"),"click", function(e){
			t.property.border['Left'] = (t.property.border['Left']) ? false : true;
			if(t.property.border['Left'])
				_chgList();
			t.setPreview("Left");
			Event.stop(e);
		    return false;
		});
		
		Event.observe($("nc_wysiwyg_tableboder_icon_right"),"click", function(e){
			t.property.border['Right'] = (t.property.border['Right']) ? false : true;
			if(t.property.border['Right'])
				_chgList();
			t.setPreview("Right");
			Event.stop(e);
		    return false;
		});
			
		// borderカラークリック
		var tableborder_color = Element.getChildElementByClassName(div, "nc_wysiwyg_tableborder_color");
		Event.observe(tableborder_color,"click", function(e){
			var self = Event.element(e);
			if(self.nodeName.toLowerCase() == "img")
				self = self.parentNode;
			var c = commonCls.getColorCode(self.childNodes[0], 'backgroundColor');
			if(c == "transparent")
				c = t.options.color;
			var callback = function() {
				var opts = {
					colorcode : c,
					callback  : function(v) {
						Element.setStyle(self.childNodes[0], {backgroundColor:v});
						self.previousSibling.value = v;
						t.property.color = v;
						//t.setPreview();
						t.options.wysiwyg.removeDialog("nc_wysiwyg_tableborder_color");
					},
					cancel_callback  : function(v) {
						t.options.wysiwyg.removeDialog("nc_wysiwyg_tableborder_color");
					}
				};
				var colorpicker = new compColorPicker(opts);
				colorpicker.showColorPicker($("nc_wysiwyg_tableborder_color"));
			};
			var toggle_options = {
				id        : "nc_wysiwyg_tableborder_color",
				js        : ['comp_colorpicker'],
				jsname    : ['window.compColorPicker'],
				pos_base  : self.previousSibling,
				css       : ['comp_colorpicker.css'],
				style     : {left:"left", top:"outbottom"},
				callback  : callback
			};
			t.options.wysiwyg.toggleDialog(self, toggle_options);
			Event.stop(e);
		    return false;
		});
		
		var tableborder_color_input = Element.getChildElementByClassName(div, "nc_wysiwyg_tableborder_color_input");
		Event.observe(tableborder_color_input,"keyup", function(e){
			var self = Event.element(e);
			var c = self.value;
			if(c.match(/^#[0-9a-f]{6}/i)) {
				Element.setStyle(self.nextSibling.childNodes[0], {backgroundColor:c});
				t.property.color = c;
				//t.setPreview();
			}
		});
		Event.observe(tableborder_color_input,"focus", function(e){
			t.options.wysiwyg.removeDialog("nc_wysiwyg_tableborder_color");
		});
		
		//ok cancel button
		buttons = document.createElement('div');
		Element.addClassName(buttons,"nc_wysiwyg_tableborder_btn");
		buttons.innerHTML = '<input id="nc_wysiwyg_tableborder_ok" name="ok" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['ok']+'" />' +
							'<input id="nc_wysiwyg_tableborder_cancel" name="cancel" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['cancel']+'" />';
		self.appendChild(buttons);
		
		Event.observe($("nc_wysiwyg_tableborder_ok"),"click", function(e){
			_registBorder();
			t.options.wysiwyg.removeDialog("nc_wysiwyg_tableborder");
			Event.stop(e);
			return false;
		});
		
		Event.observe($("nc_wysiwyg_tableborder_cancel"),"click", function(e){
			t.options.wysiwyg.removeDialog("nc_wysiwyg_tableborder");
			Event.stop(e);
		    return false;
		});

		function _borderWidthEvent(e, v){
			t.property.width = v;
			_chgList("width");
			return true;
		}
		function _borderStyleEvent(e, v){
			t.property.style = v;
			_chgList("style");
			//t.setPreview();
			return true;
		}
		function _createBoderPreview(){
			var li = document.createElement('li');
			li.innerHTML = '<ul class="nc_wysiwyg_tableborder_align">'+
								'<li><ul class="nc_wysiwyg_tableboder_icon_l">'+
									'<li><a href="javascript:;" id="nc_wysiwyg_tableboder_icon_none"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-border-none.gif" title="'+ compTableBorderLang['none'] +'" alt="'+ compTableBorderLang['none'] +'" /></a></li>'+
									'<li><a href="javascript:;" id="nc_wysiwyg_tableboder_icon_outer"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-border-outer.gif" title="'+ compTableBorderLang['outer'] +'" alt="'+ compTableBorderLang['outer'] +'" /></a></li>'+
								'</ul></li>'+
								'<li class="nc_wysiwyg_tableboder_align_clear"><ul class="nc_wysiwyg_tableboder_icon">'+
									'<li class="nc_wysiwyg_tableborder_preview">' +
										'<div id="nc_wysiwyg_tableborder_preview">'+
										'</div>' +
									'</li>'+
									'<li><a href="javascript:;" id="nc_wysiwyg_tableboder_icon_top"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-border-top.gif" title="'+ compTableBorderLang['top'] +'" alt="'+ compTableBorderLang['top'] +'" /></a></li>'+
									'<li><a href="javascript:;" id="nc_wysiwyg_tableboder_icon_bottom"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-border-bottom.gif" title="'+ compTableBorderLang['bottom'] +'" alt="'+ compTableBorderLang['bottom'] +'" /></a></li>'+
								'</ul></li>'+
								'<li class="nc_wysiwyg_tableboder_align_clear"><ul class="nc_wysiwyg_tableboder_icon_bottom">'+
									'<li><a href="javascript:;" id="nc_wysiwyg_tableboder_icon_left"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-border-left.gif" title="'+ compTableBorderLang['left'] +'" alt="'+ compTableBorderLang['left'] +'" /></a></li>'+
									'<li><a href="javascript:;" id="nc_wysiwyg_tableboder_icon_right"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-border-right.gif" title="'+ compTableBorderLang['right'] +'" alt="'+ compTableBorderLang['right'] +'" /></a></li>'+
								'</ul></li>'+
							'</ul>';
			Element.addClassName(li,"nc_wysiwyg_tableborder_content");
			Element.addClassName(li,"float-left");
			table.appendChild(li);
		}
		function _initListMenu(title, list, style_pre, style_post) {
			var html = {'' : compTableBorderLang[title]};
			for (var i = 0; i < list.length; ++i) {
			
				html[list[i]] = '<div style="' + style_pre + list[i] + style_post +';"></div>';
			}
			return html;
		}
		
		function _chgList(type) {
			var width = t.options.wysiwyg.getList(t.listWidth);
			var style = t.options.wysiwyg.getList(t.listStyle);
			if(width == "" && type != "width" && (type == undefined || style != "")) {
				t.options.wysiwyg.chgList(t.listWidth, "1px", ((type == undefined) ? true : false));
				t.property.width = "1px";
			}
			if(style == "" && type != "style" && (type == undefined || width != "")) {
				t.options.wysiwyg.chgList(t.listStyle, "solid", ((type == undefined) ? true : false));
				t.property.style = "solid";
			}
		}
		
		/* 登録処理 */
		function _registBorder() {
			switch (t.active_tab) {
				case "table":
					_regist(t.options.sel_els[0]);
					break;
				case "row":
				case "col":
				case "cell":
					for (var k = 0; k < t.options.sel_els.length; ++k) {
						var v = t.options.sel_els[k];
						_regist(v);
					}
					break;
			}
			// 再描画
			Element.setStyle(t.options.table_el, {display:"none"});
			setTimeout(function() { Element.setStyle(t.options.table_el, {display:""}); }, 100);
			
			function _regist(el) {
				var preview = $("nc_wysiwyg_tableborder_preview");
				$A(['Top','Right','Bottom','Left']).each(function(v) {
					el.style["border"+v] = Element.getStyle(preview, "border"+ v);
					if(t.property.border[v] == false) {
						el.style["border"+v] = "1px dotted #666666";
						el.setAttribute("_nc_wysiwyg_border_" + v.toLowerCase(),'1',0);
					} else {
						el.removeAttribute("_nc_wysiwyg_border_" + v.toLowerCase(), 0);
					}
				});
			}
		}
		
		t.showPreview();
		
		setTimeout(function() { $("nc_wysiwyg_tableboder_icon_none").focus(); }, 100);
	},
	
	setPreview : function (type) {
		var t = this, width,style,color;
		var preview = $("nc_wysiwyg_tableborder_preview");
		$A(['Top','Right','Bottom','Left']).each(function(v) {
			if(type == undefined || type == v || type == "none") {
				width = Element.getStyle(preview, "border" + v + "Width");
				style = Element.getStyle(preview, "border" + v + "Style");
				color = commonCls.getColorCode(preview,"border" + v + "Color");
				if(type != "none" && t.property.width != "" && t.property.style != "" && (t.property.border[v] ||
					(width != t.property.width || style != t.property.style || color != t.property.color))) {
					t.property.border[v] = true;
					preview.style["border" + v] = t.property.width + " " + t.property.style + " " + t.property.color;
				} else {
					t.property.border[v] = false;
					preview.style["border" + v] = "2px dotted #666666";
				}
			}
		});
	},

	showPreview : function() {
		// preview
		var t = this, edge_tds = _getEdgeTds(t.options.sel_els);		
		switch (t.active_tab) {
			case "table":
				_setTransparentAttrPreviewLine(t.options.sel_els[0]);
				_tracePreviewLine(Element.getStyle(t.options.sel_els[0], 'border'));
				break;
			case "row":
			case "col":
				_setTransparentAttrPreviewLine(edge_tds[0], "Top");
				_setTransparentAttrPreviewLine(edge_tds[1], "Right");
				_setTransparentAttrPreviewLine(edge_tds[2], "Bottom");
				_setTransparentAttrPreviewLine(edge_tds[0], "Left");
				_tracePreviewLine({
					borderTop    : Element.getStyle(edge_tds[0], 'borderTop'),
					borderRight  : Element.getStyle(edge_tds[1], 'borderRight'),
					borderBottom : Element.getStyle(edge_tds[2], 'borderBottom'),
					borderLeft   : Element.getStyle(edge_tds[0], 'borderLeft')
				});
				break;
			case "cell":
				_setTransparentAttrPreviewLine(edge_tds[0]);
				_tracePreviewLine({
					borderTop    : Element.getStyle(edge_tds[0], 'borderTopWidth') + " " + Element.getStyle(edge_tds[0], 'borderTopStyle') + " " + Element.getStyle(edge_tds[0], 'borderTopColor'),
					borderRight  : Element.getStyle(edge_tds[0], 'borderRightWidth') + " " + Element.getStyle(edge_tds[0], 'borderRightStyle') + " " + Element.getStyle(edge_tds[0], 'borderRightColor'),
					borderBottom : Element.getStyle(edge_tds[0], 'borderBottomWidth') + " " + Element.getStyle(edge_tds[0], 'borderBottomStyle') + " " + Element.getStyle(edge_tds[0], 'borderBottomColor'),
					borderLeft   : Element.getStyle(edge_tds[0], 'borderLeftWidth') + " " + Element.getStyle(edge_tds[0], 'borderLeftStyle') + " " + Element.getStyle(edge_tds[0], 'borderLeftColor')
				});
				break;
		}
		
		return;
		
		function _tracePreviewLine(border) {
			var preview = $("nc_wysiwyg_tableborder_preview");
			if (typeof(border) == 'string') {
				Element.setStyle(preview, {border:border});
			} else {
				Element.setStyle(preview, border);
			}
			$A(['Top','Right','Bottom','Left']).each(function(v) {
				if(t.property.border[v] == false) {
					preview.style["border" + v + "Width"] = "2px";
				}
			});
			//preview.css("borderWidth", "3px");
		}
		
		// tableボーダー0の場合、tableが点線に変わっているため、ダイアログも合わせる
		function _setTransparentAttrPreviewLine(el, value) {
			var preview = $("nc_wysiwyg_tableborder_preview");
			$A(['Top','Right','Bottom','Left']).each(function(v) {
				if(el.getAttribute("_nc_wysiwyg_border_" + v.toLowerCase()) && (value == undefined || value == v)) {
					t.property.border[v] = false;
				}
			});
		}
		
		// 中心部から最も遠いtdを4点選出
		function _getEdgeTds(sel_els) {
			var ret = [sel_els[0], sel_els[0], sel_els[0], sel_els[0]];
			if(sel_els[0].nodeName.toLowerCase() != "table") {
				for (var k = 0; k < sel_els.length; ++k) {
					var v = sel_els[k];
					if(ret[0] == null || ret[0].parentNode.rowIndex > v.parentNode.rowIndex &&
						ret[0].cellIndex > v.cellIndex) {
						ret[0] = v;
					}
					if(ret[1] == null || ret[1].parentNode.rowIndex > v.parentNode.rowIndex &&
						ret[1].cellIndex < v.cellIndex) {
						ret[1] = v;
					}
					if(ret[2] == null || ret[2].parentNode.rowIndex < v.parentNode.rowIndex &&
						ret[2].cellIndex > v.cellIndex) {
						ret[2] = v;
					}
					if(ret[3] == null || ret[3].parentNode.rowIndex < v.parentNode.rowIndex &&
						ret[3].cellIndex < v.cellIndex) {
						ret[3] = v;
					}
				}
			}
			return ret;
		}
	}
}