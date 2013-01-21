/*
 * NC mimeTex 0.0.0.1
 * @param options hash
 * 					url			: string texへの変更のためのphpファイルのアドレス(default : _nc_base_url + _nc_index_file_name)
 * 					data		: nc2系のための項目('?action=common_tex_main')
 * 					callback	: function (default : null)
 *                  text        : string textに渡す初期値
 */
var compMimeTex = Class.create();
compMimeTex.prototype = {
	options           : {},

	initialize : function(options)
	{
		var t = this;
		t.options = $H({url          : _nc_base_url + _nc_index_file_name,
						data		 : 'action=common_tex_main',
						callback	 : null,
						text         : ''
	        			}).merge($H(options));
		return t;
	},

	showMimeTex : function(self)
	{
		var t = this, tex,upload;
		if(t.options.text != '') {
			t.options.text = this.decodeURI(t.options.text);
		}

		self.innerHTML = '<div class="nc_wysiwyg_mimetex_dialog_title">'+ compMimeTexLang['dialog_title'] +'</div>'+
		  '<div class="nc_wysiwyg_mimetex_outer"><div>'+ compMimeTexLang['error_mes'] +'</div>'+
		  '<input class="nc_wysiwyg_mimetex_input" type="text" name="mimetex" value="'+t.options.text+'" />'+
		  '<input class="nc_wysiwyg_mimetex_preview_btn" type="button" value="'+ compTextareaLang['preview'] +'" name="mimetex_preview" style="margin-left: 5px;" /></div>'+
		  '<div class="nc_wysiwyg_mimetex_preview_title">'+ compTextareaLang['preview'] +'</div><div class="nc_wysiwyg_mimetex_preview"></div>' +
		  '<div class="nc_wysiwyg_mimetex_btn">'+
		  '<input class="nc_wysiwyg_mimetex_ok nc_wysiwyg_btn" name="mimetex_ok" type="button" value="'+compTextareaLang['dialog']['ok']+'" />'+
		  '<input class="nc_wysiwyg_mimetex_cancel nc_wysiwyg_btn" name="mimetex_cancel" type="button" value="'+compTextareaLang['dialog']['cancel']+'" />'+
		  '</div>';

		//ボタンの追加
		preview = Element.getChildElementByClassName(self, "nc_wysiwyg_mimetex_preview_btn");
		ok = Element.getChildElementByClassName(self, "nc_wysiwyg_mimetex_ok");
		cancel = Element.getChildElementByClassName(self, "nc_wysiwyg_mimetex_cancel");
		input = Element.getChildElementByClassName(self, "nc_wysiwyg_mimetex_input");

		//クリック時のイベント
		Event.observe(preview,"click", function (e) {
			if (t.inputChecked(input)) {
				var texurl = t.getTexURL(input.value);
				texurl = texurl.replace(/\'/g, "\\'");
				Element.setStyle(Element.getChildElementByClassName(self, "nc_wysiwyg_mimetex_preview"), {background : "#ffffff url('" + texurl + "') no-repeat"});
			}
		});

		Event.observe(ok,"click", function (e) {
			if (t.inputChecked(input)) {
				var texurl = t.getTexURL(input.value);
				var teximg = '<img class="tex" alt="'+ input.value +'" src="'+ texurl +'" />';
				if(t.options['callback'])
					t.options['callback'](teximg);
			}
		});

		Event.observe(cancel,"click", function (e) {
			Element.remove($('nc_wysiwyg_dialog'));
		});

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { input.focus(); }, 100);
		if(t.options.text != '') {
			preview.click();
		}
	},

	//入力チェックする内容は増えても良いように関数化
	inputChecked : function(input) {
		if (!input.value) {
			alert(compMimeTexLang['error_mes']);
		} else {
			return true;
		}
		return false;
	},

	//指定した文字列をTex変換するためのURLに変換
	getTexURL : function(uri) {
		var t =this, data = t.options.data == '' ? t.options.data : t.options.data + "&";
		return t.options.url + "?"+ data +"c=" + t.encodeURI(uri);
	},

	//文字列をURLエンコードする
	encodeURI : function(uri) {
		uri = encodeURIComponent(uri).replace(/%C2%A5/g,"%5C").replace(/%/g, "%_");
		return uri;
	},
	decodeURI : function(uri) {
		uri = uri.replace(/%5C/g,"%C2%A5").replace(/%_/g, "%");
		uri = decodeURIComponent(uri);
		return uri;
	}
}