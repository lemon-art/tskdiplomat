BX.namespace("BX.Sale.Admin.OrderShipment");

BX.Sale.Admin.OrderShipment = function(params)
{
	this.index = params.index;
	this.shipment_statuses = params.shipment_statuses;
	this.isAjax = !!params.isAjax;
	this.srcList = params.src_list;
	this.active = !!params.active;
	this.discounts = params.discounts || {};
	this.discountsMode = params.discountsMode || "edit";

	if (this.active)
	{
		this.initFieldChangeDeducted();
		this.initFieldChangeAllowDelivery();
		this.initFieldChangeStatus();
	}
	this.initFieldUpdateSum();
	this.initChangeProfile();
	this.initCustomEvent();
	this.initToggle();
	this.initDeleteShipment();

	if (this.discounts)
		this.setDiscountsList(this.discounts);

	var updater = [];

	if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
	{
		updater["BASE_PRICE_DELIVERY"] = {
			callback: this.setDeliveryBasePrice,
			context: this
		};

		updater["DELIVERY_PRICE_DISCOUNT"] = {
			callback: this.setDeliveryPrice,
			context: this
		};
	}

	if (!!params.base_price_delivery)
		this.setCustomPriceDelivery(params.base_price_delivery);

	if (params.templateType == 'edit')
	{
		updater["CUSTOM_PRICE"] = {
			callback: this.setCustomPriceDelivery,
			context: this
		};

		updater["DELIVERY_ERROR"] = {
			callback: BX.Sale.Admin.OrderEditPage.showDialog,
			context: this
		};

		updater["MAP"] = {
			callback: this.updateMap,
			context: this
		};

		updater["PROFILES"] = {
			callback: this.updateProfiles,
			context: this
		};

		updater["EXTRA_SERVICES"] = {
			callback: this.updateExtraService,
			context: this
		};

		updater["DELIVERY_SERVICE_LIST"] = {
			callback: this.updateDeliveryList,
			context: this
		};

		if (!!BX.Sale.Admin.OrderBuyer && !!BX.Sale.Admin.OrderBuyer.propertyCollection)
		{
			var propLocation = BX.Sale.Admin.OrderBuyer.propertyCollection.getDeliveryLocation();
			if (propLocation)
			{
				propLocation.addEvent("change", function ()
				{
					BX.Sale.Admin.OrderAjaxer.sendRequest(
						BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData(), true
					);
				});
			}
		}
	}

	updater["DISCOUNTS_LIST"] = {
		callback: this.setDiscountsList,
		context: this
	};

	BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters(updater);
};

