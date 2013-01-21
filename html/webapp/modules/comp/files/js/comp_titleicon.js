var compTitleIcon = Class.create();

compTitleIcon.prototype = {
	initialize: function(id) {
		this.id = id;
		this.el = null;
		this.hidden = null
		this.popup = null;
	},

	showDialogBox: function(el, hidden) {
		this.el = el;
		this.hidden = hidden;

		commonCls.referComp["compIcon" + this.id] = this;
		if(!this.popup) {
			this.popup = new compPopup(this.id);
		}
		var params = new Object();
		params["param"] = {
			"action":"comp_textarea_view_insertsmiley",
			"prefix_id_name":"dialog_insertsmiley",
			"parent_id_name":"compIcon" + this.id,
			"_noscript":1};
		params["top_el"] = this.id;
		params["callbackfunc"] = function(res) {
										this.popup.showPopup(res, this.el);
									}.bind(this);
		commonCls.send(params);
	},
	
	insertImage: function(params) {
		this.el.src = params["f_url"];
		this.el.title = params["f_title"];
		this.el.alt = params["f_alt"];
		var arr = params["f_url"].split("/");
		if (arr[arr.length-1] != "blank.gif") {
			this.hidden.value = arr[arr.length-2] + "/" + arr[arr.length-1];
		} else {
			this.hidden.value = "";
		}
		this.closePopup();
	},
	
	closePopup: function() {
		if (this.popup) {
			this.popup.closePopup();
		}
	}
}