/*
  Based on Rico DragAndDrop v1.1.2(ricoDragAndDrop.js, ricoDraggable.js, ricoDropzone.js)
  (http://openrico.org/)
  http://www.apache.org/licenses/LICENSE-2.0

  変更履歴：
  ドロップ中でも元画像が残ることも可能なように修正
  ドロップ先に移動しなかった場合、元の場所にAppendChildするように修正
  ドラッグ中の色を指定なしに変更
  IEでのabsoluteの場合の位置の修正
  dragとdropのエレメントが等しい場合、ドロップ先にしないように修正
*/

//-------------------- ricoDragAndDrop.js
compDragAndDrop = Class.create();

compDragAndDrop.prototype = {

	initialize: function() {
		this.dropZones                = new Array();
		this.draggables               = new Array();
		this.currentDragObjects       = new Array();
		this.dragElement              = null;
		this.lastSelectedDraggable    = null;
		this.currentDragObjectVisible = false;
		this.interestedInMotionEvents = false;
		this._mouseDown = this._mouseDownHandler.bindAsEventListener(this);
		this._mouseMove = this._mouseMoveHandler.bindAsEventListener(this);
		this._mouseUp = this._mouseUpHandler.bindAsEventListener(this);
		//追加
		this.dragElementPosition = null;		//tr,tdドラッグ用
		this.add_absolute = false;
		this.dragParentElement = null;
		this.dummyElement = null;
		this.dragObjectTransparent = true;		//ドラッグ中のエレメントを残す場合、true
		this.draggableRangeElement = null;
		this.draggableRangeElementOffset = null;
		this.origPos = null;
		this.startx = null;
		this.starty = null;
		
		this.dragElementWidth = null;
		
		this.scrollMovePx = 30;
		
		//this.start_width = null;
		//this.start_height = null;
	},

	registerDropZone: function(aDropZone, dropObjectAppendChild) {
		//エレメントならば、オブジェクトに変換
		if (aDropZone.tagName != undefined) {
			aDropZone = new compDropzone(aDropZone,null, dropObjectAppendChild);
		}
		if (dropObjectAppendChild == true || dropObjectAppendChild == false) {
			aDropZone.setDropObjectAppendChild(dropObjectAppendChild);
		}
		this.dropZones[ this.dropZones.length ] = aDropZone;
	},

	deregisterDropZone: function(aDropZone) {
		var newDropZones = new Array();
		var j = 0;
		for ( var i = 0 ; i < this.dropZones.length ; i++ ) {
			if ( this.dropZones[i] != aDropZone )
				newDropZones[j++] = this.dropZones[i];
		}

		this.dropZones = newDropZones;
	},

	clearDropZones: function() {
		this.dropZones = new Array();
	},
	//ドラッグできる範囲指定 指定el内でしか移動できない
	//デフォルト：どこでも移動可能
	registerDraggableRange: function(el) {
		el = $(el);
		this.draggableRangeElement = el;
	},

	//el or compDraggable object, boolean dragObjectTransparent
	registerDraggable: function( aDraggable,  dragObjectTransparent) {
		if (dragObjectTransparent == true || dragObjectTransparent == false) {
			this.dragObjectTransparent = dragObjectTransparent;
		}

		//エレメントならば、オブジェクトに変換
		if (aDraggable.tagName != undefined) {
			aDraggable = new compDraggable(aDraggable);
		}
		this.draggables[ this.draggables.length ] = aDraggable;
		this._addMouseDownHandler( aDraggable );
	},
	clearSelection: function() {
		for ( var i = 0 ; i < this.currentDragObjects.length ; i++ ) {
			this.currentDragObjects[i].deselect();
		}
		this.currentDragObjects = new Array();
		this.lastSelectedDraggable = null;
	},
	hasSelection: function() {
		return this.currentDragObjects.length > 0;
	},
	setStartDragFromElement: function( e, mouseDownElement ) {
		var offset = Position.cumulativeOffsetScroll(mouseDownElement);
		this.origPos = new Object();
		this.draggableRangeElementOffset = new Object();
		this.origPos.x = offset[0];
		this.origPos.y = offset[1];
		if(Element.getStyle(mouseDownElement, "position") == "relative") {
			this.draggableRangeElementOffset.x = valueParseInt(Element.getStyle(mouseDownElement, "left"));
			this.draggableRangeElementOffset.y = valueParseInt(Element.getStyle(mouseDownElement, "top"))
			this.origPos.x -= this.draggableRangeElementOffset.x;
			this.origPos.y -= this.draggableRangeElementOffset.y;
		} else {
			this.draggableRangeElementOffset.x = 0;
			this.draggableRangeElementOffset.y = 0;
		}
		//this.origPos = compCommonUtil.toDocumentPosition(mouseDownElement);
		
		//this.startx = e.screenX - this.origPos.x;
		//this.starty = e.screenY - this.origPos.y;
		
		this.startx = Event.pointerX(e) - this.origPos.x;
		this.starty = Event.pointerY(e) - this.origPos.y;
		
		//this.startComponentX = e.layerX ? e.layerX : e.offsetX;
		//this.startComponentY = e.layerY ? e.layerY : e.offsetY;
		//this.adjustedForDraggableSize = false;
		this.interestedInMotionEvents = this.hasSelection();
		this._terminateEvent(e);
	},
	updateSelection: function( draggable, extendSelection ) {
		if ( ! extendSelection )
			this.clearSelection();

		if ( draggable.isSelected() ) {
			this.currentDragObjects.removeItem(draggable);
			draggable.deselect();
			if ( draggable == this.lastSelectedDraggable )
				this.lastSelectedDraggable = null;
		} else {
			this.currentDragObjects[ this.currentDragObjects.length ] = draggable;
			draggable.select();
			this.lastSelectedDraggable = draggable;
		}
	},

	_mouseDownHandler: function(e) {
		if ( arguments.length == 0 )
			e = event;

		// if not button 1 ignore it...
		var nsEvent = e.which != undefined;
		if ( (nsEvent && e.which != 1) || (!nsEvent && e.button != 1))
			return;

		var eventTarget      = Event.element(e);

		var draggableObject  = eventTarget.draggableObject;
		if(typeof draggableObject != 'object') draggableObject = null;
		var candidate = eventTarget;
		while (draggableObject == null && candidate.parentNode) {
			candidate = candidate.parentNode;
			draggableObject = candidate.draggableObject;
		}

		if ( typeof draggableObject != 'object' )
			return;

		//divタグの広さ指定
/*TODO:後に削除
		if ( draggableObject.length > 1 )
			var dragElement = draggableObject[0].getMultiObjectDragGUI(draggableObject);
		else
			var dragElement = draggableObject.getSingleObjectDragGUI();

		this.start_width = dragElement.style.width;
		this.start_height = dragElement.style.height;
		var length_flag = false
		var dragElementBuf = dragElement;
		while(true){
			if(dragElementBuf.offsetWidth > 0) {
				break;
			}
			dragElementBuf = Element.getChildElement(dragElementBuf);
			if(dragElementBuf == null) {
				break;
			}
		}
		dragElement.style.width = dragElementBuf.offsetWidth + "px";
		dragElement.style.height = dragElementBuf.offsetHeight + "px";
*/
		//広さ指定終了


		this.updateSelection( draggableObject, e.ctrlKey );

		// clear the drop zones postion cache...
		if ( this.hasSelection() ) {
			for ( var i = 0 ; i < this.dropZones.length ; i++ ) {
				this.dropZones[i].clearPositionCache();
			}
		}
		this.setStartDragFromElement( e, draggableObject.getMouseDownHTMLElement() );
	},
	_mouseMoveHandler: function(e) {
		var nsEvent = e.which != undefined;
		if ( !this.interestedInMotionEvents ) {
			//this._terminateEvent(e);
			return;
		}

		if ( ! this.hasSelection() )
			return;

		if ( ! this.currentDragObjectVisible ) {
			this._startDrag(e);
		}
		if ( !this.activatedDropZones )
			this._activateRegisteredDropZones();

		//if ( !this.adjustedForDraggableSize )
		//   this._adjustForDraggableSize(e);

		this._updateDraggableLocation(e);
		this._updateDropZonesHover(e);

		this._terminateEvent(e);
	},



	_makeDraggableObjectVisible: function(e)
	{
		if ( !this.hasSelection() )
			return;

		var dragElement;
		if ( this.currentDragObjects.length > 1 )
			dragElement = this.currentDragObjects[0].getMultiObjectDragGUI(this.currentDragObjects);
		else
			dragElement = this.currentDragObjects[0].getSingleObjectDragGUI();

		if(valueParseInt(dragElement.style.width) > 0) {
			//広さをbuffer
			this.dragElementWidth = dragElement.style.width;
		} else {
			this.dragElementWidth = 0;
		}
		dragElement.style.width = dragElement.offsetWidth + "px";

      // need to parent him into the document...
		//if ( dragElement.parentNode == null || dragElement.parentNode.nodeType == 11 )
		//	document.body.appendChild(dragElement);
		//親absoluteエレメントの場合もあるので、常にdocument.bodyに追加するように修正
		if(dragElement.tagName.toLowerCase() == 'tr') {
			var tag_kind = "tr";
		} else if(dragElement.tagName.toLowerCase() == 'td') {
			var tag_kind = "td";
		} else {
			var tag_kind = "other";
		}
		if(tag_kind == 'tr' || tag_kind == 'td') {
		    //tr or td タグ用に追加（IEでは、うまく動作しないため）
			var table = document.createElement("table");
			var parentTable = dragElement.parentNode;
			while(parentTable.tagName.toLowerCase() != "table") {
				parentTable = parentTable.parentNode;
				if(parentTable.tagName.toLowerCase() == "body") {
					break;
				}
			}
			table.className = parentTable.className;
			var append_el = document.createElement("tbody");
			table.appendChild(append_el);
			if(tag_kind == 'td') {
				var append_el = document.createElement("tr");
				append_el.className = dragElement.parentNode.className;
				table.appendChild(append_el);
				new Insertion.After(dragElement, "<td />");
			} else {
				new Insertion.After(dragElement, "<tr />");
			}
			this.dragParentElement = table;
		} else {
			var append_el = document.createElement("div");
			//hiddenタグ挿入
			new Insertion.After(dragElement, "<input type='hidden' value='' />");
			this.dragParentElement = append_el;
		}
		this.dummyElement = dragElement.nextSibling;
		//append_el.style.position = "absolute";
		commonCls.max_zIndex = commonCls.max_zIndex + 1;
		append_el.appendChild(dragElement);
		if(this.dragObjectTransparent) {
			//var dragHtml = append_el.innerHTML;
			if(browser.isIE || browser.isSafari) {
				var buf_el = append_el.cloneNode(true);
				var inputList = buf_el.getElementsByTagName("input");
				for (var i = 0; i < inputList.length; i++){
					var type = inputList[i].getAttribute("type");
					if(type == "text" || type == "hidden") {
						inputList[i].setAttribute("value",'',0);
					}
				}
				var dragHtml = buf_el.innerHTML;
				if(browser.isIE) Element.remove(buf_el);
				else Element.remove(buf_el.childNodes[0]);
				buf_el = null;
			} else {
				var dragHtml = append_el.innerHTML;
			}
			new Insertion.After(this.dummyElement, dragHtml);
			var bufEl = this.dummyElement.nextSibling;
			if(this.dragElementWidth == null || this.dragElementWidth == 0) {
				bufEl.style.width = '';
			}
			Element.addClassName(bufEl, "_draganddrop_transparent");
			Element.remove(this.dummyElement);
			this.dummyElement = bufEl;
		}
		if(tag_kind == 'tr' || tag_kind == 'td') {
			var dragElementPosition = table;
		} else {
			var dragElementPosition = dragElement.parentNode;
		}
		dragElementPosition.style.zIndex = commonCls.max_zIndex;
		// go ahead and absolute position it...
        this.add_absolute = false;
        if ( Element.getStyle(dragElementPosition, "position")  != "absolute" ) {
			dragElementPosition.style.position = "absolute";
			this.add_absolute = true;
		}
		if(tag_kind == 'tr' || tag_kind == 'td') {
			document.body.appendChild(table);
		} else {
			document.body.appendChild(append_el);
		}
		this.dragElement = dragElement;
		this.dragElementPosition = dragElementPosition;
		this._updateDraggableLocation(e);

		this.currentDragObjectVisible = true;
   },

   /**
   _adjustForDraggableSize: function(e) {
      var dragElementWidth  = this.dragElement.offsetWidth;
      var dragElementHeight = this.dragElement.offsetHeight;
      if ( this.startComponentX > dragElementWidth )
         this.startx -= this.startComponentX - dragElementWidth + 2;
      if ( e.offsetY ) {
         if ( this.startComponentY > dragElementHeight )
            this.starty -= this.startComponentY - dragElementHeight + 2;
      }
      this.adjustedForDraggableSize = true;
   },
   **/

   _leftOffset: function(e) {
	   return e.offsetX ? (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft) : 0
	},

   _topOffset: function(e) {
	   return e.offsetY ? (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) :0
	},


   _updateDraggableLocation: function(e) {
   		var dragObjectStyle = this.dragElementPosition.style;
   		//dragObjectStyle.left = (Event.pointerX(e) - this.startx) + "px";
		//dragObjectStyle.top  = (Event.pointerY(e) - this.starty) + "px";
   		
		if(this.draggableRangeElement != null) {
			var drop_offset = Position.cumulativeOffset(this.draggableRangeElement);
			drop_offset[0] -= this.draggableRangeElementOffset.x;
			drop_offset[1] -= this.draggableRangeElementOffset.y;
			
			var drop_el_left = drop_offset[0];
			var drop_el_right = drop_offset[0] + this.draggableRangeElement.offsetWidth;
			var drop_el_top = drop_offset[1];
			var drop_el_bottom = drop_offset[1] + this.draggableRangeElement.offsetHeight;

			var offset = Position.cumulativeOffset(this.dragElementPosition);
			var el_left = offset[0];
			var el_right = offset[0] + this.dragElementPosition.offsetWidth;
			var el_top = offset[1];
			var el_bottom = offset[1] + this.dragElementPosition.offsetHeight;
			
			var buf_el_left = (Event.pointerX(e) - this.startx);
			var buf_el_right = (Event.pointerX(e) - this.startx) + this.dragElementPosition.offsetWidth;
			var buf_el_top = (Event.pointerY(e) - this.starty);
			var buf_el_bottom = (Event.pointerY(e) - this.starty) + this.dragElementPosition.offsetHeight;

			if(buf_el_left < drop_el_left) {
				dragObjectStyle.left = valueParseInt(dragObjectStyle.left) + (drop_el_left - el_left) + "px";
			} else if(buf_el_right > drop_el_right){
				dragObjectStyle.left = valueParseInt(dragObjectStyle.left) + (drop_el_right - el_right) + "px";
			} else {
				dragObjectStyle.left = buf_el_left + "px";
			}

			if(buf_el_top < drop_el_top) {
				dragObjectStyle.top = valueParseInt(dragObjectStyle.top) + (drop_el_top - el_top) + "px";
			} else if(buf_el_bottom > drop_el_bottom){
				dragObjectStyle.top = valueParseInt(dragObjectStyle.top) + (drop_el_bottom - el_bottom) + "px";
			} else {
				dragObjectStyle.top  = buf_el_top + "px";
			}
		} else {
			dragObjectStyle.left = (Event.pointerX(e) - this.startx) + "px";
			dragObjectStyle.top  = (Event.pointerY(e) - this.starty) + "px";
		}
		
		// Scroll移動
		commonCls.scrollMoveDrag(e, this.scrollMovePx);
		
   },

   _updateDropZonesHover: function(e) {
      var n = this.dropZones.length;
      for ( var i = 0 ; i < n ; i++ ) {
         if ( ! this._mousePointInDropZone( e, this.dropZones[i] ) )
            this.dropZones[i].hideHover(e);
      }

      for ( var i = 0 ; i < n ; i++ ) {
         if ( this._mousePointInDropZone( e, this.dropZones[i] ) ) {
            if ( this.dropZones[i].canAccept(this.currentDragObjects) )
               this.dropZones[i].showHover(e);
         }
      }
   },
	_startDrag: function(e) {
		for ( var i = 0 ; i < this.currentDragObjects.length ; i++ ) {
			this.currentDragObjects[i].prestartDrag();
		}
		this._makeDraggableObjectVisible(e);
		for ( var i = 0 ; i < this.currentDragObjects.length ; i++ ) {
			this.currentDragObjects[i].startDrag();
		}
	},

	_mouseUpHandler: function(e) {
		Event.stopObserving(document, "mousemove", this._mouseMove);
		Event.stopObserving(document, "mouseup",  this._mouseUp);
		if ( ! this.hasSelection() ){
			return;
		}
		var nsEvent = e.which != undefined;
		if ( (nsEvent && e.which != 1) || (!nsEvent && e.button != 1)) {
			return;
		}
		this.interestedInMotionEvents = false;
		if ( this.dragElementPosition == null ) {
			this._terminateEvent(e);
			return;
		}

		if ( this._placeDraggableInDropZone(e) ) {
			this._completeDropOperation(e);
		} else {

			this._terminateEvent(e);
			new compCommonUtil.Effect.Position( this.dragElementPosition,
                              this._leftOffset(e) + this.origPos.x - valueParseInt(Element.getStyle(Element.getChildElement(this.dragElementPosition), "marginLeft")),
                              this._topOffset(e) + this.origPos.y - valueParseInt(Element.getStyle(Element.getChildElement(this.dragElementPosition), "marginTop")),
                              200,
                              20,
                              { complete : this._doCancelDragProcessing.bind(this) } );
		}
		//Event.stopObserving(document, "mousemove", this._mouseMove);
		//Event.stopObserving(document, "mouseup",  this._mouseUp);
	},

	_retTrue: function () {
		return true;
	},

	_completeDropOperation: function(e) {
		//if ( this.dragElement != this.currentDragObjects[0].getMouseDownHTMLElement() ) {
		//	if ( this.dragElement.parentNode != null ) {
		//		this.dragElement.parentNode.removeChild(this.dragElement);
		//	}
		//}
		//削除処理
		Element.remove(this.dragParentElement);
		Element.remove(this.dummyElement);

		this._deactivateRegisteredDropZones();
		this._endDrag();
		this.clearSelection();
		this.dragElement = null;
		this.dragElementPosition = null;
		this.currentDragObjectVisible = false;
		this._terminateEvent(e);
	},

	_doCancelDragProcessing: function() {
		this._cancelDrag();
		//if ( this.dragElement != this.currentDragObjects[0].getMouseDownHTMLElement() && this.dragElement)
		//	if ( this.dragElement.parentNode != null )
		//		this.dragElement.parentNode.removeChild(this.dragElement);

		//元の場所へ戻す
		this.clearSelection();
		if(this.dragElement == null) return;
		if(this.add_absolute) {
			this.dragElement.style.position = "";
			//TODO:後に削除
			//if(this.start_width != null) {
			//	this.dragElement.style.width = this.start_width;
			//	this.dragElement.style.height = this.start_height;
			//	this.start_width = null;
			//	this.start_height = null;
			//}
		}
		if(this.dragElementWidth != null && this.dragElementWidth > 0) {
			//広さを元に戻す
			this.dragElement.style.width = this.dragElementWidth + "px";
		} else {
			this.dragElement.style.width = '';
		}
		this.dragElementWidth = null;
		
		new Insertion.After(this.dummyElement, "<input type='hidden' value='' />");
		var buf_dummy_el = this.dummyElement.nextSibling;
		Element.remove(this.dummyElement);
		this.dummyElement = buf_dummy_el;
		var parent_el = Element.getParentElement(this.dummyElement);
		parent_el.insertBefore(this.dragElement, this.dummyElement);
		Element.remove(this.dragParentElement);
		Element.remove(this.dummyElement);
		this._deactivateRegisteredDropZones();
		this.dragElement = null;
		this.dragElementPosition = null;
		this.currentDragObjectVisible = false;
	},

	_placeDraggableInDropZone: function(e) {
		var foundDropZone = false;
		var n = this.dropZones.length;
		for ( var i = 0 ; i < n ; i++ ) {
			if ( this._mousePointInDropZone( e, this.dropZones[i] ) ) {
				if ( this.dropZones[i].canAccept(this.currentDragObjects) ) {
					//this.dropZones[i].hideHover(e);

					foundDropZone = this.dropZones[i].save(this.currentDragObjects);
					if(foundDropZone) {
						this.dropZones[i].accept(this.currentDragObjects);
						this.dropZones[i].hideHover(e);
					} else {
						this.dropZones[i].hideHover(e);
						continue;
					}
					//this.dropZones[i].hideHover(e);
					
					//foundDropZone = true;
					break;
				}
			}
		}

      return foundDropZone;
   },

   _cancelDrag: function() {
      for ( var i = 0 ; i < this.currentDragObjects.length ; i++ )
         this.currentDragObjects[i].cancelDrag();
   },

   _endDrag: function() {
      for ( var i = 0 ; i < this.currentDragObjects.length ; i++ )
         this.currentDragObjects[i].endDrag();
   },

   _mousePointInDropZone: function( e, dropZone ) {

      var absoluteRect = dropZone.getAbsoluteRect();
      if(this.dragElement == dropZone.getHTMLElement()) {
          return false;
      }
      Position.prepare();
      var pointerX = Event.pointerX(e);
      var pointerY = Event.pointerY(e);
    
      if(this.draggableRangeElement != null) {
      	//mouseポイントが、Rangeを越えていた場合、Range内の場所まで戻して判定
      	var drop_offset = Position.cumulativeOffset(this.draggableRangeElement);
		var drop_el_left = drop_offset[0];
		var drop_el_right = drop_offset[0] + this.draggableRangeElement.offsetWidth;
		var drop_el_top = drop_offset[1];
		var drop_el_bottom = drop_offset[1] + this.draggableRangeElement.offsetHeight;
		
		
		if(pointerX < drop_el_left) {
			pointerX = drop_el_left + 1;
		} else if(pointerX > drop_el_right){
			pointerX = drop_el_right - 1;
		}

		if(pointerY < drop_el_top) {
			pointerY = drop_el_top + 1;
		} else if(pointerY > drop_el_bottom){
			pointerY = drop_el_bottom - 1;
		}
      }
      
      return pointerX  > absoluteRect.left &&
             pointerX  < absoluteRect.right &&
             pointerY  > absoluteRect.top   &&
             pointerY  < absoluteRect.bottom;
      
      //return pointerX  > absoluteRect.left + this._leftOffset(e) &&
      //       pointerX  < absoluteRect.right + this._leftOffset(e) &&
      //       pointerY  > absoluteRect.top + this._topOffset(e)   &&
      //       pointerY  < absoluteRect.bottom + this._topOffset(e);
      //return e.clientX  > absoluteRect.left + this._leftOffset(e) &&
      //       e.clientX  < absoluteRect.right + this._leftOffset(e) &&
      //       e.clientY  > absoluteRect.top + this._topOffset(e)   &&
      //       e.clientY  < absoluteRect.bottom + this._topOffset(e);
   },

	_addMouseDownHandler: function( aDraggable )
	{
		htmlElement  = aDraggable.getMouseDownHTMLElement();
		if ( htmlElement  != null ) {
			htmlElement.draggableObject = aDraggable;
			//イベント登録
			Event.observe(htmlElement , "mousedown", this._onmousedown.bindAsEventListener(this));
			Event.observe(htmlElement, "mousedown", this._mouseDown);
		}
	},

 	_activateRegisteredDropZones: function() {
		var n = this.dropZones.length;
		for ( var i = 0 ; i < n ; i++ ) {
			var dropZone = this.dropZones[i];
			if ( dropZone.canAccept(this.currentDragObjects) )
				dropZone.activate();
		}

		this.activatedDropZones = true;
	},

	_deactivateRegisteredDropZones: function() {
		var n = this.dropZones.length;
		for ( var i = 0 ; i < n ; i++ )
			this.dropZones[i].deactivate();
		this.activatedDropZones = false;
	},

	_onmousedown: function () {
		Event.observe(document, "mousemove", this._mouseMove);
		Event.observe(document, "mouseup",  this._mouseUp);
	},

   _terminateEvent: function(e) {
      if ( e.stopPropagation != undefined )
         e.stopPropagation();
      else if ( e.cancelBubble != undefined )
         e.cancelBubble = true;

      if ( e.preventDefault != undefined )
         e.preventDefault();
      else
         e.returnValue = false;
   }
};

