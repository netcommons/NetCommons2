var clsMenu = Class.create();
var menuCls = Array();

clsMenu.prototype = {
	initialize: function(id) {
		this.id = id;
		this.form = null;
		//this.header_fields = null;
		//this.header_disabled_fields = null;
		//this.menuRefreshFlag = 0;		//リネーム処理と、遷移処理が同時に起こった場合、編集画面へ遷移しなくなることがあるため修正：firefox
		this.menuLodingFlag = true;	//連続クリック防止
		this.url = null;
		this.center_flag = false;
		this.dndMgrMenuObj = new Object();
		this.dndCustomDrag = null;
		this.dndCustomDropzone = null;
		this.margin_left = 20;

		this.inRenameFlag = false;
		this.flat_flag = false;
	},
	menuMainInit: function() {
		//this.header_fields = null;
	},
	menuEditInit: function(center_flag,margin_left, flat_flag) {
		var top_el = $(this.id);
		this.flat_flag = flat_flag;
		this.form = top_el.getElementsByTagName("form")[0];
		var input = this.form.page_name;
		setTimeout(this.menuRenameFocus.bind(this),0);
		this._editObserver(input);
		//this.header_fields = top_el.getElementsByClassName("_menu_header_btn");
		//this.header_disabled_fields = top_el.getElementsByClassName("_menu_header_disabled_btn");

		this.center_flag = center_flag;
		this.margin_left = margin_left;
		//this.menuRefreshFlag = true;
		/*
		var top_el = $(this.id);
		this.form = top_el.getElementsByTagName("form")[0];
		this.fields = this.form.getElementsByClassName("_menutop");
		this.fields.each(function(field) {
			var field = field.parentNode;
			var enterModes = new compEnterMode(this.id);
			var enter_el = Element.getChildElementByClassName(top_el,"_menu_rename");
			enterModes.submitUse = false;
			enterModes.enterEvent = "dblclick";
			enterModes.setDisplay(field, this._enterModeInit.bindAsEventListener(this));
			enterModes.setCloneEnter(enter_el, this._cloneEnterInit.bindAsEventListener(this));
		}.bind(this));
		*/

		////this.dndMgrMenu = new compDragAndDrop();
		// ドラッグカスタム
		this.dndCustomDrag = Class.create();
		this.dndCustomDrag.prototype = Object.extend((new compDraggable), {
			prestartDrag: function()
			{
				//カテゴリで子供がいるならば、子供を閉じる処理を入れる
				var draggable = this.htmlElement;
				var next_el = draggable.nextSibling;
				if(next_el && next_el.id.match("_menu_")) {
					Element.addClassName(next_el,"display-none");
				}
			},
			endDrag: function() {
		      var draggable = this.htmlElement;
		      Element.setStyle(draggable, {opacity:""});

		      var drag_params = this.getParams();
			  var parent_el = $(drag_params[1]);
			  if(parent_el && parent_el.innerHTML=="") {
				Element.remove(parent_el);
			  }

		   }
		});
		// ドロップカスタム
		this.dndCustomDropzone = Class.create();
		this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
			canAccept: function(draggableObjects) {
      			var theGUI = draggableObjects[0].getDroppedGUI();
      			var htmlElement = this.getHTMLElement();
      			var id_arr = theGUI.id.split("_");
				var parent_id = id_arr[3];
      			if((parent_id == "1" && Element.hasClassName(theGUI, "_menu_sub_group")) && valueParseInt(theGUI.style.marginLeft) != valueParseInt(htmlElement.style.marginLeft)) {
      				// パブリック内ルーム
      				return false;
      			}
      			return true;
   			},
			showHover: function(event) {

				var htmlElement = this.getHTMLElement();
				var id_arr = htmlElement.id.split("_");
				var room_id = id_arr[3];

				var subgroup_flag = false;
				var current_drop_el = Element.getParentElementByClassName(Event.element(event), "menu_row_top");
				if(Element.hasClassName(current_drop_el, "_menu_sub_group")) {
					subgroup_flag = true;
				}

				var child_el = Element.getChildElement(htmlElement);

				var node_type_el = Element.getChildElementByClassName(htmlElement,"_menu_node_type");
				if(Element.hasClassName(node_type_el, "menu_node") && room_id != "top" && room_id != "group" && subgroup_flag != true) {
					//カテゴリ
					if ( this._showHover(child_el) )
						return;
					var offset = Position.cumulativeOffset(htmlElement);
			 		var ex = offset[0];
			 		var ey = offset[1];
			 		var part_height = (htmlElement.offsetHeight/5);

			 		//var center_y = ey + (htmlElement.offsetHeight/2);
			 		var y = Event.pointerY(event);

			 		if(y < ey + part_height * 2) {
			 			//上
			 			this.ChgSeqPosition = "top";
			 			var top_px = ey  + "px";
			 		} else if(y > ey + part_height * 3) {
			 			//下
			 			this.ChgSeqPosition = "bottom";
			 			var top_px = (ey + htmlElement.offsetHeight)  + "px";
			 		} else {
			 			//カテゴリ内
			 			this.ChgSeqPosition = "inside";
			 			if(this.ChgSeqHover) {
			 				Element.remove(this.ChgSeqHover);
							this.ChgSeqHover = null;
						}
			 			child_el.style.backgroundColor = "#ffff99";
			 		}
			 		if(this.ChgSeqPosition !=  "inside") {
			 			if(this.ChgSeqHover == undefined || this.ChgSeqHover == null) {
			 				this._hideHover(child_el);
				 			this.ChgSeqHover = document.createElement("DIV");
				 		}
				 		document.body.appendChild(this.ChgSeqHover);
				 		this.ChgSeqHover.style.width = htmlElement.offsetWidth + "px";
				 		this.ChgSeqHover.style.height = "1px"; //固定
				 		this.ChgSeqHover.style.position = "absolute";
				 		this.ChgSeqHover.style.left = ex  + "px";

				 		this.ChgSeqHover.style.top = top_px;
				 		this.ChgSeqHover.style.borderTop = "3px";
						this.ChgSeqHover.style.borderTopStyle = "solid";
						this.ChgSeqHover.style.borderTopColor = "#ffff00";
			 		}
				} else {
					//ページ
					this.showChgSeqHover(event);
				}
			},
			hideHover: function(event)
			{
				var htmlElement = this.getHTMLElement();
				var child_el = Element.getChildElement(htmlElement);
				if ( this._hideHover(child_el) )
					return;
				if(this.ChgSeqHover) {
					Element.remove(this.ChgSeqHover);
					this.ChgSeqHover = null;
				}
			},
			accept: function(draggableObjects)
			{
				var params = this.getParams();
				var top_id = params[0];

				var htmlElement = this.getHTMLElement();
				if ( htmlElement == null )
					return;
				var n = draggableObjects.length;
				for ( var i = 0 ; i < n ; i++ ) {
					var theGUI = draggableObjects[i].getDroppedGUI();
					if ( Element.getStyle( theGUI, "position" ) == "absolute" ) {
			            theGUI.style.position = "static";
			            theGUI.style.top = "";
			            theGUI.style.left = "";
			        }

			        var margin_left = params[1];

			        var id_arr = theGUI.id.split("_");
					var page_id = id_arr[2];


					if(this.ChgSeqPosition == "inside") {
						var margin_px = (valueParseInt(htmlElement.style.marginLeft) + margin_left);
					} else {
						var margin_px = valueParseInt(htmlElement.style.marginLeft);
					}
					theGUI.style.marginLeft = margin_px + "px";

			        var doc = document.createDocumentFragment();
			        doc.appendChild(theGUI);
			        var child_el = $("_menu_"+page_id+top_id);
					if(child_el) {
			        	//移動元、子供あり
			        	var child_parent_el = child_el.parentNode;
			        	this._setMargin(child_el, margin_px, margin_left);
			        	doc.appendChild(child_el);
			        	if(child_parent_el && child_parent_el.innerHTML=="") {
							Element.remove(child_parent_el);
						}
			        }

			        if(this.ChgSeqPosition == "top") {
						htmlElement.parentNode.insertBefore(doc, htmlElement);
					} else if(this.ChgSeqPosition == "bottom"){
						var next_el = htmlElement.nextSibling;
						if(next_el && next_el.id.match("_menu_")) {
							//子供が存在
							next_el = next_el.nextSibling;
						}
						if(!next_el) {
							htmlElement.parentNode.appendChild(doc);
						} else {
							next_el.parentNode.insertBefore(doc, next_el);
						}
					} else {
						var next_el = htmlElement.nextSibling;
						if(next_el && next_el.id.match("_menu_")) {
							next_el.appendChild(doc);
							if(next_el.innerHTML != "") {
								Element.removeClassName(next_el,"display-none");
							}
						} else {
							var id_arr = htmlElement.id.split("_");
							var page_id = id_arr[2];
							var div = document.createElement("DIV");
							div.id = "_menu_"+page_id+top_id;
							div.className = "_menu_"+page_id+top_id;
							var next_el = htmlElement.nextSibling;
							if(!next_el) {
								htmlElement.parentNode.appendChild(div);
							} else {
								next_el.parentNode.insertBefore(div, next_el);
							}
							div.appendChild(doc);
						}
					}
				}
			},
			_setMargin: function(child_el, margin_px, margin_left) {
				if(child_el) {
		        	//移動元、子供あり
		        	for (var i = 0,child_len=child_el.childNodes.length; i < child_len; i++) {
		        		if(!child_el.childNodes[i].id.match("_menu_")) {
		        			//margin設定
		        			child_el.childNodes[i].style.marginLeft = (margin_px + margin_left) + "px";
		        		} else {
		        			this._setMargin(child_el.childNodes[i], margin_px, margin_px + margin_left);
		        		}
		        	}
		        }
			},
			save: function(draggableObjects) {
				if(this.ChgSeqPosition == null) {
					return false;
				}
				var params = this.getParams();
			    var id = params[0];
			    var top_el = $(id);

				var drag_el = draggableObjects[0].getHTMLElement();				// ドラッグ中エレメント
				var id_arr = drag_el.id.split("_");
				var drag_page_id = id_arr[2];

				var htmlElement = this.getHTMLElement();						// ドロップ先エレメント
				var id_arr = htmlElement.id.split("_");
				var drop_page_id = id_arr[2];

				var chgseq_params = new Object();								// パラメータ設定

				// リンク表示順変更
				chgseq_params["param"] = {"action":"menu_action_edit_chgseq", "drag_page_id":drag_page_id,
										"drop_page_id":drop_page_id, "position":this.ChgSeqPosition};
				chgseq_params["callbackfunc_error"] = function(res){
					commonCls.alert(res);
					location.reload();
				}.bind(this);
				chgseq_params["method"] = "post";
				chgseq_params["top_el"] = top_el;
				commonCls.send(chgseq_params);

				return true;
			}
		});
		this.dndMgrMenuObj = new Object();
		//ドラッグできる範囲指定 指定el内でしか移動できない
		var range_el = $("_menu_range"+this.id);
		//this.dndMgrMenu.registerDraggableRange(range_el);
		var menu_rowfields = Element.getElementsByClassName(range_el, "menu_row_top");
		menu_rowfields.each(function(menu_row_el) {
			if(menu_row_el.id) {
				this._dndObserver(menu_row_el);
			}
		}.bind(this));
	},
	menuRenameFocus: function(input) {
		if(!input || input.tagName == undefined) {
			var input = this.form.page_name;
		}
		if(input) {
			input.focus();
			input.select();
		}
	},
	/*
	_enterModeInit: function(event, compEnterMode) {
		var div = compEnterMode.displayElement.getElementsByTagName("div")[0];
		var input_el = Element.getChildElement(compEnterMode.enterElement);
		input_el.value = div.innerHTML;
		input_el.focus();
		input_el.select();
		//Event.stop(event);
	},
	_cloneEnterInit: function(event, compEnterMode) {

	},
	*/
	_dndObserver: function(el) {
		//room_id取得
		var id_arr = el.id.split("_");
		var room_id = id_arr[3];
		var img_el = Element.getChildElementByClassName(el,"_menu_displayseq");
		if(img_el != null) {
			if(!this.dndMgrMenuObj[room_id]) {
				this.dndMgrMenuObj[room_id] = new compDragAndDrop();
				this.dndMgrMenuObj[room_id].registerDraggableRange(el.parentNode);
			}

			this.dndMgrMenuObj[room_id].registerDraggable(new this.dndCustomDrag(el, img_el.parentNode, new Array(this.id, el.parentNode.id)));
			this.dndMgrMenuObj[room_id].registerDropZone(new this.dndCustomDropzone(el, new Array(this.id, this.margin_left)));
		}
	},
	_editObserver: function(el) {
		if(el) {
			Event.observe(el, 'keydown', this.menuRename.bindAsEventListener(this, el) , true, $(this.id));
			Event.observe(el, 'change',  this.menuRename.bindAsEventListener(this, el), true, $(this.id)) ;
		}
	},
	menuRename: function(event, el) {
		if (!this.inRenameFlag && (event.type == "change" || event.keyCode == 13)) {
			var top_el = $(this.id);
			var parent_el = Element.getParentElement(el, 2);
			if(parent_el) {
				this.inRenameFlag = true;
			//if(parent_el && el != Event.element(event)) {
				var idName = parent_el.id;
				var page_id = parseInt(idName.replace("_menutop"+this.id+"_",""));

				var rename_params = new Object();
				//rename_params["method"] = "post";
				rename_params["method"] = "post";
				rename_params["param"] = {"action":"menu_action_edit_rename","main_page_id":page_id,"page_name": el.value};

				rename_params["callbackfunc"] = function(res){
													this.inRenameFlag = false;
													if(el) {
														if(res == "") {
															var value = el.value;
														} else {
															var value = res;
														}
														Event.stopObserving(el, "keydown",this.menuRename.bindAsEventListener(this, el),false);
														Event.stopObserving(el, "change",this.menuRename.bindAsEventListener(this, el),false);

														var text = document.createTextNode(value);
														var span_el = Element.getChildElement(parent_el);
														span_el.innerHTML = "";
														//spanタグにappend
														span_el.appendChild(text);
														//parent_el.parentNode.onclick();
														//this.menuRefreshFlag--;
														//if(this.menuRefreshFlag == 0 && this.url != null) {
														//	this.menuLodingFlag = true;
														//	////this.menuleafClick(null,this.url, page_id);
														//}
													}
												}.bind(this);
				rename_params["callbackfunc_error"] = function(res){
													//this.menuRefreshFlag--;
													this.inRenameFlag = false;
													commonCls.alert(res);
												}.bind(this);
				rename_params["top_el"] = top_el;
				//this.menuRefreshFlag++;
				commonCls.send(rename_params);
				//Event.stop(event);
			}
		}
	},
	/*メイン画面に変更*/
	menuMainShow: function() {
		commonCls.sendView(this.id,"menu_view_main_init");
	},
	/*編集画面に変更*/
	menuEditShow: function() {
		commonCls.sendView(this.id,"menu_view_edit_init");
	},
	/* ノードクリック */
	menuNodeClick: function(event, page_id, edit_flag, addpage_flag) {
		//if(Event.element(event).tagName.toLowerCase() == "input") {
		//	return true;
		//}
		var class_name = "_menu_" + page_id+this.id;
		var top_class_name = "_menutop_" + page_id;
		var top_el = $(this.id);
		var top_node_el = Element.getChildElementByClassName(top_el,top_class_name);
		var el = Element.getChildElementByClassName(top_el,class_name);

		if(edit_flag) {
			var top_node_el_href = Element.getParentElementByClassName(top_node_el, "menu_row_top");

			//Activeに変更
			this.cancelActiveBtn();
			this.addActiveBtn(top_node_el_href);
			var child_el = Element.getChildElement(top_node_el);
			var input_el = Element.getChildElement(child_el);
			var node_type_el = Element.getChildElementByClassName(top_node_el_href, "_menu_node_type");
			if(!input_el && !Element.hasClassName(child_el, "menu_lbl_disabled")) {
				var input = document.createElement("INPUT");
				input.type = "text";
				input.className = "menu_pagename_text";
				input.value = child_el.innerHTML.unescapeHTML();
				child_el.innerHTML = "";
				child_el.appendChild(input);
				this.menuRenameFocus(input);
				this._editObserver(input);
			}
			// else {
			//	this.menuRenameFocus(child_el);
			//}

		} else {
			var top_node_el_href = top_node_el;
		}

		//上部ボタン有効無効
		/*
		if(this.header_fields && addpage_flag != undefined) {
			if(!addpage_flag) {
				this.header_fields.each(function(field) {
					Element.addClassName(field,"display-none");
				});
				this.header_disabled_fields.each(function(field) {
					Element.removeClassName(field,"display-none");
				});
			} else {
				this.header_fields.each(function(field) {
					Element.removeClassName(field,"display-none");
				});
				this.header_disabled_fields.each(function(field) {
					Element.addClassName(field,"display-none");
				});
			}
		}
		*/
		if(!el) {
			//子供取得
			var detail_params = new Object();
			detail_params["method"] = "get";
			if(edit_flag) {
				var visibility_el = $("_menuvisibility"+this.id+"_" + page_id);
				if(!visibility_el || visibility_el.src == undefined) {
					var visibility_flag = 1;
				} else if(visibility_el.src.match("off.gif")) {
					//非表示中
					var visibility_flag = 0;
				} else {
					var visibility_flag = 1;
				}
				if(this.flat_flag)
					var flat_flag = 1;
				else
					var flat_flag = 0;
				detail_params["param"] = {"action":"menu_view_edit_detail","main_page_id":page_id,"visibility_flag":visibility_flag,"flat_flag":flat_flag};
			} else {
				detail_params["param"] = {"action":"menu_view_main_detail","main_page_id":page_id};
			}
			detail_params["callbackfunc"] = function(res){
												var div = document.createElement("DIV");
												div.id = "_menu_"+page_id+this.id;
												if(res == "") {
													div.className = "_menu_"+page_id+this.id+" display-none";
												} else {
													div.className = "_menu_"+page_id+this.id;
												}
												var next_el = top_node_el_href.nextSibling;
												if(!next_el) {
													top_node_el_href.parentNode.appendChild(div);
												} else {
													next_el.parentNode.insertBefore(div, next_el);
												}
												div.innerHTML = res;
												var menu_rowfields = Element.getElementsByClassName(div, "menu_row_top");
												menu_rowfields.each(function(menu_row_el) {
													if(menu_row_el.id) {
														this._dndObserver(menu_row_el);
													}
												}.bind(this));
											}.bind(this);
			detail_params["top_el"] = top_el;
			commonCls.send(detail_params);
			return;
		}

		var parent_el = Element.getParentElement(el);
		//var show_div = null;
		for (var i = 0; i < parent_el.childNodes.length; i++) {
			var div = parent_el.childNodes[i];
			if(div && div.tagName == "DIV" && Element.hasClassName(div,class_name)) {
				if(Element.hasClassName(div,"display-none")) {
					if(div.innerHTML != "") {
						Element.removeClassName(div,"display-none");
					}
					var display_flag = true;
					//show_div = div;
					break;
					//var img_src = "menu_right.gif";
				} else {
					Element.addClassName(div,"display-none");
					var display_flag = false;
					break;
					//var img_src = "menu_right.gif";
				}
			}
		}
		var img_el= top_node_el.getElementsByTagName("img")[0];
		if(img_el) {
			if(display_flag) {
				img_el.src = img_el.src.replace("right_arrow.gif","down_arrow.gif");
			} else {
				img_el.src = img_el.src.replace("down_arrow.gif","right_arrow.gif");
			}
		}
	},
	menuleafClick: function(this_el, url, page_id, edit_flag) {
		var top_table_el = Element.getParentElementByClassName(this_el, "menu_row_top");
		/*
		var top_table_el = Element.getParentElementByClassName(Event.element(event), "menu_row_top");
		if(event != undefined && event != null) {
			//var top_table_el = Element.getParentElementByClassName(Event.element(event), "menu_row_top");

			if (Event.element(event).tagName.toLowerCase() == "input" ||
				Element.hasClassName(Element.getChildElement(top_table_el),"highlight")) {

				this.menuNodeClick(event, page_id, true);

				return;
			}
		}
		*/
		var top_class_name = "_menutop_" + page_id;
		var top_el = $(this.id);
		var top_node_el = Element.getChildElementByClassName(top_el,top_class_name);

		//Activeに変更
		var child_el = Element.getChildElement(top_table_el);
		if(edit_flag) {
			if(Element.hasClassName(child_el,"highlight")) {
				//既にハイライト
				var highlight_flag = true;
			} else {
				var highlight_flag = false;
				this.cancelActiveBtn();
				this.addActiveBtn(top_table_el);
			}
		}
		var child_el = Element.getChildElement(top_node_el);
		var input_el = Element.getChildElement(child_el);
		if(!input_el && edit_flag) {
			var input = document.createElement("INPUT");
			input.type = "text";
			input.value = child_el.innerHTML.unescapeHTML();;
			child_el.innerHTML = "";
			child_el.appendChild(input);
			this.menuRenameFocus(input);
			this._editObserver(input);
		}

		//上部ボタン有効
		/*
		if(this.header_fields) {
			this.header_fields.each(function(field) {
				Element.removeClassName(field,"display-none");
			});
			this.header_disabled_fields.each(function(field) {
				Element.addClassName(field,"display-none");
			});
		}
		*/
		this.url = url;
	},
	/* 編集画面：メニュークリック
	menuEditClick: function(event, page_id) {
		var top_el = $(this.id);
		var event_el = Event.element(event);
		if(event_el && event_el.tagName == "IMG") {
			event_el = Element.getParentElement(event_el);
		}
		if(Element.hasClassName(event_el,"menu_edit_skeleton")) {
			Element.removeClassName(event_el,"menu_edit_skeleton");
			var visibility_flag = 1;
		} else {
			Element.addClassName(event_el,"menu_edit_skeleton");
			var visibility_flag = 0;
		}
		var ins_params = new Object();
		ins_params["method"] = "post";
		ins_params["param"] = {"action":"menu_action_edit_detail","menu_page_id":page_id,"visibility_flag":visibility_flag};
		ins_params["top_el"] = top_el;
		ins_params["match_str"] = "";
		commonCls.send(ins_params);
	},
	 */
	/*Activeボタンの色を変更*/
	addActiveBtn: function(el) {
		var child_el = Element.getChildElement(el);
		if(!Element.hasClassName(child_el,"highlight")) {
			Element.addClassName(child_el,"highlight");
			this._chgImage(child_el, true);
		}
	},
	cancelActiveBtn: function() {
		var active_el = this._searchActiveBtn();
		if(!active_el) return;
		var child_el = Element.getChildElement(active_el);
		if(Element.hasClassName(child_el,"highlight")) {
			Element.removeClassName(child_el,"highlight");
			this._chgImage(child_el);
		}
	},
	_searchActiveBtn: function() {
		var top_el = $(this.id);
		var tableList = top_el.getElementsByTagName("table");
		for (var i = 0; i < tableList.length; i++){
			if(Element.hasClassName(tableList[i],"highlight")) {
				return tableList[i].parentNode;
			}
		}
	},
	_chgImage: function (el, active_flag) {
		var img_el = Element.getChildElementByClassName(el, "_menu_displayseq");
		if(img_el) {
			if(active_flag) {
				img_el.src = img_el.src.replace("move_bar.gif","move_bar_active.gif");
			} else {
				img_el.src = img_el.src.replace("move_bar_active.gif","move_bar.gif");
			}
		}
	},
	insPage: function(category_flag) {
		var active_el = this._searchActiveBtn();

		var node_type_el = Element.getChildElementByClassName(active_el, "_menu_node_type");
		if(Element.hasClassName(node_type_el, "menu_node") || Element.hasClassName(node_type_el, "menu_room")) {
			var node_flag = true;
		} else {
			var node_flag = false;
		}

		//if(!Element.hasClassName(active_el,"highlight")) {
		//	active_el = active_el.parentNode.previousSibling;
		//}

		var href_el = Element.getChildElementByClassName(active_el, "_menutop");
		var div_el = Element.getChildElement(href_el);
		var idName = href_el.id;
		var page_id = parseInt(idName.replace("_menutop"+this.id+"_",""));
		var top_el = $(this.id);

		if(node_flag) {
			var visibility_el = $("_menuvisibility"+this.id+"_" + page_id);
			if(!visibility_el) {
				// 追加権限なし
				return;
			} else if(visibility_el.src != undefined && visibility_el.src.match("off.gif")) {
				//非表示中
				var id_arr = active_el.id.split("_");
				var room_id = id_arr[3];
				var space_type = id_arr[4];
				if(this.flat_flag == true && space_type == 1) {
					if(!this.chkVisibility(active_el)) {
						//非表示中
						var visibility_flag = 0;
					} else {
						var visibility_flag = 1;
					}
				} else
					var visibility_flag = 0;
			} else {
				var visibility_flag = 1;
			}
		} else {
			if(!this.chkVisibility(active_el)) {
				//非表示中
				var visibility_flag = 0;
			} else {
				var visibility_flag = 1;
			}
		}

		var ins_params = new Object();
		ins_params["method"] = "post";
		if(category_flag) {
			ins_params["param"] = {"action":"menu_action_edit_addpage","main_page_id":page_id, "node_flag":"1", "visibility_flag":visibility_flag};
		} else {
			ins_params["param"] = {"action":"menu_action_edit_addpage","main_page_id":page_id, "visibility_flag":visibility_flag};
		}
		ins_params["top_el"] = top_el;
		ins_params["callbackfunc"] = function(res){
			if(res == "") {
				return;
			}
			if(node_flag) {
				var next_el = active_el.nextSibling;
				var id_name = "_menu_"+page_id+this.id;
			} else {
				var next_el = active_el.parentNode;
				var id_name = next_el.id;
			}
			if(next_el && next_el.id == id_name) {
				var div = document.createElement("DIV");
				next_el.appendChild(div);
				div.innerHTML = res;
				var new_el = Element.getChildElement(div);
				var observe_el = Element.getChildElement(div);
				next_el.appendChild(observe_el);
				Element.remove(div);
				if(next_el.innerHTML != "") {
					Element.removeClassName(next_el,"display-none");
				}
			} else {
				var div = document.createElement("DIV");
				div.id = "_menu_"+page_id+this.id;
				div.className = "_menu_"+page_id+this.id;
				if(!next_el) {
					active_el.parentNode.appendChild(div);
				} else {
					next_el.parentNode.insertBefore(div, next_el);
				}
				div.innerHTML = res;
				var new_el = Element.getChildElement(div);
				var observe_el = Element.getChildElement(div);
			}
			var new_href_el = Element.getChildElementByClassName(new_el, "_menutop");
			////if(category_flag) {
				new_href_el.onclick();
			////}
			var input = new_href_el.getElementsByTagName("input")[0];
			this.menuRenameFocus(input);
			this._editObserver(input);
			//Drag&Dropイベント追加
			this._dndObserver(observe_el);
		}.bind(this);
		commonCls.send(ins_params);
	},
	delPage: function(page_id, id_name) {
		//ページ検索
		//var top_node_el = Element.getParentElementByClassName(node_el, "menu_row_top");
		//var href_el = Element.getChildElementByClassName(active_el, "_menutop");
		///var ins_params = new Object();
		var top_el = $(this.id);

		var del_params = new Object();
		del_params["method"] = "post";
		del_params["param"] = {"action":"menu_action_edit_deletepage","main_page_id":page_id};
		del_params["top_el"] = top_el;
		del_params["callbackfunc"] = function(res){
			if(res != "true") {
				location.href = res;
			} else {
				var del_el = $(id_name);
				var parent_el = del_el.parentNode.previousSibling;
				this.cancelActiveBtn();
				this.addActiveBtn(parent_el);
				Element.remove(del_el);
				var child_el = $("_menu_" + page_id+this.id);
				if(child_el) {
					var parent_el = child_el.parentNode;
					Element.remove(child_el);
					if(parent_el.innerHTML=="") {
						Element.remove(parent_el);
					}
				}
			}
		}.bind(this);
		commonCls.send(del_params);
	},
	chkVisibility: function(menu_top_el) {
		var id_arr = menu_top_el.id.split("_");
		var room_id = id_arr[3];
		var space_type = id_arr[4];
		if(menu_top_el && menu_top_el.parentNode) {
			if(this.flat_flag == true && space_type == 1) {
				parent_page_id = room_id;
			} else {
				var idName = menu_top_el.parentNode.id;
				var parent_page_id = idName.replace("_menu_","");
				var rObj = new RegExp(this.id+"$");
				parent_page_id = parent_page_id.replace(rObj,"");
			}
			if(parent_page_id != "" && !(this.flat_flag == true && space_type == 1)) {
				parent_page_id = parseInt(parent_page_id);
				var parent_visibility_el = $("_menuvisibility"+this.id+"_" + parent_page_id);
				if(parent_visibility_el) {
					if(parent_visibility_el.src && parent_visibility_el.src.match("off.gif")) {
						//親がすでに非表示なので変更不可
						return false;
					}
				}
			}
		}
		return true;
	},
	chgVisibilityPage: function(this_el, page_id) {
		var menu_top_el = Element.getParentElementByClassName(this_el, "menu_row_top");
		var id_arr = menu_top_el.id.split("_");
		var room_id = id_arr[3];
		var space_type = id_arr[4];
		if(!this.chkVisibility(menu_top_el)) return;
		var img_el = Element.getChildElement(this_el);
		if(img_el.src.match("on.gif")) {
			var visibility_flag = 0;
		} else {
			var visibility_flag = 1;
		}
		var top_el = $(this.id);

		var chg_params = new Object();
		chg_params["method"] = "post";
		chg_params["param"] = {"action":"menu_action_edit_visibility","main_page_id":page_id, "visibility_flag":visibility_flag};
		chg_params["top_el"] = top_el;
		chg_params["callbackfunc"] = function(res){
			if(visibility_flag) {
				img_el.src = img_el.src.replace("off.gif","on.gif");
			} else {
				img_el.src = img_el.src.replace("on.gif","off.gif");
			}
			//子供検索、表示非表示切り替え
			var next_el = menu_top_el.nextSibling;

			if(this.flat_flag == false || (this.flat_flag == true && space_type != 1)) {
				if(next_el && next_el.id == "_menu_" + page_id+this.id) {
					var visibilityfields = Element.getElementsByClassName(next_el, "_menuvisibility");
					visibilityfields.each(function(visibility_el) {
						if(visibility_flag) {
							visibility_el.src = visibility_el.src.replace("off.gif","on.gif");
						} else {
							visibility_el.src = visibility_el.src.replace("on.gif","off.gif");
						}
					}.bind(this));
				}
			}
		}.bind(this);
		commonCls.send(chg_params);
	}
}