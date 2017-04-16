BX.namespace("BX.Lists");
BX.Lists = (function ()
{
	var firstButtonInModalWindow = null;
	var windowsWithoutManager = {};

	return {
		ajax: function (config)
		{
			config.data = config.data || {};
			config.data['SITE_ID'] = BX.message('SITE_ID');
			config.data['sessid'] = BX.bitrix_sessid();

			return BX.ajax(config);
		},
		modalWindow: function (params)
		{
			params = params || {};
			params.title = params.title || false;
			params.bindElement = params.bindElement || null;
			params.overlay = typeof params.overlay == "undefined" ? true : params.overlay;
			params.autoHide = params.autoHide || false;
			params.closeIcon = typeof params.closeIcon == "undefined"? {right: "20px", top: "10px"} : params.closeIcon;
			params.modalId = params.modalId || 'lists_modal_window_' + (Math.random() * (200000 - 100) + 100);
			params.withoutContentWrap = typeof params.withoutContentWrap == "undefined" ? false : params.withoutContentWrap;
			params.contentClassName = params.contentClassName || '';
			params.contentStyle = params.contentStyle || {};
			params.content = params.content || [];
			params.buttons = params.buttons || false;
			params.events = params.events || {};
			params.withoutWindowManager = !!params.withoutWindowManager || false;

			var contentDialogChildren = [];
			if (params.title) {
				contentDialogChildren.push(BX.create('div', {
					props: {
						className: 'bx-lists-popup-title'
					},
					text: params.title
				}));
			}
			if (params.withoutContentWrap) {
				contentDialogChildren = contentDialogChildren.concat(params.content);
			}
			else {
				contentDialogChildren.push(BX.create('div', {
					props: {
						className: 'bx-lists-popup-content ' + params.contentClassName
					},
					style: params.contentStyle,
					children: params.content
				}));
			}
			var buttons = [];
			if (params.buttons) {
				for (var i in params.buttons) {
					if (!params.buttons.hasOwnProperty(i)) {
						continue;
					}
					if (i > 0) {
						buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
					}
					buttons.push(params.buttons[i]);
				}

				contentDialogChildren.push(BX.create('div', {
					props: {
						className: 'bx-lists-popup-buttons'
					},
					children: buttons
				}));
			}

			var contentDialog = BX.create('div', {
				props: {
					className: 'bx-lists-popup-container'
				},
				children: contentDialogChildren
			});

			params.events.onPopupShow = BX.delegate(function () {
				if (buttons.length) {
					firstButtonInModalWindow = buttons[0];
					BX.bind(document, 'keydown', BX.proxy(this._keyPress, this));
				}

				if(params.events.onPopupShow)
					BX.delegate(params.events.onPopupShow, BX.proxy_context);
			}, this);
			var closePopup = params.events.onPopupClose;
			params.events.onPopupClose = BX.delegate(function () {

				firstButtonInModalWindow = null;
				try
				{
					BX.unbind(document, 'keydown', BX.proxy(this._keypress, this));
				}
				catch (e) { }

				if(closePopup)
				{
					BX.delegate(closePopup, BX.proxy_context)();
				}

				if(params.withoutWindowManager)
				{
					delete windowsWithoutManager[params.modalId];
				}

				BX.proxy_context.destroy();
			}, this);

			var modalWindow;
			if(params.withoutWindowManager)
			{
				if(!!windowsWithoutManager[params.modalId])
				{
					return windowsWithoutManager[params.modalId]
				}
				modalWindow = new BX.PopupWindow(params.modalId, params.bindElement, {
					content: contentDialog,
					closeByEsc: true,
					closeIcon: params.closeIcon,
					autoHide: params.autoHide,
					overlay: params.overlay,
					events: params.events,
					buttons: [],
					zIndex : isNaN(params["zIndex"]) ? 0 : params.zIndex
				});
				windowsWithoutManager[params.modalId] = modalWindow;
			}
			else
			{
				modalWindow = BX.PopupWindowManager.create(params.modalId, params.bindElement, {
					content: contentDialog,
					closeByEsc: true,
					closeIcon: params.closeIcon,
					autoHide: params.autoHide,
					overlay: params.overlay,
					events: params.events,
					buttons: [],
					zIndex : isNaN(params["zIndex"]) ? 0 : params.zIndex
				});

			}

			modalWindow.show();

			return modalWindow;
		},
		removeElement: function (elem)
		{
			return elem.parentNode ? elem.parentNode.removeChild(elem) : elem;
		},
		addToLinkParam: function (link, name, value)
		{
			if (!link.length) {
				return '?' + name + '=' + value;
			}
			link = BX.util.remove_url_param(link, name);
			if (link.indexOf('?') != -1) {
				return link + '&' + name + '=' + value;
			}
			return link + '?' + name + '=' + value;
		},
		showModalWithStatusAction: function (response, action)
		{
			response = response || {};
			if (!response.message) {
				if (response.status == 'success') {
					response.message = BX.message('LISTS_ASSETS_JS_STATUS_ACTION_SUCCESS');
				}
				else {
					response.message = BX.message('LISTS_ASSETS_JS_STATUS_ACTION_ERROR') + '. ' + this.getFirstErrorFromResponse(response);
				}
			}
			var messageBox = BX.create('div', {
				props: {
					className: 'bx-lists-alert'
				},
				children: [
					BX.create('span', {
						props: {
							className: 'bx-lists-aligner'
						}
					}),
					BX.create('span', {
						props: {
							className: 'bx-lists-alert-text'
						},
						text: response.message
					}),
					BX.create('div', {
						props: {
							className: 'bx-lists-alert-footer'
						}
					})
				]
			});

			var currentPopup = BX.PopupWindowManager.getCurrentPopup();
			if(currentPopup)
			{
				currentPopup.destroy();
			}

			var idTimeout = setTimeout(function ()
			{
				var w = BX.PopupWindowManager.getCurrentPopup();
				if (!w || w.uniquePopupId != 'bx-lists-status-action') {
					return;
				}
				w.close();
				w.destroy();
			}, 3500);
			var popupConfirm = BX.PopupWindowManager.create('bx-lists-status-action', null, {
				content: messageBox,
				onPopupClose: function ()
				{
					this.destroy();
					clearTimeout(idTimeout);
				},
				autoHide: true,
				zIndex: 2000,
				className: 'bx-lists-alert-popup'
			});
			popupConfirm.show();

			BX('bx-lists-status-action').onmouseover = function (e)
			{
				clearTimeout(idTimeout);
			};

			BX('bx-lists-status-action').onmouseout = function (e)
			{
				idTimeout = setTimeout(function ()
				{
					var w = BX.PopupWindowManager.getCurrentPopup();
					if (!w || w.uniquePopupId != 'bx-lists-status-action') {
						return;
					}
					w.close();
					w.destroy();
				}, 3500);
			};
		},
		addNewTableRow: function(tableID, col_count, regexp, rindex)
		{
			var tbl = document.getElementById(tableID);
			var cnt = tbl.rows.length;
			var oRow = tbl.insertRow(cnt);

			for(var i=0;i<col_count;i++)
			{
				var oCell = oRow.insertCell(i);
				var html = tbl.rows[cnt-1].cells[i].innerHTML;
				oCell.innerHTML = html.replace(regexp,
					function(html)
					{
						return html.replace('[n'+arguments[rindex]+']', '[n'+(1+parseInt(arguments[rindex]))+']');
					}
				);
			}
		},
		show: function(element)
		{
			if (this.getRealDisplay(element) != 'none')
				return;

			var old = element.getAttribute("displayOld");
			element.style.display = old || "";

			if (this.getRealDisplay(element) === "none" ) {
				var nodeName = element.nodeName, body = document.body, display;

				if (displayCache[nodeName]) {
					display = displayCache[nodeName];
				} else {
					var testElem = document.createElement(nodeName);
					body.appendChild(testElem);
					display = this.getRealDisplay(testElem);

					if (display === "none" ) {
						display = "block";
					}

					body.removeChild(testElem);
					displayCache[nodeName] = display;
				}

				element.setAttribute('displayOld', display);
				element.style.display = display;
			}
		},
		hide: function(element)
		{
			if (!element.getAttribute('displayOld'))
			{
				element.setAttribute("displayOld", element.style.display);
			}
			element.style.display = "none";
		},
		getRealDisplay: function (element)
		{
			if (element.currentStyle) {
				return element.currentStyle.display;
			} else if (window.getComputedStyle) {
				var computedStyle = window.getComputedStyle(element, null );
				return computedStyle.getPropertyValue('display');
			}
		}
	}
})();