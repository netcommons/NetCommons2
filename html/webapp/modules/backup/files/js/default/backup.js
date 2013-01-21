var clsBackup = Class.create();
var backupCls = Array();

clsBackup.prototype = {
	initialize: function(id) {
		this.id = id;
		this.backingup_upload_id = new Object();

		this.visibleRows = 0;
		this.totalRows = 0;

		this.popup = null;
	},
	backupInit: function(visibleRows, totalRows) {
		if (totalRows == 0) { return; }
		if(visibleRows == undefined) {
			visibleRows = this.visibleRows
			totalRows = this.totalRows
		} else {
			this.visibleRows = visibleRows;
			this.totalRows = totalRows;
		}
		new compLiveGrid (this.id, visibleRows, totalRows, null);
	},
	refreshDetail: function() {
		commonCls.sendView(this.id,'action=backup_view_main_init&detail_flag=1&'+Form.serialize($('form'+this.id)),{'target_el': $('backup_main'+this.id),'callbackfunc':function(){this.backupInit();},'loading_el':null});
	},
	chkBackupSize: function(upload_id) {
		commonCls.sendView(this.id,'action=backup_view_main_getsize&chk_upload_id='+upload_id,{'target_el': null,'loading_el':null,"callbackfunc":function(res){this.compChkSize(res, upload_id);}.bind(this)});
	},
	compChkSize: function(res, upload_id) {
		if(res.match("comp_mes:")) {
			var timer = this.backingup_upload_id[upload_id];
			if(timer) {
				clearInterval(timer);
				this.backingup_upload_id[upload_id] = null;
			}
			res = res.replace("comp_mes:","");
			if(res != "") {
				commonCls.alert(res);
				this.refreshDetail();
			}
		} else {
			var target_el = $("backup_size" + this.id + upload_id);
			if(!target_el) {
				var timer = this.backingup_upload_id[upload_id];
				if(timer) {
					clearInterval(timer);
					this.backingup_upload_id[upload_id] = null;
				}
			} else {
				if(res.trim() != "") target_el.innerHTML = res;
				this.backingup_upload_id[upload_id] = setTimeout(function(){this.chkBackupSize(upload_id)}.bind(this), 3000);
			}
		}
	},
	/* リストア内容確認 */
	initRestore: function(visible_rows, count) {
		var top_el = $(this.id);

		if(count != 0) {
			//トップエレメントID、表示行数、トータル行数、取得アクション名称、オプション
			new compLiveGrid ($("backup_confirm_entry_users"+this.id), visible_rows, count);
		}
		var tabset = new compTabset(top_el);
		tabset.render();
	},
	showPopup: function(el) {
		if (this.popup == null || !$(this.popup.popupID)) {
			this.popup = new compPopup(this.id, "addBackup" + this.id);
		}
		commonCls.referComp["addBackup" + this.id] = this.popup;

		var src = _nc_base_url + _nc_index_file_name + "?action=backup_view_main_insertupload&top_id_name=" + this.id +
				"&_header=1&_noscript=1";
		this.popup.showSrcPopup(src, el);
	},
	_focusPopup: function() {
		var form_el = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
		commonCls.focus(form_el);
	},
	addFile: function(form_el) {
		var top_el = $(this.id);
		this.params = new Object();
		this.params['document_obj'] = this.popup.popupElement.contentWindow.document;
		this.params["method"] = "post";
		this.params["loading_el"] = top_el;
		this.params["top_el"] = top_el;
		this.params["param"] = new Object();
		this.params["param"]["action"] = "backup_action_upload_init";
		this.params["callbackfunc"] = function(file_list, res){
			this.popup.closePopup();
			this.refreshDetail();
		}.bind(this);
		this.params["callbackfunc_error"] = function(file_list, error_mes){
			commonCls.alert(error_mes.unescapeHTML());
			this._focusPopup();
		}.bind(this);
		commonCls.sendAttachment(this.params);
	}
}