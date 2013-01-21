var clsCommonOperation = Class.create();
var commonOperationCls = Array();

clsCommonOperation.prototype = {
	initialize: function(id, unioncolumn_str) {
		this.id = id;
		this.unioncolumn_str = unioncolumn_str;
	},
	init: function() {
		commonCls.focus($("form"+this.id));
	},
	selectOnChange: function(event, el) {
		if(el == undefined) {
			var event_el = Event.element(event);
		} else {
			var event_el = el;
		}
		
		var eleOptions = event_el.getElementsByTagName("option");
		var option_len = eleOptions.length;
		for (var i = option_len - 1; i >= 0 ; i--){
			if (Element.hasClassName(eleOptions[i],"disable_lbl") && eleOptions[i].selected == true){
				// disable
				eleOptions[i].selected = false;
				event_el.selectedIndex = 0;
			}
		}
		this.chgDisabled(event_el, "move");
		this.chgDisabled(event_el, "copy");
		this.chgDisabled(event_el, "shortcut");
	},
	chgDisabled: function(select_el, mode) {
		var operation_el = $(mode + this.id);
		if(operation_el) {
			if(select_el.selectedIndex == 0) {
				operation_el.disabled = true;
			} else {
				operation_el.disabled = false;
			}
		}
	},
	getConfirmMes: function(mes) {
		var move_destination_el = $("move_destination"+this.id);
		var optText = move_destination_el.options[move_destination_el.selectedIndex].text;
		return mes + optText.trim();
	},
	compBlock: function(event, parent_id_name, main_page_id, mes, mode) {
		var move_destination_el = $("move_destination"+this.id);
		var value = move_destination_el.value;
		if(mode == "move") {
			pagesCls.deleteBlock(event, parent_id_name, null, false);
		}
		commonCls.alert(mes);
		if(main_page_id == value || this.unioncolumn_str.match("/|"+value+"|/")) {
			// 左右カラム、ヘッダー
			setTimeout(function(){location.href = decodeURIComponent(_nc_current_url).unescapeHTML();}, 300);
		}
	}
}