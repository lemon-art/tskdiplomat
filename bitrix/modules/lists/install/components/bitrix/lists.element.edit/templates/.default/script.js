BX.namespace("BX.Lists");
BX.Lists.ListsElementEditClass = (function ()
{
	var ListsElementEditClass = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.ajaxUrl = '/bitrix/components/bitrix/lists.element.edit/ajax.php';
		this.urlTabBp = parameters.urlTabBp;
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.elementId = parameters.elementId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.sectionId = parameters.sectionId;
		this.jsClass = 'ListsElementEditClass_'+parameters.randomString;
		this.elementUrl = parameters.elementUrl;

		if(parameters.isConstantsTuned)
		{
			this.isConstantsTuned();
		}
	};

	ListsElementEditClass.prototype.completeWorkflow = function(workflowId, action)
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'completeWorkflow'),
			data: {
				workflowId: workflowId,
				iblockTypeId: this.iblockTypeId,
				elementId: this.elementId,
				iblockId: this.iblockId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId,
				action: action
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
						document.location.href = this.urlTabBp
					}, this), 1000);
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	ListsElementEditClass.prototype.isConstantsTuned = function()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'isConstantsTuned'),
			data: {
				iblockTypeId: this.iblockTypeId,
				iblockId: this.iblockId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					if(result.admin === false)
					{
						this.notifyAdmin();
					}
					else
					{
						this.fillConstants(result.templateData);
					}
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	ListsElementEditClass.prototype.fillConstants = function(listTemplateId)
	{
		if(!listTemplateId)
		{
			return;
		}

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'html',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'fillConstants'),
			data: {
				iblockId: this.iblockId,
				listTemplateId: listTemplateId
			},
			onsuccess: BX.delegate(function (result)
			{
				BX.adjust(BX('lists-fill-constants-content'), {
					html: result
				});
			}, this)
		});

		var modalWindow = BX.Lists.modalWindow({
			modalId: 'bx-lists-popup',
			withoutWindowManager: true,
			title: BX.message("CT_BLEE_BIZPROC_CONSTANTS_FILL_TITLE"),
			overlay: false,
			contentStyle: {
				width: '600px',
				paddingTop: '10px',
				paddingBottom: '10px'
			},
			content: [BX('lists-fill-constants-content')],
			events : {
				onPopupClose : function() {
					BX('lists-fill-constants').appendChild(BX('lists-fill-constants-content'));
					this.destroy();
				},
				onAfterPopupShow : function(popup) {
					var title = BX.findChild(popup.contentContainer, {className: 'bx-lists-popup-title'}, true);
					if (title)
					{
						title.style.cursor = "move";
						BX.bind(title, "mousedown", BX.proxy(popup._startDrag, popup));
					}
				}
			},
			buttons: [
				BX.create('a', {
					text : BX.message("CT_BLEE_BIZPROC_SAVE_BUTTON"),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function (e)
						{
							var form = BX.findChild(BX('lists-fill-constants-content'), {tag: 'FORM'}, true);
							if (form)
							{
								form.modalWindow = modalWindow;
								form.onsubmit(form, e);
							}
						})
					}
				}),
				BX.create('a', {
					text : BX.message("CT_BLEE_BIZPROC_CANCEL_BUTTON"),
					props: {
						className: 'webform-small-button webform-button-cancel'
					},
					events : {
						click : BX.delegate(function (e) {
							if(!!modalWindow) modalWindow.close();
						}, this)
					}
				})
			]
		});
	};

	ListsElementEditClass.prototype.notifyAdmin = function()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'getListAdmin'),
			data: {
				iblockId: this.iblockId,
				iblockTypeId: this.iblockTypeId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					var content = this.createHtmlNotifyAdmin(result.listAdmin);
					BX('lists-notify-admin-popup-content').appendChild(content);

					BX.Lists.modalWindow({
						modalId: 'bx-lists-popup',
						title: BX.message('CT_BLEE_BIZPROC_NOTIFY_TITLE'),
						overlay: false,
						contentStyle: {
							width: '600px',
							paddingTop: '10px',
							paddingBottom: '10px'
						},
						content: [BX('lists-notify-admin-popup-content')],
						events : {
							onPopupClose : function() {
								BX('lists-notify-admin-popup').appendChild(BX('lists-notify-admin-popup-content'));
								this.destroy();
							},
							onAfterPopupShow : function(popup) {
								var title = BX.findChild(popup.contentContainer, {className: 'bx-lists-popup-title'}, true);
								if (title)
								{
									title.style.cursor = "move";
									BX.bind(title, "mousedown", BX.proxy(popup._startDrag, popup));
								}
							}
						},
						buttons: [
							BX.create('a', {
								text : BX.message("CT_BLEE_BIZPROC_NOTIFY_ADMIN_BUTTON_CLOSE"),
								props: {
									className: 'webform-small-button webform-button-cancel'
								},
								events : {
									click : BX.delegate(function (e) {
										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							})
						]
					});
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	ListsElementEditClass.prototype.notify = function (userId)
	{
		if(!BX('lists-notify-button-'+userId))
		{
			return;
		}

		BX('lists-notify-button-'+userId).setAttribute('onclick','');

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'notifyAdmin'),
			data: {
				iblockId: this.iblockId,
				userId: userId,
				iblockTypeId: this.iblockTypeId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId,
				elementUrl: this.elementUrl
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.Lists.removeElement(BX('lists-notify-button-'+userId));
					BX('lists-notify-success-'+userId).innerHTML = result.message;
				}
				else
				{
					BX('lists-notify-button-'+userId).setAttribute(
						'onclick',
						'BX.Lists["'+this.jsClass+'"].notify("'+userId+'");'
					);
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	ListsElementEditClass.prototype.createHtmlNotifyAdmin = function(listAdmin)
	{
		if(!listAdmin)
		{
			return null;
		}

		var domElement;

		domElement = BX.create('div', {
			children: [
				BX.create('span', {
					props: {
						className: 'lists-notify-question'
					},
					children: [
						BX.create('span', {
							props: {
								innerHTML: '!',
								className: 'icon'
							}
						}),
						BX.create('span', {
							props: {
								innerHTML: BX.message('CT_BLEE_BIZPROC_SELECT_STAFF_SET_RESPONSIBLE')
							}
						})
					]
				}),
				BX.create('p', {
					html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_ONE')
				}),
				BX.create('p', {
					html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_TWO')
				}),
				BX.create('span', {
					props: {className: 'lists-notify-question-title'},
					html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE')
				})
			]
		});

		for(var k in listAdmin)
		{
			var img;
			if(listAdmin[k].img)
			{
				img = BX.create('img', {
					attrs: {
						src: listAdmin[k].img
					}
				});
			}

			domElement.appendChild(
				BX.create('div', {
					props: {className: 'lists-notify-question-item'},
					children: [
						BX.create('a', {
							props: {className: 'lists-notify-question-item-avatar'},
							attrs: {
								href: 'javascript:void(0)'
							},
							children: [
								BX.create('span', {
									props: {
										id: 'lists-notify-question-item-avatar-inner',
										className: 'lists-notify-question-item-avatar-inner'
									},
									children: [img]
								})
							]
						}),
						BX.create('span', {
							props: {className: 'lists-notify-question-item-info'},
							children: [
								BX.create('span', {
									html: listAdmin[k].name
								})
							]
						}),
						BX.create('span', {
							props: {
								id: 'lists-notify-success-'+listAdmin[k].id,
								className: 'lists-notify-success'
							}
						}),
						BX.create('a', {
							props: {
								id: 'lists-notify-button-'+listAdmin[k].id,
								className: 'webform-small-button lists-notify-small-button webform-small-button-blue'
							},
							attrs: {
								href: 'javascript:void(0)',
								onclick: 'BX.Lists["'+this.jsClass+'"].notify("'+listAdmin[k].id+'");'
							},
							html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE_BUTTON')
						})
					]
				})
			);
		}

		return domElement;
	};

	ListsElementEditClass.prototype.elementDelete = function(form_id, message)
	{
		var _form = document.getElementById(form_id);
		var _flag = document.getElementById('action');
		if(_form && _flag)
		{
			if(confirm(message))
			{
				_flag.value = 'delete';
				_form.submit();
			}
		}
	};

	ListsElementEditClass.prototype.createAdditionalHtmlEditor = function(tableId, fieldId, formId)
	{
		var tbl = document.getElementById(tableId);
		var cnt = tbl.rows.length;
		var oRow = tbl.insertRow(cnt);
		var oCell = oRow.insertCell(0);
		var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
		var p = 0, s, e, n;
		while (true)
		{
			s = sHTML.indexOf('[n', p);
			if (s < 0)
				break;
			e = sHTML.indexOf(']', s);
			if (e < 0)
				break;
			n = parseInt(sHTML.substr(s + 2, e - s));
			sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
			p = s + 1;
		}
		p = 0;
		while (true)
		{
			s = sHTML.indexOf('__n', p);
			if (s < 0)
				break;
			e = sHTML.indexOf('_', s + 2);
			if (e < 0)
				break;
			n = parseInt(sHTML.substr(s + 3, e - s));
			sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
			p = e + 1;
		}
		oCell.innerHTML = sHTML;

		var idEditor = 'id_'+fieldId+'__n'+cnt+'_';
		var fieldIdName = fieldId+'[n'+cnt+'][VALUE]';
		window.BXHtmlEditor.Show({
			'id':idEditor,
			'inputName':fieldIdName,
			'name' : fieldIdName,
			'content':'',
			'width':'100%',
			'height':'200',
			'allowPhp':false,
			'limitPhpAccess':false,
			'templates':[],
			'templateId':'',
			'templateParams':[],
			'componentFilter':'',
			'snippets':[],
			'placeholder':'Text here...',
			'actionUrl':'/bitrix/tools/html_editor_action.php',
			'cssIframePath':'/bitrix/js/fileman/html_editor/iframe-style.css?1412693817',
			'bodyClass':'',
			'bodyId':'',
			'spellcheck_path':'/bitrix/js/fileman/html_editor/html-spell.js?v=1412693817',
			'usePspell':'N',
			'useCustomSpell':'Y',
			'bbCode': false,
			'askBeforeUnloadPage':false,
			'settingsKey':'user_settings_1',
			'showComponents':true,
			'showSnippets':true,
			'view':'wysiwyg',
			'splitVertical':false,
			'splitRatio':'1',
			'taskbarShown':false,
			'taskbarWidth':'250',
			'lastSpecialchars':false,
			'cleanEmptySpans':true,
			'lazyLoad':false,
			'showTaskbars':false,
			'showNodeNavi':false,
			'controlsMap':[
				{'id':'Bold','compact':true,'sort':'80'},
				{'id':'Italic','compact':true,'sort':'90'},
				{'id':'Underline','compact':true,'sort':'100'},
				{'id':'Strikeout','compact':true,'sort':'110'},
				{'id':'RemoveFormat','compact':true,'sort':'120'},
				{'id':'Color','compact':true,'sort':'130'},
				{'id':'FontSelector','compact':false,'sort':'135'},
				{'id':'FontSize','compact':false,'sort':'140'},
				{'separator':true,'compact':false,'sort':'145'},
				{'id':'OrderedList','compact':true,'sort':'150'},
				{'id':'UnorderedList','compact':true,'sort':'160'},
				{'id':'AlignList','compact':false,'sort':'190'},
				{'separator':true,'compact':false,'sort':'200'},
				{'id':'InsertLink','compact':true,'sort':'210','wrap':'bx-htmleditor-'+formId},
				{'id':'InsertImage','compact':false,'sort':'220'},
				{'id':'InsertVideo','compact':true,'sort':'230','wrap':'bx-htmleditor-'+formId},
				{'id':'InsertTable','compact':false,'sort':'250'},
				{'id':'Code','compact':true,'sort':'260'},
				{'id':'Quote','compact':true,'sort':'270','wrap':'bx-htmleditor-'+formId},
				{'id':'Smile','compact':false,'sort':'280'},
				{'separator':true,'compact':false,'sort':'290'},
				{'id':'Fullscreen','compact':false,'sort':'310'},
				{'id':'BbCode','compact':true,'sort':'340'},
				{'id':'More','compact':true,'sort':'400'}],
			'autoResize':true,
			'autoResizeOffset':'40',
			'minBodyWidth':'350',
			'normalBodyWidth':'555'
		});
		var htmlEditor = BX.findChildrenByClassName(BX(tableId), 'bx-html-editor');
		for(var k in htmlEditor)
		{
			var editorId = htmlEditor[k].getAttribute('id');
			var frameArray = BX.findChildrenByClassName(BX(editorId), 'bx-editor-iframe');
			if(frameArray.length > 1)
			{
				for(var i = 0; i < frameArray.length - 1; i++)
				{
					frameArray[i].parentNode.removeChild(frameArray[i]);
				}
			}

		}
	};

	return ListsElementEditClass;
})();
