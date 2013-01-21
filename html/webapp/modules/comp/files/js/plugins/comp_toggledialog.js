/*
 * NC toggledialog 0.0.0.1
 * @author      Ryuji Masukawa
 *
 * ポップアップダイアログ表示・非表示
 * (@param self          : object)	: $()として呼ぶ場合、使用しない
 * @param options
 *          id        : dialog id名称      id default this.dialog_id (nc_wysiwyg_dialog)
 *          el        : string             ダイアログの中身(el or content)
 *          content   : object element     ダイアログの中身(el or content)
 *          css       : array css src
 *          js        : array javascript src
 *          jsname    : array javascript valiable name js　jsの指定と対応して指定すること（jsを指定しているならば、jsnameは必須）
 *          pos_base  : 位置指定の基準element default　document.body
 *          style     : hash トップエレメントのスタイルをhashで指定 {left : 100, top : 100} 等
 *                      width,height : max    pos_baseの広さに設定
 *                      left : left       : pos_baseの内のleft
 *                             right      : pos_baseの内のright
 *                             outleft    : pos_baseの外側のleft
 *                             outright   : pos_baseの外側のright
 *                             center     : pos_baseの中央
 *                      top  : top        : pos_baseの内のtop
 *                             bottom     : pos_baseの内のbottom
 *                　           outtop     : pos_baseの外側のtop
 *							   outbottom  : pos_baseの外側のbottom
 *                             center     : pos_baseの中央
 *          callback  : array functions
 */
