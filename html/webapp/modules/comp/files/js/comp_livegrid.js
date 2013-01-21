/*
  Based on Rico LiveGrid v1.1.2, enhanced with resizable columns, sorting, filtering, horizontal scrolling and other goodies
  (http://openrico.org/)
  http://www.apache.org/licenses/LICENSE-2.0
  
  変更履歴：基本構造から改変
  バッファを、内部に保管しない(display切り替え)
  idではなくクラス名称からエレメントを取得するように修正
  action_nameでcommonCls.sendできるように修正
  パラメータ名一部変更
*/
//-------------------- ricoLiveGrid.js
// Rico.LiveGrid -----------------------------------------------------

var compLiveGrid = Class.create();

compLiveGrid.prototype = {
	initialize: function(top_el, visibleRows, totalRows, action_name ,options ) {
		if(visibleRows > totalRows) {
			visibleRows = totalRows;
		}
		this.top_el = top_el;
		this.action_name = action_name;
		
		this.limit_str = "limit";
		this.offset_str = "offset";
		this.sort_col_str = "sort_col";
		this.sort_dir_str = "sort_dir";
		
		if(action_name != undefined && action_name != null) var prefetchBuffer = true;
		else  var prefetchBuffer = false;
		
		//
		//idではなくクラス名称からエレメントを取得するように修正
		//
		//prefetchBuffer
		//onscroll
		//onscrollidle
		////loadingClass:         $(tableId).className,
		//sort trueならばソート処理を入れる　defalt false
		this.options = {
				prefetchBuffer:       prefetchBuffer,
				tableClass:           'grid',
				scrollerBorderRight: '1px solid #ababab',
				bufferTimeout:        20000,
				sort:                 false,
				sort_prefix:          "_sort_",
				sortAscendImg:        _nc_core_base_url + '/images/comp/livegrid/sort_asc.gif',
				sortDescendImg:       _nc_core_base_url + '/images/comp/livegrid/sort_desc.gif',
				sortImageWidth:       9,
				sortImageHeight:      5,
				onSendCallback:       null,
				onRefreshComplete:    null,
				requestParameters:    null
		};
		Object.extend(this.options, options || {});
		
		this.table = Element.getChildElementByClassName(this.top_el,this.options.tableClass);
		this.addLiveGridHtml();
		
		//this.visibleRows = visibleRows;
		//this.totalRows = totalRows;	
		
		var rowCount = this.table.rows.length;
		var columnCount  = (rowCount == 0) ? 0 : this.table.rows[0].cells.length;
		this.metaData    = new compLiveGrid.LiveGridMetaData(visibleRows, totalRows, columnCount, this.options);
		this.buffer      = new compLiveGrid.LiveGridBuffer(this.metaData, visibleRows, 0, rowCount, this.table);	
		//
		// tdタグにdivタグを追加
		//
		for (var i=0; i < rowCount; i++) {
			if(this.table.rows[i]) {
				if ( !this.options.prefetchBuffer) {
					this.buffer.rows[i]= true;
				}
				this.buffer.value_el_rows[i] = this.table.rows[i];
				this.buffer.key_el_rows[this.table.rows[i]] = i;
					
            	this.buffer.initRow(this.table.rows[i]);
            	if(i > visibleRows - 1) {
                	Element.addClassName(this.table.rows[i],"display-none");
                }
            } 
        }
		//1レコードの高さ(this.table.offsetHeight/rowCount)
		this.viewPort = new compLiveGrid.GridViewPort(this.table, 
                                            this.table.offsetHeight/visibleRows,
                                            visibleRows,
                                            this.buffer, this);
		
		this.scroller    = new compLiveGrid.LiveGridScroller(this, this.viewPort);
		this.options.sortHandler = this.sortHandler.bind(this);
		
		this.table_header = Element.getChildElementByClassName(this.top_el,this.options.tableClass+'_header');
		if ( this.table_header ) {
			if(browser.isIE) {
				this.table_header.style.width = this.table.offsetWidth + "px";
			}
			if(this.options.sort) {
				this.sort = new compLiveGrid.LiveGridSort(this.options.tableClass+'_header', this.table_header ,this.options);
            }
            //修正：header　Width
            //初期化 overflow:hiddenに設定
		    for (var i=0,row_len = this.table_header.rows.length; i < row_len; i++) {
                this.buffer.initRow(this.table_header.rows[i]);
            }
        }
		this.processingRequest = null;
		//this.unprocessedRequest = null;
		
		if ( this.options.prefetchBuffer) {
		//if ( this.options.prefetchBuffer || this.options.prefetchOffset > 0) {
			//最初に読み込み処理を行うならば
			var offset = 0;
			if (this.options.offset ) {
				offset = this.options.offset;            
				this.scroller.moveScroll(offset);
				this.viewPort.scrollTo(this.scroller.rowToPixel(offset));            
			} else {
				this.viewPort.refreshContents(offset);
			}
			if (this.options.sortCol) {
				this.sortCol = options.sortCol;
				this.sortDir = options.sortDir;
			}
			
         	this.requestContentRefresh(offset);
		}
		//IEがページスクロール時にテーブルの広さが若干狭くなるため修正
		if(browser.isIE) {
			this.table.style.width = this.table.offsetWidth + "px";
		}
	},
	addLiveGridHtml: function() {
		// header部分自動生成処理
		// Check to see if need to create a header table.
		if (this.table.getElementsByTagName("thead").length > 0){
			// Create Table this.options.tableClass+'_header'
			var tableHeader = this.table.cloneNode(true);
			tableHeader.setAttribute('class', this.options.tableClass+'_header');
			// Clean up and insert
			for( var i = 0; i < tableHeader.tBodies.length; i++ ) 
				tableHeader.removeChild(tableHeader.tBodies[i]);
			this.table.deleteTHead();
			this.table.parentNode.insertBefore(tableHeader,this.table);
		}
		
		new Insertion.Before(this.table, "<div class='"+this.options.tableClass+"_container'></div>");
		this.table.previousSibling.appendChild(this.table);
		new Insertion.Before(this.table,"<table border='0' cellspacing='0' cellpadding='0' class='"+this.options.tableClass+"_viewport'><tr><td><div></div></td><td class='valign-top'></td></tr></table>");
		//new Insertion.Before(this.table,"<div class='"+this.options.tableClass+"_viewport' style='float:left;'></div>");
		//this.table.previousSibling.appendChild(this.table);
		//
		//floatを使わないように修正
		//
		var table_view = Element.getChildElementByClassName(this.top_el,this.options.tableClass + "_viewport");
		var div_el = Element.getChildElement(table_view.rows[0].cells[0]);
		//if(browser.isSafari) {
			//var tr_el = table_view.getElementsByTagName("tr")[0];
		//	//if(tr_el) {
		//		var offset = 0;
		//		div_el.style.width = (valueParseInt(Element.getStyle( this.table, "width" )) + offset) + "px";
		//		//debug.p(this.table.parentNode.className);
		//	//}
		//}
		div_el.appendChild(this.table);
		//if(browser.isSafari) {
		//	//var offset = 19;
		//	div_el.style.width = (valueParseInt(Element.getStyle( this.table, "width" )) + offset) + "px";
		//}
	},

	resetContents: function() {
		this.scroller.moveScroll(0);
		this.buffer.clear();
		this.viewPort.clearContents();
	},
   
	sortHandler: function(column) {
		if(!column) return ;
		this.sortCol = column.name;
		this.sortDir = column.currentSort;
		
		this.resetContents();
		this.requestContentRefresh(0); 
	},

	adjustRowSize: function() {
		  
	},
	setTotalRows: function( newTotalRows, reset_flag ) {
		if(reset_flag) this.resetContents();
		this.totalRows = newTotalRows;
		if(this.visibleRows > this.totalRows) {
			this.visibleRows = this.totalRows;
			this.viewPort.visibleRows = this.visibleRows;
		}
		this.metaData.setTotalRows(newTotalRows);
		this.scroller.updateSize();
		//this.resetContents();
		//this.metaData.setTotalRows(newTotalRows);
		//this.scroller.updateSize();
	},
	
	//initAjax: function(url) {
	//	ajaxEngine.registerRequest( this.tableId + '_request', url );
	//	ajaxEngine.registerAjaxObject( this.tableId + '_updater', this );
	//},

	//invokeAjax: function() {
	//},

	handleTimedOut: function() {
		//server did not respond in 4 seconds... assume that there could have been
		//an error or something, and allow requests to be processed again...
		this.processingRequest = null;
		//this.processQueuedRequest();
	},
	fetchBuffer: function(offset) {
		// 修正
		if ( this.buffer.isInRange(offset) ) {	
			return;
		}
		//if ( this.buffer.isInRange(offset) &&
		//	!this.buffer.isNearingLimit(offset)) {
		//	return;
		//}
		if (this.processingRequest) {
			//this.unprocessedRequest = new compLiveGrid.LiveGridRequest(offset);
			setTimeout( function(){this.fetchBuffer(offset);}.bind(this), 300);
			
			return;
		}
		var bufferStartPos = this.buffer.getFetchOffset(offset);
		this.processingRequest = true;		//new compLiveGrid.LiveGridRequest(offset);
		//this.processingRequest.bufferOffset = bufferStartPos;   
		var fetchSize = this.buffer.getFetchSize(bufferStartPos);
		//
		// table行初期化
		//
		//initTableFlag = false;
		//for (var i = 0; i < bufferStartPos + fetchSize; i++) {
		//	var row_el = this.table.rows[i];
		//	if(!row_el) {
		//		var insert_tr = this.table.insertRow(i);
		//		Element.addClassName(insert_tr,"display-none");
		//	}
		//}
		//if(initTableFlag) this.buffer.initTable(bufferStartPos + fetchSize);
		
		
		var partialLoaded = false;
		//パラメータをcommonCls.sendメソッドを使用するように修正
		var queryString;
		if (this.options.requestParameters)
			queryString = this._createQueryString(this.options.requestParameters, 0);
		if(this.action_name == undefined) {
            return;
        }
		queryString = (queryString == null) ? '' : '&'+queryString;
		queryString  = queryString+'&' + this.limit_str + '='+fetchSize+'&' + this.offset_str + '='+bufferStartPos;

		if (this.sortCol) {
			queryString = queryString+'&' + this.sort_col_str + '='+escape(this.sortCol)+'&' + this.sort_dir_str + '='+this.sortDir;
		}
		//パラメータ設定
		var send_params = new Object();
		
		send_params["method"] = "get";
		send_params["param"] = this.action_name + queryString;
		send_params["top_el"] = this.top_el;
		send_params["eval_flag"] = 0;
		//send_params["loading_el"] = this.table;
		//send_params["match_str"] = "^true";
		//send_params["callbackfunc"] = function(){this.announcementMainShow();}.bind(this);

		send_params["callbackfunc"] = function(ajaxResponse){
											try {
												this.buffer.update(ajaxResponse,bufferStartPos);
												//this.buffer.update(ajaxResponse,this.processingRequest.bufferOffset);
												//TODO:後に削除
												/*
												if(!(this.viewPort.rowHeight > 0)) {
													//var row_len = this.table.rows.length;
													//this.buffer.size = row_len;
													//行の幅がテキストを越えた場合、this.table.offsetHeightがIEの場合おかしくなるためinit処理を行う
													//if(browser.isIE) {
                                                    //    for (var i=0; i < row_len; i++) {
                                                    //        var now_row = this.table.rows[i];
                                                    //        this.initRow(now_row);
                                                    //    }
                                                    //}
                                                    var row_len = this.buffer.size;
													//1レコードの高さ(this.table.offsetHeight/rowCount)
													this.viewPort = new compLiveGrid.GridViewPort(this.table, 
											                                            this.table.offsetHeight/row_len,
											                                            this.visibleRows,
											                                            this.buffer, this);
                                            
													this.scroller    = new compLiveGrid.LiveGridScroller(this,this.viewPort);
													this.viewPort.bufferChanged();
												}
												*/
												//this.viewPort.bufferChanged();
												
											}
											catch(err) {}
											finally {this.processingRequest = null;}
											//this.processQueuedRequest();
											if(this.options.onSendCallback != null) {
												this.options.onSendCallback(ajaxResponse, this.action_name + queryString);
											}
										}.bind(this);
		commonCls.send(send_params);
        //this.ajaxOptions.parameters = queryString;
		//ajaxEngine.sendRequest( this.tableId + '_request', this.ajaxOptions );	
		////this.timeoutHandler = setTimeout( this.handleTimedOut.bind(this), this.options.bufferTimeout);
	},
   
	setRequestParams: function() {
		this.options.requestParameters = [];
		for ( var i=0 ; i < arguments.length ; i++ )
			this.options.requestParameters[i] = arguments[i];
	},

	requestContentRefresh: function(contentOffset) {
		this.fetchBuffer(contentOffset);
	},

	//ajaxUpdate: function(ajaxResponse) {
	//	try {
	//		clearTimeout( this.timeoutHandler );
	//		this.buffer.update(ajaxResponse,this.processingRequest.bufferOffset);
	//		this.viewPort.bufferChanged();
	//	}
	//	catch(err) {}
	//	finally {this.processingRequest = null; }
	//	this.processQueuedRequest();
	//},

	_createQueryString: function( theArgs, offset ) {
		var queryString = ""
		if (!theArgs)
			return queryString;
		for ( var i = offset,theArgs_len = theArgs.length ; i < theArgs_len ; i++ ) {
			if ( i != offset )
				queryString += "&";

			var anArg = theArgs[i];

			if ( anArg.name != undefined && anArg.value != undefined ) {
				queryString += anArg.name +  "=" + escape(anArg.value);
			}
			else {
				var ePos  = anArg.indexOf('=');
				var argName  = anArg.substring( 0, ePos );
				var argValue = anArg.substring( ePos + 1 );
				queryString += argName + "=" + escape(argValue);
			}
		}
		return queryString;
	}
/*
	processQueuedRequest: function() {
		if (this.unprocessedRequest != null) {
			this.requestContentRefresh(this.unprocessedRequest.requestOffset);
			this.unprocessedRequest = null
		}
	}
*/
};
// Rico.LiveGridMetaData -----------------------------------------------------