//-------------------- ricoDraggable.js
compDraggable = Class.create();

compDraggable.prototype = {

   initialize: function( htmlElement, dragElement, params, type ) {
      this.type          = type;
      this.htmlElement   = $(htmlElement);
      if(dragElement == undefined || dragElement == null) {
          this.dragElement   = this.htmlElement;
      } else {
          this.dragElement   = dragElement;
      }
      this.selected      = false;
      //追加
      this.params = params;
   },

   /**
    *   Returns the HTML element that should have a mouse down event
    *   added to it in order to initiate a drag operation
    *
    **/
   getMouseDownHTMLElement: function() {
      return this.dragElement;
   },
   
   getHTMLElement: function() {
      return this.htmlElement;
   },
   
   select: function() {
      this.selected = true;

      if ( this.showingSelected )
         return;

      //var htmlElement = this.getMouseDownHTMLElement();

      //var color = compCommonUtil.Color.createColorFromBackground(htmlElement);
      //color.isBright() ? color.darken(0.033) : color.brighten(0.033);

      //this.saveBackground = compCommonUtil.getElementsComputedStyle(htmlElement, "backgroundColor", "background-color");
      //htmlElement.style.backgroundColor = color.asHex();
      this.showingSelected = true;
   },

   deselect: function() {
      this.selected = false;
      if ( !this.showingSelected )
         return;

      //var htmlElement = this.getMouseDownHTMLElement();

      //htmlElement.style.backgroundColor = this.saveBackground;
      this.showingSelected = false;
   },

   isSelected: function() {
      return this.selected;
   },

   startDrag: function() {
       var draggable = this.htmlElement;
       Element.setStyle(draggable, {opacity:0.7});
       //new compCommonUtil.Effect.FadeTo(draggable, 0.7, 0, 1);
   },
   prestartDrag: function() {
   },
   cancelDrag: function() {
       var draggable = this.htmlElement;
       Element.setStyle(draggable, {opacity:""});
       //new compCommonUtil.Effect.FadeTo(draggable, 1.0, 0, 1);
   },

   endDrag: function() {
      var draggable = this.htmlElement;
      Element.setStyle(draggable, {opacity:""});
      //new compCommonUtil.Effect.FadeTo(draggable, 1.0, 0, 1);
   },

   getSingleObjectDragGUI: function() {
      return this.htmlElement;
   },

   getMultiObjectDragGUI: function( draggables ) {
      return this.htmlElement;
   },

   getDroppedGUI: function() {
      return this.htmlElement;
   },

   toString: function() {
      return this.type + ":" + this.htmlElement + ":";
   },
   
   getParams: function() {
      return this.params;
   }

};