BX.Sale.Admin.OrderShipment.prototype.updateDeliveryList = function(services)
{
	var serviceControl = BX('DELIVERY_'+this.index);
	if (!serviceControl)
		return;

	var selectedItem = serviceControl.options[serviceControl.selectedIndex].value;
	serviceControl.innerHTML = services;
	for (var i in serviceControl.options)
	{
		if (serviceControl.options[i].value == selectedItem)
		{
			serviceControl.options[i].selected = true;
			break;
		}
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDiscountsList = function(discounts)
{
	this.discounts = discounts;
	var container = BX("sale-order-shipment-discounts-container-"+this.index),
		row = BX("sale-order-shipment-discounts-row-"+this.index),
		display = "none";

	if(container)
	{
		container.innerHTML = "";

		if(discounts && discounts.RESULT && discounts.RESULT.DELIVERY && discounts.RESULT.DELIVERY.length > 0)
		{
			container.appendChild(
				this.createDiscountsNode(discounts.RESULT.DELIVERY)
			);

			display = "";
		}
	}

	if(row && row.nextElementSibling)
	{
		row.style.display = display;
		row.nextElementSibling.style.display = display;
	}
};

BX.Sale.Admin.OrderShipment.prototype.createDiscountsNode = function(discounts)
{
	return BX.Sale.Admin.OrderEditPage.createDiscountsNode(
		"",
		"DELIVERY",
		discounts,
		this.discounts,
		this.discountsMode == "edit" ? "EDIT" : "VIEW"
	);
};

BX.Sale.Admin.OrderShipment.prototype.updateProfiles = function(profiles)
{
	var selectedItem = null;
	var blockDeliveryService = BX('BLOCK_DELIVERY_SERVICE_' + this.index);
	var blockProfiles = BX('BLOCK_PROFILES_' + this.index);

	var select = BX('PROFILE_' + this.index);
	if (select)
		selectedItem = select.options[select.selectedIndex].value;

	if (blockProfiles)
		BX.remove(blockProfiles);

	var tr = BX.create('tr', {
		props: {
			id: 'BLOCK_PROFILES_' + this.index
		},
		children: [
			BX.create('td', {
				text: BX.message('SALE_ORDER_SHIPMENT_PROFILE')+':',
				style: {
					'width': '40%'
				},
				props: {
					className: 'adm-detail-content-cell-l'
				}
			}),
			BX.create('td', {
				html: profiles,
				props: {
					id: ' PROFILE_SELECT_' + this.index,
					className: 'adm-detail-content-cell-r'
				}
			})
		]
	});
	blockDeliveryService.parentNode.appendChild(tr);

	select = tr.lastChild.firstChild;

	if (selectedItem)
	{
		for (var i in select.options)
		{
			if (select.options[i].value == selectedItem)
			{
				select.options[i].selected = true;
				break;
			}
		}
	}

	BX.bind(select, 'change', BX.proxy(function() {
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
			this.updateDeliveryLogotip();
		}
		else
		{
			this.updateDeliveryInfo();
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.updateExtraService = function(extraService)
{
	var blockExtraService = BX('DELIVERY_INFO_'+this.index);
	blockExtraService.innerHTML = extraService;
};

BX.Sale.Admin.OrderShipment.prototype.updateShipmentStatus = function(field, status, params)
{
	var request = {
		'action' : 'updateShipmentStatus',
		'orderId' : BX('ID').value,
		'shipmentId' : BX('SHIPMENT_ID_'+this.index).value,
		'field' : field,
		'status' : status,
		'callback' : BX.proxy(function (result)
		{
			if (result.ERROR && result.ERROR.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
			}
			else
			{
				this[params.callback](params.args);

				if(result.RESULT)
					BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);
			}
		}, this)
	};
	BX.Sale.Admin.OrderAjaxer.sendRequest(request);
};

BX.Sale.Admin.OrderShipment.prototype.updateMap = function(map)
{
	var data = BX.processHTML(map);
	var div = BX('section_map_'+this.index);

	div.innerHTML = data['HTML'];

	for (var i in data['SCRIPT'])
		BX.evalGlobal(data['SCRIPT'][i]['JS']);

	BX.loadCSS(data['STYLE']);
};

BX.Sale.Admin.OrderShipment.prototype.updateDeliveryLogotip = function()
{
	var obj = BX('DELIVERY_'+this.index);
	var tbody = BX.findParent(obj, {tag : 'tbody'}, true);
	if (tbody.children.length > 1)
		obj = BX('PROFILE_'+this.index);

	var mainLogo = '';
	var shortLogo = '';

	var i = 0;
	if (this.srcList[BX(obj).value])
		i = BX(obj).value;

	mainLogo = this.srcList[i]['MAIN'];
	shortLogo = this.srcList[i]['SHORT'];


	var obMainLogo = BX('delivery_service_logo_' + this.index);
	if (!!obMainLogo)
		obMainLogo.style.background = 'url(' + mainLogo + ')';

	var obShortImg = BX('delivery_service_short_logo_' + this.index);
	if (!!obShortImg)
		obShortImg.style.background = 'url(' + shortLogo + ')';
};

BX.Sale.Admin.OrderShipment.prototype.initChangeProfile = function()
{
	var ob = BX('DELIVERY_'+this.index);

	BX.bind(ob, 'change', BX.proxy(function()
	{
		var blockExtraService = BX('DELIVERY_INFO_'+this.index);
		blockExtraService.innerHTML = '';

		var div = BX('section_map_'+this.index);
		div.innerHTML = '';

		var blockProfiles = BX('BLOCK_PROFILES_'+this.index);
		if (blockProfiles)
			BX.remove(blockProfiles);
		if (BX.Sale.Admin.OrderEditPage.formId == 'order_shipment_edit_info_form')
		{
			var discounts = BX('sale-order-shipment-discounts-row-' + this.index);
			if (discounts)
			{
				BX.hide(discounts.nextElementSibling);
				BX.hide(discounts);
			}

			var customPriceDelivery = BX('CUSTOM_PRICE_DELIVERY_' + this.index);
			if (customPriceDelivery.value != 'Y')
			{
				BX('BASE_PRICE_DELIVERY_' + this.index).value = parseFloat(BX('PRICE_DELIVERY_' + this.index).innerHTML);
				customPriceDelivery.value = 'Y';
			}
		}

		var deliveryId = BX(ob).value;
		if (deliveryId > 0)
			this.updateDeliveryInfo();
		else
			this.setDeliveryPrice(0);
	}, this));

	var profile = BX('PROFILE_'+this.index);
	if (profile)
	{
		BX.bind(profile, 'change', BX.proxy(function ()
		{
			var blockExtraService = BX('DELIVERY_INFO_' + this.index);
			blockExtraService.innerHTML = '';

			var div = BX('section_map_' + this.index);
			div.innerHTML = '';

			if (BX.Sale.Admin.OrderEditPage.formId == 'order_shipment_edit_info_form')
			{
				var discounts = BX('sale-order-shipment-discounts-row-' + this.index);
				if (discounts)
				{
					BX.hide(discounts.nextElementSibling);
					BX.hide(discounts);
				}

				var customPriceDelivery = BX('CUSTOM_PRICE_DELIVERY_' + this.index);
				if (customPriceDelivery.value != 'Y')
				{
					BX('BASE_PRICE_DELIVERY_' + this.index).value = parseFloat(BX('PRICE_DELIVERY_' + this.index).innerHTML);
					customPriceDelivery.value = 'Y';
				}
			}

			var deliveryId = BX(profile).value;
			if (deliveryId > 0)
				this.updateDeliveryInfo();
			else
				this.setDeliveryPrice(0);
		}, this));
	}

};

BX.Sale.Admin.OrderShipment.prototype.initFieldChangeDeducted = function()
{
	var obStatusDeducted = BX('STATUS_DEDUCTED_'+this.index);
	var postfix = ['SHORT_'+this.index, this.index];
	for (var i in postfix)
	{
		var btnDeducted = BX('BUTTON_DEDUCTED_' + postfix[i]);
		if (!btnDeducted)
			continue;

		var menu = [];
		if (obStatusDeducted.value == 'N')
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_DEDUCTED_YES'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'Y'};
						if (this.isAjax)
							this.updateShipmentStatus('DEDUCTED', 'Y', {callback: 'setDeducted', args: data});
						else
							this.setDeducted(data);

					}, this)
				}
			);
		}
		else
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_DEDUCTED_NO'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'N'};
						if (this.isAjax)
							this.updateShipmentStatus('DEDUCTED', 'N', {callback : 'setDeducted', args : data});
						else
							this.setDeducted(data);
					}, this)
				}
			);
		}

		var deducted = new BX.COpener(
			{
				'DIV': btnDeducted.parentNode,
				'MENU': menu
			}
		);
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDeducted = function(data)
{
	var fullStatus = (data.status == 'Y') ? 'YES' : 'NO';
	var obStatusDeducted = BX('STATUS_DEDUCTED_'+this.index);
	var postfix = ['SHORT_'+this.index, this.index];
	obStatusDeducted.value = data.status;

	for (var i in postfix)
	{
		var btnDeducted = BX('BUTTON_DEDUCTED_' + postfix[i]);
		if (!btnDeducted)
			continue;
		BX.html(btnDeducted, BX.message('SALE_ORDER_SHIPMENT_DEDUCTED_'+fullStatus));
		if (data.status == 'Y')
			BX.removeClass(btnDeducted, 'notdeducted');
		else
			BX.addClass(btnDeducted, 'notdeducted');
	}
	this.initFieldChangeDeducted();
};

