//カレンダー用クラス
var compCalendar = Class.create();
var calendarComp = Array();

/*
    モジュール側のjavascriptクラスから
	this.calendar = new compCalendar(id, text_el, options);
	@param string top_id
	@param text_el or string text_el_id
	       text_elの後ろにカレンダーアイコンを挿入する
    @param object options
    	onClickCallback   日付選択時コールバック関数
    	parentFrame       text_elがあるFrame(default:document)
    	designatedDate    日付指定（20070101のように指定）
    				      日付指定されていれば、テキストエリアの日付に有無にかかわらず優先される
    	calendarImgPath   カレンダーImgPath(themes/images/icons）
    	calendarColorDir  カレンダーColorDir(default）
    	calendarImg       テキスト横につくカレンダーアイコンimage(style/images/icons/下)
    	calendarThemeDir  カレンダーCSSの定義ファイルDir(htdocs/css/comp/XXX/comp_calendar.css) default: default
*/
compCalendar.prototype = {
	initialize: function(id, text_el, options) {
		this.id = id;
		this.popup = null;
		this.text_el = null;
		this.calendarIcon_el = null;

		this.options = {
				onClickCallback:       null,
				parentFrame:           "",
				onClickCallback:       null,
				designatedDate:        "",
				pre_show_week:         0,			// 前月0週目まで表示(0-3)
				next_show_week:        1,			// 次月1週目まで表示(0-3)
				calendarImgPath:       _nc_core_base_url + "/themes/images/icons",
				calendarColorDir:      "default",
				calendarImg:           "calendar.gif",
				calendarThemeDir:      "default"
		};
		Object.extend(this.options, options || {});

		// private
		this.date = null;

		this.todayYear = null;
		this.todayMonth = null;
		this.todayDay = null;

		this.selectedYear = null;
		this.selectedMonth = null;
		this.selectedDay = null;

		this.currentYear = null;
		this.currentMonth = null;
		this.currentDay = null;
		this.currentDate = null;
		this.setDesignatedDateFlag = false;

		this.Mdays = {"01":"31", "02":"28", "03":"31", "04":"30", "05":"31", "06":"30", "07":"31", "08":"31", "09":"30", "10":"31", "11":"30", "12":"31"};

		// １ブロック内に複数、カレンダーコンポーネントを張ってあった場合の対応
		var key = id;
		var count = 1;
		while (calendarComp[key]) {
      		key = id + "_" + count;
      		count++;
  		}
  		calendarComp[key] = this;
  		this.key = key;

		////calendarComp[id + this.calendar_classname] = this;

		this._showCalendarImg(text_el);
	},
	//------------------------------
	// 表示指定日セット
	//------------------------------
	setDesignatedDate: function(yyyymmdd) {
		this.options.designatedDate = yyyymmdd;
	},
	//------------------------------
	// 日付移動クリック
	//------------------------------
	onMoveClick: function(yyyymmdd) {
		this.currentYear = yyyymmdd.substr(0, 4);
		this.currentMonth = yyyymmdd.substr(4, 2);
		this.currentDay = yyyymmdd.substr(6, 2);
		this.currentDate = yyyymmdd;

		var popup_el = this.popup.getPopupElement();
		popup_el.contentWindow.document.body.innerHTML = this._render();
		this.popup.resize();
	},
	//------------------------------
	// 日付クリック
	//------------------------------
	onDayClick: function(yyyymmdd) {
		var day_separator = (compCalendarLang.day_separator != undefined) ? compCalendarLang.day_separator : "/";
		//callbackが指定されていれば実行、そうでなければ、this.text_elがinput type="text"ならば代入
		if(this.options.onClickCallback != null) {
			this.options.onClickCallback(yyyymmdd);
		} else {
			if(this.text_el.tagName.toLowerCase() == 'input' && this.text_el.type.toLowerCase() == 'text') {
				this.text_el.value = yyyymmdd.substr(0, 4)+ day_separator + yyyymmdd.substr(4, 2) + day_separator + yyyymmdd.substr(6, 2);
				commonCls.focus(this.text_el);
			}
		}
		this._popupClose();
	},
	//----------------------------------------------------------
	// カレンダー使用有効・無効処理 value=true or false
	//----------------------------------------------------------
	disabledCalendar: function(value) {
		this.text_el.disabled = value;
		if(value) {
			this.text_el.blur();
			this.calendarIcon_el.blur();
			Element.addClassName(this.calendarIcon_el, "display-none");
			if (this.popup != null) {
				this.popup.closePopup(this.popup.getPopupElement());
			}
		} else {
			Element.removeClassName(this.calendarIcon_el, "display-none");
			commonCls.focus(this.text_el);
		}
	},
	//------------------------------
	// カレンダーボタン表示処理
	//------------------------------
	_showCalendarImg: function(text_el) {
		if(typeof text_el == 'string') {
			if(this.options.parentFrame == "" || this.options.parentFrame == null) {
				text_el = $(text_el);
			} else {
				text_el = this.options.parentFrame.contentWindow.document.getElementById(text_el);
			}
		}
		if(typeof text_el != 'object') return;

		if(this.options.parentFrame == "" || this.options.parentFrame == null) {
			var calendarA_el = document.createElement("a");
			var calendarImg_el = document.createElement("img");
		} else {
			var calendarA_el = this.options.parentFrame.contentWindow.document.createElement("a");
			var calendarImg_el = this.options.parentFrame.contentWindow.document.createElement("img");
		}
		calendarA_el.href = "#";

		calendarImg_el.src = this.options.calendarImgPath + "/" +  this.options.calendarColorDir + "/" + this.options.calendarImg;
		Element.addClassName(calendarA_el, "comp_calendar_icon");
		if(text_el.tagName.toLowerCase() == 'input' && text_el.type.toLowerCase() == 'text') {
			Element.addClassName(text_el, "comp_calendar_text");
		}
		calendarImg_el.alt = (compCalendarLang.icon_alt != undefined) ? compCalendarLang.icon_alt : "Calendar";
		calendarImg_el.title = (compCalendarLang.icon_title != undefined) ? compCalendarLang.icon_title : "Show Calendar";
		text_el.parentNode.insertBefore(calendarA_el, text_el);
		text_el.parentNode.insertBefore(text_el, calendarA_el);
		calendarA_el.appendChild(calendarImg_el);

		Event.observe(calendarA_el, "click",
						function(event){
							this._showCalendar();
							Event.stop(event);
						}.bindAsEventListener(this), false, this.id);

		this.text_el = text_el;
		this.calendarIcon_el = calendarA_el;
	},
	//------------------------------
	// カレンダー表示処理
	//------------------------------
	_showCalendar: function() {
		// 今日の日付
		this.date = new Date();
		this.todayYear = this._getFormat(this.date.getFullYear());	//文字列変換
		this.todayMonth = this._getFormat(this.date.getMonth() + 1);
		this.todayDay = this._getFormat(this.date.getDate());

		if(this.setDesignatedDateFlag == true) {
			// 初期化
			this.options.designatedDate = null;
			this.setDesignatedDateFlag = false;
		}

		if((this.options.designatedDate == null || this.options.designatedDate == "") &&
			this.text_el.tagName.toLowerCase() == 'input' && this.text_el.type.toLowerCase() == 'text') {
			var sel_date = this.text_el.value;
			if(sel_date.length == 10) {
				var sel_year = sel_date.substr(0, 4);
				var sel_month = sel_date.substr(5, 2);
				var sel_day = sel_date.substr(8, 2);
				if(valueParseInt(sel_month) > 0 && valueParseInt(sel_month) < 13 &&
					valueParseInt(sel_day) > 0 && valueParseInt(sel_day) < 32) {
					//yyyy/mm/ddの形式チェックのみ行っている
					this.setDesignatedDateFlag = true;
					this.options.designatedDate = sel_year + sel_month + sel_day;
				}
			}
		}

		// 指定した日付
		if(this.options.designatedDate == null || this.options.designatedDate == "") {
			this.selectedYear = null;
			this.selectedMonth = null;
			this.selectedDay = null;

			this.currentYear = this.todayYear;
			this.currentMonth = this.todayMonth;
			this.currentDay = "01";

		} else {
			this.selectedYear = this.options.designatedDate.substr(0, 4);
			this.selectedMonth = this.options.designatedDate.substr(4, 2);
			this.selectedDay = this.options.designatedDate.substr(6, 2);

			this.currentYear = this.selectedYear;
			this.currentMonth = this.selectedMonth;
			this.currentDay = "01";
		}
		this.currentDate = this.currentYear + this.currentMonth + this.currentDay;

		var html = this._render();

		if(!this.popup) {
			this.popup = new compPopup(this.id,  "compCalendar");
			var new_dir_name ="/comp/"+this.options.calendarThemeDir+"/comp_calendar.css";
			var css_name = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&dir_name="+new_dir_name+"&header=0";
			this.popup.addCSSFiles(css_name);
			this.popup.observer = function(event) {this._popupClose(); }.bind(this);
			//this.popup.loadObserver = function(event) {
			//	this.popup.resize();
			//}.bind(this);
		}
		if(this.options.parentFrame) {
			this.popup.setLapPosition(this.calendarIcon_el, this.options.parentFrame);
			this.popup.showPopup(html);
		} else {
			this.popup.showPopup(html, this.calendarIcon_el);
		}
	},
	_getNextYear: function(yyyy, mm, dd) {
		yyyy = valueParseInt(yyyy) + 1;

		// 文字列として連結
		return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
	},
	_getPrevYear: function(yyyy, mm, dd) {
		yyyy = valueParseInt(yyyy) - 1;
		if(yyyy < 1900) {
			yyyy = 1900;
		}
		// 文字列として連結
		return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
	},
	_getNextDate: function(yyyy, mm, dd) {
		mm = valueParseInt(mm) + 1;
		if(mm == 13) {
			mm = 1;
			yyyy = valueParseInt(yyyy) + 1;
		}
		// 文字列として連結
		return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
	},
	_getPrevDate: function(yyyy, mm, dd) {
		mm = valueParseInt(mm) - 1;
		if(mm <= 0) {
			mm = 12;
			yyyy = valueParseInt(yyyy) - 1;
		}
		// 文字列として連結
		return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
	},
	_getFormat: function(num) {
		return (valueParseInt(num) < 10) ? ("0" + valueParseInt(num)) : "" + num;
	},
	//yy,mm の月の日数を返す
	_getMonthDays: function(yy, mm) {
		if(mm == "02") {
			if ((yy % 4) == 0) {
				return "29";
			} else if ((yy % 100) == 0) {
				return "28";
			} else if ((yy % 400) == 0) {
				return "29";	//閏年の処理終了
			}
		}
		return this.Mdays[mm];
	},
	//曜日のインデックス（0-6）を返す。
	_getWeekDays: function(yyyy,mm,dd) {
		var now = new Date(valueParseInt(yyyy), valueParseInt(mm) - 1, valueParseInt(dd));
		var w = now.getDay();
		return w;
	},
	_render: function() {
		var next_year = this._getNextYear(this.currentYear, this.currentMonth, this.currentDay);
		var prev_year = this._getPrevYear(this.currentYear, this.currentMonth, this.currentDay);
		var next_month = this._getNextDate(this.currentYear, this.currentMonth, this.currentDay);
		var prev_month = this._getPrevDate(this.currentYear, this.currentMonth, this.currentDay);

		var pre_end_date = this._getMonthDays(prev_month.substr(2, 2), prev_month.substr(4, 2));
		var end_date = this._getMonthDays(this.currentYear.substr(2, 2), this.currentMonth);

		var start_w = this._getWeekDays(this.currentYear, this.currentMonth, this.currentDay);
		if(start_w == 0) {
			if(this.options.pre_show_week != 0) {
				var pre_start_date = pre_end_date - 7*this.options.pre_show_week + 1;
			} else {
				var pre_start_date = 0;
			}
		} else {
			var pre_start_date = pre_end_date - 7*this.options.pre_show_week - (start_w - 1);
		}
		var loop_week = valueParseInt(Math.ceil((valueParseInt(end_date) + start_w + 7*this.options.pre_show_week + 7*this.options.next_show_week)  / 7));

		var currentMonth = valueParseInt(this.currentMonth);
		//if(currentMonth < 10) currentMonth = "&nbsp;" + currentMonth;
		switch (currentMonth) {
			case 1: currentMonth = compCalendarLang.month_jan; break;
			case 2: currentMonth = compCalendarLang.month_feb; break;
			case 3: currentMonth = compCalendarLang.month_mar; break;
			case 4: currentMonth = compCalendarLang.month_apr; break;
			case 5: currentMonth = compCalendarLang.month_may; break;
			case 6: currentMonth = compCalendarLang.month_jun; break;
			case 7: currentMonth = compCalendarLang.month_jul; break;
			case 8: currentMonth = compCalendarLang.month_aug; break;
			case 9: currentMonth = compCalendarLang.month_sep; break;
			case 10: currentMonth = compCalendarLang.month_oct; break;
			case 11: currentMonth = compCalendarLang.month_nov; break;
			case 12: currentMonth = compCalendarLang.month_dec; break;
			default:
		}
		var html =
		"<table class=\"compcalendar_top\" summary=\"\"><tr><td class=\"compcalendar_top_td\">" +
			"<table border=\"0\" class=\"compcalendar\" summary=\"" + compCalendarLang.summary + "\">" +
				"<tr>" +
					"<td class=\"compcalendar_title\" colspan=\"7\">" +
						this.currentYear +
						compCalendarLang.year +
						"&nbsp;" +
						currentMonth +
					"</td>" +
				"</tr>" +
				"<tr class=\"compcalendar_button\">" +
					"<td>" +
						"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + prev_year + "'); return false;\" title=\"" + compCalendarLang.title_prev_year + "\">" +
							//<{* « *}>
							compCalendarLang.btn_prev_year +
						"</a>" +
					"</td>" +
					"<td>" +
						"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + prev_month + "'); return false;\" title=\"" + compCalendarLang.title_prev_month + "\">" +
							//<{* ‹ *}>
							compCalendarLang.btn_prev_month +
						"</a>" +
					"</td>" +
					"<td colspan=\"3\">" +
						"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + this.todayYear + this.todayMonth + "01" + "'); return false;\" title=\"" + compCalendarLang.title_today + "\">" +
							//<{* 今日へ *}>
							compCalendarLang.move_today +
						"</a>" +
					"</td>" +
					"<td>" +
						"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + next_month + "'); return false;\" title=\"" + compCalendarLang.title_next_month + "\">" +
							//<{* › *}>
							compCalendarLang.btn_next_month +
						"</a>" +
					"</td>" +
					"<td>" +
						"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + next_year + "'); return false;\" title=\"" + compCalendarLang.title_next_year + "\">" +
							//<{* » *}>
							compCalendarLang.btn_next_year +
						"</a>" +
					"</td>" +
				"</tr>" +
				"<tr class=\"compcalendar_week\">" +
					"<td class=\"compcalendar_sun\">" +
						//<{* 日 *}>
						compCalendarLang.week_sun +
					"</td>" +
					"<td>" +
						//<{* 月 *}>
						compCalendarLang.week_mon +
					"</td>" +
					"<td>" +
						//<{* 火 *}>
						compCalendarLang.week_tue +
					"</td>" +
					"<td>" +
						//<{* 水 *}>
						compCalendarLang.week_wed +
					"</td>" +
					"<td>" +
						//<{* 木 *}>
						compCalendarLang.week_thu +
					"</td>" +
					"<td>" +
						//<{* 金 *}>
						compCalendarLang.week_fri +
					"</td>" +
					"<td class=\"compcalendar_sat\">" +
						//<{* 土 *}>
						compCalendarLang.week_sat +
					"</td>" +
				"</tr>";

				var pre_outside_day = pre_start_date;
				var current_day = 1;
				var post_end_date = 1;
				for (var i = 0; i < loop_week; i++) {
					html += "<tr class=\"compcalendar_day\">";
					for (var j = 0; j < 7; j++) {
						if(pre_outside_day > 0) {
							var day_class = "compcalendar_outside";
							var day = pre_outside_day;
							if(pre_end_date < day+1) {
								pre_outside_day = 0
							} else {
								pre_outside_day++;
							}
							var prefix_day_click = prev_month.substr(0, 6);
						} else if(current_day > 0) {
							var day = current_day;
							if (end_date < day+1) {
								current_day = 0;
							} else {
								current_day++;
							}
							if (j == 0) {
								var day_class="compcalendar_sun";
							} else if(j == 6) {
								var day_class="compcalendar_sat";
							} else {
								var day_class="compcalendar_weekday";
							}
							//<{* 選択中の日付 *}>
							if (this.currentYear == this.selectedYear && this.currentMonth == this.selectedMonth && this.selectedDay == day) {
								day_class += " compcalendar_highlight";
							}
							//<{* 今日の日付 *}>
							if (this.currentYear == this.todayYear && this.currentMonth == this.todayMonth && this.todayDay == day) {
								day_class += " compcalendar_today";
							}
							var prefix_day_click = this.currentYear + this.currentMonth;
						} else {
							var day_class = "compcalendar_outside";
							var day = post_end_date;
							post_end_date++
							var prefix_day_click = next_month.substr(0, 6);
						}
						//<{* Td *}>
						html += "<td class=\"" + day_class + "\">" +
							"<a href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onDayClick('" + prefix_day_click + this._getFormat(day) + "'); return false;\" class=\"compcalendar_link\">" +
								day +
							"</a>" +
						"</td>";
					}
					html += "</tr>";
				}
			html += "</table>" +
		"</td></tr></table>";

		return html;
	},
	_popupClose: function() {
		this.popup.closePopup(this.popup.getPopupElement());
		if(this.text_el.tagName.toLowerCase() == 'input' && this.text_el.type.toLowerCase() == 'text') {
			commonCls.focus(this.text_el);
		}
	}
}