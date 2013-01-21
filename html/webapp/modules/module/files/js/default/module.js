var clsModule = Class.create();
var moduleCls = Array();

clsModule.prototype = {
	initialize: function(id) {
		this.id = id;
		
		this.tab1_el = null;
		this.tab2_el = null;
		this.tab3_el = null;
	},
	//一般画面初期処理
	moduleInit: function(visibleRows, totalRows, totalSystemRows, totalInstRows) {
		var top_el = $(this.id);
		//Grid
		new compLiveGrid (this.id,visibleRows, totalRows, null);
			
		var opts_system = { 
			tableClass : "module_data_grid_system"
		};
		new compLiveGrid (this.id,visibleRows, totalSystemRows, null, opts_system);
		
		var opts_inst = { 
			tableClass : "module_data_grid_inst"
		};
		new compLiveGrid (this.id,visibleRows, totalInstRows, null, opts_inst);
		
		//タブ
		tabset = new compTabset(top_el);
		tabset.render();
		
		//Element.addClassName(top_el,"display-none");
		//Element.removeClassName(top_el,"display-none");
	},
	//表示順変更初期処理
	moduleDisplayInit: function() {
		var top_el = $(this.id);
		
		//タブ
		var tabset = new compTabset(top_el);
		tabset.render();
	},
	//一括モジュールアップデート
	moduleAllUpdate: function() {
		var top_el = $(this.id);
		//パラメータ設定
		var allins_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"content");	
		
		allins_params["method"] = "post";
		allins_params["top_el"] = top_el;
		allins_params["param"] = {"action":"module_action_admin_update", "op":"all", "upd_module_id":0};
		allins_params["loading_el"] = content;
		allins_params["target_el"] = content;
		//allins_params["match_str"] = "^<span class=\"bold\">";
		
		commonCls.send(allins_params);
	},
	moduleAllUpdateGlobal: function() {
		var top_el = $(this.id);
		//パラメータ設定
		var global_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"module_result_str");
		var div = document.createElement("DIV");
		content.appendChild(div);
		Element.addClassName(div,"module_result_detail_str");

		var parent = Element.getChildElementByClassName(top_el,"content");

		global_params["method"] = "post";
		global_params["top_el"] = top_el;
		global_params["param"] = {"action":"module_action_admin_allupdate"};
		global_params["loading_el"] = parent;
		global_params["target_el"] = div;
		
		commonCls.send(global_params);
	},
	moduleAllUpdateDetail: function(module_id) {
		var top_el = $(this.id);
		//パラメータ設定
		var ins_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"module_result_str");
		var div = document.createElement("DIV");
		content.appendChild(div);
		Element.addClassName(div,"module_result_detail_str");

		var parent = Element.getChildElementByClassName(top_el,"content");

		ins_params["method"] = "post";
		ins_params["top_el"] = top_el;
		ins_params["param"] = {"action":"module_action_admin_update", "op":"detail", "upd_module_id":module_id};
		ins_params["loading_el"] = parent;
		ins_params["target_el"] = div;
		
		commonCls.send(ins_params);
	},
	//モジュールアップデート
	moduleUpdate: function(upd_module_id) {
		var top_el = $(this.id);
		
		//パラメータ設定
		var upd_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"content");	
		
		upd_params["method"] = "post";
		upd_params["top_el"] = top_el;
		upd_params["param"] = {"action":"module_action_admin_update", "upd_module_id":upd_module_id};
		upd_params["loading_el"] = content;
		upd_params["target_el"] = content;
		//upd_params["match_str"] = "^<span class=\"bold\">";
		
		commonCls.send(upd_params);
	},
	//モジュールインストール
	moduleInstall: function(dir_name) {
		var top_el = $(this.id);
		
		//パラメータ設定
		var ins_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"content");	
		
		ins_params["method"] = "post";
		ins_params["top_el"] = top_el;
		ins_params["param"] = {"action":"module_action_admin_install", "dir_name":dir_name};
		ins_params["loading_el"] = content;
		ins_params["target_el"] = content;
		//ins_params["match_str"] = "^<span class=\"bold\">";
		
		commonCls.send(ins_params);
	},
	//モジュールアンインストール
	moduleUninstall: function(upd_module_id) {
		var top_el = $(this.id);
		
		//パラメータ設定
		var uni_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"content");	
		
		uni_params["method"] = "post";
		uni_params["top_el"] = top_el;
		uni_params["param"] = {"action":"module_action_admin_uninstall", "upd_module_id":upd_module_id};
		uni_params["loading_el"] = content;
		uni_params["target_el"] = content;
		//uni_params["match_str"] = "^<span class=\"bold\">";
		
		commonCls.send(uni_params);
	},
	//モジュール表示順変更
	moduleChangeDisplay: function(event,frm,e) {
		var top_el = $(this.id);
		//パラメータ取得
		var params = "";
		
		if (frm.elements[e] == undefined ) {
		}else{
	    	var n = frm.elements[e].length;
	    	for (var i = 0; i < n ; i++) {
	        	params+="&module_array[" + frm.elements[e].options[i].value + "]=" + (i+1);
	    	}
		}
		//パラメータ設定
		var dis_params = new Object();
		var content = Element.getChildElementByClassName(top_el,"content");	
		
		dis_params["method"] = "post";
		dis_params["top_el"] = top_el;
		dis_params["param"] = "module_action_admin_chgdisplayseq" + params;
		dis_params["loading_el"] = content;
		dis_params["callbackfunc"] = function(res){
					commonCls.sendView(this.id,{'action':'module_view_admin_init'});
				}.bind(this);
		//dis_params["target_el"] = content;
		//dis_params["match_str"] = "^<span class=\"bold\">";
		
		commonCls.send(dis_params);
	},
	//権限設定ダイアログ
	showPopup: function(event, module_id, refresh_flag) {
		this.refresh_flag = refresh_flag;

		var param_popup = new Object();
		param_popup["action"] = "module_view_admin_selectauth";
		param_popup["prefix_id_name"] = "popup";
		param_popup["act_module_id"] = module_id;

		var set_param = new Object();
		set_param["target_el"] = $(this.id);
		set_param["center_flag"] = true;
		set_param["modal_flag"] = true;

		commonCls.sendPopupView(event, param_popup, set_param);
	},
	initPopup: function(id, visibleRows, totalRows, module_id) {
		this.act_module_id = module_id;
		var LiveGrid = new compLiveGrid($(id), visibleRows, totalRows);
	},
	closePopup: function(id) {
		commonCls.removeBlock(id);
	},
	authorityCommit: function(id, refresh_flag) {
		var top_el = $(id);
		var elements = Form.getElements($("form" + id));
		var selectauth_params = new Object();

		selectauth_params["method"] = "post";
		
		var params_str = "";
		params_str += "action=module_action_admin_selectauth";
		params_str += "&prefix_id_name=popup";
		params_str += "&act_module_id=" + encodeURIComponent(this.act_module_id);
		var module_authority = new Object();
		for (var i = 0; i < elements.length; i++) {
			switch (elements[i].name) {
			case "module_authority":
				if (elements[i].checked) {
					params_str += "&module_authorities[]" + "=" + encodeURIComponent(elements[i].value);
				}
				break;
			default:
			}
		}
		selectauth_params["param"] = params_str;
		selectauth_params["top_el"] = top_el;
		selectauth_params["loading_el"] = top_el;
		selectauth_params["callbackfunc"] = function(){ 
													commonCls.removeBlock(id); 
													if (this.refresh_flag) {
														commonCls.sendView(this.id,"module_view_admin_init");
													}
												}.bind(this);
		commonCls.send(selectauth_params);
	}
}