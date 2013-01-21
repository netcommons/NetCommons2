/*
 * NC TableProperty 0.0.0.1
 * @param options    hash
 *                      wysiwyg         : nc_wysiwyg object
 *                      table_pos       : hash   　nc_wysiwygのgetSelectTablePosメソッドの返り値（詳しくはgetSelectTablePosメソッド参照）
 *                      color           : string カラーダイアログのデフォルト値
 */
var compTableProperty = Class.create();
compTableProperty.prototype = {
	options           : {},
	active_tab        : null,
	property          : {},

	initialize : function(options)
	{
		var t = this;
		t.options = $H({table_pos	: null,
						wysiwyg     : null,
						color       : "#ff0000"
	        		}).merge($H(options));
		t.active_tab = t.options['table_pos']['sel_name'];
		t.property = t.getPropertyValue(t.options.table_pos);
		return t;
	},

	getPropertyValue : function(table_pos)
	{
		var t = this, property = {}, pos;
		property['table'] = _setProperty("table", table_pos['table_el']);
		pos = t.getRowPos();
		property['row'] = _setProperty("row", pos[1][0], pos);
		pos = t.getColPos();
		property['col'] = _setProperty("col", pos[1][0], pos);
		pos = t.getCellPos();
		property['cell'] = _setProperty("cell", pos[1][0], pos);

		return property;

		function _setProperty(type, base_el, pos) {
			var property;
			var c = commonCls.getColorCode(base_el, 'color');
			var bc = commonCls.getColorCode(base_el, 'backgroundColor');
			switch (type) {
				case "table":
					property = {
						sel_els         : [base_el],
						textAlign       : _getTextAlign(base_el.style.textAlign),
						verticalAlign   : _getVerticalAlign(base_el.style.verticalAlign),
						width           : parseInt(base_el.style.width) || "",
						widthUnit       : Element.getStyle(base_el, 'width').match(/%$/) ? '%' : 'px',
						height          : parseInt(base_el.style.height) || "",
						heightUnit      : Element.getStyle(base_el, 'height').match(/%$/) ? '%' : 'px',
						marginWidth     : parseInt(Element.getStyle(base_el, 'marginLeft')) || 0,
						marginHeight    : parseInt(Element.getStyle(base_el, 'marginTop')) || 0,
						cellPadding     : base_el.getAttribute("cellPadding") || "",
						cellSpacing     : base_el.getAttribute("cellSpacing") || "",
						borderCollapse  : Element.getStyle(base_el, 'borderCollapse'),
						backgroundColor : (bc == "transparent") ? "" : bc,
						color           : (c == "transparent") ? "" : c,
						whiteSpace      : Element.getStyle(base_el, 'whiteSpace') || "",
						uniformlySized  : t.getUniformlySized(base_el),
						align           : base_el.getAttribute("align") || "",
						summary         : base_el.getAttribute("summary") || ''
					};
					break;
				case "row":
				case "col":
				case "cell":
					property = {
						pos             : pos[0],
						sel_els         : pos[1],
						textAlign       : _getTextAlign(pos[1][0].style.textAlign),
						verticalAlign   : _getVerticalAlign(pos[1][0].style.verticalAlign),
						width           : (pos[1][0].style.width) ? parseInt(pos[1][0].style.width) : "",
						widthUnit       : (pos[1][0].style.width).match(/%$/) ? '%' : 'px',
						height          : (pos[1][0].style.height) ? parseInt(pos[1][0].style.height) : "",
						heightUnit      : (pos[1][0].style.height).match(/%$/) ? '%' : 'px',
						cellPadding     : (pos[1][0].style.paddingTop) ? parseInt(pos[1][0].style.paddingTop) : "",
						backgroundColor : (bc == "transparent") ? "" : bc,
						color           : (c == "transparent") ? "" : c,
						whiteSpace      : Element.getStyle(pos[1][0], 'whiteSpace') || ""
					};
					break;
			}
			return property;
		}

		function _getTextAlign(value) {
			var ret = "";
			switch (value) {
				case "left":
				case "center":
				case "right":
					ret = value;
					break;
			}
			return ret;
		};

		function _getVerticalAlign(value) {
			var ret = "";
			switch (value) {
				case "top":
				case "middle":
				case "bottom":
					ret = value;
					break;
			}
			return ret;
		};
	},

	// セル幅が均一かどうか
	getUniformlySized : function(table)
	{
		var w=null, ret = null;
		var tds = table.getElementsByTagName("td");
		for (var k = 0; k < tds.length; ++k) {
			var v = tds[k];
			if(ret == false)
				return ret;
			var w_buf = Element.getStyle(v, 'width');
			if(w_buf.match(/%$/)) {
				if(v.colSpan > 1)
					w_buf = parseInt(parseInt(w_buf)/v.colSpan);
				else
					w_buf = parseInt(w_buf);
				if(w == null)
					w = parseInt(w_buf);
				if(w == w_buf) {
					ret = true;
				} else {
					ret = false;
				}
			}
		}
		if(ret == null)
			ret = false;
		return ret;
	},

	// セル幅を均一にセット
	setUniformlySized : function(table)
	{
		var n = table.getElementsByTagName("tr")[0], i = 3, td, cnt = 0, percent = 100;
		// 列数を求める
		for (var i = 0; i < n.childNodes.length; i++) {
			td = n.childNodes[i];
			if(td.colSpan > 1)
				cnt += td.colSpan;
			else
				cnt++;
		}
		percent = Math.floor(100/cnt);
		var tds = table.getElementsByTagName("td");
		for (var k = 0; k < tds.length; ++k) {
			var v = tds[k];
			Element.setStyle(v, {width:(percent*parseInt(v.colSpan)) + "%"});
		}
		return true;
	},

	// 選択行の位置取得
	// rowSpanは考慮せず、単純に選択行を取得する
	// @return array[title string, array rows]
	getRowPos : function() {
		var t = this, rows = {}, ret_rows = [], ret_str = '', apret_str = null, preRowIndex = 0,row_flag=false;
		var lang = compTablePropertyLang['panel'];
		for (var k = 0; k < t.options.table_pos.cell_els.length; ++k) {
			var v = t.options.table_pos.cell_els[k];
			var row = v.parentNode, rowIndex = row.rowIndex;
			if(!rows[rowIndex]) {
				rows[rowIndex] = row;
				ret_rows.push(row);
				if(ret_str == '')
					ret_str += (rowIndex + 1);
				else if(preRowIndex + 1 == rowIndex) {
					if(apret_str != null && row_flag) {
						ret_str += apret_str;
						apret_str = null;
						row_flag = false;
					}
					apret_str = lang['col_sep'] + (rowIndex + 1);
				} else {
					if(apret_str != null) {
						ret_str += apret_str;
						apret_str = null;
					}
					row_flag = true;
					apret_str = lang['row_sep']  + "&nbsp;" + (rowIndex + 1);
				}
				preRowIndex = rowIndex;
			}
		}

		if(apret_str != null)
			ret_str += apret_str;
		return [ret_str, ret_rows];
	},

	// 選択列の位置取得
	// colSpanは考慮せず、単純に選択列を取得する
	// @return array[title string, array cells]
	getColPos : function() {
		var t = this, cells = {}, ret_cells = [], ret_str = '', apret_str = null, preCellIndex = 0, buf_cells = {}, ret_cells = [];
		var lang = compTablePropertyLang['panel'];
		// cellIndex順にソート
		var cell_els = {};
		for (var k = 0; k < t.options.table_pos.cell_els.length; ++k) {
			var v = t.options.table_pos.cell_els[k];
			var row = v.parentNode, rowIndex = row.rowIndex;
			var cell = v, cellIndex = cell.cellIndex;
			if(!cell_els[cellIndex]) {
				cell_els[cellIndex] = {};
			}

			if(!cell_els[cellIndex][rowIndex]) {
				cell_els[cellIndex][rowIndex] = cell;
			}
		}
		cells = cell_els;


		for (var k in cell_els ) {
			var v = cell_els[k];
			if(v) {
				for (var sub_k in cell_els[k] ) {
					var sub_v = cell_els[k][sub_k];
					var cell = sub_v, cellIndex = cell.cellIndex;
					ret_cells.push(cell);
					// 選択列内のその他のセルを取得
					var trs = t.options.table_pos['table_el'].getElementsByTagName("tr");

					for (var other_tr_k = 0; other_tr_k < trs.length; ++other_tr_k) {
						var other_tr_v = trs[other_tr_k];
						var other_tr_v_index = 0;

						var other_tr_v_p = other_tr_v;
						while(1) {
							other_tr_v_p = other_tr_v_p.parentNode;
							if(other_tr_v_p.tagName.toLowerCase() == "table") {
								other_tr_v_p = other_tr_v_p;
								break;
							}
						}
						if(other_tr_v_p != t.options.table_pos['table_el'])
							continue;

						//var tds = other_tr_v.getElementsByTagName("td");
						var tds = other_tr_v.childNodes;
						for (var other_k = 0; other_k < tds.length; ++other_k) {
							var other_v = tds[other_k];
							var other_cellIndex = other_v.cellIndex + other_tr_v_index;
							var other_row = other_v.parentNode, other_rowIndex = other_row.rowIndex;
							if(other_v.colSpan > 1) {
								other_tr_v_index += other_v.colSpan - 1;
							}
							if(cellIndex == other_cellIndex) {
								// 列が等しい
								if(!cells[other_cellIndex]) {
									cells[other_cellIndex] = {};
								}
								if(!cells[other_cellIndex][other_rowIndex]) {
									cells[other_cellIndex][other_rowIndex] = other_v;
									ret_cells.push(other_v);
								}
							}
						}
					}

					if(!buf_cells[cellIndex]) {
						if(ret_str == '')
							ret_str += (cellIndex + 1);
						else if(preCellIndex + 1 == cellIndex)
							apret_str = lang['col_sep'] + (cellIndex + 1);
						else {
							if(apret_str != null) {
								ret_str += apret_str;
								apret_str = null;
							}
							apret_str = lang['row_sep']  + "&nbsp;" + (cellIndex + 1);
						}
						preCellIndex = cellIndex;
						buf_cells[cellIndex] = cell;
					}
				}
			}
		}

		if(apret_str != null)
			ret_str += apret_str;

		return [ret_str, ret_cells];
	},

	// 選択セルの位置取得
	// @return array[title string, array cells]
	getCellPos : function() {
		var t = this;
		var cells = {}, ret_cells = t.options.table_pos.cell_els, ret_str = '';
		var lang = compTablePropertyLang['panel'];

		for (var k = 0; k < t.options.table_pos.cell_els.length; ++k) {
			var v = t.options.table_pos.cell_els[k];
			var row = v.parentNode, rowIndex = row.rowIndex;
			var cell = v, cellIndex = cell.cellIndex;

			if(!cells[rowIndex]) {
				cells[rowIndex] = {};
			}

			if(!cells[rowIndex][cellIndex]) {
				cells[rowIndex][cellIndex] = cell;
				if(ret_str != '')
					ret_str += lang['row_sep'] + "&nbsp;";
				ret_str += (rowIndex + 1) + lang['cell_sep'] + (cellIndex + 1);
			}
		}

		return [ret_str, ret_cells];
	},

	showTableProperty : function( self ) {
		var t = this, div, tabs, table, col, row, cell,buttons;
		var select_tab = t.options['table_pos']['sel_name'];

		tabs = document.createElement('ul');
		Element.addClassName(tabs,"nc_wysiwyg_tableproperty");
		self.appendChild(tabs);

		div = document.createElement('div');
		Element.setStyle(div, {clear:'left'});
		self.appendChild(div);

		_appendTab(tabs, 'table', compTablePropertyLang['tab_name']['table'], (select_tab == 'table') ? true : false);
		div.appendChild(_createContent('table'));

		_appendTab(tabs, 'row', compTablePropertyLang['tab_name']['row'], (select_tab == 'row') ? true : false);
		div.appendChild(_createContent('row', compTablePropertyLang['tab_name']['row']));

		_appendTab(tabs, 'col', compTablePropertyLang['tab_name']['col'], (select_tab == 'col') ? true : false);
		div.appendChild(_createContent('col', compTablePropertyLang['tab_name']['col']));

		_appendTab(tabs, 'cell', compTablePropertyLang['tab_name']['cell'], (select_tab == 'cell') ? true : false);
		div.appendChild(_createContent('cell', compTablePropertyLang['tab_name']['cell']));

		Element.setStyle($('tableproperty_panel_'+select_tab), {display:'block'});

		// 線を重ねる
		Event.observe($("nc_wysiwyg_tableproperty_table_borderCollapse"),"click", function(e){
			var event_el = Event.element(e);
			if(event_el.checked)
				$("nc_wysiwyg_tableproperty_table_cellSpacing").disabled = true;
			else
				$("nc_wysiwyg_tableproperty_table_cellSpacing").disabled = false;
		});

		// 背景選択
		var colors = Element.getElementsByClassName(div, "nc_wysiwyg_tableproperty_sel_color");
		$A(colors).each(function(n){
			Event.observe(n,"click", function(e){
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
							t.options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty_color");
						},
						cancel_callback  : function(v) {
							t.options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty_color");
						}
					};
					var colorpicker = new compColorPicker(opts);
					colorpicker.showColorPicker($("nc_wysiwyg_tableproperty_color"));
				};
				var top_el = $("nc_wysiwyg_tableproperty");
				var pos = Position.cumulativeOffset(top_el);
				var input_pos = Position.cumulativeOffset(self.previousSibling);
				var toggle_options = {
					id        : "nc_wysiwyg_tableproperty_color",
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
		});

		var input_colors = Element.getElementsByClassName(div, "nc_wysiwyg_tableproperty_color_input");
		$A(input_colors).each(function(n){
			Event.observe(n,"keyup", function(e){
				var self = Event.element(e);
				var c = self.value;
				if(c.match(/^#[0-9a-f]{6}/i)) {
					Element.setStyle(self.nextSibling.childNodes[0], {backgroundColor:c});
				}
			});
			Event.observe(n,"focus", function(e){
				t.options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty_color");
			});
		});

		//ok cancel button
		buttons = document.createElement('div');
		Element.addClassName(buttons,"nc_wysiwyg_tableproperty_btn");
		self.appendChild(buttons);
		buttons.innerHTML = '<input id="nc_wysiwyg_tableproperty_ok" name="ok" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['ok']+'" />' +
							'<input id="nc_wysiwyg_tableproperty_cancel" name="cancel" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['cancel']+'" />';

		Event.observe($("nc_wysiwyg_tableproperty_ok"),"click", function(e){
			// 決定
			// Activeのタブのみ更新。その他、タブまで更新してしまうと
			// 本来、更新したくないものまで更新されてしまうため
			var css = null, attr = null;
			var w_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_width"), w = w_el.value;
			var w_unit_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_width_unit"), w_unit = w_unit_el.value;
			var h_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_height"), h = h_el.value;
			var h_unit_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_height_unit"), h_unit = h_unit_el.value;
			var bgc_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_backgroundColor"), bgc = bgc_el.value;
			var c_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_color"), c = c_el.value;
			var ws_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_whiteSpace");
			var m_w_el, m_w, m_h_el, m_h, bc_el, cp_el, cp, cs_el, cs, summary_el, summary;
			var cell_p_el,cell_p;
			if(!c.match(/^#[0-9a-f]{6}/i))
				c = '';
			if(!bgc.match(/^#[0-9a-f]{6}/i))
				bgc = '';

			switch (t.active_tab) {
				case "table":
					m_w_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_margin_width"), m_w = m_w_el.value;
					m_h_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_margin_height"), m_h = m_h_el.value;
					bc_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_borderCollapse");
					cp_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_cellPadding"), cp = cp_el.value;
					cs_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_cellSpacing"), cs = cs_el.value;
					summary_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_summary"), summary = t.options.wysiwyg.htmlEncode(summary_el.value);
					css = {
						width           : parseInt(w) ? parseInt(w) + w_unit : "",
						height          : parseInt(h) ? parseInt(h) + h_unit : "",
						margin          : (m_w != '' || m_h != '') ? (parseInt(m_h) + 'px ' + parseInt(m_w) + 'px') : '',
						borderCollapse  : (bc_el.checked) ? 'collapse' : 'separate',
						backgroundColor : bgc,
						color           : c,
						whiteSpace      : (ws_el.checked) ? 'nowrap' : ''
					};
					attr = {
						cellPadding     : cp,
						cellSpacing     : cs,
						summary         : summary
					};
					// セル幅を均一に
					if($("nc_wysiwyg_tableproperty_cell_equality").checked)
						t.setUniformlySized(t.property[t.active_tab].sel_els[0]);
					else if(t.getUniformlySized(t.property[t.active_tab].sel_els[0])) {
						var tds = t.property[t.active_tab].sel_els[0].getElementsByTagName("td");
						for (var k = 0; k < tds.length; ++k) {
							var v = tds[k];
							Element.setStyle(v, {width : ''});
						}
					}
					// 表をセンタリングする
					if($("nc_wysiwyg_tableproperty_align_center").checked) {
						t.property[t.active_tab].sel_els[0].setAttribute('align','center', 0);
						if(m_h != '') {
							css['margin'] = parseInt(m_h) + 'px ' + 'auto';
						} else {
							css['margin'] = '0px ' + 'auto';
						}
					} else {
						t.property[t.active_tab].sel_els[0].removeAttribute('align', 0);
					}
					break;
				case "row":
				case "col":
				case "cell":
					cell_p_el = $("nc_wysiwyg_tableproperty_" + t.active_tab+ "_padding"), cell_p = cell_p_el.value;
					css = {
						width           : parseInt(w) ? parseInt(w) + w_unit : "",
						height          : parseInt(h) ? parseInt(h) + h_unit : "",
						padding         : (cell_p != '') ? (parseInt(cell_p) + 'px ' + parseInt(cell_p) + 'px') : '',
						backgroundColor : bgc,
						color           : c,
						whiteSpace      : (ws_el.checked) ? 'nowrap' : ''
					};
			}
			// 配置
			var positions = Element.getElementsByClassName($("tableproperty_panel_" + t.active_tab), "nc_wysiwyg_tableproperty_active");
			$A(positions).each(function(v){
				if(Element.hasClassName(v,"nc_wysiwyg_tableproperty_" + t.active_tab + "_left")) {
					css['textAlign'] = "left";
				} else if(Element.hasClassName(v,"nc_wysiwyg_tableproperty_" + t.active_tab + "_center")) {
					css['textAlign'] = "center";
				} else if(Element.hasClassName(v,"nc_wysiwyg_tableproperty_" + t.active_tab + "_right")) {
					css['textAlign'] = "right";
				} else if(Element.hasClassName(v,"nc_wysiwyg_tableproperty_" + t.active_tab + "_top")) {
					css['verticalAlign'] = "top";
				} else if(Element.hasClassName(v,"nc_wysiwyg_tableproperty_" + t.active_tab + "_middle")) {
					css['verticalAlign'] = "middle";
				} else if(Element.hasClassName(v,"nc_wysiwyg_tableproperty_" + t.active_tab + "_bottom")) {
					css['verticalAlign'] = "bottom";
				}
			});
			if(!css['textAlign'])
				css['textAlign'] = '';

			if(!css['verticalAlign']) {
				css['verticalAlign'] = '';
			}
			if(t.active_tab == "table") {
				var tds = t.options.table_pos['table_el'].getElementsByTagName("td");
				for (var k = 0; k < tds.length; ++k) {
					var v = tds[k];
					Element.setStyle(v, {verticalAlign:css['verticalAlign']});
					// tr,td 0pxの定義がnc2にはされているため対処
					if(attr['cellPadding']) {
						Element.setStyle(v, {padding:attr['cellPadding'] + "px"});
					} else {
						Element.setStyle(v, {padding:''});
					}
				}
			}
			for (var k = 0; k < t.property[t.active_tab].sel_els.length; ++k) {
				var v = t.property[t.active_tab].sel_els[k];
				if(css != null) {
					Element.setStyle(v, css);
					if(t.active_tab == "row") {
						for (var j = 0; j < v.childNodes.length; ++j) {
							var td_v = v.childNodes[j];
							Element.setStyle(td_v, {padding : css['padding']});
						}
					}
				}
				if(attr != null) {
					for (var attr_k in attr ) {
						v.setAttribute(attr_k,attr[attr_k],0);
					}
				}
			}

			t.options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty");
			Event.stop(e);
	        return false;
		});
		Event.observe($("nc_wysiwyg_tableproperty_cancel"),"click", function(e){
			t.options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty");
			Event.stop(e);
	        return false;
		});

		return;

		function _appendTab(tabs, name, title, active) {
			var tab = document.createElement('li');
			tab.innerHTML = '<a class="nc_wysiwyg_tableproperty_tab" href="javascript:;" ><span>'+ title +'</span></a>';
			tabs.appendChild(tab);

			if (active == true) {

				var active_a = tab.childNodes[0];
				Element.setStyle(active_a, {backgroundColor:"#ffffff"});
				// focus移動
				setTimeout(function() { active_a.focus(); }, 100);
			}

			var atag = Element.getChildElementByClassName(tab, "nc_wysiwyg_tableproperty_tab");
			Event.observe(atag,"click", function(e){
				var buf_tabs = Element.getElementsByClassName(tabs, "nc_wysiwyg_tableproperty_tab");
				$A(buf_tabs).each(function(n){
					Element.setStyle(n, {backgroundColor:""});
				});
				var act_a_el = Element.getParentElementByClassName(Event.element(e),"nc_wysiwyg_tableproperty_tab");
				Element.setStyle(act_a_el, {backgroundColor:"#ffffff"});
				// set active tab
				t.active_tab = name;
				var panels = Element.getElementsByClassName(div, "tableproperty_panel");
				$A(panels).each(function(n){
					Element.setStyle(n, {display:"none"});
				});
				var panel = $('tableproperty_panel_'+ name);
				if(Element.getStyle(panel, "display") == "none")
					Element.setStyle(panel, {display:"block"});
				else
					Element.setStyle(panel, {display:"none"});
			});
			return ;
		}

		function _createContent(type, title) {
			var content,pos, context = compTablePropertyLang['panel'];

			content = document.createElement('ul');
			Element.addClassName(content,"tableproperty_panel");
			content.id = "tableproperty_panel_"+ type;
			Element.setStyle(content, {display:"none"});

			if (type == 'table') {
				_appendTextAlign(type, content, t.property.table.textAlign, t.property.table.verticalAlign);
				_appendLi(content, '<dl><dt style="float: left;">'+ context['width'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_width" name="table_width" class="tableproperty_text align-right" value="'+ t.property.table.width +'" /><select id="nc_wysiwyg_tableproperty_table_width_unit" name="table_width_unit"><option value="px" '+ ((t.property.table.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.table.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['height'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_height" name="table_height" class="tableproperty_text align-right" value="'+ t.property.table.height +'" /><select id="nc_wysiwyg_tableproperty_table_height_unit" name="table_height_unit"><option value="px" '+ ((t.property.table.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.table.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['margin_width'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_margin_width" name="table_margin_width" class="tableproperty_text align-right" value="'+ t.property.table.marginWidth +'" />px</dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['margin_height'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_margin_height" name="table_margin_height" class="tableproperty_text align-right" value="'+ t.property.table.marginHeight +'" />px</dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['cellpadding'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_cellPadding" name="table_cellPadding" class="tableproperty_text align-right" value="'+ t.property.table.cellPadding +'"/>px</dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['cellspacing'] +'</dt><dd>'+ context['separator'] +'<input id="nc_wysiwyg_tableproperty_table_cellSpacing" type="text" id="nc_wysiwyg_tableproperty_table_cellSpacing" name="table_cellSpacing" class="tableproperty_text align-right" value="'+ t.property.table.cellSpacing +'"'+ ((t.property.table.borderCollapse == 'collapse') ? ' disabled="disabled"' : '') +' />px</dd></dl>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_table_borderCollapse"><input type="checkbox" id="nc_wysiwyg_tableproperty_table_borderCollapse" name="table_borderCollapse" '+ ((t.property.table.borderCollapse == 'collapse') ? 'checked="checked"' : '') +' />'+ context['border_pile'] +'</label>');
				var li = document.createElement('li');
				Element.addClassName(li,"nc_wysiwyg_tableproperty_linebreak");
				Element.addClassName(li,"float-clear");
				content.appendChild(li);
				_appendLi(content, '<dl><dt>'+ context['bgcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_backgroundColor" name="table_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.table.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.table.backgroundColor != '') ? ' style="background-color:' + t.property.table.backgroundColor + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['fontcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_table_color" name="table_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.table.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.table.color != '') ? ' style="background-color:' + t.property.table.color + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_table_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_table_whiteSpace" name="table_whiteSpace" '+ ((t.property.table.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ context['nowrap'] +'</label>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_cell_equality"><input type="checkbox" id="nc_wysiwyg_tableproperty_cell_equality" name="cell_equality" '+ ((t.property.table.uniformlySized == true) ? 'checked="checked"' : '') +' />'+ context['cell_equality'] +'</label>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_align_center"><input type="checkbox" id="nc_wysiwyg_tableproperty_align_center" name="align_center" '+ ((t.property.table.align == 'center') ? 'checked="checked"' : '') +' />'+ context['align_center'] +'</label>');
				_appendBorderBtn();
				_appendLi(content, '<dl><dt>'+ context['summary'] +'</dt><span class="nc_wysiwyg_tableproperty_summary_sep">'+ context['separator'] +'</span><dd><textarea id="nc_wysiwyg_tableproperty_table_summary" name="table_summary" style="width:250px;height:50px;" >'+ t.property.table.summary +'</textarea></dd></dl>');
			} else if (type == 'row') {
				_appendLi(content, '<dl><dt>' + title + '</dt><dd>' + context['separator'] + t.property.row.pos +'</dd></dl>', "nc_wysiwyg_tableproperty_title");
				_appendTextAlign(type, content, t.property.row.textAlign, t.property.row.verticalAlign);
				_appendLi(content, '<dl><dt style="float: left;">'+ context['width'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_row_width" name="row_width" class="tableproperty_text align-right" value="'+ t.property.row.width +'" /><select id="nc_wysiwyg_tableproperty_row_width_unit" name="row_width_unit"><option value="px" '+ ((t.property.row.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.row.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['height'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_row_height" name="row_height" class="tableproperty_text align-right" value="'+ t.property.row.height +'" /><select id="nc_wysiwyg_tableproperty_row_height_unit" name="row_height_unit"><option value="px" '+ ((t.property.row.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.row.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['cellpadding'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_row_padding" name="row_padding" class="tableproperty_text align-right" value="'+ t.property.row.cellPadding +'"/>px</dd></dl>');
				var li = document.createElement('li');
				Element.addClassName(li,"nc_wysiwyg_tableproperty_linebreak");
				Element.addClassName(li,"float-clear");
				content.appendChild(li);
				_appendLi(content, '<dl><dt>'+ context['bgcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_row_backgroundColor" name="row_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.row.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.row.backgroundColor != '') ? ' style="background-color:' + t.property.row.backgroundColor + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['fontcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_row_color" name="row_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.row.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.row.color != '') ? ' style="background-color:' + t.property.row.color + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_row_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_row_whiteSpace" name="row_whiteSpace" '+ ((t.property.row.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ context['nowrap'] +'</label>');
				_appendBorderBtn();
			} else if (type == 'col') {
				_appendLi(content, '<dl><dt>' + title + '</dt><dd>' + context['separator'] + t.property.col.pos +'</dd></dl>', "nc_wysiwyg_tableproperty_title");
				_appendTextAlign(type, content, t.property.col.textAlign, t.property.col.verticalAlign);
				_appendLi(content, '<dl><dt style="float: left;">'+ context['width'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_col_width" name="col_width" class="tableproperty_text align-right" value="'+ t.property.col.width +'" /><select id="nc_wysiwyg_tableproperty_col_width_unit" name="col_width_unit"><option value="px" '+ ((t.property.col.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.col.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['height'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_col_height" name="col_height" class="tableproperty_text align-right" value="'+ t.property.col.height +'" /><select id="nc_wysiwyg_tableproperty_col_height_unit" name="col_height_unit"><option value="px" '+ ((t.property.col.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.col.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['cellpadding'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_col_padding" name="col_padding" class="tableproperty_text align-right" value="'+ t.property.col.cellPadding +'"/>px</dd></dl>');
				var li = document.createElement('li');
				Element.addClassName(li,"nc_wysiwyg_tableproperty_linebreak");
				Element.addClassName(li,"float-clear");
				content.appendChild(li);
				_appendLi(content, '<dl><dt>'+ context['bgcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_col_backgroundColor" name="col_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.col.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.col.backgroundColor != '') ? ' style="background-color:' + t.property.col.backgroundColor + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['fontcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_col_color" name="col_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.col.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.col.color != '') ? ' style="background-color:' + t.property.col.color + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_col_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_col_whiteSpace" name="col_whiteSpace" '+ ((t.property.col.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ context['nowrap'] +'</label>');
				_appendBorderBtn();
			} else if (type == 'cell') {
				_appendLi(content, '<dl><dt>' + title + '</dt><dd>' + context['separator'] + t.property.cell.pos +'</dd></dl>', "nc_wysiwyg_tableproperty_title");
				_appendTextAlign(type, content, t.property.cell.textAlign, t.property.cell.verticalAlign);
				_appendLi(content, '<dl><dt style="float: left;">'+ context['width'] +'</dt><dd class="nc_wysiwyg_tableproperty_title_v">'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_cell_width" name="cell_width" class="tableproperty_text align-right" value="'+ t.property.cell.width +'" /><select id="nc_wysiwyg_tableproperty_cell_width_unit" name="cell_width_unit"><option value="px" '+ ((t.property.cell.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.cell.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['height'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_cell_height" name="cell_height" class="tableproperty_text align-right" value="'+ t.property.cell.height +'" /><select id="nc_wysiwyg_tableproperty_cell_height_unit" name="cell_height_unit"><option value="px" '+ ((t.property.cell.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((t.property.cell.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['cellpadding'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_cell_padding" name="cell_padding" class="tableproperty_text align-right" value="'+ t.property.cell.cellPadding +'"/>px</dd></dl>');
				var li = document.createElement('li');
				Element.addClassName(li,"nc_wysiwyg_tableproperty_linebreak");
				Element.addClassName(li,"float-clear");
				content.appendChild(li);
				_appendLi(content, '<dl><dt>'+ context['bgcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_cell_backgroundColor" name="cell_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.cell.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.cell.backgroundColor != '') ? ' style="background-color:' + t.property.cell.backgroundColor + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<dl><dt>'+ context['fontcolor'] +'</dt><dd>'+ context['separator'] +'<input type="text" id="nc_wysiwyg_tableproperty_cell_color" name="cell_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ t.property.cell.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((t.property.cell.color != '') ? ' style="background-color:' + t.property.cell.color + '"' : '') + ' src="' + _nc_core_base_url + '/images/comp/textarea/dialog/sel_color.gif" title="' + context['sel_color'] + '" alt="' + context['sel_color'] + '" /></a></dd></dl>');
				_appendLi(content, '<label for="nc_wysiwyg_tableproperty_cell_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_cell_whiteSpace" name="cell_whiteSpace" '+ ((t.property.cell.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ context['nowrap'] +'</label>');
				_appendBorderBtn();
			}
			return content;

			function _appendLi(content, html, className) {
				var li = document.createElement('li');
				li.innerHTML = html;
				if(className != undefined && className != "")
					Element.addClassName(li,className);
				content.appendChild(li);
			}

			function _appendBorderBtn() {
				var wysiwyg = t.options['wysiwyg'];
				var li = document.createElement('li');
				Element.addClassName(li,"nc_wysiwyg_tableproperty_border");
				content.appendChild(li);
				li.innerHTML = '<input type="button" value="'+ context['border'] +'" />';

				Event.observe(li.getElementsByTagName("input")[0],"click", function(e){
					var callback = function() {
										var opts = {
											wysiwyg    : wysiwyg,
											active_tab : t.active_tab,
											table_el   : t.options.table_pos['table_el'],
											pos_title  : t.property[t.active_tab]['pos'],
											sel_els    : t.property[t.active_tab]['sel_els']
										}
										var tableborder = new compTableBorder(opts);
										tableborder.showTableBorder($("nc_wysiwyg_tableborder"));
					}
					var toggle_opts = {
						id  : "nc_wysiwyg_tableborder",
						css : ['comp_tableborder.css'],
						js : ['comp_tableborder'],
						jsname : ['window.compTableBorder'],
						callback : callback
					};
					wysiwyg.toggleDialog(self, toggle_opts);
				});
			}

			function _appendTextAlign(type, content, textAlign, verticalAlign){
				var lang = compTablePropertyLang['panel'];
				var li = document.createElement('li');
				Element.addClassName(li,"nc_wysiwyg_tableproperty_position");
				li.innerHTML = '<fieldset><legend>'+context['text_align']+'</legend>' +
									'<ul class="tableproperty_align">'+
										'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_left' + ((textAlign == "left") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-left.gif" title="' + lang['text_align_left'] + '" alt="' + lang['text_align_left'] + '" /></a></li>'+
										'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_center' + ((textAlign == "center") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-center.gif" title="' + lang['text_align_center'] + '" alt="' + lang['text_align_center'] + '" /></a></li>'+
										'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_right' + ((textAlign == "right") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-right.gif" title="' + lang['text_align_right'] + '" alt="' + lang['text_align_right'] + '" /></a></li>'+
									'</ul></fieldset>' +
									'<fieldset><legend>'+context['vertical_align']+'</legend>' +
									'<ul class="tableproperty_align">' +
										'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_top' + ((verticalAlign == "top") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-top.gif" title="' + lang['vertical_align_top'] + '" alt="' + lang['vertical_align_top'] + '" /></a></li>'+
										'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_middle' + ((verticalAlign == "middle") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-middle.gif" title="' + lang['vertical_align_middle'] + '" alt="' + lang['vertical_align_middle'] + '" /></a></li>'+
										'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_bottom' + ((verticalAlign == "bottom") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="' + _nc_core_base_url + '/images/comp/textarea/dialog/table-bottom.gif" title="' + lang['vertical_align_bottom'] + '" alt="' + lang['vertical_align_bottom'] + '" /></a></li>'+
								'</ul></fieldset>';
				content.appendChild(li);

				// Add Event
				var as = li.getElementsByTagName("a");
				for (var k = 0; k < as.length; ++k) {
					var v = as[k];
					Event.observe(v,"click", function(e){
						var a = Event.element(e);
						if(a.nodeName.toLowerCase() != 'a')
							a = a.parentNode;

						if(Element.hasClassName(a,"nc_wysiwyg_tableproperty_active"))
							Element.removeClassName(a,"nc_wysiwyg_tableproperty_active");
						else {
							var u_align = a.parentNode.parentNode;
							var buf_active = Element.getElementsByClassName(u_align, "nc_wysiwyg_tableproperty_active");
							$A(buf_active).each(function(n){
								Element.removeClassName(n,"nc_wysiwyg_tableproperty_active");
							});
							Element.addClassName(a,"nc_wysiwyg_tableproperty_active");
						}
						Event.stop(e);
						return false;
					});
				}

			}
		}
	}
}