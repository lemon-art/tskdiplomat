BX.CLBlock = function(arParams)
{
	this.arData = new Array();
	this.arData["Subscription"] = new Array();
	this.arData["Transport"] = new Array();
	this.arData["Visible"] = new Array();
	this.UTPopup = null;
	this.aUnSubscribeTransport = false;

	this.entity_type = null;
	this.entity_id = null;
	this.event_id = null;
	this.event_id_fullset = false;
	this.cb_id = null;
	this.t_val = null;
	this.v_val = null;
	this.ind = null;
	this.type = null;
}

BX.CLBlock.prototype.DataParser = function(str)
{
	str = str.replace(/^\s+|\s+$/g, '');
	while (str.length > 0 && str.charCodeAt(0) == 65279)
		str = str.substring(1);

	if (str.length <= 0)
		return false;

	if (str.substring(0, 1) != '{' && str.substring(0, 1) != '[' && str.substring(0, 1) != '*')
		str = '"*"';

	eval("arData = " + str);

	return arData;
}

BX.CLBlock.prototype.ShowContentTransport = function()
{
	node_code = this.entity_type + '_' + this.entity_id + '_' + this.event_id + '_' + this.ind;

	if (this.arData["Subscription"][node_code].length <= 0)
		return BX.create('DIV', {
				props: {},
				html: BX.message('sonetLNoSubscriptions')
			});

	var div = BX.create('DIV', {
		props: {
			'className': 'popup-window-content-transport-div'
		}
	} );

	div.appendChild(BX.create('div', {
		props: {
			'className': 'popup-window-content-transport-div-title'
		},
		html: BX.message('sonetLTransportTitle')
	}));
	var table = div.appendChild(BX.create('table', {
		props: {
			'width': '100%'
		}
	}));

	var tbody = table.appendChild(BX.create('tbody', {}));

	if (
		typeof this.arData["Subscription"][node_code]["EVENT"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["EVENT"]["TITLE_1"] != 'undefined'
	)
		this.ShowContentTransportRow(tbody, this.arData, node_code, "EVENT");

	if (
		typeof this.arData["Subscription"][node_code]["ALL"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["ALL"]["TITLE_1"] != 'undefined'
	)
		this.ShowContentTransportRow(tbody, this.arData, node_code, "ALL");

	if (
		typeof this.arData["Subscription"][node_code]["CB_ALL"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["CB_ALL"]["TITLE_1"] != 'undefined'
	)
		this.ShowContentTransportRow(tbody, this.arData, node_code, "CB_ALL");

	return div;
}

BX.CLBlock.prototype.ShowContentTransportRow = function(tbody_ob, arData, node_code, type)
{
	var tr = false;
	var transport_hidden = false;
	var transport_div = false;
	var select = false;
	var is_selected = false;
	var transport_class = false;

	tbody_ob.appendChild(BX.create('tr', {
		props: {},
		children: [
			BX.create('td', {
				attrs: {
					'colspan': '2'
				},
				children: [
					BX.create('div', {
						props: {
							'className': 'popup-window-hr'
						},
						children: [
							BX.create('i', {})
						]
					})
				]
			})
		]
	}));

	tr = tbody_ob.appendChild(BX.create('tr', {
		props: {},
		children: [
			BX.create('td', {
				props: {
					'className': 'popup-window-content-transport-cell-title'
				}
			}),
			BX.create('td', {
				props: {
					'className': 'popup-window-content-transport-cell-control'
				}
			})
		]
	}));

	tr.firstChild.appendChild(BX.create('div', {
			props: {},
			html: arData["Subscription"][node_code][type]["TITLE_1"]
		}));


	transport_hidden = tr.firstChild.nextSibling.appendChild(BX.create('INPUT', {
			props: {
				'name': 't_lr_' + node_code + '_' + type,
				'id': 't_lr_' + node_code + '_' + type,
				'bx-type': type
			},
			attrs: {
				'type': 'hidden'
			}
		}));

	transport_div = tr.firstChild.nextSibling.appendChild(BX.create('DIV', {
			props: {
				'className': 'transport-popup-list-list'
			}
		}));

/*
	select = tr.firstChild.nextSibling.appendChild(BX.create('select', {
			props: {
				'name': 't_lr_' + node_code
			}
		}));
*/

	// inherited
	if (
		arData["Subscription"][node_code][type]["TRANSPORT_INHERITED"]
		&& arData["Subscription"][node_code][type]["TRANSPORT"] != "N"
	)
	{
		for (var i = 0; i < arData["Transport"].length; i++)
		{
			if (arData["Transport"][i]["Key"] == arData["Subscription"][node_code][type]["TRANSPORT"])
			{
				InheritedName = arData["Transport"][i]["Value"];
				break;
			}
		}

		transport_div.appendChild(BX.create('span', {
				props: {
//					'value': 'I',
					'className': 'transport-popup-list-item transport-popup-list-item-selected',
					'bx-option-value': 'I',
					'bx-hidden-id': 't_lr_' + node_code + '_' + type,
					'id': 't_lr_' + node_code + '_' + type + '_I'
//					'selected': true,
//					'defaultSelected': true
				},
				children: [
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-left'
						}
					}),
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-icon transport-popup-icon-' + arData["Subscription"][node_code][type]["TRANSPORT"]
						}
					}),
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-text'
						},
						html: BX.message('sonetLInherited') + ' (' + InheritedName + ')'
					}),
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-right'
						}
					})
				],
				'events': {
					'click': BX.delegate(this.OnTransportClick, this)
				}
		}));

		this.SetTransportHidden('t_lr_' + node_code + '_' + type, 'I');
	}

	// all transports
	for (var i = 0; i < arData["Transport"].length; i++)
	{
		if (
			arData["Subscription"][node_code][type]["TRANSPORT"] == arData["Transport"][i]["Key"]
			&&
			(
				!arData["Subscription"][node_code][type]["TRANSPORT_INHERITED"]
				|| arData["Subscription"][node_code][type]["TRANSPORT"] == "N"
			)
		)
		{
			is_selected = true;
			transport_class = 'transport-popup-list-item transport-popup-list-item-selected';
			this.SetTransportHidden('t_lr_' + node_code + '_' + type, arData["Transport"][i]["Key"]);
		}
		else
		{
			is_selected = false;
			transport_class = 'transport-popup-list-item';
		}


		transport_div.appendChild(BX.create('span', {
				props: {
//					'value': arData["Transport"][i]["Key"],
					'className': transport_class,
					'bx-option-value': arData["Transport"][i]["Key"],
					'bx-hidden-id': 't_lr_' + node_code + '_' + type,
					'id': 't_lr_' + node_code + '_' + type + '_' + arData["Transport"][i]["Key"]
//					'selected': true,
//					'defaultSelected': true
				},
				children: [
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-left'
						}
					}),
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-icon transport-popup-icon-' + arData["Transport"][i]["Key"]
						}
					}),
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-text'
						},
						html: arData["Transport"][i]["Value"]
					}),
					BX.create('span', {
						props: {
							'className': 'transport-popup-list-item-right'
						}
					})
				],
				'events': {
					'click': BX.delegate(this.OnTransportClick, this)
				}

		}));

	}
}

BX.CLBlock.prototype.OnTransportClick = function()
{
	var ob = BX.proxy_context;
	this.SetTransportHidden(ob["bx-hidden-id"], ob["bx-option-value"]);

	var arItems = BX.findChildren(ob.parentNode, {'tag':'span'}, false);
	for (var i = 0; i < arItems.length; i++)
	{
		if (arItems[i].id == ob.id)
			BX.addClass(arItems[i], 'transport-popup-list-item-selected');
		else
			BX.removeClass(arItems[i], 'transport-popup-list-item-selected');
	}
}

BX.CLBlock.prototype.SetTransportHidden = function(hidden_id, val)
{
	if (BX(hidden_id))
		BX.adjust(BX(hidden_id), {
			props : {
				'value' : val
			}
		});

//		BX(hidden_id).value = val;
}