BX.Sale.Admin.OrderShipment.prototype.initFieldChangeStatus = function()
{
	var postfix = ['SHORT_'+this.index, this.index];
	var obStatusShipment = BX('STATUS_SHIPMENT_' + this.index);
	for (var i in postfix)
	{
		var btnShipment = BX('BUTTON_SHIPMENT_' + postfix[i]);

		var menu = [];
		for (var j in this.shipment_statuses)
		{
			if (this.shipment_statuses[j].ID == obStatusShipment.value)
				continue;

			function addMenuStatus(status, event)
			{
				var data = {name : status.NAME, id: status.ID};
				var obj = {
					'TEXT': status.NAME,
					'ONCLICK': function ()
					{
						event.updateShipmentStatus('STATUS_ID', status.ID, {callback : 'setDeliveryStatus', args : data});
					}
				};
				menu.push(obj);
			}
			addMenuStatus(this.shipment_statuses[j], this);
		}

		if(btnShipment)
		{
			var shipment = new BX.COpener(
				{
					'DIV' : btnShipment.parentNode,
					'MENU' : menu
				}
			);
		}
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryStatus = function (data)
{

	var obStatusShipment = BX('STATUS_SHIPMENT_' + this.index);
	obStatusShipment.value = data.id;

	var postfix = ['SHORT_'+this.index, this.index];
	for (var k in postfix)
	{
		var btnShipment = BX('BUTTON_SHIPMENT_' + postfix[k]);
		BX.html(btnShipment, data.name);
	}

	this.initFieldChangeStatus();
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryBasePrice = function(basePrice)
{
	if(!BX('BASE_PRICE_DELIVERY_'+this.index))
		return;

	BX('BASE_PRICE_DELIVERY_'+this.index).value = basePrice;
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryPrice = function(price)
{
	if(!BX('PRICE_DELIVERY_'+this.index))
		return;

	BX('PRICE_DELIVERY_'+this.index).innerHTML = price;
};

BX.Sale.Admin.OrderShipment.prototype.setCustomPriceDelivery = function(deliveryPrice)
{
	var customPrice = BX('CUSTOM_PRICE_DELIVERY_'+this.index);
	if (customPrice.value != 'Y')
		return;

	var obDiscountSum = BX('PRICE_DELIVERY_'+this.index);
	var parent = BX.findParent(obDiscountSum, {tag : 'tbody'}, true);
	var child = BX.findChildByClassName(parent, 'row_set_new_delivery_price');
	if (child)
		BX.remove(child);

	BX('CUSTOM_PRICE_'+this.index).value = deliveryPrice;

	var tr = BX.create('tr',
	{
		children : [
			BX.create('td',
			{
				html : BX.message('SALE_ORDER_SHIPMENT_NEW_PRICE_DELIVERY')+': ',
				props : {
					className: 'adm-detail-content-cell-l'
				}
			}),
			BX.create('td',
			{
				children : [
					BX.create('span',
					{
						text : BX.Sale.Admin.OrderEditPage.currencyFormat(deliveryPrice)
					}),
					BX.create('span', {
						text : BX.message('SALE_ORDER_SHIPMENT_APPLY'),
						props : {
							onclick: BX.proxy(function ()
							{
								if (confirm(BX.message('SALE_ORDER_SHIPMENT_CONFIRM_SET_NEW_PRICE')))
								{
									BX('BASE_PRICE_DELIVERY_'+this.index).value = deliveryPrice;
									var child = BX.findChildByClassName(parent, 'row_set_new_delivery_price');
									BX.remove(child);

									if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
									{
										customPrice.value = 'N';
										BX.Sale.Admin.OrderAjaxer.sendRequest(
											BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
										);
									}
								}
							}, this),
							className : 'new_delivery_price_button'
						}
					})
				],
				props : {
					className: 'adm-detail-content-cell-r'
				}
			})
		],
		props : {
			className : 'row_set_new_delivery_price'
		}
	});
	parent.appendChild(tr);
};

BX.Sale.Admin.OrderShipment.prototype.updateDeliveryInfo = function()
{
	var formData = BX.Sale.Admin.OrderEditPage.getAllFormData();
	var request = {
		'action': 'changeDeliveryService',
		'formData': formData,
		'index' : this.index,
		'callback' : BX.proxy(function (result) {
			if (result.ERROR && result.ERROR.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
			}
			else
			{
				BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.SHIPMENT_DATA);
				this.updateDeliveryLogotip();
			}
		}, this)
	};
	if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		BX.Sale.Admin.OrderAjaxer.sendRequest(request, false, true);
	else
		BX.Sale.Admin.OrderAjaxer.sendRequest(request, false, false);
};

BX.Sale.Admin.OrderShipment.prototype.getDeliveryPrice = function()
{
	var formData = BX.Sale.Admin.OrderEditPage.getAllFormData();
	var request = {
	'action': 'getDefaultDeliveryPrice',
	'formData': formData,
	'callback' : BX.proxy(function (result) {
		if (result.ERROR && result.ERROR.length > 0)
			BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
		else
			BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);
		}, this)
	};

	var refreshForm = (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form');
	BX.Sale.Admin.OrderAjaxer.sendRequest(request, false, refreshForm);
};

BX.Sale.Admin.OrderShipment.prototype.initCustomEvent = function()
{
	BX.addCustomEvent('onDeliveryExtraServiceValueChange', BX.proxy(function (params)
	{
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
		}
		else
		{
			this.getDeliveryPrice();
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.initFieldChangeAllowDelivery = function()
{
	var obStatusAllowDelivery = BX('STATUS_ALLOW_DELIVERY_'+this.index);
	var postfix = ['SHORT_'+this.index, this.index];
	for (var i in postfix)
	{
		var btnAllowDelivery = BX('BUTTON_ALLOW_DELIVERY_' + postfix[i]);
		if (!btnAllowDelivery)
			continue;

		var menu = [];

		if (obStatusAllowDelivery.value == 'Y')
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_ALLOW_DELIVERY_NO'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'N'};
						if (this.isAjax)
							this.updateShipmentStatus('ALLOW_DELIVERY', 'N', {callback : 'setAllowDelivery', args : data});
						else
							this.setAllowDelivery(data);
					}, this)
				}
			);
		}
		else
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_ALLOW_DELIVERY_YES'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'Y'};
						if (this.isAjax)
							this.updateShipmentStatus('ALLOW_DELIVERY', 'Y', {callback : 'setAllowDelivery', args : data});
						else
							this.setAllowDelivery(data);

						this.initFieldChangeAllowDelivery();
					}, this)
				}
			);
		}

		var allowDelivery = new BX.COpener(
			{
				'DIV' : btnAllowDelivery.parentNode,
				'MENU': menu
			}
		);
	}
};

