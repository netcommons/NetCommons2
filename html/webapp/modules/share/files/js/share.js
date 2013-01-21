var clsShare = Class.create();
var shareCls = Array();

clsShare.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	/*一覧表示画面[Init]*/
	initMain: function(visibleRows, totalRows) {
		new compLiveGrid($(this.id), visibleRows, totalRows);
		commonCls.focus($("share_add"+this.id));
	},
	initRegist: function() {
		commonCls.focus($("url"+this.id));
	},
	showAddSite: function(event, parent_id_name, url) {
		var params = new Object();
		params["action"] = "share_view_admin_regist";
		if(url != undefined) {
			params["url"] = url;
		} else {
			url = "new";
		}
		params["parent_id_name"] = parent_id_name;
		params["prefix_id_name"] = "share_add_" + url;
		
		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		// popupParams['target_el'] = top_el;
		// popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	}
}