BX.CLBlock.prototype.ShowContentVisible = function()
{
	var a = null;
	var div1 = null;
	var span = null;
	var cnt = 1;

	node_code = this.entity_type + '_' + this.entity_id + '_' + this.event_id + '_' + this.ind;

	if (this.arData["Subscription"][node_code].length <= 0)
		return BX.create('DIV', {
				props: {},
				html: BX.message('sonetLNoSubscriptions')
			});

	var div = BX.create('DIV', {
		props: {
			'id': 'popup-window-content-visible-div',
			'className': 'popup-window-content-visible-div'
		}
	} );

	div.appendChild(BX.create('DIV', {
			props: {
				'className': 'popup-window-content-visible-title'
			},
			html: BX.message('sonetLVisibleTitle_' + this.v_val)
		}));

	div.appendChild(BX.create('div', {
			props: {
				'className': 'popup-window-content-visible-sep'
			},
			children: [
				BX.create('div', {
					props: {
						'className': 'popup-window-hr'
					},
					children: [
						BX.create('i', {})
					]
				})
			]
		}));

	if (
		typeof this.arData["Subscription"][node_code]["EVENT"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["EVENT"]["TITLE_2"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["EVENT"]["VISIBLE"] != 'undefined'
		&& this.arData["Subscription"][node_code]["EVENT"]["VISIBLE"] == this.v_val
	)
	{
		div1 = div.appendChild(BX.create('DIV', {}));

		span = div1.appendChild(BX.create('span', {
			props: {
				'className': 'popup-window-content-visible-row'
			},
			children: [
				BX.create('span', {
					props: {
						'className': 'popup-window-content-row-cnt'
					},
					html: cnt + '. '
				})
			]
		}));
		cnt++;

		a = span.appendChild(BX.create('a', {
					props: {
						'bx-has-transport' : 'N',
						'bx-type' : 'EVENT',
						'href': 'javascript:void(0)',
						'className': 'popup-window-content-row-text'
					},
					html: this.arData["Subscription"][node_code]["EVENT"]["TITLE_2"]
				}));

		if (
			this.v_val == 'Y'
			&& typeof this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT"] != 'undefined'
			&& this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT"] != 'N'
		)
		{
			BX.adjust(a, {
				props : {
					'bx-has-transport' : 'Y'
				}
			});
		}

		BX.bind(a, "click", BX.delegate(this.Subscribe, this));

		div1.appendChild(BX.create('div', {
				props: {
					'className': 'popup-window-content-visible-sep'
				},
				children: [
					BX.create('div', {
						props: {
							'className': 'popup-window-hr'
						},
						children: [
							BX.create('i', {})
						]
					})
				]
			}));
	}

	if (
		typeof this.arData["Subscription"][node_code]["ALL"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["ALL"]["TITLE_2"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["ALL"]["VISIBLE"] != 'undefined'
		&& this.arData["Subscription"][node_code]["ALL"]["VISIBLE"] == this.v_val
	)
	{
		div1 = div.appendChild(BX.create('DIV', {}));

		span = div1.appendChild(BX.create('span', {
			props: {
				'className': 'popup-window-content-visible-row'
			},
			children: [
				BX.create('span', {
					props: {
						'className': 'popup-window-content-row-cnt'
					},
					html: cnt + '. '
				})
			]
		}));
		cnt++;

		a = span.appendChild(BX.create('a', {
					props: {
						'bx-has-transport' : 'N',
						'bx-type' : 'ALL',
						'href': 'javascript:void(0)',
						'className': 'popup-window-content-row-text'
					},
					html: this.arData["Subscription"][node_code]["ALL"]["TITLE_2"]
				}));


		if (
			this.v_val == 'Y'
			&& typeof this.arData["Subscription"][node_code]["ALL"]["TRANSPORT"] != 'undefined'
			&& this.arData["Subscription"][node_code]["ALL"]["TRANSPORT"] != 'N'
		)
		{
			BX.adjust(a, {
				props : {
					'bx-has-transport' : 'Y'
				}
			});
		}

		div1.appendChild(BX.create('div', {
				props: {
					'className': 'popup-window-content-visible-sep'
				},
				children: [
					BX.create('div', {
						props: {
							'className': 'popup-window-hr'
						},
						children: [
							BX.create('i', {})
						]
					})
				]
			}));

		BX.bind(a, "click", BX.delegate(this.Subscribe, this));
	}

	if (
		typeof this.arData["Subscription"][node_code]["CB_ALL"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["CB_ALL"]["TITLE_2"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["CB_ALL"]["VISIBLE"] != 'undefined'
		&& this.arData["Subscription"][node_code]["CB_ALL"]["VISIBLE"] == this.v_val
	)
	{
		div1 = div.appendChild(BX.create('DIV', {}));

		span = div1.appendChild(BX.create('span', {
			props: {
				'className': 'popup-window-content-visible-row'
			},
			children: [
				BX.create('span', {
					props: {
						'className': 'popup-window-content-row-cnt'
					},
					html: cnt + '. '
				})
			]
		}));
		cnt++;

		a = span.appendChild(BX.create('a', {
					props: {
						'bx-has-transport' : 'N',
						'bx-type' : 'CB_ALL',
						'href': 'javascript:void(0)',
						'className': 'popup-window-content-row-text'
					},
					html: this.arData["Subscription"][node_code]["CB_ALL"]["TITLE_2"]
				}));


		if (
			this.v_val == 'Y'
			&& typeof this.arData["Subscription"][node_code]["CB_ALL"]["TRANSPORT"] != 'undefined'
			&& this.arData["Subscription"][node_code]["CB_ALL"]["TRANSPORT"] != 'N'
		)
		{
			BX.adjust(a, {
				props : {
					'bx-has-transport' : 'Y'
				}
			});
		}

		div1.appendChild(BX.create('div', {
				props: {
					'className': 'popup-window-content-visible-sep'
				},
				children: [
					BX.create('div', {
						props: {
							'className': 'popup-window-hr'
						},
						children: [
							BX.create('i', {})
						]
					})
				]
			}));

		BX.bind(a, "click", BX.delegate(this.Subscribe, this));
	}

	var lastSep = BX.findChild(div1, {'tag': 'div', 'className': 'popup-window-content-visible-sep'}, false);
	if (lastSep)
		BX.remove(lastSep);

	return div;

}

BX.CLBlock.prototype.Subscribe = function(el)
{
	var ob = BX.proxy_context;

	this.type = ob["bx-type"];

	if (
		this.v_val == 'Y'
		&& typeof ob["bx-has-transport"] != 'undefined'
		&& ob["bx-has-transport"] == 'Y'
	)
	{
		// create unsubscribe transport popup
		var YesButton = new BX.PopupWindowButton(
					{
						'text': BX.message('sonetLDialogUT_Y'),
						'className' : 'popup-window-button-accept',
						'id': 'bx_log_ut_popup_submitY'
					}
				);
		var NoButton = new BX.PopupWindowButton(
					{
						'text': BX.message('sonetLDialogUT_N'),
						'className' : 'popup-window-button-decline',
						'id': 'bx_log_ut_popup_submitN'
					}
				);

		if (this.UTPopup == null)
		{
			var popup = new BX.PopupWindow(
						'bx_log_ut_popup',
						BX('popup-window-content-visible-div'),
						{
							closeIcon : true,
							autoHide: true,
							buttons: [YesButton, NoButton]
						}
					);

			this.UTPopup = popup;
			BX.bind(BX('bx_log_ut_popup_submitY'), "click", BX.delegate(this.onUTSubmit, this));
			BX.bind(BX('bx_log_ut_popup_submitN'), "click", BX.delegate(this.onUTReject, this));
		}
		else
		{
			var popup = this.UTPopup;
			popup.setBindElement(BX('popup-window-content-visible-div'));
		}

		var content = BX.message('sonetLTransportUnsubscribe');
		popup.setContent(content);
		popup.show();
	}
	else
		this.SetVisible();
}

BX.CLBlock.prototype.onVisiblePopupClose = function()
{
	this.UTClose();
}

BX.CLBlock.prototype.onUTSubmit = function()
{
	this.t_newval = 'N';
	this.SetTransport();
	this.SetVisible();
	this.UTClose();
}

BX.CLBlock.prototype.onUTReject = function()
{
	this.SetVisible();
	this.UTClose();
}

BX.CLBlock.prototype.UTClose = function()
{
	if (this.UTPopup != null)
		this.UTPopup.close();
}


BX.CLBlock.prototype.SetVisible = function()
{
	var arItems = [];
	var arComments = [];
	var bVisibleComments = false;

	params = 'entity_type=' + this.entity_type + '&entity_id=' + this.entity_id + '&event_id=' + this.event_id + '&cb_id=' + this.cb_id + '&ls=' + this.type + '&visible=' + this.v_newval + '&action=set';
	params += '&site=' + BX.util.urlencode(BX.message('SITE_ID'));

	sonetLXmlHttpSet.open(
		"get",
		BX.message('sonetLSetPath') + "?" + BX.message('sonetLSessid')
			+ "&" + params
			+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetLXmlHttpSet.send(null);

	sonetLXmlHttpSet.onreadystatechange = function()
	{
		if (sonetLXmlHttpSet.readyState == 4 && sonetLXmlHttpSet.status == 200)
		{
			if (sonetLXmlHttpSet.responseText && sonetLXmlHttpSet.responseText.replace(/^\'+|\'+$/g,"").length > 0)
			{
				if (typeof sonetEventsErrorDiv != 'undefined' && sonetEventsErrorDiv != null)
				{
					sonetEventsErrorDiv.style.display = "block";
					sonetEventsErrorDiv.innerHTML = sonetEventXmlHttpSet.responseText;
				}
			}
		}
	}

	items = BX('log_external_container');

	if (items)
	{
		if (BX.hasClass(items, 'show-hidden-Y'))
			var bShowHidden = true;
		else
			var bShowHidden = false;

		if (this.type == 'ALL')
			arItems = BX.findChildren(items, {'tag':'div', 'class':'sonet-log-item-where-' + this.entity_type + '-' + this.entity_id + '-all'}, true);
		else if (this.type == 'EVENT')
		{
			if (this.event_id_fullset)
				arItems = BX.findChildren(items, {'tag':'div', 'class':'sonet-log-item-where-' + this.entity_type + '-' + this.entity_id + '-' + this.event_id_fullset.replace(/_/g, '-')}, true);
			else
				arItems = BX.findChildren(items, {'tag':'div', 'class':'sonet-log-item-where-' + this.entity_type + '-' + this.entity_id + '-' + this.event_id.replace(/_/g, '-')}, true);

		}
		else if (this.type == 'CB_ALL')
		{
			arItems = BX.findChildren(items, {'tag':'div', 'class':'sonet-log-item-createdby-' + this.cb_id }, true);
			arComments = BX.findChildren(items, {'tag':'div', 'class':'sonet-log-comment-createdby-' + this.cb_id }, true);
		}
	}

	if (arComments)
	{
		for (var i = 0; i < arComments.length; i++)
		{
			if (this.v_newval == 'N')
				BX.addClass(arComments[i], 'feed-hidden-post');
			else
				BX.removeClass(arComments[i], 'feed-hidden-post');

			if (!bShowHidden && this.v_newval == 'N')
				arComments[i].style.display = 'none';
		}
	}

	if (arItems)
	{
		for (var i = 0; i < arItems.length; i++)
		{
			bVisibleComments = false;

			if (this.v_newval == 'N')
				BX.addClass(arItems[i], 'feed-hidden-post');
			else
				BX.removeClass(arItems[i], 'feed-hidden-post');

			if (!bShowHidden && this.v_newval == 'N')
			{
				// check visible comments
				if (arItems[i].nextSibling)
				{
					arComments = BX.findChildren(arItems[i].nextSibling, {'tag': 'div', 'class': 'feed-com-block' }, false);
					if (arComments)
					{
						for (var j = 0; j < arComments.length; j++)
						{
							if (arComments[j].style.display != 'none')
							{
								bVisibleComments = true;
								break;
							}
						}
					}
				}

				if (!bVisibleComments)
				{
					arItems[i].parentNode.style.display = 'none';
					// hide comments form

					if (arItems[i].nextSibling && arItems[i].nextSibling.nextSibling)
						arItems[i].nextSibling.nextSibling.style.display = 'none';
				}
			}
		}
	}

	LBlock.VisiblePopup.close();
}

BX.CLBlock.prototype.SetTransport = function()
{
	params = 'entity_type=' + this.entity_type + '&entity_id=' + this.entity_id + '&event_id=' + this.event_id + '&cb_id=' + this.cb_id + '&ls=' + this.type + '&transport=' + this.t_newval + '&action=set';
	params += '&site=' + BX.util.urlencode(BX.message('SITE_ID'));

	var sonetLXmlHttpSet2 = new XMLHttpRequest();

	sonetLXmlHttpSet2.open(
		"get",
		BX.message('sonetLSetPath') + "?" + BX.message('sonetLSessid')
			+ "&" + params
			+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetLXmlHttpSet2.send(null);

	sonetLXmlHttpSet2.onreadystatechange = function()
	{
		if (sonetLXmlHttpSet2.readyState == 4 && sonetLXmlHttpSet2.status == 200)
		{
			if (sonetLXmlHttpSet2.responseText && sonetLXmlHttpSet2.responseText.replace(/^\'+|\'+$/g,"").length > 0)
			{
				if (typeof sonetEventsErrorDiv != 'undefined' && sonetEventsErrorDiv != null)
				{
					sonetEventsErrorDiv.style.display = "block";
					sonetEventsErrorDiv.innerHTML = sonetEventXmlHttpSet.responseText;
				}
			}
		}
	}
}

BX.CLBlock.prototype.SetTransportFromPopup = function(arObHidden)
{
	if (arObHidden == null)
		return false;

	var params = 'entity_type=' + this.entity_type + '&entity_id=' + this.entity_id + '&event_id=' + this.event_id + '&cb_id=' + this.cb_id + '&action=set_transport_arr';

	for (var i = 0; i < arObHidden.length; i++)
	{
		var obHidden = arObHidden[i];
		if (
			obHidden.value != ""
			&& obHidden.value != null
		)
		params += '&ls_arr['+obHidden["bx-type"]+']=' + obHidden["value"];

	}

	params += '&site=' + BX.util.urlencode(BX.message('SITE_ID'));

	sonetLXmlHttpSet.open(
		"get",
		BX.message('sonetLSetPath') + "?" + BX.message('sonetLSessid')
			+ "&" + params
			+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetLXmlHttpSet.send(null);

	sonetLXmlHttpSet.onreadystatechange = function()
	{
		if (sonetLXmlHttpSet.readyState == 4 && sonetLXmlHttpSet.status == 200)
		{
			if (sonetLXmlHttpSet.responseText && sonetLXmlHttpSet.responseText.replace(/^\'+|\'+$/g,"").length > 0)
			{
				if (typeof sonetEventsErrorDiv != 'undefined' && sonetEventsErrorDiv != null)
				{
					sonetEventsErrorDiv.style.display = "block";
					sonetEventsErrorDiv.innerHTML = sonetEventXmlHttpSet.responseText;
				}
			}
		}
	}
}

BX.CLBlock.prototype.onTransportPopupSubmit = function()
{
	var ob = BX.proxy_context;
	var formNode = BX.findParent(ob, {'tag': 'tr', 'className': 'popup-window-content-row'});
	var arItems = BX.findChildren(formNode, {'tag':'input', 'attr': {'type': 'hidden'}}, true);
	this.SetTransportFromPopup(arItems);
	this.TransportPopup.destroy();
}

function __logFilterShow()
{
	if (BX('bx_sl_filter').style.display == 'none')
	{
		BX('bx_sl_filter').style.display = 'block';
		BX('bx_sl_filter_hidden').style.display = 'none';
	}
	else
	{
		BX('bx_sl_filter').style.display = 'none';
		BX('bx_sl_filter_hidden').style.display = 'block';
	}
}

__logShowTransportDialog = function(ind, entity_type, entity_id, event_id, event_id_fullset, cb_id)
{
	if (BX.PopupMenu && BX.PopupMenu.Data["post-menu-" + ind])
		BX.PopupMenu.Data["post-menu-" + ind].popupWindow.close();

	var submitButton = new BX.PopupWindowButton(
		{
			'text': BX.message('sonetLDialogSubmit'),
			'className' : 'popup-window-button-accept',
			'id': 'bx_log_transport_popup_submit'
		}
	);

	var cancelButton = new BX.PopupWindowButtonLink(
		{
			'text': BX.message('sonetLDialogCancel'),
			'className' : 'popup-window-button-link-cancel',
			'id': 'bx_log_transport_popup_cancel'
		}
	);

	var popup = BX.PopupWindowManager.create(
		'bx_log_transport_popup',
		false,
		{
			closeIcon : true,
			offsetTop: 2,
			autoHide: true,
			buttons: [submitButton, cancelButton]
		}
	);

	BX.bind(BX('bx_log_transport_popup_submit'), "click", BX.delegate(LBlock.onTransportPopupSubmit, LBlock));
	BX.bind(BX('bx_log_transport_popup_cancel'), "click", BX.delegate(popup.close, popup));

	LBlock.entity_type = entity_type;
	LBlock.entity_id = entity_id;
	LBlock.event_id = event_id;
	if (event_id_fullset)
		LBlock.event_id_fullset = event_id_fullset;
	LBlock.cb_id = cb_id;
	LBlock.ind = ind;

	LBlock.TransportPopup = popup;

	if (
		entity_type != null
		&& entity_type != false
		&& entity_id != null
		&& entity_id != false
		&& event_id != null
		&& event_id != false
	)
	{

		var params = BX.message('sonetLGetPath') + "?" + BX.message('sonetLSessid')
			+ "&action=get_data"
			+ "&lang=" + BX.util.urlencode(BX.message('sonetLLangId'))
			+ "&site=" + BX.util.urlencode(BX.message('sonetLSiteId'))
			+ "&et=" + BX.util.urlencode(entity_type)
			+ "&eid=" + BX.util.urlencode(entity_id)
			+ "&evid=" + BX.util.urlencode(event_id)
			+ "&r=" + Math.floor(Math.random() * 1000);

		if (
			cb_id != null
			&& cb_id != false
		)
			params += "&cb_id=" + BX.util.urlencode(cb_id);

		sonetLXmlHttpGet.open(
			"get",
			params
		);
		sonetLXmlHttpGet.send(null);

		sonetLXmlHttpGet.onreadystatechange = function()
		{
			if (sonetLXmlHttpGet.readyState == 4 && sonetLXmlHttpGet.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpGet.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet.responseText;
						}
						return;
					}
					sonetLXmlHttpGet.abort();
					LBlock.arData["Subscription"][entity_type + '_' + entity_id + '_' + event_id + '_' + ind] = data["Subscription"];

					if (
						typeof LBlock.arData["Transport"] == 'undefined'
						|| LBlock.arData["Transport"].length <= 0
					)
						LBlock.arData["Transport"] = data["Transport"];

					if (
						typeof LBlock.arData["Visible"] == 'undefined'
						|| LBlock.arData["Visible"].length <= 0
					)
						LBlock.arData["Visible"] = data["Visible"];

					if (popup.bindElementPos != null)
					{
						popup.setBindElement(BX('sonet_log_transport_' + ind));
						BX.cleanNode(popup.contentContainer);
					}

					var content = LBlock.ShowContentTransport(entity_type, entity_id, event_id, cb_id, ind);
					popup.setContent(content);
					popup.show();
				}
			}
		}
	}

}


__logShowVisibleDialog = function(ind, entity_type, entity_id, event_id, event_id_fullset, cb_id, val)
{
	if (BX.PopupMenu && BX.PopupMenu.Data["post-menu-" + ind])
		BX.PopupMenu.Data["post-menu-" + ind].popupWindow.close();

	var popup = BX.PopupWindowManager.create(
		'bx_log_visible_popup',
		false,
		{
			closeIcon: true,
			autoHide: true,
			offsetLeft: 0,
			offsetTop: 0,
			'events': {
				'onPopupClose': BX.delegate(LBlock.onVisiblePopupClose, LBlock)
			}
		}
	);

	LBlock.entity_type = entity_type;
	LBlock.entity_id = entity_id;
	LBlock.event_id = event_id;
	if (event_id_fullset)
		LBlock.event_id_fullset = event_id_fullset;
	LBlock.cb_id = cb_id;

	if (BX('sonet_log_day_item_' + ind))
		var nodePost = BX('sonet_log_day_item_' + ind);
	else if(BX('sonet_log_comment_' + ind))
		var nodeComment = BX('sonet_log_comment_' + ind);

	if (val == 'Y' && BX(nodePost) && !BX.hasClass(BX(nodePost), 'feed-hidden-post'))
	{
		LBlock.v_val = 'Y';
		LBlock.v_newval = 'N';
	}
	else if (BX(nodePost) && BX.hasClass(BX(nodePost), 'feed-hidden-post'))
	{
		LBlock.v_val = 'N';
		LBlock.v_newval = 'Y';
	}
	else if (val == 'Y' && BX(nodeComment) && !BX.hasClass(BX(nodeComment), 'feed-hidden-post'))
	{
		LBlock.v_val = 'Y';
		LBlock.v_newval = 'N';
	}
	else if (BX(nodeComment) && BX.hasClass(BX(nodeComment), 'feed-hidden-post'))
	{
		LBlock.v_val = 'N';
		LBlock.v_newval = 'Y';
	}
	else
	{
		LBlock.v_val = 'Y';
		LBlock.v_newval = 'N';
	}


	LBlock.ind = ind;

	LBlock.VisiblePopup = popup;

	if (
		event_id != null
		&& event_id != false
	)
	{

		var params = BX.message('sonetLGetPath') + "?" + BX.message('sonetLSessid')
			+ "&action=get_data"
			+ "&lang=" + BX.util.urlencode(BX.message('sonetLLangId'))
			+ "&site=" + BX.util.urlencode(BX.message('sonetLSiteId'))
			+ "&et=" + BX.util.urlencode(entity_type)
			+ "&eid=" + BX.util.urlencode(entity_id)
			+ "&evid=" + BX.util.urlencode(event_id)
			+ "&r=" + Math.floor(Math.random() * 1000);

		if (
			cb_id != null
			&& cb_id != false
		)
			params += "&cb_id=" + BX.util.urlencode(cb_id);

		sonetLXmlHttpGet.open(
			"get",
			params
		);
		sonetLXmlHttpGet.send(null);

		sonetLXmlHttpGet.onreadystatechange = function()
		{
			if (sonetLXmlHttpGet.readyState == 4 && sonetLXmlHttpGet.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpGet.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet.responseText;
						}
						return;
					}
					sonetLXmlHttpGet.abort();
					LBlock.arData["Subscription"][entity_type + '_' + entity_id + '_' + event_id + '_' + ind] = data["Subscription"];

					if (
						typeof LBlock.arData["Transport"] == 'undefined'
						|| LBlock.arData["Transport"].length <= 0
					)
						LBlock.arData["Transport"] = data["Transport"];

					if (
						typeof LBlock.arData["Visible"] == 'undefined'
						|| LBlock.arData["Visible"].length <= 0
					)
						LBlock.arData["Visible"] = data["Visible"];

					if (popup.bindElementPos != null)
					{
						popup.setBindElement(BX('sonet_log_visible_' + ind));
						BX.cleanNode(popup.contentContainer);
					}

					var content = LBlock.ShowContentVisible();
					popup.setContent(content);
					popup.show();
				}
			}
		}
	}

}

if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var sonetLXmlHttpGet = new XMLHttpRequest();
var sonetLXmlHttpSet = new XMLHttpRequest();
var waitTimeout = null;
var waitDiv = null;
var	waitPopup = null;
var waitTime = 500;
var arrGetComments = new Array();

var LBlock = new BX.CLBlock();

function WriteMicroblog(val)
{
	if(val)
	{
		document.getElementById('microblog-link').style.display = "none";
		document.getElementById('microblog-form').style.display = "block";
		BX.onCustomEvent(BX('microblog-form'), 'onFormShow');
	}
	else
	{
		document.getElementById('microblog-link').style.display = "block";
		document.getElementById('microblog-form').style.display = "none";
	}

	BX.onCustomEvent(
		'OnWriteMicroblog',
		[ val ]
	);
	
}

function __logShowCommentForm(log_id, error, comment)
{
	var pForm = BX('sonet_log_comment_form_container');
	var commentLink = false;
	var place = false;
	var commentsNode = false;
	var tmpPos = 0;
	var iMaxHeight = 0;
	var commentLinkOffsetHeight;

	place = pForm.parentNode;
	if (BX(place))
	{
		if (BX(place).id == 'sonet_log_comment_form_place_' + log_id && pForm.style.display != 'none')
			return false;

		if (pForm.style.display == 'none')
			pForm.style.display = 'block';

		if (BX.hasClass(place, 'sonet-log-comment-form-place'))
		{
			var oldCommentsNode = BX.findParent(place, {'className': 'feed-comments-block'});
			if (BX(oldCommentsNode))
			{
				var oldCommentsNodeLimited = BX.findChild(oldCommentsNode, {'tag': 'div', 'className': 'feed-comments-limited'}, false);
				var oldCommentsNodeFull = BX.findChild(oldCommentsNode, {'tag': 'div', 'className': 'feed-comments-full'}, false);
				if (!BX(oldCommentsNodeLimited) && !BX(oldCommentsNodeFull))
					oldCommentsNode.style.display = 'none';

				var oldCommentsFooter = BX.findPreviousSibling(place, {'tag': 'div', 'className': 'feed-com-footer'});
				if (BX(oldCommentsFooter))
					oldCommentsFooter.style.display = 'block';
			}
		}
	}

	BX('sonet_log_comment_form_place_' + log_id).appendChild(pForm); // Move form
	pForm.style.display = "block";

	CommentFormWidth = BX('sonet_log_comment_text', true).offsetWidth;
	CommentFormColsDefault = Math.floor(CommentFormWidth / CommentFormSymbolWidth);
	CommentFormRowsDefault = BX('sonet_log_comment_text', true).rows;

	var commentsBlock = BX('feed_comments_block_' + log_id);
	if (commentsBlock)
	{
		var source_node = BX.findChild(commentsBlock, {'tag': 'div', 'className': 'feed-com-footer'}, false);
		if (BX(source_node))
			BX(source_node).style.display = 'none';
	}

	BX.focus(BX('sonet_log_comment_text'));

	BX('sonet_log_comment_logid').value = log_id;
	BX('sonet_log_comment_form').action.value = 'add_comment';

	if(error == "Y")
	{
		if(comment && comment.length > 0)
		{
			comment = comment.replace(/\/</gi, '<');
			comment = comment.replace(/\/>/gi, '>');
			BX('sonet_log_comment_text').value = comment;
		}
	}

	return false;
}


function __logCommentAdd()
{
	var textarea = BX('sonet_log_comment_text');
	var logIDField = BX('sonet_log_comment_logid');
	var log_id = logIDField.value
	var sonetLXmlHttpSet3 = new XMLHttpRequest();

	if (!textarea.value)
		return;

	sonetLXmlHttpSet3.open("POST", BX.message('sonetLSetPath'), true);
	sonetLXmlHttpSet3.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	sonetLXmlHttpSet3.onreadystatechange = function()
	{
		if(sonetLXmlHttpSet3.readyState == 4)
		{
			__logCommentCloseWait();
			if(sonetLXmlHttpSet3.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpSet3.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet3.responseText;
						}
						return;
					}
					sonetLXmlHttpSet3.abort();

					var commentID = false;
					var strMessage = '';

					if (data["commentID"] != 'undefined' && data["commentID"] > 0)
						commentID = data["commentID"];
					else if (data["strMessage"] != 'undefined' && data["strMessage"].length > 0)
					{
						strMessage = data["strMessage"];
						__logShowCommentForm(log_id, "Y", data["commentText"]);
					}

					__logCommentGet(log_id, commentID, strMessage);
					__logCommentFormAutogrow(textarea);

					if (BX("log_entry_follow_" + log_id, true))
					{
						var strFollowOld = (BX("log_entry_follow_" + log_id, true).getAttribute("data-follow") == "Y" ? "Y" : "N");
						if (strFollowOld == "N")
						{
							BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollowY');
							BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", "Y");
						}
					}
				}
			}
			else
			{
				// error!
			}
		}
	}

	sonetLXmlHttpSet3.send("r=" + Math.floor(Math.random() * 1000)
		+ "&" + BX.message('sonetLSessid')
		+ "&log_id=" + encodeURIComponent(log_id)
		+ "&p_smile=" + encodeURIComponent(BX.message('sonetLPathToSmile'))
		+ "&p_ubp=" + encodeURIComponent(BX.message('sonetLPathToUserBlogPost'))
		+ "&p_gbp=" + encodeURIComponent(BX.message('sonetLPathToGroupBlogPost'))
		+ "&p_umbp=" + encodeURIComponent(BX.message('sonetLPathToUserMicroblogPost'))
		+ "&p_gmbp=" + encodeURIComponent(BX.message('sonetLPathToGroupMicroblogPost'))
		+ "&f_id=" + encodeURIComponent(BX.message('sonetLForumID'))
		+ "&bapc=" + encodeURIComponent(BX.message('sonetLBlogAllowPostCode'))
		+ "&site=" + encodeURIComponent(BX.message('sonetLSiteId'))
		+ "&lang=" + encodeURIComponent(BX.message('sonetLLangId'))
		+ "&message=" + encodeURIComponent(textarea.value)
		+ "&action=add_comment"
	);
	textarea.value = '';
	__logCommentShowWait(500, log_id);
}

function __logCommentGet(logID, commentID, strMessage)
{
	var container = BX('feed_comments_block_' + logID);

	if (container && container != null)
	{
		if (commentID > 0)
		{
			var sonetLXmlHttpGet3 = new XMLHttpRequest();

			var params = "action=get_comment"
				+ "&site=" + BX.util.urlencode(BX.message('sonetLSiteId'))
				+ "&lang=" + BX.util.urlencode(BX.message('sonetLLangId'))
				+ "&nt=" + BX.util.urlencode(BX.message('sonetLNameTemplate'))
				+ "&dtf=" + BX.util.urlencode(BX.message('sonetLDateTimeFormat'))
				+ "&sl=" + BX.util.urlencode(BX.message('sonetLShowLogin'))
				+ "&as=" + BX.util.urlencode(BX.message('sonetLAvatarSizeComment'))
				+ "&p_user=" + BX.util.urlencode(BX.message('sonetLPathToUser'))
				+ "&p_smile=" + BX.util.urlencode(BX.message('sonetLPathToSmile'))
				+ "&cid=" + BX.util.urlencode(commentID);

			sonetLXmlHttpGet3.open(
				"get",
				BX.message('sonetLGetPath') + "?" + BX.message('sonetLSessid')
					+ "&" + params
					+ "&r=" + Math.floor(Math.random() * 1000)
			);
			sonetLXmlHttpGet3.send(null);

			sonetLXmlHttpGet3.onreadystatechange = function()
			{
				if(sonetLXmlHttpGet3.readyState == 4)
				{
					if(sonetLXmlHttpGet3.status == 200)
					{
						var data = LBlock.DataParser(sonetLXmlHttpGet3.responseText);
						if (typeof(data) == "object")
						{
							if (data[0] == '*')
							{
								if (sonetLErrorDiv != null)
								{
									sonetLErrorDiv.style.display = "block";
									sonetLErrorDiv.innerHTML = sonetLXmlHttpGet3.responseText;
								}
								return;
							}
							sonetLXmlHttpGet3.abort();
							var arComment = data["arComment"];
							var arCommentFormatted = data["arCommentFormatted"];
							__logCommentShow(arCommentFormatted, container);

						}
					}
					else
					{
						// error!

					}
				}
			}
		}
		else if (strMessage.length > 0)
			__logMessageShow(strMessage, container);
	}
}

function __logCommentShow(arComment, container)
{
	anchor_id = Math.floor(Math.random()*100000) + 1;
	avatar = false;
	containerHidden = null;

	if (container)
	{
		var commentsFull = BX.findChild(container, {'tag': 'div', 'className': 'feed-comments-full'}, false);
		var commentsFullInner = BX.findChild(commentsFull, {'tag': 'div', 'className': 'feed-comments-full-inner'}, false);
		var commentsLimited = BX.findChild(container, {'tag': 'div', 'className': 'feed-comments-limited'}, false);

		if (commentsFull && commentsFull.style.display != 'none')
		{
			container = BX.findChild(commentsFull, {'tag': 'div', 'className': 'feed-comments-full-inner'} );
			containerHidden = BX.findChild(commentsLimited, {'tag': 'div', 'className': 'feed-comments-limited-inner'} );
		}
		else if (commentsLimited)
		{
			container = BX.findChild(commentsLimited, {'tag': 'div', 'className': 'feed-comments-limited-inner'} );
			if (commentsFullInner.innerHTML.length > 0)
				containerHidden = BX.findChild(commentsFull, {'tag': 'div', 'className': 'feed-comments-full-inner'} );
		}
		else
		{
			commentsFull = container.insertBefore(BX.create('div', { props: { 'className': 'feed-comments-full' } } ), BX.findChild(container, { 'tag': 'div', 'className': 'feed-com-footer' } ));
			container = commentsFull.appendChild(BX.create('div', { props: { 'className': 'feed-comments-full-inner' } } ));
		}

		if (arComment["AVATAR_SRC"] && arComment["AVATAR_SRC"] != 'undefined')
			avatar = BX.create('div', { props: { 'className': 'feed-com-avatar' }, style: { background: "url('" + arComment["AVATAR_SRC"] + "') no-repeat center #FFFFFF" } } );
		else
			avatar = BX.create('div', { props: { 'className': 'feed-com-avatar' } } );

		var newCommentNode = BX.create('div', {
			props: { 'className': 'feed-com-block' },
			children: [
				avatar,
				BX.create('span', {
					props: { 'className': 'feed-com-name' },
					children: [
						BX.create('a', {
							props: { 'id': 'anchor_' + anchor_id },
							attrs: { 'href': arComment["CREATED_BY"]["URL"] },
							html: arComment["CREATED_BY"]["FORMATTED"]
						})
					]
				}),
				BX.create('div', {
					props: { 'className': 'feed-com-informers' },
					children: [
						BX.create('span', { props: { 'className': 'feed-time' }, html: arComment["LOG_TIME_FORMAT"] } )
					]
				}),
				BX.create('div', {
					props: { 'className': 'feed-com-text-wrap' },
					children: [
						BX.create('div', {
							props: { 'className': 'feed-com-text' },
							children: [
								BX.create('div', {
									props: { 'className': 'feed-com-text-inner' },
									children: [
										BX.create('div', {
											props: { 'className': 'feed-com-text-inner-inner' },
											children: [
												BX.create('span', { html: arComment["MESSAGE_FORMAT"] })
											]
										})
									]
								})
							]
						})
					]
				})
			]
		});

		container.appendChild(newCommentNode);

		//adding comment copy to have comments both in limited and full modes
		if (containerHidden !== null)
		{
			newCommentCopy = BX.clone(newCommentNode);
			containerHidden.appendChild(newCommentCopy);
			containerHidden.style.display = 'none';
		}

		BX.tooltip(arComment["USER_ID"], "anchor_" + anchor_id, "");

		commentsNode = BX.findParent(container, {'className': 'sonet-log-item-comments'});
		if (BX(commentsNode) && BX(commentsNode).style.maxHeight != '')
		{
			tmpPos = BX(commentsNode).style.maxHeight.indexOf('px');
			iMaxHeight = parseInt(BX(commentsNode).style.maxHeight.substr(0, tmpPos));
			BX(commentsNode).style.maxHeight = (iMaxHeight + newCommentNode.offsetHeight) + 'px';
		}

	}
}

function __logMessageShow(strMessage, container)
{
	if (container)
	{
		var commentsFull = BX.findChild(container, {'tag': 'span', 'className': 'feed-comments-full-inner'}, false);
		var commentsLimited = BX.findChild(container, {'tag': 'span', 'className': 'feed-comments-limited-inner'}, false);

		if (commentsFull && commentsFull.style.display != 'none')
			container = commentsFull;
		else if (commentsLimited && commentsLimited.style.display != 'none')
			container = commentsLimited;
		else
		{
			commentsFull = container.appendChild(BX.create('span', {
					props: {
						'className': 'feed-com-block'
					}
				})
			);
			container = commentsFull;
		}

		if (container)
			container.appendChild(BX.create('div', {
				props: {
				},
				html: strMessage
			})
			);
	}
}

function __logComments(logID, ts, bFollow)
{
	bFollow = !!bFollow;
	var container = BX('feed_comments_block_' + logID);
	var contentBlock = false;
	var expandBlock = false;
	var comment_message = '';
	var comment_datetime = '';
	var avatar = false;
	var class_name_unread = '';
	var you_like_class = "";
	var you_like_text = "";
	var vote_text = null;

	if (container && container != null)
	{
		var commentsFull = BX.findChild(container, {'tag': 'div', 'className': 'feed-comments-full'}, false);
		var commentsLimited = BX.findChild(container, {'tag': 'div', 'className': 'feed-comments-limited'}, false);

		if (commentsFull && commentsLimited)
		{
			var commentsFullInner = BX.findChild(commentsFull, {'tag': 'div', 'className': 'feed-comments-full-inner'}, false);
			var commentsLimitedInner = BX.findChild(commentsLimited, {'tag': 'div', 'className': 'feed-comments-limited-inner'}, false);

			var commentsAll = BX.findChild(container, {'tag': 'div', 'className': 'feed-com-all'}, true);
			if (BX(commentsAll))
			{
				var commentsAllText = BX.findChild(commentsAll, {'tag': 'span', 'className': 'feed-com-all-text'}, true);
				var commentsAllCount = BX.findChild(commentsAll, {'tag': 'span', 'className': 'feed-comments-all-count'}, true);
				var commentsAllHide = BX.findChild(commentsAll, {'tag': 'span', 'className': 'feed-comments-all-hide'}, true);
			}

			//no comments in full mode - make AJAX request
			if (commentsFullInner.innerHTML.length <= 0 && commentsFull.style.display == 'none')
			{
				if (!BX.util.in_array(logID, arrGetComments))
					arrGetComments[arrGetComments.length] = logID;
				else
					return;

				var sonetLXmlHttpGet4 = new XMLHttpRequest();
				var params = "action=get_comments"
					+ "&lang=" + BX.util.urlencode(BX.message('sonetLLangId'))
					+ "&site=" + BX.util.urlencode(BX.message('sonetLSiteId'))
					+ "&nt=" + BX.util.urlencode(BX.message('sonetLNameTemplate'))
					+ "&sl=" + BX.util.urlencode(BX.message('sonetLShowLogin'))
					+ "&as=" + BX.util.urlencode(BX.message('sonetLAvatarSizeComment'))
					+ "&p_user=" + BX.util.urlencode(BX.message('sonetLPathToUser'))
					+ "&p_smile=" + BX.util.urlencode(BX.message('sonetLPathToSmile'))
					+ "&logid=" + BX.util.urlencode(logID);

				sonetLXmlHttpGet4.open(
					"get",
					BX.message('sonetLGetPath') + "?" + BX.message('sonetLSessid')
						+ "&" + params
						+ "&r=" + Math.floor(Math.random() * 1000)
				);
				sonetLXmlHttpGet4.send(null);

				sonetLXmlHttpGet4.onreadystatechange = function()
				{
					if(sonetLXmlHttpGet4.readyState == 4)
					{
						if (BX.util.in_array(logID, arrGetComments))
							for (key in arrGetComments)
								if (arrGetComments[key] == logID)
									arrGetComments.splice(key, 1);

						if(sonetLXmlHttpGet4.status == 200)
						{
							var data = LBlock.DataParser(sonetLXmlHttpGet4.responseText);
							if (typeof(data) == "object")
							{
								if (data[0] == '*')
								{
									if (sonetLErrorDiv != null)
									{
										sonetLErrorDiv.style.display = "block";
										sonetLErrorDiv.innerHTML = sonetLXmlHttpGet4.responseText;
									}
									return;
								}
								sonetLXmlHttpGet4.abort();
								var arComments = data["arComments"];

								for (var i = 0; i < arComments.length; i++)
								{
									anchor_id = Math.floor(Math.random()*100000) + 1;

									contentBlock = false;

									if (
										arComments[i]["EVENT_FORMATTED"]
										&& arComments[i]["EVENT_FORMATTED"]['MESSAGE']
										&& arComments[i]["EVENT_FORMATTED"]['MESSAGE'].length > 0
									)
										comment_message = arComments[i]['EVENT_FORMATTED']['MESSAGE'];
									else
										comment_message = arComments[i]['EVENT']['MESSAGE'];

									if (arComments[i]["AVATAR_SRC"] && arComments[i]["AVATAR_SRC"] != 'undefined')
										avatar = BX.create('div', {
											props: {
												'className': 'feed-com-avatar'
											},
											style: { background: "url('" + arComments[i]["AVATAR_SRC"] + "') no-repeat center #FFFFFF" }
										});
									else
										avatar = BX.create('div', {
											props: {
												'className': 'feed-com-avatar'
											}
										});

									if (
										arComments[i]["EVENT_FORMATTED"] != 'undefined'
										&& arComments[i]["EVENT_FORMATTED"]['DATETIME'] != 'undefined'
									)
										comment_datetime = arComments[i]["EVENT_FORMATTED"]['DATETIME'];
									else
										comment_datetime = arComments[i]["LOG_TIME_FORMAT"];

									if (
										bFollow
										&& parseInt(arComments[i]["LOG_DATE_TS"]) > ts
										&& arComments[i]["EVENT"]["USER_ID"] != BX.message('sonetLCurrentUserID')
									)
										class_name_unread = ' feed-com-block-new';
									else
										class_name_unread = '';

									ratingNode = null;

									if (
										arComments[i]["EVENT"]["RATING_TYPE_ID"].length > 0
										&& arComments[i]["EVENT"]["RATING_ENTITY_ID"] > 0
										&& BX.message("sonetLShowRating") == 'Y'
									)
									{
										if (BX.message("sonetLRatingType") == "like")
										{
											you_like_class = (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] > 0) ? " bx-you-like" : "";
											you_like_text = (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] > 0) ? BX.message("sonetLTextLikeN") : BX.message("sonetLTextLikeY");

											if (arComments[i]["EVENT_FORMATTED"]["ALLOW_VOTE"]['RESULT'])
												vote_text = BX.create('span', {
													props: {
														'className': 'bx-ilike-text'
													},
													html: you_like_text
												});
											else
												vote_text = null;

											ratingNode = BX.create('span', {
												props: {
													'className': 'sonet-log-comment-like rating_vote_text'
												},
												children: [
													BX.create('span', {
														props: {
															'className': 'ilike-light'
														},
														children: [
															BX.create('span', {
																props: {
																	'id': 'bx-ilike-button-' + arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id,
																	'className': 'bx-ilike-button'
																},
																children: [
																	BX.create('span', {
																		props: {
																			'className': 'bx-ilike-right-wrap' + you_like_class
																		},
																		children: [
																			BX.create('span', {
																				props: {
																					'className': 'bx-ilike-right'
																				},
																				html: arComments[i]["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"]
																			})
																		]
																	}),
																	BX.create('span', {
																		props: {
																			'className': 'bx-ilike-left-wrap'
																		},
																		children: [
																			vote_text
																		]
																	})
																]
															}),
															BX.create('span', {
																props: {
																	'id': 'bx-ilike-popup-cont-' + arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id,
																	'className': 'bx-ilike-wrap-block'
																},
																style: {
																	'display': 'none'
																},
																children: [
																	BX.create('span', {
																		props: {
																			'className': 'bx-ilike-popup'
																		},
																		children: [
																			BX.create('span', {
																				props: {
																					'className': 'bx-ilike-wait'
																				}
																			})
																		]
																	})
																]
															})
														]
													})
												]
											});
										}
										else if (BX.message("sonetLRatingType") == "standart_text")
										{
											ratingNode = BX.create('span', {
												props: {
													'className': 'sonet-log-comment-like rating_vote_text'
												},
												children: [
													BX.create('span', {
														props: {
															'className': 'bx-rating' + (!arComments[i]["EVENT_FORMATTED"]["ALLOW_VOTE"]['RESULT'] ? ' bx-rating-disabled' : '') + (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] != 0 ? ' bx-rating-active' : ''),
															'id': 'bx-rating-' + arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id,
															'title': (!arComments[i]["EVENT_FORMATTED"]["ALLOW_VOTE"]['RESULT'] ? arComments[i]["EVENT_FORMATTED"]["ERROR_MSG"] : '')
														},
														children: [
															BX.create('span', {
																props: {
																	'className': 'bx-rating-absolute'
																},
																children: [
																	BX.create('span', {
																		props: {
																			'className': 'bx-rating-question'
																		},
																		html: (!arComments[i]["EVENT_FORMATTED"]["ALLOW_VOTE"]['RESULT'] ? BX.message("sonetLTextDenied") : BX.message("sonetLTextAvailable"))
																	}),
																	BX.create('span', {
																		props: {
																			'className': 'bx-rating-yes ' +  (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] > 0 ? '  bx-rating-yes-active' : ''),
																			'title': (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] > 0 ? BX.message("sonetLTextCancel") : BX.message("sonetLTextPlus"))
																		},
																		children: [
																			BX.create('a', {
																				props: {
																					'className': 'bx-rating-yes-count',
																					'href': '#like'
																				},
																				html: ""+parseInt(arComments[i]["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"])
																			}),
																			BX.create('a', {
																				props: {
																					'className': 'bx-rating-yes-text',
																					'href': '#like'
																				},
																				html: BX.message("sonetLTextRatingY")
																			})
																		]
																	}),
																	BX.create('span', {
																		props: {
																			'className': 'bx-rating-separator'
																		},
																		html: '/'
																	}),
																	BX.create('span', {
																		props: {
																			'className': 'bx-rating-no ' +  (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] < 0 ? '  bx-rating-no-active' : ''),
																			'title': (arComments[i]["EVENT"]["RATING_USER_VOTE_VALUE"] < 0 ? BX.message("sonetLTextCancel") : BX.message("sonetLTextMinus"))
																		},
																		children: [
																			BX.create('a', {
																				props: {
																					'className': 'bx-rating-no-count',
																					'href': '#dislike'
																				},
																				html: ""+parseInt(arComments[i]["EVENT"]["RATING_TOTAL_NEGATIVE_VOTES"])
																			}),
																			BX.create('a', {
																				props: {
																					'className': 'bx-rating-no-text',
																					'href': '#dislike'
																				},
																				html: BX.message("sonetLTextRatingN")
																			})
																		]
																	})
																]
															})
														]
													}),
													BX.create('span', {
														props: {
															'id': 'bx-rating-popup-cont-' + arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id + '-plus'
														},
														style: {
															'display': 'none'
														},
														children: [
															BX.create('span', {
																props: {
																	'className': 'bx-ilike-popup  bx-rating-popup'
																},
																children: [
																	BX.create('span', {
																		props: {
																			'className': 'bx-ilike-wait'
																		}
																	})
																]
															})
														]
													}),
													BX.create('span', {
														props: {
															'id': 'bx-rating-popup-cont-' + arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id + '-minus'
														},
														style: {
															'display': 'none'
														},
														children: [
															BX.create('span', {
																props: {
																	'className': 'bx-ilike-popup  bx-rating-popup'
																},
																children: [
																	BX.create('span', {
																		props: {
																			'className': 'bx-ilike-wait'
																		}
																	})
																]
															})
														]
													})
												]
											});
										}
									}

									if (comment_message.length > 0)
										contentBlock = BX.create('div', {
											props: {
												'className': 'feed-com-text'
											},
											children: [
												BX.create('div', {
													props: {
														'className': 'feed-com-text-inner'
													},
													children: [
														BX.create('div', {
															props: {
																'className': 'feed-com-text-inner-inner'
															},
															children: [
																BX.create('span', {
																	html: arComments[i]["EVENT_FORMATTED"]['FULL_MESSAGE_CUT']
																})
															]
														})
													]
												}),
												BX.create('div', {
													props: {
														'className': 'feed-post-text-more'
													},
													'events': {
														'click': BX.delegate(__logCommentExpand, this)
													},
													children: [
														BX.create('div', {
															props: {
																'className': 'feed-post-text-more-but'
															},
															children: [
																BX.create('div', {
																	props: {
																		'className': 'feed-post-text-more-left'
																	}
																}),
																BX.create('div', {
																	props: {
																		'className': 'feed-post-text-more-right'
																	}
																})
															]
														})
													]
												})
											]
										});

									commentsFullInner.appendChild(BX.create('div', {
										props: {
											'className': 'feed-com-block sonet-log-createdby-' + arComments[i]["EVENT"]["USER_ID"] + class_name_unread
										},
										children: [
											avatar,
											BX.create('span', {
												props: {
													'className': 'feed-com-name'
												},
												children: [
													BX.create('a', {
														props: {
															'id': 'anchor_' + anchor_id
														},
														attrs: {
															'href': arComments[i]["CREATED_BY"]["URL"]
														},
														html: arComments[i]["CREATED_BY"]["FORMATTED"]
													})
												]
											}),
											BX.create('div', {
												props: {
													'className': 'feed-com-informers'
												},
												children: [
													BX.create('span', {
														props: {
															'className': 'feed-time'
														},
														html: comment_datetime
													}),
													ratingNode
												]
											}),
											BX.create('div', {
												props: {
													'className': 'feed-com-text-wrap'
												},
												children: [ contentBlock ]
											})
										]
									})
									);

										if (ratingNode)
										{
											if (BX.message("sonetLRatingType") == "like")
											{
												RatingLike.Set(
													arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id,
													arComments[i]["EVENT"]["RATING_TYPE_ID"],
													arComments[i]["EVENT"]["RATING_ENTITY_ID"],
													(!arComments[i]["EVENT_FORMATTED"]["ALLOW_VOTE"]['RESULT']) ? 'N' : 'Y',
													BX.message('sonetLCurrentUserID'),
													{
														'LIKE_Y' : BX.message('sonetLTextLikeN'),
														'LIKE_N' : BX.message('sonetLTextLikeY'),
														'LIKE_D' : BX.message('sonetLTextLikeD')
													},
													'light',
													BX.message('sonetLPathToUser')
												);
											}
											else if (BX.message("sonetLRatingType") == "standart_text")
											{
												Rating.Set(
													arComments[i]["EVENT"]["RATING_TYPE_ID"] + '-' + arComments[i]["EVENT"]["RATING_ENTITY_ID"] + '-' + anchor_id,
													arComments[i]["EVENT"]["RATING_TYPE_ID"],
													arComments[i]["EVENT"]["RATING_ENTITY_ID"],
													(!arComments[i]["EVENT_FORMATTED"]["ALLOW_VOTE"]['RESULT']) ? 'N' : 'Y',
													BX.message('sonetLCurrentUserID'),
													{
														'PLUS' : BX.message('sonetLTextPlus'),
														'MINUS' : BX.message('sonetLTextMinus'),
														'CANCEL' : BX.message('sonetLTextCancel')
													},
													'light',
													BX.message('sonetLPathToUser')
												);
											}
										}

										BX.tooltip(arComments[i]["EVENT"]["USER_ID"], "anchor_" + anchor_id, "");
								}

								if (BX(commentsAll))
								{
									BX.addClass(commentsAll, 'feed-com-all-expanded');
									if (BX(commentsAllText))
										BX(commentsAllText).style.display = 'none';
									if (BX(commentsAllCount))
										BX(commentsAllCount).style.display = 'none';
									if (BX(commentsAllHide))
										BX(commentsAllHide).style.display = 'inline-block';
								}

								commentsLimited.style.display = 'none';

								var fxStart = 0;
								commentsFull.style.maxHeight = fxStart + 'px';
								commentsFull.style.display = 'block';

								var fxFinish = commentsFullInner.offsetHeight;

								var time = 1.0 * fxFinish / 1200;
								if(time < 0.3)
									time = 0.3;
								if(time > 0.8)
									time = 0.8;

								(new BX.fx({
									time: time,
									step: 0.05,
									type: 'linear',
									start: fxStart,
									finish: fxFinish,
									callback: BX.delegate(__logEventExpandSetHeight, commentsFull),
									callback_complete: BX.delegate(function()
									{
										commentsFull.style.maxHeight = 'none';
									})
								})).start();
							}
						}
						else
						{
							// error!

						}
					}
				}
			}
			//comments in full mode are hidden - show full list
			else if (commentsFull.style.display == 'none')
			{
				commentsFullInner.style.display = 'block';

				if (BX(commentsAll))
				{
					BX.addClass(commentsAll, 'feed-com-all-expanded');
					if (BX(commentsAllText))
						BX(commentsAllText).style.display = 'none';
					if (BX(commentsAllCount))
						BX(commentsAllCount).style.display = 'none';
					if (BX(commentsAllHide))
						BX(commentsAllHide).style.display = 'inline-block';
				}

				commentsLimited.style.display = 'none';
				commentsFull.style.display = 'block';

				var fxStart = 0;
				var fxFinish = commentsFullInner.offsetHeight;

				var time = 1.0 * fxFinish / 1200;
				if(time < 0.3)
					time = 0.3;
				if(time > 0.8)
					time = 0.8;

				(new BX.fx({
					time: time,
					step: 0.05,
					type: 'linear',
					start: fxStart,
					finish: fxFinish,
					callback: BX.delegate(__logEventExpandSetHeight, commentsFull),
					callback_complete: BX.delegate(function()
					{
						commentsFull.style.maxHeight = 'none';
					})
				})).start();
			}
			//show limited list
			else
			{
				commentsLimitedInner.style.display = 'block';

				if (BX(commentsAll))
				{
					BX.removeClass(commentsAll, 'feed-com-all-expanded');
					if (BX(commentsAllText))
						BX(commentsAllText).style.display = 'inline-block';
					if (BX(commentsAllCount))
						BX(commentsAllCount).style.display = 'inline-block';
					if (BX(commentsAllHide))
						BX(commentsAllHide).style.display = 'none';
				}

				var fxStart = commentsFullInner.offsetHeight;
				var fxFinish = 0;

				var time = 1.0 * fxStart / 1200;
				if(time < 0.3)
					time = 0.3;
				if(time > 0.8)
					time = 0.8;

				(new BX.fx({
					time: time,
					step: 0.05,
					type: 'linear',
					start: fxStart,
					finish: fxFinish,
					callback: BX.delegate(__logEventExpandSetHeight, commentsFull),
					callback_complete: BX.delegate(function()
					{
						commentsFull.style.display = 'none';
						commentsLimited.style.display = 'block';
//						commentsLimited.style.maxHeight = commentsLimitedInner.offsetHeight + 'px';
						commentsLimited.style.maxHeight = 'none';
					})
				})).start();
			}
		}
	}
}

