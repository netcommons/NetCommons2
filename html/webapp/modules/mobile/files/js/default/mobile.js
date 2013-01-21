var clsMobile = Class.create();
var mobileCls = Array();

clsMobile.prototype = {
	initialize: function(id) {
		this.id = id;
		this.popup = null;
	},

	switchDefault: function(el) {
		commonCls.sendPost(this.id, 'action=mobile_action_admin_default&target_module_id=' + el.value);
	},
	switchEmulator: function(el) {
		commonCls.sendPost(this.id, 'action=mobile_action_admin_emulator&allow_emulator=' + el.value);
	},
	switchMobileTextHtmlMode: function(el) {
		commonCls.sendPost(this.id, 'action=mobile_action_admin_texthtmlmode&mobile_text_html_mode=' + el.value);
	},
	switchMobileImgdspOpt: function(el) {
		commonCls.sendPost(this.id, 'action=mobile_action_admin_imgdspopt&mobile_imgdsp_size=' + el.value);
	},
	switchUsed: function(el, module_id) {
		var params = new Object();
		params["callbackfunc"] = function(res) {
			var on_el = el.firstChild;
			var off_el = on_el.nextSibling;
			commonCls.displayChange(on_el);
			commonCls.displayChange(off_el);
		}.bind(this);
		commonCls.sendPost(this.id, 'action=mobile_action_admin_used&target_module_id=' + module_id, params);
	},
	showPopup: function(el) {
		if (this.popup == null || !$(this.popup.popupID)) {
			this.popup = new compPopup(this.id, this.id);
		}
		this.popup.loadObserver = this._focusPopup.bind(this);
		this.popup.showPopup(this.popup.getPopupElementByEvent(el), el);
	},
	_focusPopup: function() {
		var form_el = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
		commonCls.focus(form_el);
	},
	logoUpload: function() {
		var top_el = $(this.id);
		var params = new Object();
		params['document_obj'] = this.popup.popupElement.contentWindow.document;
		params["method"] = "post";
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params["param"] = new Object();
		params["param"]["action"] = "mobile_action_admin_upload";
		params["param"]["room_id"] = 0;
		params["param"]["unique_id"] = 0;
		params["callbackfunc"] = function(file_list, res){
			$("mobile_logo" + this.id).src = "./?action=common_download_main&upload_id=" + file_list[0]["upload_id"];
			this.popup.closePopup();
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, error_mes){
			commonCls.alert(error_mes.unescapeHTML());
			this._focusPopup();
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	_isDisplayTypeFlat : function( ) {

		var top_el = $(this.id);
		var form_el = top_el.getElementsByTagName("form")[0];
		var	tree_els = $( form_el ).getInputs( "radio", "mobile_menu_display_type" );

		tree_el = tree_els.find( function( tag ) { return tag.checked; } );
		if( tree_el.value.match("tree") ) {
			return false;
		}
		else {
			return true;
		}
	},
	_isEachRoomMenu : function( ) {

		var top_el = $(this.id);
		var form_el = top_el.getElementsByTagName("form")[0];
		var	room_els = $( form_el ).getInputs( "radio", "mobile_menu_each_room" );

		room_el = room_els.find( function( tag ) { return tag.checked; } );
		return room_el.value;
	},
	menuNodeClick: function(event, page_id, edit_flag, addpage_flag) {
		var class_name = "_menu_" + page_id+this.id;
		var top_class_name = "_menutop_" + page_id;
		var top_el = $(this.id);
		var top_node_el = Element.getChildElementByClassName(top_el,top_class_name);
		var el = Element.getChildElementByClassName(top_el,class_name);

		var	top_node_el_href = top_node_el;

		if( !el ) {
			var	detail_params = new Object();
			detail_params["method"] = "get";
			if( edit_flag ) {
				var visibility_el = $( "_menuvisibility" + this.id + "_" + page_id );
				if( !visibility_el || visibility_el.src == undefined ) {
					var	visibility_flag = 0;
				}
				else if( visibility_el.src.match("off.gif") ) {
					var	visibility_flag = 0;
				}
				else {
					var	visibility_flag = 1;
				}
				detail_params["param"] = {"action":"mobile_view_admin_menu_detail","main_page_id":page_id,"option":"edit","visibility_flag":visibility_flag};
			}
			else {
				detail_params["param"] = {"action":"mobile_view_admin_menu_detail","main_page_id":page_id,"option":"init","visibility_flag":visibility_flag};
			}
			detail_params["callbackfunc"] = function(res) {
								var top_node_el_href = Element.getParentElementByClassName(top_node_el, "menu_row_top");
								var	div = document.createElement("DIV");
								div.id = "_menu_" + page_id + this.id;
								res = res.replace(/[\n\r]/g,"");
								if( !res  ) {
									div.className = "_menu_" + page_id + this.id + " display-none";
								}
								else {
									div.className = "_menu_" + page_id + this.id;
								}
								var	next_el = top_node_el_href.nextSibling;
								if( !next_el ) {
									top_node_el_href.parentNode.appendChild(div);
								}
								else {
									next_el.parentNode.insertBefore(div,next_el);
								}

								div.innerHTML = res;
							}.bind(this);
			detail_params["top_el"] = top_el;
			commonCls.send(detail_params);
			return;
		}

		var	parent_el = Element.getParentElement( el );

		for( var i=0 ; i<parent_el.childNodes.length ; i++ ) {
			var	div = parent_el.childNodes[i];
			if( div && div.tagName == "DIV" && Element.hasClassName( div, class_name ) ) {
				if( Element.hasClassName( div, "display-none" ) ) {
					if( div.innerHTML != "" ) {
						Element.removeClassName( div, "display-none" );
					}
				}
				else {
					Element.addClassName( div, "display-none" );
				}
				break;
			}
		}

	},
	chkVisibilitySpace: function( menu_top_el, click_page_id ) {
		var	space_type_chk_elm = $("_menutop" + this.id + "_" + click_page_id );
		if( space_type_chk_elm ) {
			// get menu top
			var menu_top_div = $('mobile_menu_setting_outer');

			// get space top
			if( Element.hasClassName( space_type_chk_elm, "menu_public" ) ) {
				var	spacetop = Element.getChildElementByClassName( menu_top_div, "menu_top_public" );
			}
			else if( Element.hasClassName( space_type_chk_elm, "menu_group" ) ) {
				var	spacetop = Element.getChildElementByClassName( menu_top_div, "menu_top_group" );
			}
			else {
				var	spacetop = Element.getChildElementByClassName( menu_top_div, "menu_top_private" );
			}
			if( spacetop ) {
				var spacetop_page_id_ar = spacetop.id.split( "_" );
				var	spacetop_page_id = spacetop_page_id_ar[ spacetop_page_id_ar.length -1 ];

				var	space_visibility_el = $("_menuvisibility" + this.id + "_" + spacetop_page_id );
				if( space_visibility_el ) {
					if( space_visibility_el.src && space_visibility_el.src.match( "off.gif" ) ) {
						return false;
					}
				}
			}
		}
		return true;
	},
	chkVisibilityParent: function( menu_top_el, click_page_id, flat_flag, each_room_flag ) {

		if( ( flat_flag==false && menu_top_el && menu_top_el.parentNode )
			||
			( flat_flag==false && menu_top_el && parseInt(Element.getStyle(menu_top_el, "marginLeft"))==0 ) ) {

			var	idName = menu_top_el.parentNode.id;
			var	parent_page_id = idName.replace("_menu_", "");
			var	rObj = new RegExp( this.id + "$" );
			parent_page_id = parent_page_id.replace( rObj, "" );
			if( parent_page_id != "" ) {
				parent_page_id = parseInt( parent_page_id );
				var	parent_visibility_el = $("_menuvisibility" + this.id + "_" + parent_page_id );
				if( parent_visibility_el ) {
					if( parent_visibility_el.src && parent_visibility_el.src.match( "off.gif" ) ) {
						return false;
					}
				}
			}
		}
		return true;
	},
	chkVisibilityRoom: function( menu_top_el, click_page_id, flat_flag, each_room_flag ) {

		var	idName = menu_top_el.id;
		var idName_ar = idName.split( "_" );
		var	root_page_id = idName_ar[ idName_ar.length - 1 ];

		if( each_room_flag==true && menu_top_el ) {
			if( root_page_id != "" ) {
				root_page_id = parseInt( root_page_id );
				var	root_visibility_el = $("_menuvisibility" + this.id + "_" + root_page_id );
				if( root_visibility_el ) {
					if( root_visibility_el.src && root_visibility_el.src.match( "off.gif" ) ) {
						var root_nodetype_el = $("_menunodetype" + this.id + "_" + root_page_id );
						if( root_nodetype_el ) {
							if( Element.hasClassName( root_nodetype_el, "mobile_menu_room" ) ) {
								return false;
							}
						}
					}
				}
			}
		}
		return true;
	},
	chgVisibilityPage: function( this_el, page_id, alert_mess_hidden_menu ) {

		var top_el = $(this.id);


		var	flat_flag = this._isDisplayTypeFlat();
		var	each_room_flag = this._isEachRoomMenu();

		// if menu_item which is clicked is not visible, then return
		var	menu_top_el = Element.getParentElementByClassName( this_el, "menu_row_top" );

		if( parseInt(Element.getStyle(menu_top_el, "marginLeft")) == 0 ) {
			var is_space_visi_chg = true;
		}
		else {
			var is_space_visi_chg = false;
		}

		if( is_space_visi_chg == false ) {
			if( !this.chkVisibilitySpace( menu_top_el, page_id ) ) {
				commonCls.alert( alert_mess_hidden_menu );
				return;
			}
			if( !this.chkVisibilityParent( menu_top_el, page_id, flat_flag, each_room_flag ) ) {
				commonCls.alert( alert_mess_hidden_menu );
				return;
			}
			if( !this.chkVisibilityRoom( menu_top_el, page_id, flat_flag, each_room_flag ) ) {
				commonCls.alert( alert_mess_hidden_menu );
				return;
			}
		}
		var	img_el = Element.getChildElement( this_el );
		if( img_el.src.match( "on.gif" ) ) {
			var	visibility_flag = 0;
		}
		else {
			var	visibility_flag = 1;
		}

		var	chg_params = new Object();
		chg_params["method"] = "post";
		chg_params["param"] = {"action":"mobile_action_admin_menu_visibility","main_page_id":page_id,"visibility_flag":visibility_flag};
		chg_params["top_el"] = top_el;
		chg_params["callbackfunc"] = function(res) {

			if( visibility_flag ) {
				img_el.src = img_el.src.replace("off.gif","on.gif");
				Element.removeClassName( img_el, "force_off_setting" );
			}
			else {
				img_el.src = img_el.src.replace("on.gif","off.gif");
				Element.addClassName( img_el, "force_off_setting" );
			}

			var	next_el = menu_top_el.nextSibling;

			var nodetype_el = $("_menunodetype"+this.id+"_"+page_id);
			var	is_room = false;
			if( nodetype_el ) {
				is_room = Element.hasClassName( nodetype_el, "mobile_menu_room" );
			}

			if( flat_flag == false || (each_room_flag==true && is_room==true) || is_space_visi_chg == true ) {

				if( next_el && next_el.id == "_menu_" + page_id + this.id ) {

					var	visibilityfields = Element.getElementsByClassName( next_el, "mobile_menu_edit_top_outer" );
					var onchange_break_classname = "";
					var onchange_break_idName_ar;
					visibilityfields.each( function(visibility_el) {
						if( !Element.hasClassName( visibility_el, "mobile_menu_lbl_noblock_disabled" ) ) {
							var	visibilityimg = Element.getChildElementByClassName( visibility_el, "_menuvisibility" );
							if( visibility_flag ) {
								if( onchange_break_classname != "" ) {
									if( Element.getParentElementByClassName( visibilityimg, onchange_break_classname ) ) {
										throw $continue;
									}
								}
								if( !Element.hasClassName( visibilityimg, "force_off_setting" ) ) {
									visibilityimg.src = visibilityimg.src.replace( "off.gif", "on.gif" );
									onchange_break_classname = "";
								}
								else {
									if( flat_flag == false ) {
										//throw $break;
										onchange_break_idName_ar = visibilityimg.id.split( "_" );
										onchange_break_classname = "_menu_"+ onchange_break_idName_ar[ onchange_break_idName_ar.length -1 ]+ this.id;
									}
									else if( each_room_flag == true ) {
										onchange_break_idName_ar = visibilityimg.id.split( "_" );
										onchange_break_chk_elm = $("_menunodetype"+this.id+"_"+onchange_break_idName_ar[ onchange_break_idName_ar.length -1 ]);
										if( Element.hasClassName( onchange_break_chk_elm, "mobile_menu_room" ) ) {
											onchange_break_classname = "_menu_"+ onchange_break_idName_ar[ onchange_break_idName_ar.length -1 ]+ this.id;
										}
									}
								}
							}
							else {
								visibilityimg.src = visibilityimg.src.replace( "on.gif", "off.gif" );
							}
						}
					}.bind(this) );
				}
			}
		}.bind(this);
		commonCls.send(chg_params);
	},
	chgEachRoom: function( this_el, each_room_flag ) {
		var top_el = $(this.id);

		var	chg_params = new Object();
		chg_params["method"] = "post";
		chg_params["param"] = {"action":"mobile_action_admin_menu_room","each_room_flag":each_room_flag};
		chg_params["top_el"] = top_el;
		chg_params["callbackfunc"] = function(res) {
			if( res ) {
				this_el.checked = true;

				// get menu top
				var menu_top_div = $('mobile_menu_setting_outer');
				if( each_room_flag == true ) {
					this._chgVisibilityToOffForEachRoomMenu( menu_top_div );
				}
			}
		}.bind(this);
		commonCls.send(chg_params);
	},
	chgDisplayType: function( this_el, display_type ) {
		var top_el = $(this.id);

		var	chg_params = new Object();
		chg_params["method"] = "post";
		chg_params["param"] = {"action":"mobile_action_admin_menu_display","display_type":display_type};
		chg_params["top_el"] = top_el;
		chg_params["callbackfunc"] = function(res) {
			if( res ) {
				this_el.checked = true;
				// get menu top
				var menu_top_div = $('mobile_menu_setting_outer');
				var menu_row = menu_top_div.firstChild;
				while ( menu_row.nodeType != 1 ) {
						menu_row = menu_row.nextSibling;
					}
				if( display_type == "tree" ) {
					this._chgVisibilityToOff( menu_row, 1 );
				}
			}
		}.bind(this);
		commonCls.send(chg_params);
	},
	_getChildMenuId: function( menu_top_row ) {
		var	idKeys = menu_top_row.id.split( '_' );
		var	childMenuId = "_" + idKeys[1] + "_" + idKeys[2] +  this.id;
		return childMenuId;
	},
	_getNextSiblingNode: function( elm ) {
		elm = elm.nextSibling;
		while ( 1 ) {
			if( !elm ) break;
			if ( elm.nodeType == 1 ) break;

			elm = elm.nextSibling;
		}
		return elm;
	},
	_chgVisibilityToOff : function( menu_row, set_visibility ) {

		// 渡された要素から兄弟を手繰って全メニューITEMを操作する
		// loop menu
		while( menu_row ) {

			// 渡された要素がメニューITEMではないときはスキップ
			if( !menu_row.hasClassName( 'menu_row_top' ) ) {
				//menu_row = menu_row.nextSibling;
				menu_row = this._getNextSiblingNode( menu_row );
				continue;
			}

			// 操作不可能な状態かどうかを取得
			var	menu_noblock_disabled = Element.getElementsByClassName( menu_row, 'mobile_menu_lbl_noblock_disabled' );

			// 操作対象メニューのページIDを取得
			var	idKeys = menu_row.id.split( "_" );
			var	page_id = idKeys[2];


			// 操作対象メニューのON,OFF状態を取得
			var	img_visibility = $( "_menuvisibility" + this.id + "_" + page_id );
			var	now_visibility = 1;
			if( img_visibility ) {
				if( img_visibility.src && img_visibility.src.match( "off.gif" ) ) {
					now_visibility = 0;
				}
			}

			// 関数引数によって指示された状態、操作可能かどうか、現時点でのメニューのONOFFを見て
			// OFFにしろと言われている、操作可能な状態である、かつ今はONであるなら状態をOFFに変更する
			if( menu_noblock_disabled.length == 0 && set_visibility == 0 && now_visibility == 1 && img_visibility) {
				img_visibility.src = img_visibility.src.replace( "on.gif", "off.gif" );
				now_visibility = 0;
			}


			// このメニューの配下に子供達がいるタイプなのかを取得する
			menu_leaf_type = Element.getElementsByClassName( menu_row, 'mobile_menu_leaf' );

			// 子供がいるようなら
			// if menu_ite is parent
			if( menu_leaf_type.length == 0  ) {

				var	chk_descent_menu = $( this._getChildMenuId( menu_row ) );

				if( chk_descent_menu ) {

					// if menu_item is disabled , then children is hidden
					if( menu_noblock_disabled.length > 0 ) {
						Element.addClassName( chk_descent_menu, "display-none" );
					}
					// else if menu_item is off, then check children and they are become off
					else {
						var	new_menu_row = chk_descent_menu.firstChild;
						this._chgVisibilityToOff( new_menu_row, now_visibility );
					}
				}
			}
			//menu_row = menu_row.nextSibling;
			menu_row = this._getNextSiblingNode( menu_row );
		}
	},
	_chgVisibilityToOffForEachRoomMenu : function( menu_row ) {
		var	menu_room_type = Element.getElementsByClassName( menu_row, 'mobile_menu_room' );
		menu_room_type.each( function( room_el ) {
			var idName = room_el.id;
			var visibilityName = idName.replace("_menunodetype", "_menuvisibility");
			var visibility_el = $(visibilityName);
			if( visibility_el ) {

				var menu_top_row = Element.getParentElementByClassName( room_el, "menu_row_top" );
				if( menu_top_row ) {
					var	menu_noblock_disabled = Element.getElementsByClassName( menu_top_row, 'mobile_menu_lbl_noblock_disabled' );

					if( menu_noblock_disabled.length == 0 && visibility_el.src && visibility_el.src.match( "off.gif" ) ) {
						var	chk_descent_menu = $( this._getChildMenuId( menu_top_row ) );
						if( chk_descent_menu ) {
							this._chgVisibilityToOff( chk_descent_menu.firstChild, 0 );
						}
					}
				}
			}
		}.bind(this) );
	},
	setColorConfig: function(action_name,success_message)
	{
		var top_el        = $(this.id);
		var form_el       = $("form" + this.id);

		var action_params = new Object();

		action_params['method'] = "post";
		action_params["param"] = "action=" + action_name + "&"+ Form.serialize(form_el);
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;
		action_params["callbackfunc"] = function(res) {
			if (res.match(/error_message:(.*)$/)) {
				commonCls.alert(RegExp.$1);
				return;
			}
			commonCls.alert(success_message);
		}.bind(this);
		commonCls.send(action_params);
	},
	setSampleColor: function( elm )
	{
		var	target_elm = $("mobile_sample_" + elm.name);
		var	target_color = elm.value;
		if( target_color.match(/^#[0-9a-fA-F]{6}$/) ) {
			Element.setStyle( target_elm,  { backgroundColor:target_color } );
		}
	},
	changeDisplayType:function(flg){
		if(flg == 1){
			$("whatsnew_the_number_of_display" + this.id).disabled = true;
			$("whatsnew_display_days" + this.id).disabled = false;
		}else{
			$("whatsnew_the_number_of_display" + this.id).disabled = false;
			$("whatsnew_display_days" + this.id).disabled = true;
		}
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
	setWhatsnewConfig: function(action_name,success_message){
		var top_el        = $(this.id);
		var form_el       = $("form" + this.id);

		var action_params = new Object();

		action_params['method'] = "post";
		action_params["param"] = "action=" + action_name + "&"+ Form.serialize(form_el);
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;

		action_params["callbackfunc"] = function(res) {
			if (res.match(/error_message:(.*)$/)) {
				commonCls.alert(RegExp.$1);
				return;
			}
			commonCls.alert(success_message);
		}.bind(this);
		commonCls.send(action_params);
	},
	changeLanguage: function(select_lang) {
		var params = {
			"action":"mobile_view_admin_menu_init",
			"lang":select_lang
		}
		commonCls.sendView(this.id, params);
	}
}