;(function(window){

	//
	window.EditEventManager = function(config)
	{
		this.config = config;
		this.id = this.config.id;
		this.bAMPM = this.config.bAMPM;

		this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
		this.DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"));
		if ((this.DATETIME_FORMAT.substr(0, this.DATE_FORMAT.length) == this.DATE_FORMAT))
			this.TIME_FORMAT = BX.util.trim(this.DATETIME_FORMAT.substr(this.DATE_FORMAT.length));
		else
			this.TIME_FORMAT = BX.date.convertBitrixFormat(this.bAMPM ? 'H:MI:SS T' : 'HH:MI:SS');
		this.TIME_FORMAT_SHORT = this.TIME_FORMAT.replace(':s', '');

		this.bFullDay = false;
		this.bReminder = false;
		this.bAdditional = false;

		var _this = this;

		BX.addCustomEvent('onCalendarLiveFeedShown', function()
		{
			_this.Init();

			_this.defaultValues = {
				remind: {count: 15, type: 'min'}
			};

			_this.config.arEvent = _this.HandleEvent(_this.config.arEvent);
			_this.ShowFormData(_this.config.arEvent);
		});
	};

	window.EditEventManager.prototype = {
		Init: function()
		{
			var _this = this;
			// From-to
			this.pFromToCont = BX('feed-cal-from-to-cont' + this.id);
			this.pFromDate = BX('feed-cal-event-from' + this.id);
			this.pToDate = BX('feed-cal-event-to' + this.id);
			this.pFromTime = BX('feed_cal_event_from_time' + this.id);
			this.pToTime = BX('feed_cal_event_to_time' + this.id);
			this.pFullDay = BX('event-full-day' + this.id);
			//this.pFromTs = BX('event-from-ts' + this.id);
			//this.pToTs = BX('event-to-ts' + this.id);

			// Timezones controls
			this.pDefTimezone = BX('feed-cal-tz-def' + this.id);
			this.pDefTimezoneWrap = BX('feed-cal-tz-def-wrap' + this.id);
			this.pFromTz = BX('feed-cal-tz-from' + this.id);
			this.pToTz = BX('feed-cal-tz-to' + this.id);
			this.pDefTimezone.onchange = BX.proxy(this.DefaultTimezoneOnChange, this);

			this.pTzOuterCont = BX('feed-cal-tz-cont-outer' + this.id);
			this.pTzSwitch = BX('feed-cal-tz-switch' + this.id);
			this.pTzCont = BX('feed-cal-tz-cont' + this.id);
			this.pTzInnerCont = BX('feed-cal-tz-inner-cont' + this.id);
			this.pTzSwitch.onclick = BX.proxy(this.TimezoneSwitch, this);

			this.pFromTz.onchange = BX.proxy(this.TimezoneFromOnChange, this);
			this.pToTz.onchange = BX.proxy(this.TimezoneToOnChange, this);
			// Hints for dialog
			new BX.CHint({parent: BX('feed-cal-tz-tip' + this.id), hint: _this.config.message.eventTzHint});
			new BX.CHint({parent: BX('feed-cal-tz-def-tip' + this.id), hint: _this.config.message.eventTzDefHint});

			//Reminder
			this.pReminderCont = BX('feed-cal-reminder-cont' + this.id);
			this.pReminder = BX('event-reminder' + this.id);

			this.pEventName = BX('feed-cal-event-name' + this.id);
			this.pForm = this.pEventName.form;
			this.pLocation = BX('event-location' + this.id);
			this.pImportance = BX('event-importance' + this.id);
			this.pAccessibility = BX('event-accessibility' + this.id);
			this.pSection = BX('event-section' + this.id);
			this.pRemCount = BX('event-remind_count' + this.id);
			this.pRemType = BX('event-remind_type' + this.id);

			// Location
			if (this.config.meetingRooms)
 			{
				this.Location = new BXInputPopup({
					id: this.id + '_loc_mr',
					values: this.config.meetingRooms,
					input: this.pLocation,
					defaultValue: this.config.message.SelectMR,
					openTitle: this.config.message.OpenMRPage,
					className: 'calendar-inp calendar-inp-time calendar-inp-loc',
					noMRclassName: 'calendar-inp calendar-inp-time calendar-inp-loc'
				});
				this.Loc = {};
				BX.addCustomEvent(this.Location, 'onInputPopupChanged', BX.proxy(this.LocationOnChange, this));
				BX.addClass(this.pLocation, "calendar-inp-time");
				this.Location.Set(false, '');
			}

			// Control events
			this.pFullDay.onclick = BX.proxy(this.FullDay, this);
			this.pReminder.onclick = BX.proxy(this.Reminder, this);

			BX.bind(this.pForm, 'submit', BX.proxy(this.OnSubmit, this));
			// *************** Init events ***************

			BX("feed-cal-additional-show").onclick = BX("feed-cal-additional-hide").onclick = BX.proxy(this.ShowAdditionalParams, this);

			this.InitDateTimeControls();

			var oEditor = window["BXHtmlEditor"].Get(this.config.editorId);
			if (oEditor && oEditor.IsShown())
			{
				this.CustomizeHtmlEditor(oEditor);
			}
			else
			{
				BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function(editor)
				{
					if (editor.id == _this.config.editorId)
					{
						_this.CustomizeHtmlEditor(editor);
					}
				});
			}

			// repeat
			this.pRepeat = BX('event-repeat' + this.id);
			this.pRepeatDetails = BX('event-repeat-details' + this.id);
			this.RepeatDiapTo = BX('event-repeat-to' + this.id);
			this.RepeatDiapToValue = BX('event-repeat-to-value' + this.id);

			this.pRepeat.onchange = function()
			{
				var value = this.value;
				_this.pRepeatDetails.className = "feed-cal-repeat-details feed-cal-repeat-details-" + value.toLowerCase();
			};
			this.pRepeat.onchange();

			this.RepeatDiapTo.onclick = function(){
				BX.calendar({node: this, field: this, bTime: false});
				BX.focus(this);
			};
			this.RepeatDiapTo.onfocus = function()
			{
				if (!this.value || this.value == _this.config.message.NoLimits)
					this.title = this.value = '';
				this.style.color = '#000000';
			};
			this.RepeatDiapTo.onblur = this.RepeatDiapTo.onchange = function()
			{
				if (this.value && this.value != _this.config.message.NoLimits)
				{
					var until = BX.parseDate(this.value);
					if (until && until.getTime)
						_this.RepeatDiapToValue.value = BX.date.getServerTimestamp(until.getTime());
					this.style.color = '#000000';
					this.title = '';
					return;
				}
				this.title = this.value = _this.config.message.NoLimits;
				this.style.color = '#C0C0C0';
			};
			this.RepeatDiapTo.onchange();

			this.eventNode = BX('div' + this.config.editorId);
			if (this.eventNode)
			{
				BX.onCustomEvent(this.eventNode, 'OnShowLHE', ['justShow']);
			}
		},

		CustomizeHtmlEditor: function(editor)
		{
			if (editor.toolbar.controls && editor.toolbar.controls.spoiler)
			{
				BX.remove(editor.toolbar.controls.spoiler.pCont);
			}
		},

		InitDateTimeControls: function()
		{
			var _this = this;
			// Date
			this.pFromDate.onclick = function(){BX.calendar({node: this.parentNode, field: this, bTime: false});};
			this.pToDate.onclick = function(){BX.calendar({node: this.parentNode, field: this, bTime: false});};

			this.pFromDate.onchange = function()
			{
				if(_this._FromDateValue)
				{
					var
						prevF = BX.parseDate(_this._FromDateValue),
						F = BX.parseDate(_this.pFromDate.value),
						T = BX.parseDate(_this.pToDate.value);

					if (F)
					{
						var duration = T.getTime() - prevF.getTime();
						if (duration < 0)
							duration = 0;
						T = new Date(F.getTime() + duration);
						if (T)
							_this.pToDate.value = bxFormatDate(T.getDate(), T.getMonth() + 1, T.getFullYear());
					}
				}
				_this._FromDateValue = _this.pFromDate.value;
			};

			// Time
			this.pFromTime.parentNode.onclick = this.pFromTime.onclick = window['bxShowClock_' + 'feed_cal_event_from_time' + this.id];
			this.pToTime.parentNode.onclick = this.pToTime.onclick = window['bxShowClock_' + 'feed_cal_event_to_time' + this.id];

			this.pFromTime.onchange = function()
			{
				var fromDate = _this.ParseDate(BX.util.trim(_this.pFromDate.value) + ' ' + BX.util.trim(_this.pFromTime.value));
				if (_this.pToDate.value == '')
					_this.pToDate.value = _this.pFromDate.value;

				var toDate = _this.ParseDate(BX.util.trim(_this.pToDate.value) + ' ' + BX.util.trim(_this.pToTime.value));

				if (_this._FromTimeValue)
				{
					var prefFromDate = _this.ParseDate(BX.util.trim(_this.pFromDate.value) + ' ' + _this._FromTimeValue);
					var duration = toDate.getTime() - prefFromDate.getTime();
					if (duration < 0)
						duration = 3600000; // 1 hour

					var newToDate = new Date(fromDate.getTime() + duration);
					_this.pToDate.value = _this.FormatDate(newToDate);
					_this.pToTime.value = _this.FormatTime(newToDate);
				}

				_this._FromTimeValue = _this.pFromTime.value;
			};
		},

		OnSubmit: function(e)
		{
			// Check Meeting and Video Meeting rooms accessibility
			if (this.Loc.NEW.substr(0, 5) == 'ECMR_' && !this.bLocationChecked && window.setBlogPostFormSubmitted)
			{
				var
					_this = this,
					fromDate = this.ParseDate(BX.util.trim(this.pFromDate.value) + ' ' + BX.util.trim(this.pFromTime.value)),
					toDate = this.ParseDate(BX.util.trim(this.pToDate.value) + ' ' + BX.util.trim(this.pToTime.value));

				top.BXCRES_Check = null;
				this.CheckMeetingRoom(
					{
						from : this.FormatDateTime(fromDate),
						to : this.FormatDateTime(toDate),
						location : this.Loc.NEW
					},
					function()
					{
						setTimeout(function()
						{
							var check = top.BXCRES_Check;
							if ((!check || check == 'reserved') && BX("blog-submit-button-save"))
							{
								setBlogPostFormSubmitted(false);
								BX.removeClass(BX("blog-submit-button-save"), 'feed-add-button-load');
							}

							if (!check)
								return alert(_this.config.message.MRReserveErr);

							if (check == 'reserved')
								return alert(_this.config.message.MRNotReservedErr);

							_this.bLocationChecked = true;
							BX('event-location-new' + _this.id).name = _this.pLocation.name;
							BX('event-location-new' + _this.id).value = _this.Loc.NEW;
							_this.pLocation.name = '';
							setBlogPostFormSubmitted(false);
							submitBlogPostForm();
						}, 100);
					}
				);
				return BX.PreventDefault(e);
			}
		},

		HandleEvent: function(oEvent)
		{
			if(oEvent)
			{
				oEvent.DT_FROM_TS = BX.date.getBrowserTimestamp(oEvent.DT_FROM_TS);
				oEvent.DT_TO_TS = BX.date.getBrowserTimestamp(oEvent.DT_TO_TS);

				if (oEvent.DT_FROM_TS > oEvent.DT_TO_TS)
					oEvent.DT_FROM_TS = oEvent.DT_TO_TS;

				if ((oEvent.RRULE && oEvent.RRULE.FREQ && oEvent.RRULE.FREQ != 'NONE'))
				{
					oEvent['~DT_FROM_TS'] = BX.date.getBrowserTimestamp(oEvent['~DT_FROM_TS']);
					oEvent['~DT_TO_TS'] = BX.date.getBrowserTimestamp(oEvent['~DT_TO_TS']);

					if (oEvent.RRULE && oEvent.RRULE.UNTIL)
						oEvent.RRULE.UNTIL = BX.date.getBrowserTimestamp(oEvent.RRULE.UNTIL);
				}
			}
			return oEvent;
		},

		ShowFormData: function(oEvent)
		{
			var bNew = false;
			if (!oEvent || !oEvent.ID)
			{
				bNew = true;
				oEvent = {};
			}

			// Name
			this.pEventName.value = oEvent.NAME || '';

			this.linkFromToTz = true;
			this.linkFromToDefaultTz = true;

			// Default Timezone
			if (this.config.userTimezoneName)
			{
				this.pDefTimezoneWrap.style.display = 'none';
				this.pDefTimezone.value = this.config.userTimezoneName;
				this.pFromTz.value = this.pToTz.value = this.config.userTimezoneName;
			}
			else
			{
				this.pDefTimezoneWrap.style.display = '';
				this.pFromTz.value = this.pToTz.value = this.pDefTimezone.value = this.config.userTimezoneDefault || '';
			}

			// Dafault values for from-to fields
			var dateFrom = this.GetUsableDateTime(new Date().getTime(), 30);
			var dateTo = this.GetUsableDateTime(dateFrom.getTime() + 3600000 /* one hour*/, 30);

			this.pFromDate.value = this.FormatDate(dateFrom);
			this.pToDate.value = this.FormatDate(dateTo);
			this.pFromTime.value = this.FormatTime(dateFrom);
			this.pToTime.value = this.FormatTime(dateTo);

			this._FromDateValue = this.pFromDate.value;
			this._FromTimeValue = this.pFromTime.value;

			// Default Timezone
			if (this.config.userTimezoneName)
			{
				this.pDefTimezoneWrap.style.display = 'none';
				this.pDefTimezone.value = this.config.userTimezoneName;
				this.pFromTz.value = this.pToTz.value = this.config.userTimezoneName;
			}
			else
			{
				this.pDefTimezoneWrap.style.display = '';
				this.pFromTz.value = this.pToTz.value = this.pDefTimezone.value = this.config.userTimezoneDefault || '';
			}

			this.pFullDay.checked = oEvent.DT_SKIP_TIME == "Y";
			this.FullDay(false, oEvent.DT_SKIP_TIME !== "Y");

			if (bNew)
			{
				this.pLocation.value = '';
				if (this.Location)
				{
					this.Location.Set(false, '');
				}

				this.pImportance.value = 'normal';
				this.pAccessibility.value = 'busy';
				if (this.pSection.options && this.pSection.options.length > 0)
					this.pSection.value = this.pSection.options[0].value;

				this.pReminder.checked = !!this.defaultValues.remind;
				this.pRemCount.value = (this.defaultValues.remind && this.defaultValues.remind.count) || '15';
				this.pRemType.value = (this.defaultValues.remind && this.defaultValues.remind.type) || 'min';
			}
			else
			{
				this.pLocation.value = oEvent.LOCATION;
				this.pImportance.value = oEvent.IMPORTANCE;
				this.pAccessibility.value = oEvent.ACCESSIBILITY;
				this.pSection.value = oEvent.SECT_ID;

				// Remind
				this.pReminder.checked = oEvent.REMIND && oEvent.REMIND[0];
				this.pRemCount.value = oEvent.REMIND[0].count;
				this.pRemType.value = oEvent.REMIND[0].type;
			}
			this.Reminder(false, true);

			var _this = this;
			setTimeout(function()
			{
				BX.focus(_this.pEventName);
			}, 100);
		},

		FullDay: function(bSaveOption, value)
		{
			if (value == undefined)
				value = !this.bFullDay;

			if (value)
				BX.removeClass(this.pFromToCont, 'feed-cal-full-day');
			else
				BX.addClass(this.pFromToCont, 'feed-cal-full-day');
			this.bFullDay = value;
		},

		Reminder: function(bSaveOption, value)
		{
			if (value == undefined)
				value = !this.bReminder;

			this.pReminderCont.className = value ? 'feed-event-reminder' : 'feed-event-reminder-collapsed';

			this.bReminder = value;
		},

		ShowAdditionalParams: function()
		{
			var value = !this.bAdditional;
			if (!this.pAdditionalCont)
				this.pAdditionalCont = BX("feed-cal-additional");

			if (value)
				BX.removeClass(this.pAdditionalCont, 'feed-event-additional-hidden');
			else
				BX.addClass(this.pAdditionalCont, 'feed-event-additional-hidden');

			this.bAdditional = value;
		},

		ParseTime: function(str)
		{
			var h, m, arTime;
			str = BX.util.trim(str);
			str = str.toLowerCase();

			if (this.bAMPM)
			{
				var ampm = 'pm';
				if (str.indexOf('am') != -1)
					ampm = 'am';

				str = str.replace(/[^\d:]/ig, '');
				arTime = str.split(':');
				h = parseInt(arTime[0] || 0, 10);
				m = parseInt(arTime[1] || 0, 10);

				if (h == 12)
				{
					if (ampm == 'am')
						h = 0;
					else
						h = 12;
				}
				else if (h != 0)
				{
					if (ampm == 'pm' && h < 12)
					{
						h += 12;
					}
				}
			}
			else
			{
				arTime = str.split(':');
				h = arTime[0] || 0;
				m = arTime[1] || 0;

				if (h.toString().length > 2)
					h = parseInt(h.toString().substr(0, 2));
				m = parseInt(m);
			}

			if (isNaN(h) || h > 24)
				h = 0;
			if (isNaN(m) || m > 60)
				m = 0;

			return {h: h, m: m};
		},

		TimezoneSwitch: function()
		{
			if(this.pTzCont.offsetHeight > 0)
			{
				this.pTzCont.style.height = 0;
				BX.removeClass(this.pTzOuterCont, 'feed-ev-timezone-outer-wrap-opened');
			}
			else
			{
				this.pTzCont.style.height = this.pTzInnerCont.offsetHeight + 'px';
				BX.addClass(this.pTzOuterCont, 'feed-ev-timezone-outer-wrap-opened');
			}
		},

		DefaultTimezoneOnChange: function()
		{
			var defTimezoneName = this.pDefTimezone.value;
			BX.userOptions.save('calendar', 'timezone_name', 'timezone_name', defTimezoneName);
			if (this.linkFromToDefaultTz)
				this.pToTz.value = this.pFromTz.value = this.pDefTimezone.value;
		},

		TimezoneFromOnChange: function()
		{
			if (this.linkFromToTz)
				this.pToTz.value = this.pFromTz.value;
			this.linkFromToDefaultTz = false;
		},

		TimezoneToOnChange: function()
		{
			this.linkFromToTz = false;
			this.linkFromToDefaultTz = false;
		},

		FormatDate: function(date)
		{
			return BX.date.format(this.DATE_FORMAT, date.getTime() / 1000);
		},

		FormatTime: function(date, seconds)
		{
			return BX.date.format(seconds === true ? this.TIME_FORMAT : this.TIME_FORMAT_SHORT, date.getTime() / 1000);
		},

		FormatDateTime: function(date)
		{
			return BX.date.format(this.DATETIME_FORMAT, date.getTime() / 1000);
		},

		GetUsableDateTime: function(timestamp, roundMin)
		{
			var r = (roundMin || 10) * 60 * 1000;
			timestamp = Math.ceil(timestamp / r) * r;
			return new Date(timestamp);
		},

		ParseDate: function(str, trimSeconds)
		{
			var bUTC = false;
			var format = BX.message('FORMAT_DATETIME');

			if (trimSeconds !== false)
				format = format.replace(':SS', '');

			if (BX.type.isNotEmptyString(str))
			{
				var regMonths = '';
				for (i = 1; i <= 12; i++)
				{
					regMonths = regMonths + '|' + BX.message('MON_'+i);
				}

				var expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig');
				var aDate = str.match(expr),
					aFormat = BX.message('FORMAT_DATE').match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
					i, cnt,
					aDateArgs=[], aFormatArgs=[],
					aResult={};

				if (!aDate)
					return null;

				if(aDate.length > aFormat.length)
				{
					aFormat = format.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
				}

				for(i = 0, cnt = aDate.length; i < cnt; i++)
				{
					if(BX.util.trim(aDate[i]) != '')
					{
						aDateArgs[aDateArgs.length] = aDate[i];
					}
				}

				for(i = 0, cnt = aFormat.length; i < cnt; i++)
				{
					if(BX.util.trim(aFormat[i]) != '')
					{
						aFormatArgs[aFormatArgs.length] = aFormat[i];
					}
				}

				var m = BX.util.array_search('MMMM', aFormatArgs);
				if (m > 0)
				{
					aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
					aFormatArgs[m] = "MM";
				}
				else
				{
					m = BX.util.array_search('M', aFormatArgs);
					if (m > 0)
					{
						aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
						aFormatArgs[m] = "MM";
					}
				}

				for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
				{
					var k = aFormatArgs[i].toUpperCase();
					aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
				}

				if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
				{
					var d = new Date();

					if(bUTC)
					{
						d.setUTCDate(1);
						d.setUTCFullYear(aResult['YYYY']);
						d.setUTCMonth(aResult['MM'] - 1);
						d.setUTCDate(aResult['DD']);
						d.setUTCHours(0, 0, 0);
					}
					else
					{
						d.setDate(1);
						d.setFullYear(aResult['YYYY']);
						d.setMonth(aResult['MM'] - 1);
						d.setDate(aResult['DD']);
						d.setHours(0, 0, 0);
					}

					if(
						(!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G']))
							&& !isNaN(aResult['MI'])
						)
					{
						if (!isNaN(aResult['H']) || !isNaN(aResult['G']))
						{
							var bPM = (aResult['T']||aResult['TT']||'am').toUpperCase()=='PM';
							var h = parseInt(aResult['H']||aResult['G']||0, 10);
							if(bPM)
							{
								aResult['HH'] = h + (h == 12 ? 0 : 12);
							}
							else
							{
								aResult['HH'] = h < 12 ? h : 0;
							}
						}
						else
						{
							aResult['HH'] = parseInt(aResult['HH']||aResult['GG']||0, 10);
						}

						if (isNaN(aResult['SS']))
							aResult['SS'] = 0;

						if(bUTC)
						{
							d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
						else
						{
							d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
					}

					return d;
				}
			}
		},

		LocationOnChange: function(oLoc, ind, value)
		{
			this.pLocation.className = 'calendar-inp calendar-inp-time calendar-inp-loc';
			if (ind === false)
			{
				this.Loc.NEW = value || '';
			}
			else
			{
				this.Loc.NEW = 'ECMR_' + this.config.meetingRooms[ind].ID;
			}
		},

		CheckMeetingRoom: function(params, callback)
		{
			params.bx_event_calendar_check_meeting_room = 'Y';
			params.sessid = BX.bitrix_sessid();
			BX.ajax.get(
				'/bitrix/components/bitrix/calendar.livefeed.edit/ajax_action.php',
				params,
				function()
				{
					if (callback && typeof callback == 'function')
						callback();
					return true;
				}
			);
		}
	};

	// Calbacks for destination
	window.BXEvDestSetLinkName = function(name)
	{
		if (BX.SocNetLogDestination.getSelectedCount(name) <= 0)
			BX('feed-event-dest-add-link').innerHTML = BX.message("BX_FPD_LINK_1");
		else
			BX('feed-event-dest-add-link').innerHTML = BX.message("BX_FPD_LINK_2");
	};

	window.BXEvDestSelectCallback = function(item, type, search)
	{
		var
			type1 = type,
			prefix = 'S';

		if (type == 'sonetgroups')
			prefix = 'SG';
		else if (type == 'groups')
		{
			prefix = 'UA';
			type1 = 'all-users';
		}
		else if (type == 'users')
			prefix = 'U';
		else if (type == 'department')
			prefix = 'DR';

		BX('feed-event-dest-item').appendChild(
			BX.create("span", { attrs : { 'data-id' : item.id }, props : { className : "feed-event-destination feed-event-destination-"+type1 }, children: [
				BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'EVENT_PERM[' + prefix + '][]', 'value' : item.id }}),
				BX.create("span", { props : { 'className' : "feed-event-destination-text" }, html : item.name}),
				BX.create("span", { props : { 'className' : "feed-event-del-but"}, events : {'click' : function(e){BX.SocNetLogDestination.deleteItem(item.id, type, destinationFormName);BX.PreventDefault(e)}, 'mouseover' : function(){BX.addClass(this.parentNode, 'feed-event-destination-hover')}, 'mouseout' : function(){BX.removeClass(this.parentNode, 'feed-event-destination-hover')}}})
			]})
		);

		BX('feed-event-dest-input').value = '';
		BXEvDestSetLinkName(destinationFormName);
	};

	// remove block
	window.BXEvDestUnSelectCallback = function(item, type, search)
	{
		var elements = BX.findChildren(BX('feed-event-dest-item'), {attribute: {'data-id': ''+item.id+''}}, true);
		if (elements != null)
		{
			for (var j = 0; j < elements.length; j++)
				BX.remove(elements[j]);
		}
		BX('feed-event-dest-input').value = '';
		BXEvDestSetLinkName(destinationFormName);
	};
	window.BXEvDestOpenDialogCallback = function()
	{
		BX.style(BX('feed-event-dest-input-box'), 'display', 'inline-block');
		BX.style(BX('feed-event-dest-add-link'), 'display', 'none');
		BX.focus(BX('feed-event-dest-input'));
	};

	window.BXEvDestCloseDialogCallback = function()
	{
		if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-event-dest-input').value.length <= 0)
		{
			BX.style(BX('feed-event-dest-input-box'), 'display', 'none');
			BX.style(BX('feed-event-dest-add-link'), 'display', 'inline-block');
			BXEvDestDisableBackspace();
		}
	};

	window.BXEvDestCloseSearchCallback = function()
	{
		if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-event-dest-input').value.length > 0)
		{
			BX.style(BX('feed-event-dest-input-box'), 'display', 'none');
			BX.style(BX('feed-event-dest-add-link'), 'display', 'inline-block');
			BX('feed-event-dest-input').value = '';
			BXEvDestDisableBackspace();
		}

	};
	window.BXEvDestDisableBackspace = function()
	{
		if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
			BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);

		BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(e)
		{
			if (e.keyCode == 8)
			{
				BX.PreventDefault(e);
				return false;
			}
		});
		setTimeout(function()
		{
			BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
			BX.SocNetLogDestination.backspaceDisable = null;
		}, 5000);
	};

	window.BXEvDestSearchBefore = function(event)
	{
		return BX.SocNetLogDestination.searchBeforeHandler(event, {
			formName: destinationFormName,
			inputId: 'feed-event-dest-input'
		});
	};
	window.BXEvDestSearch = function(event)
	{
		return BX.SocNetLogDestination.searchHandler(event, {
			formName: destinationFormName,
			inputId: 'feed-event-dest-input',
			linkId: 'feed-event-dest-add-link',
			sendAjax: true
		});
	};

	function bxFormatDate(d, m, y)
	{
		var str = BX.message("FORMAT_DATE");

		str = str.replace(/YY(YY)?/ig, y);
		str = str.replace(/MMMM/ig, BX.message('MONTH_' + this.Number(m)));
		str = str.replace(/MM/ig, zeroInt(m));
		str = str.replace(/M/ig, BX.message('MON_' + this.Number(m)));
		str = str.replace(/DD/ig, zeroInt(d));

		return str;
	}

	function zeroInt(x)
	{
		x = parseInt(x, 10);
		if (isNaN(x))
			x = 0;
		return x < 10 ? '0' + x.toString() : x.toString();
	}

	function bxGetDateFromTS(ts, getObject)
	{
		var oDate = new Date(ts);
		if (!getObject)
		{
			var
				ho = oDate.getHours() || 0,
				mi = oDate.getMinutes() || 0;

			oDate = {
				date: oDate.getDate(),
				month: oDate.getMonth() + 1,
				year: oDate.getFullYear(),
				bTime: !!(ho || mi),
				oDate: oDate
			};

			if (oDate.bTime)
			{
				oDate.hour = ho;
				oDate.min = mi;
			}
		}

		return oDate;
	}

	function getUsableDateTime(timestamp, roundMin)
	{
		var r = (roundMin || 10) * 60 * 1000;
		timestamp = Math.ceil(timestamp / r) * r;
		return bxGetDateFromTS(timestamp);
	}

	function formatTimeByNum(h, m, bAMPM)
	{
		var res = '';
		if (m == undefined)
			m = '00';
		else
		{
			m = parseInt(m, 10);
			if (isNaN(m))
				m = '00';
			else
			{
				if (m > 59)
					m = 59;
				m = (m < 10) ? '0' + m.toString() : m.toString();
			}
		}

		h = parseInt(h, 10);
		if (h > 24)
			h = 24;
		if (isNaN(h))
			h = 0;

		if (bAMPM)
		{
			var ampm = 'am';

			if (h == 0)
			{
				h = 12;
			}
			else if (h == 12)
			{
				ampm = 'pm';
			}
			else if (h > 12)
			{
				ampm = 'pm';
				h -= 12;
			}

			res = h.toString() + ':' + m.toString() + ' ' + ampm;
		}
		else
		{
			res = ((h < 10) ? '0' : '') + h.toString() + ':' + m.toString();
		}
		return res;
	}


})(window);


