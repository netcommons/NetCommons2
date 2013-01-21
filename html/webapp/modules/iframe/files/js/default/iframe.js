var clsIframe = Class.create();
var iframeCls = Array();

clsIframe.prototype = {
	initialize: function(id) {
		this.id = id;
    },
    /* メイン画面初期化処理 */
    iframeEditInit: function() {
        commonCls.focus(this.id);
	},
    /* 詳細表示 */
    showDetail: function(this_el) {
    	var detailfields = Element.getElementsByClassName($(this.id), "_iframe_detail");
		detailfields.each(function(detail_el) {
			Element.removeClassName(detail_el, "display-none");
		}.bind(this));
		Element.addClassName(this_el, "display-none");
		commonCls.focus(this.id);
	},
	/*登録処理*/
	iframeRegist: function() {
		/* TODO:エラー処理をまったくしていない。後に修正 */
		var top_el = $(this.id);
		var form_el = top_el.getElementsByTagName("form")[0];
		//パラメータ設定
		var params = new Object();
		params["method"] = "post";
		params["param"] = "action=iframe_action_edit_init" + "&"+ Form.serialize(form_el);
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		commonCls.send(params);
	}
}
