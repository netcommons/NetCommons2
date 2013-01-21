/*
 * NC InsertVideo 0.0.0.1
 * @param options hash
 * 					callback	: fanction (default : null)
 */
var compInsertVideo = Class.create();
compInsertVideo.prototype = {
	options           : {},

	initialize : function(options)
	{
		var t = this;
		t.options = $H({
						callback        : null
	        		}).merge($H(options));
		return t;
	},

	showInsertVideo : function(self)
	{
		var t = this,html,ok,cancel;
		var div,buttons,px,msg;

		if (_nc_allow_video == 2)
			msg = compInsertVideoLang['mod_desc'];
		else
			msg = compInsertVideoLang['desc'];

		self.innerHTML = '<div class="nc_wysiwyg_insertvideo_dialog_title">'+ compInsertVideoLang['dialog_title'] +'</div>' +
						'<div><div id="nc_wysiwyg_insertvideo_video_msg">'+ msg +'</div>'+
						'<div><textarea id="nc_wysiwyg_insertvideo_f_video_el" cols="36" rows="4"></textarea></div>' +
						'<div class="nc_wysiwyg_insertvideo_btn">'+
							'<input id="nc_wysiwyg_insertvideo_ok" name="insertvideo_ok" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['ok']+'" />'+
							'<input id="nc_wysiwyg_insertvideo_cancel" name="insertvideo_cancel" type="button" class="nc_wysiwyg_btn" value="'+compTextareaLang['dialog']['cancel']+'" />'+
						'</div>'+
						'</div>';

		ok = $("nc_wysiwyg_insertvideo_ok");
		cancel = $("nc_wysiwyg_insertvideo_cancel");

		//各ブラウザ毎にwidthの長さを変える
		px = $('nc_wysiwyg_insertvideo_f_video_el').clientWidth;
		Element.setStyle($('nc_wysiwyg_insertvideo_video_msg'), {width : px + "px"});

		Event.observe(ok,"click", function (e) {
			html = $('nc_wysiwyg_insertvideo_f_video_el').value;
			if (t.options.callback && t.inputChecked(html))
				t.options.callback(html);
		});

		Event.observe(cancel,"click", function (e) {
			$(self).remove();
		});

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { $('nc_wysiwyg_insertvideo_f_video_el').focus(); }, 100);
	},

	inputChecked : function(html) {
		if (!html.match(/^\s*<(iframe|object|embed).*>(.|\s)*<\/(iframe|object|embed)>\s*$/i)) {
			alert(compInsertVideoLang['error_mes']);
			return false;
		} else {
			return true;
		}
	}
}