
var sbbl = {

	toggleExpandCollapseCart: function ()
	{
		if (BX.hasClass(sbbl.elemBlock, "close"))
		{
			BX.removeClass(sbbl.elemBlock, "close");
			sbbl.elemStatus.innerText = sbbl.strCollapse;
		}
		else
		{
			BX.addClass(sbbl.elemBlock, "close");
			sbbl.elemStatus.innerText = sbbl.strExpand;
		}
	},

	refreshCart: function (data)
	{
		if (! data)
			data = {};

		data.sessid = BX.bitrix_sessid();
		data.siteId = sbbl.siteId;
		data.arParams = sbbl.arParams;

		BX.ajax({
			url: sbbl.ajaxPath,
			method: 'POST',
			dataType: 'html',
			data: data,
			onsuccess: function(result)
			{
				sbbl.elemBlock.innerHTML = result;
			}
		});
	},

	removeItemFromCart: function (id)
	{
		sbbl.refreshCart ({removeItemFromCart: id});
	}
};


