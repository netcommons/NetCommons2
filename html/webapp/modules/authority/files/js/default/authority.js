var clsAuthority = Class.create();
var authorityCls = Array();

clsAuthority.prototype = {
	initialize: function(id) {
		this.id = id;
		
		this.dndMgrLevel = null;
		this.dndCustomDraggable = null;
		this.dndCustomDropzone = null;
		
		//this.params = new Object();
		//this.config = new Object();
		//this.conf_val = new Object();
	},
	/*一覧表示画面[Init]*/
	initMain: function(visibleRows, totalRows) {
		new compLiveGrid($(this.id), visibleRows, totalRows);
		commonCls.focus($("authority_add"+this.id));
	},
	/*一般設定画面[Init]*/
	initGeneral: function() {
		commonCls.focus($("role_authority_name" + this.id));
	},
	initDetail: function() {
		commonCls.focus($('private_use_flag_do'+ this.id));
		this.clickModuleUser();
		this.clickModuleRoom();
	},
	initLevel: function() {
		commonCls.focus($('hierarchy'+ this.id));
		
		// ドロップ
		this.dndCustomDropzone = Class.create();
		this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
			showHover: function(event) {
				this.authority_x = Event.pointerX(event);
			},
			hideHover: function(event) {},
			accept: function(draggableObjects) {
				var theGUI = draggableObjects[0].getDroppedGUI();
				//if ( Element.getStyle( theGUI, "position" ) == "absolute" ) {
					theGUI.style.position = "";
					var drag_params = this.getParams();
					var this_obj = drag_params['this_object'];
					var id = this_obj.id;
					
					var authority_level_el = $("authority_level"+id);
					var offset = -1;	//固定値
					var authority_level_el_offset = Position.cumulativeOffset(authority_level_el);
					var level = Math.floor((this.authority_x - authority_level_el_offset[0])/3)+offset;
					////theGUI.style.left = level + "px";
					this_obj._setLevel(level, true);
					authority_level_el.appendChild(theGUI);

				//}
			}
		});
		
		this.dndMgrLevel = new compDragAndDrop();
		this.dndMgrLevel.dragObjectTransparent = false;
		var authority_level_el = $("authority_level"+this.id);
		this.dndMgrLevel.registerDraggableRange(authority_level_el);
		var arrow_el = $("authority_arrow" + this.id);
		this.dndMgrLevel.registerDraggable(new compDraggable(arrow_el, arrow_el));
		this.dndMgrLevel.registerDropZone(new this.dndCustomDropzone(authority_level_el, {"this_object":this}));
	},
	chgAuthority: function(form_el) {
		var selectedIndex = form_el.user_authority_id.selectedIndex;
		var auth_desc_el = $("authority_auth_desc" + this.id);
		var trList = auth_desc_el.getElementsByTagName("tr");
		this._cancelHighLight(trList);
		Element.addClassName(trList[selectedIndex],"highlight");
	},
	_cancelHighLight: function(trList) {
		for (var i = 0; i < trList.length; i++){
			Element.removeClassName(trList[i],"highlight");
		}
	},
	/* 権限名称変更 */
	authorityNameChange: function(this_el) {
		var input_next =  $("authority_next" + this.id);
		var errorstr_el=  $("authority_errorstr" + this.id);
		if(errorstr_el.innerHTML != "") {
			errorstr_el.innerHTML = "";
		}
		if(this_el.value != "") {
			input_next.disabled = false;
		} else {
			input_next.disabled = true;
		}
	},
	/*一般設定画面[次へ]*/
	showDetail: function(level_flag) {
		var form_el =  $("form" + this.id);
		var input_next =  $("authority_next" + this.id);
		var errorstr_el=  $("authority_errorstr" + this.id);
		var parameter = new Object();
		parameter["method"] = 'post';
		parameter["callbackfunc_error"] = function(res){
													if(errorstr_el) {
														errorstr_el.innerHTML = res;
														commonCls.focus(form_el.role_authority_name);
														input_next.disabled = true;
													} else {
														commonCls.alert(res);
													}
												}.bind(this);
		var add_str = "";
		if(level_flag === true) {add_str = "level_flag=1&";}
		else if(level_flag === false) {add_str = "level_flag=0&";}
		commonCls.sendView(this.id,'action=authority_view_admin_detail&'+add_str+Form.serialize(form_el), parameter);
	},
	/*一般設定画面[次へ][戻る]*/
	showCommon: function(action_name, parameter) {
		var form_el =  $("form" + this.id);
		if(parameter == undefined) {
			var parameter = new Object();
		}
		parameter["callbackfunc_error"] = function(res){
													commonCls.alert(res);
													commonCls.focus($('private_use_flag_do'+ this.id));
												}.bind(this);
		commonCls.sendView(this.id,'action='+action_name+'&'+Form.serialize(form_el), parameter);
	},
	/*プライベートルームを使用するラジオボタンのクリック*/
	clickUsedPrivate: function(this_el) {
		var top_el = $(this.id);
		var form_el = top_el.getElementsByTagName("form")[0];
		if (this_el.value == "1") {
			form_el["detail[max_size]"].disabled = false;
		} else {
			form_el["detail[max_size]"].disabled = true;
		}
	},
	
	/*会員管理チェックボックスのクリック*/
	clickModuleUser: function(form_el) {
		if (!form_el) {
			var top_el = $(this.id);
			var form_el = top_el.getElementsByTagName("form")[0];
		}
		// 固定値
		if (form_el.user_authority_id.value < 4 || form_el["detail[modselect][user]"].checked==false) {
			form_el["detail[usermodule_auth]"].disabled = true;
		} else {
			form_el["detail[usermodule_auth]"].disabled = false;
		}
	},
	/*ルーム管理チェックボックスのクリック*/
	clickModuleRoom: function(form_el) {
		if (!form_el) {
			var top_el = $(this.id);
			var form_el = top_el.getElementsByTagName("form")[0];
		}
		// 固定値
		if (form_el.user_authority_id.value == 5 || form_el.user_authority_id.value == 1 || form_el["detail[modselect][room]"].checked==false) {
			form_el["detail[public_createroom_flag]"].disabled = true;
			form_el["detail[group_createroom_flag]"].disabled = true;
		} else {
			form_el["detail[public_createroom_flag]"].disabled = false;
			form_el["detail[group_createroom_flag]"].disabled = false;
		}
	},
	/* レベル変更 */
	chgLevel: function(this_el) {
		var level = this_el.value;
		this._setLevel(level, true);
	},
	clickLevel: function(e, this_el) {
		var x = Event.pointerX(e);
		var this_el_offset = Position.cumulativeOffset(this_el);
		var offset = -1;	//固定値
		var level = Math.floor((x - this_el_offset[0])/3)+offset;
		this._setLevel(level, true);
	},
	_setLevel: function(level, set_text_flag) {
		if(level == "" || level < 0) level = 0;
		else if(level > 100) level = 100;
		var arrow_el = $("authority_arrow" + this.id);
		arrow_el.style.left = level*3 + "px";
		if(set_text_flag) {
			var hierarchy_el = $("hierarchy" + this.id);
			hierarchy_el.value = level;
		}
	},
	/*プライベートに配置可能なモジュール画面[Init]*/
	initSelectModules: function() {
		var top_el = $(this.id);
		var form_el = top_el.getElementsByTagName("form")[0];
		this.chkSelectModules(form_el);
	},
	/*追加・削除イベント*/
	chkSelectModules: function(form_el) {
		if (form_el['enroll_modules[]'].options.length > 0) {
			form_el.next_arrow.disabled = false;
		} else {
			form_el.next_arrow.disabled = true;
		}
	}
}