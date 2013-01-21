var clsCounter = Class.create();
var counterCls = Array();

clsCounter.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	/*メイン画面に変更*/
	counterMainShow: function() {
		commonCls.sendView(this.id,"counter_view_main_init");
	},
	/*登録ボタン*/
	counterPreview: function() {
		var top_el = $(this.id);
		var form = top_el.getElementsByTagName("form")[0];
		var preview_el = $("_counter_preview"+this.id);

		//パラメータ設定
		var params = new Object();

		params["method"] = "get";
		params["param"] = "action=counter_view_edit_preview" + "&"+ Form.serialize(form);
		params["top_el"] = top_el;
		params["loading_el"] = preview_el;
		params["target_el"] = preview_el;
		commonCls.send(params);
	},
	/*登録ボタン*/
	counterRegist: function(cmd) {
		var top_el = $(this.id);
		var form = top_el.getElementsByTagName("form")[0];
		//パラメータ設定
		var params = new Object();

		params["method"] = "post";
		params["param"] = "action=counter_action_edit_init&zero_flag="+ cmd + "&"+ Form.serialize(form);
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		//params["callbackfunc"] = function(){
		//	this.counterMainShow();
		//}.bind(this);
		commonCls.send(params);
	}
}