compLiveGrid.LiveGridMetaData = Class.create();

compLiveGrid.LiveGridMetaData.prototype = {
   
	initialize: function( pageSize, totalRows, columnCount, options ) {
		this.pageSize  = pageSize;
		this.totalRows = totalRows;
		this.setOptions(options);
		this.ArrowHeight = 16;
		this.columnCount = columnCount;
	},

	setOptions: function(options) {
		this.options = {
			largeBufferSize    : 2.0,   // 2 pages
			nearLimitFactor    : 0.2    // 20% of buffer
		};
		Object.extend(this.options, options || {});
	},

	getPageSize: function() {
		return this.pageSize;
	},

	getTotalRows: function() {
		return this.totalRows;
	},

	setTotalRows: function(n) {
		this.totalRows = n;
	},

	getLargeBufferSize: function() {
		return parseInt(this.options.largeBufferSize * this.pageSize);
	},

	getLimitTolerance: function() {
		return parseInt(this.getLargeBufferSize() * this.options.nearLimitFactor);
	}
};

// Rico.LiveGridScroller -----------------------------------------------------

compLiveGrid.LiveGridScroller = Class.create();

compLiveGrid.LiveGridScroller.prototype = {

	initialize: function(liveGrid, viewPort) {
		// this.isIE = navigator.userAgent.toLowerCase().indexOf("msie") >= 0;
		this.liveGrid = liveGrid;
		this.viewPort = viewPort;
		this.metaData = liveGrid.metaData;
		this.createScrollBar();
		this.scrollTimeout = null;
		this.lastScrollPos = 0;
		this.rows = new Array();
		//追加
		this.handleScrollEvent = null;
	},

	isUnPlugged: function() {
		return this.scrollerDiv.onscroll == null;
	},
	plugin: function() {
		//observeに変更
		Event.observe(this.scrollerDiv,"scroll", this.handleScroll.bindAsEventListener(this), false, this.liveGrid.top_el);
		//this.scrollerDiv.onscroll = this.handleScroll.bindAsEventListener(this);
	},
	unplug: function() {
		//observeに変更
		Event.stopObserving(this.scrollerDiv,"scroll", this.handleScroll.bindAsEventListener(this), false, this.liveGrid.top_el);
		//this.scrollerDiv.onscroll = null;
	},

	sizeIEHeaderHack: function() {
		//if ( !this.isIE ) return;
		if ( !browser.isIE ) return;
		//修正
		//var headerTable = $(this.liveGrid.tableId + "_header");
		var headerTable = this.liveGrid.table_header;
		if ( headerTable )
			headerTable.rows[0].cells[0].style.width =
 				(headerTable.rows[0].cells[0].offsetWidth + 1) + "px";
	},

	createScrollBar: function() {
		var visibleHeight = this.liveGrid.viewPort.visibleHeight();
		// create the outer div...
		this.scrollerDiv  = document.createElement("div");
		var scrollerStyle = this.scrollerDiv.style;
		
		//scrollerStyle.borderRight = this.liveGrid.options.scrollerBorderRight;
		//scrollerStyle.position    = "relative";

		// create the inner div...
		this.heightDiv = document.createElement("div");

		if(this.viewPort.visibleRows != this.metaData.totalRows) {
			//scrollerStyle.left        = "-3px";
			//scrollerStyle.left        = browser.isIE ? "-6px" : "-3px";
			scrollerStyle.borderRight = this.liveGrid.options.scrollerBorderRight;
			scrollerStyle.width       = "19px";
			if(browser.isIE) {
				scrollerStyle.overflowY = "scroll";
			}
			this.heightDiv.style.width  = "1px";
		} else {
      		//スクロールバーを表示させなくてよい
      		//scrollerStyle.left        = "-1px";
      		//scrollerStyle.left        = browser.isIE ? "-4px" : "-1px";
        	scrollerStyle.width       = "0px";
		}
		scrollerStyle.height      = visibleHeight + "px";
		scrollerStyle.overflow    = "auto";
      
	
		var height_buf = parseInt(visibleHeight *
                        this.metaData.getTotalRows()/this.metaData.getPageSize());

        
        if(!isNaN(height_buf) && height_buf != null) this.heightDiv.style.height = height_buf + "px" ;
		this.plugin();	//必要ないかも・・・
		//this.scrollerDiv.onscroll = this.handleScroll.bindAsEventListener(this);
		var table = this.liveGrid.table;
		//table.parentNode.parentNode.insertBefore( this.scrollerDiv, table.parentNode.nextSibling );
		//floatを使わないように修正
		table.parentNode.parentNode.parentNode.cells[1].appendChild(this.scrollerDiv);
		
		this.scrollerDiv.appendChild(this.heightDiv);
		if(this.viewPort.visibleRows != this.metaData.totalRows) {
			var eventName = browser.isIE ? "mousewheel" : "DOMMouseScroll";
			Event.observe(table, eventName, 
		                function(evt) {
		                   if (evt.wheelDelta>=0 || evt.detail < 0) //wheel-up
		                      this.scrollerDiv.scrollTop -= (2*this.viewPort.rowHeight);
		                   else
		                      this.scrollerDiv.scrollTop += (2*this.viewPort.rowHeight);
		                   this.handleScroll(false);
		                }.bindAsEventListener(this), 
	  	    false, this.liveGrid.top_el);
		}
	},
	updateSize: function() {
		if(this.viewPort.visibleRows == this.metaData.totalRows) {
			//スクロールバーを表示させなくてよい
			this.scrollerDiv.style.width       = "0px";
		}
		this.scrollerDiv.style.height = (this.viewPort.rowHeight * this.viewPort.visibleRows) + "px";
      
		var table = this.liveGrid.table;
		var visibleHeight = this.viewPort.visibleHeight();
		this.heightDiv.style.height = parseInt(visibleHeight *
                                  this.metaData.getTotalRows()/this.metaData.getPageSize()) + "px";
	},
	rowToPixel: function(rowOffset) {
		return (rowOffset / this.metaData.getTotalRows()) * this.heightDiv.offsetHeight
	},
   
	moveScroll: function(rowOffset) {
		this.scrollerDiv.scrollTop = this.rowToPixel(rowOffset);
		if ( this.metaData.options.onscroll )
			this.metaData.options.onscroll( this.liveGrid, rowOffset );
	},
	handleScroll: function() {
		if ( this.scrollTimeout ) {
			clearTimeout( this.scrollTimeout );
		}
		if ( this.handleScrollEvent ) {
			clearTimeout( this.handleScrollEvent );
		}
		//IEの場合、複数、onscrollイベントが呼ばれるため、最後の通知のみ拾う
		this.handleScrollEvent = setTimeout(this.handleScrollTimer.bind(this), 0 );
	},
	handleScrollTimer: function() {
		//if ( this.scrollTimeout ) {
		//    clearTimeout( this.scrollTimeout );
		//}
		var scrollDiff = this.lastScrollPos-this.scrollerDiv.scrollTop;
		if (scrollDiff != 0.00) {
			var r = this.scrollerDiv.scrollTop % this.viewPort.rowHeight;
			if (r != 0) {
				this.unplug();
				if ( scrollDiff < 0 ) {
					this.scrollerDiv.scrollTop += (this.viewPort.rowHeight-r);
				} else {
					this.scrollerDiv.scrollTop -= r;
				}
				this.plugin();
			}
		}
		var contentOffset = Math.round(this.scrollerDiv.scrollTop / this.viewPort.rowHeight);

		this.liveGrid.requestContentRefresh(contentOffset);
		this.viewPort.scrollTo(this.scrollerDiv.scrollTop);

		if ( this.metaData.options.onscroll )
			this.metaData.options.onscroll( this.liveGrid, contentOffset );

		this.scrollTimeout = setTimeout(this.scrollIdle.bind(this), 1200 );
		this.lastScrollPos = this.scrollerDiv.scrollTop;
	},

	scrollIdle: function() {
		if ( this.metaData.options.onscrollidle )
			this.metaData.options.onscrollidle();
	}
};

