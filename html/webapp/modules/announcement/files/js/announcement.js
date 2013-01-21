var clsAnnouncement = Class.create();
var announcementCls = Array();

clsAnnouncement.prototype = {
	initialize: function(id) {
		this.id = id;
		this.textarea = null;
		this.textarea_more = null;
	},
	/*メイン画面に変更*/
	announcementMainShow: function() {
		commonCls.sendView(this.id,"announcement_view_main_init");
	},
	/*編集テキストエリアに変更*/
	announcementEditShow: function() {
		commonCls.sendView(this.id,"announcement_view_edit_init");
	},
	/*編集画面初期処理*/
	announcementEditInit: function() {
		//テキストエリア
		this.textarea = new compTextarea();
		this.textarea.uploadAction = {
			//unique_id   : 0,
			image    : "announcement_action_upload_image",
			file     : "announcement_action_upload_init"
		};
		this.textarea.focus = true;
		this.textarea.textareaShow(this.id, "comptextarea", "full");
	},
	moreInit: function() {
		//テキストエリア;
		if(this.textarea_more == null) {
			this.textarea_more = new compTextarea();
			this.textarea_more.uploadAction = {
				//unique_id   : 0,
				image    : "announcement_action_upload_image",
				file     : "announcement_action_upload_init"
			};
		}
		this.textarea_more.textareaShow(this.id, "textarea_more"+this.id, "full");
	},
	/*登録ボタン*/
	announcementRegist: function(form_el) {
		var top_el = $(this.id);
		var more_checked = 0;
		if(form_el.more_checkbox.checked) {
			more_checked = 1;
		}
		var more_title = null;
		var more_content = null;
		var hide_more_title = null;
		if(this.textarea_more != null) {
			more_title = form_el.more_title.value;
			more_content = this.textarea_more.getTextArea();
			hide_more_title = form_el.hide_more_title.value;
		}
		var content = this.textarea.getTextArea();
		//パラメータ設定
		var ins_params = new Object();
		
		ins_params["method"] = "post";
		ins_params["param"] = {"action":"announcement_action_edit_init",
							   "content":content,
							   "more_checked":more_checked,
							   "more_content":more_content,
							   "more_title":more_title,
							   "hide_more_title":hide_more_title
							  };
		ins_params["top_el"] = top_el;
		ins_params["loading_el"] = top_el;
		ins_params["callbackfunc"] = function(){this.announcementMainShow();}.bind(this);
		
		commonCls.send(ins_params);
	},
	/*続きを書く*/
	checkMore: function(check_el, confirmMessage) {
		if(check_el.checked == true) {
			commonCls.displayChange($('announcement_more_content' + this.id)); 
			this.moreInit();
		}else { 
			if (!commonCls.confirm(confirmMessage)) {
				check_el.checked = true;
				return false;
			} 
			commonCls.displayChange($('announcement_more_content' + this.id));
		}
	},
	/*キャンセルボタン*/
	announcementCancel: function() {
		this.announcementMainShow();
	}
}