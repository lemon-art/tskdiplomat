BX.namespace("BX.Lists");
BX.Lists.ListsEditClass = (function ()
{
	var ListsEditClass = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.ajaxUrl = '/bitrix/components/bitrix/lists.list.edit/ajax.php';
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.jsClass = 'ListsEditClass_'+parameters.randomString;
		this.listsUrl = parameters.listsUrl || '';
		this.copyIblockElement = BX('lists-edit-copy-iblock');
	};

	ListsEditClass.prototype.copyIblock = function()
	{
		var cloneButton = this.copyIblockElement.cloneNode(true),
			parentButton = this.copyIblockElement.parentNode;

		this.copyIblockElement.setAttribute('href', '');
		this.copyIblockElement.innerHTML = '';
		this.copyIblockElement.appendChild(
			BX.create('span', {
			props: {
				className: 'bx-context-button-icon btn-copy'
				}
			})
		);
		this.copyIblockElement.appendChild(
			BX.create('span', {
				props: {
					className: 'bx-context-button-text'
				},
				text: BX.message('CT_BLLE_TOOLBAR_LIST_COPY_BUTTON_TITLE')
			})
		);

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'copyIblock'),
			data: {
				iblockTypeId: this.iblockTypeId,
				iblockId: this.iblockId,
				socnetGroupId: this.socnetGroupId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.Lists.showModalWithStatusAction({
						status: 'success',
						message: result.message
					});
					setTimeout(BX.delegate(function() {
						document.location.href = this.listsUrl
					}, this), 1000);
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					});

					BX.Lists.removeElement(this.copyIblockElement);
					parentButton.appendChild(cloneButton);
				}
			}, this)
		});
	};

	ListsEditClass.prototype.deleteIblock = function(form_id, message)
	{
		var _form = BX(form_id);
		var _flag = BX('action');
		if(_form && _flag)
		{
			if(confirm(message))
			{
				_flag.value = 'delete';
				_form.submit();
			}
		}
	};

	ListsEditClass.prototype.migrateList = function(formId, message)
	{
		var _form = BX(formId);
		var _flag = BX('action');
		if(_form && _flag)
		{
			if(confirm(message))
			{
				_flag.value = 'migrate';
				_form.submit();
			}
		}
	};

	return ListsEditClass;
})();
