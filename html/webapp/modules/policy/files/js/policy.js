var clsPolicy = Class.create();
var policyCls = Array();

clsPolicy.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	init: function() {
		var top_el = $(this.id);
		var tabset = new compTabset(top_el);
		tabset.render();
		//管理者～ゲストにfocus
		commonCls.tabsetFocus(this.id);
	}
}