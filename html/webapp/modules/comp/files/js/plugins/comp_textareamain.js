/*
 * NC WYSIWYG 0.0.0.1
 * @author      Ryuji Masukawa
 *
 * NC WYSIWYG was made based on the jwysiwyg0.5.
 * http://code.google.com/p/jwysiwyg/
 *
 * tinymce3(minorVersion 2.7) is used as reference.
 * http://tinymce.moxiecode.com/
 *
 */

//テキストエリア用クラス
var compTextareamain = Class.create();
var textareamainComp = Array();
/*
    モジュール側のjavascriptクラスから
    this.textareamain = new parent.compTextarea();
    this.textareamain.textareaShow(this.id, "announcement_text", "full");
    のように使用
*/
compTextareamain.prototype = {
		top_id              : null,
		editor          : null,
	    options         : {},
		panel           : null,
		panel_btns      : {},
		statusbar       : null,
		resize          : null,
		start_w         : null,
		start_h         : null,
		original        : null,

		initialContent  : null,
		editorDoc       : null,
		currentNode     : null,
		is_mac          : null,
		dialog_id       : 'nc_wysiwyg_dialog',
		bookmark        : null,
		_pendingStyles  : null,
		_keyhandler     : null,
		_checkNode      : null,

		nc_undoManager  : null,
		dialogs         : null,

		components      : {},
		events          : [],
		eventstags      : [],

		autoregist      : null,

		// nc20用
		id              : null,
		uploadAction    : {},
		focus           : false,
		controls        : null,
		popup           : {},
		popupPrefix     : "",
		downloadAction  : "common_download_main",
		uploadAction    : {
			unique_id : "0",
			image     : null,
			file      : null
		},
		imageProperty   : {},
		top_table       : null,

		onloadEvent     : null,
		loadEventFlag   : false,

		js_path         : null,
		css_path        : null,
		mode            : null,
		edit_mode       : 'edit',

		initialize: function(options) {
			/**
	         * もし、カスタムコントローラをセットする場合、
	         * 以下のように動作する。
	         *
	         * ・同じライン、同じグループ（キー）に同じボタンがあった場合は、マージする。
	         * ・そうでない場合は、追加
	         * ・同じライン上にあるグループは、グループのキーでソートした順番で表示する。
	         *
	         */
	        var css = [], js = [];
	        if ( options ) {
	        	if ( options.css ) {
		            css = options.css;
		            delete options.css;
		        }
		        if ( options.js ) {
		            js = options.js;
		            delete options.js;
		        }
	        }

	        options = $H({
	            html : '<html><head xmlns="http://www.w3.org/1999/xhtml"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>INITIAL_TITLE</title>INITIAL_HEADER</head><body>INITIAL_CONTENT</body></html>',
	            title: 'wysiwyg editor',
				css  : [],                // css src
				js   : [],				  // javascript src
				style: null,

	            debug        : false,

	            autoSave     : true,
	            rmUnwantedBr : false,

	            cssInc       : true,	  		// 親のCSSをincludeするかどうか
	            parseHtml    : true,      		// 登録時、htmlモードに移る時にタグを整形（タブ、改行挿入等）するかどうか
	            tabStr       : '    ',    		// parseHtmlがtrueの場合のタブのスペースの数
	            lineBreak    : '\n',      		// parseHtmlがtrueの場合の改行コード

	            forecolor    : '#ff0000', 		// fontカラーのデフォルト色
	            hilitecolor  : '#ff0000',		// backgroundカラーのデフォルト色

	            undo_level   : 100,      		// undo redoできる履歴を保持する数

	            autoRegist   : false,			// 自動登録を行うかどうか
	            regist_time  : 60000,			// 60秒
	            regist_url   : "./regist.php",
	            regist_data  : {},

	            formatMes    : true,             // 空のelement等のメッセージを画面上に表示するかどうか
	            format_time  : 3000				 // 3秒
	        }).merge($H(options));
	        options.css = options.css.concat(css);
	        options.js = options.js.concat(js);
	        this.js_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_js&add_block_flag=1&dir_name=";
	        this.css_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&header=0&dir_name=/comp/plugins/";

	        this.options = options;

	        /**
	         * グループ - ボタン
	         *
	         * 説明(value):
	         *          key  className,commandが指定されていなければ、ボタンのクラス名称
	         *　　　　　　　　commandが指定されていなければ、execCommandの第一引数（コマンド名）
	         *         	  visible     : boolean  ボタンを表示するかどうか
	         *			  tags        : array    選択範囲が、tagsで書かれてある内部であれば、ボタンをactiveに変更
	         *			  css         : hash     選択範囲が、cssで書かれてあるスタイルであれば、ボタンをactiveに変更
	         *			  active_class: array    選択範囲が、classで書かれてあるclassNameであれば、ボタンをactiveに変更
	         *			  command     : string   execCommandの第一引数（コマンド名）
	         *						             classNameが指定されていなければ、ボタンのクラス名称
	         *    		  arguments   : array    execCommandの第三引数
	         *			  className   : string   ボタンアイコン(a)のクラス名称
	         *			  exec        : function 押下した時の動作を独自で設定（指定しない場合、execCommand)
	         *            list        : hash     ボタンをリストボックスで表示（keyの値がexec指定にした場合の第三引数、valueはリストの表示文字列）
	         *            extend_body : boolean  falseの場合、tag,cssが一致した場合、それ以上親のelementまで遡らない（default true）
	         *                                   例えば、現要素がfont-family指定があり、一致していたら、その親要素で違うfont-family指定がしてあっても
	         *                                   現要素のfont-family指定を優先する。
	         *			  liClassName : string   ボタン(li)のクラス名称
	         *            collapsedDis: boolean  trueの場合、選択範囲が折りたたまれている場合、ボタンを有効化しない(default false)
	         *            eventtags   : array    イベントを実行するタグの一覧（event参照）
			 *            event       : hash     WYSIWYG内のeventtagsに記述されたタグにおけるイベントによるcallbackを指定する
			 *                                   記述方法：　{dblclick :function(e){
			 *								                      alert(e.type);
			 *								                 }}
			 *                                   等、現状、dblclick,contextmenuのみ指定可能
			 *            components  : hash     TOOLBAR共通メソッド定義用　呼び出し方：this.components.key_name.appley,this.components.key_name.call等
			 *                                   hash_key==this.components.key_nameで一意の値を設定する
			 *            title       : string   アイコンのtitleタグの内容 default compTextareaLang['icons'][key名]
	         */
	        this.controls = [
				// 1行目
				[
					{
						group :   "001",
						value : {
							fontname      : { visible : true, css : {fontFamily : ''}, list : compTextareaLang['fontname'], extend_body : false,
											  exec : function(value) {
			            					      var n = this.applyInlineStyle('span', {style : {fontFamily : value}});
			            					      if(n) this.rangeSelect(n);
			            					      else this.chgList(this.panel_btns[this.top_id + 'fontname'], '');
			            					  }
			            					},
			            	fontsize      : { visible : true, css : {fontSize : ''}, list : compTextareaLang['fontsize'], extend_body : false,
			            					  exec : function(value) {
			            					      var n = this.applyInlineStyle('span', {style : {fontSize : value}});
			            					      if(n) this.rangeSelect(n);
			            					      else this.chgList(this.panel_btns[this.top_id + 'fontname'], '');
			            					  }
			            					},
			            	formatblock   : { visible : true, tags : ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'address', 'pre', 'p'], list : compTextareaLang['formatblock'], extend_body : false,
			            					  exec : function(value) {
			            					      var n = this.applyInlineStyle(value);
			            					      if(n) this.rangeSelect(n);
			            					      else this.chgList(this.panel_btns[this.top_id + 'fontname'], '');
			            					  }
			            					}
						}
					},
					{
						group :   "002",
						value : {
							bold          : { visible : true, tags : ['b', 'strong'], css : { fontWeight : 'bold' } },
			            	italic        : { visible : true, tags : ['i', 'em'], css : { fontStyle : 'italic' } },
			            	underline     : { visible : true, tags : ['u'], css : { textDecoration : 'underline' } },
			            	strikeThrough : { visible : true, tags : ['s', 'strike'], css : { textDecoration : 'line-through' } }
						}
					},
					{
						group :   "003",
						value : {
							subscript   : { visible : true, tags : ['sub'],
											exec : function(e) {
												var sel_n = null;
												this.editorDoc.execCommand("subscript", false, []);
			            					    var r = this.getRange();
												if(r.endContainer && r.endContainer.parentNode) {
													sel_n = r.endContainer.parentNode;
													if(sel_n.nodeName.toLowerCase() == 'sup' || sel_n.nodeName.toLowerCase() == 'sub')
														this.rangeSelect(sel_n, 1);
												} else {
													sel_n = this.getSelectNode().parentNode;
												}

			            					    if(sel_n && sel_n.childNodes[0] && sel_n.childNodes[0].nodeName.toLowerCase() == 'sup')
													sel_n = sel_n.childNodes[0];
												if(sel_n && sel_n.nodeName.toLowerCase() == 'sup' &&
											   		sel_n.childNodes.length == 1) {
											   		this.insertAfter(sel_n, sel_n.innerHTML);
											   		this.rangeSelect(sel_n.parentNode, 1);
											   		sel_n.parentNode.removeChild(sel_n);
											    }
											    this.checkTargets();
			            					}
			            				  },
			            	superscript : { visible : true, tags : ['sup'],
			            					exec : function(e) {
												var sel_n = null;
												this.editorDoc.execCommand("superscript", false, []);
			            					    var r = this.getRange();
			            					    if(r.endContainer && r.endContainer.parentNode) {
													sel_n = r.endContainer.parentNode;
													if(sel_n.nodeName.toLowerCase() == 'sup' || sel_n.nodeName.toLowerCase() == 'sub')
														this.rangeSelect(sel_n, 1);
												} else {
													sel_n = this.getSelectNode().parentNode;
												}
												if(sel_n && sel_n.childNodes[0] && sel_n.childNodes[0].nodeName.toLowerCase() == 'sub')
													sel_n = sel_n.childNodes[0];
												if(sel_n && sel_n.nodeName.toLowerCase() == 'sub' &&
											   		sel_n.childNodes.length == 1) {
											   		this.insertAfter(sel_n, sel_n.innerHTML);
											   		this.rangeSelect(sel_n.parentNode, 1);
											   		sel_n.parentNode.removeChild(sel_n);
											    }
											    this.checkTargets();
			            					}
			            				  }
						}
					},
					{
						group :   "004",
						value : {
							forecolor   : { visible : true,
											exec : function(e) {
												var c = Element.getStyle(Element.getChildElementByClassName(this.panel, "forecolor"), 'backgroundColor');
												var n = this.applyInlineStyle('span', {style : {color : c}});
												if(n) {
													this.rangeSelect(n, 1);
												}
											}
										  },
							forecolor_arrow : { visible : true, liClassName : 'nc_wysiwyg_arrow',
											exec : function(e) {
												var self = this, event_el = Event.element(e);
												var c = commonCls.getColorCode(Element.getChildElementByClassName(this.panel, "forecolor"), 'backgroundColor');
												var callback = function() {self.components.colorpickerCallback.call(self, 'forecolor', c);};
												var options = {
													js        : ['comp_colorpicker'],
													jsname    : ['window.compColorPicker'],
													css       : ['comp_colorpicker.css'],
													callback  : callback
												};
												this.toggleDialog(e, options);
											},
											components : {
												colorpickerCallback : function (name, c) {
													var self = this;
													var opts = {
														colorcode : c,
														callback  : function(v) {
															Element.setStyle(Element.getChildElementByClassName(self.panel, name), {backgroundColor : v});
															self.removeDialog();
															if(name == 'hilitecolor') {
																var n = self.applyInlineStyle('span', {style : {backgroundColor : v}});
															} else {
																var n = self.applyInlineStyle('span', {style : {color : v}});
															}
															if(n) {
																self.rangeSelect(n, 1);
															}
														},
														cancel_callback  : function(v) {
															self.removeDialog();
														}
													};
													var colorpicker = new compColorPicker(opts);
													colorpicker.showColorPicker($(this.dialog_id));
												}
											}
										  },
			            	hilitecolor : { visible : true,
											exec : function(e) {
												var c = Element.getStyle(Element.getChildElementByClassName(this.panel, "hilitecolor"), 'backgroundColor');
												var n = this.applyInlineStyle('span', {style : {backgroundColor : c}});
												if(n) {
													this.rangeSelect(n, 1);
												}
											}
										  },
			            	hilitecolor_rarrow : { visible : true, liClassName : 'nc_wysiwyg_rarrow',
											 exec : function(e) {
												var self = this, event_el = Event.element(e);
												var c = commonCls.getColorCode(Element.getChildElementByClassName(this.panel, "hilitecolor"), 'backgroundColor');
												var callback = function() {self.components.colorpickerCallback.call(self, 'hilitecolor', c);};
												var options = {
													js        : ['comp_colorpicker'],
													jsname    : ['window.compColorPicker'],
													css       : ['comp_colorpicker.css'],
													callback  : callback
												};
												this.toggleDialog(e, options);
											  }
										    }
						}
					},
					{
						group :   "005",
						value : {
							removeFormat : {
				                visible : true,
				                exec    : function()
				                {
				                	var self = this;
				                	if(browser.isIE) {
				                		var spans, font, loop_flag = true;
				                		var f = self.currentNode ? self.currentNode : self.getSelectNode();
				                    	// 選択NodeTopをselect
			                    		var span = null, p = f.parentNode;
			                    		while(1) {
			                    			if(p.nodeName.toLowerCase() == 'span' && p.innerHTML == f.outerHTML) {
			                    				f = p;
			                    				p = p.parentNode;
			                    			} else {
			                    				break;
			                    			}
			                    		}
			                    		// fontタグへ置換
			                    		if(f.nodeName.toLowerCase() == 'body') {
			                    			var bm = self.getBookmark();
			                    			if(!bm || bm.length == 0) {
			                    				return;
			                    			}
			                    			f = self.applyInlineStyle('font');
			                    		} else if(f.style.color != '' || f.style.backgroundColor != '' || f.style.fonSize != '' || f.style.fontFamily != '') {
			                    			font = this.editorDoc.createElement('font');
					                    	f = self.replace(font, f, true);
			                    		}
			                    		while(loop_flag) {
				                    		loop_flag = false;
				                    		spans = f.getElementsByTagName("span");
				                    		for (var i = 0; i < spans.length; ++i) {
				                    			if(spans[i].style.color != '' || spans[i].style.backgroundColor != '' || spans[i].style.fonSize != '' || spans[i].style.fontFamily != '') {
					                    			font = this.editorDoc.createElement('font');
							                    	self.replace(font, spans[i], true);
							                    	loop_flag = true;
							                    	break;
					                    		}
				                    		}
			                    		}
			                    		self.rangeSelect(f);
				                    } else if(browser.isSafari) {
				                    	var f = self.getSelectNode();
				                    	var formatTags = " font span b script strong em i u ins s strike sub sup ";
				                    	if(formatTags.indexOf(" " + f.tagName.toLowerCase() + " ") != -1) {
				                    		var buf_f = f;
				                    		do {
				                    			if(!buf_f || buf_f.nodeName.toLowerCase() == "body")
				                    				break;
				                    			if(buf_f.childNodes.length == 1)
				                    				f = buf_f;
				                    		} while ( buf_f = buf_f.parentNode );
				                    		if(f.nextSibling && f.nextSibling.nodeName.toLowerCase() == 'br') {
					                    		var r = self.getRange();
												r.setStartBefore(f);
												r.setEndAfter(f.nextSibling);
												self.setRange(r);
											}
				                    	}
				                    }
				                    this.editorDoc.execCommand('removeFormat', false, []);
				                    if(browser.isSafari && f.nodeName.toLowerCase() != "body") {
				                    	// Class Apple-style-spanを検索し、削除
				                    	var remove_el_arr = [];
				                    	var buf_f = f;
				                    	if(!f.parentNode) {
				                    		f = this.editorDoc.body;
				                    	} else {
					                    	do {
				                    			if(!buf_f || buf_f.nodeName.toLowerCase() == "body")
				                    				break;
				                    			if(buf_f.childNodes.length == 1)
				                    				f = buf_f;
				                    		} while ( buf_f = buf_f.parentNode );
				                    	}
				                    	var target_arr = Element.getElementsByClassName(f, "Apple-style-span");
										$A(target_arr).each(function(el) {
											remove_el_arr.push(el);
										});
				                    	for (i = remove_el_arr.length - 1; i >= 0; i--) {
											var rm_el = remove_el_arr[i];
											self.insertAfter(rm_el, rm_el.innerHTML);
											rm_el.parentNode.removeChild(rm_el);
										}
				                    }
				                    this.checkTargets();
				                    //this.editorDoc.execCommand('unlink', false, []);
				                }
				            }
						}
					}
				],
				// 2行目
				[
					{
						group :   "001",
						value : {
							undo : { visible : true, exec : function(){ this.undo();} },
			            	redo : { visible : true, exec : function(){ this.redo();} }
						}
					},
					{
						group :   "002",
						value : {
							justifyLeft   : { visible : true, css : { textAlign : 'left' },
											  exec : function(e) {
				            				  	this.components.execTextAlign.call(this, "justifyLeft", "left");
			            					  }
			            				    },
				            justifyCenter : { visible : true, tags : ['center'], css : { textAlign : 'center' },
				            				  exec : function(e) {
				            				  	this.components.execTextAlign.call(this, "justifyCenter", "center");
			            					  },
			            					  components : {
												execTextAlign : function (name, type) {
													if(browser.isIE || browser.isOpera){
					            				  		var n = this.currentNode ? this.currentNode : this.getSelectNode();
					            				  		if(n && n.nodeName.toLowerCase() == 'img') {
					            				  			n = this.applyInlineStyle('<div style="text-align:'+type+'">' + n.outerHTML + '</div>', null, true);
					            				  		} else if(n && n.nodeName.toLowerCase() != 'div') {
						            				  		n = this.applyInlineStyle('div', {style : {textAlign : type}});
														} else {
															Element.setStyle(n, {textAlign:type});
														}
					            				  	} else {
					            				  		var sel_n = null;
														this.editorDoc.execCommand(name, false, []);
					            					    var r = this.getRange();
					            					    if(r.endContainer && r.endContainer.parentNode) {
															sel_n = r.endContainer.parentNode;
															if(sel_n.nodeName.toLowerCase() != 'div')
																sel_n = r.startContainer.parentNode;
															if(sel_n.nodeName.toLowerCase() == 'div') {
																sel_n.removeAttribute("align", 0);
																Element.setStyle(sel_n, {textAlign:type});
															}
														}
														this.checkTargets();
					            				  	}
												}
											  }
			            				   },
				            justifyRight  : { visible : true, css : { textAlign : 'right' },
											  exec : function(e) {
				            				  	this.components.execTextAlign.call(this, "justifyRight", "right");
			            					  }
			            				    }
						}
					},
					{
						group :   "003",
						value : {
							insertOrderedList    : { visible : true, tags : ['ol'],
														exec : function(e) {
															if(!browser.isIE)
				            				  					this.editorDoc.execCommand("insertOrderedList", false, []);
				            				  				else {
				            				  					var n = this.applyInlineStyle('div');
				            				  					this.rangeSelect(n);
				            				  					this.editorDoc.execCommand("insertOrderedList", false, []);
				            				  					if(n && n.parentNode) {
												   					this.insertBefore(n, n.innerHTML)
																	n.parentNode.removeChild(n);
																}
				            				  				}
				            				  				this.checkTargets();
			            					  			}
			            					   		},
			            	insertUnorderedList  : { visible : true, tags : ['ul'],
														exec : function(e) {
															if(!browser.isIE)
				            				  					this.editorDoc.execCommand("insertUnorderedList", false, []);
				            				  				else {
				            				  					var n = this.applyInlineStyle('div');
				            				  					this.rangeSelect(n);
				            				  					this.editorDoc.execCommand("insertUnorderedList", false, []);
				            				  					if(n && n.parentNode) {
												   					this.insertBefore(n, n.innerHTML)
																	n.parentNode.removeChild(n);
																}
				            				  				}
				            				  				this.checkTargets();
			            					  			}
			            					   		}
						}
					},
					{
						group :   "004",
						value : {
							outdent    : { visible : true,
										   exec : function() {
										   		var self = this;
										   		var n = this.currentNode ? this.currentNode : this.getSelectNode();
										   		if(n && n.nodeName.toLowerCase() == 'li') {
										   			this.editorDoc.execCommand('outdent', false, []);
			            				        } else if(n && n.nodeName.toLowerCase() == 'div') {
										   			var marginLeft = parseInt(Element.getStyle(n, 'marginLeft'));
										   			if(marginLeft > 20) {
										   				Element.setStyle(n, {marginLeft:(marginLeft - 20) + "px"});
										   			} else {
										   				Element.setStyle(n, {marginLeft:''});
														var attrs = n.attributes, attrs_flag = false;
														for (var i = 0; i < attrs.length; ++i) {
															a = attrs.item(i);
															if (!a.specified) {
																continue;
															}
															attrs_flag = true;
															break;
														}
										   				if(attrs_flag == false || n.style.length == 0 || n.getAttribute("style") == '') {
										   					this.insertBefore(n, n.innerHTML)
															n.parentNode.removeChild(n);
															this.checkTargets();
														}
													}

												}
			            				   }
			            				 },
							indent     : { visible : true,
							               exec : function() {
										   		var n = this.currentNode ? this.currentNode : this.getSelectNode();
										   		var r = this.getRange();
										   		if(n && n.nodeName.toLowerCase() == 'li') {
										   			this.editorDoc.execCommand('indent', false, []);
			            				        } else if(n && n.nodeName.toLowerCase() != 'div') {
										   			if(!browser.isOpera && r.startContainer && r.endContainer &&
										   				r.startContainer == r.endContainer) {

										   				var br = r.startContainer.nextSibling;
										   				if(!br) {
										   					br = this.editorDoc.createTextNode("");
										   					r.insertNode(br);
										   					br = br.nextSibling;
										   					r.setStartBefore(br);
										   				} else {
										   					r.setStartBefore(r.startContainer);
										   				}
										   				r.setEndAfter(br);
										   				this.setRange(r);
										   			}
										   			n = this.applyInlineStyle('div', {style : {marginLeft : "20px"}});
										   			if(n) {
														this.rangeSelect(n, 1);
													}
												} else {
													var m = (parseInt(Element.getStyle(n, 'marginLeft'))) ? parseInt(Element.getStyle(n, 'marginLeft')) : 0;
													Element.setStyle(n, {marginLeft:(m + 20) + "px"});
												}
			            				   }
			            				 },
							blockquote : { visible : true, tags : ['blockquote'],
										   exec : function() {
										   		var self = this;
										   		var n = this.getSelectBlockNode();
										   		if(n && n.nodeName.toLowerCase() != 'blockquote') {
										   			n = this.applyInlineStyle("blockquote", {"class" : "quote"});
										   			if(n) {
										   				Element.addClassName(n,"quote");
										   				self.rangeSelect(n);
										   			}
												} else {
													this.insertBefore(n, n.innerHTML)
													n.parentNode.removeChild(n);
													this.checkTargets();
												}
			            				   }
			            				 }
						}
					},
					{
						group :   "005",
						value : {
							inserttable  : { visible : true,exec : function(e) {
								var self = this;
								var callback = function() {
													var opts = {
														'callback' : function(html){
															self.addFocus(true);
															if(browser.isIE)
																self.moveToBookmark(self.bookmark);
															var table = self.applyInlineStyle(html, null, true);
															self.removeDialog(self.dialog_id);
															if(!table.nextSibling || table.nextSibling.nodeName.toLowerCase() != "br") {
																self.insertAfter(table, "<br />");
															}
															self.rangeSelect(table);
															self.checkTargets();
															self.addUndo();
														}
													};
													var inserttable = new compInsertTable(opts);
													inserttable.showInsertTable($(self.dialog_id));
												};
								var options = {
									css : ['comp_inserttable.css'],
									js : ['comp_inserttable'],
									jsname : ['window.compInsertTable'],
									callback : callback
								};
								this.toggleDialog(e, options);
							}},
							inserttable_rarrow : { visible : true, liClassName : 'nc_wysiwyg_rarrow',
								eventtags : ['table', 'thead', 'tbody', 'tfoot','tr', 'th', 'td'],
								event : {contextmenu :function(e, n){
									var pos = Position.positionedOffsetScroll(this.editor), sc_pos = (browser.isIE && browser.version < 9) ? this.getScrollDoc(window, document) : this.getScrollDoc();
									var options = {
										style    : {left: (Event.pointerX(e) + pos[0] - sc_pos['left']) + "px", top : (Event.pointerY(e) + pos[1] - sc_pos['top']) + "px"}
									};
									this.components.showTableMenu.call(this, Element.getChildElementByClassName(this.panel, "inserttable_rarrow"), options);
									Event.stop(e);
									return false;
								}},
								tags : ['table', 'thead', 'tbody', 'tfoot','tr', 'th', 'td'],
								exec : function(e) {
									this.components.showTableMenu.call(this, e);
								},
								components : {
									showTableMenu : function (o, options) {
										var self = this, selPos;
										var selPos = self.getSelectTablePos();
										options = $H({
											css : ['comp_tablemenu.css'],
											js : ['comp_tablemenu'],
											jsname : ['window.compTableMenu'],
											className : "nc_wysiwyg_tablemenu",
											callback : function() {
												var opts = {table_pos : selPos};
												var tablemenu = new compTableMenu(opts);
												tablemenu.showTableMenu(self, $(self.dialog_id));
											}
										}).merge($H(options));
										this.toggleDialog(o, options);
									}
								}
							}
						}
					},
					{
						group :   "006",
						value : {
							insertHorizontalRule : { visible : true, tags : ['hr'],
														exec : function(e) {
			            					      			var n = this.applyInlineStyle('hr', {style : {width : "100%", height : "2px"}}, true);
			            					      			if(n) this.rangeSelect(n);
			            					  			}
			            					  	   }
						}
					},
					{
						group :   "007",
						value : {
							insertsmiley : { visible : true,exec : function(e) {
												var self = this;
												var el = Event.element(e);
												this.showDialogBox("insertsmiley", "comp_textarea_view_insertsmiley", el);
											}
										}
						}
					},
					{
						group :   "008",
						value : {
							inserttex : { visible : true,
										  active_class : ['tex'],
										  eventtags : ['img'],
										  event : {dblclick :function(e, n){
											var src = ((browser.isIE && browser.version < 9) || browser.isOpera) ? n.src : n.getAttribute("src");
											var re = new RegExp(/.*action=common_tex_main&c=(.*)/i);
											if(src.match(re)) {
												var inserttex = Element.getChildElementByClassName(this.panel, "inserttex");
												this.components.showInserTex.call(this, inserttex, n);
												return true;
											}
											return false;
										  }},
										  components : {
											  showInserTex : function (e, n) {
											  		var self = this;
											  		var text = '';
													n = (n == undefined) ? Event.element(e) : n;
													if(n && n.nodeName.toLowerCase() == 'img') {
														var src = ((browser.isIE && browser.version < 9) || browser.isOpera) ? n.src : n.getAttribute("src");
														var re = new RegExp(/.*action=common_tex_main&c=(.*)/i);
														if(src.match(re)) {
															text = RegExp.$1;
														}
													}
													var callback = function(){
																		var opts = {
																			'callback' : function(html){
																				self.addFocus(true);
																				if(browser.isIE)
																					self.moveToBookmark(self.bookmark);
																				var tex_el = self.applyInlineStyle(html, null, true);
																				self.removeDialog(self.dialog_id);
																				if(browser.isSafari && n && n.nodeName.toLowerCase() == 'img') {
																					self.insertBefore(n, tex_el);
																					Element.remove(n);
																				}
																				self.addUndo();
																			},
																			'text' : text
																		};
																		var mimetex = new compMimeTex(opts);
																		mimetex.showMimeTex($(self.dialog_id));
																	}
													var options = {
														css : ['comp_mimetex.css'],
														js  : ['comp_mimetex'],
														jsname  : ['window.compMimeTex'],
														callback : callback
													};
													self.toggleDialog(e, options);
												}
										  },
										  exec : function(e) {
											  	var self = this;
											    var n = self.currentNode ? self.currentNode : self.getSelectNode();
											    self.components.showInserTex.call(this, e, n);

											}
										}
						}
					},
					{
						group :   "009",
						value : {
							createlink : { visible : true,
										   tags : ['a'],
										   exec : function(e) {
										   		var self = this;
										   		var options = {
													css      : ['comp_insertlink.css'],
													js       : ['comp_insertlink'],
													jsname    : ['window.compInsertLink'],
													callback : function(){
														var n = self.currentNode ? self.currentNode : self.getSelectNode();
														var opts = {
											        		callback : function(args) {
											        			var a, bm, v;
											        			// リンク挿入
											        			self.removeDialog(self.dialog_id);
											        			self.addFocus(true);
											        			if(n && n.nodeName.toLowerCase() != 'a') {
											        				bm = self.bookmark;
											        				if(n.nodeName.toLowerCase() != "img" && (!bm || (browser.isIE && bm.length == 0) || (!browser.isIE && bm.start == bm.end))) {
											        					var v = (args.title) ? args.title : args.href;
											        					if(browser.isIE)
											        						self.moveToBookmark(bm);
																		a = self.applyInlineStyle('<a>' + v + '</a>', args, true);
												        			} else if(!browser.isFirefox) {
												        				if(n.nodeName.toLowerCase() == "img") {
																			if(!browser.isOpera)
																				self.rangeSelect(n);
																			a = self.applyInlineStyle('a', args, true);
																			a.appendChild(n);
																		} else {
																			self.moveToBookmark(self.bookmark);
																			a = self.applyInlineStyle('a', args);
																		}
																	} else
												        				a = self.applyInlineStyle('a', args);
												        		} else {
												        			// 更新
												        			if(!args['title'])
												        				n.removeAttribute('title',0);
												        			if(!args['target'])
												        				n.removeAttribute('target',0);
												        			for (var key in args)
												        				n.setAttribute(key,args[key],0);
												        			a = n;
											        			}
											        			self.rangeSelect(a);
											        			self.addUndo();

											        			return true;
											        		},
											        		cancel_callback : function() {
											        			// キャンセル
											        			self.removeDialog(self.dialog_id);
											        			//self.addFocus(true);
											        			//self.moveToBookmark(bm);
											        			return true;
											        		}
												        };
												        if(n && n.nodeName.toLowerCase() == 'img' &&
												        	n.parentNode && n.parentNode.nodeName.toLowerCase() == 'a') {
												        	n = n.parentNode;
												        }
														if(n && n.nodeName.toLowerCase() == 'a') {
															opts = $H({
																url    : (n.getAttribute("href")) ? n.getAttribute("href") : '',
																title  : (n.getAttribute("title")) ? n.getAttribute("title") : '',
																target : (n.getAttribute("target")) ? n.getAttribute("target") : ''
															}).merge($H(opts));
														}
														var insertlink = new compInsertLink(opts);
														insertlink.showInsertLink($(self.dialog_id));
													}
												}

												this.toggleDialog(e, options);
										   }
										 },
							unlink     : { visible : true,
										   tags : ['a'],
										   exec : function(e) {
										   		var n = this.currentNode ? this.currentNode : this.getSelectNode();
										   		if(n && n.nodeName.toLowerCase() == 'a')
										   			this.rangeSelect(n);
										   		this.editorDoc.execCommand('unlink', false, []);
										   }
				                         }
						}
					},
					{
						group :   "010",
						value : {
							savezip : { visible : true ,
										exec : function(e) {
											var root = document.createElement('div');
											this.setContent(this.editorDoc.body.innerHTML, root);
											this.chgModeInit("html", root);
											var content = this.getParseContent(root);
											root = null;
											var form_el = document.createElement('form');
											form_el.action = _nc_base_url + _nc_index_file_name;
											form_el.method = 'POST';
											$(this.id).appendChild(form_el);
											form_el.appendChild(this._createInputHidden('action', 'common_download_zip'));
											form_el.appendChild(this._createInputHidden('content', content));
											form_el.appendChild(this._createInputHidden('download_action', this.downloadAction));
											form_el.submit();
											Element.remove(form_el);
										}
							}
						}
					},
					{
						group :   "011",
						value : {
							insertvideo : { visible : true,exec : function(e) {
												var self = this;
												var callback = function(){
																	var opts = {
																		'callback' : function(html){
																			self.addFocus(true);
																			if(browser.isIE)
																				self.moveToBookmark(self.bookmark);
																			var spn = self.applyInlineStyle('span', null, true);
																			spn.innerHTML = html;
																			spn.parentNode.insertBefore(spn.childNodes[0], spn);
																			spn.parentNode.removeChild(spn);
																			self.checkTargets();
																			self.removeDialog(self.dialog_id);
																			self.addUndo();
																		}
																	};
																	var insertvideo = new compInsertVideo(opts);
																	insertvideo.showInsertVideo($(self.dialog_id));
																};
												var options = {
													css : ['comp_insertvideo.css'],
													js  : ['comp_insertvideo'],
													jsname  : ['window.compInsertVideo'],
													callback : callback
												};
												this.toggleDialog(e, options);
											}
							},
							insertamazon: { visible : true,exec : function(e) {
												var el = Event.element(e);
												this.showDialogBox("insertamazon", "comp_textarea_view_insertamazon", el, false, [-422, -40], true);
											}
							},
							insertimage : { visible : true,
											tags : ['img'],
											eventtags : ['img'],
											event : {dblclick :function(e, image){
												var re = new RegExp(/.*action=common_tex_main&c=(.*)/i);
												var src = ((browser.isIE && browser.version < 9) || browser.isOpera) ? image.src : image.getAttribute("src");
												if(!src.match(re)) {
													this.components.showInserImage.call(this, e, image);
													return true;
												}
												return false;
											}},
											exec : function(e) {
												//初期化
												var n = this.currentNode ? this.currentNode : this.getSelectNode();
												this.components.showInserImage.call(this, e, n);
											},
											components : {
												showInserImage : function (e, image) {
													var el;
													this.addFocus(true);
													image = (image == undefined) ? Event.element(e) : image;
													if(image.nodeName.toLowerCase() == 'img') {
														this.currentNode = image;
														var el = $(this.top_id + '_btn_insertimage');
														var border = valueParseInt(Element.getStyle(image, "borderWidth"));
														if(typeof image.style.cssFloat != "undefined") {
											    			var float_value = image.style.cssFloat;
											    		} else {
											    			var float_value = image.style.styleFloat;
											    		}
												    	if(float_value == "left" || float_value == "right") {
												    		var align = float_value;
												    	} else {
												    		var align = image.align;
												    	}
														this.imageProperty = {
															f_base   : '',
															f_url    : ((browser.isIE && browser.version < 9) || browser.isOpera) ? image.src : image.getAttribute("src"),
															f_alt    : image.alt.strip(),
															f_align  : align,
															f_border : (border <= 0) ? '' : border,
															f_vert   : (image.vspace <= 0) ? '' : image.vspace,
															f_horiz  : (image.hspace <= 0) ? '' : image.hspace,
															f_width  : image.clientWidth,
															f_height : image.clientHeight
														};
													} else {
														el = Event.element(e);
														this.imageProperty  = {
															f_base   : '',
															f_url    : '',
															f_alt    : '',
															f_border : '',
															f_align  : '',
															f_vert   : '',
															f_horiz  : '',
															f_width  : '',
															f_height : ''
														};
													}
													this.showDialogBox("insertimage", "comp_textarea_view_insertimage", el);
												}
											}
							},
							insertfile : { visible : true,
										   exec : function(e) {
												var el = Event.element(e);
												this.showDialogBox("insertupload", "comp_textarea_view_insertupload", el);
											}
							}
						}
					}
				]
			];
		},

		textareaShow : function(id, textarea_classname, mode) {
			this.id = id;
			this.top_id = (textarea_classname != null ? textarea_classname : 'nc_wysiwyg') + id;
			var text_el = Element.getChildElementByClassName($(id),textarea_classname);
			if($(this.top_id)) {
				return;
			}
			commonCls.referComp[this.top_id + this.popupPrefix] = this;
			this.chgTextareaMode(mode);
			this.init(text_el);
		},

		textareaEditShow : function(id,text_el,mode) {
			this.id = id;
			var class_arr = text_el.className.split(" ");
			var textarea_classname = null;
			if(text_el.id != "") {
				textarea_classname = 'nc_wysiwyg_' + text_el.id;
			} else if(class_arr[0] != "") {
				textarea_classname = class_arr[0];
			}

			this.top_id = (textarea_classname != null ? textarea_classname : 'nc_wysiwyg') + id;
			if($(this.top_id)) {
				return;
			}
			commonCls.referComp[this.top_id + this.popupPrefix] = this;
			this.chgTextareaMode(mode);
			this.init(text_el);
		},

		chgTextareaMode : function(mode) {
			this.mode = (mode == undefined) ? "full" : mode;
			if(mode == "simple") {
				var visible_control = " bold italic underline strikeThrough undo redo insertOrderedList insertUnorderedList forecolor forecolor_arrow hilitecolor hilitecolor_rarrow removeFormat blockquote insertsmiley createlink unlink insertvideo insertimage insertfile ";
				for (var line = 0; line < this.controls.length; ++line) {
					for ( var group in this.controls[line] ) {
						for ( var name in this.controls[line][group]["value"] ) {
							if(visible_control.indexOf(" " + name + " ") != -1)
								this.controls[line][group]["value"][name]['visible'] = true;
							else
								this.controls[line][group]["value"][name]['visible'] = false;
						}
					}
				}
			}
		},

		init : function(el)
		{
			var self = this;

			this.is_mac = navigator.userAgent.indexOf('Mac') != -1;

	        /////this.top_id = 'nc_wysiwyg' + $.data(el);

	        var newX = parseInt(Element.getStyle(el, "width")) || el.clientWidth;
	        var newY = parseInt(Element.getStyle(el, "height")) || el.clientHeight;

            if ( el.nodeName.toLowerCase() == 'textarea' ) {
				this.original = el;
				Element.addClassName(el,"nc_wysiwyg");

                if ( newX == 0 && el.cols )
                    newX = ( el.cols * 8 ) + 21;

                if ( newY == 0 && el.rows )
                    newY = ( el.rows * 16 ) + 16;

				if(newX < 514)
					newX = 514;

				var editor = this.editor = document.createElement('iframe');
				Element.setStyle(editor, {
                    height    : ( newY  ).toString() + 'px',
                    width     : ( newX  ).toString() + 'px'
                });
				Element.addClassName(editor,"nc_wysiwyg");
				self.loadEventFlag = false;
				self.onloadEvent = function(){
					if(!self.editorDoc || self.loadEventFlag == false || !self.editor.parentNode) {
						self.loadEventFlag = true;
						Event.stopObserving(editor,"load", self.onloadEvent, false, self.id);
						self.initialContent = self.original.value;
						self.editorDoc = self.getWin().document;
						self.chgDesignMode();
						self.initFrame();
						self.chgModeInit("edit");
						// undo redo
						self.nc_undoManager = new compUndoManager(self);
						commonCls.load(self.js_path + "comp_toggledialog"+"&vs="+_nc_js_vs, "window.compToggleDialog", function(){
							self.dialogs = new compToggleDialog(self);
						});

						self.addUndo(true);
						// nc20用
						if(self.focus)
							self.addFocus();
					}
                };
				Event.observe(editor,"load", self.onloadEvent, false, self.id);
            }

            this.start_w = newX;
            this.start_h = newY;

			var panel = this.panel = document.createElement('div');
			Element.addClassName(panel,"nc_wysiwyg_panels");

            var statusbar = this.statusbar = document.createElement('ul');
			Element.addClassName(statusbar,"statusbar");

			/**
			 * toolbar作成
             */
            this.appendControls();
            this.el = document.createElement('div');
            this.el.id = this.top_id;
            Element.addClassName(this.el, "nc_wysiwyg_outer");
            // nc20用
            this.top_table = this.el;

            this.el.appendChild(panel);
             var div = document.createElement('div');
            div.innerHTML = "<!-- -->";
            Element.setStyle(div, { clear : 'both' });
            this.el.appendChild(div);
            this.el.appendChild(editor);
            Element.setStyle(el, {display:'none'});
			this.insertBefore(el, this.el)
			Element.setStyle(this.el, {
                width : ( newX > 0 ) ? ( newX + parseInt(Element.getStyle(this.el, 'padding-left') || 0)).toString() + 'px' : '100%'
            });
            this.el.appendChild(el);
            this.el.appendChild(statusbar);
            var div = document.createElement('div');
            div.innerHTML = "<!-- -->";
            Element.setStyle(div, { clear : 'both' });
            this.el.appendChild(div);

			/**
			 * statusbar作成
             */
            this.appendStatusbar();
        },
        /* iframe initialize */
		initFrame : function(content) {

			var self = this, n, r, sn, en , br;
			if(content != undefined)
				this.options.content = content;
			else if(!self.editorDoc || !self.editorDoc.parentNode)
				this.options.content = this.initialContent;


			this._initFrame(this.editorDoc, this.options);

			Event.observe(this.editorDoc,"mouseup", function(e) {
                self.bookmark = self.getBookmark();	// IEはbookmarkを保持しないため
            	self.currentNode = self.getSelectNode();
                self.checkTargets(Event.element(e));
                self.addUndo();
                self.closeDialogs();
            }, false, this.id);

			Event.observe(this.editorDoc,"contextmenu", function(e) {
                // コンテキストメニュー
				_addEvents(e, "contextmenu");
            }, false, this.id);

			Event.observe(this.editorDoc,"keydown", function(e) {
				if(browser.isSafari && (e.keyCode == 46 || e.keyCode == 8)) {
            		// １行選択してdelete(backspace)ボタン、
            		// または、1行にわたるNodeを選択してdelete(backspace)
            		// ボタンを押すと、そのelementが削除されないため対処
					var data = null, p, pre_p, cur_flag, r = self.getRange(), f = self.getSelectNode();
					switch (r.startContainer.nodeType) {
					    case 3:
					    case 4:
					    case 8:
					    	data = r.startContainer.data;
					    	break;
					}
					p = f;
					cur_flag = true;
					do {
						if(cur_flag == true) {
							cur_flag = false;
						 	if(!(p.childNodes.length == 1 && f.innerHTML == data))
						 		break;
						 } else {
						 	if(p.nodeName.toLowerCase() == 'body' || p.childNodes.length != 1) {
						 		p = pre_p;
						 		break;
						 	}
						 }
						 pre_p = p;
					} while ( p = p.parentNode );

                  	if(data != null && f.innerHTML == data && p.nextSibling.nodeName.toLowerCase() == 'br') {
                  		r.setEndAfter(p.nextSibling);
						self.setRange(r);
                  	}
                  	// 削除後、さらに入力すると削除前のスタイルが残ってしまうため対処
                  	setTimeout(function() {self.collapse();}, 100);
				}
				if(e.ctrlKey && e.keyCode == 90) {
            		// undo
            		self.undo();
            		Event.stop(e);
    				return false;
            	}
            	if(e.ctrlKey && e.keyCode == 89) {
            		// redo
            		self.redo();
            		Event.stop(e);
    				return false;
            	}
            	if(browser.isIE && e.keyCode == 8) {
            		// brのみの状態でBackspaceを押すとpタグに変換されてしまうため対処
            		setTimeout(function() {
            			if(self.editorDoc.body.innerHTML.toLowerCase() == "<p>&nbsp;</p>") {
            				self.editorDoc.body.innerHTML = "<br />";
            				self.rangeSelect(self.editorDoc.body.childNodes[0]);
            				self.collapse(true);
            				self.checkTargets();
            			}
            		}, 100);
            	}
            }, false, this.id);

            Event.observe(this.editorDoc,"keypress", function(e) {
				n = self.getSelectNode();
            	if( e.keyCode == 13 && e.shiftKey == false && self.detachBlockQuote(n)) {
            		Event.stop(e);
					return false;
            	} else if ( !browser.isFirefox && e.keyCode == 13 ) {
            		if(n.nodeName.toLowerCase() != 'li') {
            			if (browser.isIE) {
            				var rng = self.getRange();
		                    rng.pasteHTML('<br />');
		                    rng.collapse(false);
		                    rng.select();
		                } else if(browser.isSafari) {
		                	self.editorDoc.execCommand("InsertLineBreak", false, []);
		                	var bm = self.getBookmark();
		                	self.getWin().scrollTo(bm.scrollX, bm.scrollY+16);
		                } else {
		                	r = self.getRange();
		                	sn = r.startContainer;
							r = self.getRange();
							if(r.startContainer && r.startContainer.nextSibling)
								n = r.startContainer.previousSibling;
	            			self.editorDoc.execCommand('inserthtml', false, '<br><br id="nc_wysiwygbr">');
							r = self.getRange();
	            			en = r.startContainer;
	            			br = self.editorDoc.getElementById('nc_wysiwygbr');
	            			if(sn != en || browser.isOpera) {
		            			r.setStartBefore(br);
								r.setEndBefore(br);
								self.setRange(r);
							}
							if(sn != en && !browser.isOpera) {
								br.removeAttribute("id", 0);
								self.rangeSelect(br);
		                    } else {
		                    	br.removeAttribute("id", 0);
		                    	var buf_br = br.previousSibling;
	                    		br.parentNode.removeChild(br);
	                    		br = buf_br;
		                    }
		                    r.setStartAfter(br);
							r.setEndAfter(br);
							self.setRange(r);
							var bm = self.getBookmark();
							self.getWin().scrollTo(bm.scrollX, bm.scrollY+16);
	           			}
	           			Event.stop(e);
	           			return false;
            		}
            	}
            }, false, this.id);

			Event.observe(this.editorDoc,"keyup", function(e) {
				var k = e.keyCode;
				self.bookmark = self.getBookmark();	// IEはbookmarkを保持しないため
				self.currentNode = self.getSelectNode();
                if ((k >= 33 && k <= 36) || (k >= 37 && k <= 40) || k == 13 || k == 45 || k == 46 || k == 8  ||
                		(e.ctrlKey && (k == 86 || k == 88)) || k.ctrlKey || (this.is_mac && (k == 91 || k == 93))) {
            		// enter、上下左右、baskspace, Delキー,カット＆ペーストならば、checkTargetsを呼び出す
            		self.checkTargets(self.currentNode);
            		self.addUndo();
            	}
            	if ( self.options.autoSave )
            		self.saveContent();
            }, false, this.id);

			Event.observe(this.editorDoc,"dblclick", function(e) {
				if(self.getMode() == "edit")
					_addEvents(e, "dblclick");
            	Event.stop(e);
    			return false;
            }, false, this.id);

			// auto regist
            if( this.options.autoRegist ) {
            	this.autoregist = this.getParseContent();
				setTimeout(function() {
					self.regist(null, null, true);
					if (self.options.autoRegist && self.el)
						setTimeout(arguments.callee, self.options.regist_time);
				}, this.options.regist_time);
            }

            return;

            function _addEvents(e, type) {
            	var n = Event.element(e);
				var node_name = n.nodeName.toLowerCase(), chk = false;
				for (var i = 0; i < self.events.length; ++i) {
            		if(self.events[i][type]) {
            			// イベントが一致
            			for (var j = 0; j < self.eventstags[i].length; ++j) {
            				if(node_name == self.eventstags[i][j]) {
            					chk = true;
            					break;
            				}
            			}
            			// タグ一致
            			var ret = false;
            			if(chk == true)
	            			ret = self.events[i][type].call(self, e, n);
            			if(ret)
            				break;
            		}
            	}
            }
		},

		_initFrame : function(doc, options) {
			var self = this, headstr='', vq;
			var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
			var br = '<br />';

			if (options.css)
                for (var i = 0; i < options.css.length; ++i)
	                headstr += '<link rel="stylesheet" type="text/css" media="screen"  charset="utf-8" href="' + options.css[i] + '" />';

            if (options.js)
            	for (var i = 0; i < options.js.length; ++i)
	                headstr += '<script type="text/javascript" charset="utf-8" src="' + options.js[i] + '"></script>';

			if (options.cssInc) {
				// 親のCSSをinclude
				var links = $A(document.getElementsByTagName("link"));
				links.each(function(v){
					headstr += '<link rel="' + v.getAttribute("rel") + '" type="' + v.getAttribute("type") + '" media="' + v.getAttribute("media") + '" href="' + v.getAttribute("href") + '" />';
				});
			}
			headstr += '<style>html,body {height : 100% !important; background-image : none; padding:0px;margin:0px; background-color:#ffffff;color:#494949;}';
			if((browser.isIE && browser.version < 9) || browser.isOpera) {
				//options.htmlの値を変更したことにより、tableのフォントサイズが一サイズ大きくなったため
				headstr += 'td {font-size:80%;}';
			}
			headstr += '</style>';

			if(options.content != undefined && options.content.trim().match(re))
				br = '';						// 最後にbrがある場合は、追加しない

			doc.open();
            doc.write(
                options.html
					.replace(/INITIAL_TITLE/, options.title)
                    .replace(/INITIAL_CONTENT/, (options.content != undefined) ? options.content.replace(/\$/g,"$$$$") + br : '<br />')
                    .replace(/INITIAL_HEADER/, headstr)
            );
            doc.close();

            if (options.style)
            {
                setTimeout(function()
                {
                	Element.setStyle(doc.body, options.style);
                }, 100);
            }
            this._setStyleWithCSS();
		},

		getWin : function(iframe) {
			return (typeof iframe == "undefined") ? this.editor.contentWindow : iframe.contentWindow;
		},

        getContent : function()
        {
            var html = this.editorDoc.body.innerHTML;
            if ( this.options.rmUnwantedBr ) {
				var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
				html = html.replace(re, '');
			}
			return html;
        },

        setContent : function(newContent, body)
        {
        	body = (body == undefined) ? this.editorDoc.body : body;
        	//SCRIPTタグをinnerHTMLした場合、IEでは削除されるため
			if((browser.isIE && browser.version < 9) || browser.isSafari) {
				body.innerHTML = "<br />" + newContent;
				body.removeChild(body.firstChild);
			} else {
				body.innerHTML = newContent;
			}
        },

        saveContent : function(parse_flag)
        {
            if ( this.original )
            {
                var content = (parse_flag && this.options.parseHtml) ? this.parseContent() : this.getContent();

                this.original.value = content;
                return content;
            }
        },

		// getTextAreaと同等の処理 nc2系では、このmethodの別名とする
		getParseContent : function(root)
		{
			return this.parseContent(true, true, root);
		},

		clear : function()
        {
            this.setContent('<br />');
            this.saveContent();
            this.addUndo();
        },

        appendMenu : function( panel, cmd, args, className, fn )
        {
            var self = this;
            var args = args || [];
            var li = document.createElement('li');
            Element.addClassName(li,"l");
            var a = document.createElement('a');
            a.setAttribute('href','javascript:;',0);
            a.innerHTML = "<!-- -->";
            Element.addClassName(a,"l");
            Element.addClassName(a,className || cmd);
            Event.observe(a,"click", function(e) {
				var li = Event.element(e).parentNode;
				if(self.getMode() == 'edit' && Element.getStyle(li, 'opacity') >= 1.0) {
					self.closeDialogs(Event.element(e).nextSibling);
					args.push(e);
					self.addFocus(true);
					if ( fn ) fn.apply(self, args); else self.editorDoc.execCommand(cmd, false, args);
					if ( self.options.autoSave ) self.saveContent();
					self.addUndo();
				}
				self.addFocus(false, function(){
												if(browser.isIE)
													self.moveToBookmark(self.bookmark);
											});
				Event.stop(e);
				return false;
            }, false, this.id);
            li.appendChild(a);
            panel.appendChild(li);
            self.panel_btns[self.top_id + className] = li;

            var id = this.top_id + '_btn_' + cmd;
            ////var id = this.top_id + '_btn_' + $.data(li);
            li.setAttribute('id',id, 0);

            if(cmd == 'forecolor') {
				// forecolorのデフォルト色
				Element.setStyle(li.childNodes[0], {'backgroundColor': self.options.forecolor});
			} else if(cmd == 'hilitecolor') {
				// hilitecolorのデフォルト色
				Element.setStyle(li.childNodes[0], {'backgroundColor': self.options.hilitecolor});
			}

            return li;
        },

        appendList : function( panel, cmd, args, className, fn, list )
        {
        	var self = this, callback, btn_callback;
        	var li = document.createElement('li');
            Element.addClassName(li,"l");
            panel.appendChild(li);
        	for ( var i in list ) {
        		if(cmd == 'formatblock' && i != '') {
					list[i] = '<' + i + '>' + list[i] + '</' + i + '>';
				} else if(cmd == 'fontname' && i != '') {
					list[i] = '<div style="font-family:' + i.replace(/\\/, "\\\\") + '">' + list[i] + '</div>';
				} else if(cmd == 'fontsize' && i != '') {
					list[i] = '<div style="font-size:' + i + '">' + list[i] + '</div>';
				}
			}
			self.panel_btns[self.top_id + className] = li;

			callback = function(e, key, value) {
				var event_el = Event.element(e);
				var buf_event_el = Element.getParentElementByClassName(event_el,"listmenu_sub");
				if(!buf_event_el)
					event_el = Element.getParentElementByClassName(event_el,"listbox");
				else
					event_el = buf_event_el;
				if(key == "") {
					//if(self.getMode() != 'edit') return false;
            		self.closeDialogs(event_el.nextSibling);
            		return true;
				}
        		var li = Element.getParentElementByClassName(event_el,"l");
				if(self.getMode() == 'edit' && Element.getStyle(li, 'opacity') >= 1.0) {
					var args = args || [key] || [];
					if ( fn ) fn.apply(self, args); else self.editorDoc.execCommand(cmd, false, args);
					if ( self.options.autoSave ) self.saveContent();
					self.addUndo();
				}
				self.addFocus(false, function(){
												if(browser.isIE)
													self.moveToBookmark(self.bookmark);
											});
        	};

        	return self.appendCommonList(li, list, className || cmd, callback, args);
        },

        appendControls : function()
        {
        	var t = this, panel = document.createElement('ul');
            Element.addClassName(panel,"nc_wysiwyg_panel");
			this.panel.appendChild(panel);

			for (var line = 0; line < this.controls.length; ++line) {
				// 改行
				var br_flag = false;
				if(li && line != 0 && this.mode != "simple") br_flag = true;

				for ( var group in this.controls[line] ) {
					var group_li = null;
					var first = false;
					for ( var name in this.controls[line][group]["value"] ) {
						var control = this.controls[line][group]["value"][name];

						// netcommons用グローバル定義によるボタン非表示
						if(name == "insertimage" && _nc_allow_attachment == 0 ||
							name == "insertfile" && _nc_allow_attachment <= 1 ||
							name == "insertvideo" && _nc_allow_video == 0)
							control.visible = false;

						if ( control.visible ) {
							// ボタン背景
							if(group_li) {
								if(!first) Element.addClassName(group_li,"lbtn");
								else Element.addClassName(group_li,"cbtn");
								first = true;
							}

							if(br_flag) {
								panel = document.createElement('ul');
								Element.addClassName(panel,"nc_wysiwyg_panel");
								this.panel.appendChild(panel);
								br_flag = false;
							}

							if ( control.list ) {
								// リスト表示
								var li = t.appendList(
									panel,
			                        control.command || name,
			                        control.arguments || [],
			                        control.className || control.command || name || 'empty',
			                        control.exec,
			                        control.list
			                    );
			                    Element.addClassName(li,"list");
			                    Event.observe(li,"click", function(e) {
			                    	var c_a = Event.element(e);
			                    	c_a = Element.getParentElementByClassName(c_a,"listbox");
									if(c_a && Element.getStyle(c_a.parentNode.parentNode, 'opacity') < 1.0) {
										if(Element.getStyle(c_a.nextSibling, "display") == "none")
											Element.setStyle(c_a.nextSibling, {display:'block'});
										else
											Element.setStyle(c_a.nextSibling, {display:'none'});
				                    	Event.stop(e);
							           	return false;
							        }
					            }, false, this.id);
							} else {
								var li = t.appendMenu(
									panel,
			                        control.command || name,
			                        control.arguments || [],
			                        control.className || control.command || name || 'empty',
			                        control.exec
			                    );
			                    if(control.liClassName)
									Element.addClassName(li,control.liClassName);
								if(control.title)
									li.childNodes[0].setAttribute('title',control.title,0);
								else if(compTextareaLang['icons'][name]){
									li.childNodes[0].setAttribute('title', compTextareaLang['icons'][name], 0);
								}
								group_li = li;
							}
							if ( control.event ) {
								t.events.push(control.event);
								t.eventstags.push(control.eventtags);
							}

							if( control.components ) {
								t.components = $H(t.components).merge($H(control.components));
							}

							if ( control.collapsedDis ) {
								Element.addClassName(li,"collapsedDis");
								Element.setStyle(li, {opacity:'0.4'});
							}
							if ((Element.hasClassName(li,"nc_wysiwyg_arrow") || Element.hasClassName(li,"nc_wysiwyg_rarrow")) && ( control.tags || control.css )) {
								Element.setStyle(li, {opacity:'0.4'});
							}
						}
					}
					if(group_li) {
						if(!first) Element.addClassName(group_li,"btn");
						else Element.addClassName(group_li,"rbtn");
					}

				}
			}
        },

		appendModeMenu : function( lang_key, active_flag, className )
        {
            var self = this;
			className = className || lang_key;
			var li = document.createElement('li');
			var a = document.createElement('a');
			a.innerHTML = compTextareaLang[lang_key];
			li.appendChild(a);
			if(active_flag) Element.addClassName(li,"active");
			Event.observe(li, "mousedown", function(e) {
				var e_li = Event.element(e).parentNode;
				var pre_mode = self.getMode();
				for (var i = 0; i < e_li.parentNode.childNodes.length; ++i) {
					var el = e_li.parentNode.childNodes[i];
					(el == e_li) ? Element.addClassName(el,"active") : Element.removeClassName(el,"active");
				}
				switch (className) {
					case "edit":
						self.chgEdit(pre_mode);
						break;
					case "html":
						self.chgHtml(pre_mode);
						break;
					case "preview":
						self.chgPreview(pre_mode);
						break;
				}
            }, false, this.id);
            this.statusbar.appendChild(li);
            Element.addClassName(li,className);
        },

		appendStatusbar : function()
        {
			var modemenu = ["edit", "html", "preview"];
			for (var i = 0; i < modemenu.length; ++i)
				this.appendModeMenu(modemenu[i], (modemenu[i] == "edit") ? true : false);

			// pathメニュー
			var li = document.createElement('li');
			li.innerHTML = compTextareaLang['path']+'&nbsp;:&nbsp;';
			Element.addClassName(li,"path");
			this.statusbar.appendChild(li);
			li.setAttribute('id','path_'+ this.top_id,0);

			// resizeメニュー
			this.appendResize("resize");
		},

		appendResize : function(className) {
			var self = this, img_w = ((browser.isIE && browser.version < 9)) ? 15 : 25;
			var resize = document.createElement('a');
			var li = document.createElement('li');
			li.appendChild(resize);
			Element.addClassName(li,className);

			setTimeout(function()
            {
				var s_w = 0;
				for (var i = 0; i < self.statusbar.childNodes.length; i++) {
					var s_li = self.statusbar.childNodes[i];
					s_w += parseInt(Element.getStyle(s_li, "width") || 0)
							 + parseInt(Element.getStyle(s_li, "paddingLeft") || 0) + parseInt(Element.getStyle(s_li, "paddingRight") || 0);
				}
            	Element.setStyle(li, {left: parseInt(Element.getStyle(self.editor, "width") || 0) - s_w - img_w + 'px'});
				self.statusbar.appendChild(li);
            }, 100);

            // リサイズmousedownイベント
            Event.observe(resize, "mousedown", function(e) {
				var sx = null, sy = null;
            	var m_w = Element.getStyle(self.el, "width");
            	var m_h = Element.getStyle(self.el, "height");
            	var w = Element.getStyle(self.editor, "width") || Element.getStyle(self.original, "width");
            	var h = Element.getStyle(self.editor, "height") || Element.getStyle(self.original, "height");
            	var r_w = Element.getStyle(resize, "width");

				self.editor.blur();

            	Element.setStyle(self.editor, {display:'none'});
            	Element.setStyle(self.original, {display:'none'});

	            self.resize = document.createElement('div');
	            Element.setStyle(self.resize, {width : w, height : h});
	            Element.addClassName(self.resize,"resizebox");
	            self.insertBefore( self.editor, self.resize );

            	// リサイズmousemoveイベント
            	var resizeMouseMove = function(event) {
            		var x_offset = 0, y_offset = 0;
            		if(sx == null) {
            			sx = Event.pointerX(event), sy = Event.pointerY(event);
            		} else {
            			x_offset = Event.pointerX(event) - sx, y_offset = Event.pointerY(event) - sy;
            		}
            		if(parseInt(w || 0) + x_offset < self.start_w) x_offset = self.start_w - parseInt(w || 0);
            		if(parseInt(h || 0) + y_offset < self.start_h) y_offset = self.start_h - parseInt(h || 0);

					// リサイズ
					Element.setStyle(self.resize, {
       					width      : parseInt(w || 0) + x_offset + 'px',
	                    height     : parseInt(h || 0) + y_offset + 'px'
	                });

					Element.setStyle(self.el, {
       					width      : parseInt(m_w || 0) + x_offset + 'px',
	                    height     : parseInt(m_h || 0) + y_offset + 'px'
	                });

					Element.setStyle(resize, {
       					width      : parseInt(r_w || 0) + x_offset + 'px'
	                });
	                Event.stop(event);
            	}.bindAsEventListener(self);

            	// リサイズmouseupイベント
            	var resizeMouseUp = function(event) {
            		var mode = self.getMode();
            		var w = Element.getStyle(self.resize, "width");
            		var h = Element.getStyle(self.resize, "height");

            		// リサイズ
            		Element.setStyle(self.editor, {
       					width      : Element.getStyle(self.resize, "width"),
	                    height     : Element.getStyle(self.resize, "height")
	                });
            		Element.setStyle(self.original, {
       					width      : Element.getStyle(self.resize, "width"),
	                    height     : Element.getStyle(self.resize, "height")
	                });
	                self.resize.parentNode.removeChild(self.resize);
	                Event.stopObserving(document,"mousemove", resizeMouseMove,false);
            		Event.stopObserving(document,"mouseup", resizeMouseUp,false);

            		(mode != "html") ? Element.setStyle(self.editor, {display:'block'}) : Element.setStyle(self.original, {display:'block'});
            		self._setStyleWithCSS();
            		Event.stop(event);
            	}.bindAsEventListener(self);

            	Event.observe(document,"mousemove", resizeMouseMove);
            	Event.observe(document,"mouseup", resizeMouseUp);
            	Event.stop(e);
            }, false, this.id);
		},

		chgEdit : function(pre_mode) {
			var self =this;
			if(pre_mode == "edit")
				return;
			var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
            var ul_list = $A(this.panel.getElementsByTagName("ul"));
			ul_list.each(function(v){
				Element.setStyle(v, {opacity:''});
			});

            this.closeDialogs();
            if(pre_mode == "html") {
            	this.setContent(this.original.value);
            }
			// blankを取り除いたものを表示
			var content = this.parseContent(true);
			this.original.value = content;
			if(!((browser.isIE && browser.version < 9) || browser.isOpera) && !content.match(re)) {
				// 最後にbrが無い場合、付与
				this.setContent(content + '<br />');
			} else
				this.setContent(content);
			Element.setStyle(this.editor, {display:'block'});
			Element.setStyle($('path_'+ this.top_id), {visibility : 'visible'});
            Element.setStyle(this.original, {display:'none'});
            this.chgDesignMode('on');

        	// フォーカスの移動
        	this.addFocus(false, this.checkTargets);

        	// Operaでモードを変更した場合、iframeのイベントがリセットされるようなので修正(Ver9.64)
            if((browser.isIE && browser.version < 9) || browser.isOpera)
	            this.initFrame(content);

			setTimeout(function() {self.chgModeInit("edit");}, 100);
		},

		chgHtml : function(pre_mode) {
			var self =this;
			if(pre_mode == "html")
				return;
			var ul_list = $A(this.panel.getElementsByTagName("ul"));
			ul_list.each(function(v){
				Element.setStyle(v, {opacity:'0.4'});
			});
			this.closeDialogs();
			this.chgModeInit("html");
			this.saveContent(true);
			Element.setStyle(this.original, {display:'block',visibility : 'visible'});
			Element.setStyle($('path_'+ this.top_id), {visibility : 'hidden'});
            Element.setStyle(this.editor, {display:'none'});

            // フォーカスの移動
            this.addFocus();
		},

		chgPreview : function(pre_mode) {
			var self = this;
			if(pre_mode == "preview")
				return;
			else if(pre_mode == "html")
				this.setContent(this.original.value);
			// blankを取り除いたものを表示
			var content = this.parseContent(true);
			this.original.value = content;
			this.setContent(content);
			var ul_list = $A(this.panel.getElementsByTagName("ul"));
			ul_list.each(function(v){
				Element.setStyle(v, {opacity:'0.4'});
			});
			this.closeDialogs();
			//this.saveContent();
			Element.setStyle(this.editor, {display:'block'});
            Element.setStyle(this.original, {display:'none'});
            Element.setStyle($('path_'+ this.top_id), {visibility : 'hidden'});
            this.chgDesignMode('off');
            // IEでdesignModeをoffにした場合、iframeが再読み込みされるため
            if((browser.isIE && browser.version < 9))
	            this.initFrame(content);
	        setTimeout(function() {self.chgModeInit("preview");}, 100);
		},

		/**
		 * モード変更init
		 * tableの枠線が0、かつ、編集モードならば、点線を表示する。
		 */
		chgModeInit : function(mode, root) {
			var t = this, border;
			root = root || this.editorDoc;
			mode = mode || self.getMode();
			if(!root)
				return;
			switch (mode) {
				case "edit":
					var table_list = $A(root.getElementsByTagName("table"));
					table_list.each(function(el){
						var list = $A(['Top','Right','Bottom','Left']);
						list.each(function(v) {
							if(Element.getStyle(el, "border" + v + "Width") == "0px" || Element.getStyle(el, "border" + v + "Style") == "none") {
								el.setAttribute('_nc_wysiwyg_border_' + v.toLowerCase(),'1',0);
								el.style["border" + v + "Width"] = "1px";
								el.style["border" + v + "Style"] = "dotted";
								el.style["border" + v + "Color"] = "#666666";
							}
						});
					});
					var td_list = $A(root.getElementsByTagName("td"));
					td_list.each(function(el){
						var list = $A(['Top','Right','Bottom','Left']);
						list.each(function(v) {
							if(Element.getStyle(el, "border" + v + "Width") == "0px" || Element.getStyle(el, "border" + v + "Style") == "none") {
								el.setAttribute('_nc_wysiwyg_border_' + v.toLowerCase(),'1',0);
								el.style["border" + v + "Width"] = "1px";
								el.style["border" + v + "Style"] = "dotted";
								el.style["border" + v + "Color"] = "#666666";
							}
						});
					});
					break;
				default :
					var table_list = $A(root.getElementsByTagName("table"));
					table_list.each(function(el){
						var list = $A(['Top','Right','Bottom','Left']);
						list.each(function(v) {
							if(el.getAttribute("_nc_wysiwyg_border_" + v.toLowerCase())) {
								el.style["border" + v + "Width"] = "0px";
								el.style["border" + v + "Style"] = "none";
								el.style["border" + v + "Color"] = "";
								if(mode == "html")
									el.removeAttribute("_nc_wysiwyg_border_" + v.toLowerCase(), 0);
							}
						});
					});
					var td_list = $A(root.getElementsByTagName("td"));
					td_list.each(function(el){
						var list = $A(['Top','Right','Bottom','Left']);
						list.each(function(v) {
							if(el.getAttribute("_nc_wysiwyg_border_" + v.toLowerCase())) {
								el.style["border" + v + "Width"] = "0px";
								el.style["border" + v + "Style"] = "none";
								el.style["border" + v + "Color"] = "";
								if(mode == "html")
									el.removeAttribute("_nc_wysiwyg_border_" + v.toLowerCase(), 0);
							}
						});
					});
					break;
			}
			//PreviewでAタグをクリックしてもキャンセルする処理を入れる
			if (mode == "preview") {
				var aList = root.getElementsByTagName("A");
				for (var i = 0,aLen = aList.length; i < aLen; i++) {
					if(aList[i].target != "_blank" && aList[i].onclick == undefined) {
						aList[i].onclick = function(){return false;};
					}
				}
			}
		},

		chgDesignMode : function(mode) {
			if(browser.isIE && browser.version >= 9) {
				// designModeをonにしたものをoffにした場合、
				// 「Internet Explorerは動作を停止しました」
				// と表示されてしまうため、offには設定させないように修正
				mode = 'on';
			}
			this.editorDoc.designMode = (mode != undefined) ? mode : ((this.editorDoc.designMode != 'on') ? 'on' : 'off');
			this._setStyleWithCSS();
		},

		_setStyleWithCSS : function(v) {
			var self = this;
			if((browser.isFirefox || (browser.isIE && browser.version >= 9))) {
				setTimeout(function() {
					try {
						self.editorDoc.execCommand("styleWithCSS", v || false, false);
					} catch (e) {
						try {self.editorDoc.execCommand("useCSS", v || false, true);} catch (e) {}
					}
				}, 500);
			}
		},

		_nodeChanged : function( el )
		{
			var self = this, sep = '&nbsp;&gt;&gt;&nbsp;', a, pa, t=0, nodeN;
			var spn = document.createElement('span');
			spn.innerHTML = compTextareaLang['path'] + '&nbsp;:&nbsp;';
			var path = document.getElementById('path_'+ this.top_id);
			path.innerHTML = '';
			path.appendChild(spn);
			var n_el = el, buf_n;
			do {
				nodeN = el.nodeName.toLowerCase();
			    if ( el.nodeType != 1 || nodeN == 'body' ||  nodeN == 'html')
			        break;
			    if(nodeN == "b")
			    	nodeN = "strong";

			    a = document.createElement('a');
			    a.setAttribute('href','javascript:;',0);
			    Element.addClassName(a, 'nc_wysiwyg_path_' + t);
			    Event.observe(a,"click", function(e) {
					var n = Event.element(e);
	            	if (n.nodeName == 'A') {
	            		var cth = 0, th = n.className.replace(/^.*nc_wysiwyg_path_([0-9]+).*$/, '$1');
	            		n = n_el ;	//self.getSelectNode();
	            		do {
	            			if(n.nodeType == 1 && cth++ == th) {
	            				if((browser.isOpera || browser.isSafari) && n.nodeName == 'TABLE') {
	            					buf_n = n.getElementsByTagName("tbody");
	            					if(buf_n && buf_n[0])
	            						n = buf_n[0];
	            				}
	            				self.addFocus(true);
	            				self.rangeSelect(n);
	            				self.checkTargets();
	            				self.bookmark = self.getBookmark();	// IEはbookmarkを保持しないため
	            				self.currentNode = self.getSelectNode();
	            				self.closeDialogs();
	            				break;
	            			}
	            			if ( n.nodeName.toLowerCase() == 'body' ||  n.nodeName.toLowerCase() == 'html') break;
	            		} while ( n = n.parentNode );
					}
	            	Event.stop(e);
	            	return false;
	            }, false, this.id);

	            a.innerHTML = nodeN;

	            if(t == 0) this.insertAfter(spn, a);
	            else this.insertBefore(pa, a);

	            pa = a;
			    t++;
			} while ( el = el.parentNode );
		},

        checkTargets : function( element )
        {
        	var o,bm;
        	element = element || this.getSelectNode();

			if(this._checkNode != element) {
				this._checkNode = element;
	        	// path
				this._nodeChanged( element );
				this.currentNode = element;

				// 上部ボタンの色変更
				for (var line = 0; line < this.controls.length; ++line)
				{
					for ( var group in this.controls[line] )
					{
						for ( var name in this.controls[line][group]["value"] )
						{
							var control = this.controls[line][group]["value"][name];
							var className = control.className || control.command || name || 'empty';

			                var li = this.panel_btns[this.top_id + className]; //$($('.' + className, this.panel)[0].parentNode);
							var a_className = Element.hasClassName(li,"nc_wysiwyg_arrow") ? "nc_wysiwyg_arrow_active" : (Element.hasClassName(li,"nc_wysiwyg_rarrow") ? "nc_wysiwyg_rarrow_active" : "active");
			                Element.removeClassName(li, a_className);
			                if ( !control.visible)
			                	continue;
			                if ( control.list) {
			                	var list_control = Element.getChildElementByClassName(li, "listcontent");
			                	list_control.innerHTML = control.list[""];
			                }

			                if ( control.active_class )
			                {
			                    var el = element;

			                    do {
			                        if ( el.nodeType != 1 )
			                            break;

									var in_array_flag = false;
									for (var i = 0; i < control.active_class.length; ++i) {
										if(Element.hasClassName(el, control.active_class[i])) {
											var act_class = control.active_class[i];
											in_array_flag = true;
											break;
										}
									}
			                        if ( in_array_flag ) {
			                            Element.addClassName(li, a_className);
			                            if(control.list && typeof control.list[act_class] != 'undefined')
			                            	list_control.innerHTML = control.list[act_class];
			                        	if(control.extend_body == false) break;
			                        }
			                    } while ( el = el.parentNode );
			                }

			                if ( control.tags )
			                {
			                    var el = element;

			                    do {
			                        if ( el.nodeType != 1 )
			                            break;

									var in_array_flag = false;
									for (var i = 0; i < control.tags.length; ++i) {
										if(el.tagName.toLowerCase() == control.tags[i]) {
											in_array_flag = true;
											break;
										}
									}
			                        if ( in_array_flag ) {
			                            Element.addClassName(li, a_className);
			                            if(control.list && typeof control.list[el.tagName.toLowerCase()] != 'undefined')
			                            	list_control.innerHTML = control.list[el.tagName.toLowerCase()];
			                        	if(control.extend_body == false) break;
			                        }
			                    } while ( el = el.parentNode );
			                }

			                if ( control.css )
			                {
			                    var el = element, break_flag = false;
			                    do {
			                        if ( el.nodeType != 1 )
			                            break;

			                        for ( var cssProperty in control.css ) {
										if(el.style[cssProperty] == undefined || el.style[cssProperty] == '')
											continue;
										if(browser.isSafari && cssProperty == "fontFamily") {
											var p_key = Element.getStyle(el, cssProperty);
											p_key = p_key.toString().replace(/, /g, ",");
										} else {
											var p_key = Element.getStyle(el, cssProperty);
											p_key = p_key.toString();
										}
										if ( control.list && control.list[p_key]) {
											// リスト表示に一致するものあり
											Element.addClassName(li, a_className);
											list_control.innerHTML = control.list[p_key];
											if(control.extend_body == false) {
												break_flag = true;
												break;
											}
										} else if ( (Element.getStyle(el, cssProperty)).toString().toLowerCase() == control.css[cssProperty] ) {
			                                Element.addClassName(li, a_className);
			                                if(control.extend_body == false) {
			                                	break_flag = true;
			                                	break;
			                                }
										}
									}
									if(break_flag)
										break;
			                    } while ( el = el.parentNode );
			                }
			                if ( a_className != "active" && ( control.tags || control.css || control.active_class)) {
			                	Element.setStyle(li, {opacity:(Element.hasClassName(li, a_className) ? '' : '0.4')});
			                }
						}
					}
				}
			}
			// 選択範囲が折りたたまれている場合、ボタンを有効化しない
			bm = this.getBookmark();
			if( element && element.nodeName.toLowerCase() != "img" && (!bm || (browser.isIE && bm.length == 0) || (!browser.isIE && bm.start == bm.end))) {
				o = Element.getElementsByClassName(this.panel, "collapsedDis");
				$A(o).each(function(v) {
					Element.setStyle(v, {opacity:(!Element.hasClassName(v, "active") ? '0.4' : '')});
				});
			} else {
				o = Element.getElementsByClassName(this.panel, "collapsedDis");
				$A(o).each(function(v) {
					Element.setStyle(v, {opacity : ''});
				});
			}
        },

        parseContent : function(blank_flag, clone_flag, root) {
        	var self = this, root, blank_flag = blank_flag || false;
        	var mes  = [];
        	var edit_mode = self.edit_mode;
        	self.edit_mode = this.getMode();
			if( clone_flag ) {
				if(!root) {
					root = this.editorDoc.createElement('div');
					this.setContent(this.editorDoc.body.innerHTML, root);
				}
			}else
				root = root || this.editorDoc.body;

        	if((browser.isFirefox || (browser.isIE && browser.version >= 9)) && root.lastChild && root.lastChild.nodeType == 1 && root.lastChild.getAttribute("type") == "_moz") {
        		// mozBRの削除処理（type=_moz）
        		root.lastChild.parentNode.removeChild(root.lastChild);
			}
			if((browser.isFirefox || (browser.isIE && browser.version >= 9)) && root.firstChild && root.firstChild.nodeType == 1 && root.firstChild.getAttribute("_moz_editor_bogus_node")) {
        		//_moz_editor_bogus_nodeの削除処理（type=_moz）
        		root.firstChild.parentNode.removeChild(root.firstChild);
			}
        	//WYSIWYGで自動的に付与される属性を削除（_moz_･･･等）
        	if( !clone_flag )
        		this.setContent(root.innerHTML);

        	//try{
        		var html ="";
        		var tab_space_num = (blank_flag) ? '' : this.options.tabStr;		// tabの半角スペース数
        		var closingTags = " head noscript script style div span tr td tbody table em strong b i code cite dfn abbr acronym font a title sub sup object em strike s ";
        		var allowEmpty = " td script object iframe video ";

        		var deleteNode = /[\t\r\n ]/g;
        		if(browser.isOpera)
        			var deleteText = /(\t|\r|\n)/g;
        		else
        			var deleteText = /[\t\r\n]/g;
        		var parse = function(root, tab_space) {
        			var html = "",row_html,closed, a,name,value,attrs,alt_flag, node_name,pre_type = null,split_style, split_buf, split_params;
        			var n = (blank_flag) ? '' : self.options.lineBreak;
        			var re = new RegExp(n + "$"), unit, embed_flag, embed_at, param_html, param_name, althtml_html;
        			for (var node = root.firstChild; node; node = node.nextSibling) {
	        			row_html = "";
	        			switch (node.nodeType) {
						    case 1: // Node.ELEMENT_NODE
						    case 11: // Node.DOCUMENT_FRAGMENT_NODE
						    	node_name = node.tagName.toLowerCase();

								if(node_name == 'b') node_name = 'strong';
								//iframeの場合<iframe src="XXX" />と変換してしまうとjavascriptの読み込みがストップされるようなので
								//中の値があるなしにかかわらず、閉じタグを挿入する
								if(node.tagName.toLowerCase() == "iframe") {
									closed = false;
								} else {
									closed = (!(node.hasChildNodes() || node.nodeType == 1 && (closingTags.indexOf(" " + node.tagName.toLowerCase() + " ") != -1)));
								}
								if(closed == false && node.innerHTML.replace(deleteNode,'') == '' && allowEmpty.indexOf(" " + node.tagName.toLowerCase() + " ") == -1) {
									// block要素の中身が空なのでスルー
									if(pre_type == 3) {
										// 1つ前がTextならば、最後の改行削除
										html = html.replace(re, '');
									}
									if(!clone_flag) mes.push(compTextareaLang['mes']['del_empty'].replace(/%s/, node_name));
									continue;
								}
								attrs = node.attributes;
								alt_flag = false;
								embed_flag = false;
								althtml_html = '';
								if((browser.isIE && browser.version < 9) && node_name == "embed") {
									embed_flag = true;
									embed_at = '';
									param_html = '';
									$A(['width','height']).each(function(v) {
										value = node.getAttribute(v);
										if(node.style[v]) {
											embed_at += v + ":" + node.style[v] + ";";
										} else if(value) {
											unit = (value.match(/%$/)) ? "%" : "px";
											embed_at += v + ":" + parseInt(value) + unit + ";";
										}
									});
									if(embed_at != '')
										embed_at = ' style="' + embed_at + '"';
								}

								if((browser.isIE && browser.version < 9) && node_name.match(/^\//i))
									continue;
								row_html += "<" + node_name;
								for (var i = 0; i < attrs.length; ++i) {
									a = attrs.item(i);
									if (!a.specified) {
										continue;
									}
									name = self.getNodeName(a);
									value = self.getNodeValue(node, a);
									// border,width,height属性は、style指定に変換
									if((name == "border" || name == "width" || name == "height")) {
										if(parseInt(value) == "0")
											value = valueParseInt(Element.getStyle(node, name));
										unit = "px";
										if(name == "border" && (node.style.borderWidth == "" || node.style.borderWidth == "0px"))
											Element.setStyle(node, {borderWidth : parseInt(value) + unit});
										else if(node.style[name] =="") {
											node.style[name] = parseInt(value) + unit;
										}

									}
								}
								attrs = node.attributes;
								for (var i = 0; i < attrs.length; ++i) {
									a = attrs.item(i);
									if (!a.specified) {
										continue;
									}
									name = self.getNodeName(a);
									value = self.getNodeValue(node, a);
									if((name == "border" || name == "width" || name == "height")) {
										continue;
									}
									if (typeof(value) == 'string')
										value = value.replace(/\"/g, '\'');
									if((browser.isSafari || browser.isOpera || browser.isIE) && name == "style") {
										// 小文字に変換し、border部分のスタイルを整理
										//value = value.toLowerCase();
										split_style = value.split(/;/);
										split_params = {};
										for (var j = 0; j < split_style.length; ++j) {
											split_buf = split_style[j].split(/:/);
											var s_key = '', s_value = '';
											for (var k = 0; k < split_buf.length; ++k) {
												var buf = split_buf[k].replace(/^\s+/, '').replace(/\s+$/, '');
												if(k == 0)
													s_key = buf.toLowerCase();
												else if(k == 1)
													s_value += buf;
												else
													s_value += ':' + buf;
											}
											if(s_key == '' || s_value == 'initial') continue;
											if( s_key != "font-family" ) {
												s_value = s_value.toLowerCase();
											}
											split_params[s_key] = s_value;
										}
										value = '';
										if(split_params['border-top-width'] && split_params['border-top-width'] == split_params['border-right-width'] &&
													split_params['border-top-width'] == split_params['border-bottom-width'] &&
													split_params['border-top-width'] == split_params['border-left-width'])
											value = 'border-width:' + split_params['border-top-width'] + ';';
										else if(split_params['border-top'] && split_params['border-top'] == split_params['border-right'] &&
													split_params['border-top'] == split_params['border-bottom'] &&
													split_params['border-top'] == split_params['border-left'])
											value = 'border:' + split_params['border-top'] + ';';
										else if((split_params['border-top'] && split_params['border-top'] == split_params['border-bottom']) &&
													(split_params['border-right'] && split_params['border-right'] == split_params['border-left']))
											value = 'border:' + split_params['border-top'] + ' ' + split_params['border-right'] + ';';
										var c_value = '';
										if(split_params['border-top-color'] && split_params['border-top-color'] == split_params['border-right-color'] &&
													split_params['border-top-color'] == split_params['border-bottom-color'] &&
													split_params['border-top-color'] == split_params['border-left-color'])
											c_value = 'border-color:' + split_params['border-top-color'] + ';';
										var s_value = '';
										if(split_params['border-top-style'] && split_params['border-top-style'] == split_params['border-right-style'] &&
													split_params['border-top-style'] == split_params['border-bottom-style'] &&
													split_params['border-top-style'] == split_params['border-left-style'])
											s_value = 'border-style:' + split_params['border-top-style'] + ';';
										var m_value = '';
										if(split_params['margin-top'] && split_params['margin-top'] == split_params['margin-right'] &&
													split_params['margin-top'] == split_params['margin-bottom'] &&
													split_params['margin-top'] == split_params['margin-left'])
											m_value = 'margin:' + split_params['margin-top'] + ';';
										var p_value = '';
										if(split_params['padding-top'] && split_params['padding-top'] == split_params['padding-right'] &&
													split_params['padding-top'] == split_params['padding-bottom'] &&
													split_params['padding-top'] == split_params['padding-left'])
											p_value = 'padding:' + split_params['padding-top'] + ';';

										var buf_value = value;
										for (var k in split_params ) {
											var v = split_params[k];
											if(c_value != '' && (k == 'border-top-color' ||
												k == 'border-right-color' || k == 'border-bottom-color' || k == 'border-left-color'))
												continue;
											else if(s_value != '' && (k == 'border-top-style' ||
												k == 'border-right-style' || k == 'border-bottom-style' || k == 'border-left-style'))
												continue;
											else if(m_value != '' && (k == 'margin-top' ||
												k == 'margin-right' || k == 'margin-bottom' || k == 'margin-left'))
												continue;
											else if(p_value != '' && (k == 'padding-top' ||
												k == 'padding-right' || k == 'padding-bottom' || k == 'padding-left'))
												continue;
											else if(buf_value == '' || (k != 'border' &&
												k != 'border-top' && k != 'border-right' && k != 'border-bottom' && k != 'border-left' &&
												k != 'border-top-width' && k != 'border-right-width' && k != 'border-bottom-width' && k != 'border-left-width'))
												value += k + ':' + v + ';';
										}
										value += c_value + s_value + m_value + p_value;
									} else if(name == "alt") {
										alt_flag = true;
									} else if(browser.isIE && name.match(/^jquery/i)) {
										// jquery用の属性がIEだと付与されてしまうため削除
										continue;
									} else if(browser.isIE && name == "althtml") {
										// object：IEのobjectタグ下のembedタグがalthtml属性となってしまう
										althtml_html = value;
										continue;
									}
									if( (browser.isFirefox || (browser.isIE && browser.version >= 9)) && !clone_flag && (name == "src" || name == "href")) {
										// this.setContent(root.innerHTML)した段階でencodeされるようなので元に戻す
										try{
											value = decodeURIComponent(value);
										} catch(e){}
									}
									if(name=="value" || name=="alt" || name=="title") {
										value = String(value).escapeHTML();
									}
									if(name=="style" && value == "")
										continue;
									if(name == "class" && browser.isSafari) {
										var apple_re = new RegExp(/\s*Apple-style.+\s*/i);
										value = value.replace(apple_re, '');
										if(value == "")
											continue;
									}
									row_html += " " + name + '="' + value + '"';
									if(embed_flag && " type style script style code alt hspace vspace border width height ".indexOf(" " + name + " ") == -1) {
										// param属性
										param_name = (name == "src") ? "movie" : name;
										param_html += '<param name="' + param_name + '" value="'+ value +'"></param>' + n;
									}

								}
								// 古いIEはvalue属性をattributesで取得してくれないため
								if (browser.isIE
									&& parseInt(browser.version) <= 8
									&& node_name == "input"
									&& node.value != undefined
									&& !row_html.match(/ value=".*"/i)) {
									row_html += " " + 'value="' + node.value + '"';
								}

								row_html += closed ? " />" : ">" + n;
								if((browser.isIE && browser.version < 9) && (node_name == "script" || (node_name == "object" && parseInt(browser.version) < 8))) {
									// trim
									// object：IEのobjectタグ下のembedタグがalthtml属性となってしまう
									// embedタグは判別しないが、paramタグは判別する
									var embed_html = node.innerHTML.replace(/^\s+/, '').replace(/\s+$/, '') + n;
									if(node_name == "object") {
										if(parseInt(browser.version) < 7) {
											var embed_re = new RegExp(/(<embed(.|\s)+?(\/){1}>)/i);
											embed_html = embed_html.replace(embed_re, '')+ RegExp.$1;
											embed_re = new RegExp(/<param((.|\s)+?)>/ig);
											embed_html = embed_html.replace(embed_re, '<param$1></param>');
										}
										embed_html = embed_html.replace(/&/ig, "&amp;");
										row_html += embed_html.replace(/&amp;amp;/ig, "&amp;");
									} else
										row_html += embed_html;
								} else if(node_name == "script") {
									// scriptタグはそのままの状態を保持
									row_html += node.innerHTML.replace(/^\s+/, '').replace(/\s+$/, '');
								} else if(node_name == "pre") {
									// preタグはそのままの状態を保持
									row_html += node.innerHTML;
								} else {
									row_html += parse(node, tab_space + tab_space_num);
								}
								if(althtml_html)
									row_html += tab_space + althtml_html.trim() + n;
								if (!closed) {
									row_html += tab_space + "</" + node_name + ">";
								}
								if(embed_flag) {
									row_html = "<object"+ embed_at + ">" + n + param_html + n + row_html + "</object>" + n;
								}
								break;
		        			case 3: // Node.TEXT_NODE
		        				node.data = node.data.replace(deleteText,'');
		        				if(node.data == "") continue;
		        				if(/^noscript|script|style|table|tbody|tr$/i.test(node.parentNode.tagName)) {
									row_html = node.data.replace(/^[ ]+/, '').replace(/[ ]+$/, '');
								} else {
									if(blank_flag) {
										if(edit_mode == "html")
											row_html = self.htmlEncode(node.data).replace(/^[ ]+/, '').replace(/[ ]+$/, '');
										else
											row_html = self.htmlEncode(node.data);
									} else {
										row_html = self.htmlEncode(node.data).replace(/^ /ig, "&nbsp;").replace(/ $/ig, "&nbsp;");
									}
								}
								break;
		        			case 4: // Node.CDATA_SECTION_NODE
		        				node.data = node.data.replace(deleteText,'');
		        				if(node.data == "") continue;
		        				row_html = "<![CDATA[" + node.data + "]]>";
								break;
		        			case 8: // Node.COMMENT_NODE
		        				node.data = node.data.replace(deleteText,'');
		        				if(node.data == "") continue;
		        				row_html = "<!--" + node.data + "-->";
								break;
		        		}
		        		if(node.nodeType == 1 || node.nodeType == 11 || node.nodeType == 3 || node.nodeType == 4 || node.nodeType == 8) {
		        			if(pre_type == 3 && !html.match(re))
		        				html += row_html + n;
		        			else
		    					html += tab_space + row_html + n;
		        		}
		        		pre_type = node.nodeType;
					}
					return html;
				}

				html = parse(root, "");
        	//} catch(e){
        	//	html = $(root).html();
	    	//}
	    	if( clone_flag ) {
	    		if(root.parentNode)
		    		root.parentNode.removeChild(root);
		    	else
		    		root = null;
	    	}
	    	if ( clone_flag || this.options.rmUnwantedBr ) {
				var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
				html = html.replace(re, '');
			}
			if(mes.length > 0 && self.options.formatMes) {
				var dialog,con_mes = '';
				options = {
					id       : "nc_wysiwyg_mes_" + self.top_id,
					className: "nc_wysiwyg_mes",
					style    : {opacity : '0.8', left: "center", top : "center"},
					pos_base : self.el,
					callback : function(e){
						$A(mes).each(function(v) {
							con_mes += '<li>' + v + '</li>';
						});
						$("nc_wysiwyg_mes_" + self.top_id).innerHTML = '<ul>' + con_mes + '</ul>';
						setTimeout(function() {
							if(dialog) {
								dialog.hide();
							}
						}, self.options.format_time);
					}
				}
				if(!dialog) {
					dialog = new compToggleDialog(options);
				}
				dialog.show(self.el, options);
			}
        	return html;
        },

		htmlEncode : function(str) {
			str = str.replace(/&/ig, "&amp;");
			str = str.replace(/</ig, "&lt;");
			str = str.replace(/>/ig, "&gt;");
			str = str.replace(/\x22/ig, "&quot;");
			str = str.replace(/\xA0/ig, "&nbsp;");
			return str;
		},

        // edit or html or preview
        getMode : function() {
        	var self = this, act_btn = Element.getChildElementByClassName(this.statusbar, "active");
        	if(Element.hasClassName(act_btn, "edit"))
	        	return "edit";
			else if(Element.hasClassName(act_btn, "html"))
				return "html";
			else if(Element.hasClassName(act_btn, "preview"))
				return "preview";
        },

        addFocus : function(now, callback) {
        	var self = this, mode = this.getMode();
        	now = (now == undefined) ? false : now;
        	if(mode == "edit") {
	        	if(now)
	        		self.getWin().focus();
	        	else
	        		setTimeout(function() {
	        			self.getWin().focus();
	        			if(callback)
	        				callback.call(self);
	        		}, 100);
			} else if(mode == "html") {
				if(now)
					self.original.focus();
				else
					setTimeout(function() {
						self.original.focus();
						if(callback)
	        				callback.call(self);
					}, 100);
			}
        },

        /* Dialog関連 */
	    toggleDialog : function(o, options) {
	    	if(o.target || o.srcElement) o = Event.element(o);
	    	if(!options.id) options.id = this.dialog_id;
	    	this.dialogs.toggle(o, options);
	    },

	    showDialog : function(o, options) {
	    	if(o.target) o = $(o.target);
	    	if(!options.id) options.id = this.dialog_id;
	    	this.dialogs.show(o, options);
	    },

	    removeDialog : function(id) {
	    	this.dialogs.hide(id || this.dialog_id);
	    },

	    // 表示中のダイアログ非表示
	    // self以外を削除する
		closeDialogs : function(self) {
			var listmenu = Element.getElementsByClassName(this.panel, "listmenu");
			$A(listmenu).each(function(n){
				if(!self || self != n)
					Element.setStyle(n, {display:'none'});
			});
			if(this.dialogs)
				this.dialogs.removes(self);
		},

	    /* Undo Redo関連 */
	    addUndo : function(init_flag) {
	    	this.nc_undoManager.add(init_flag);
	    },

	    undo : function() {
	    	this.nc_undoManager.undo();
	    	this.checkTargets();
	    },

	    redo : function() {
	    	this.nc_undoManager.redo();
	    	this.checkTargets();
	    },

		/* 登録 */
		regist : function() {
			var t = this, content, params, dialog;
			if(t.getMode() == 'html')
				t.setContent(this.original.value);
			var root = document.createElement('div');
			this.setContent(t.editorDoc.body.innerHTML, root);
			this.chgModeInit("html", root);
			content = this.getParseContent(root);
			root = null;
			return content;
		},

		// nc20用
		getTextArea : function() {
			/* IEで日誌で記事を書いた直後、コメントがかけなくなるため修正 */
			if(browser.isIE) {
				try {
					this.getWin().focus();
				} catch (ex) {}
			}
			return this.regist();
		},

		setTextArea : function(newContent) {
			this.options.content = newContent;
			//this.setContent(newContent);
		},

		// addFocusの別名
		focusEditor : function(now, callback) {
			this.addFocus(now, callback);
		},

		insertImage : function(params) {
			this.closePopup("insertimage");
			this.closePopup("insertsmiley");
			this.addFocus(true);
			if(browser.isIE)
				this.moveToBookmark(this.bookmark);

			var img = this.currentNode ? this.currentNode : this.getSelectNode();
			if (img.tagName.toLowerCase() != 'img') {

				img = this.applyInlineStyle('img', {src : params.f_url}, true);
			}else {
				if (browser.isSafari) {
					this.rangeSelect(img);
				}
				img.src = params.f_url;
			}
			img.alt = ""; // default
			for (var field in params) {
				var value = params[field];
				value = value.strip();
				switch (field) {
				    case "f_alt"    : img.title	 = value; img.alt = value; break;
				    case "f_border" : Element.setStyle(img, {"border":parseInt(value || "0")+"px solid #cccccc"}); break;
				    case "f_align"  :
				    	//align処理は最後で行う
				    	break;
				    case "f_vert"   : img.vspace = parseInt(value || "0"); break;
				    case "f_horiz"  : img.hspace = parseInt(value || "0"); break;
				    case "f_width"   : img.style.width =  (value=='' || value=='0') ? '' : parseInt(value || "0") + "px"; break;
				    case "f_height"  : img.style.height = (value=='' || value=='0') ? '' : parseInt(value || "0") + "px"; break;
				}
			}
			var value = params["f_align"];
			if(value == "") {
	    		if(typeof img.style.cssFloat != "undefined") {
	    			img.style.cssFloat = "none";
	    		} else {
	    			img.style.styleFloat = "none";
	    		}
	    		img.removeAttribute('align');
	    	}
	    	if(value == "left" || value == "right") {
	    		if(typeof img.style.cssFloat != "undefined") {
	    			img.style.cssFloat = value;
	    		} else {
	    			img.style.styleFloat = value;
	    		}
	    	} else if(value != "") {
	    		img.align	 = value;
	    	}
	    	this.rangeSelect(img);
	    	this.checkTargets();
			this.addUndo();
		},
		insertUpload : function(params) {
			this.closePopup("insertupload");
			if (!params) {	// user must have pressed Cancel
				return false;
			}
			var n = this.currentNode ? this.currentNode : this.getSelectNode();
			if (n.tagName.toLowerCase() == 'a') {
				var a = n;
			} else {
				var a = "";
			}

			if (a) {
				this.rangeSelect(a);
				this.editorDoc.execCommand('unlink', false, []);
			}
			this.addFocus(true);
			if(browser.isIE)
				this.moveToBookmark(this.bookmark);
			var split_params = params.split("<br />");
			if(split_params.length > 0 && (browser.isOpera)) {
				var split_params_buf = split_params;
				var j = 0;
				split_params = new Array();
				for (var i = split_params_buf.length-1; i >= 0; --i) {
					split_params[j] = split_params_buf[i];
					j++;
				}
			}
			for (var i = 0; i < split_params.length; ++i) {
				if(i != 0) {
					if (browser.isSafari) {
						this.rangeSelect(a);
						this.collapse(false);
						this.editorDoc.execCommand("InsertLineBreak", false, []);
					} else if(browser.isIE) {
						this.rangeSelect(a);
						this.collapse(false);
						var rng = this.getRange();
	                    rng.pasteHTML('<br />');
	                    rng.collapse(false);
	                    rng.select();
					} else if(browser.isOpera ) {
						this.editorDoc.execCommand('inserthtml', false, '<br>');
					} else
						this.applyInlineStyle("br", null, true);
				}
				a = this.applyInlineStyle(split_params[i], null, true);
			}
			this.rangeSelect(a);
			this.checkTargets();
			this.addUndo();
		},

		_createInputHidden : function(name, value) {
			var input_el = document.createElement('input');
			input_el.setAttribute('name', name);
			input_el.setAttribute('type', 'hidden');
			input_el.setAttribute('value', value);
			return input_el;
		},

		showDialogBox : function(cmdID, action_name, el, right_flag , offset, modal_flag) {
			//IMG取得
			var EX1 = Position.positionedOffset(el)[0];
			var EY1 = Position.positionedOffset(el)[1];
			var EY2 = el.offsetHeight + EY1;
			var EX2 = el.offsetWidth + EX1;
			offset = (offset == undefined || offset == null) ? [0,0] : offset;
			modal_flag = (modal_flag == undefined || modal_flag == null) ? true : modal_flag;
			if(right_flag) {
				var x = EX2 + offset[0];
				var y = EY1 + offset[1];
			} else {
				var x = EX1 + offset[0];
				var y = EY2 + offset[1];
			}

			//Safariの場合、srcからiframeを表示しなければ、リファラが取得できないため
			var src = _nc_base_url + _nc_index_file_name + "?action=" + action_name + "&prefix_id_name="+ "dialog_"+cmdID + "&parent_id_name=" +
						this.top_id + this.popupPrefix + "&top_id_name=" + this.id + "&cmd_name=" + cmdID +
						"&_header=1&_noscript=1";
			var queryParams = commonCls.getParams(this.id);
			if(queryParams) {
				var page_id = queryParams["page_id"];
				var block_id = queryParams["block_id"];
				var module_id = queryParams["module_id"];
				if(page_id) src += "&page_id=" + page_id;
				if(block_id) src += "&block_id=" + block_id;
				if(module_id) src += "&module_id=" + module_id;
			}
			this._showPopup(cmdID, x, y, modal_flag, src);
		},
		_showPopup : function(cmdID, x, y, modal_flag, src) {
			var t = this;
			this.popup[this.popupPrefix + this.id + cmdID] = new compPopup(this.id, this.popupPrefix + this.top_id + cmdID);
			if(modal_flag) {
				this.popup[this.popupPrefix + this.id + cmdID].observer = function(){this.closePopup(cmdID);}.bind(this);
				this.popup[this.popupPrefix + this.id + cmdID].observing = true;
				this.popup[this.popupPrefix + this.id + cmdID].modal = true;
			} else {
				this.popup[this.popupPrefix + this.id + cmdID].observing = false;
				this.popup[this.popupPrefix + this.id + cmdID].modal = false;
			}
			//if(typeof compTextareaLang != "undefined" && typeof compTextareaLang.icons[cmdID] != "undefined") {
			//	this.popup[this.popupPrefix + this.id + cmdID].setTitle(compTextareaLang.icons[cmdID]);
			//}
			this.popup[this.popupPrefix + this.id + cmdID].setPosition(Array(x, y));
			if(this.popup[this.popupPrefix + this.id + cmdID].popupElement) {
				this.popup[this.popupPrefix + this.id + cmdID].popupElement.src = "";
			}
			this.popup[this.popupPrefix + this.id + cmdID].showSrcPopup(src);
		},

		closePopup : function(cmdID) {
			if(this.popup[this.popupPrefix + this.id + cmdID]) {
				this.popup[this.popupPrefix + this.id + cmdID].closePopup();
			}
		},

        /* Selection関連 */
        getSelection : function() {
            return (this.getWin().getSelection && !(browser.isIE && browser.version >= 9)) ? this.getWin().getSelection() : this.editorDoc.selection;
        },

        getRangeCount : function() {
        	var sel = this.getSelection();
        	return sel.rangeCount;
        },

        getRanges : function() {
        	var t = this, ranges=[], r_cnt;
        	r_cnt = t.getRangeCount();
        	if(r_cnt == undefined)
        		ranges.push(t.getRange());
        	else if(r_cnt > 0) {
        		for (var i = 0; i < r_cnt; i++)
        			ranges.push(t.getRange(i));
        	}
        	return ranges;
        },

        getRange : function(r_num) {
            var range, sel = this.getSelection();
            r_num = r_num || 0;
            if (!sel) return null;
            try {
				range = sel.rangeCount > 0 ? sel.getRangeAt(r_num) : (sel.createRange ? sel.createRange() : this.getWin().document.createRange());
			} catch (ex) {}
			if (!range) range = (browser.isIE) ? this.editorDoc.body.createTextRange() : this.editorDoc.createRange();
			return range;
        },

        createRange : function() {
			var sel = this.getSelection();
			//return ((browser.isIE && browser.version < 9)) ? sel.createRange() : this.editorDoc.createRange();
			return ((browser.isIE)) ? sel.createRange() : this.editorDoc.createRange();
		},

		setRange : function(range) {
			var sel = this.getSelection();
			if (sel) {
				if(browser.isIE)
					range.select();
				else {
					sel.removeAllRanges();
					sel.addRange(range);
				}
			}
		},

		collapse : function(b) {
			var n, range = this.getRange();

			if (range.item) {
				n = range.item(0);
				range = this.editorDoc.body.createTextRange();
				range.moveToElementText(n);
			}

			range.collapse(!!b);
			this.setRange(range);
		},

		getSelectNode : function() {
			var r = this.getRange(),s, e;

			if (!browser.isIE) {
				s = this.getSelection();
				if (!r) return this.editorDoc.body;

				e = r.commonAncestorContainer;
				if (!r.collapsed) {
					if (browser.isSafari && s.anchorNode && s.anchorNode.nodeType == 1)
						return s.anchorNode.childNodes[s.anchorOffset] || this.editorDoc.body;

					if (r.startContainer == r.endContainer) {
						if (r.startOffset - r.endOffset < 2) {
							if (r.startContainer.hasChildNodes())
								e = r.startContainer.childNodes[r.startOffset];
						}
					}
				}
				if( e.nodeType == 1 )
					return e || this.editorDoc.body;
				else
					return e.parentNode || this.editorDoc.body;
			}

			return r.item ? r.item(0) : r.parentElement();
		},

		getSelectBlockNode : function() {
			var blockTags = " blockquote center div dl form h1 h2 h3 h4 h5 h6 hr ol p pre table ul ";
        	var n = this.currentNode ? this.currentNode : this.getSelectNode();
			do {
      			if(blockTags.indexOf(" " + n.tagName.toLowerCase() + " ") != -1) {
      				break;
      			}
      			if ( n.nodeName.toLowerCase() == 'body' ||  n.nodeName.toLowerCase() == 'html') break;
      		} while ( n = n.parentNode );

			return n;
		},

		rangeSelect : function(n, c) {
			var t = this, r = t.getRange(), s = t.getSelection(), b, fn, ln, d = t.getWin().document;

			function find(n, start) {
				var walker, o;

				if (n) {
					walker = d.createTreeWalker(n, NodeFilter.SHOW_TEXT, null, false);

					// Find first/last non empty text node
					while (n = walker.nextNode()) {
						o = n;
						if (n.nodeValue.replace(/^\s*|\s*$/g, '').length != 0) {
							if (start)
								return n;
							else
								o = n;
						}
					}
				}

				return o;
			};

			if (browser.isIE) {
				try {
					b = d.body;

					if (/^(IMG|TABLE)$/.test(n.nodeName)) {
						r = b.createControlRange();
						r.addElement(n);
					} else {
						r = b.createTextRange();
						r.moveToElementText(n);
					}

					r.select();
				} catch (ex) {
					// Throws illigal agrument in IE some times
				}
			} else {
				if (c) {
					fn = find(n, 1);
					ln = find(n, 0);
					if(!fn || !ln)
						brs = n.getElementsByTagName("br");
					if(!fn && brs[0]) {
						fn = brs[0];
					}
					if(!ln && brs[0]) {
						fn = brs[brs.length - 1];
					}

					if (fn && ln) {
						r = d.createRange();

						if (fn.nodeName == 'BR')
							r.setStartBefore(fn);
						else
							r.setStart(fn, 0);

						if (ln.nodeName == 'BR')
							r.setEndBefore(ln);
						else
							r.setEnd(ln, ln.nodeValue.length);
					} else
						r.selectNode(n);
				} else
					r.selectNode(n);

				t.setRange(r);
			}

			return n;
		},

		/**
		 * @return hash
		 *			sel_name   : string "table" or "row" or "col" or "cell" or false
		 *                        テーブル内を選択していないならば、falseを返す
		 *          table_el   : object table element
		 *          sel_els    : array object table element OR tr element OR td element
		 *                      elementをvalueにもつ配列で返す。
		 *                      複数選択されている場合を考慮するため。
		 *                      sel_nameが"cell"の場合、cell_elsと同じ値がセットされる
		 *                      sel_nameが"cell"の場合、cell_elsと同じ値がセットされる
		 *			cell_els   : array 選択されているtd elementをすべて配列で返す
		 *          ranges     : array 選択range
		 */
		getSelectTablePos : function() {
			var t = this, sel_el, ranges = t.getRanges(), table, rows = [], cells = [], row_cnt, col_cnt, commonCon, td;
			var buf_rows = [], buf_cols = [], sel_rows = [], sel_cols = [];
			var ret = {
				sel_name : false,
				table_el : [],
				sel_els  : [],
				cell_els : [],
				ranges   : ranges
			};
			sel_el = t.currentNode || t.getSelectNode();
			switch (sel_el.nodeName.toLowerCase()) {
				case "table":
					ret.sel_name = "table";
					ret.table_el = sel_el;
					ret.sel_els.push(sel_el);
					ret.cell_els = _getTdByTable(sel_el);
					break;
				case "tbody":
				case "thead":
				case "tfoot":
					// tbodyは、tableとする
					ret.sel_name = "table";
					table = _getParentTable(sel_el);
					ret.table_el = table;
					ret.sel_els.push(table);
					ret.cell_els = _getTdByTable(table);
					break;
				case "tr":
					ret.sel_name = "row";
					table = _getParentTable(sel_el);
					ret.table_el = table;
					ret.sel_els.push(sel_el);
					ret.cell_els = _getTdByTr(sel_el);
					break;
				case "th":
				case "td":
					if(ranges.length == 1) {
						ret.sel_name = "cell";
						table = _getParentTable(sel_el);
						ret.table_el = table;
						ret.sel_els.push(sel_el);
						ret.cell_els.push(sel_el);
					} else {
						table = _getParentTable(sel_el);
						rows = _getTr(table);
						cells = _getTdByTr(rows[0]); 	// 1行目のtd
						row_cnt = rows.length;
						col_cnt = 0;
						$A(cells).each(function(cel){
							col_cnt += cel.colSpan;
						});
						for (var i = 0; i < row_cnt; i++)
							buf_rows[i] = col_cnt;
						for (var i = 0; i < col_cnt; i++)
							buf_cols[i] = row_cnt
						ret.table_el = table;
						for (var i = 0; i < ranges.length; i++) {
							commonCon = ranges[i].commonAncestorContainer;
							if(commonCon && commonCon.cells)
								td = commonCon.cells[ranges[i].startOffset];
							if(td) {
								for(var j = td.parentNode.rowIndex; j < td.parentNode.rowIndex + td.rowSpan; j++) {
									buf_rows[j] -= td.colSpan;
									if(buf_rows[j] == 0)
										sel_rows.push(td.parentNode);
								}
								for(var j = td.cellIndex; j < td.cellIndex + td.colSpan; j++) {
									buf_cols[j] -= td.rowSpan;
									if(buf_cols[j] == 0)
										sel_cols.push(td);
								}

								ret.cell_els.push(td);
								ret.sel_els.push(td);
							}
						}
						var row_eq = 0, sel_all = true, sel_cell = false;
						for (var i = 0; i < row_cnt; i++) {
							if(buf_rows[i] != 0) {
								sel_all = false;
								if(row_eq != 0 && buf_rows[i] != row_eq) {
									sel_cell = true;
									break;
								}
								row_eq = buf_rows[i];
							}
						}

						var col_eq = 0;
						for (var i = 0; i < col_cnt; i++) {
							if(buf_cols[i] != 0) {
								sel_all = false;
								if(col_eq != 0 && buf_cols[i] != col_eq) {
									sel_cell = true;
									break;
								}
								col_eq = buf_cols[i];
							}
						}
						if(sel_all) {
							// すべて選択
							ret.sel_name = "table";
							ret.sel_els = table;
						} else if(sel_cell) {
							// セルが選択
							ret.sel_name = "cell";
						} else if(sel_rows.length > 0) {
							// 行が選択
							ret.sel_name = "row";
							ret.sel_els = sel_rows;
						} else if(sel_cols.length > 0) {
							// 列が選択
							ret.sel_name = "col";
							ret.sel_els = sel_cols;
						}
					}
					break;
				default:
					var chk_el = sel_el;
					do {
						if(sel_el.nodeName.toLowerCase() == "td" || sel_el.nodeName.toLowerCase() == "th" || sel_el.nodeName.toLowerCase() == "table") {
							break;
						}
					} while ( sel_el = sel_el.parentNode );
					if(sel_el) {
						ret.sel_name = "cell";
						ret.table_el = _getParentTable(sel_el);
						ret.sel_els.push(sel_el);
						ret.cell_els.push(sel_el);
					}
			}
			return ret;

			function _getParentTable(el) {
				do {
					if(el.nodeName.toLowerCase() == "table") {
						break;
					}
				} while ( el = el.parentNode );

				return el;
			}

			function _getTr(table) {
				var ret = [], child;
				for (var i = 0; i < table.childNodes.length; i++) {
					child = table.childNodes[i];
					if(child.nodeName.toLowerCase() == "tbody" || child.nodeName.toLowerCase() == "thead" ||
						 child.nodeName.toLowerCase() == "tfoot") {
						for (var j = 0; j < child.childNodes.length; j++) {
							if(child.childNodes[j].nodeName.toLowerCase() == "tr")
								ret.push(child.childNodes[j]);
						}
					} else if(child.nodeName.toLowerCase() == "tr") {
						ret.push(child);
					}
				}
				return ret;
			}

			function _getTdByTr(tr, ret) {
				var ret = ret || [], child;
				for (var i = 0; i < tr.childNodes.length; i++) {
					child = tr.childNodes[i];
					if(child.nodeName.toLowerCase() == "td" || child.nodeName.toLowerCase() == "th") {
						ret.push(child);
					}
				}
				return ret;
			}

			function _getTdByTable(table) {
				var ret = [], tr = _getTr(table);
				$A(tr).each(function(v) {
					ret = _getTdByTr(v, ret);
				});
				return ret;
			}
		},
		/**
		  * blockquoteタグ内部でリターンキーをクリックした場合、blockquote外部へ移動する
		  * 掲示板の返信などの引用文などでblockquoteタグを使用
		  * 基本、nc2系のまま実装
		  */
		detachBlockQuote : function(bq) {
			var t = this, r = t.getRange(), s = t.getSelection();

			// check
			do {
				if(bq.nodeName.toLowerCase() == "blockquote" || bq.nodeName.toLowerCase() == "body") {
					break;
				}
			} while ( bq = bq.parentNode );

			if(bq.nodeName.toLowerCase() != 'blockquote')
				return false;

			// blockquote外部へ移動
			if (browser.isIE) {
				var id_name = "nc_wysiwyg_split";
				r.pasteHTML('<span id="' + id_name + '"></span>');
				var id_name_el = t.editorDoc.getElementById('nc_wysiwyg_split');
				var clone_el = id_name_el.cloneNode(false);
				var new_text_nd = _cloneTextElement(id_name_el, clone_el, bq);
				if(!bq.nextSibling) {
					bq.parentNode.appendChild(new_text_nd);
				} else {
					bq.parentNode.insertBefore(new_text_nd, bq.nextSibling);
				}
		        var br_el = this.editorDoc.createElement("BR");
		        bq.parentNode.insertBefore(br_el, new_text_nd);
		        var br_el = this.editorDoc.createElement("BR");
		        bq.parentNode.insertBefore(br_el, new_text_nd);
		        if(browser.isIE && browser.version >= 9) {
			        setTimeout(function() {
			        	r = t.getRange();
			        	r.move("character", 2);
				        r.select();
					}, 100);
		        } else {
		        	r = t.getRange();
		        	r.move("character", 2);
		        	r.select();
		        }
		        id_name_el.parentNode.removeChild(id_name_el);
			} else {
				var text_nd, stNode = s.anchorNode;
				var cpRange = r.cloneRange();

				if(stNode.nodeType == 3) {
					//text Node:テキスト分割
					text_nd = stNode.splitText(s.anchorOffset);
				} else {
					stNode = this.editorDoc.createTextNode("");
					cpRange.insertNode(stNode);
					text_nd = this.editorDoc.createTextNode("");
					cpRange.insertNode(text_nd);
				}

				if(bq) {
					var new_text_nd = _cloneTextElement(stNode, text_nd, bq);
					if(!bq.nextSibling) {
						bq.parentNode.appendChild(new_text_nd)
					} else {
						bq.parentNode.insertBefore(new_text_nd, bq.nextSibling)
					}
					//分割したblockquote_el次にbr挿入
					var br_el = this.editorDoc.createElement("BR");
					bq.parentNode.insertBefore(br_el, new_text_nd);
					r.setStartBefore(br_el);
					r.setEndBefore(br_el);
					this.setRange(r);
				}
			}
			var bq_list = $A(bq.parentNode.getElementsByTagName("blockquote"));
			bq_list.each(function(el){
				if(el.innerHTML == '' || el.innerHTML.toLowerCase() == '<span id=nc_wysiwyg_split></span>') {
					el.parentNode.removeChild(el);
				}
			});

			return true;
			function _cloneTextElement(node_el, text_el, bq) {
				while(node_el != bq)
				{
					var parent_el = node_el.parentNode;
					if(!parent_el) {
						return false;
					}
					var clone_el = parent_el.cloneNode(false);
					clone_el.appendChild(text_el);
					var next_el = node_el.nextSibling;
					while(next_el != null) {
						parent_el.removeChild(next_el);
						clone_el.appendChild(next_el);
						next_el = node_el.nextSibling;
					}
					node_el = parent_el;
					text_el = clone_el;
				}
				return text_el;
			};
		},


		// 選択範囲のブックマーク取得
		getBookmark : function(si) {
			var t = this, r = t.getRange(), tr, sx, sy, w = this.getWin(), e, sp, bp, le, c = -0xFFFFFF, s
			var sc_pos = this.getScrollDoc(), ro = t.editorDoc.body, wb = 0, wa = 0, nv;
			sx = sc_pos['left'];
			sy = sc_pos['top'];

			// Simple bookmark fast but not as persistent
			if (si)
				return {rng : r, scrollX : sx, scrollY : sy};

			// Handle IE
			if (browser.isIE) {
				// Control selection
				if (r.item) {
					e = r.item(0);
					var nodes = this.editorDoc.getElementsByTagName(e.nodeName.toLowerCase());
					$A(nodes).each(function(n, i) {
						if (e == n) {
							sp = i;
							return false;
						}
					});

					return {
						tag : e.nodeName,
						index : sp,
						scrollX : sx,
						scrollY : sy
					};
				}

				// Text selection
				tr = t.editorDoc.body.createTextRange();
				tr.moveToElementText(ro);
				tr.collapse(true);
				bp = Math.abs(tr.move('character', c));

				tr = r.duplicate();
				tr.collapse(true);
				sp = Math.abs(tr.move('character', c));

				tr = r.duplicate();
				tr.collapse(false);
				le = Math.abs(tr.move('character', c)) - sp;
				return {
					start : sp - bp,
					length : le,
					scrollX : sx,
					scrollY : sy
				};
			}

			// Handle W3C
			e = t.getSelectNode();
			s = t.getSelection();

			if (!s)
				return null;

			// Image selection
			if (e && e.nodeName == 'IMG') {
				return {
					scrollX : sx,
					scrollY : sy
				};
			}

			// Text selection

			function getPos(r, sn, en) {
				var w = t.editorDoc.createTreeWalker(r, NodeFilter.SHOW_TEXT, null, false), n, p = 0, d = {};

				while ((n = w.nextNode()) != null) {
					if (n == sn)
						d.start = p;

					if (n == en) {
						d.end = p;
						return d;
					}

					p += (n.nodeValue || '').replace(/[\n\r]+/g, '').length;
				}

				return null;
			};

			// Caret or selection
			if (s.anchorNode && s.anchorNode == s.focusNode && s.anchorOffset == s.focusOffset) {
				if(s.focusNode.nodeName.toLowerCase() == "body")
					e = getPos(ro, s.anchorNode, s.anchorNode);
				else
					e = getPos(ro, s.anchorNode, s.focusNode);

				if (!e)
					return {scrollX : sx, scrollY : sy};

				// Count whitespace before
				(s.anchorNode.nodeValue || '').replace(/[\n\r]+/g, '').replace(/^\s+/, function(a) {wb = a.length;});

				return {
					start : Math.max(e.start + s.anchorOffset - wb, 0),
					end : Math.max(e.end + s.focusOffset - wb, 0),
					scrollX : sx,
					scrollY : sy,
					beg : s.anchorOffset - wb == 0
				};
			} else {
				if(r.endContainer.nodeName.toLowerCase() == "body")
					e = getPos(ro, r.startContainer, r.startContainer);
				else
					e = getPos(ro, r.startContainer, r.endContainer);
				// Count whitespace before start and end container
				//(r.startContainer.nodeValue || '').replace(/^\s+/, function(a) {wb = a.length;});
				//(r.endContainer.nodeValue || '').replace(/^\s+/, function(a) {wa = a.length;});

				if (!e)
					return {scrollX : sx, scrollY : sy};

				return {
					start : Math.max(e.start + r.startOffset - wb, 0),
					end : Math.max(e.end + r.endOffset - wa, 0),
					scrollX : sx,
					scrollY : sy,
					beg : r.startOffset - wb == 0
				};
			}
		},

		moveToBookmark : function(b) {
			var t = this, r = t.getRange(), s = t.getSelection(), ro = t.editorDoc.body, sd, nvl, nv;

			function getPos(r, sp, ep) {
				var w = t.editorDoc.createTreeWalker(r, NodeFilter.SHOW_TEXT, null, false), n, p = 0, d = {}, o, v, wa, wb;

				while ((n = w.nextNode()) != null) {
					wa = wb = 0;

					nv = n.nodeValue || '';
					//nv.replace(/^\s+[^\s]/, function(a) {wb = a.length - 1;});
					//nv.replace(/[^\s]\s+$/, function(a) {wa = a.length - 1;});

					nvl = nv.replace(/[\n\r]+/g, '').length;
					p += nvl;

					if (p >= sp && !d.startNode) {
						o = sp - (p - nvl);

						// Fix for odd quirk in FF
						if (b.beg && o >= nvl)
							continue;

						d.startNode = n;
						d.startOffset = o + wb;
					}

					if (p >= ep) {
						d.endNode = n;
						d.endOffset = ep - (p - nvl) + wb;
						return d;
					}
				}

				return null;
			};

			if (!b)
				return false;

			t.getWin().scrollTo(b.scrollX, b.scrollY);

			t.bookmark = b;

			// Handle explorer
			if (browser.isIE) {
				// Handle simple
				if (r = b.rng) {
					try {
						r.select();
					} catch (ex) {
						// Ignore
					}

					return true;
				}

				t.addFocus(true);

				// Handle control bookmark
				if (b.tag) {
					r = ro.createControlRange();

					var nodes = this.editorDoc.getElementsByTagName(b.tag);
					$A(nodes).each(function(n, i) {
						if (i == b.index)
							r.addElement(n);
					});
				} else {
					// Try/catch needed since this operation breaks when TinyMCE is placed in hidden divs/tabs
					try {
						// Incorrect bookmark
						if (b.start < 0)
							return true;

						r = s.createRange();
						r.moveToElementText(ro);
						r.collapse(true);
						r.moveStart('character', b.start);
						r.moveEnd('character', b.length);
					} catch (ex2) {
						return true;
					}
				}

				try {
					r.select();
				} catch (ex) {
					// Needed for some odd IE bug #1843306
				}

				return true;
			}

			// Handle W3C
			if (!s)
				return false;

			// Handle simple
			if (b.rng) {
				s.removeAllRanges();
				s.addRange(b.rng);
			} else {
				if (typeof(b.start) != 'undefined' && typeof(b.end) != 'undefined') {
					try {
						sd = getPos(ro, b.start, b.end);

						if (sd) {
							r = t.editorDoc.createRange();
							r.setStart(sd.startNode, sd.startOffset);
							r.setEnd(sd.endNode, sd.endOffset);
							s.removeAllRanges();
							s.addRange(r);
						}

						if (!browser.isOpera)
							t.addFocus();
					} catch (ex) {
						// Ignore
					}
				}
			}
		},

		/* 共通 */

		getParent : function(node, f, r_node) {
			return this.getParents(node, f, r_node, false);
		},

		getScrollDoc : function(w, d) {
			var t = this, w = (w == undefined) ? this.getWin() : w;
			var d = (d == undefined) ? t.editorDoc : d;

			var sx = d.documentElement.scrollLeft || d.body.scrollLeft || w.pageXOffset || 0;
			var sy = d.documentElement.scrollTop || d.body.scrollTop || w.pageYOffset || 0;
			return {left : sx, top : sy};
		},

		/**
		 * Nodeから親の要素を求める
		 * @param n        : node object
		 * @param f        : function
		 * @param r        : node    object 親要素のルート
		 * @param c        : boolean 複数のelementを返すかどうか(trueの場合、返り値　array )。 default true
		 */
		getParents : function(n, f, r, c) {
			var t = this, na, o = [];

			c = c === undefined;

			r = r || this.editorDoc.body;

			while (n) {
				if (n == r || !n.nodeType || n.nodeType === 9)
					break;

				if (!f || f(n)) {
					if (c)
						o.push(n);
					else
						return n;
				}

				n = n.parentNode;
			}

			return c ? o : null;
		},

		run : function(e, f, s) {

			var t = this, o;

			if (!e)
				return false;

			s = s || this;
			if (!e.nodeType && (e.length || e.length === 0)) {
				o = [];

				$.each(e, function(i, e) {
					if (e) {
						if (typeof(e) == 'string')
							e = t.editorDoc.getElementById(e);

						o.push(f.call(s, e, i));
					}
				});

				return o;
			}

			return f.call(s, e);
		},

		applyInlineStyle : function(na, at, collapsed) {
			var t = this, bm, lo = {}, r_el, c;
			var buf_at = at;
			at = at || {};
			collapsed = collapsed || false;

			//na = na.toUpperCase();

			//if (op && op.check_classes && at['class'])
			//	op.check_classes.push(at['class']);

			function removeEmpty() {
				var nodes = $A(t.editorDoc.getElementsByTagName(na));
				if(nodes.length > 0)
					nodes.reverse();
				$A(nodes).each(function(n) {
					var c = 0;

					// Check if there is any attributes
					var n_attrs = t.attrs(n);
					for (var k in n_attrs ) {
						var v = n_attrs[k];
						if (k.substring(0, 1) != '_' && v != '' && v != null) {
							c++;
						}
					}

					// No attributes then remove the element and keep the children
					if (c == 0) {
						for (i = n.childNodes.length - 1; i >= 0; i--)
							t.insertAfter(n, n.childNodes[i]);
						n.parentNode.removeChild(n);
					}
				});
			};

			function replaceFonts() {
				var bm, c_el, r_el;
				var tags = new Array('span', 'font', 'img');
				var tags_length = tags.length;
				for (var k = tags_length; k > 0; ) {
					var target_ar = t.editorDoc.getElementsByTagName(tags[--k]);
					var target_ar_length = target_ar.length;
					for (var i = target_ar_length; i > 0; i--) {
						var el = target_ar[i - 1];
						if (el.style.fontFamily == 'nc_wysiwygfont' || (el.face && el.face == 'nc_wysiwygfont') || (el.src && el.src.match(/nc_wysiwygurl$/))) {
							if (!bm)
								bm = t.getBookmark();

							if(collapsed == false)
								at._nc_wysiwyg_ins = '1';
							if(na.match(/^</)) {
								var spn = t.editorDoc.createElement("span");
								spn.innerHTML = na;

								c_el = spn.childNodes[0];
							} else
								c_el = t.editorDoc.createElement(na);
							if (!r_el)
								r_el = c_el;
							t.replace(t.attrs(c_el, at), el, 1);
							if(spn)
								spn = null;
						}
					}
				}

				// 重複するelementの削除
				if(collapsed == false) {
					var target_ar = t.editorDoc.getElementsByTagName(na);
					var target_ar_length = target_ar.length;
					for (var i = 0; i < target_ar_length; ++i) {
						var value = target_ar[i].getAttribute("_nc_wysiwyg_ins");
						if(value) {
							var n = target_ar[i];
							function removeStyle(n) {
								if (n.nodeType == 1 && at.style) {

									for (var k in at.style ) {
										//var v = at.style[k];
										if(browser.isIE)
											n.style.removeAttribute(k);
										else
											n.style[k] = '';
									}

									// Remove spans with the same class or marked classes
									//if (at['class'] && n.className && op) {
									//	each(op.check_classes, function(c) {
									//		if (dom.hasClass(n, c))
									//			dom.removeClass(n, c);
									//	});
									//}
								}
							};

							// Remove specified style information from child elements
							var nodes = (n || t.editorDoc.body).getElementsByTagName(na);
							$A(nodes).each(function(n, i) {
								removeStyle(n);
							});

							// Remove the specified style information on parent if current node is only child (IE)
							if (n.parentNode && n.parentNode.nodeType == 1 && n.parentNode.childNodes.length == 1)
								removeStyle(n.parentNode);

							// Remove the child elements style info if a parent already has it

							t.getParent(n.parentNode, function(pn) {
								if (pn.nodeType == 1) {
									if (at.style) {
										for (var k in at.style ) {
											var v = at.style[k];
											var sv;

											if (!lo[k] && (sv = Element.getStyle(pn, k))) {
												if (sv === v) {
													if(browser.isIE)
														n.style.removeAttribute(k);
													else
														n.style[k] = '';
												}
												lo[k] = 1;
											}

										}
									}

									// Remove spans with the same class or marked classes
									//if (at['class'] && pn.className && op) {
									//	each(op.check_classes, function(c) {
									//		if (dom.hasClass(pn, c))
									//			dom.removeClass(n, c);
									//	});
									//}
								}

								return false;
							});

							n.removeAttribute('_nc_wysiwyg_ins');
						}
					}

					if(buf_at != undefined && buf_at != null)
						removeEmpty();
				}
				t.moveToBookmark(bm);
				return r_el;
			};

			// Create inline elements
			t.addFocus();
			if(collapsed)
				t.editorDoc.execCommand('insertImage', false, 'nc_wysiwygurl');
			else
				t.editorDoc.execCommand('fontName', false, 'nc_wysiwygfont');
			r_el = replaceFonts();
			if(t._keyhandler) {
				Event.stopObserving(this.editorDoc,"keyup",t._keyhandler);
				Event.stopObserving(this.editorDoc,"keypress",t._keyhandler);
				Event.stopObserving(this.editorDoc,"keydown",t._keyhandler);
			}
			// nodechange
			if(r_el) {
				t.checkTargets(r_el);
				t._pendingStyles = 0;

			} else {
				// mozillaでなにも選択せずにサイズを指定後、文字をかくと、サイズ指定にならず、fontタグが表示されてしまうため
				// Start collecting styles
				$H(t._pendingStyles || {}).merge($H(at.style));

				t._keyhandler = function(e) {
					// Use pending styles
					if (t._pendingStyles) {
						at.style = t._pendingStyles;
						delete t._pendingStyles;
					}

					if (replaceFonts()) {
						if (e.type == 'keypress')
							Event.stopObserving(this.editorDoc,"keypress",t._keyhandler);
						if (e.type == 'keydown')
							Event.stopObserving(this.editorDoc,"keydown",t._keyhandler);
					}

					if (e.type == 'keyup') {
						Event.stopObserving(t.editorDoc,"keyup",t._keyhandler);
						if((browser.isFirefox || (browser.isIE && browser.version >= 9)))
							t.nc_undoManager.index = t.nc_undoManager.index - 2;
						else
							t.nc_undoManager.index = t.nc_undoManager.index - 1;
						t.addUndo();
					}
					t.checkTargets();
				};
				Event.observe(t.editorDoc,"keydown", t._keyhandler);
				Event.observe(t.editorDoc,"keypress", t._keyhandler);
				Event.observe(t.editorDoc,"keyup", t._keyhandler);
			}
			return r_el;
		},

		/**
		 * Nodeを別Nodeにreplaceする
		 * @param node     : new node object
		 * @param o        : replace node object or array replace node
		 * @param k        : boolean      子供をコピーするかどうか
		 */
		replace : function(node, o, k) {
			var self = this;
			var ret, n = node;

			if (typeof(o) == 'array')
				node = node.cloneNode(true);

			ret = this.run(o, function(o) {
				if (k) {
					$A(o.childNodes).each(function(c) {
						node.appendChild(c.cloneNode(true));
					});
				}

				// Fix IE psuedo leak for elements since replacing elements if fairly common
				// Will break parentNode for some unknown reason
				if (o.nodeType === 1) {
					o.parentNode.insertBefore(node, o);
					o.parentNode.removeChild(o);
					return node;
				}

				return o.parentNode.replaceChild(node, o);
			});
			return ret;
		},

		//
		// attrの複数版
		//
		attrs : function(el, at) {
			var attrs, len, ret = true, i;

			if(at === undefined) {
				ret = {};
				attrs = el.attributes;
				for (var i = 0; i < attrs.length; ++i) {
					a = attrs.item(i);
					name = this.getNodeName(a);
					value = this.getNodeValue(el, a);
					ret[name] = value;
				}

				return ret;
			} else
				len = at.length;

			var len = at.length, ret = true;
			if (typeof(at) == 'string')
				return el.getAttribute(at);

			if(len === undefined) {
				// hash
				for (var key in at ) {
					if (typeof(at[key]) == 'string') {
						el.setAttribute(key,at[key],0);
					} else {
						Element.setStyle(el, at[key]);
					}
				}
				return el;
			} else {
				// array
				ret = [];
				for ( ; i < len; )
					ret.push(el.getAttribute(i));
			}

			return ret;
		},
		getNodeName: function(a) {
        	return a.nodeName.toLowerCase();
        },
        getNodeValue: function(node, a) {
        	var name, value ="";
        	try {
	        	name = this.getNodeName(a);
	        	if (name != "style") {
					// ブラウザによっては、height等の属性は、本来入力していないものを自動的に指定されてしまう可能性があるため、
					// a.nodeValueを用いる
					if (typeof node[a.nodeName] != "undefined" && name != "height"  && name != "width"  && name != "href" && name != "src" && !/^on/.test(name)) {
						value = node[a.nodeName];
					} else {
						if((name == "href" || name == "src") &&
							 browser.isIE && parseInt(browser.version) < 7) {
							// IE6では、URIEncodeしたURLを指定すると文字化けしてしまうため
							value = node.getAttribute(name);
						} else
							value = a.nodeValue;
					}
				} else {
					value = node.style.cssText;
				}
			} catch(e){}
			return value;
        },

		/**
		 * リストメニュー作成
		 * @param node          element  追加する親element
		 * @param list          hash     リストのキーとvalueをhash配列で指定。valueはhtmlでも可
		 * @param className     string   リストメニューとプルダウンした箇所に追加するclassName
		 *                           CSS側でa.className di.classNameとして定義を分けることが可
		 * @param callback      function callback function　メニューを選択時
		 * @param args          array    callback function args デフォルト　event,リストのkey,value
		 *                           argsをセットした場合、デフォルト値にpushされる
		 * @return object list element
		 */
		appendCommonList : function (node, list, className, callback, args) {
			var t = this, name=null, a, li;
			var umenu = document.createElement('ul');

			var listmenu = document.createElement('div');
			Element.addClassName(listmenu,"listmenu");
			Element.setStyle(listmenu, {zIndex : commonCls.max_zIndex++});
			listmenu.appendChild(umenu);

			var a = document.createElement('a');
			a.setAttribute('href','javascript:;',0);

			if(className) Element.addClassName(listmenu,className);
			for ( var k in list ) {
				if (name == null) name = list[k];
				else if(list[k] != "") {
					li = document.createElement('li');
					var sub_a = document.createElement('a');
					Element.addClassName(sub_a,"listmenu_sub");
					sub_a.setAttribute('href','javascript:;',0);
					Event.observe(sub_a,"click", function(e) {
						var sub_a = Event.element(e);
						var sub_a = Element.getParentElementByClassName(sub_a,"listmenu_sub");
						var d = Element.getParentElementByClassName(sub_a,"listmenu");
						var set_args = [e, sub_a.getAttribute("name"), sub_a.innerHTML];
						for (var j = 0; j < args.length; ++j) {
							set_args.push(args[j]);
						}
						var buf_el = Element.getChildElementByClassName(d.previousSibling, "listcontent");
						buf_el.innerHTML = sub_a.innerHTML;
						if(callback)
							callback.apply(a, set_args);
						Element.setStyle(d, {display:'none'});
						d.previousSibling.focus();
		            }, false, this.id);
					li.appendChild(sub_a);
					sub_a.setAttribute('title',k,0);
					sub_a.setAttribute('name',k,0);
					sub_a.innerHTML = list[k];
					umenu.appendChild(li);
				}
			}

			Element.addClassName(a,"listbox");
			var span = document.createElement('span');
			Element.addClassName(span,"listbtn");
			a.appendChild(span);
			span = document.createElement('span');
			Element.addClassName(span,"listcontent");
			span.setAttribute('title',name,0);
			span.innerHTML = name;
			a.appendChild(span);
			Event.observe(a,"click", function(e) {
				// 表示非表示切替
	           	var ret = true;
	           	var self = Event.element(e);
	           	if(Element.hasClassName(self, "listbox") != "a") {
			    	var self = Element.getParentElementByClassName(self,"listbox");
			    }

	           	var listcontent = Element.getChildElementByClassName(self, "listcontent");
	           	var html = listcontent.innerHTML;
	           	listcontent.innerHTML = listcontent.getAttribute("title");
				if(callback && Element.getStyle(self.nextSibling, "display") == "block") {
					var name = self.getAttribute("name");
					var set_args = [e, ((name == null) ? "" : name), self.innerHTML];
					for (var j = 0; j < args.length; ++j) {
						set_args.push(args[j]);
					}
	            	ret = callback.apply(self, set_args);
	            }
	            if(ret) {
					if(Element.getStyle(self.nextSibling, "display") == "none")
						Element.setStyle(self.nextSibling, {display:'block'});
					else
						Element.setStyle(self.nextSibling, {display:'none'});

					var a_list = $A(self.nextSibling.getElementsByTagName("a"));
					a_list.each(function(v){
						if(v.innerHTML == html)
							Element.addClassName(v,"active");
						else
							Element.removeClassName(v,"active");
					});
				}
	          	Event.stop(e);
				return false;
			}, false, this.id);

	        if(className) Element.addClassName(a,className);
			node.appendChild(a);
	        this.insertAfter(a, listmenu);
	        return a;
		},
		/**
		 * リストメニューをjavascriptから選択メソッド
		 * @param listbox       element  listbox element
		 * @param name          string   リストのキー名称
		 * @param callback_flag boolean  変更時にcallbackを実行するかどうか
		 */
		chgList : function(listbox, name, callback_flag) {
			var a = null;
			var a_list = $A(listbox.nextSibling.getElementsByTagName("a"));
			a_list.each(function(v){
				if(v.getAttribute("name") == name) {
					a = v;
				}
			});
			var listcontent = Element.getChildElementByClassName(listbox, "listcontent");
			if(name == "") {
				listcontent.innerHTML = listcontent.getAttribute('title');
				return;
			}
			if(!a)
				return false;
			if(callback_flag)
				a.click();
			else
				listcontent.innerHTML = a.innerHTML;
			return true;
		},
		/**
		 * リストメニューの選択しているキー取得
		 * @param listbox       element  listbox element
		 * @return string
		 */
		getList : function(listbox) {
			var ret= '';
			var listcontent = Element.getChildElementByClassName(listbox, "listcontent");
			var value = listcontent.innerHTML;

			var a_list = $A(listbox.nextSibling.getElementsByTagName("a"));

			a_list.each(function(v){
				if(value == v.innerHTML) ret = v.getAttribute("name");
			});
			return ret;
		},

		insertAfter : function(el, new_el) {
			var div, new_el_arr = [];
			if (typeof(new_el) == 'string') {
				div = el.ownerDocument.createElement("div");
				div.innerHTML = new_el;
				for (var i = 0; i < div.childNodes.length; i++) {
					new_el_arr.push(div.childNodes[i]);
				}
				for (var i = 0; i < new_el_arr.length; i++) {
					el.parentNode.insertBefore( new_el_arr[i], el.nextSibling );
				}
				div = null;
				return;
			}
			el.parentNode.insertBefore( new_el, el.nextSibling );
			div = null;
		},
		insertBefore : function(el, new_el) {
			var div, new_el_arr = [];
			if (typeof(new_el) == 'string') {
				div = el.ownerDocument.createElement("div");
				div.innerHTML = new_el;
				for (var i = 0; i < div.childNodes.length; i++) {
					new_el_arr.push(div.childNodes[i]);
				}
				for (var i = 0; i < new_el_arr.length; i++) {
					el.parentNode.insertBefore( new_el_arr[i], el );
				}
				div = null;
				return;
			}
			el.parentNode.insertBefore( new_el, el );
		}
}

var compUndoManager = Class.create();
var undoManagerComp = Array();

compUndoManager.prototype = {
		nc_wysiwyg : null,
    	data       : null,
    	index      : 0,
    	bm         : null,

    	initialize : function(nc_wysiwyg)
        {
        	this.nc_wysiwyg = nc_wysiwyg;
        	this.data = [];
        	this.bm = [];
        	this.index = 0;
        },

        add : function(init_flag) {
        	var t = this, i, ed = t.nc_wysiwyg, content, re, la;

        	content = ed.getContent();
        	la = t.data[t.index > 0 && t.index == t.data.length ? t.index - 1 : t.index];

        	// br delete
        	var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
            content = content.replace(re, '') + "<br />";

        	if(la == content)
        		return null;

        	if (t.data.length > ed.options.undo_level) {
				for (i = 0; i < t.data.length - 1; i++) {
					t.data[i] = t.data[i + 1];
					if((browser.isFirefox || (browser.isIE && browser.version >= 9)))
						t.bm[i] = ed.getBookmark();
				}

				t.data.length--;
				t.index = t.data.length;
			}

			if (t.index < t.data.length)
				t.index++;

			if (t.data.length === 0 && !init_flag)
				return null;

			// Add level
			t.data.length = t.index + 1;
			t.data[t.index] = content;
			if((browser.isFirefox || (browser.isIE && browser.version >= 9)))
				t.bm[t.index] = ed.getBookmark();
			t.index++

			if (init_flag)
				t.index = 0;

        	return content;
        },

        undo : function() {
        	var t = this, ed = t.nc_wysiwyg, content = null, i;

        	if (t.index > 0) {
				t.add();
        		// If undo on last index then take snapshot
				if (t.index == t.data.length && t.index > 1) {
					i = t.index;
					//t.typing = 0;

					if (!t.add())
						t.index = i;

					--t.index;
				}
        		content = t.data[--t.index];
        		ed.setContent(content);
        		if((browser.isFirefox || (browser.isIE && browser.version >= 9)))
	        		ed.moveToBookmark(t.bm[t.index]);
        	}

        	return content;
        },

        redo : function() {
			var t = this, ed = t.nc_wysiwyg, content = null;

			if (t.index < t.data.length - 1) {
				content = t.data[++t.index];
				ed.setContent(content);
				if((browser.isFirefox || (browser.isIE && browser.version >= 9)))
					ed.moveToBookmark(t.bm[t.index]);
			}

			return content;
		},

		clear : function() {
			var t = this;
			t.data = [];
			t.bm   = [];
			t.index = 0;
		}
}