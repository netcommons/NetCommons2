/*
 * NC WYSIWYG 0.0.0.1
 * @author      Ryuji Masukawa
 *
 * NC WYSIWYG was made based on the jwysiwyg0.5.
 * http://code.google.com/p/jwysiwyg/
 *
 * tinymce3(minorVersion 2.7) is used as reference.
 * http://tinymce.moxiecode.com/
 *
 */

//テキストエリア用クラス
var compTextarea = Class.create();
var textareaComp = Array();
/*
    モジュール側のjavascriptクラスから
    this.textarea = new parent.compTextarea();
    this.textarea.textareaShow(this.id, "announcement_text", "full");
    のように使用
*/
compTextarea.prototype = {

		// nc20用
		uploadAction    : {},
		focus           : false,
		popupPrefix     : "",
		downloadAction  : "common_download_main",
		uploadAction    : {
			unique_id : "0",
			image     : null,
			file      : null
		},
		top_table       : null,

		js_path         : null,
		css_path        : null,

		textarea        : null,

		initialize: function(options) {
			var self = this;
			self.js_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_js&add_block_flag=1&dir_name=";
			self.css_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&header=0&dir_name=/comp/plugins/";

			commonCls.load(self.js_path + "comp_textareamain"+"&vs="+_nc_js_vs, "window.compTextareamain", function(){
				self.textarea = new compTextareamain(self);
			});

		},

		textareaShow : function(id, textarea_classname, mode) {
			var self = this;
			if( this.textarea == null) {
				setTimeout(function(){self.textareaShow(id, textarea_classname, mode);}.bind(this), 100);
				return;
			}
			this.setOptions();
			this.textarea.textareaShow(id, textarea_classname, mode);
			this.setOptionsAfter();
		},

		textareaEditShow : function(id,text_el,mode) {
			var self = this;
			if( this.textarea == null) {
				setTimeout(function(){self.textareaEditShow(id,text_el,mode);}.bind(this), 100);
				return;
			}
			this.setOptions();
			this.textarea.textareaEditShow(id,text_el,mode);
			this.setOptionsAfter();
		},

		clear : function()
        {
			this.textarea.clear();
        },

        addFocus : function(now, callback) {
			this.textarea.addFocus(now, callback);
        },

		// nc20用
		getTextArea : function() {
			return this.textarea.getTextArea();
		},

		setTextArea : function(newContent) {
			var self = this;
			if( this.textarea == null) {
				setTimeout(function(){self.setTextArea(newContent);}.bind(this), 100);
				return;
			}
			this.textarea.options.content = newContent;
		},

		// addFocusの別名
		focusEditor : function(now, callback) {
			this.textarea.addFocus(now, callback);
		},

		setOptions : function() {
			var self = this;
			self.textarea.uploadAction = self.uploadAction;
			self.textarea.downloadAction = self.downloadAction;
			self.textarea.popupPrefix = self.popupPrefix;
			self.textarea.focus = self.focus;
		},
		setOptionsAfter : function() {
			var self = this;
			this.top_table = this.textarea.el;
		}
}