// Rico.LiveGridBuffer -----------------------------------------------------

compLiveGrid.LiveGridBuffer = Class.create();

compLiveGrid.LiveGridBuffer.prototype = {
	initialize: function(metaData, visibleRows, startPos, size, table) {
		this.startPos = 0;		//startPos;						//スタート位置
		this.size     = size;									//offset
		this.metaData = metaData;
		this.rows         = new Array();							//取得中行配列
		this.value_el_rows= new Array();							//取得中行配列 key:行数 value el
		this.key_el_rows  = new Array();							//取得中行配列 key:el   value 行数
		this.visibleRows = visibleRows;
		
		this.width_buf = new Object();
	
		this.table = table;
		
		this.maxFetchSize = metaData.getLargeBufferSize();
		//this.lastOffset = 0;
		
		//Buffer行エレメント取得
		if(this.table.rows[0]) {
			//XMLでの取得用クローン
			this.clone_row = this.table.rows[0].cloneNode(true);
			/*
			var tdList = this.clone_row.getElementsByTagName("td");
			for (var i = 0,cell_len=tdList.length; i < cell_len; i++) {
				var div_el =Element.getChildElement(tdList[i]);
				if(!div_el || div_el.tagName.toLowerCase() != 'div' || Element.getStyle( div_el, "overflow" ) != "hidden") {
					tdList[i].innerHTML = "";
				} else {
					div_el.innerHTML = "";
				}
				
			}
			*/
			// trのクラスは、prefixがgridのもののみ追加
			var class_name = this.clone_row.className;
			if(class_name) {
				var class_arr = class_name.split(/\s+/);
				var add_class_arr = new Array();
				class_arr.each(function(name) {
					if(name.match(/^grid_.*/)) {
						this.push(name);
					}
				}.bind(add_class_arr));
				this.clone_row.className = add_class_arr.join(' ');
			}
			
			this.initRow(this.clone_row, true);
			//for (var i = 0,cell_len=this.clone_row.childNodes.length; i < cell_len; i++) {
			//	var cell_el = this.clone_row.childNodes[i];
			//	cell_el.innerHTML = "&nbsp;";
			//}
		}
	},
	/*
	initTable: function(end_position) {
		var tbody = Element.getChildElement(this.table);
		if(tbody == null || tbody.tagName.toLowerCase() != "tbody") {
			tbody = this.table;
		}
		for (var i = 0; i < end_position; i++) {
			if(!this.table.rows[i]) {
				//tr-tdエレメント挿入
				if(browser.isSafari) {
					var new_tr = document.createElement("tr");
					new_tr.innerHTML = this.clone_row.innerHTML;
					new_tr.className = this.clone_row.className;
				} else {
					var new_tr = this.clone_row.cloneNode(true);
				}
				if(browser.isIE) {
					Element.addClassName(new_tr,"display-none");
					tbody.appendChild(new_tr);
				} else {
					var insert_tr = this.table.insertRow(i);
					insert_tr.className = new_tr.className;
					Element.addClassName(insert_tr,"display-none");
					insert_tr.innerHTML = new_tr.innerHTML;
					new_tr = null;
				}
			} else if(this.table.rows[i].cells[0]){
				if(this.table.rows[i].cells[0].innerHTML != "") {
					//値が入っている場合、取得済みとする
					this.rows[i] = true;
				}
			}
		}
	},
	*/
	update: function(ajaxResponse, start) {
		//tableにデータ挿入
		//this.startPos = start;
		var tbody = Element.getChildElement(this.table);
		if(tbody == null || tbody.tagName.toLowerCase() != "tbody") {
			tbody = this.table;
		}
		if(typeof ajaxResponse == 'string') {
			//
			// HTML
			//
			if(ajaxResponse != "") {alert(ajaxResponse);}
			return false;
		} else {
			//
			// XML
			//
			var lists = Element.getChildElement(ajaxResponse);
			var child_len=lists.childNodes.length;
			//var buf_len = this.table.rows.length;
			for (var i = 0; i < child_len; i++) {
				/****
				if(!this.rows[start+i]) {
					if(this.table.rows[start+i]) {
						var table_row_el = this.table.rows[start+i];
					} else {
						var rows_len = this.table.rows.length;
						if(rows_len < start+i) {
						    var table_row_el = this.table.insertRow(rows_len);
						} else {
							var table_row_el = this.table.insertRow(start+i);
						}
					}
					this.rows[start+i] = table_row_el;
				} else {
					var table_row_el = this.rows[start+i];
				}
				****/
				
				var table_row_el = this.value_el_rows[start+i];
				
				//table_row_el.className = this.clone_row.className;
				//if(row_start + this.visibleRows <= start+i) {
				//	Element.addClassName(table_row_el,"display-none");
				//}
				//if(!table_row_el.cells[0]) {
				//	// tdエレメントがなければ作成
				//	for (var j = 0,cells_len = this.clone_row.cells.length; j < cells_len; j++) {
				//		var insert_td = table_row_el.insertCell(j);
				//		insert_td.className = this.clone_row.cells[j].className;
				//		insert_td.innerHTML = this.clone_row.cells[j].innerHTML;
				//	}
				//}
				
				var row_el = lists.childNodes[i];
				//
				//xmlのid,class属性がある場合、this.table-trにセットする
				//
				var class_name = Element.readAttribute(row_el, "class");
				if(class_name) {
					Element.addClassName(table_row_el, class_name);
					//Element.addClassName(this.table.rows[start+i], class_name);
				}
				var id = Element.readAttribute(row_el, "id");
				if(id) {
					table_row_el.id = id;
				}
				
				//if(!this.table.rows[start+i]) {
				//	//tr-tdエレメント挿入
				//	var new_tr = this.clone_row.cloneNode(true);
				//	if(browser.isIE) {
				//		tbody.appendChild(new_tr);
				//	} else {
				//		var insert_tr = this.table.insertRow(buf_len + i);
				//		insert_tr.innerHTML = new_tr.innerHTML;
				//		new_tr = null;
				//	}
				//} else {
				//	buf_len--;
				//}
				if(row_el) {
					// tdエレメントがなければ作成
					//if(table_row_el.childNodes.length == 0) {
					//	for (var j = 0,cells_len = this.clone_row.childNodes.length; j < cells_len; j++) {
					//		var insert_td = table_row_el.insertCell(j);
					//		insert_td.className = this.clone_row.childNodes[j].className;
					//		insert_td.innerHTML = this.clone_row.childNodes[j].innerHTML;
					//	}
					//}
					for (var j = 0,cell_len=row_el.childNodes.length; j < cell_len; j++) {
						var cell_el = row_el.childNodes[j];
						//
						//xmlのclass属性がある場合、this.table-tdにセットする
						//
						var class_name = Element.readAttribute(cell_el, "class");
						if(class_name) {
							Element.addClassName(table_row_el.childNodes[j], class_name);
							//Element.addClassName(this.table.rows[start+i].childNodes[j], class_name);
						}
						
						//div作成
						//if(!this.table.rows[start+i].childNodes[j].childNodes[0]) {
						//	this.appendDivElement(this.table.rows[start+i].childNodes[j]);
						//}
						var text = "";
						if(cell_el.firstChild != null) {text = cell_el.firstChild.nodeValue;}
						
						Element.getChildElement(table_row_el.childNodes[j]).innerHTML = cell_el.textContent || cell_el.text || text;
						//this.table.rows[start+i].childNodes[j].childNodes[0].innerHTML = cell_el.textContent || cell_el.text || text;
					}
					this.rows[start+i] = true;
					//if(row_start + this.visibleRows > start+i) {
					//	Element.removeClassName(table_row_el,"display-none");
					//}
				}
			}
		}
		this.size = start + child_len;
	},
   
	clear: function() {
		this.rows = new Array();
		//this.value_el_rows = new Array();
		//this.key_el_rows = new Array();
		this.startPos =  0;
		this.size = 0;
	},

	isInRange: function(position) {
		//追加修正
		var end_position = position + this.metaData.getPageSize() - 1;
		if(end_position > this.metaData.getTotalRows() - 1) {
			end_position = this.metaData.getTotalRows() - 1;
		}
		// 未取得があるかどうか
		var now_pos = position;
		while (this.rows[now_pos]) {
		
      		if(now_pos == end_position) return true;
      		now_pos++;
  		}
	  		
		return false;
		//return (position >= this.startPos) && (position + this.metaData.getPageSize() <= this.endPos()); 
			//&& this.size()  != 0;
	},

	isNearingTopLimit: function(position) {
		return position - this.startPos < this.metaData.getLimitTolerance();
	},

	endPos: function() {
		return this.table.rows.length;
		//return this.startPos + this.rows.length;
	},
   
	isNearingBottomLimit: function(position) {
		return this.endPos() - (position + this.metaData.getPageSize()) < this.metaData.getLimitTolerance();
	},

	isAtTop: function() {
		return this.startPos == 0;
	},

	isAtBottom: function() {
		return this.endPos() == this.metaData.getTotalRows();
	},

	isNearingLimit: function(position) {
		return ( !this.isAtTop()    && this.isNearingTopLimit(position)) ||
             ( !this.isAtBottom() && this.isNearingBottomLimit(position) );
	},

	getFetchSize: function(offset) {
		var adjustedOffset = offset;
		//var adjustedOffset = this.getFetchOffset(offset);
		var adjustedSize = 0;
		var endFetchOffset = this.maxFetchSize  + adjustedOffset;
		if (endFetchOffset > this.metaData.totalRows) {
			//トータル数を越えている
			if(this.metaData.totalRows > 0) {
				endFetchOffset = this.metaData.totalRows;
			}
			// else {
			//	endFetchOffset = 0;
			//}
		}
		if(this.rows[endFetchOffset]) {
			//既に取得済み
			var row_num = endFetchOffset - 1;
			while (this.rows[row_num]) {
	      		row_num--;
	      		if(row_num < adjustedOffset) {
	      			//start行数を越えた場合
	      			return 1;
	      		}
	  		}
	  		endFetchOffset = row_num + 1;
		}
		adjustedSize = endFetchOffset - adjustedOffset;
      /*
      var adjustedSize = 0;
      if (adjustedOffset >= this.startPos) { //apending
         var endFetchOffset = this.maxFetchSize  + adjustedOffset;
         if (endFetchOffset > this.metaData.totalRows)
            endFetchOffset = this.metaData.totalRows;
         adjustedSize = endFetchOffset - adjustedOffset;  
			if(adjustedOffset == 0 && adjustedSize < this.maxFetchSize){
			   adjustedSize = this.maxFetchSize;
			}
      } else { //prepending
         var adjustedSize = this.startPos - adjustedOffset;
         if (adjustedSize > this.maxFetchSize)
            adjustedSize = this.maxFetchSize;
      }
      */
      return adjustedSize;
	}, 

	
	getFetchOffset: function(offset) {
		//fetch start位置取得	
		var adjustedOffset = offset;
		if(this.rows[adjustedOffset]) {
			//既に取得済み
			var row_num = adjustedOffset + 1;
			while (this.rows[row_num]) {
          		row_num++;
          		if(row_num > this.metaData.getTotalRows() - 1) {
          			//トータル行数を越えた場合
          			return offset;
          		}
      		}
      		adjustedOffset = row_num;
		}
		return adjustedOffset;
   /*
      var adjustedOffset = offset;
      if (offset > this.startPos) { 
         //apending
         adjustedOffset = (offset > this.endPos()) ? offset :  this.endPos(); 
      } else { //prepending
         if (offset + this.maxFetchSize >= this.startPos) {
            var adjustedOffset = this.startPos - this.maxFetchSize;
            if (adjustedOffset < 0)
               adjustedOffset = 0;
         }
      }
      this.lastOffset = adjustedOffset;
      return adjustedOffset;
   */
   },
	convertSpaces: function(s) {
		return s.split(" ").join("&nbsp;");
	},
	initRow: function(htmlRow, clone_flag) {
		for (var j=0,row_len=htmlRow.childNodes.length; j < row_len; j++) {
			//td-divに対応
			var child_el = Element.getChildElement(htmlRow.childNodes[j]);
			if(!child_el || child_el.tagName.toLowerCase() != 'div' || Element.getStyle( child_el, "overflow" ) != "hidden") {		// || ( || Element.getStyle( child_el, "white-space" ) != "nowrap")
         		//div作成
         		if((browser.isIE || browser.isSafari) && clone_flag) {
         			//SafariはNodeにないものの広さを求められないため
         			this.appendDivElement(htmlRow.childNodes[j], this.table.rows[0].childNodes[j]);
         		} else {
					this.appendDivElement(htmlRow.childNodes[j]);
				}
				child_el = Element.getChildElement(htmlRow.childNodes[j]);
			} 
			//if(browser.isIE && clone_flag) {
			//}
			//if(browser.isIE && !clone_flag) {
			//	var parent_el = htmlRow.childNodes[j];
			//	
			//	parent_el.style.width = (valueParseInt(Element.getStyle( child_el, "width" ))
           	//		+ valueParseInt(Element.getStyle( parent_el, "paddingLeft" )) 
           	//		+ valueParseInt(Element.getStyle( parent_el, "paddingRight" ))) + "px";
			//}
			if(child_el && clone_flag) {
				child_el.innerHTML = "";
			}
		}
	},
	appendDivElement: function(el, first_el) {
		// widthの値は静的な値ではないため（getComputedStyleを使用するため）
		// 最初に取得したものを使いまわす
		if(el.className != "") {
			if(this.width_buf[el.className]) {
				var width = this.width_buf[el.className];
			} else {
				var width = valueParseInt(Element.getStyle( el, "width" ));
				this.width_buf[el.className] = width;
			}
		} else {
			var width = valueParseInt(Element.getStyle( el, "width" )); 
		}

		if(width == 0 && first_el) {
			//Safari用
			var width = valueParseInt(Element.getStyle( first_el, "width" ));
			this.width_buf[el.className] = width;
		}

		var child_el  = document.createElement("div");
		var children_length = el.childNodes.length;
		var el_arr = new Object;
		for (var k = 0; k < children_length; k++) {
			var child = el.childNodes[k];
		    if (child.nodeType == 1) {
		    	el_arr[k] = child;
		    } else if(child.nodeType == 3) {
		        el_arr[k] = document.createTextNode(child.nodeValue);
		        child.nodeValue = "";
		    }
		}
		el.appendChild(child_el);
		for (var k = 0; k < children_length; k++) {
			child_el.appendChild(el_arr[k]);
		}
		//el.innerHTML = "";
		this.divAddStyle(child_el, el, width);
		
	},
	divAddStyle: function(el, parent_el, parent_width) {
		var width = parent_width;
	   //if(browser.isIE) {
       //    var width = parent_width;
           // - valueParseInt(Element.getStyle( parent_el, "paddingLeft" )) - valueParseInt(Element.getStyle( parent_el, "paddingRight" ));
           //		 - valueParseInt(Element.getStyle( parent_el, "borderLeftWidth" )) - valueParseInt(Element.getStyle( parent_el, "borderRightWidth" ));
      // } else {
       //     var width = parent_width;
            //- valueParseInt(Element.getStyle( parent_el, "paddingLeft" )) - valueParseInt(Element.getStyle( parent_el, "paddingRight" ))
       		//- valueParseInt(Element.getStyle( parent_el, "borderLeftWidth" )) - valueParseInt(Element.getStyle( parent_el, "borderRightWidth" ));
       //}
       //if(el.style.overflow != "hidden" && width > 0) {
           el.style.width = width + "px";
           el.style.overflow = "hidden";
           el.style.whiteSpace = "nowrap";
       //}
	}
};

