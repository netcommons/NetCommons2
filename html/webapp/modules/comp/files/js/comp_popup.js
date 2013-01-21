compPopup = Class.create();

compPopup.prototype = {

	initialize: function(id, popupID) {
		this.id = id;

		this.IDPrefix = "popup";
		this.popupID = (popupID) ? this.IDPrefix + popupID : this.IDPrefix;
		this.IDNone = false;
		this.src = null;
		this.popupElement = null;
		if(this.popupID) {
			var popupElement = $(this.popupID);
			if(popupElement && popupElement.contentWindow) {
				// 初期化
				//Element.remove(popupElement);
				this.popupElement = popupElement;
			}
		}

		this.classNames = new Array("popupIframe");
		this.iframeAttributes = {
			marginHeight:"0px",
			marginWidth:"0px",
			frameBorder:"0",
			scrolling:"no"
		};

		this.posCenter = false;					//true or false(posisionにかかわらず中心に表示)
		this.position = new Array(null, null);
		this.topOverlap = 3;

		this.cssFiles = new Array();
		this.jsFiles = new Array();	// No Use

		this.observing = true;
		this.observer = this._closePopupObserver.bindAsEventListener(this);

		this.loadObserver = null;	//loadイベント登録用パラメータ

		this.head_title = "Popup Dialog";
		this.popup_width = 0;
		this.popup_height = 0;

		this.modal = true;
		//this.inModalElement = null;

		this.allowTransparency = false;

		this._loadEventFunc = null;

		this.showPopupFlag = false;
	},
	showPopup: function(targetElement, eventElement) {
		if(commonCls.referComp["comp_popup"+this.id + this.popupID] == true) {
			return;
		}
		commonCls.referComp["comp_popup"+this.id + this.popupID] = true;
		//this.showPopupFlag = true;

		if(targetElement && typeof targetElement != 'string') {
			targetElement.style.display = (!(browser.isIE && browser.version < 9) && targetElement.tagName == "TABLE") ? "table" : "block";
		}
		if (!this.popupElement) {
			this.createPopup();
		} else {
			var visible_flag = true;
			if(this.src != null) {
				if(this.src != this.popupElement.src) {
					this.popupElement.src = this.src;
					this.popupElement.contentWindow.document.body.innerHTML = "";
					this.popupElement.contentWindow.location.reload();
					this.popupElement.style.visibility = "visible";
				} else {
					this.popupElement.style.visibility = "visible";
				}
			}
		}
		if(browser.isGecko || (browser.isIE && browser.version >= 9)) {
			if(this.src != null && this.src != this.popupElement.src) this.popupElement.style.visibility = "hidden";
			this.popupElement.onload = function() {
				this.popupElement.style.visibility = "visible";
				setTimeout(function() {
					this.resize();
					if(this.posCenter) {
						commonCls.popup.setPosition(commonCls.getCenterPosition(this.popupElement));
					}
				}.bind(this), 300);
			}.bind(this);
		}
		if(browser.isSafari) {
			this.popupElement.style.visibility = "visible";
			setTimeout(function() {
				this.resize();
				if(this.posCenter) {
					commonCls.popup.setPosition(commonCls.getCenterPosition(this.popupElement));
				}
			}.bind(this), 300);
		}

		if(this.posCenter) {
		} else if (eventElement) {
			this.setEventPosition(eventElement);
		} else {
			this.setPosition(this.position);
		}
		//window.onload時のfocus移動に対応するため、ここで表示
		if (visible_flag && (browser.isIE && browser.version < 9)) {
			//this.popupElement.contentWindow.document.open();
			//this.popupElement.contentWindow.document.write("");
			//this.popupElement.contentWindow.document.close();
			this.popupElement.style.display = (!(browser.isIE && browser.version < 9) && this.popupElement.tagName == "TABLE") ? "table" : "block";
			//if(browser.isIE) this.popupElement.style.display = "";
			//this.popupElement.style.display = "";
			this.popupElement.style.visibility = "visible";
		}
		if(this.src == null) {
			this.setHTMLText(targetElement);
		}
		var div = $("_global_modal_dialog");
		if(this.modal && !div) {
			div = document.createElement("DIV");
			div.id = "_global_modal_dialog";
			if(this.id) {
				var top_el = $(this.id);
			}
			if(top_el) {
				if(top_el.tagName == "TABLE") {
					//td
					Element.getChildElement(top_el, 3).appendChild(div);
				} else {
					top_el.appendChild(div);
				}
			} else {
				document.body.appendChild(div);
			}
			//document.body.appendChild(div);
			commonCls.showModal(null, div);
			////this.inModalElement = div;
		}
		if(this.observing) {
			//this.observer = this._closePopupObserver.bindAsEventListener(this);
			//Event.observe(document, "mousedown", this.observer, false);
			if(this.modal) {
				Event.observe(div, "mousedown", this.observer, false, $(this.id));
			} else {
				Event.observe(document, "mousedown", this.observer, false, $(this.id));
			}
		}
		commonCls.max_zIndex = commonCls.max_zIndex + 1;
		this.popupElement.style.zIndex = commonCls.max_zIndex;
		commonCls.referComp["comp_popup"+this.id + this.popupID] = false;
	},

	/* Src指定によるPopup */
	showSrcPopup: function(src, eventElement) {
		this.src = src;
		this.showPopup(null, eventElement);
	},

	createPopup: function() {
		if (!this.IDNone) {
			var popupElement = $(this.popupID);
			if (popupElement) {
				this.popupElement = popupElement;
				return;
			}
		}

		this.popupElement = document.createElement("iframe");
		if((browser.isIE && browser.version < 9 && browser.version >= 7) || this.allowTransparency)this.popupElement.allowTransparency="true";

		if (!this.IDNone) {
			this.popupElement.id = this.popupID;
		}

		for (var i = 0; i < this.classNames.length; i++) {
			Element.addClassName(this.popupElement, this.classNames[i]);
		}

		for (var i in this.iframeAttributes) {
			this.popupElement[i] = this.iframeAttributes[i];
		}
		if(this.id) {
			var top_el = $(this.id);
		}
		if(top_el) {
			if(top_el.tagName == "TABLE") {
				//td
				Element.getChildElement(top_el, 3).appendChild(this.popupElement);
			} else {
				top_el.appendChild(this.popupElement);
			}
		} else {
			document.body.appendChild(this.popupElement);
		}
		if(this.src != null) this.popupElement.src = this.src;
	},

	setHTMLText: function(targetElement) {
		var html = document.getElementsByTagName("html")[0];
		var htmlAttr = "xmlns=\"" + html.getAttribute("xmlns") + "\" ";
		htmlAttr += "xml:lang=\"" + html.getAttribute("xml:lang") + "\" ";
		htmlAttr += "lang=\"" + html.getAttribute("lang") + "\"";

		var head = document.getElementsByTagName("head")[0];
		var links = head.getElementsByTagName("link");
		var titleText = "";
		var linkText = "";
		if(this.head_title != "") {
			titleText = "<title>" + this.head_title + "</title>\n"
		}
	    for (var i = 0; i < links.length; i++) {
			var link = links[i];
			if (link.getAttribute("rel") == "stylesheet") {
				linkText += "<link ";
				linkText += "rel=\"" + link.getAttribute("rel") + "\" ";
				linkText += "type=\"" + "text/css" + "\" ";
				if(link.getAttribute("media")) {
					linkText += "media=\"" + link.getAttribute("media") + "\" ";
				} else {
					linkText += "media=\"screen\" ";
				}
				linkText += "href=\"" + link.getAttribute("href") + "\" ";
				linkText += "/>\n";
			}
		}
		for (var i = 0; i < this.cssFiles.length; i++) {
			var link = this.cssFiles[i];
			linkText += "<link ";
			linkText += "rel=\"stylesheet\" ";
			linkText += "type=\"text/css\" ";
			linkText += "media=\"" + link[1] + "\" ";
			linkText += "href=\"" + link[0] + "\" ";
			linkText += "/>\n";
		}


		// --------------------------------
		// --- ポップアップ内容設定処理 ---
		// --------------------------------
		// IEの場合はフレーム間でのエレメント移動ができないためouterHTMLで内容を取得
		var text = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
		text += "<html " + htmlAttr + ">\n";
		text += "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">" + titleText + linkText + "</head>\n";
		text += "<body style=\"background-color:transparent;\">\n";
		if (typeof targetElement == 'string') {
			text += targetElement + "\n";
		//} else if (browser.isIE) {
		//	text += targetElement.outerHTML + "\n";
		} else {
			//jsファイル以外を読み込む
			//親のjsファイルを実行したい場合、parent指定にすること
			if(targetElement.nodeName.toLowerCase() != "script") {
				text += "<" + targetElement.nodeName;

			    for (var i = 0; i < targetElement.attributes.length; i++) {
					var attribute = targetElement.attributes[i];
					if (attribute.nodeName != "style") {
						var value = attribute.nodeValue;
					} else { // IE fails to put style in attributes list
						// FIXME: cssText reported by IE is UPPERCASE
						var value = targetElement.style.cssText;
					}
					if((browser.isIE && browser.version < 9)) {
						if(attribute.nodeName == "_extended" || value == null || value == "" || typeof value=="function") {
						    //変数汚染されるため追加
							continue;
						}
					}
					text += " " + attribute.name + "=\"" + value + "\"";
				}
				text += ">\n";
				text += targetElement.innerHTML.replace(/[\r\n\t]/g, "");	//offsetWidthプロパティに影響する場合があるためTAB、改行コードを消去
				text += "</" + targetElement.nodeName + ">\n";
			}
		}
		text += "</body>\n";
		text += "</html>";
		text += "<script>";
		text += "function popup_resize(){";
		text += "var targetElement = document.body.firstChild;";
		text += "while (targetElement.nodeType != 1) {";
		text += "targetElement = targetElement.nextSibling;";
		text += "}";
		//text += "window.frameElement.style.display = 'block';";
		text += "targetElement.style.display=(!((parent.browser.isIE && parent.browser.version < 9)) && targetElement.tagName=='TABLE')?'table':'block';";
		text += "window.frameElement.width = targetElement.offsetWidth + 'px';";
		text += "window.frameElement.height = targetElement.offsetHeight + 'px';";
		text += "targetElement.style.visibility = 'visible';";
		text += "window.frameElement.style.visibility = 'visible';";
		//text += "if(parent.Element.hasClassName(window.frameElement, 'visible-hide')) {";
		//text += "parent.Element.removeClassName(window.frameElement, 'visible-hide');";
		//text += "}";
		text += "}";
		text += "setTimeout(popup_resize, 300);";	//タイマー0だと、一度目の表示がくずれる場合があるため修正
		text += "</script>";
		this.popupElement.contentWindow.document.open();
		this.popupElement.contentWindow.document.write(text);
		this.popupElement.contentWindow.document.close();
		//if(typeof this.loadObserver != 'undefined' && typeof this.loadObserver != 'null') {
			this._loadEventFunc = this.loadEvent.bindAsEventListener(this);
			if(browser.isIE || browser.isSafari) {
				setTimeout(this._loadEventFunc, 500);
			} else {
				Event.observe(this.popupElement,"load",this._loadEventFunc, false, this.id);
			}
		//}
	},
	//
	// loadイベント
	//
	loadEvent: function(event){
		//if(!browser.isGecko && !browser.isSafari) {
		//	Event.stopObserving(this.popupElement,"load", this._loadEventFunc, false);
		//}
		if((browser.isIE && browser.version < 9) || browser.isSafari) {
			//Safariの場合、iframe-Loadイベントが動かないため
			try{
				var tmp = this.popupElement.contentWindow.document;
				if(tmp == undefined || tmp == null) {
					setTimeout(this._loadEventFunc, 500);
					return;
				}
			} catch(e) {
				setTimeout(this._loadEventFunc, 500);
				return;
			}
		}
		if(!this.popupElement.contentWindow || !this.popupElement.contentWindow.document ||
			this.popupElement.contentWindow.document.body.innerHTML.strip() == "") {
			setTimeout(this._loadEventFunc, 500);
			return;
		}
		//
		//ロードイベント
		//
		if(typeof this.loadObserver == 'function') {
			this.loadObserver(event);
		}
		//if(this.loadObserver) this.loadObserver(event);
		Event.stopObserving(this.popupElement,"load", this._loadEventFunc, false);
	},
	stopLoadEvent: function(event){
		if(this.loadObserver && !((browser.isIE && browser.version < 9) || browser.isSafari)) {
			Event.stopObserving(this.popupElement,"load", this._loadEventFunc, false);
		}
		this.loadObserver = null;
	},
	_closePopupObserver: function(event){
		this.closePopupAll(event);
	},
	closePopup: function(iframe){
		iframe = (iframe == undefined || iframe == null) ? this.popupElement : iframe;
		if (iframe) {
			var div = $("_global_modal_dialog");
			if(div) {
				try{
					commonCls.stopModal(div);
				}catch(e){}
				Element.remove(div);
				//this.inModalElement = null;
			}
			if((browser.isIE && browser.version < 9)) iframe.style.display = "none";	//safariの場合、再描画する時に描画処理がはしってしまうためコメント
			iframe.style.visibility = "hidden";
			if(browser.isOpera) {
				$(this.popupElement).remove();
				this.src = null;
				this.popupElement = null;
				commonCls.referComp["comp_popup"+this.id + this.popupID] = false;
			//	var div = $("_global_modal_dialog");
			}
		}
		if(this.observer != null && !(browser.isIE && browser.version >= 9)) {
			if(this.modal) {
				Event.stopObserving(div, "mousedown", this.observer, false);
			} else {
				Event.stopObserving(document, "mousedown", this.observer, false);
			}
			//this.observer = null;
		}
	},

	closePopupAll: function(event){
		var iframes = document.body.getElementsByTagName("iframe");
		for (var i = 0; i < iframes.length; i++) {
			//if(iframes[i].id == this.popupID && !Element.hasClassName(iframes[i], "visible-hide")) {
			if (iframes[i].id.substr(0, this.IDPrefix.length) == this.IDPrefix && !Element.hasClassName(iframes[i], "visible-hide")) {
				this.closePopup(iframes[i]);	//iframes[i].style.visibility = "hidden";
				//Event.stop(event);
			}
		}
	},

	resize: function() {
		if (!this.popupElement) {
			return;
		}
		//if(typeof this.popupElement.contentWindow.document != 'object') {
		//	setTimeout(function() {this.resize();}.bind(this), 300);
		//	return;
		//}
		try{
			var targetElement = this.popupElement.contentWindow.document.body.firstChild;
			if(!targetElement) {
				return;
			}
		} catch(e) {
			setTimeout(function() {this.resize();}.bind(this), 300);
			return;
		}

		while (targetElement.nodeType != 1) {
			targetElement = targetElement.nextSibling;
		}
		this.popupElement.width = targetElement.offsetWidth + 'px';
		this.popupElement.height = targetElement.offsetHeight + 'px';

		var position = new Array();
		var popupX2 = this.popupElement.offsetLeft + this.popupElement.offsetWidth;
		var bodyX2 = Position.getWinOuterWidth() + document.documentElement.scrollLeft;
		if (popupX2 > bodyX2) {
			position[0] = this.popupElement.offsetLeft - (popupX2 - bodyX2);
			if (position[0] < 0) {
				position[0] = 0;
			}
			position[1] = this.popupElement.offsetTop;
			this.setPosition(position);
		}
	},

	addClassNames: function(className) {
		if (this.popupElement) {
			Element.addClassName(this.popupElement, className);
		}
		this.classNames.push(className);
	},

	addCSSFiles: function(cssFile, media) {
		if (!media) {
			media = "all";
		}
		this.cssFiles.push(new Array(cssFile, media));
	},

	setIframeAttributes: function(iframeAttributes) {
		if (this.popupElement) {
			for (var i in iframeAttributes) {
				this.popupElement[i] = iframeAttributes[i];
			}
		}
		Object.extend(this.iframeAttributes, iframeAttributes || {});
	},

	setIDNone: function() {
		if (this.popupElement) {
			this.popupElement.id = null;
		}
		this.IDNone = true;
	},

	setObserving: function(value) {
		this.observing = value;
	},

	setPosition: function(position) {
		if (this.popupElement) {
			this.popupElement.style.left = position[0] + "px";
			this.popupElement.style.top = position[1] + "px";
		}
		this.position = position;
	},

	setEventPosition: function(eventElement) {
		if(eventElement.tagName == "INPUT" && eventElement.type == "text") {
			//inputタグ(Text)の場合、正常に位置を取得できないため修正
			var offset = Position.cumulativeOffsetScroll(eventElement);
		} else {
			var offset = Position.positionedOffsetScroll(eventElement);
		}
		var position = new Array();

		position[0] = offset[0];
		position[1] = offset[1] + eventElement.offsetHeight - this.topOverlap;
		this.setPosition(position);
	},

	setParentPosition: function(parentElement) {
		var offset = Position.cumulativeOffset(parentElement);
		this.setPosition(offset);
	},

	setLapPosition: function(eventElement, parentElement) {
		var offset = Position.cumulativeOffset(eventElement);
		var parentOffset = Position.cumulativeOffset(parentElement);
		var position = new Array();

		position[0] = offset[0] + parentOffset[0];
		position[1] = offset[1] + parentOffset[1] + eventElement.offsetHeight - this.topOverlap;
		this.setPosition(position);
	},

	setCenterPosition: function(popupElement, targetElement) {
		var target_el = $(popupElement);
		var center_position = commonCls.getCenterPosition(target_el,targetElement);
		this.setPosition(center_position);
	},

	getPopupElementByEvent: function(eventElement)	{
		eventElement = $(eventElement)
		targetElement = eventElement.nextSibling;
		while (targetElement.nodeType != 1 || !Element.hasClassName(targetElement, "popupClass")) {
			targetElement = targetElement.nextSibling;
		}
		return targetElement;
	},

	getPopupElement: function()	{
		return this.popupElement;
	},

	isVisible: function()	{
		if (this.popupElement && this.popupElement.style.visibility == "visible" && !Element.hasClassName(this.popupElement, 'visible-hide')) {
			return true;
		}
		return false;
	},
	setTitle: function(head_title_str)	{
		this.head_title = head_title_str;
	}
}