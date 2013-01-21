var clsWhatsnew = Class.create();
var whatsnewCls = Array();

clsWhatsnew.prototype = {
	initialize: function(id, block_id) {
		this.id = id;
		this.block_id = block_id;
		this.params = new Object();
		this.rss_action = "whatsnew_view_main_rss";
		this.strlen = 80;
		this.more_str = "";
		this.WHATSNEW_DEF_FLAT = "0";
		this.WHATSNEW_DEF_MODULE = "1";
		this.WHATSNEW_DEF_ROOM = "2";
	},

	showOriginal: function(whatsnew_id, parameter) {
		var params = new Object();
		params["callbackfunc"] = function(res) {
			location.href = _nc_base_url + _nc_index_file_name + "?" + parameter;
		}.bind(this);
		commonCls.sendPost(this.id, 'action=whatsnew_action_main_read&whatsnew_id=' + whatsnew_id, params); 
	},

	switchMain: function(display_type, display_value, flag) {
		var top_el = $(this.id);
		var href_param_str = "&block_id=" + this.block_id;
		if (!display_type) {
			var display_type_el = $("whatsnew_display_type" + this.id);
			if (display_type_el) {
				var display_type = display_type_el.value;
			}
		}
		if (display_type) {
			href_param_str += "&display_type=" + display_type;
		}
		if (!display_value && flag == 0) {
			var display_days_el = $("whatsnew_display_days" + this.id);
			if (display_days_el) {
				var display_days = display_days_el.value;
			}
		}else if(!display_value && flag == 1){
			var the_number_of_display_el = $("whatsnew_the_number_of_display" + this.id);
			if (the_number_of_display_el) {
				var display_number = the_number_of_display_el.value;
			}
		}
		if (display_value && flag == 0) {
			href_param_str += "&display_days=" + display_value;
		}else if(display_value && flag == 1){
			href_param_str += "&display_number=" + display_value;
		}
		href_param_str += "&_header=0";

		var rss_el = $("whatsnew_rss" + this.id);
		if (rss_el) {
			rss_el.href = _nc_base_url + _nc_index_file_name + "?action=" + this.rss_action + href_param_str;
		}
		var params = new Object();
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params["method"] = "post";
		params["param"] = new Object();
		params["param"]["action"] = "whatsnew_action_main_result";
		if (display_type) {
			params["param"]["display_type"] = display_type;
		}
		if (display_value && flag == 0) {
			params["param"]["display_days"] = display_value;
		}else if(display_value && flag == 1){
			params["param"]["display_number"] = display_value;
		}
		params["target_el"] = $("whatsnew_contents" + this.id);
		commonCls.send(params);
	},

	switchDisplayType: function(form_el, not_checked) {
		if (form_el.display_type.value == this.WHATSNEW_DEF_ROOM) {
			form_el.display_room_name.checked = false;
			form_el.display_room_name.disabled = true;
			var el = $("whatsnew_display_room_name" + this.id);
			if (!Element.hasClassName(el.nextSibling, "disable_lbl")) {
				Element.addClassName(el.nextSibling, "disable_lbl");
			}
			if (!not_checked) {
				form_el.display_module_name.checked = true;
			}
			form_el.display_module_name.disabled = false;
			var el = $("whatsnew_display_module_name" + this.id);
			if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
				Element.removeClassName(el.nextSibling, "disable_lbl");
			}
		} else if (form_el.display_type.value == this.WHATSNEW_DEF_MODULE) {
			form_el.display_module_name.checked = false;
			form_el.display_module_name.disabled = true;
			var el = $("whatsnew_display_module_name" + this.id);
			if (!Element.hasClassName(el.nextSibling, "disable_lbl")) {
				Element.addClassName(el.nextSibling, "disable_lbl");
			}
			if (!not_checked) {
				form_el.display_room_name.checked = true;
			}
			form_el.display_room_name.disabled = false;
			var el = $("whatsnew_display_room_name" + this.id);
			if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
				Element.removeClassName(el.nextSibling, "disable_lbl");
			}
		} else {
			if (!not_checked) {
				form_el.display_room_name.checked = true;
			}
			form_el.display_room_name.disabled = false;
			var el = $("whatsnew_display_room_name" + this.id);
			if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
				Element.removeClassName(el.nextSibling, "disable_lbl");
			}
			if (!not_checked) {
				form_el.display_module_name.checked = true;
			}
			form_el.display_module_name.disabled = false;
			var el = $("whatsnew_display_module_name" + this.id);
			if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
				Element.removeClassName(el.nextSibling, "disable_lbl");
			}
		}
	},
	changeStyle: function(form_el) {
		commonCls.sendPost(this.id, 'action=whatsnew_action_edit_style&' + Form.serialize(form_el), {"target_el":$(this.id)}); 
	},

	setSelectRoom: function(form_el) {
		var params = new Object();
		params["callbackfunc"] = function(res) {
			commonCls.removeBlock(this.id);
		}.bind(this);

		commonCls.frmAllSelectList(form_el, "not_enroll_room[]");
		commonCls.frmAllSelectList(form_el, "enroll_room[]");

		commonCls.sendPost(this.id, Form.serialize(form_el), params); 
	},

	changeDisplayType: function(flg) {
		if(flg == 1){
			$("whatsnew_the_number_of_display" + this.id).disabled = true;
			$("whatsnew_display_days" + this.id).disabled = false;
		}else{
			$("whatsnew_the_number_of_display" + this.id).disabled = false;
			$("whatsnew_display_days" + this.id).disabled = true;
		}
	}
}