function __logCommentFormAutogrow(el)
{
	var placeNodeoffsetHeightOld = 0;

	if (el && BX.type.isDomNode(el))
		var textarea = el;
	else
	{
		var textarea = BX.proxy_context;
		var event = el || window.event;

		if ((event.keyCode == 13 || event.keyCode == 10) && event.ctrlKey)
			__logCommentAdd();
	}

	var placeNode = BX.findParent(textarea, {'className': 'sonet-log-comment-form-place'});
	if (BX(placeNode))
		placeNodeoffsetHeightOld = BX(placeNode).offsetHeight;

	var linesCount = 0;
	var lines = textarea.value.split('\n');

	for (var i=lines.length-1; i>=0; --i)
		linesCount += Math.floor((lines[i].length / CommentFormColsDefault) + 1);

	if (linesCount >= CommentFormRowsDefault)
		textarea.rows = linesCount + 1;
	else
		textarea.rows = CommentFormRowsDefault;
}

function __logCommentShowWait(timeout, log_id)
{
	var comments_block = BX('feed_comments_block_' + log_id);

	var pForm = BX('sonet_log_comment_form_container');
	pForm.style.display = 'none';

	var place = pForm.parentNode;
	if (BX(place))
	{
		var commentLink = BX.findPreviousSibling(place, {'tag': 'div', 'className': 'feed-com-footer'});
		if (BX(commentLink))
			commentLink.style.display = 'block';
	}

	waitDiv = waitDiv || comments_block;
	comments_block = BX(comments_block || waitDiv);

	if (timeout !== 0)
	{
		return (waitTimeout = setTimeout(function(){
			__logCommentShowWait(0, log_id)
		}, timeout || waitTime));
	}

	if (!waitPopup)
	{
		waitPopup = new BX.PopupWindow('log_comment_wait', comments_block, {
			autoHide: true,
			lightShadow: true,
			zIndex: 2,
			content: BX.create('DIV', {props: {className: 'log-comment-wait'}})
		});
	}
	else
		waitPopup.setBindElement(comments_block);

	var height = comments_block.offsetHeight, width = comments_block.offsetWidth;
	if (height > 0 && width > 0)
	{
		waitPopup.setOffset({
			offsetTop: -parseInt(height/2+15),
			offsetLeft: parseInt(width/2-15)
		});

		waitPopup.show();
	}

	return waitPopup;
}