BX.Sale.Admin.OrderShipment.prototype.setAllowDelivery = function(data)
{
	var fullStatus = (data.status == 'Y') ? 'YES' : 'NO';
	var postfix = ['SHORT_'+this.index, this.index];

	var obStatusAllowDelivery = BX('STATUS_ALLOW_DELIVERY_'+this.index);
	obStatusAllowDelivery.value = data.status;

	for (var i in postfix)
	{
		var btnDelivery = BX('BUTTON_ALLOW_DELIVERY_' + postfix[i]);
		if (!btnDelivery)
			continue;
		BX.html(btnDelivery, BX.message('SALE_ORDER_SHIPMENT_ALLOW_DELIVERY_'+fullStatus));

		if (data.status == 'Y')
			BX.removeClass(btnDelivery, 'notdelivery');
		else
			BX.addClass(btnDelivery, 'notdelivery');
	}
	this.initFieldChangeAllowDelivery();
};

BX.Sale.Admin.OrderShipment.prototype.initFieldUpdateSum = function()
{
	var obSum = BX('BASE_PRICE_DELIVERY_'+this.index);
	var customPrice = BX('CUSTOM_PRICE_DELIVERY_'+this.index);
	BX.bind(obSum, 'change', BX.proxy(function()
	{
		customPrice.value = 'Y';
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
		}
		else
		{
			var discounts = BX('sale-order-shipment-discounts-row-' + this.index);
			if (discounts)
			{
				BX.hide(discounts.nextElementSibling);
				BX.hide(discounts);
			}

			BX('CUSTOM_PRICE_DELIVERY_' + this.index).value = 'Y';
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.initToggle = function()
{
	var fullView = BX('SHIPMENT_SECTION_'+this.index);
	var shortView = BX('SHIPMENT_SECTION_SHORT_'+this.index);

	var btnToggleView = BX('SHIPMENT_SECTION_'+this.index+'_TOGGLE');
	BX.bind(btnToggleView, 'click', BX.proxy(function() {
		btnToggleView.innerHTML = (shortView.style.display != 'none') ? BX.message('SALE_ORDER_SHIPMENT_BLOCK_SHIPMENT_TOGGLE') : BX.message('SALE_ORDER_SHIPMENT_BLOCK_SHIPMENT_TOGGLE_UP');
		BX.toggle(fullView);
		BX.toggle(shortView);
	}, this));
};


BX.Sale.Admin.OrderShipment.prototype.initDeleteShipment = function()
{
	var btnShipmentSectionDelete = BX('SHIPMENT_SECTION_'+this.index+'_DELETE');
	BX.bind(btnShipmentSectionDelete, 'click', BX.proxy(function() {
		if (confirm(BX.message('SALE_ORDER_SHIPMENT_CONFIRM_DELETE_SHIPMENT')))
			{
				var orderId = (BX('ID')) ? BX('ID').value : 0;
				var shipmentId = (BX('SHIPMENT_ID_'+this.index)) ? BX('SHIPMENT_ID_'+this.index).value : 0;

				if ((orderId > 0) && (shipmentId > 0))
				{
					var request = {
						'action': 'deleteShipment',
						'order_id': orderId,
						'shipment_id': shipmentId,
						'callback' : BX.proxy(function (result) {
							if (result.ERROR && result.ERROR.length > 0)
							{
								BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
							}
							else
							{
								BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);
								BX.cleanNode(BX('shipment_container_' + this.index));
							}
						}, this)
					};
					BX.Sale.Admin.OrderAjaxer.sendRequest(request);
				}
			}
	}, this));
};

BX.namespace("BX.Sale.Admin.GeneralShipment");

BX.Sale.Admin.GeneralShipment =
{
	getIds : function()
	{
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
		);
	},

	createNewShipment : function()
	{
		var orderId = BX('ID').value;
		window.location = '/bitrix/admin/sale_order_shipment_edit.php?lang='+BX.Sale.Admin.OrderEditPage.languageId+'&order_id='+orderId+'&backurl='+encodeURIComponent(window.location.pathname+window.location.search);
	},

	findProductByBarcode : function(_this)
	{
		BX.hide(_this.parentNode);
		BX.show(_this.parentNode.nextElementSibling);
	}
};

