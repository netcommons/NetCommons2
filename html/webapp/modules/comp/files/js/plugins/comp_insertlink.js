/*
 * NC InsertLink 0.0.0.1
 * @param options hash
 *					url        : string   デフォルトのURLテキストに表示する値(default 'http://')
 *					title      : string   デフォルトのtitleテキストに表示する値(default '')
 *					target     : string   デフォルトのtargetテキストに表示する値(default '') 
 *										  指定なし ：'' or 新規ウィドウ : '_blank' or その他 : '_other'
 *					callback   : function リンク挿入時のcallback関数(default null)
 *										  callback内：args hash
 *                                                            @param url
 *                                                            @param title
 *                                                            @param target
 *                                                            @return boolean
 *                  cancel_callback : function　キャンセルボタン押下時のcallback関数(default null)
 *                                                            @return boolean
 */
var compInsertLink = Class.create();
compInsertLink.prototype = {
	options           : {},

	initialize : function(options) 
	{
		var t = this;
		t.options = $H({url             : 'http://',
			            title           : '',
			            target          : '',				// '' or '_blank' or '_other'
			            callback        : null,
			            cancel_callback : null,
			            html            : 
			            	'<div class="nc_wysiwyg_insertlink_title">'+ compInsertLinkLang['dialog_title'] +'</div>' +
						  		'<ul class="nc_wysiwyg_insertlink">'+
						 			'<li><dl><dt><label for="nc_wysiwyg_insertlink_url">'+ compInsertLinkLang['url'] +'<span class="require">*</span></label></dt><dd><input id="nc_wysiwyg_insertlink_url" name="url" class="nc_wysiwyg_insertlink_input" type="text" /></dd></dl></li>' +
						  			'<li><dl><dt><label for="nc_wysiwyg_insertlink_title">'+ compInsertLinkLang['title'] +'</label></dt><dd><input id="nc_wysiwyg_insertlink_title" name="url_title" class="nc_wysiwyg_insertlink_input" type="text" /></dd></dl></li>' +
						  			'<li><dl><dt><label for="nc_wysiwyg_insertlink_target">'+ compInsertLinkLang['target'] +'</label></dt><dd><select id="nc_wysiwyg_insertlink_target" class="nc_wysiwyg_insertlink_select" name="url_target"><option value="">'+ compInsertLinkLang['target_none'] +'</option><option value="_blank">'+ compInsertLinkLang['target_blank'] +'</option><option value="_other">'+ compInsertLinkLang['target_other'] +'</option></select>&nbsp;<input id="nc_wysiwyg_insertlink_other" name="insertlink_other" size="10" style="visibility: hidden;" type="text" /></dd></dl></li>' +
						  		'</ul><div class="nc_wysiwyg_insertlink_btn"><input id="nc_wysiwyg_insertlink_ok" class="nc_wysiwyg_btn" name="insertlink_ok" type="button" value="' + compInsertLinkLang['ok'] + '" />' +
						  	'&nbsp;<input id="nc_wysiwyg_insertlink_cancel" name="insertlink_cancel" class="nc_wysiwyg_btn" type="button" value="' + compTextareaLang['dialog']['cancel'] + '" /></div>'
	        			}).merge($H(options));
		return t;
	},

	showInsertLink : function(self) 
	{
		var t = this,u,ti,ta,o;
		
		// create form
		self.innerHTML = t.options.html;
		
		// el取得
		u = $("nc_wysiwyg_insertlink_url");
		ti = $("nc_wysiwyg_insertlink_title");
		ta = $("nc_wysiwyg_insertlink_target");
		o = $("nc_wysiwyg_insertlink_other");
		
		// 初期値セット
		u.value = t.options.url;
		ti.value = t.options.title;

		if(t.options.target != '_blank' && t.options.target != '' && t.options.target != undefined && t.options.target != null) {
			ta.value = '_other';
			Element.setStyle(o, {visibility:'visible'});
			o.value = t.options.target;
		} else
			ta.value = t.options.target;
			
		// イベント
		Event.observe($("nc_wysiwyg_insertlink_target"),"change", function (e) {
			return t.changeTarget();
		});

		Event.observe($("nc_wysiwyg_insertlink_ok"),"click", function (e) {
			// OK
			return t.clickLink(e);
		});

		Event.observe($("nc_wysiwyg_insertlink_cancel"),"click", function (e) {
			// cancel
			return t.clickCancel(e);
		});
		
		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { u.focus(); }, 100);
	},
	
	clickLink : function(e) {
		var t = this, arg = {},u,ti;
		u = $("nc_wysiwyg_insertlink_url");
		ti = $("nc_wysiwyg_insertlink_title");
		if(u.value == '' || u.value == 'http://') {
			// エラー(未入力)
			alert(compInsertLinkLang['err_url']);
			u.focus();
			return false;
		}
		
		arg['href'] = u.value;
		if(ti.value != '') arg['title'] = ti.value;
		var ta_val = $("nc_wysiwyg_insertlink_target").value;
		if(ta_val != '')
			arg['target'] = (ta_val != '_other') ? ta_val : $("nc_wysiwyg_insertlink_other").value; 
										        				
		if(t.options.callback)
			if(!t.options.callback.apply(self, [arg]))
				return false;
		Event.stop(e);
        return false;
	},
	
	clickCancel : function(e) {
		var t = this;
		if(t.options.cancel_callback)
			if(!t.options.cancel_callback.apply(self))
				return false;
		Event.stop(e);
        return false;
	},
	
	changeTarget : function() {
		var t = this, o;
		o = $("nc_wysiwyg_insertlink_other");
		// ターゲット（その他の場合、テキスト表示）
		Element.setStyle(o, {visibility:($("nc_wysiwyg_insertlink_target").value == '_other' ? 'visible' : 'hidden')});
		if($("nc_wysiwyg_insertlink_target").value == '_other') o.focus();
	}
}