function __logCommentCloseWait()
{
	if (waitTimeout)
	{
		clearTimeout(waitTimeout);
		waitTimeout = null;
	}

	if (waitPopup)
		waitPopup.close();
}

function __logMoveBody(div_id, container_id)
{
	var node = BX(div_id);
	if (node)
	{
		node.parentNode.removeChild(node);
		BX(container_id).appendChild(node);
		BX.onCustomEvent(window, 'onSocNetLogMoveBody', [div_id, container_id]);
	}
}

function __logOnAjaxInsertToNode(params) {
	var arPos = false;


	if (BX('sonet_log_more_container'))
	{
		nodeTmp1 = BX.findChild(BX('sonet_log_more_container'), {'tag':'span', 'className': 'feed-new-message-inf-text'}, false);
		nodeTmp2 = BX.findChild(BX('sonet_log_more_container'), {'tag':'span', 'className': 'feed-new-message-inf-text-waiting'}, false);
		if (nodeTmp1 && nodeTmp2)
		{
			nodeTmp1.style.display = 'none';
			nodeTmp2.style.display = 'inline';
		}
		arPos = BX.pos(BX('sonet_log_more_container'));
		nodeTmp1Cap = document.body.appendChild(BX.create('div', {
			style: {
				position: 'absolute',
				width: arPos.width + 'px',
				height: arPos.height + 'px',
				top: arPos.top + 'px',
				left: arPos.left + 'px',
				zIndex: 1000
			}
		}));
	}

	if (BX('sonet_log_counter_2_container'))
	{
		nodeTmp1 = BX.findChild(BX('sonet_log_counter_2_container'), {'tag':'span', 'className': 'feed-new-message-inf-text'}, false);
		nodeTmp2 = BX.findChild(BX('sonet_log_counter_2_container'), {'tag':'span', 'className': 'feed-new-message-inf-text-waiting'}, false);

		if (nodeTmp1 && nodeTmp2)
		{
			nodeTmp1.style.display = 'none';
			nodeTmp2.style.display = 'inline';
		}
		arPos = BX.pos(BX('sonet_log_more_container'));
		nodeTmp2Cap = document.body.appendChild(BX.create('div', {
			style: {
				position: 'absolute',
				width: arPos.width + 'px',
				height: arPos.height + 'px',
				top: arPos.top + 'px',
				left: arPos.left + 'px',
				zIndex: 1000
			}
		}));
	}

	BX.unbind(BX('sonet_log_counter_2_container'), 'click', __logOnAjaxInsertToNode);
}

