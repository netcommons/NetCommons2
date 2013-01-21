var clsCabinet = Class.create();
var cabinetCls = Array();

clsCabinet.prototype = {
	initialize: function(id) {
		this.id = id;
		this.cabinet_id = null;
		this.folder_id = null;

		this.gridList = null;
		this.popupContext = null;

		this.branch_minus = "branch_minus.gif";
		this.branch_plus = "branch_plus.gif";
		this.open_folder = "open_folder.gif";
		this.close_folder = "close_folder.gif";

		this.CABINET_FILETYPE_FILE = "0";
		this.CABINET_FILETYPE_FOLDER = "1";

		this.messages = new Object();
		this.movement = new Object();
		this.compress_download = null;
		
		this.downloads = new Object();

		this.ascImg = _nc_core_base_url + '/images/comp/livegrid/sort_asc.gif';
		this.descImg = _nc_core_base_url + '/images/comp/livegrid/sort_desc.gif';
		this.sortCol = null;
		this.sortDir = "DESC";
		this.oldSortCol = null;
		this.folder_height = 0;

	},


	/* 
	 * Main function
	 */
	switchBranch: function(folder_id) {
		var child_el = $("cabinet_children_folder" + folder_id + this.id);
		if (child_el) {
			var img_branch_el = $("cabinet_branch_image" + folder_id + this.id);
			var img_folder_el = $("cabinet_folder_image" + folder_id + this.id);
			if (Element.hasClassName(child_el, "display-none")) {
				img_branch_el.src = img_branch_el.src.replace(this.branch_plus, this.branch_minus);
				img_folder_el.src = img_folder_el.src.replace(this.close_folder, this.open_folder);
			} else {
				img_branch_el.src = img_branch_el.src.replace(this.branch_minus, this.branch_plus);
				img_folder_el.src = img_folder_el.src.replace(this.open_folder, this.close_folder);
			}
			commonCls.displayChange(child_el);
		}
	},

	download: function(file_id, default_count) {
		if (typeof this.downloads["_" + file_id] == "undefined") {
			this.downloads["_" + file_id] = default_count;
		}
		this.downloads["_" + file_id]++;
		
		var download_count_str = this.messages["download_count"].replace("%s", this.downloads["_" + file_id]);
		
		var el = $("cabinet_count" + file_id + this.id);
		if (el) { el.innerHTML = download_count_str; }
	},

	switchAddress: function(el) {
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_switchAddress", "cabinet_id":this.cabinet_id, "address":el.value};
		params["top_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			if (res) {
				this.folder_id = res;
				this.reloadExplorer();
			}
		}.bind(this);
		commonCls.send(params);
	},
	setAddress: function() {
		var input_el = $("cabinet_address_input" + this.id);
		if (!input_el) { return; }

		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_address", "cabinet_id":this.cabinet_id, "folder_id":this.folder_id};
		params["top_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			input_el.value = res;
		}.bind(this);
		commonCls.send(params);
	},

	initFileList: function(visibleRow, fileNum) {
		if (fileNum == 0) { return; }
		var opts = {
			requestParameters : new Array("cabinet_id=" + this.cabinet_id, "folder_id=" + this.folder_id),
			prefetchBuffer : true,
			sort : true,
			tableClass : "cabinet_right",
			onSendCallback : function(res) {
				this.setDragAndDrop("cabinet_explore_right" + this.id);
			}.bind(this)
		};
		this.gridList = new compLiveGrid (this.id, visibleRow, fileNum, "cabinet_view_main_explorer_list", opts);
	},
	
	sortBy: function(sort_col) {
		if(this.sortCol == null) {
			this.sortDir = "DESC";
		}else {
			if(this.sortCol != sort_col) {
				this.oldSortCol = this.sortCol;
				this.sortDir = "DESC";
			}else {
				if(this.sortDir == "DESC") {
					this.sortDir = "ASC";
				}else {
					this.sortDir = "DESC";
				}
			}
		}
		this.sortCol = sort_col;

		$("cabinet_file_list" + this.id).innerHTML = "";

		var top_el = $(this.id);
		var params = new Object();
		params["param"] = {
			"action": "cabinet_view_main_explorer_list",
			"cabinet_id": this.cabinet_id, 
			"folder_id": this.folder_id,
			"sort_col": this.sortCol,
			"sort_dir": this.sortDir,
			"success":"html"
		};
		params["top_el"] = top_el;
		params["target_el"] = $("cabinet_file_list" + this.id);
		params["callbackfunc"] = function(res) {
			var imgObj = $("cabinet_sort_img" + this.id + "_" + this.sortCol);
			if(this.sortDir == "ASC") {
				imgObj.src = this.ascImg;
			}else {
				imgObj.src = this.descImg;
			}
			commonCls.displayVisible(imgObj);
			if(this.oldSortCol != null) {
				var oldImgObj = $("cabinet_sort_img" + this.id + "_" + this.oldSortCol);
				commonCls.displayNone(oldImgObj);
				this.oldSortCol = null;
			}
		}.bind(this);
																	
		commonCls.send(params);
	},
	
	reloadExplorer: function() {
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_explorer_left", "cabinet_id":this.cabinet_id, "folder_id":this.folder_id};
		params["top_el"] = $(this.id);
		params["target_el"] = $("cabinet_explore_left" + this.id);
		commonCls.send(params);
		
		this.reloadExpList();
	},

	reloadExpList: function() {
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_explorer_right", "cabinet_id":this.cabinet_id, "folder_id":this.folder_id};
		params["top_el"] = $(this.id);
		params["target_el"] = $("cabinet_explore_right" + this.id);
		commonCls.send(params);
	},
	
	nodeClick: function(folder_id) {
		var old_folder_id = this.folder_id;
		var tree_el = $("cabinet_tree_folder" + old_folder_id + this.id);
		if (tree_el) {
			if (Element.hasClassName(tree_el, "highlight")) {
				Element.removeClassName(tree_el, "highlight");
			}
			var child_el = $("cabinet_children_folder" + old_folder_id + this.id);
			if (Element.hasClassName(child_el, "cabinet_nochild")) {
				this.switchBranch(old_folder_id);
			}
		}

		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_explorer_right", "cabinet_id":this.cabinet_id, "folder_id":folder_id};
		params["top_el"] = $(this.id);
		params["target_el"] = $("cabinet_explore_right" + this.id);
		params["callbackfunc_error"] = function(res){
			var tree_el = $("cabinet_tree_folder" + old_folder_id + this.id);
			if (tree_el) {
				if (!Element.hasClassName(tree_el, "highlight")) {
					Element.addClassName(tree_el, "highlight");
				}
			}
			this.folder_id = old_folder_id;
		}.bind(this);
		commonCls.send(params);
		
		this.folder_id = folder_id;

		var tree_el = $("cabinet_tree_folder" + this.folder_id + this.id);
		if (tree_el) {
			if (!Element.hasClassName(tree_el, "highlight")) {
				Element.addClassName(tree_el, "highlight");
			}
		}

		var child_el = $("cabinet_children_folder" + this.folder_id + this.id);
		if (child_el) {
			if (Element.hasClassName(child_el, "display-none")) {
				this.switchBranch(folder_id);
			}
		}
	},

	showAddFolder: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_cabinet";
		params["action"] = "cabinet_view_main_add_folder";
		params["cabinet_id"] = this.cabinet_id;
		params["folder_id"] = this.folder_id;
		commonCls.sendPopupView(event, params, {"top_el":$(this.id),"modal_flag":true});
	},
	addFolder: function(id, form_el) {
		var params = new Object();
		params["method"] = "post";
		params["top_el"] = $(this.id);
		params["param"] = "action=cabinet_action_main_add_folder" + 
						"&" + Form.serialize(form_el);
		params["callbackfunc"] = function(res){
			commonCls.removeBlock(this.id);
			cabinetCls[id].reloadExplorer();
		}.bind(this);
		commonCls.send(params);
	},

	showAddFile: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_cabinet";
		params["action"] = "cabinet_view_main_add_file";
		params["cabinet_id"] = this.cabinet_id;
		params["folder_id"] = this.folder_id;
		commonCls.sendPopupView(event, params, {"top_el":$(this.id),"modal_flag":true});
	},
	addFile: function(id, form_el) {
		var top_el = $(this.id);

		var params = new Object();
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params["param"] = new Object();
		params["param"]["action"] = "cabinet_action_main_add_file";
		params["param"]["unique_id"] = cabinetCls[id].cabinet_id;
		params["param"]["cabinet_id"] = cabinetCls[id].cabinet_id;
		params["param"]["folder_id"] = cabinetCls[id].folder_id;
		params["param"]["comment"] = form_el.comment.value;
		params["callbackfunc"] = function(file_list, res){
			commonCls.removeBlock(this.id);
			cabinetCls[id].reloadExpList();
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	
	modifyFile: function(id, form_el) {
		var params_str = "action=cabinet_action_main_modify" + 
						"&" + Form.serialize(form_el);
		
		var file_type = form_el.file_type.value;
		var params = new Object();

		if (file_type == this.CABINET_FILETYPE_FOLDER) {
			params["callbackfunc"] = function(res){
				commonCls.removeBlock(this.id);
				cabinetCls[id].reloadExplorer();
			}.bind(this);
		} else {
			params["callbackfunc"] = function(res){
				commonCls.removeBlock(this.id);
				cabinetCls[id].reloadExpList();
			}.bind(this);
		}
		commonCls.sendPost(this.id, params_str, params); 
	},

	delFile: function(file_id, file_type, confirm_mes, error_reload) {
		if (!commonCls.confirm(confirm_mes)) { return false; }

		var params_str = "action=cabinet_action_main_delete" + 
						"&cabinet_id=" + this.cabinet_id + 
						"&file_id=" + file_id;

		var params = new Object();
		if (file_type == this.CABINET_FILETYPE_FOLDER) {
			params["callbackfunc"] = function(res){
				this.reloadExplorer();
			}.bind(this);
		} else {
			params["callbackfunc"] = function(res){
				this.reloadExpList();
			}.bind(this);
		}

		if (error_reload) {
			params["callbackfunc_error"] = function(res){
				commonCls.alert(res);
				this.reloadExpList();
			}.bind(this);
		}
		commonCls.sendPost(this.id, params_str, params); 
		return true;
	},

	moveFile: function(file_id, folder_id, file_type, confirm_mes, error_reload) {
		if (file_id == folder_id) {
			return false;
		}
		if (!commonCls.confirm(confirm_mes)) { return false; }
		
		var params_str = "action=cabinet_action_main_move" + 
						"&cabinet_id=" + this.cabinet_id + 
						"&folder_id=" + folder_id + 
						"&file_id=" + file_id;
		
		var params = new Object();
		if (file_type == this.CABINET_FILETYPE_FOLDER) {
			params["callbackfunc"] = function(res){
				this.reloadExplorer();
			}.bind(this);
		} else {
			params["callbackfunc"] = function(res){
				this.reloadExpList();
			}.bind(this);
		}

		if (error_reload) {
			params["callbackfunc_error"] = function(res){
				commonCls.alert(res);
				this.reloadExpList();
			}.bind(this);
		}
		commonCls.sendPost(this.id, params_str, params); 
		return true;
	},

	compressFile: function(file_id, confirm_mes, error_reload) {
		if (!commonCls.confirm(confirm_mes)) { return false; }

		var top_el = $(this.id);
		if (this.compress_download == "1") {
			var queryParams = commonCls.getParams(top_el);
			if (queryParams) {
				var parameter = "";
				if (queryParams['room_id']) parameter += "&room_id=" + queryParams["room_id"];
				if (queryParams['block_id']) parameter += "&block_id=" + queryParams["block_id"];
				if (queryParams['module_id']) parameter += "&module_id=" + queryParams["module_id"];
				parameter += "&_token=" + commonCls.getToken(top_el);
			}
			if (browser.isIE) {
				var url_str = "./?action=cabinet_action_main_compress&download=1&cabinet_id=" + this.cabinet_id + "&folder_id=" + this.folder_id + "&file_id=" + file_id + parameter;
				var compress_download_link = $("cabinet_compress_download_link" + this.id);
				compress_download_link.href = url_str;
				compress_download_link.click();
			} else {
				window.open("./?action=cabinet_action_main_compress&download=1&cabinet_id=" + this.cabinet_id + "&folder_id=" + this.folder_id + "&file_id=" + file_id + parameter, "_blank");
			}
			this.reloadExpList();
		} else {
			var params = new Object();
			params["param"] = {"action":"cabinet_action_main_compress", 
										"cabinet_id":this.cabinet_id, 
										"folder_id":this.folder_id, 
										"file_id":file_id
									};
			params["method"] = "get";
			params["loading_el"] = top_el;
			params["top_el"] = top_el;
			params["callbackfunc"] = function(res){
				this.reloadExplorer();
			}.bind(this);

			if (error_reload) {
				params["callbackfunc_error"] = function(res){
					commonCls.alert(res);
					this.reloadExpList();
				}.bind(this);
			}
			commonCls.send(params);
		}
		return true;
	},

	decompressFile: function(file_id, confirm_mes, error_reload) {
		if (!commonCls.confirm(confirm_mes)) { return false; }
		
		var params_str = "action=cabinet_action_main_decompress" + 
						"&cabinet_id=" + this.cabinet_id + 
						"&folder_id=" + this.folder_id + 
						"&file_id=" + file_id;
		
		var params = new Object();
		params["callbackfunc"] = function(res){
			this.reloadExplorer();
		}.bind(this);

		if (error_reload) {
			params["callbackfunc_error"] = function(res){
				commonCls.alert(res);
				this.reloadExpList();
			}.bind(this);
		}
		commonCls.sendPost(this.id, params_str, params); 
		return true;
	},

	/* 
	 * Function of context menu
	 */
	showContext: function(el, file_id) {
		var top_el = $(this.id);
		this.context_el = el;

		if (this.popupContext == null || !$(this.popupContext.popupID)) {
			this.popupContext = new compPopup(this.id, "cabinet_context" + this.id);
		}
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_context", "cabinet_id":this.cabinet_id, "file_id":file_id};
		params["callbackfunc"] = function(res){
			this.popupContext.showPopup(res, this.context_el);
			setTimeout(this._popupObserve.bindAsEventListener(this), 0);
		}.bind(this);
		params["top_el"] = top_el;
		commonCls.send(params);
	},
	_popupObserve: function(event) {
		if (!this.popupContext.popupElement.contentWindow.document.body) {
			setTimeout(this._popupObserve.bindAsEventListener(this), 0);
			return;
		}
		var aList = this.popupContext.popupElement.contentWindow.document.getElementsByTagName("a");
		for (var i = 0; i < aList.length; i++) {
			Event.observe(aList[i], "mouseover", this._mouseoverContext.bind(this), false, $(this.id));
			Event.observe(aList[i], "mouseout", this._mouseoutContext.bind(this), false, $(this.id));
		}
	},
	_mouseoverContext: function(event) {
		var event_el = Event.element(event);
		if (event_el.tagName == "A") {
			var el = Element.getChildElement(event_el);
		} else {
			var el = event_el;
		}
		if (!Element.hasClassName(el, "contextHighlight")) {
			Element.addClassName(el, "contextHighlight");
		}
	},
	_mouseoutContext: function(event) {
		var event_el = Event.element(event);
		if(event_el.tagName == "A") {
			var el = Element.getChildElement(event_el);
		} else {
			var el = event_el;
		}
		if(Element.hasClassName(el,"contextHighlight")) {
			Element.removeClassName(el, "contextHighlight");
		}
	},
	actionContext: function(action_type, file_id, file_type, file_name, reference) {
		var params = new Object();
		switch (action_type) {
			case "edit":
				params["prefix_id_name"] = "popup_cabinet";
				params["action"] = "cabinet_view_main_modify";
				params["cabinet_id"] = this.cabinet_id;
				params["file_id"] = file_id;

				var offset = Position.positionedOffset(this.context_el);
				var sendparams = new Object();
				sendparams["top_el"] = $(this.id);
				sendparams['x'] = offset[0];
				sendparams['y'] = offset[1];
				sendparams['modal_flag'] = true;

				commonCls.sendPopupView(null, params, sendparams);
				break;
			case "delete":
				var confirm_mes = this.messages["del_confirm"].replace("%s", file_name);
				this.delFile(file_id, file_type, confirm_mes);
				break;
			case "move":
				params["prefix_id_name"] = "popup_cabinet";
				params["action"] = "cabinet_view_main_move";
				params["cabinet_id"] = this.cabinet_id;
				params["file_id"] = file_id;

				var offset = Position.positionedOffset(this.context_el);
				var sendparams = new Object();
				sendparams["top_el"] = $(this.id);
				sendparams['x'] = offset[0];
				sendparams['y'] = offset[1];
				sendparams['modal_flag'] = true;

				commonCls.sendPopupView(null, params, sendparams);
				break;
			case "compress":
			case "compress_download":
				var confirm_mes = this.messages["compress_confirm"].replace("%s", file_name);
				this.compressFile(file_id, confirm_mes);
				break;
			case "decompress":
				var confirm_mes = this.messages["decompress_confirm"].replace("%s", file_name);
				this.decompressFile(file_id, confirm_mes);
				break;
			case "property":
				params["prefix_id_name"] = "popup_cabinet_property" + file_id;
				params["action"] = "cabinet_view_main_property";
				params["cabinet_id"] = this.cabinet_id;
				params["file_id"] = file_id;
				params["theme_name"] = "system";

				var sendparams = new Object();
				sendparams["top_el"] = $(this.id);
				sendparams['x'] = 0;
				sendparams['y'] = 0;

				if (reference == "1") {
					var absolute_el = $(this.id).parentNode;
					var offset = Position.positionedOffset(absolute_el);
					sendparams['x'] += offset[0];
					sendparams['y'] += offset[1];
				}

				var offset = Position.positionedOffset(this.context_el);
				sendparams['x'] += offset[0];
				sendparams['y'] += offset[1];

				commonCls.sendPopupView(null, params, sendparams);
				break;
			default:
		}
		this.popupContext.closePopup();
	},

	/* 
	 * Function of property
	 */
	propertyAddress: function(cabinet_id, folder_id) {
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_address", "cabinet_id":cabinet_id, "folder_id":folder_id};
		params["top_el"] = $(this.id);
		params["target_el"] = $("cabinet_property_address" + this.id);
		commonCls.send(params);
	},
	propertySize: function(cabinet_id, file_id) {
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_size", "cabinet_id":cabinet_id, "file_id":file_id};
		params["top_el"] = $(this.id);
		params["target_el"] = $("cabinet_property_size" + this.id);
		commonCls.send(params);
	},

	/* 
	 * Function of movement by the popup
	 */
	popupMoveSelect: function(folder_id) {
		var old_folder_id = this.folder_id;
		var tree_el = $("cabinet_tree_folder" + old_folder_id + this.id);
		if (tree_el) {
			if (Element.hasClassName(tree_el, "highlight")) {
				Element.removeClassName(tree_el, "highlight");
			}
			var child_el = $("cabinet_children_folder" + old_folder_id + this.id);
			if (Element.hasClassName(child_el, "cabinet_nochild")) {
				this.switchBranch(old_folder_id);
			}
		}
		
		this.folder_id = folder_id;

		var tree_el = $("cabinet_tree_folder" + this.folder_id + this.id);
		if (tree_el) {
			if (!Element.hasClassName(tree_el, "highlight")) {
				Element.addClassName(tree_el, "highlight");
			}
		}

		var child_el = $("cabinet_children_folder" + this.folder_id + this.id);
		if (child_el) {
			if (Element.hasClassName(child_el, "display-none")) {
				this.switchBranch(folder_id);
			}
		}

		var form_el = $("cabinet_form" + this.id);
		form_el.regist.disabled = false;
	},
	popupMoveFile: function(id) {
		var confirm_mes = this.messages["move_confirm"].replace("%s", this.movement["file_name"]);
		cabinetCls[id].moveFile(this.movement["file_id"], this.folder_id, this.movement["file_type"], confirm_mes);
		commonCls.removeBlock(this.id);
	},

	/* 
	 * Function of drag and drop
	 */
	initDragAndDrop: function() {
		this.cabinetDragAndDrop = new compDragAndDrop();
		this.cabinetDragAndDrop.registerDraggableRange($(this.id));

		this.folderDropzone = Class.create();
		this.folderDropzone.prototype = Object.extend((new compDropzone), {
			save: function(draggableObjects) {
				var cabinetCls = this.getParams();
				var drag_el = draggableObjects[0].getMouseDownHTMLElement();
				var file_id = drag_el.className.match(/cabinet_draggable[0-9]+/i)[0].replace("cabinet_draggable", "");
				var file_type = drag_el.className.match(/cabinet_file_type[0-9]+/i)[0].replace("cabinet_file_type", "");
				var file_name = drag_el.nextSibling.title;
				
				var drop_el = this.getHTMLElement();
				var folder_id = drop_el.className.match(/cabinet_dropzone[0-9]+/i)[0].replace("cabinet_dropzone", "");
				
				var confirm_mes = cabinetCls.messages["move_confirm"].replace("%s", file_name);
				return cabinetCls.moveFile(file_id, folder_id, file_type, confirm_mes, 1);
   			}
		});
		
		this.trashDropzone = Class.create();
		this.trashDropzone.prototype = Object.extend((new compDropzone), {
			save: function(draggableObjects) {
				var cabinetCls = this.getParams();
				var drag_el = draggableObjects[0].getMouseDownHTMLElement();
				var file_id = drag_el.className.match(/cabinet_draggable[0-9]+/i)[0].replace("cabinet_draggable", "");
				var file_type = drag_el.className.match(/cabinet_file_type[0-9]+/i)[0].replace("cabinet_file_type", "");
				var file_name = drag_el.nextSibling.title;

				var confirm_mes = cabinetCls.messages["del_confirm"].replace("%s", file_name);
				return cabinetCls.delFile(file_id, file_type, confirm_mes, 1);
   			}
		});
		this.cabinetDragAndDrop.registerDropZone(new this.trashDropzone($("cabinet_trash_box" + this.id), this));

		this.compressDropzone = Class.create();
		this.compressDropzone.prototype = Object.extend((new compDropzone), {
			save: function(draggableObjects) {
				var cabinetCls = this.getParams();
				var drag_el = draggableObjects[0].getMouseDownHTMLElement();
				var file_id = drag_el.className.match(/cabinet_draggable[0-9]+/i)[0].replace("cabinet_draggable", "");
				var file_name = drag_el.nextSibling.title;

				var confirm_mes = cabinetCls.messages["compress_confirm"].replace("%s", file_name);
				return cabinetCls.compressFile(file_id, confirm_mes, 1);
   			}
		});
		this.cabinetDragAndDrop.registerDropZone(new this.compressDropzone($("cabinet_compression" + this.id), this));

		this.decompressDropzone = Class.create();
		this.decompressDropzone.prototype = Object.extend((new compDropzone), {
			save: function(draggableObjects) {
				var cabinetCls = this.getParams();
				var drag_el = draggableObjects[0].getMouseDownHTMLElement();
				var file_id = drag_el.className.match(/cabinet_draggable[0-9]+/i)[0].replace("cabinet_draggable", "");
				var file_name = drag_el.nextSibling.title;
				
				var extensions = cabinetCls.messages["decompress_files"].split("|");
				var ok = false;
				var filename_length = file_name.length;
				for (var i=0; i<extensions.length; i++) {
					var extension = extensions[i];
					if (extension == file_name.substr(filename_length - extension.length)) {
						var ok = true;
						break;
					}
				}
				if (!ok) { return false; }

				var confirm_mes = cabinetCls.messages["decompress_confirm"].replace("%s", file_name);
				return cabinetCls.decompressFile(file_id, confirm_mes, 1);
   			}
		});
		this.cabinetDragAndDrop.registerDropZone(new this.decompressDropzone($("cabinet_decompression" + this.id), this));
	},

	setDragAndDrop: function(id) {
		var fields = Element.getElementsByClassName($(id), "cabinet_draggable");
		fields.each(function(el) {
			this.cabinetDragAndDrop.registerDraggable(new compDraggable(el.nextSibling, el));
		}.bind(this));
		
		var fields = Element.getElementsByClassName($(id), "cabinet_dropzone");
		fields.each(function(el) {
			this.cabinetDragAndDrop.registerDropZone(new this.folderDropzone(el, this));
		}.bind(this));
	},


	/* 
	 * Function of cabinet list
	 */
	initCabList: function(line_num, count) {
		if (count == 0) { return; }
		var opts = {
			prefetchBuffer : false,
			sort : true,
			requestParameters:new Array("scroll=1"),
			onSendCallback:function(res) {this.checkCurrent();}.bind(this)
		};
		this.gridList = new compLiveGrid(this.id, line_num, count, "cabinet_view_edit_list", opts);
	},
	checkCurrent: function() {
		var currentRow = $("cabinet_current_row" + this.cabinet_id + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("cabinet_current" + this.cabinet_id + this.id);
		current.checked = true;
	},
	changeCurrent: function(cabinet_id) {
		var oldCurrentRow = $("cabinet_current_row" + this.cabinet_id + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}
		
		this.cabinet_id = cabinet_id;
		var currentRow = $("cabinet_current_row" + this.cabinet_id + this.id);
		Element.addClassName(currentRow, "highlight");
		
		var post = {
			"action":"cabinet_action_edit_current",
			"cabinet_id":cabinet_id
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "cabinet_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	referCab: function(event, cabinet_id){
		var params = new Object();
		params["action"] = "cabinet_view_main_init";
		params["prefix_id_name"] = "popup_cabinet_reference" + cabinet_id;
		params["cabinet_id"] = cabinet_id;
		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},
	modifyCab: function(cabinet_id) {
		commonCls.sendView(this.id, {"action":"cabinet_view_edit_modify", "cabinet_id":cabinet_id}); 
	},
	deleteCab: function(cabinet_id, confirm_mes) {
		if (!commonCls.confirm(confirm_mes)) { return; }
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "cabinet_view_edit_list");
		}.bind(this);
		params["target_el"] = $(this.id);
		commonCls.sendPost(this.id, "action=cabinet_action_edit_delete&cabinet_id=" + cabinet_id, params); 
	},

	/* 
	 * The function of cabinet creation and cabinet modification
	 */
	registCab: function(form_el, action_name) {
		commonCls.sendPost(this.id, "action=" + action_name + "&" + Form.serialize(form_el), {"target_el":$(this.id)}); 
	},
	
	
	changeStyle: function(form_el) {
		commonCls.sendPost(this.id, Form.serialize(form_el), {"target_el":$(this.id)}); 
	},
	
	
	setOffsetHeight: function(disp_folder) {
		var params = new Object();
		params["param"] = {"action":"cabinet_view_main_explorer_list", "cabinet_id":this.cabinet_id, "folder_id":this.folder_id, "success":"html"};
		params["top_el"] = $(this.id);
		params["target_el"] = $('cabinet_file_list' + this.id);
		if (disp_folder == 1) {
			params["callbackfunc"] = function(res){
				if ($('cabinet_explore_folder_tree' + this.id).offsetHeight < $('cabinet_file_list' + this.id).offsetHeight) {
					$('cabinet_explore_folder_tree' + this.id).style.height = $('cabinet_file_list' + this.id).offsetHeight + "px";
					this.folder_height = $('cabinet_file_list' + this.id).offsetHeight;
				}
			}.bind(this);
		}
		commonCls.send(params);
	}
	
}