//Rico.GridViewPort --------------------------------------------------
compLiveGrid.GridViewPort = Class.create();

compLiveGrid.GridViewPort.prototype = {

   initialize: function(table, rowHeight, visibleRows, buffer, liveGrid) {
		//追加
		rowHeight = (isNaN(rowHeight) || rowHeight == null || rowHeight == undefined) ? 0 : rowHeight;
      
		this.lastDisplayedStartPos = 0;
		this.div = table.parentNode;
		this.table = table
		this.rowHeight = rowHeight;
		//this.div.style.height = (this.rowHeight * visibleRows) + "px";
		//this.div.style.overflow = "hidden";
		this.div.style.whiteSpace = "nowrap";	//追加
		//this.div.style.borderLeftStyle = Element.getStyle(this.table, 'borderLeftStyle');
		//this.div.style.borderLeftWidth = Element.getStyle(this.table, 'borderLeftWidth');
		//this.div.style.borderLeftColor = Element.getStyle(this.table, 'borderLeftColor');
		
		this.buffer = buffer;
		this.liveGrid = liveGrid;
		this.visibleRows = visibleRows;
		//this.visibleRows = visibleRows + 1;
		this.lastPixelOffset = 0;
		this.startPos = 0;
		// 追加
		this.lastStartPos = 0;
		if(liveGrid.options.prefetchBuffer) {
			this.isBlank = true;
		} else {
			this.isBlank = false;
		}
	},
/*
   populateRow: function(htmlRow, row) {
      for (var j=0; j < row.length; j++) {
         //td-divに対応
         htmlRow.cells[j].innerHTML = ""
         htmlRow.cells[j].appendChild(row[j]);
         var child_el = Element.getChildElement(htmlRow.cells[j]);
         if(!child_el || (child_el.style.overflow != "hidden" || child_el.style.whiteSpace != "nowrap")) {
         	//htmlRow.cells[j].innerHTML = row[j];
         	//div作成
         	this.liveGrid.appendDivElement(htmlRow.cells[j]);
         } 
         //else {
         	//htmlRow.cells[j].innerHTML = row[j];
         //}
         //TODO:後に削除
         //var child_el = Element.getChildElement(htmlRow.cells[j]);
         //if(child_el) {
         //	this.liveGrid.divAddStyle(child_el, htmlRow.cells[j].style.width);
         //	child_el.innerHTML = row[j];
         //} else {
         //	htmlRow.cells[j].innerHTML = row[j];
         //}
      }
   },
   populateRow: function(htmlRow, row) {
      for (var j=0; j < row.length; j++) {
         htmlRow.cells[j].innerHTML = row[j]
      }
   },
*/
	bufferChanged: function() {
		//修正
		var offset = Math.round(this.lastPixelOffset / this.rowHeight);
        if(offset +  this.visibleRows > this.liveGrid.metaData.getTotalRows()) {
            offset = this.liveGrid.metaData.getTotalRows() - this.visibleRows;
        }
		this.refreshContents(offset);
	},
   
	clearRows: function() {
		//消さないで残しておく
		/*
		if (!this.isBlank) {
			//this.liveGrid.table.className = this.liveGrid.options.loadingClass;
			//for (var i=0; i < this.visibleRows; i++)
			//   this.populateRow(this.table.rows[i], this.buffer.getBlankRow());
			//追加
			var row_len = this.table.rows.length;
			var remove_arr = new Array();
			for (var i=0; i < row_len; i++) {
				remove_arr[i] = this.table.rows[i];
			}
			for (var i=0; i < row_len; i++) {
				Element.remove(remove_arr[i]);
			}
			// 追加end 
			this.isBlank = true;
		}
		*/
		//if (!this.isBlank) {
			// class属性がbufferのcloneのclass定義にないものがあれば削除する
			for (var i = 0,child_len = this.liveGrid.table.rows.length; i < child_len; i++) {
				var row_el = this.liveGrid.table.rows[i];
				if(row_el) {
					if(Element.hasClassName(row_el,"display-none")) {
						row_el.className = this.liveGrid.buffer.clone_row.className + " display-none";
					} else {
						row_el.className = this.liveGrid.buffer.clone_row.className;
					}
					for (var j = 0,cell_len=row_el.childNodes.length; j < cell_len; j++) {
						var cell_el = row_el.childNodes[j];
						var grid_cell_el = this.liveGrid.buffer.clone_row.childNodes[j];
						cell_el.className = grid_cell_el.className;
						var child_el = Element.getChildElement(cell_el)
						if(child_el) child_el.innerHTML = "";
					}
				}
			}
			//this.liveGrid.buffer.value_el_rows = new Array();
			//this.liveGrid.buffer.key_el_rows = new Array();
			this.liveGrid.buffer.rows = new Array();
		//}
	},
   
	clearContents: function() {
		this.clearRows();
		this.isBlank = true;
		this.scrollTo(0);
		this.startPos = 0;
		this.lastStartPos = 0;
		//this.lastStartPos = -1;   
	},
 	refreshContents: function(startPos) {
		if (startPos == this.lastStartPos && !this.isPartialBlank && !this.isBlank) {
			return;
		}
		//if ((startPos + this.visibleRows < this.buffer.startPos)  
		//    || (this.buffer.startPos + this.buffer.size < startPos) 
		//    || (this.buffer.size == 0)) {
		//   this.clearRows();
		//   return;
		//}
		//this.isBlank = false;
		//var viewPrecedesBuffer = this.buffer.startPos > startPos
		//var contentStartPos = viewPrecedesBuffer ? this.buffer.startPos: startPos; 
		var contentStartPos = startPos;
		var contentEndPos = startPos + this.visibleRows;
		//var contentEndPos = (this.buffer.startPos + this.buffer.size < startPos + this.visibleRows) 
		//                           ? this.buffer.startPos + this.buffer.size
		//                           : startPos + this.visibleRows;
		var rowSize = contentEndPos - contentStartPos;
		
		//var rows = this.buffer.getRows(contentStartPos, rowSize ); 
		var blankSize = this.visibleRows - rowSize;
		//var blankOffset = viewPrecedesBuffer ? 0: rowSize;
		//var contentOffset = viewPrecedesBuffer ? blankSize: 0;
		
		//for (var i=0; i < rows.length; i++) {//initialize what we have
		//  this.populateRow(this.table.rows[i + contentOffset], rows[i]);
		//}
		//for (var i=0; i < blankSize; i++) {// blank out the rest 
		//  this.populateRow(this.table.rows[i + blankOffset], this.buffer.getBlankRow());
		//}
		//
		// 非表示にする
		//
		var lastRowPosLen = this.lastStartPos + this.visibleRows;
		for (var i=this.lastStartPos; i < lastRowPosLen; i++) {
			Element.addClassName(this.buffer.value_el_rows[i],"display-none");
		}
		
		//
		// 表示する
		//
		//startPos + this.visibleRows
		var fetchSize = this.buffer.maxFetchSize;
		var lastRowPosLen = startPos + fetchSize;
		if(lastRowPosLen > this.liveGrid.metaData.getTotalRows()) {
			lastRowPosLen = this.liveGrid.metaData.getTotalRows();
		}
		var insert_row = -1;
		for (var i=startPos; i < lastRowPosLen; i++) {
			var insert_flag = false;
			if(this.buffer.value_el_rows[i]) {
				var table_row_el = this.buffer.value_el_rows[i];
			} else {
				if(this.table.rows[i]) {
					// 入れようとしているrowが既にある
					if(this.isBlank == false && this.buffer.key_el_rows[this.table.rows[i]] != i) {
						// 新規取得
						insert_flag = true;
					} else {
						// 既に取得済み
						var table_row_el = this.table.rows[i];
					}
				} else {
					insert_flag = true;
				}
				if(insert_flag) {
					if(insert_row == -1) {
						var rows_len = this.table.rows.length;
						
						if(rows_len < i) {
							var insert_row = rows_len;
						} else {
							var insert_row = i;
						}
						while (this.buffer.key_el_rows[this.table.rows[insert_row - 1]] > i) {
				      		insert_row--;
				      		if(insert_row == 0) {
				      			break;
				      		}
				  		}
				  	}
					var table_row_el = this.table.insertRow(insert_row);
					insert_row++;
					// tdエレメントがなければ作成
					//if(browser.isIE) {
					//	for (var j = 0,cells_len = this.buffer.clone_row.childNodes.length; j < cells_len; j++) {
					//		var new_td = document.createElement("td");
					//		new_td.className = this.buffer.clone_row.childNodes[j].className;
					//		new_td.innerHTML = this.buffer.clone_row.childNodes[j].innerHTML;
					//		table_row_el.appendChild(new_td);
					//		//var insert_td = table_row_el.insertCell(j);
					//		//insert_td.className = this.buffer.clone_row.childNodes[j].className;
					//		//insert_td.innerHTML = this.buffer.clone_row.childNodes[j].innerHTML;
					//	}
					//} else {
						for (var j = 0,cells_len = this.buffer.clone_row.childNodes.length; j < cells_len; j++) {
							var insert_td = table_row_el.insertCell(j);
							insert_td.className = this.buffer.clone_row.childNodes[j].className;
							insert_td.innerHTML = this.buffer.clone_row.childNodes[j].innerHTML;
						}
					//}
				}
				this.buffer.value_el_rows[i] = table_row_el;
				this.buffer.key_el_rows[table_row_el] = i;
			}
			if(table_row_el.className == "") {
				table_row_el.className = this.buffer.clone_row.className;
			}
			if(i < startPos + this.visibleRows) {
				Element.removeClassName(table_row_el,"display-none");
			} else {
				Element.addClassName(table_row_el,"display-none");
			}
      }
      
      this.isBlank = false;   
      //var row_len = this.table.rows.length;
      //for (var i=0; i < row_len; i++) {
      //    var now_row = this.table.rows[i];
      //    if(i >= contentStartPos && i < contentEndPos) {
      //        //表示範囲
      //        //if(Element.hasClassName(now_row,"display-none")) {
      //    	     Element.removeClassName(now_row,"display-none");
      //    	  //}
      //    	  ////this.liveGrid.initRow(now_row);
      //    } else {
      //    	 //if(!Element.hasClassName(now_row,"display-none")) {
      //    	     Element.addClassName(now_row,"display-none");
      //    	 //}
      //    }
      //}
      this.isPartialBlank = blankSize > 0;
      this.lastStartPos = startPos;

       //this.liveGrid.table.className = this.liveGrid.options.tableClass;
       Element.addClassName(this.liveGrid.table,this.liveGrid.options.tableClass);
       
       // Check if user has set a onRefreshComplete function
       var onRefreshComplete = this.liveGrid.options.onRefreshComplete;
       if (onRefreshComplete != null)
           onRefreshComplete();  
   },

   scrollTo: function(pixelOffset) {      
      if (this.lastPixelOffset == pixelOffset)
         return;
      //修正
      var offset = Math.round(pixelOffset / this.rowHeight);
      if(offset +  this.visibleRows > this.liveGrid.metaData.getTotalRows()) {
          offset = this.liveGrid.metaData.getTotalRows() - this.visibleRows;
      }

      this.refreshContents(offset);
      this.div.scrollTop = pixelOffset % this.rowHeight;
      
      this.lastPixelOffset = pixelOffset;
   },
   
	visibleHeight: function() {
		return parseInt(Element.getStyle(this.div, 'height'));
		//return parseInt(RicoUtil.getElementsComputedStyle(this.div, 'height'));
	}
};