function sonetLClearContainerExternalNew()
{
	logAjaxMode = 'new';
	BX.addCustomEvent('onAjaxSuccess', _sonetLClearContainerExternal);
}

function sonetLClearContainerExternalMore()
{
	logAjaxMode = 'more';
	BX.addCustomEvent('onAjaxSuccess', _sonetLClearContainerExternal);
}

function _sonetLClearContainerExternal(mode)
{
	if (BX.message('sonetLContainerExternal') != null && logAjaxMode == 'new')
	{
		contentDiv = BX.findChildren(BX(BX.message('sonetLContainerExternal')), { tagName: "div", className : "feed-wrap" }, false)
		for (var i = 0; i < contentDiv.length; i++)
			BX.cleanNode(contentDiv[i]);
	}


	if (BX('sonet_log_more_container'))
	{
		nodeTmp1 = BX.findChild(BX('sonet_log_more_container'), {'tag':'span', 'className': 'feed-new-message-inf-text'}, false);
		nodeTmp2 = BX.findChild(BX('sonet_log_more_container'), {'tag':'span', 'className': 'feed-new-message-inf-text-waiting'}, false);
		if (nodeTmp1 && nodeTmp2)
		{
			nodeTmp1.style.display = 'inline';
			nodeTmp2.style.display = 'none';
		}
	}

	if (BX('sonet_log_counter_2_container'))
	{
		BX("sonet_log_counter_2_container").style.display = "none";
		nodeTmp1 = BX.findChild(BX('sonet_log_counter_2_container'), {'tag':'span', 'className': 'feed-new-message-inf-text'}, false);
		nodeTmp2 = BX.findChild(BX('sonet_log_counter_2_container'), {'tag':'span', 'className': 'feed-new-message-inf-text-waiting'}, false);

		if (nodeTmp1 && nodeTmp2)
		{
			nodeTmp1.style.display = 'inline';
			nodeTmp2.style.display = 'none';
		}
	}

	if (nodeTmp1Cap && nodeTmp1Cap.parentNode)
		nodeTmp1Cap.parentNode.removeChild(nodeTmp1Cap);
	if (nodeTmp2Cap && nodeTmp2Cap.parentNode)
		nodeTmp2Cap.parentNode.removeChild(nodeTmp2Cap);

	if (BX("sonet_log_counter_preset") && logAjaxMode == 'new')
		BX("sonet_log_counter_preset").style.display = "none";

	BX.removeCustomEvent('onAjaxSuccess', _sonetLClearContainerExternal);
}

