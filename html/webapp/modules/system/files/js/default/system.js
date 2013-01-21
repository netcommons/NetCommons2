var clsSystem = Class.create();
var systemCls = Array();

clsSystem.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	
	systemInit: function(message) {
		this.origItems = $H(Form.serialize($("form" + this.id), 1));
		this.message = message;
		this.showAllUseItems = false;
		// this.initShowDate();
	},
	
	initShowDate: function() {
		this._setEventForDate($("form" + this.id).getElementsByTagName('input'));
		this._setEventForDate($("form" + this.id).getElementsByTagName('textarea'));
	},
	
	_setEventForDate: function(elements) {
		$A(elements).each(function(element) {
			var date_el = element.nextSibling;
			if (Element.hasClassName(date_el, 'system_date'))
				commonCls.displayNone(date_el);
			Event.observe(element, 'focus', function(event) {
				var target = Event.element(event);
				if (target.type == 'text' || target.type == 'textarea') {
					var el = target.nextSibling;
					if (Element.hasClassName(el, 'system_date')) {
						commonCls.displayVisible(el);
					}
				}
			});
			Event.observe(element, 'blur', function(event) {
				if (event.target.type == 'text' || event.target.type == 'textarea') {
					var el = event.target.nextSibling;
					if (Element.hasClassName(el, 'system_date')) {
						commonCls.displayNone(el);
					}
				}
			});
		});
	},
	
	chgAutoregistApprover: function(this_el) {
		if(this_el.selectedIndex == 0) {
			this.visible(this_el, 'system_user_agree'); 
			this.hidden(this_el, 'system_admin_agree');
		} else if(this_el.selectedIndex == 1) {
			this.hidden(this_el, ['system_user_agree', 'system_admin_agree']);
		} else if(this_el.selectedIndex == 2) {
			this.visible(this_el, ['system_user_agree', 'system_admin_agree']); 
		}
	},

	/* system_setting以下の指定されたクラスを可視化 */
	visible: function(this_el, name) {
		var table_el = Element.getParentElementByClassName(this_el, 'system_setting');
		var elems = document.getElementsByClassName(name, table_el);
		if (typeof name == 'string') {
			elems = document.getElementsByClassName(name, table_el);
		} else {
			name.each(function(name_str){
				elems = elems.concat(document.getElementsByClassName(name_str, table_el));
			});
		}
		
		elems.each(function(el) {
			if ($(this_el).getParentElement(2) != el) {
				commonCls.displayVisible(el);
				commonCls.blockNotice(null, el);
			}
		});
		
		
		//elems.each(function(el) {
		//	commonCls.displayVisible(el);
		//	commonCls.blockNotice(null, el);
		//});
		// commonCls.focus(this.id);
		if (elems[0].display != "none") {
				var input_el = (elems[0].getElementsByTagName("input")[0] || elems[0].getElementsByTagName("textarea")[0])
				if (input_el) {
					input_el.focus();
					input_el.select();
				}
		}
	},

	hidden: function(this_el, name) {
		var table_el = Element.getParentElementByClassName(this_el, 'system_setting');
		var elems = new Array();
		if (typeof name == 'string') {
			elems = document.getElementsByClassName(name, table_el);
		} else {
			name.each(function(name_str){
				elems = elems.concat(document.getElementsByClassName(name_str, table_el));
			});
		}
		elems.each(function(el) {
			if ($(this_el).getParentElement(2) != el) {
				commonCls.displayNone(el);
			}
		});
	},
	
	mailMethodChange: function(method) {
		var form_el = $("form" + this.id);
		var el = form_el.elements["mailmethod"];
		if (el == undefined) {
			// TODO
		} else {
			// needed?
			el.selectedIndex = -1;
			for (var i = 0; i < el.options.length; i++){
      			if (el.options[i].value == method){
        			el.selectedIndex = i;
        			break;
        		}
        	}
        	// Visible the required element(s)      			
        	if (method.match(/^smtp/)) {
        		this.hidden(el, 'system_sendmail');
        		this.visible(el, 'system_smtp');
        		if (method == 'smtpauth') {
        			this.visible(el, 'system_smtpauth');
        		} else {
        			this.hidden(el, 'system_smtpauth');
        		}
        	} else {
	        	this.hidden(el, ['system_smtp', 'system_smtpauth']);
	        	if (method == 'mail') {
	        		this.hidden(el, 'system_sendmail');
	        	} else {
	        		this.visible(el, 'system_sendmail');
	        	}
        	}
        } 
    }, 
    toggleShowItems: function() {
    	this.showAllUseItems = (this.showAllUseItems) ? false : true;
    	var items = document.getElementsByClassName('system_autoregist_item', $('system_autoregist_items' + this.id));
    	if (this.showAllUseItems) {
	     	$A(items).each(function(item) {
	    		commonCls.displayVisible(item);
	    	});
	    } else {	
	    	$A(items).each(function(item) {
	    		// n=2:SYSTEM_AUTOREGIST_HIDE_ITEM
	    		var n = item.getElementsByTagName('select')[0].selectedIndex;
	    		n = (n != undefined) ? n : 2;
	    		if (Element.hasClassName(item, 'must_visible')) {
	    			// already visible
	    		} else if (n != 2) { 
	    			commonCls.displayVisible(item);
	    		} else {
	    			commonCls.displayNone(item);
	    		}
	    	});
    	}
    	items = document.getElementsByClassName('system_autoregist_show_items');
    	$A(items).each(function(item) {
    		commonCls.displayChange(item);
    	});
    	
    },

	pagestyleInit: function() {
		var top_el = $(this.id);
		this.origItems = $H(Form.serialize($("form" + this.id), 1));
		this.pagetheme_categories = {};
		
		var tabset = new compTabset(top_el);
		//tabset.setActiveIndex(0);	
		tabset.render();
	
		var tds = $('system_pagetheme_panels' + this.id).getElementsByTagName('td');
		$A(tds).each(function(td) {	
			Event.observe(td, 'click', this.clickPanel.bindAsEventListener(this));
		}.bind(this));
	},

    selectLayout: function(this_el, type) {
    	var el = $('system_pagelayout_type' + this.id);
    	el.value = type;
    	var elems = $('_pagestyle_layout' + this.id).getElementsByTagName('a');
    	$A(elems).each(function(el) {
			if (Element.hasClassName(el, "highlight")) {
    			Element.removeClassName(el, 'highlight');
    		}
    	});
    	Element.addClassName(this_el, 'highlight');
    },
    
    setTheme: function(this_el, theme_name, lang_theme_name, category_name) {
    	// パネル部分
    	var tds = $('system_pagetheme_panels' + this.id).getElementsByTagName('td');
    	$A(tds).each(function (td) {
    		if (td.hasClassName('highlight')) {
    			var input = $(td.id + '_input'); // hidden field
    			input.value = theme_name;
    			var src_img = Element.getChildElement(this_el);
    			var target_img = $(td.id + '_img');
    			target_img.src = src_img.src;
    			target_img.nextSibling.innerHTML = lang_theme_name;
    			this.pagetheme_categories[input.id] = category_name;
    			commonCls.blockNotice(null, td);
    		}
    	}.bind(this));
    	
    	// カテゴリ分けされたテーマ
    	var elems = document.getElementsByClassName('system_pagestyle', 'system_pagestyle_top' + this.id);
    	elems.each(function (el) {
    		if (Element.hasClassName(el, 'highlight')) {
    			Element.removeClassName(el, 'highlight');
    		}
    	});
    	Element.addClassName(this_el, 'highlight');
    },	

	clickPanel: function(event) {	
		var el = Event.element(event);
		Event.stop(event);
		if (el.tagName != "TD") {
			el = Element.getParentElementByClassName(el, 'system_pagetheme_panel');
		}

		// パネル部分
		var tds = $('system_pagetheme_panels' + this.id).getElementsByTagName('td');
    	$A(tds).each(function (td) {
    		if (td == el) {
    			if (!td.hasClassName('highlight')) {
    				td.addClassName('highlight');
    			}
	    	} else if (!event.shiftKey) {
		    	if (td.hasClassName('highlight')) {
		   			td.removeClassName('highlight');
				}
    		}
    	}.bind(this));

		// カテゴリ一覧から現在設定されているカテゴリを開く
		var blocks = document.getElementsByClassName('system_pagetheme_category_block', 'system_pagestyle_top');
		blocks.each(function (block) {
			if (this.pagetheme_categories) {
				for (var id in this.pagetheme_categories) {
					var category_name = this.pagetheme_categories[id];
					if (block.hasClassName('system_pagetheme_' + category_name)) {
						commonCls.displayVisible(block);
					}
				}
			} else {
				commonCls.displayNone(block);
			}
		}.bind(this));
		
		// テーマアイコンハイライト
		var elems = document.getElementsByClassName('system_pagestyle', 'system_pagestyle_top' + this.id);
    	elems.each(function (el) {
    		if (Element.hasClassName(el, 'highlight')) {
    			Element.removeClassName(el, 'highlight');
    		}
    	});
		var theme_name = $(el.id + '_input').value;
		var theme_el = $('system_' + theme_name + this.id);
		if (!theme_el.hasClassName('highlight')) {
			theme_el.addClassName('highlight');
		}
		
	},
	
	setConfig: function(view_name, change_lang) {
		var top_el = $(this.id);
		var form_el = $("form" + this.id);
		var action_params = new Object();
		action_params['method'] = "post";
		var action_name = this._getConfigAction(view_name);
		action_params["param"] = "action=" + action_name + "&"+ Form.serialize(form_el);
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;
		action_params["callbackfunc"] = function(res) {
			if (res.match(/error_message:(.*)$/)) {
				commonCls.alert(RegExp.$1);
				return;
			}	
			// NOTICE: Hash Object incompatibility (1.5 and 1.6)
			var items = $H(Form.serialize(form_el, 1));
			var origItems = this.origItems;
			var focused = false;
			var changed_items = "";
			Form.getElements(form_el).each(function(el) {
				var name = el.name;
				if (items[name] != origItems[name]) {
					commonCls.blockNotice(null, Element.getParentElement(el));
					// changed_items += "\n" + name + ": " + origItems[name] + " -> " + items[name];
				}
				if (!focused) {
					el.focus();
					focused = true;
				}
			});
			this.origItems = Object.clone(items);
			commonCls.alert(this.message + changed_items);
			if (change_lang) {
				var params = {
					"action":view_name,
					"lang":change_lang
				}
				commonCls.sendView(this.id, params);
			}
		}.bind(this);

		action_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			var formElement = $("form" + this.id);
			formElement['lang_dirname'].value = formElement['current_lang_dirname'].value;
		}.bind(this);

		commonCls.send(action_params);
	},

	_getConfigAction: function(action_name) {
		return action_name.gsub('^system_view_main_', 'system_action_');
	},

	changeLanguage: function(confirmMessage, viewActionName, language) {
		if (!commonCls.confirm(confirmMessage)) {
			var formElement = $("form" + this.id);
			formElement['lang_dirname'].value = formElement['current_lang_dirname'].value;
			return false;
		}

		this.setConfig(viewActionName, language);
	}
}