/*
compLiveGrid.LiveGridRequest = Class.create();
compLiveGrid.LiveGridRequest.prototype = {
	initialize: function( requestOffset, options ) {
		this.requestOffset = requestOffset;
	}
};
*/

//-------------------- ricoLiveGridSort.js
compLiveGrid.LiveGridSort = Class.create();

compLiveGrid.LiveGridSort.prototype = {
	//headerTable追加
   initialize: function(headerTableId, headerTable, options) {
      this.headerTableId = headerTableId;
      this.headerTable   = headerTable;
      //this.headerTable   = $(headerTableId);
      this.options = options;
      this.setOptions();
      this.applySortBehavior();

      if ( this.options.sortCol ) {
         this.setSortUI( this.options.sortCol, this.options.sortDir );
      }
   },

   setSortUI: function( columnName, sortDirection ) {
      var cols = this.options.columns;
      for ( var i = 0 ; i < cols.length ; i++ ) {
         if ( cols[i].name == columnName ) {
            this.setColumnSort(i, sortDirection);
            break;
         }
      }
   },

   setOptions: function() {
      // preload the images...
      new Image().src = this.options.sortAscendImg;
      new Image().src = this.options.sortDescendImg;

      this.sort = this.options.sortHandler;
      if ( !this.options.columns )
         this.options.columns = this.introspectForColumnInfo();
      else {
         // allow client to pass { columns: [ ["a", true], ["b", false] ] }
         // and convert to an array of compLiveGrid.TableColumn objs...
         this.options.columns = this.convertToTableColumns(this.options.columns);
      }
   },

   applySortBehavior: function() {
      //2行以上あるtable_headerに対応
      var tdList = this.headerTable.getElementsByTagName("th");
      if(tdList.length == 0) {
          tdList = this.headerTable.getElementsByTagName("td");
      }
      for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
          //var child_el = Element.getChildElement(tdList[i]);
	      //if(child_el) {
	      //    this.addSortBehaviorToColumn( i, child_el );
	      //} else {
              this.addSortBehaviorToColumn( i, tdList[i] );
          //}
      }
      //var headerRow   = this.headerTable.rows[0];
      //var headerCells = headerRow.cells;
      //for ( var i = 0 ; i < headerCells.length ; i++ ) {
      //   this.addSortBehaviorToColumn( i, headerCells[i] );
      //}
   },

   addSortBehaviorToColumn: function( n, cell ) {
      if ( this.options.columns[n].isSortable() ) {
         cell.id            = this.headerTableId + '_' + n;
         cell.style.cursor  = 'pointer';
         cell.onclick       = this.headerCellClicked.bindAsEventListener(this);
         Element.addClassName(cell, "grid_sort");
         var child_el = Element.getChildElement(cell);
         if(child_el) {
         	 child_el.innerHTML     = child_el.innerHTML + '<span class="' + this.headerTableId + '_img_' + n + '">'
	                           + '&nbsp;&nbsp;&nbsp;</span>';
         } else {
	         cell.innerHTML     = cell.innerHTML + '<span class="' + this.headerTableId + '_img_' + n + '">'
	                           + '&nbsp;&nbsp;&nbsp;</span>';
	     }
      }
   },

   // event handler....
   headerCellClicked: function(evt) {
      var eventTarget = evt.target ? evt.target : evt.srcElement;
      //tdまで遡る
      while (eventTarget.tagName.toLowerCase() != "td" && eventTarget.tagName.toLowerCase() != "th") {
          eventTarget = eventTarget.parentNode;
      }
      
      //var columnNumber = 0;
      //var tdList = this.headerTable.getElementsByTagName("td");
      //for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
      //    if(tdList[i].className == eventTarget.className) {
      //         columnNumber = i;
      //    }
      //}
      var cellId = eventTarget.id;
      var columnNumber = parseInt(cellId.substring( cellId.lastIndexOf('_') + 1 ));
      var sortedColumnIndex = this.getSortedColumnIndex();
      if ( sortedColumnIndex != -1 ) {
         if ( sortedColumnIndex != columnNumber ) {
            this.removeColumnSort(sortedColumnIndex);
            this.setColumnSort(columnNumber, compLiveGrid.TableColumn.SORT_ASC);
         } else {
            this.toggleColumnSort(sortedColumnIndex);
         }
      } else {
         this.setColumnSort(columnNumber, compLiveGrid.TableColumn.SORT_ASC);
      }
      if (this.options.sortHandler) {
         this.options.sortHandler(this.options.columns[columnNumber]);
      }
   },

   removeColumnSort: function(n) {
      this.options.columns[n].setUnsorted();
      this.setSortImage(n);
   },

   setColumnSort: function(n, direction) {
   	if(isNaN(n)) return ;
      this.options.columns[n].setSorted(direction);
      this.setSortImage(n);
   },

   toggleColumnSort: function(n) {
      this.options.columns[n].toggleSort();
      this.setSortImage(n);
   },

   setSortImage: function(n) {
      var sortDirection = this.options.columns[n].getSortDirection();
      var sortImageSpan = Element.getChildElementByClassName(this.headerTable, this.headerTableId + '_img_' + n);
      //var sortImageSpan = $( this.headerTableId + '_img_' + n );
      if ( sortDirection == compLiveGrid.TableColumn.UNSORTED )
         sortImageSpan.innerHTML = '&nbsp;&nbsp;';
      else if ( sortDirection == compLiveGrid.TableColumn.SORT_ASC )
         sortImageSpan.innerHTML = '&nbsp;&nbsp;<img width="'  + this.options.sortImageWidth    + '" ' +
                                                     'height="'+ this.options.sortImageHeight   + '" ' +
                                                     'src="'   + this.options.sortAscendImg + '"/>';
      else if ( sortDirection == compLiveGrid.TableColumn.SORT_DESC )
         sortImageSpan.innerHTML = '&nbsp;&nbsp;<img width="'  + this.options.sortImageWidth    + '" ' +
                                                     'height="'+ this.options.sortImageHeight   + '" ' +
                                                     'src="'   + this.options.sortDescendImg + '"/>';
   },

   getSortedColumnIndex: function() {
      var cols = this.options.columns;
      for ( var i = 0 ; i < cols.length ; i++ ) {
         if ( cols[i].isSorted() )
            return i;
      }

      return -1;
   },

   introspectForColumnInfo: function() {
      var columns = new Array();
      //2行以上あるtable_headerに対応
      var tdList = this.headerTable.getElementsByTagName("th");
      if(tdList.length == 0) {
          tdList = this.headerTable.getElementsByTagName("td");
      }
      for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
          cellContent = this.deriveColumnNameFromCell(tdList[i]);
          //cellContent = this.deriveColumnNameFromCell(tdList[i],i);
          if(cellContent) {
            columns.push( new compLiveGrid.TableColumn( cellContent, true ) );
          } else {
            columns.push( new compLiveGrid.TableColumn( cellContent, false ) );
          }
      }
      
      //var columns = new Array();
      //var headerRow   = this.headerTable.rows[0];
      //var headerCells = headerRow.cells;
      //for ( var i = 0 ; i < headerCells.length ; i++ )
      //   columns.push( new compLiveGrid.TableColumn( this.deriveColumnNameFromCell(headerCells[i],i), true ) );
      return columns;
   },

   convertToTableColumns: function(cols) {
      var columns = new Array();
      for ( var i = 0 ; i < cols.length ; i++ )
         columns.push( new compLiveGrid.TableColumn( cols[i][0], cols[i][1] ) );
      return columns;
   },

   //deriveColumnNameFromCell: function(cell,columnNumber) {
	deriveColumnNameFromCell: function(cell) {
		//cell.classNameの先頭のclass_nameのsort_prefixを取り除いたもの
		//sort_prefixがついてないものは、sort対象にしない
		var className = cell.className.split(" ")[0];
		var re_cut = new RegExp("^" + this.options.sort_prefix, "i");
		if(className.match(re_cut)) {
			var name = className.replace(re_cut,"");
			if(name != "") {
				return name.toLowerCase();
			}
		}
		return null;
		//cell.idがあれば、id　なければ内部のテキスト
		//var cellContent = cell.id != undefined ? cell.id : (cell.innerText != undefined ? cell.innerText : cell.textContent);
		//return cellContent ? cellContent.toLowerCase().split(' ').join('_') : "col_" + columnNumber;
	}
};