function __logChangeFavorites(log_id)
{
	var node = BX.proxy_context;

	if (!log_id)
		return;

	var sonetLXmlHttpSet5 = new XMLHttpRequest();

	sonetLXmlHttpSet5.open("POST", BX.message('sonetLSetPath'), true);
	sonetLXmlHttpSet5.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	sonetLXmlHttpSet5.onreadystatechange = function()
	{
		if(sonetLXmlHttpSet5.readyState == 4)
		{
			if(sonetLXmlHttpSet5.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpSet5.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet5.responseText;
						}
						return;
					}
					sonetLXmlHttpSet5.abort();

					var strMessage = '';

					if (data["bResult"] != 'undefined' && (data["bResult"] == "Y" || data["bResult"] == "N"))
					{
						if (BX.hasClass(BX(node), 'menu-popup-item-text'))
							var nodeToAdjust = BX(node);
						else
							var nodeToAdjust = BX.findChild(BX(node), { 'className': 'menu-popup-item-text' });

						if (BX(nodeToAdjust))
							BX.adjust(BX(nodeToAdjust), {html: (data["bResult"] == "Y" ? BX.message('sonetLMenuFavoritesTitleY'): BX.message('sonetLMenuFavoritesTitleN'))} )
					}
				}
			}
			else
			{
				// error!
			}
		}
	}

	sonetLXmlHttpSet5.send("r=" + Math.floor(Math.random() * 1000)
		+ "&" + BX.message('sonetLSessid')
		+ "&site=" + BX.util.urlencode(BX.message('SITE_ID'))
		+ "&log_id=" + encodeURIComponent(log_id)
		+ "&action=change_favorites"
	);

}

