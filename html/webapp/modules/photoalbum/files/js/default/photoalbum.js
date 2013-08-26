var clsPhotoalbum = Class.create();
var photoalbumCls = Array();

clsPhotoalbum.prototype = {
	initialize: function(id) {
		this.id = id;

		this.currentPhotoalbumID = null;
		this.photoalbum_id = null;
		this.callbackAction = null;

		this.album_id = null;
		this.photo_count = null;
		this.size_flag = false;
		this.comment_flag = false;
		this.minWidth = null;

		this.photos = new Array();
		this.slide_type = null;
		this.currentPhotoIndex = 0;
		this.leftIndex = 0;
		this.rightIndex = 0;
		this.popupAdjustmentY = 19;

		this.timer = null;

		this.dragDrop = null;
	},

	checkCurrent: function() {
		var currentRow = $("photoalbum_current_row" + this.currentPhotoalbumID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("photoalbum_current" + this.currentPhotoalbumID + this.id);
		current.checked = true;
	},

	changeCurrent: function(photoalbumID) {
		var oldCurrentRow = $("photoalbum_current_row" + this.currentPhotoalbumID + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentPhotoalbumID = photoalbumID;
		var currentRow = $("photoalbum_current_row" + this.currentPhotoalbumID + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"photoalbum_action_edit_current",
			"photoalbum_id":photoalbumID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "photoalbum_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	selectDisplayList: function() {
		var albumSelect = $("photoalbum_display_album_id" + this.id);
		if (albumSelect) {
			albumSelect.disabled = true;
		}
		Element.addClassName($("photoalbum_album_create" + this.id), "display-none");
		Element.addClassName($("photoalbum_photo_upload" + this.id), "display-none");
	},

	selectDisplaySlide: function() {
		var albumSelect = $("photoalbum_display_album_id" + this.id);
		if (albumSelect) {
			albumSelect.disabled = false;
		}
		Element.removeClassName($("photoalbum_album_create" + this.id), "display-none");
		Element.removeClassName($("photoalbum_photo_upload" + this.id), "display-none");
	},

	changeSizeFlag: function(value) {
		$("photoalbum_slide_size_width" + this.id).disabled = !value;
		$("photoalbum_slide_size_height" + this.id).disabled = !value;
	},

	deletePhotoalbum: function(photoalbumID, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"photoalbum_action_edit_delete",
			"photoalbum_id":photoalbumID
		};

		var params = new Object();
		params["callbackfunc"] = function(res){
											commonCls.sendView(this.id, "photoalbum_view_edit_list");
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	showPopupAlbumList: function(event, photoalbumID, prefixID) {
		var params = new Object();
		params["action"] = "photoalbum_view_main_init";
		params["photoalbum_id"] = photoalbumID;
		params["prefix_id_name"] = prefixID;

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showAlbumList: function(param) {
		var params = new Object();
		params["action"] = "photoalbum_view_main_init";
		params["photoalbum_id"] = this.photoalbum_id;
		Object.extend(params, param);

		commonCls.sendView(this.id, params);
	},

	_getPopupParams: function() {
		var params = new Object();
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['modal_flag'] = true;

		return params;
	},

	showAlbumEntry: function(event, albumID) {
		var params = new Object();
		params["action"] = "photoalbum_view_main_album_entry";
		params["photoalbum_id"] = this.photoalbum_id;
		params["album_id"] = albumID;
		params["prefix_id_name"] = "photoalbum_album_entry";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	selectJacket: function(src) {
		var form = $("photoalbum_album_form" + this.id);
		form["album_jacket"].value = src.substring(src.lastIndexOf("/") + 1, src.length);
		form["upload_id"].value = "";

		var params = new Object();
		params["param"] = {
			"action":"photoalbum_view_main_album_jacket",
			"photoalbum_id":this.photoalbum_id,
			"album_id":this.album_id,
			"album_jacket":form["album_jacket"].value
		};

		params["top_el"] = $(this.id);
		params["target_el"] = $("photoalbum_album_jacket" + this.id).parentNode;

		commonCls.send(params);
	},

	uploadJacket: function () {
		var form = $("photoalbum_album_form" + this.id);

		var params = new Object();
		params["param"] = {
			"action":"photoalbum_action_main_album_jacket",
			"photoalbum_id":form["photoalbum_id"],
			"album_id":form["album_id"]
		};

		params["top_el"] = $(this.id);
		params['form_prefix'] = "photoalbum_jacket";

		params["callbackfunc"] = function(files, res){
										form["album_jacket"].value = "?action="+ files[0]['action_name'] + "&upload_id=" + files[0]['upload_id'];
										form["upload_id"].value = files[0]["upload_id"];

										var params = new Object();
										params["param"] = {
											"action":"photoalbum_view_main_album_jacket",
											"photoalbum_id":this.photoalbum_id,
											"album_id":this.album_id,
											"upload_id":form["upload_id"].value,
											"album_jacket":form["album_jacket"].value
										};

										params["top_el"] = $(this.id);
										params["target_el"] = $("photoalbum_album_jacket" + this.id).parentNode;

										commonCls.send(params);
									}.bind(this);

		commonCls.sendAttachment(params);
	},

	showAlbumDetail: function() {
		var rows = $("photoalbum_album_entry" + this.id).getElementsByTagName("tr");
		for (var i = 2; i < rows.length - 1; i++) {
			commonCls.displayChange(rows[i]);
		}
	},

	enterAlbum: function() {
		var post = "action=photoalbum_action_main_album_entry&" + Form.serialize($("photoalbum_album_form" + this.id));

		var params = new Object();
		params["callbackfunc"] = function(res){
										var id = this.id.replace("_photoalbum_album_entry", "");
										commonCls.sendView(id, photoalbumCls[id].callbackAction);
										commonCls.removeBlock(this.id);
									}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	deleteAlbum: function(albumID, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"photoalbum_action_main_album_delete",
			"album_id":albumID
		};

		var params = new Object();
		params["callbackfunc"] = function(res){
											commonCls.sendView(this.id, "photoalbum_view_main_init");
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	showPhoto: function(event, albumID) {
		var params = new Object();
		params["action"] = "photoalbum_view_main_photo_init";
		params["photoalbum_id"] = this.photoalbum_id;
		params["album_id"] = albumID;
		params["prefix_id_name"] = "photoalbum_photo";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	initializeThumbnail: function() {
		var thumbnail = $("photoalbum_thumbnail" + this.id);
		var height = parseInt(Element.getStyle(thumbnail.parentNode, "height"));
		Element.setStyle(thumbnail, {"height":height + "px"});
		if (thumbnail.lastChild.nodeType != 1) {
			thumbnail.removeChild(thumbnail.lastChild);
		}

		this.selectPhoto();
	},

	selectPhoto: function(index) {
		if (index == null) {
			index = 0;
		}

		var photo = $("photoalbum_photo" + this.id);
		if (!photo) {
			clearInterval(this.timer);
			return;
		}
		var className = photo.className;
		var parent = photo.parentNode;
		Element.remove(photo);

		photo = document.createElement("IMG");
		photo.id = "photoalbum_photo" + this.id;
		photo.className = className;
		Element.addClassName(photo, "display-none");
		parent.appendChild(photo);

		this._resizePhoto(index);

		if (typeof(photoalbumSlideshowCls) != "undefined") {
			photoalbumSlideshowCls.movePhoto(index);
		}

		if (browser.isIE) {
			if (this.slide_type.constructor == Array) {
				var rand = Math.floor(Math.random() * this.slide_type.length);
				var slideType = this.slide_type[rand];
			} else {
				var slideType = this.slide_type;
			}

			photo.style.filter = slideType;
			if (browser.version < 10) photo.filters[0].Apply();
		}

		photo.src = this.photos[index]["src"].replace("&amp;","&");
		photo.title = this.photos[index]["photo_name"];
		photo.alt = this.photos[index]["photo_name"];

		Element.setStyle(photo, {"width":this.photos[index]["width"] + "px"});
		Element.setStyle(photo, {"height":this.photos[index]["height"] + "px"});

		if (!this.size_flag) {
			var photoArea = $("photoalbum_photo_area" + this.id);
			if (this.photos[index]["width"] < this.minWidth) {
				Element.setStyle(photoArea, {"width":this.minWidth + "px"});
			} else {
				Element.setStyle(photoArea, {"width":"auto"});
			}
		}
		if (browser.isIE) {
			if (browser.version < 10) photo.filters[0].Play();
		}

		var oldPhotoIndex = this.currentPhotoIndex;
		this.currentPhotoIndex = index;

		Element.removeClassName(photo, "display-none");

		var thumbnail = $("photoalbum_thumbnail" + this.id);
		if (!thumbnail
				&& typeof(photoalbumSlideshowCls) == "undefined") {
			return;
		}

		$("photoalbum_photo_name" + this.id).innerHTML = this.photos[this.currentPhotoIndex]["photo_name"];
		$("photoalbum_photo_description" + this.id).innerHTML = this.photos[this.currentPhotoIndex]["photo_description"];
		$("photoalbum_photo_current" + this.id).innerHTML = this.currentPhotoIndex + 1;

		if (typeof(photoalbumSlideshowCls) != "undefined") {
			photoalbumSlideshowCls.moveDescription();
		}

		if (!thumbnail) {
			return;
		}

		Element.removeClassName($("photoalbum_photo" + this.photos[oldPhotoIndex]["photo_id"] + this.id), "photoalbum_current");
		Element.addClassName($("photoalbum_photo" + this.photos[index]["photo_id"] + this.id), "photoalbum_current");

		this._resizeThumbnail();
		this.cancelComment();
		this._showFooter();
	},

	_resizePhoto: function(index) {
		if (!this.size_flag) {
			return;
		}

		var photoArea = $("photoalbum_photo_area" + this.id);
		if (this.photos[index]["width"] <= photoArea.offsetWidth
				&& this.photos[index]["height"] <= photoArea.offsetHeight) {
			return;
		}

		var fileRatio =  this.photos[index]["height"] / this.photos[index]["width"];
		var fixRatio =  photoArea.offsetHeight / photoArea.offsetWidth;

		if (fixRatio < fileRatio) {
			this.photos[index]["width"] = parseInt(photoArea.offsetHeight / fileRatio);
			this.photos[index]["height"] = photoArea.offsetHeight
		} else {
			this.photos[index]["width"] = photoArea.offsetWidth;
			this.photos[index]["height"] = parseInt(photoArea.offsetWidth * fileRatio);
		}
	},

	_resizeThumbnail: function() {
		var thumbnail = $("photoalbum_thumbnail" + this.id);
		if (browser.isIE) {
			Element.setStyle(thumbnail, {"margin-left":"0px"});
		}

		Element.setStyle(thumbnail, {"width":"auto"});
		var photoArea = $("photoalbum_photo_area" + this.id);
		Element.setStyle(thumbnail.parentNode, {"width":photoArea.offsetWidth
				- parseInt(Element.getStyle(thumbnail.parentNode, 'borderLeftWidth'))
				- parseInt(Element.getStyle(thumbnail.parentNode, 'borderRightWidth')) + "px"});
		var previous = $("photoalbum_thumbnail_previous" + this.id);
		var next = $("photoalbum_thumbnail_next" + this.id);

		var width = thumbnail.parentNode.clientWidth - previous.offsetWidth - next.offsetWidth;
		Element.setStyle(thumbnail, {"width":width + "px"});

		var end = thumbnail.offsetLeft + thumbnail.offsetWidth;
		for (var i = this.leftIndex, length = thumbnail.childNodes.length; i < length; i++) {
			Element.removeClassName(thumbnail.childNodes[i], "display-none");

			if (i == this.leftIndex) {
				var top = thumbnail.childNodes[i].offsetTop;
			}

			var left = thumbnail.childNodes[i].offsetLeft + thumbnail.childNodes[i].offsetWidth;

			if (top < thumbnail.childNodes[i].offsetTop
					|| left > end) {
				break;
			}

			this.rightIndex = i;
		}
		for (var j = i, length = thumbnail.childNodes.length; j < length; j++) {
			if (Element.hasClassName(thumbnail.childNodes[j], "display-none") &&
				next.offsetTop == previous.offsetTop) {
				break;
			}

			Element.addClassName(thumbnail.childNodes[j], "display-none");
		}

		if (this.rightIndex < thumbnail.childNodes.length - 1) {
			Element.removeClassName(next, "visible-hide");
		} else {
			Element.addClassName(next, "visible-hide");
		}
	},

	_showFooter: function() {
		var params = new Object();
		params["param"] = {
			"action":"photoalbum_view_main_photo_footer",
			"photoalbum_id":this.photoalbum_id,
			"album_id":this.album_id,
			"photo_id":this.photos[this.currentPhotoIndex]["photo_id"]
		};

		params["top_el"] = $(this.id);
		params["target_el"] = $("photoalbum_photo_footer" + this.id);

		commonCls.send(params);
	},

	showPreviousPhoto: function() {
		if (this.currentPhotoIndex <= 0) {
			var index = this.photo_count - 1;
		} else {
			var index = this.currentPhotoIndex - 1;
		}
		this.selectPhoto(index);
	},

	showNextPhoto: function() {
		if (this.currentPhotoIndex >= this.photo_count - 1) {
			var index = 0;
		} else {
			var index = this.currentPhotoIndex + 1;
		}
		this.selectPhoto(index);
	},

	showPreviousThumbnail: function() {
		if (this.leftIndex == 0) {
			return;
		}

		this.leftIndex--;
		if (this.leftIndex == 0) {
			Element.addClassName($("photoalbum_thumbnail_previous" + this.id), "visible-hide");
		}

		var thumbnail = $("photoalbum_thumbnail" + this.id);
		Element.removeClassName(thumbnail.childNodes[this.leftIndex], "display-none");
		Element.addClassName(thumbnail.childNodes[this.rightIndex], "display-none");

		var lastIndex = this.photo_count - 1;
		if (lastIndex == this.rightIndex) {
			Element.removeClassName($("photoalbum_thumbnail_next" + this.id), "visible-hide");
		}
		this.rightIndex--;
	},

	showNextThumbnail: function() {
		var lastIndex = this.photo_count - 1;
		if (lastIndex == this.rightIndex) {
			return;
		}

		this.rightIndex++;
		if (lastIndex == this.rightIndex) {
			Element.addClassName($("photoalbum_thumbnail_next" + this.id), "visible-hide");
		}

		var thumbnail = $("photoalbum_thumbnail" + this.id);
		Element.removeClassName(thumbnail.childNodes[this.rightIndex], "display-none");
		Element.addClassName(thumbnail.childNodes[this.leftIndex], "display-none");

		if (this.leftIndex == 0) {
			Element.removeClassName($("photoalbum_thumbnail_previous" + this.id), "visible-hide");
		}
		this.leftIndex++;
	},

	showPhotoUpload: function(event) {
		var form = $("photoalbum_form" + this.id);
		if (form) {
			var albumID = form["display_album_id"].value;
		} else {
			var albumID = this.album_id;
		}

		var params = new Object();
		params = {
			"action":"photoalbum_view_main_photo_upload",
			"photoalbum_id":this.photoalbum_id,
			"album_id":albumID,
			"prefix_id_name":"photoalbum_photo_upload"
		};

		var popupParams = this._getPopupParams();
		var offset = Position.cumulativeOffsetScroll(popupParams['top_el']);
		popupParams["x"] = offset[0];
		popupParams["y"] = offset[1] + this.popupAdjustmentY;
		popupParams['modal_flag'] = false;

		commonCls.sendPopupView(event, params, popupParams);
	},

	addUpload: function(size) {
		var div = document.createElement("div");
		div.innerHTML = '<input type="file" class="photoalbum_photo_upload" name="upload[]" size="' + size + '" />';

		var uploadArea = $("photoalbum_photo_upload_area" + this.id);
		uploadArea.appendChild(div);
	},

	upload: function(type) {
		var params = new Object();
		params["param"] = {
			"action":"photoalbum_action_main_photo_upload_" + type
		};
		params["top_el"] = $(this.id);
		params["form_prefix"] = "photoalbum_" + type;
		params["callbackfunc"] = function(files, res) {
											var refreshElementID = this.id.replace("photoalbum_photo_upload", "photoalbum_photo");
											if ($(refreshElementID)) {
												commonCls.sendRefresh(refreshElementID);
											}
											commonCls.removeBlock(this.id);
										}.bind(this);

		commonCls.sendAttachment(params);
	},

	showComment: function() {
		var params = new Object();
		params["param"] = {
			"action":"photoalbum_view_main_comment",
			"photoalbum_id":this.photoalbum_id,
			"album_id":this.album_id,
			"photo_id":this.photos[this.currentPhotoIndex]["photo_id"]
		};
		params["top_el"] = $(this.id);
		params["target_el"] = $("photoalbum_comment_area" + this.id);

		commonCls.send(params);
	},

	showCommentEntry: function(commentID) {
		var commentForm = $("photoalbum_comment_form" + this.id);
		commentForm["comment_value"].value = $("photoalbum_comment_value" + commentID + this.id).innerHTML.replace(/\n/ig,"").replace(/(<br(?:.|\s|\/)*?>)/ig,"\n").unescapeHTML();
		commentForm["comment_id"].value = commentID;

		commentForm["comment_value"].focus();
		commentForm["comment_value"].select();
	},

	enterComment: function() {
		var params = new Object();
		params["target_el"] = $("photoalbum_comment_area" + this.id);
		params["callbackfunc"] = function(res) {
											this._showFooter();
										}.bind(this);

		commonCls.sendPost(this.id, Form.serialize($("photoalbum_comment_form" + this.id)), params);
	},

	deleteComment: function(commentID, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"photoalbum_action_main_comment_delete",
			"photoalbum_id":this.photoalbum_id,
			"album_id":this.album_id,
			"photo_id":this.photos[this.currentPhotoIndex]["photo_id"],
			"comment_id":commentID
		};

		var params = new Object();
		params["target_el"] = $("photoalbum_comment_area" + this.id);
		params["callbackfunc"] = function(res){
											this._showFooter();
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	cancelComment: function() {
		$("photoalbum_comment_area" + this.id).innerHTML="";
		commonCls.focus($("_href" + this.id));
	},

	startSlide: function(timer) {
		this.timer = setInterval(function(){this.showNextPhoto();}.bind(this), timer * 1000);
		this._slideDisplay();
	},

	stopSlide: function() {
		clearInterval(this.timer);
		this._slideDisplay();
	},

	_slideDisplay: function() {
		var start = $("photoalbum_slide_start" + this.id);
		if (!start) {
			return;
		}
		commonCls.displayChange($("photoalbum_slide_start" + this.id));
		commonCls.displayChange($("photoalbum_slide_stop" + this.id));

		var footer = $("photoalbum_photo_footer" + this.id);
		if (!footer) {
			return;
		}
		commonCls.displayChange(footer);
		commonCls.displayChange($("photoalbum_comment_area" + this.id));
	},

	vote: function() {
		var post = {
			"action":"photoalbum_action_main_photo_vote",
			"photoalbum_id":this.photoalbum_id,
			"album_id":this.album_id,
			"photo_id":this.photos[this.currentPhotoIndex]["photo_id"]
		};

		commonCls.sendPost(this.id, post, {"target_el":$("photoalbum_photo_footer" + this.id)});
	},

	showPhotoList: function(event, param) {
		var params = new Object();
		params["action"] = "photoalbum_view_main_photo_list";
		params["photoalbum_id"] = this.photoalbum_id;
		params["album_id"] = this.album_id;
		Object.extend(params, param);

		commonCls.sendView(this.id, params);
	},

	redrawPhoto: function() {
		var params = new Object();
		params["action"] = "photoalbum_view_main_photo_init";
		params["photoalbum_id"] = this.photoalbum_id;
		params["album_id"] = this.album_id;

		commonCls.sendView(this.id, params);
	},

	photoMouseOver: function(div) {
		if (!this.dragDrop.hasSelection()) {
			Element.addClassName(div, "photoalbum_photo_over");
		}
	},

	photoMouseOut: function(div) {
 		if (!this.dragDrop.hasSelection()) {
 			Element.removeClassName(div, 'photoalbum_photo_over');
 		}
  	},

	showSlide: function(url) {
		if(browser.isIE) {
			window.open(url, "slide", "fullscreen=yes");
		} else {
			var slide = window.open(url, "slide", "scrollbars,width=" + window.screen.width + ",height=" + window.screen.height);
			slide.moveTo(0,0);
			slide.focus();
		}
	},

	showPhotoEntry: function(event, photoID) {
		var params = new Object();
		params["action"] = "photoalbum_view_main_photo_entry";
		params["photoalbum_id"] = this.photoalbum_id;
		params["album_id"] = this.album_id;
		params["photo_id"] = photoID;
		params["prefix_id_name"] = "photoalbum_photo_entry";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	enterPhoto: function() {
		var params = new Object();
		params["callbackfunc"] = function(res){
										commonCls.removeBlock(this.id);
										commonCls.sendRefresh(this.id.replace("photoalbum_photo_entry", "photoalbum_photo"));
									}.bind(this);

		commonCls.sendPost(this.id, Form.serialize($("photoalbum_photo_entry_form" + this.id)), params);
	},

	deletePhoto: function(photoID, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"photoalbum_action_main_photo_delete",
			"photoalbum_id":this.photoalbum_id,
			"album_id":this.album_id,
			"photo_id":photoID
		};

		var params = new Object();
		params["callbackfunc"] = function(res){
											commonCls.sendRefresh(this.id);
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	}
}