compLiveGrid.TableColumn = Class.create();

compLiveGrid.TableColumn.UNSORTED  = 0;
compLiveGrid.TableColumn.SORT_ASC  = "ASC";
compLiveGrid.TableColumn.SORT_DESC = "DESC";

compLiveGrid.TableColumn.prototype = {
   initialize: function(name, sortable) {
      this.name        = name;
      this.sortable    = sortable;
      this.currentSort = compLiveGrid.TableColumn.UNSORTED;
   },

   isSortable: function() {
      return this.sortable;
   },

   isSorted: function() {
      return this.currentSort != compLiveGrid.TableColumn.UNSORTED;
   },

   getSortDirection: function() {
      return this.currentSort;
   },

   toggleSort: function() {
      if ( this.currentSort == compLiveGrid.TableColumn.UNSORTED || this.currentSort == compLiveGrid.TableColumn.SORT_DESC )
         this.currentSort = compLiveGrid.TableColumn.SORT_ASC;
      else if ( this.currentSort == compLiveGrid.TableColumn.SORT_ASC )
         this.currentSort = compLiveGrid.TableColumn.SORT_DESC;
   },

   setUnsorted: function(direction) {
      this.setSorted(compLiveGrid.TableColumn.UNSORTED);
   },

   setSorted: function(direction) {
      // direction must by one of compLiveGrid.TableColumn.UNSORTED, .SORT_ASC, or .SORT_DESC...
      this.currentSort = direction;
   }

};