function __logChangeCounter(count)
{
	if (parseInt(count) > 0)
	{
		if (BX("sonet_log_counter_2"))
			BX("sonet_log_counter_2").innerHTML = count;
		if (BX("sonet_log_counter_2_container"))
			BX("sonet_log_counter_2_container").style.display = "block";
	}
	else
	{
		if (BX("sonet_log_counter_2_container"))
			BX("sonet_log_counter_2_container").style.display = "none";
		if (BX("sonet_log_counter_2"))
			BX("sonet_log_counter_2").innerHTML = "0";
	}
}

function __logChangeCounterArray(arCount)
{
	if (typeof arCount[BX.message('sonetLCounterType')] != 'undefined')
		__logChangeCounter(arCount[BX.message('sonetLCounterType')]);
}

function __logEventExpand(node)
{
	if (BX(node))
	{
		BX(node).style.display = "none";

		var tmpNode = BX.findParent(BX(node), {'tag': 'div', 'className': 'feed-post-text-block'});
		if (tmpNode)
		{
			var contentContrainer = BX.findChild(tmpNode, {'tag': 'div', 'className': 'feed-post-text-block-inner'}, true);
			var contentNode = BX.findChild(tmpNode, {'tag': 'div', 'className': 'feed-post-text-block-inner-inner'}, true);

			if (contentContrainer && contentNode)
			{
				fxStart = 300;
				fxFinish = contentNode.offsetHeight;

				(new BX.fx({
					time: 1.0 * (contentNode.offsetHeight - fxStart) / (1200 - fxStart),
					step: 0.05,
					type: 'linear',
					start: fxStart,
					finish: fxFinish,
					callback: BX.delegate(__logEventExpandSetHeight, contentContrainer),
					callback_complete: BX.delegate(function()
					{
						contentContrainer.style.maxHeight = 'none';
					})
				})).start();
			}
		}
	}
}

