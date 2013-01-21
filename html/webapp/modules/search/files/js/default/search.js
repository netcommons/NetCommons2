var clsSearch = Class.create();
var searchCls = Array();

clsSearch.prototype = {
	initialize: function(id, block_id) {
		this.id = id;
		this.block_id = block_id;

		this.SEARCH_SHOW_MODE_NORMAL = "0";
		this.SEARCH_SHOW_MODE_SIMPLE = "1";

		this._SELECT_KIND_AND = "0";
		this._SELECT_KIND_OR = "1";
		this._SELECT_KIND_PHRASE = "2";

		this.strlen = 80;
		this.more_str = "";
		this.i = 0;
	},

	initMain: function(show_mode, pages_action) {
		if (show_mode == this.SEARCH_SHOW_MODE_NORMAL) {
			this.calendarFm = new compCalendar(this.id, "search_fm_target_date" + this.id);
			this.calendarTo = new compCalendar(this.id, "search_to_target_date" + this.id);
		}
	},
	keydownExec: function(event, form_el) {
		if (event.keyCode == 13) {
			this.searchExec(form_el);
		}
	},
	easySearch: function(form_el, pages_action) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = "action=search_action_main_result_easy&" + Form.serialize(form_el);
		params["method"] = "post";
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["callbackfunc"] = function(res) {
			location.href = _nc_base_url + _nc_index_file_name + "?action=" + pages_action +
							"&active_center=search_view_main_center&active_block_id=" + this.block_id +
							"&page_id=" + _nc_main_page_id;
		}.bind(this);
		commonCls.send(params);
	},
	searchExec: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = "action=search_action_main_result_condition&" + Form.serialize(form_el);
		params["method"] = "post";
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["callbackfunc"] = function(res) {
			var condition_el = $("search_result" + this.id);
			condition_el.innerHTML = res;

			this.i = 0;
			while (form_el["target_modules[]"][this.i] != undefined) {
				var target_module = form_el["target_modules[]"][this.i];
				if (target_module.checked) {
					this._searchExec(target_module.value, form_el.target_room.value, form_el);
					break;
				}
				this.i++;
			}
		}.bind(this);
		commonCls.send(params);
	},
	_searchExec: function(target_module, target_room, form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = "action=search_action_main_result_transition&target_module=" + target_module;
		if (target_room != "0") {
			params["param"] += "&target_room=" + target_room;
		}
		params["method"] = "post";
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["callbackfunc"] = function(res) {
			this.i++;
			while (form_el["target_modules[]"][this.i] != undefined) {
				var target_module_el = form_el["target_modules[]"][this.i];
				if (target_module_el.checked) {
					this._searchExec(target_module_el.value, target_room, form_el);
				}
				this.i++;
			}
		}.bind(this);
		params["target_el"] = $("search_result_content" + this.id + "_" + target_module);
		commonCls.send(params);
	},
	clearExec: function(pages_action) {
		if (pages_action) {
			var top_el = $(this.id);
			var params = new Object();
			params["param"] = "action=search_action_main_result_clear&block_id=" + this.block_id;
			params["method"] = "post";
			params["top_el"] = top_el;
			params["loading_el"] = top_el;
			params["callbackfunc"] = function(res) {
				location.href = _nc_base_url + _nc_index_file_name + "?action=" + pages_action +
								"&page_id=" + _nc_main_page_id;
			}.bind(this);
			commonCls.send(params);
		} else {
			commonCls.sendPost(this.id, 'action=search_action_main_result_clear', {"target_el":$(this.id)});
		}
	},
	getResult: function(target_module, target_room, offset, limit, form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = "action=search_action_main_result_transition&target_module=" + target_module;
		if (target_room != "0") {
			params["param"] += "&target_room=" + target_room;
		}
		if (offset) {
			params["param"] += "&offset=" + offset;
		}
		if (limit) {
			params["param"] += "&limit=" + limit;
		}
		params["method"] = "post";
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("search_result_content" + this.id + "_" + target_module);
		commonCls.send(params);
	},
	descriptionMore: function(module_id, class_name, keywords, select_kind) {
		var fields = Element.getElementsByClassName($("search_result_content" + this.id + "_" + module_id), class_name);
		fields.each(function(el) {
			var content_str = el.innerHTML;
			var content_length = content_str.length;

			if (keywords == "") {
				el.innerHTML = (content_length > this.strlen) ? content_str.substr(0, this.strlen) + this.more_str : content_str;
			} else if (select_kind == this._SELECT_KIND_PHRASE) {
				var keyword = keywords;
				var pattern = new RegExp(commonCls.escapeRegExp(keyword), "i");
				var keyword_match = content_str.search(pattern);
				if (keyword_match != -1) {
					el.innerHTML = this._moreSubstr(content_str, keyword);
				} else {
					el.innerHTML = (content_length > this.strlen) ? content_str.substr(0, this.strlen) + this.more_str : content_str;
				}
			} else {
				var keyword_arr = keywords.replace("　", " ").split(" ");
				var match_flag = false;
				for (var i=0; i<keyword_arr.length; i++) {
					var keyword = keyword_arr[i];
					if (keyword == "") { continue; }
					var pattern = new RegExp(commonCls.escapeRegExp(keyword), "i");
					var keyword_match = content_str.search(pattern);
					if (keyword_match != -1) {
						match_flag = true;
						el.innerHTML = this._moreSubstr(content_str, keyword);
						break;
					}
				}
				if (!match_flag) {
					el.innerHTML = (content_length > this.strlen) ? content_str.substr(0, this.strlen) + this.more_str : content_str;
				}
			}
		}.bind(this));
	},
	_moreSubstr: function(content_str, keyword) {
		var content_length = content_str.length;
		var pattern = new RegExp(commonCls.escapeRegExp(keyword), "i");
		var keyword_match = content_str.search(pattern);
		if (keyword_match != -1) {
			if (content_length <= this.strlen) {
				var content_str = this._highlight(content_str, keyword);
			} else if (content_length - keyword_match <= this.strlen) {
				var content_str = content_str.substr(content_length - this.strlen - 1);
				var content_length = content_str.length;
				var keyword_match = content_str.search(pattern);
				var content_str = this._highlight(content_str, keyword);
			} else {
				var content_str = content_str.substr(keyword_match, this.strlen);
				var content_length = content_str.length;
				var keyword_match = content_str.search(pattern);
				var content_str = this._highlight(content_str, keyword) + this.more_str;
			}
		}
		return content_str;
	},
	_highlight: function(content_str, keyword) {
		var content_length = content_str.length;
		var pattern = new RegExp(commonCls.escapeRegExp(keyword), "i");
		var keyword_match = content_str.search(pattern);
		var ret_content_str = content_str;
		var next_content_str = "";
		if (keyword_match != -1) {
			var ret_content_str = content_str.substr(0, keyword_match) +
							"<span class='highlight'>" + content_str.substr(keyword_match, keyword.length) + "</span>";
			var next_str = content_str.substr(keyword_match + keyword.length);
			if (next_str != "") {
				next_content_str = this._highlight(next_str, keyword);
			}
		}
		return ret_content_str + next_content_str;
	},

	registStyle: function(form_el) {
		commonCls.sendPost(this.id, 'action=search_action_edit_style&' + Form.serialize(form_el), {"target_el":$(this.id)});
	}
}