//-------------------- ricoDropzone.js
compDropzone = Class.create();

compDropzone.prototype = {

   initialize: function( htmlElement , params, dropObjectAppendChild) {
      this.htmlElement  = $(htmlElement);
      this.absoluteRect = null;
      //追加
      this.params = params;
      if(dropObjectAppendChild == null || dropObjectAppendChild == undefined) 
      	this.dropObjectAppendChild = false;	//ドロップ後に自動的にドロップ先にappendChildする場合、false(default:false)
      else
      	this.dropObjectAppendChild = dropObjectAppendChild;
      this.showingHover = false;
      this.ChgSeqHover = null;
      this.ChgSeqPosition = null;
   },
   setDropObjectAppendChild: function(dropObjectAppendChild) {
      this.dropObjectAppendChild = dropObjectAppendChild;
   },
   getParams: function() {
      return this.params;
   },

   getHTMLElement: function() {
      return this.htmlElement;
   },

   clearPositionCache: function() {
      this.absoluteRect = null;
   },

   getAbsoluteRect: function() {
      if ( this.absoluteRect == null ) {
         var htmlElement = this.getHTMLElement();
         var offset = Position.cumulativeOffsetScroll(htmlElement);
         //var offset = Position.cumulativeOffset(htmlElement);
         Position.prepare();//this.deltaY,this.deltaX         
         var pos = {"x":offset[0],"y":offset[1]};
         //var pos = compCommonUtil.toViewportPosition(htmlElement);
         this.absoluteRect = {
            top:    pos.y,
            left:   pos.x,
            bottom: pos.y + htmlElement.offsetHeight,
            right:  pos.x + htmlElement.offsetWidth
         };
      }
      return this.absoluteRect;
   },

   activate: function() {
      var htmlElement = this.getHTMLElement();
      if (htmlElement == null  || this.showingActive)
         return;

      this.showingActive = true;
      /**mouse-move時にドロップ先の色を変更しない
      this.saveBackgroundColor = htmlElement.style.backgroundColor;

      var fallbackColor = "#ffea84";
      var currentColor = compCommonUtil.Color.createColorFromBackground(htmlElement);
      if ( currentColor == null )
         htmlElement.style.backgroundColor = fallbackColor;
      else {
         currentColor.isBright() ? currentColor.darken(0.2) : currentColor.brighten(0.2);
         htmlElement.style.backgroundColor = currentColor.asHex();
      }
      **/
   },

   deactivate: function() {
      var htmlElement = this.getHTMLElement();
      if (htmlElement == null || !this.showingActive)
         return;
      /**mouse-move時にドロップ先の色を変更しない
      htmlElement.style.backgroundColor = this.saveBackgroundColor;
      this.saveBackgroundColor = null;
      **/
      this.showingActive = false;
   },

   showHover: function(e) {
      var htmlElement = this.getHTMLElement();
      if ( this._showHover(htmlElement) )
         return;

      //htmlElement.style.borderWidth = "1px";
      //htmlElement.style.borderStyle = "solid";
      //htmlElement.style.borderColor = "#ff9900";
      ////htmlElement.style.borderColor = "#ffff00";
      htmlElement.style.backgroundColor = "#ffff99";
   },
   _showHover: function(htmlElement) {
      if ( htmlElement == null || this.showingHover )
         return false;

      this.saveHoverBackgroundColor = htmlElement.style.backgroundColor;
      this.saveHoverBorderWidth = htmlElement.style.borderWidth;
      this.saveHoverBorderStyle = htmlElement.style.borderStyle;
      this.saveHoverBorderColor = htmlElement.style.borderColor;

      this.showingHover = true;
      return true;
   },
   hideHover: function(e) {
      var htmlElement = this.getHTMLElement();
      if ( this._hideHover(htmlElement) )
         return;
   },
   _hideHover: function(htmlElement) {
      if ( htmlElement == null || !this.showingHover )
         return;
      htmlElement.style.backgroundColor = this.saveHoverBackgroundColor;
      if(this.saveHoverBorderWidth != "") htmlElement.style.borderWidth = this.saveHoverBorderWidth;
      if(this.saveHoverBorderStyle != "") htmlElement.style.borderStyle = this.saveHoverBorderStyle;
      if(this.saveHoverBorderColor != "") htmlElement.style.borderColor = this.saveHoverBorderColor;
      this.showingHover = false;
   },
	//リンクリスト表示順変更等で使用
	showChgSeqHover: function(event, pos) {
		var htmlElement = this.getHTMLElement();
		//if ( this._showHover(htmlElement) )
		//	return;
		
		//Position.prepare();

		var offset = Position.cumulativeOffset(htmlElement);
 		var ex = offset[0];
 		var ey = offset[1];
 		var center_y = ey + (htmlElement.offsetHeight/2);
 		var y = Event.pointerY(event);
 		if(this.ChgSeqHover == undefined || this.ChgSeqHover == null) {
 			this.ChgSeqHover = document.createElement("div");
 			document.body.appendChild(this.ChgSeqHover);
 		}
 		//document.body.appendChild(this.ChgSeqHover);
 		this.ChgSeqHover.style.width = htmlElement.offsetWidth + "px";
 		this.ChgSeqHover.style.height = "1px"; //固定
 		this.ChgSeqHover.style.position = "absolute";
 		this.ChgSeqHover.style.left = ex  + "px";
 		commonCls.max_zIndex = commonCls.max_zIndex + 1;
		this.ChgSeqHover.style.zIndex = commonCls.max_zIndex;
		if(pos != undefined) {
			this.ChgSeqPosition = pos;
			if(pos == "bottom") this.ChgSeqHover.style.top = (ey + htmlElement.offsetHeight)  + "px";
			else this.ChgSeqHover.style.top = ey  + "px";
		} else if(y > center_y) {
			//bottom
			this.ChgSeqPosition = "bottom";
			this.ChgSeqHover.style.top = (ey + htmlElement.offsetHeight)  + "px";
		} else {
			//top
			this.ChgSeqPosition = "top";
			this.ChgSeqHover.style.top = ey  + "px";
		}
		this.ChgSeqHover.style.borderTop = "3px";
		this.ChgSeqHover.style.borderTopStyle = "solid";
		this.ChgSeqHover.style.borderTopColor = "#ffff00";
	},
	//リンクリスト表示順変更等で使用
	showChgSeqHoverInside: function(event) {
		var htmlElement = this.getHTMLElement();
		//if ( this._showHover(htmlElement) )
		//	return;
		this.ChgSeqPosition = "inside";
		htmlElement.style.backgroundColor = "#ffff99";
			 			
	},
	hideChgSeqHover: function(event) {
		var htmlElement = this.getHTMLElement();
		if ( this._hideHover(htmlElement) )
			return;
		if(this.ChgSeqHover) {
			Element.remove(this.ChgSeqHover);
			this.ChgSeqHover = null;
			this.ChgSeqPosition = null;
		}
	},
	acceptChgSeq: function(draggableObjects, dropElement, pos) {
		var htmlElement = this.getHTMLElement();
		if ( htmlElement == null )
			return;
		var n = draggableObjects.length;
		for ( var i = 0 ; i < n ; i++ ) {
			var theGUI = draggableObjects[i].getDroppedGUI();
			if ( Element.getStyle( theGUI, "position" ) == "absolute" )
	         {
	            theGUI.style.position = "static";
	            theGUI.style.top = "";
	            theGUI.style.left = "";
	         }
	        //指定があれば、優先
	        if(dropElement) {
	        	htmlElement = dropElement;
	        }
	        if(pos) this.ChgSeqPosition = pos;
	        
			if(this.ChgSeqPosition == "top") {
				htmlElement.parentNode.insertBefore(theGUI, htmlElement);
			} else if(this.ChgSeqPosition == "bottom"){
				var next_el = htmlElement.nextSibling;
				if(!next_el) {
					if(htmlElement.parentNode.tagName.toLowerCase() == "table") {
						var append_el = document.createElement("tbody");
						htmlElement.parentNode.appendChild(append_el);
					} else {
						var append_el = htmlElement.parentNode;
					}
					append_el.appendChild(theGUI);
					//htmlElement.parentNode.appendChild(theGUI);
				} else {
					next_el.parentNode.insertBefore(theGUI, next_el);
				}
			} else {
				var next_el = htmlElement.nextSibling;
				if(next_el.tagName.toLowerCase() == "table") {
					var append_el = document.createElement("tbody");
					next_el.appendChild(append_el);
				} else {
					var append_el = next_el;
				}
				append_el.appendChild(theGUI);
				//next_el.appendChild(theGUI);
			}
//debug.p(this.ChgSeqPosition);
			commonCls.blockNotice(null, theGUI);
		}
	},
   canAccept: function(draggableObjects) {
      return true;
   },

   accept: function(draggableObjects) {
      var htmlElement = this.getHTMLElement();
      if ( htmlElement == null )
         return;
      if(this.dropObjectAppendChild) {
	      var n = draggableObjects.length;
	      for ( var i = 0 ; i < n ; i++ )
	      {
	         var theGUI = draggableObjects[i].getDroppedGUI();
	         if ( Element.getStyle( theGUI, "position" ) == "absolute" )
	         {
	            theGUI.style.position = "static";
	            theGUI.style.top = "";
	            theGUI.style.left = "";
	         }
	         htmlElement.appendChild(theGUI);
	      }
	  }
   },
   //ドロップ後の処理の記述メソッド
   save: function(draggableObjects) {
      return true;
   }
};