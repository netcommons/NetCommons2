/*
 * NC TableMerge 0.0.0.1
 * @param options    hash
 *                      td              : td element
 * 	          　　　  	callback        : function 決定時のcallback関数(default null)
 * 	            	　　cancel_callback : function キャンセル時のcallback関数(default null)
 */
var compTableMerge = Class.create();
compTableMerge.prototype = {
	options           : {},
	initialize : function(options) 
	{
		var t = this;
		t.options = $H({td              : null,
						callback        : null,
	        			cancel_callback : null
	        		}).merge($H(options));
		return t;
	},
	showTableMerge : function(el) 
	{
		var t = this, merge, ul, buttons, ok,cancel;
		el.innerHTML = '<div class="nc_wysiwyg_tablemerge_title">'+ compTableMergeLang['cell'] +'&nbsp;'+ compTableMergeLang['separator'] +'&nbsp;'+ 
			(t.options.td.parentNode.rowIndex + 1) + compTableMergeLang['cell_sep'] + (t.options.td.cellIndex + 1) + '</div>' +
			'<div class="nc_wysiwyg_tablemerge_title align-center">' + compTableMergeLang['merge'] + '</div>';
		
		ul = document.createElement('ul');
		Element.addClassName(ul, "nc_wysiwyg_tablemerge");
		el.appendChild(ul);
		ul.innerHTML = '<li><dl><dt>'+ compTableMergeLang['col'] +'</dt><dd>'+ compTableMergeLang['separator'] +'<input id="nc_wysiwyg_tablemerge_col" class="align-right" type="text" name="col" value="1" /></dd></dl></li>' +
						'<li><dl><dt>'+ compTableMergeLang['row'] +'</dt><dd>'+ compTableMergeLang['separator'] +'<input id="nc_wysiwyg_tablemerge_row" class="align-right" type="text" name="row" value="1" /></dd></dl></li>';

		//ok cancel button
		buttons = document.createElement('div');
		Element.addClassName(buttons, "nc_wysiwyg_tablemerge_btn");
		el.appendChild(buttons);
		
		buttons.innerHTML = '<input id="nc_wysiwyg_tablemerge_ok" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['ok']+'" />'+
						'<input id="nc_wysiwyg_tablemerge_cancel" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['cancel']+'" />';
		
		ok = $("nc_wysiwyg_tablemerge_ok");
		cancel = $("nc_wysiwyg_tablemerge_cancel");;
		Event.observe(ok,"click", function (e) {
			var col = parseInt($("nc_wysiwyg_tablemerge_col").value);
			var row = parseInt($("nc_wysiwyg_tablemerge_row").value);
			col = (col > 0) ? col : 0;
			row = (row > 0) ? row : 0;
			if(t.options.callback)
				if(!t.options.callback.call(el, col, row))
					return false;
			Event.stop(e);
	        return false;
		});
		Event.observe(cancel,"click", function (e) {
			if(t.options.cancel_callback)
				if(!t.options.cancel_callback.apply(el))
					return false;
			Event.stop(e);
	        return false;
		});

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { $("nc_wysiwyg_tablemerge_col").focus(); }, 100);
	}
}