;(function() {
	var BX = window.BX,
		BXMobileApp = window.BXMobileApp;
	if (BX && BX["Mobile"] && BX["Mobile"]["Grid"] && BX["Mobile"]["Grid"]["Form"])
		return;
	BX.namespace("BX.Mobile.Grid.Form");
	var repo = {formId : {}, gridId : {}},
		initSelect = (function () {
			var d = function(select, eventNode, container) {
				this.click = BX.delegate(this.click, this);
				this.callback = BX.delegate(this.callback, this);
				this.init(select, eventNode, container);
			};
			d.prototype = {
				multiple : false,
				select : null,
				eventNode : null,
				container : null,
				init : function(select, eventNode, container) {
					if (BX(select) && select.options.length > 0 &&
						BX(eventNode) && BX(container)/* && !select.hasAttribute("bx-bound")*/)
					{
						select.setAttribute("bx-bound", "Y");
						this.select = select;
						this.eventNode = eventNode;
						this.container = container;
						BX.bind(this.eventNode, "click", this.click);
						this.multiple = select.hasAttribute("multiple");
						this.initValues();
					}
				},
				initValues: function() {
					this.titles = [];
					this.values = [];
					this.defaultTitles = [];
					for (var ii = 0; ii < this.select.options.length; ii++)
					{
						this.titles.push(this.select.options[ii].innerHTML);
						this.values.push(this.select.options[ii].value);
						if (this.select.options[ii].hasAttribute("selected"))
							this.defaultTitles.push(this.select.options[ii].innerHTML);

					}
				},
				click : function(e) {
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
					BXMobileApp.UI.SelectPicker.show({
						callback: this.callback,
						values: this.titles,
						multiselect: this.multiple,
						default_value : this.defaultTitles
					});
				},
				callback : function(data) {
					this.defaultTitles = [];
					if (data && data.values && data.values.length > 0)
					{
						var keys = [], ii, jj;
						for (ii = 0; ii < this.titles.length; ii++)
						{
							for (jj = 0; jj < data.values.length; jj++)
							{
								if (this.titles[ii] == data.values[jj])
								{
									keys.push(this.values[ii]);
									this.defaultTitles.push(this.titles[ii]);
									break;
								}
							}
						}
						var html = '';
						for (ii = 0; ii < this.select.options.length; ii++)
						{
							this.select.options[ii].removeAttribute("selected");

							if (BX.util.in_array(this.select.options[ii].value, keys))
							{
								this.select.options[ii].setAttribute("selected", "selected");
								if (this.multiple)
								{
									html += '<a href="javascript:void();">' + this.select.options[ii].innerHTML + '</a>';
								}
								else
								{
									html = this.select.options[ii].innerHTML;
								}
							}
						}
						if (html === '' && !this.multiple)
							html = '<span style="color:grey">' + BX.message("interface_form_select") + '</span>';
						this.container.innerHTML = html;
						BX.onCustomEvent(this, "onChange", [this, this.select]);
					}
				}
			};
			return d;
		})(),
		initDatetime = (function () {
		var d = function(node, type, container, formats) {
				this.type = type;
				this.node = node;
				this.container = container;
				this.click = BX.delegate(this.click, this);
				this.callback = BX.delegate(this.callback, this);
				BX.bind(this.container, "click", this.click);
				this.init(formats);
			};
			d.prototype = {
				type : 'datetime', // 'datetime', 'date', 'time'
				format : {
					inner : {
						datetime : 'dd.MM.yyyy H:mm',
						time : 'H:mm',
						date : 'dd.MM.yyyy'
					},
					bitrix : {
						datetime : null,
						time : null,
						date : null
					},
					visible : {
						datetime : null,
						time : null,
						date : null
					}
				},
				node : null,
				click : function(e) {
					BX.eventCancelBubble(e);
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
					var res = {
						type: this.type,
						start_date: this.getStrDate(this.node.value),
						format: this.format.inner[this.type],
						callback: this.callback
					};
					if (res["start_date"] == "")
						delete res["start_date"];
					BXMobileApp.UI.DatePicker.setParams(res);
					BXMobileApp.UI.DatePicker.show();
				},
				callback : function(data) {
					this.node.value = data;
					/*var d = this.makeDate(data);
					this.node.value = BX.date.format(this.format.bitrix[this.type], d);
					var text = BX.date.format(this.format.visible[this.type], d);
					if (!BX.type.isNotEmptyString(text))
						text = this.container.getAttribute("placeholder") || ' ';*/
					this.container.innerHTML = data;
					this.delButton.style.display = "inline-block";
					BX.onCustomEvent(this, "onChange", [this, this.node]);
				},
				makeDate : function(str) {

					//Format: "day.month.year hour:minute"
					var d = new Date();
					if (BX.type.isNotEmptyString(str))
					{
						var dateR = new RegExp("(\\d{2}).(\\d{2}).(\\d{4})"),
							timeR = new RegExp("(\\d{2}):(\\d{2})"),
							m;
						if (dateR.test(str) && (m = dateR.exec(str)) && m)
						{
							d.setDate(m[1]);
							d.setMonth((m[2]-1));
							d.setFullYear(m[3])
						}
						if (timeR.test(str) && (m = timeR.exec(str)) && m)
						{
							d.setHours(m[1]);
							d.setMinutes(m[2]);
						}
					}

					return d;
				},
				getStrDate : function(value) {
					var d = BX.parseDate(value), res = '';
					if (d !== null)
					{
						if (this.type == 'date' || this.type == 'datetime')
						{
							res = BX.util.str_pad_left(d.getDate().toString(), 2, "0") + '.' +
								BX.util.str_pad_left(d.getMonth().toString(), 2, "0") + '.' +
								d.getFullYear().toString();
						}
						if (this.type == 'datetime')
							res += ' ';
						if (this.type == 'time' || this.type == 'datetime')
						{
							res += BX.util.str_pad_left(d.getHours().toString(), 2, "0") + ':' + d.getMinutes().toString();
						}
					}
					return res;
				},
				init : function(formats) {
					var DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME")),
						DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE")),
						TIME_FORMAT;
					if ((DATETIME_FORMAT.substr(0, DATE_FORMAT.length) == DATE_FORMAT))
						TIME_FORMAT = BX.util.trim(DATETIME_FORMAT.substr(DATE_FORMAT.length));
					else
						TIME_FORMAT = BX.date.convertBitrixFormat(DATETIME_FORMAT.indexOf('T') >= 0 ? 'H:MI:SS T' : 'HH:MI:SS');
					this.format.bitrix.datetime = DATETIME_FORMAT;

					this.format.bitrix.date = DATE_FORMAT;
					this.format.bitrix.time = TIME_FORMAT;

					formats = (formats || {});

					this.format.visible.datetime = (formats["datetime"] || DATETIME_FORMAT.replace(':s', ''));
					this.format.visible.date = (formats["date"] || DATE_FORMAT);
					this.format.visible.time = (formats["time"] || TIME_FORMAT.replace(':s', ''));
					this.format.visible.datetime = [
						["today", "today, " + this.format.visible.time],
						["tommorow", "tommorow, " + this.format.visible.time ],
						["yesterday", "yesterday, " + this.format.visible.time],
						["" , this.format.visible.datetime]
					];
					this.format.visible.date = [
						["today", "today"],
						["tommorow", "tommorow"],
						["yesterday", "yesterday"],
						["" , this.format.visible.date]
					];

					this.delButton = BX(this.node.id + '_del');
					BX.bind(this.delButton, "click", BX.proxy(function(){
						this.drop();
					}, this));
				},
				drop : function()
				{
					this.node.value = "";
					this.container.innerHTML = this.container.getAttribute("placeholder");
					this.delButton.style.display = "none";
				}
			};
			return d;
		})(),
		initSelectUser = (function () {
		var d = function(select, eventNode, container) {
			this.click = BX.delegate(this.click, this);
			this.callback = BX.delegate(this.callback, this);
			this.drop = BX.delegate(this.drop, this);
			this.select = BX(select);
			this.eventNode = BX(eventNode);
			this.container = BX(container);
			BX.bind(this.eventNode, "click", this.click);
			this.multiple = select.hasAttribute("multiple");
			this.showDrop = !(select.hasAttribute("bx-can-drop") && select.getAttribute("bx-can-drop").toString() == "false");
			this.urls = {
				"list" : BX.message('SITE_DIR') + 'mobile/index.php?mobile_action=get_user_list',
				"profile" : BX.message("interface_form_user_url")
			};
			this.actualizeNodes();
		};
			d.prototype = {
				multiple : false,
				select : null,
				eventNode : null,
				container : null,
				showDrop : true,
				showMenu : false,
				click : function(e) {
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
					(new BXMobileApp.UI.Table({
						url: this.urls.list,
						table_settings : {
							callback: this.callback,
							markmode: true,
							multiple: this.multiple,
							return_full_mode: true,
							skipSpecialChars : true,
							modal: true,
							alphabet_index: true,
							outsection: false,
							okname: BX.message("interface_form_select"),
							cancelname: BX.message("interface_form_cancel")
						}
					}, "users")).show();
				},
				drop : function() {
					var node = BX.proxy_context,
						id = node.id.replace(this.select.id + '_del_', '');
					for (var ii = 0;  ii < this.select.options.length; ii++)
					{
						if ((this.select.options[ii].value + '') == (id + ''))
						{
							BX.remove(BX.findParent(node, {"tagName" : "DIV", "className" : "mobile-grid-field-select-user-item"}));
							BX.remove(this.select.options[ii]);
						}
					}
					BX.onCustomEvent(this, "onChange", [this, this.select]);
				},
				actualizeNodes : function() {
					for (var ii = 0;  ii < this.select.options.length; ii++)
					{
						if (BX(this.select.id + '_del_' + this.select.options[ii].value))
						{
							BX.bind(BX(this.select.id + '_del_' + this.select.options[ii].value), "click", this.drop);
						}
					}
				},
				buildNodes : function(items) {
					var options = '',
						html = '',
						ii,
						user, existedUsers = [];
					for (ii = 0; ii < this.select.options.length; ii++)
					{
						existedUsers.push(this.select.options[ii].value.toString());
					}
					for (ii = 0; ii < Math.min((this.multiple ? items.length : 1), items.length); ii++)
					{
						user = items[ii];
						if (BX.util.in_array(user["ID"], existedUsers))
							continue;

						options += '<option value="' + user["ID"] + '" selected>"' + BX.util.htmlspecialchars(user["NAME"]) + '"</option>';
						html += ([
							'<div class="mobile-grid-field-select-user-item-outer">',
								'<div class="mobile-grid-field-select-user-item">',
									(this.showDrop ? '<del id="' + this.select.id + '_del_' + user["ID"] + '"></del>' : ''),
									'<div class="avatar"', (user["IMAGE"] ? ' style="background-image:url(\'' + user["IMAGE"] + '\')"' : ''), '></div>',
									'<span onclick="BXMobileApp.PageManager.loadPageBlank({url: \'' +  this.urls.profile.replace("#ID#", user["ID"]) + '\',bx24ModernStyle : true});">' + BX.util.htmlspecialchars(user["NAME"]) + '</span>',
								'</div>',
							'</div>'
						].join('').replace(' style="background-image:url(\'\')"', ''));
					}

					if (html != '')
					{
						this.select.innerHTML = (this.multiple ? this.select.innerHTML : '') + options;
						this.container.innerHTML = (this.multiple ? this.container.innerHTML : '') + html;
						BX.onCustomEvent(this, "onChange", [this, this.select]);
						var ij = 0,
							f = BX.proxy(function() {
							if (ij < 100)
							{
								if (this.container.childNodes.length > 0)
									this.actualizeNodes();
								else if (ij++)
									setTimeout(f, 50);
							}
						}, this);
						setTimeout(f, 50);
					}
				},
				callback : function(data) {
					if (data && data.a_users)
						this.buildNodes(data.a_users);
				}
			};
			return d;
		})(),
		initSelectGroup = (function () {
			var d = function(select, eventNode, container) {
				initSelectGroup.superclass.constructor.apply(this, arguments);
				this.urls = {
					list : BX.message('SITE_DIR') + 'mobile/index.php?mobile_action=get_group_list',
					profile : BX.message("interface_form_group_url")
				};
			};
			BX.extend(d, initSelectUser);
			d.prototype.callback = function(data) {
				if (data && data.b_groups)
					this.buildNodes(data.b_groups);
			};
			return d;
		})(),
		initText = (function () {
			var d = function(node, container) {
				this.node = node;
				this.container = container;
				this.click = BX.delegate(this.click, this);
				this.callback = BX.delegate(this.callback, this);
				BX.bind(this.container, "click", this.click);
			};
			d.prototype = {
				click : function(e) {
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
						window.app.exec('showPostForm', {
						attachButton : { items : []},
						attachFileSettings: {},
						attachedFiles : [],
						extraData: {},
						mentionButton: {},
						smileButton: {},
						message : { text : BX.util.htmlspecialcharsback(this.node.value) },
						okButton: {
							callback: this.callback,
							name: BX.message("interface_form_save")
						},
						cancelButton : {
							callback : function(){},
							name : BX.message("interface_form_cancel")
						}
					});
				},
				callback: function(data) {
					data.text = (BX.util.htmlspecialchars(data.text) || '');
					this.node.value = data.text;
					if (data.text == '')
						this.container.innerHTML = '<span class="placeholder">' + this.node.getAttribute("placeholder") + "</span>";
					else
						this.container.innerHTML = data.text;
					BX.onCustomEvent(this, "onChange", [this, this.node]);
				}
			};
			return d;
		})(),
		initBox = (function () {
			var d = function(node) {
				this.node = node;
				BX.bind(this.node, "change", BX.delegate(this.change, this));
			};
			d.prototype = {
				change : function() {
					BX.onCustomEvent(this, "onChange", [this, this.node]);
				}
			};
			return d;
		})();
	window.app.exec("enableCaptureKeyboard", true);
	BX.Mobile.Grid.Form = function(params) {
		BXMobileApp.UI.Page.LoadingScreen.hide();
		if (typeof params === "object")
		{
			this.gridId = params["gridId"] || "";
			this.formId = params["formId"] || "";
			if (this.gridId != '')
				repo["gridId"][this.gridId] = this;
			if (this.formId != '')
				repo["formId"][this.formId] = this;
			this.formats = params["formats"] || null;
			var nodes = params["customElements"] || [], node, obj;
			this.apply = BX.delegate(this.apply, this);
			this.restrictedMode = params["restrictedMode"];

			while ((node = nodes.pop()) && node)
			{
				if ((obj = this.bindElement(BX(node))) && obj)
				{
					this.elements.push(obj);
					if (params["restrictedMode"])
						BX.addCustomEvent(obj, "onChange", this.apply);
				}
			}
			if (BX(this.formId) && BX('submit_' + this.formId))
			{
				BX.bind(BX('submit_' + this.formId), "click", BX.delegate(this.click, this));
				BX.bind(BX('cancel_' + this.formId), "click", BX.delegate(this.cancel, this));
			}
			else if (params["buttons"] == "app")
			{
				window.BXMobileApp.UI.Page.TopBar.updateButtons({
					cancel: {
						type: "back_text", // @param buttons.type The type of the button (plus|back|refresh|right_text|back_text|users|cart)
						callback: BX.delegate(this.cancel, this),
						name: BX.message("interface_form_cancel"),
						bar_type: "navbar", //("toolbar"|"navbar")
						position: "left"//("left"|"right")
					},
					ok: {
						type: "back_text", // @param buttons.type The type of the button (plus|back|refresh|right_text|back_text|users|cart)
						callback: BX.delegate(this.click, this),
						name: BX.message("interface_form_save"),
						bar_type: "navbar", //("toolbar"|"navbar")
						position: "right"//("left"|"right")
					}
				});
			}
			if (BX('buttons_' + this.formId))
			{
				var formId = this.formId;
				BX.addCustomEvent("onKeyboardWillShow", function() { BX.addClass(BX('buttons_' + formId), "mobile-grid-button-panel-regular"); });
				BX.addCustomEvent("onKeyboardDidHide", function() { BX.removeClass(BX('buttons_' + formId), "mobile-grid-button-panel-regular"); });
			}
		}
	};
	BX.Mobile.Grid.Form.prototype = {
		elements : [],
		bindElement : function(node) {
			var result = null;
			if (BX(node))
			{
				var tag = node.tagName.toLowerCase(),
					type = (node.hasAttribute("bx-type") ? node.getAttribute("bx-type").toLowerCase() : "");

				if (tag == 'select' && node.getAttribute("bx-type") == 'select-user')
				{
					result = new initSelectUser(node, BX(node.id + '_select'), BX(node.id + '_target'));
				}
				else if (tag == 'select' && node.getAttribute("bx-type") == 'select-group')
				{
					result = new initSelectGroup(node, BX(node.id + '_select'), BX(node.id + '_target'));
				}
				else if (tag == 'select')
				{
					result = new initSelect(node, BX(node.id + '_select'), (node.hasAttribute("multiple") ? BX(node.id + '_target') : BX(node.id + '_select')));
				}
				else if (node.getAttribute("type") == "text")
				{
					BX.bind(node, "keyup", function(e) {
						e = (e||window.event);
						if (e && e.keyCode == 13)
						{
							var ii, found = false;
							BX.eventCancelBubble(e);
							for (ii = 0; ii < node.form.elements.length; ii++)
							{
								if (found)
								{
									if (node.form.elements[ii].tagName.toLowerCase() == 'textarea' || node.form.elements[ii].tagName.toLowerCase() == 'input' && node.form.elements[ii].getAttribute("type").toLowerCase() == "text")
									{
										BX.focus(node.form.elements[ii]);
									}
									break;
								}
								found = (node.form.elements[ii] == node);
							}
						}
					});
				}
				else if (tag == 'textarea')
				{

				}
				else if (node.getAttribute("type") == "checkbox" || node.getAttribute("type") == "radio")
				{
					result = new initBox(node);
				}
				else if (type == 'text' || type == 'textarea')
				{
					result = new initText(node, BX(node.id + '_target'));
				}
				else if (type == 'date' || type == 'datetime' || type == 'time')
				{
					result = new initDatetime(node, type, BX(node.id + '_container'), this.format);
				}
				else if (type == 'section')
				{
					BX.bind(node, "click", function(e){
						BX.PreventDefault(e);
						if (BX.hasClass(node, "mobile-grid-field-expanded-head"))
						{
							BX.removeClass(node, "mobile-grid-field-expanded-head");
							BX.removeClass(node.nextSibling, "mobile-grid-field-expanded-body");
							BX.addClass(node, "mobile-grid-field-collapsed-head");
							BX.addClass(node.nextSibling, "mobile-grid-field-collapsed-body");
						}
						else
						{
							BX.removeClass(node, "mobile-grid-field-collapsed-head");
							BX.removeClass(node.nextSibling, "mobile-grid-field-collapsed-body");
							BX.addClass(node, "mobile-grid-field-expanded-head");
							BX.addClass(node.nextSibling, "mobile-grid-field-expanded-body");
						}
						return false;
					});
				}
				else if (type == 'disk_file')
				{
					result = BX.Disk.UFMobile.getByName(node.value);
				}
			}
			return result;
		},
		cancel : function(e){
			if (e)
				BX.PreventDefault(e);
			BX.onCustomEvent(this, 'onCancel', [this, BX(this.formId)]);
			return false;
		},
		click : function(e){
			if (e)
				BX.PreventDefault(e);
			this.save();
			return false;
		},
		apply: function(obj, input) {
			var res = {submit : true};
			BX.onCustomEvent(this, 'onSubmitForm', [this, BX(this.formId), input, res]);
			window.app.onCustomEvent('onSubmitForm', [this.gridId, this.formId, (input ? input.id : null)]);
			if (res.submit !== false)
				this.submit(true);
		},
		save: function() {
			var res = {submit : true};
			BX.onCustomEvent(this, 'onSubmitForm', [this, BX(this.formId), null, res]);
			window.app.onCustomEvent('onSubmitForm', [this.gridId, this.formId, null]);
			if (res.submit !== false)
				this.submit(false);
		},
		submit : function(ajax) {
			if (!BX(this.formId))
				return;
			var options = {
				restricted : "Y",
				method : BX(this.formId).getAttribute("method"),
				onsuccess : BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitAjaxSuccess", [this, arguments[0]]);
				}, this),
				onfailure : BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitAjaxFailure", [this, arguments[0]]);
				}, this),
				onprogress : BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitAjaxProgress", [this, arguments]);
				}, this)
			};

			if (ajax)
			{
				BX.onCustomEvent(this, "onBeforeSubmitAjax", [this, options]);
			}
			else
			{
				options["restricted"] = "N";
				options["onsuccess"] = BX.proxy(function() {
					BXMobileApp.UI.Page.LoadingScreen.hide();
					BX.onCustomEvent(this, "onSubmitFormSuccess", [this, arguments[0]]);
				}, this);
				options["onfailure"] = BX.proxy(function() {
					BXMobileApp.UI.Page.LoadingScreen.hide();
					BX.onCustomEvent(this, "onSubmitFormFailure", [this, arguments[0]]);
				}, this);
				options["onprogress"] = BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitFormProgress", [this, arguments]);
				}, this);
				BX.onCustomEvent(this, "onBeforeSubmitForm", [this, options]);
				BXMobileApp.UI.Page.LoadingScreen.show();
			}
			var save = BX(this.formId).elements["save"];
			if (!BX(save))
			{
				save = BX.create("INPUT", {attrs : {type : "hidden", name : "save"}});
				BX(this.formId).appendChild(save);
			}
			save.value = "Y";
			BX.ajax.submitAjax(BX(this.formId), options);
		}
	};
	BX.Mobile.Grid.Form.getByFormId = function(id) { return repo["formId"][id]; };
	BX.Mobile.Grid.Form.getByGridId = function(id) { return repo["gridId"][id]; };
}());