function __logCommentExpand(node)
{
	if (!BX.type.isDomNode(node))
		node = BX.proxy_context;

	if (BX(node))
	{
		var topContrainer = BX.findParent(BX(node), {'tag': 'div', 'className': 'feed-com-text'});
		if (topContrainer)
		{
			BX.remove(node);
			var contentContrainer = BX.findChild(topContrainer, {'tag': 'div', 'className': 'feed-com-text-inner'}, true);
			var contentNode = BX.findChild(topContrainer, {'tag': 'div', 'className': 'feed-com-text-inner-inner'}, true);

			if (contentNode && contentContrainer)
			{
				fxStart = 200;
				fxFinish = contentNode.offsetHeight;

				var time = 1.0 * (fxFinish - fxStart) / (2000 - fxStart);
				if(time < 0.3)
					time = 0.3;
				if(time > 0.8)
					time = 0.8;

				(new BX.fx({
					time: time,
					step: 0.05,
					type: 'linear',
					start: fxStart,
					finish: fxFinish,
					callback: BX.delegate(__logEventExpandSetHeight, contentContrainer),
					callback_complete: BX.delegate(function()
					{
						contentContrainer.style.maxHeight = 'none';
					})
				})).start();
			}
		}
	}
}

function __logEventExpandSetHeight(height)
{
	this.style.maxHeight = height + 'px';
}

/*
function __logExpandAdjust(rootNodeID)
{
	var minVal = 0;
	var maxVal = 0;
	var commentsNode = false;
	var eventNode = false;
	var arComments = false;

	if (BX(rootNodeID))
	{
		var arItems = BX.findChildren(BX(rootNodeID), {'className':'feed-wrap'}, true);
		if (arItems)
		{
			for (var i = 0; i < arItems.length; i++)
			{
				// event
				eventNode = BX.findChild(arItems[i], {'className':'sonet-log-message-full-event'}, true, false);
				if (BX(eventNode))
				{
					if (BX.hasClass(eventNode.parentNode, "sonet-log-item-content-body-unread"))
					{
						minVal = 170;
						maxVal = 214;
					}
					else
					{
						minVal = 156;
						maxVal = 200;
					}
					if (eventNode.offsetHeight > minVal && eventNode.offsetHeight < maxVal)
						eventNode.style.height = "200px";

					// comments
					commentsNode = BX.findChild(arItems[i], {'className':'sonet-log-item-comments'}, true, false);

					arComments = BX.findChildren(commentsNode, {'className':'sonet-log-comment-body'}, true);
					if (arComments)
					{
						maxVal = 200;
						for (var j = 0; j < arComments.length; j++)
						{
							commentBody = BX.findChild(arComments[j], {'className':'sonet-log-message-full-comment'}, true, false);
							if (BX(commentBody) && arComments[j].offsetHeight > maxVal)
							{
								commentSwitch = BX.findChild(arComments[j], {'className':'sonet-log-comment-switch'}, true, false);
								if (BX(commentSwitch))
									BX(commentSwitch).style.display = 'inline-block';
							}
						}
					}
				}
			}
		}
	}
}
*/

function __logShowPostMenu(bindElement, ind, entity_type, entity_id, event_id, fullset_event_id, user_id, visible, log_id, bFavorites, bUseVisible)
{
	if (bUseVisible)
		var arItems = [
			{ text : (bFavorites ? BX.message('sonetLMenuFavoritesTitleY') : BX.message('sonetLMenuFavoritesTitleN')), className : "menu-popup-no-icon", onclick : function(e) { __logChangeFavorites(log_id); return BX.PreventDefault(e); } },
			{ text : BX.message('sonetLMenuTransportTitle'), className : "menu-popup-no-icon", onclick : function(e) { __logShowTransportDialog(ind, entity_type, entity_id, event_id, fullset_event_id, user_id); return BX.PreventDefault(e); } },
			{ text : BX.message('sonetLMenuVisibleTitle'), className : "menu-popup-no-icon", onclick : function(e) { __logShowVisibleDialog(ind, entity_type, entity_id, event_id, fullset_event_id, user_id, visible); return BX.PreventDefault(e); } }
		];
	else
		var arItems = [
			{ text : (bFavorites ? BX.message('sonetLMenuFavoritesTitleY') : BX.message('sonetLMenuFavoritesTitleN')), className : "menu-popup-no-icon", onclick : function(e) { __logChangeFavorites(log_id); return BX.PreventDefault(e); } },
			{ text : BX.message('sonetLMenuTransportTitle'), className : "menu-popup-no-icon", onclick : function(e) { __logShowTransportDialog(ind, entity_type, entity_id, event_id, fullset_event_id, user_id); return BX.PreventDefault(e); } }
		];

	if (BX.message('sonetLIsB24') == "Y")
		var arParams = {
			offsetLeft: -32,
			offsetTop: 4,
			lightShadow: false,
			angle: {position: 'top', offset : 93}
		};
	else
		var arParams = {
			offsetLeft: -32,
			offsetTop: 4,
			lightShadow: false
		};

	BX.PopupMenu.show("post-menu-" + ind, bindElement, arItems, arParams);
}

function __logShowCommentMenu(bindElement, ind, entity_type, entity_id, event_id, fullset_event_id, user_id, visible)
{

	if (BX.message('sonetLIsB24') == "Y")
		var arParams = {
			offsetLeft: -55,
			offsetTop: 4,
			lightShadow: false,
			angle: {position: 'top', offset: 15}
		};
	else
		var arParams = {
			offsetLeft: 10,
			offsetTop: 0,
			lightShadow: false
		};

	BX.PopupMenu.show("post-menu-" + ind, bindElement, [
		{ text : BX.message('sonetLMenuVisibleTitle'), onclick : function(e) { __logShowVisibleDialog(ind, entity_type, entity_id, event_id, fullset_event_id, user_id, visible); return BX.PreventDefault(e); } }	
	], arParams);
}

function __logShowHiddenDestination(log_id, bindElement)
{

	var sonetLXmlHttpSet6 = new XMLHttpRequest();

	sonetLXmlHttpSet6.open("POST", BX.message('sonetLSetPath'), true);
	sonetLXmlHttpSet6.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	sonetLXmlHttpSet6.onreadystatechange = function()
	{
		if(sonetLXmlHttpSet6.readyState == 4)
		{
			if(sonetLXmlHttpSet6.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpSet6.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet6.responseText;
						}
						return;
					}
					sonetLXmlHttpSet6.abort();
					var arDestinations = data["arDestinations"];
					
					if (typeof (arDestinations) == "object")
					{
						if (BX(bindElement))
						{
							var cont = bindElement.parentNode;
							cont.removeChild(bindElement);
							var url = '';

							for (var i = 0; i < arDestinations.length; i++)
							{
								if (typeof (arDestinations[i]['TITLE']) != 'undefined' && arDestinations[i]['TITLE'].length > 0)
								{
									cont.appendChild(BX.create('SPAN', {
										html: ',&nbsp;'
									}));

									if (typeof (arDestinations[i]['URL']) != 'undefined' && arDestinations[i]['URL'].length > 0)
										cont.appendChild(BX.create('A', {
											props: {
												className: 'feed-add-post-destination-new',
												'href': arDestinations[i]['URL']
											},
											html: arDestinations[i]['TITLE']
										}));
									else
										cont.appendChild(BX.create('SPAN', {
											props: {
												className: 'feed-add-post-destination-new'
											},
											html: arDestinations[i]['TITLE']
										}));
								}
							}
						}
					}
				}
			}
			else
			{
				// error!
			}
		}
	}

	sonetLXmlHttpSet6.send("r=" + Math.floor(Math.random() * 1000)
		+ "&" + BX.message('sonetLSessid')
		+ "&site=" + BX.util.urlencode(BX.message('SITE_ID'))
		+ "&nt=" + BX.util.urlencode(BX.message('sonetLNameTemplate'))
		+ "&log_id=" + encodeURIComponent(log_id)
		+ "&p_user=" + BX.util.urlencode(BX.message('sonetLPathToUser'))
		+ "&p_group=" + BX.util.urlencode(BX.message('sonetLPathToGroup'))
		+ "&p_dep=" + BX.util.urlencode(BX.message('sonetLPathToDepartment'))
		+ "&dlim=" + BX.util.urlencode(BX.message('sonetLDestinationLimit'))
		+ "&action=get_more_destination"
	);

}

function __logSetFollow(log_id)
{
	var strFollowOld = (BX("log_entry_follow_" + log_id, true).getAttribute("data-follow") == "Y" ? "Y" : "N");
	var strFollowNew = (strFollowOld == "Y" ? "N" : "Y");	

	if (BX("log_entry_follow_" + log_id, true))
	{
		BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollow' + strFollowNew);
		BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowNew);
	}
				
	BX.ajax({
		url: BX.message('sonetLSetPath'),
		method: 'POST',
		dataType: 'json',
		data: {
			"log_id": log_id,
			"action": "change_follow",
			"follow": strFollowNew,
			"sessid": BX.bitrix_sessid(),
			"site": BX.message('sonetLSiteId')
		},
		onsuccess: function(data) {
			if (
				data["SUCCESS"] != "Y"
				&& BX("log_entry_follow_" + log_id, true)
			)
			{
				BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollow' + strFollowOld);
				BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowOld);
			}
		},
		onfailure: function(data) {
			if (BX("log_entry_follow_" +log_id, true))
			{
				BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollow' + strFollowOld);
				BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowOld);
			}		
		}
	});
	return false;
}