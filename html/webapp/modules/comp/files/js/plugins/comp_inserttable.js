/*
 * NC InsertTable 0.0.0.1
 * @param e event object
 * @param options hash
 * 					row				: inserttable用のtableの行数
 * 					col				: inserttable用のtableの列数
 * 					border			: ボーダーの太さ
 * 					cellspacing		: テーブルの行間の長さ
 * 					cellpadding		: セル全体のpadding
 * 					tableStyle		: table用のスタイル
 * 					tdStyle			: td用のスタイル
 * 
 */
 var compInsertTable = Class.create();
compInsertTable.prototype = {
	options           : {},
	table             : null,

	initialize : function(options) 
	{
		var t = this;
		t.options = $H({'callback'	    : null,
						'row'			: 5,
						'col'			: 5,
						'border'		: '1',
						'cellspacing'	: '0',
						'cellpadding'	: '0',
						'tableStyle'	: {'border' : '1px solid rgb(0, 0, 0)', 'margin' : '5px', 'width' : '200px', 'border-collapse' : 'collapse'},
						'tdStyle'		: {'border' : '1px solid rgb(0, 0, 0)'}
	        			}).merge($H(options));
		return t;
	},
	showInsertTable : function(el) 
	{
		var t = this, options = t.options;
		el.innerHTML = '<div class="insertTable_title" >' + compInsertTableLang['dialog_title'] + '</div>'+
				 							'<div id="nc_wysiwyg_inserttable_table"></div>'+
				 							'<div id="table_size" class="align-center" > 0x0 </div>';
		
		var table_str = '<table class="insertTable_dialog" >';
		//var table = Element.getChildElementByClassName(el, "insertTable_dialog");
		//t.table = table;
		
		for (i = 1; i <= options['row']; i++) {
			table_str += '<tr id="row_'+ i +'">';
			
			for (y = 1;y <= options['col']; y++) {
				table_str += '<td id="td_'+ i + '_' + y +'"></td>'
			}
			table_str += '</tr>';
		}
		table_str += '</table>';
		$("nc_wysiwyg_inserttable_table").innerHTML = table_str;
		var tds = $("nc_wysiwyg_inserttable_table").getElementsByTagName("td");
		for (var k = 0; k < tds.length; ++k) {
			var td = tds[k];
			Event.observe(td,"mouseover", function (e) {t.onHoverEvent(Event.element(e));});
			Event.observe(td,"click", function (e) {t.onClickEvent(Event.element(e));});
		}
		t.table = $("nc_wysiwyg_inserttable_table").childNodes[0];
		if(browser.isOpera) {
			Element.setStyle(t.table, {display:'none'});
			setTimeout(function() { Element.setStyle(t.table, {display:''}); }, 100);
		}
	},
	
	onHoverEvent : function(el)
	{
		var t = this, id_arr = el.id.split("_");
		var row = id_arr[1];
		var col = id_arr[2];
		var tdList = t.table.getElementsByTagName("td");
		for (i = 0; i < tdList.length; i++){
			id_arr = tdList[i].id.split("_");
			var now_row = id_arr[1];
			var now_col = id_arr[2];
			if(now_row <= row && now_col <= col) {
				Element.setStyle(tdList[i], {backgroundColor:'#e6e6e6'});
			} else {
				Element.setStyle(tdList[i], {backgroundColor:''});
			}
		}
		$('table_size').innerHTML = '<div id="table_size" class="align-center" > '+ row + 'x' + col +' </div>';
	},
		
	onClickEvent : function (el) 
	{
		var t = this, id_arr = el.id.split("_");
		var row = id_arr[1];
		var col = id_arr[2];
		
		t.options['row'] = row;
		t.options['col'] = col;
		t.options['tdStyle']['width'] = Math.floor(100/col) +'%';
		var html = t.createTable();
		if(t.options.callback)
			t.options.callback.apply(el, [html]);
	},
		
	createTable : function() 
	{
		var t = this;	
		html = "";
		html += '<table summary="" cellspacing="'+ t.options['cellspacing'] +'" cellpadding="'+ t.options['cellpadding'] +'" style="'+ t.createStyle(t.options['tableStyle']) +'" >';
		for (i = 1; i <= t.options['row']; i++) {
			html += '<tr>';
			for (y = 1; y <= t.options['col']; y++) {
				html += '<td style="'+ t.createStyle(t.options['tdStyle']) +'">&nbsp;</td>';
			}
			html += '</tr>';
		}
		html += '</table><br />';
		return html;
	},
		
	createStyle : function(optStyle) 
	{
		var style = "";
		
		for (k in optStyle) {
			style += k +": "+ optStyle[k] +"; ";
		}
		
		return style;
	}
}