var compToggleDialog = Class.create();
compToggleDialog.prototype = {
	dialogs    : {},
	object     : null,
    options    : {},
    className  : 'comp_toggledialog',
    js_path         : null,
	css_path        : null,

	initialize: function(options)
	{
		var t = this;
       	t.dialogs = {};
       	t.object = t;
       	t.options = t.initOpt(options);
       	t.js_path  = _nc_core_base_url + _nc_index_file_name + "?action=common_download_js&add_block_flag=1&dir_name=";
		t.css_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&header=0&dir_name=/comp/plugins/";

       	commonCls.addLink(t.css_path + "comp_toggledialog.css"+"&vs="+_nc_css_vs);

       	return t;
	},

	initOpt : function(options)
	{
		var t = this;
		var css = [], js = [], jsname = [], callback = [];

		if ( options ) {
			if ( options.css ) {
				css = options.css;
				delete options.css;
			}
			var js = [];
			if ( options.js ) {
				js = options.js;
				delete options.js;
			}
			var jsname = [];
			if ( options.jsname ) {
				jsname = options.jsname;
				delete options.jsname;
			}
		}
	   	var options = $H({
	   		id        : 'comp_toggledialog',
	   		className : '',
			el        : null,
			content   : '',
			css       : [],
			js        : [],
			jsname    : [],
			pos_base  : document.body,
			style     : null,
			effect    : 'slide',
			callback  : null,
			show_flag : false
		}).merge($H(options));
		options.css = options.css.concat(css);
		options.js = options.js.concat(js);
		options.jsname = options.jsname.concat(jsname);

		return options;
	},

	// 表示中のダイアログ非表示
	// self以外を削除する
	removes : function(self)
	{
		var t = this, rm_dialogs = [];
       	for (var i in t.dialogs) {
       		if(!self || self != t.dialogs[i])
       			rm_dialogs.push(i);
       	}
       	// IEでダイアログからダイアログを表示する際に2つ目のダイアログが再表示できなくなるため修正
       	// （2つ目のダイアログが削除する前に1つ目が削除されてしまうため）
       	for (var i = rm_dialogs.length - 1; i >= 0; --i) {
       		t.remove(rm_dialogs[i]);
       	}
	},

	remove : function(id, hide_flag)
	{
		var t = this, id = id || t.options.id, check, timeout = 5000;
		var dialog = $(id), options = t.options;

		if(!dialog || !dialog.parentNode || typeof dialog.parentNode.tagName == "undefined")
			return false;

		dialog.hide();
 		commonCls.moveVisibleHide(dialog);

		if(hide_flag) {
			check = function() {return !!(Element.getStyle(dialog, "display") == "none");};
			commonCls.wait(check, _remove, timeout);
		} else
			_remove();

		function _remove() {
			if(!dialog)
				return false;

			Element.remove(dialog);
			delete t.dialogs[id];
		};
	},

	show : function(event_el, options)
	{
		var t = this;
		if(options) t.options = t.initOpt(options);
		var id = t.options.id;
		var check, timeout = 5000;
		var dialog = t.dialogs[id], options = t.options;

		if($(id)) Element.remove($(id));
		if(!dialog || !dialog.parentNode || typeof dialog.parentNode.tagName == "undefined")
			dialog = t._create(event_el);

		// loadが終わるまで待機
		check = function() {return !!(t.dialogs[id]);}; //new Function('return !!(t.dialogs[options.id])');
		commonCls.wait(check, _show);

	 	function _show() {
			Element.setStyle(dialog, {zIndex : commonCls.max_zIndex++});
			commonCls.moveVisibleHide(dialog);
			Element.setStyle(dialog, {"visibility" : 'visible'});
	 	}
	},

	hide : function(id)
	{
		var t = this, id = id || t.options.id;
		var dialog = $(id), options = t.options;
		if(!dialog || !dialog.parentNode || typeof dialog.parentNode.tagName == "undefined")
			return false;

		t.remove(id, true);
	},

	toggle : function(event_el, options)
	{
		var t = this;
		if(options) t.options = t.initOpt(options);
		var id = t.options.id;

		var el = (t.options.id) ? $(t.options.id) : event_el.nextSibling;
		if(!el || (id && id != el.id) || !Element.hasClassName(el, t.className) || Element.getStyle(el, "display") == "none") {
			t.show(event_el);
		} else {
			t.hide(id);
		}
	},

	_create : function(event_el)
	{
		var t = this, id = t.options.id;
		var dialog , options = t.options;
		dialog = document.createElement("div");
		dialog.innerHTML = options.content;
		dialog.setAttribute('id', options.id);
		Element.setStyle(dialog, {"visibility" : 'hidden'});
		if(options.className != '')
			Element.addClassName(dialog, options.className);
		Element.addClassName(dialog, t.className);
		if(event_el.tagName && event_el.tagName.toLowerCase() == "body"){
    		event_el.appendChild(dialog);
    	}else {
    		event_el.parentNode.insertBefore( dialog, event_el.nextSibling );
    	}
		if (options.css && options.css.length > 0) {
			for (var i = 0; i < options.css.length; i++) {
				commonCls.addLink(t.css_path + options.css[i]+"&vs="+_nc_css_vs);
			}
		}
		if(options.el) {
			dialog.appendChild(options.el);
		}
		if(options.js && options.js.length > 0) {
			for (var i = 0; i < options.js.length; i++) {
				var j = i;
				commonCls.load(t.js_path + options.js[i]+"&vs="+_nc_js_vs, options.jsname[i], function() {
					if (j+1 == options.js.length) {
						_finProcess();
					}
				});
			}
			return dialog;
		}

		_finProcess();

		return dialog;

		function _finProcess() {
			if(options.callback){
				options.callback.apply(t.object);
			}
			if (options.style)
				_setCss(options.style);

			// 値を保持
			t.dialogs[id] = dialog;

			return true;

			function _setCss(style) {
				var pos, buf;
				var pos_base = options.pos_base;
				if(options.pos_base.nodeName.toLowerCase() != 'body') {
					//Position.prepare();
					pos = Position.positionedOffsetScroll(pos_base);
				} else {
					pos = [0, 0];
				}
				var pos_base_d = Element.getDimensions(pos_base);
				var dialog_d = Element.getDimensions(dialog);
				if(style.width) {
					switch (style.width) {
						case "max":
							style.width = pos_base_d.width - parseInt(Element.getStyle(dialog, "paddingLeft")) - parseInt(Element.getStyle(dialog, "paddingRight"));
							break;
					}
					Element.setStyle(dialog, {width: style.width + "px"});
					delete style.width;
				}
				if(style.height) {
					switch (style.height) {
						case "max":
							style.height = pos_base_d.height - parseInt(Element.getStyle(dialog, "paddingTop")) - parseInt(Element.getStyle(dialog, "paddingBottom"));
							break;
					}
					Element.setStyle(dialog, {height: style.height + "px"});
					delete style.height;
				}
				if(style.top) {
					switch (style.top) {
						case "top":
							style.top = pos[1] + "px";
							break;
						case "bottom":
							style.top = (pos[1] + pos_base_d.height - dialog_d.height) + "px";
							break;
						case "outtop":
							style.top = (pos[1] - dialog_d.height) + "px";
							break;
						case "outbottom":
							style.top = (pos[1] + pos_base_d.height) + "px";
							break;
						case "center":
							buf = pos[1] + pos_base_d.height/2 - dialog_d.height/2;
							style.top = (buf < 0) ? "0px" : buf + "px";
							break;
					}
				}
				if(style.left) {
					switch (style.left) {
						case "left":
							style.left = pos[0] + "px";
							break;
						case "right":
							style.left = (pos[0] + pos_base_d.width - dialog_d.width) + "px";
							break;
						case "outleft":
							style.left = (pos[0] - dialog_d.width) + "px";
							break;
						case "outright":
							style.left = (pos[0] + pos_base_d.width) + "px";
							break;
						case "center":
							buf = pos[0] + pos_base_d.width/2 - dialog_d.width/2;
							style.left = (buf < 0) ? "0px" : buf + "px";
					}
				}
				Element.setStyle(dialog, style);
			};
		};
	}
}