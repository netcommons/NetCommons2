var compColor = Class.create();

compColor.prototype = {
	initialize: function(id) {
		this.id = id;
		this.el = null;
		this.popup = null;
	},

	showDialogBox: function(el, hidden) {
		this.el = el;
		this.hidden = hidden;
		commonCls.referComp[this.id] = this;
	
		commonCls.referComp["compIcon" + this.id] = this;
		if(!this.popup) {
			this.popup = new compPopup(this.id);
		}
		var params = new Object();
		params["param"] = {
			"action":"comp_textarea_view_selectcolor",
			"prefix_id_name":"dialog_selectcolor",
			"parent_id_name":"compIcon" + this.id,
			"_noscript":1};
		params["top_el"] = this.id;
		params["callbackfunc"] = function(res) {
										this.popup.showPopup(res, this.el);
									}.bind(this);
		commonCls.send(params);
	},
	
	setColor: function($dummy, params) {
		this.el.style.backgroundColor = params["color"];
		this.hidden.value = params["color"];
		this.closePopup();
	},
	
	closePopup: function() {
		if(this.popup) {
			this.popup.closePopup();
		}
	}
}