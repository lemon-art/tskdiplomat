;(function (window)
{
	if (window.BX.MessengerCommon) return;

	var BX = window.BX;

	BX.MessengerCommon = function ()
	{
		this.BXIM = {};
	};



	/* Section: Context */
	BX.MessengerCommon.prototype.setBxIm = function(dom)
	{
		this.BXIM = dom;
	}

	BX.MessengerCommon.prototype.isMobile = function()
	{
		return this.BXIM.mobileVersion;
	}

	BX.MessengerCommon.prototype.muteMessageChat = function(dialogId, mute, sendAjax)
	{
		var chatId = 0;
		var userIsChat = false;
		if (dialogId.toString().substr(0,4) == 'chat')
		{
			chatId = dialogId.toString().substr(4);
			if (!this.BXIM.messenger.chat[chatId])
				return false;
		}
		else
		{
			chatId = this.BXIM.messenger.userChat[dialogId];
			if (!chatId)
				return false;
		}

		sendAjax = sendAjax != false;

		if (!this.BXIM.messenger.userChatBlockStatus[chatId])
			this.BXIM.messenger.userChatBlockStatus[chatId] = {}

		if (mute)
		{
			this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] = mute;
		}
		else
		{
			if (this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] == 'Y')
				this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] = 'N';
			else
				this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] = 'Y';
		}
		
		this.BXIM.messenger.dialogStatusRedraw();
		this.BXIM.messenger.updateMessageCount();

		if (sendAjax)
		{
			BX.localStorage.set('mcl2', {dialogId: dialogId, mute: this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId]}, 5);

			BX.ajax({
				url: this.BXIM.pathToAjax+'?CHAT_MUTE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_CHAT_MUTE' : 'Y', 'CHAT_ID': chatId, 'MUTE': this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId], 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}
	};

	BX.MessengerCommon.prototype.MobileActionEqual = function(action)
	{
		if (!this.isMobile())
			return true;

		for (var i = 0; i < arguments.length; i++)
		{
			if (arguments[i] == this.BXIM.mobileAction)
				return true;
		}

		return false;
	}

	BX.MessengerCommon.prototype.MobileActionNotEqual = function(action)
	{
		if (!this.isMobile())
			return false;

		for (var i = 0; i < arguments.length; i++)
		{
			if (arguments[i] == this.BXIM.mobileAction)
				return false;
		}

		return true;
	}

	BX.MessengerCommon.prototype.isScrollMax = function(element, infelicity)
	{
		if (!element) return true;
		infelicity = typeof(infelicity) == 'number'? infelicity: 0;

		if (this.isMobile())
		{
			var height = window.orientation == 0? screen.height-125: screen.width-113;
			return (document.body.scrollHeight - height - height/2 <= element.scrollTop);
		}
		else
		{
			return (element.scrollHeight - element.offsetHeight - infelicity <= element.scrollTop);
		}

	};

	BX.MessengerCommon.prototype.isScrollMin = function(element)
	{
		if (!element) return false;
		return (0 == element.scrollTop);
	};

	BX.MessengerCommon.prototype.enableScroll = function(element, max, scroll)
	{
		if (!element)
			return false;

		if (this.BXIM.messenger.isBodyScroll)
			return false;

		scroll = scroll !== false;
		max = 400;//parseInt(max);

		return (scroll && this.isScrollMax(element, max));
	};

	BX.MessengerCommon.prototype.preventDefault = function(event)
	{
		event = event||window.event;

		if (event.stopPropagation)
			event.stopPropagation();
		else
			event.cancelBubble = true;

		if (typeof(BXIM) != 'undefined' && BXIM.messenger && BXIM.messenger.closeMenuPopup)
			BXIM.messenger.closeMenuPopup();

		if (typeof(BX) != 'undefined' && BX.calendar && BX.calendar.get().popup)
			BX.calendar.get().popup.close();
	};

	BX.MessengerCommon.prototype.countObject = function(obj)
	{
		var result = 0;

		for (var i in obj)
		{
			if (obj.hasOwnProperty(i))
			{
				result++;
			}
		}

		return result;
	};

	/* Section: Element Coords */
	BX.MessengerCommon.prototype.isElementCoordsBelow = function (element, domBox, offset, returnArray)
	{
		if (this.isMobile())
		{
			return true;
		}

		if (!domBox || typeof(domBox.getElementsByClassName) == 'undefined')
		{
			return false;
		}

		offset = offset? offset: 0;

		var coords = this.getElementCoords(element, domBox);
		coords.bottom = coords.top+element.offsetHeight;

		var topVisible = (coords.top >= offset);
		var bottomVisible = (coords.bottom > offset);

		if (returnArray)
		{
			return {'top': topVisible, 'bottom': bottomVisible, 'coords': coords};
		}
		else
		{
			return (topVisible || bottomVisible);
		}
	}

	BX.MessengerCommon.prototype.isElementVisibleOnScreen = function (element, domBox, returnArray)
	{
		if (this.isMobile())
		{
			return BitrixMobile.isElementVisibleOnScreen(element);
		}

		if (!domBox || typeof(domBox.getElementsByClassName) == 'undefined')
		{
			return false;
		}

		var coords = this.getElementCoords(element, domBox);
		coords.bottom = coords.top+element.offsetHeight;

		var windowTop = domBox.scrollTop;
		var windowBottom = windowTop + domBox.clientHeight;

		var topVisible = (coords.top >= 0 && coords.top < windowBottom);
		var bottomVisible = (coords.bottom > 0 && coords.bottom < domBox.clientHeight);

		if (returnArray)
		{
			return {'top': topVisible, 'bottom': bottomVisible};
		}
		else
		{
			return (topVisible || bottomVisible);
		}
	}

	BX.MessengerCommon.prototype.getElementCoords = function (element, domBox)
	{
		if (this.isMobile())
		{
			return BitrixMobile.getElementCoords(element);
		}

		if (!domBox || typeof(domBox.getElementsByClassName) == 'undefined')
		{
			return false;
		}

		var box = element.getBoundingClientRect();
		var inBox = domBox.getBoundingClientRect();

		return {
			originTop: box.top,
			originLeft: box.left,
			top: box.top - inBox.top,
			left: box.left - inBox.left
		};
	}



	/* Section: Date */
	BX.MessengerCommon.prototype.getDateFormatType = function(type)
	{
		type = type? type.toString().toUpperCase(): 'DEFAULT';

		var format = [];
		if (type == 'MESSAGE_TITLE')
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.date.convertBitrixFormat(BX.message("IM_M_MESSAGE_TITLE_FORMAT_DATE"))]
			];
		}
		else if (type == 'MESSAGE')
		{
			format = [
				["", BX.message("IM_M_MESSAGE_FORMAT_TIME")]
			];
		}
		else if (type == 'RECENT_TITLE')
		{
			format = [
				["tommorow", "today"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.date.convertBitrixFormat(BX.message("IM_CL_RESENT_FORMAT_DATE"))]
			]
		}
		else
		{
			format = [
				["tommorow", "tommorow, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["today", "today, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["yesterday", "yesterday, "+BX.message("IM_M_MESSAGE_FORMAT_TIME")],
				["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"))]
			];
		}
		return format;
	}

	BX.MessengerCommon.prototype.formatDate = function(timestamp, format)
	{
		if (typeof(format) == 'undefined')
		{
			format = this.getDateFormatType('DEFAULT')
		}
		return BX.date.format(format, parseInt(timestamp)+parseInt(BX.message("SERVER_TZ_OFFSET")), this.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET")), true);
	};

	BX.MessengerCommon.prototype.getNowDate = function(today)
	{
		var currentDate = (new Date);
		if (today == true)
			currentDate = (new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate(), 0, 0, 0));

		return Math.round((+currentDate/1000))+parseInt(BX.message("USER_TZ_OFFSET"));
	};

	BX.MessengerCommon.prototype.getDateDiff = function (timestamp)
	{
		var userTzOffset = BX.message("USER_TZ_OFFSET");
		if (userTzOffset === "")
			return 0;

		var localTimestamp = this.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET"));
		var incomingTimestamp = parseInt(timestamp)+parseInt(BX.message("SERVER_TZ_OFFSET"));

		return localTimestamp - incomingTimestamp;
	};



	/* Section: Images */
	BX.MessengerCommon.prototype.isBlankAvatar = function(url)
	{
		return url == '' || url.indexOf(this.BXIM.pathToBlankImage) >= 0;
	};

	BX.MessengerCommon.prototype.getDefaultAvatar = function(type)
	{
		return "/bitrix/js/im/images/default-avatar-"+type+".png";
	};

	BX.MessengerCommon.prototype.hideErrorImage = function(element)
	{
		var link = element.src;
		if (element.parentNode && element.parentNode.parentNode)
		{
			element.parentNode.parentNode.className = ''
			element.parentNode.parentNode.innerHTML = '<a href="'+link+'" target="_blank">'+link+'</a>';
		}
	}



	/* Section: Text */
	BX.MessengerCommon.prototype.prepareText = function(text, prepare, quote, image, highlightText)
	{
		var textElement = text;
		prepare = prepare == true;
		quote = quote == true;
		image = image == true;
		highlightText = highlightText? highlightText: false;

		textElement = BX.util.trim(textElement);

		if (textElement.indexOf('/me') == 0)
		{
			textElement = textElement.substr(4);
			textElement = '<i>'+textElement+'</i>';
		}
		else if (textElement.indexOf('/loud') == 0)
		{
			textElement = textElement.substr(6);
			textElement = '<b>'+textElement+'</b>';
		}

		var quoteSign = "&gt;&gt;";
		if(quote && textElement.indexOf(quoteSign) >= 0)
		{
			var textPrepareFlag = false;
			var textPrepare = textElement.split("<br />");
			for(var i = 0; i < textPrepare.length; i++)
			{
				if(textPrepare[i].substring(0,quoteSign.length) == quoteSign)
				{
					textPrepare[i] = textPrepare[i].replace(quoteSign, "<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">");
					while(++i < textPrepare.length && textPrepare[i].substring(0,quoteSign.length) == quoteSign)
					{
						textPrepare[i] = textPrepare[i].replace(quoteSign, '');
					}
					textPrepare[i-1] += '</div></div>';
					textPrepareFlag = true;
				}
			}
			textElement = textPrepare.join("<br />");
		}
		if (prepare)
		{
			textElement = BX.util.htmlspecialchars(textElement);
		}


		textElement = textElement.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, function(whole, userId, text)
		{
			var html = '';

			userId = parseInt(userId);
			if (quote && text && userId > 0 && typeof(BXIM) != 'undefined')
				html = '<span class="bx-messenger-ajax '+(userId == BXIM.userId? 'bx-messenger-ajax-self': '')+'" data-entity="user" data-userId="'+userId+'">'+text+'</span>';
			else
				html = text;

			return html;
		});

		textElement = textElement.replace(/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function(whole, chatId, text)
		{
			var html = '';

			chatId = parseInt(chatId);
			if (quote && text && chatId > 0 && typeof(BXIM) != 'undefined')
				html = '<span class="bx-messenger-ajax" data-entity="chat" data-chatId="'+chatId+'">'+text+'</span>';
			else
				html = text;

			return html;
		});

		textElement = textElement.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, function(whole, historyId, text)
		{
			var html = '';

			historyId = parseInt(historyId);
			if (quote && text && historyId > 0)
				html = '<span class="bx-messenger-ajax" data-entity="phoneCallHistory" data-historyId="'+historyId+'">'+text+'</span>';
			else
				html = text;

			return html;
		});

		if (quote)
		{
			textElement = textElement.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function(whole, p1, p2, p3, p4, offset){
				return (offset > 0? '<br>':'')+"<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\"><div class=\"bx-messenger-content-quote-name\">"+p1+" <span class=\"bx-messenger-content-quote-time\">"+p2+"</span></div>"+p3+"</div></div><br />";
			});
			textElement = textElement.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function(whole, p1, p2, p3, offset){
				return (offset > 0? '<br>':'')+"<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">"+p1+"</div></div><br />";
			});
		}
		if (prepare)
		{
			textElement = textElement.replace(/\n/gi, '<br />');
		}
		textElement = textElement.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

		if (image)
		{
			textElement = textElement.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/ig, function(whole, aInner, text, offset)
			{
				if(!text.match(/\.(jpg|jpeg|png|gif)$/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0)
				{
					return whole;
				}
				else if (BX.MessengerCommon.isMobile())
				{
					return (offset > 0? '<br />':'')+'<span class="bx-messenger-file-image"><span class="bx-messenger-file-image-src"><img src="'+text+'" class="bx-messenger-file-image-text" onclick="BXIM.messenger.openPhotoGallery(this.src);" onerror="BX.MessengerCommon.hideErrorImage(this)"></span></span><br>';
				}
				else
				{
					return (offset > 0? '<br />':'')+'<span class="bx-messenger-file-image"><a' +aInner+ ' target="_blank" class="bx-messenger-file-image-src"><img src="'+text+'" class="bx-messenger-file-image-text" onerror="BX.MessengerCommon.hideErrorImage(this)"></a></span><br>';
				}
			});
		}
		if (highlightText)
		{
			textElement = textElement.replace(new RegExp("("+highlightText.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'), '<span class="bx-messenger-highlight">$1</span>');
		}

		if (this.BXIM.settings.enableBigSmile)
		{
			textElement = textElement.replace(
				/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?width=")(\d+)("[^>]+?height=")(\d+)("[^>]+?class="bx-smile"\s*\/?>\s*)$/,
				function doubleSmileSize(match, start, width, middle, height, end) {
					return start + (parseInt(width, 10) * 2) + middle + (parseInt(height, 10) * 2) + end;
				}
			);
		}

		if (textElement.substr(-6) == '<br />')
		{
			textElement = textElement.substr(0, textElement.length-6);
		}
		textElement = textElement.replace(/<br><br \/>/ig, '<br />');
		textElement = textElement.replace(/<br \/><br>/ig, '<br />');

		return textElement;
	};

	BX.MessengerCommon.prototype.prepareTextBack = function(text, trueQuote)
	{
		var textElement = text;

		trueQuote = trueQuote === true;

		textElement = BX.util.htmlspecialcharsback(textElement);
		textElement = textElement.replace(/<(\/*)([buis]+)>/ig, '[$1$2]');
		textElement = textElement.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
		textElement = textElement.replace(/<a.*?href="([^"]*)".*?>.*?<\/a>/ig, '$1');
		if (!trueQuote)
		{
			textElement = textElement.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, "["+BX.message("IM_M_QUOTE_BLOCK")+"]");
		}
		textElement = textElement.split('&nbsp;&nbsp;&nbsp;&nbsp;').join("\t");
		textElement = textElement.split('<br />').join("\n");//.replace(/<\/?[^>]+>/gi, '');

		return textElement;
	};

	BX.MessengerCommon.prototype.addMentionList = function(tabId, dialogName, dialogId)
	{
		if (!tabId || !dialogName)
			return false;

		if (!this.BXIM.messenger.mentionList[tabId])
			this.BXIM.messenger.mentionList[tabId] = {};

		this.BXIM.messenger.mentionList[tabId][dialogName] = dialogId;
	}

	BX.MessengerCommon.prototype.prepareMention = function(tabId, text)
	{
		if (!this.BXIM.messenger.mentionList[tabId])
			return text;

		for (var dialogName in this.BXIM.messenger.mentionList[tabId])
		{
			var dialogId = this.BXIM.messenger.mentionList[tabId][dialogName];
			if (dialogId.toString().substr(0,4) == 'chat')
			{
				text = text.split(dialogName).join('[CHAT='+dialogId.toString().substr(4)+']'+dialogName+'[/CHAT]');
			}
			else
			{
				text = text.split(dialogName).join('[USER='+dialogId+']'+dialogName+'[/USER]');
			}
		}

		this.clearMentionList(tabId);

		return text;
	}

	BX.MessengerCommon.prototype.clearMentionList = function(tabId)
	{
		delete this.BXIM.messenger.mentionList[tabId];
	}



	/* Section: User state */
	BX.MessengerCommon.prototype.getRecipientByChatId = function(chatId)
	{
		var recipientId = 0;
		if (this.BXIM.messenger.chat[chatId])
		{
			recipientId = 'chat'+chatId;
		}
		else
		{
			for (var userId in this.BXIM.messenger.userChat)
			{
				if (this.BXIM.messenger.userChat[userId] == chatId)
				{
					recipientId = userId;
					break;
				}
			}
		}
		return recipientId;
	}

	BX.MessengerCommon.prototype.getUserIdByChatId = function(chatId)
	{
		var result = 0;
		for (var userId in this.BXIM.messenger.userChat)
		{
			if (this.BXIM.messenger.userChat[userId] == chatId)
			{
				result = userId;
				break;
			}
		}
		return result;
	}

	BX.MessengerCommon.prototype.getUserParam = function(userId, reset)
	{
		userId = typeof(userId) == 'undefined'? this.BXIM.userId: userId;
		reset = typeof(reset) == 'boolean'? reset: false;

		if (userId.toString().substr(0,4) == 'chat')
		{
			var chatId = userId.toString().substr(4);
			if (reset || !(this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].id))
			{
				this.BXIM.messenger.chat[chatId] = {'id': chatId, 'name': BX.message('IM_M_LOAD_USER'), 'owner': 0, workPosition: '', 'avatar': this.BXIM.pathToBlankImage, 'type': 'chat', color: '#556574', 'fake': true};
				if (reset)
				{
					this.BXIM.messenger.chat[chatId].fake = false;
				}
			}
			return this.BXIM.messenger.chat[chatId];
		}
		else
		{
			if (reset || !(this.BXIM.messenger.users[userId] && this.BXIM.messenger.users[userId].id))
			{
				var profilePath = parseInt(userId)? this.BXIM.path.profileTemplate.replace('#user_id#', userId): '';
				this.BXIM.messenger.users[userId] = {'id': userId, 'avatar': this.BXIM.pathToBlankImage, 'name': BX.message('IM_M_LOAD_USER'), 'profile': profilePath, 'status': 'guest', workPosition: '', 'extranet': false, 'network': false, color: '#556574', 'fake': true};
				this.BXIM.messenger.hrphoto[userId] = '/bitrix/js/im/images/hidef-avatar-v3.png';
				if (reset)
				{
					this.BXIM.messenger.users[userId].fake = false;
				}
			}
			return this.BXIM.messenger.users[userId];
		}
	}

	BX.MessengerCommon.prototype.userInChat = function(chatId, userId)
	{
		if (!this.BXIM.messenger.userInChat[chatId])
			return false;

		if (typeof(userId) == 'undefined')
		{
			userId = this.BXIM.userId;
		}

		var userFound = false;
		if (typeof(this.BXIM.messenger.userInChat[chatId].indexOf) != 'undefined')
		{
			if (this.BXIM.messenger.userInChat[chatId].indexOf(userId.toString()) != -1 || this.BXIM.messenger.userInChat[chatId].indexOf(parseInt(userId)) != -1)
			{
				userFound = true;
			}
		}
		else // TODO delete if not support IE 8
		{
			for (var i = 0; i < this.BXIM.messenger.userInChat[chatId].length; i++)
			{
				if (parseInt(this.BXIM.messenger.userInChat[chatId][i]) == parseInt(userId))
				{
					userFound = true;
					break;
				}
			}
		}

		return userFound;
	}

	BX.MessengerCommon.prototype.getUserStatus = function(userId, getText)
	{
		if (!userId || userId.toString().substr(0, 7) != 'network')
		{
			userId = parseInt(userId);
			userId = isNaN(userId)? this.BXIM.userId: userId;
		}

		getText = getText === true;

		var status = '';
		var statusText = '';
		if (typeof(this.BXIM.messenger.users[userId]) == 'undefined')
		{
			status = 'guest';
			statusText = BX.message('IM_STATUS_GUEST');
		}
		else if (this.BXIM.messenger.users[userId].status == 'offline')
		{
			status = 'offline';
			statusText = BX.message('IM_STATUS_OFFLINE');
		}
		else if (this.BXIM.messenger.users[userId].status == 'guest')
		{
			status = 'guest';
			statusText = BX.message('IM_STATUS_GUEST');
		}
		else if (this.BXIM.userId == userId)
		{
			status = this.BXIM.messenger.users[userId].status? this.BXIM.messenger.users[userId].status.toString(): '';
			statusText = status? BX.message('IM_STATUS_'+status.toUpperCase()): '';
		}
		else if (this.getUserMobileStatus(userId))
		{
			status = 'mobile';
			statusText = BX.message('IM_STATUS_MOBILE');
		}
		else if (this.BXIM.messenger.users[userId].idle > 0)
		{
			status = 'idle';
			statusText = BX.message('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getUserIdle(userId));
		}
		else if (this.BXIM.messenger.users[userId].birthday && (this.BXIM.messenger.users[userId].status == 'online' || this.BXIM.messenger.users[userId].status == 'offline'))
		{
			status = 'birthday';
			if (this.BXIM.messenger.users[userId].status == 'offline')
			{
				statusText = BX.message('IM_STATUS_OFFLINE');
			}
			else
			{
				statusText = BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT');
			}
		}
		else
		{
			status = this.BXIM.messenger.users[userId].status? this.BXIM.messenger.users[userId].status.toString(): '';
			statusText = BX.message('IM_STATUS_'+status.toUpperCase());
		}

		return getText? statusText: status;
	}

	BX.MessengerCommon.prototype.getUserIdle = function(userId)
	{
		userId = parseInt(userId);
		userId = isNaN(userId)? this.BXIM.userId: userId;

		var message = "";
		if ( this.BXIM.messenger.users[userId].idle > 0)
		{
			var idle = parseInt(this.BXIM.messenger.users[userId].idle);
			message = this.formatDate(this.BXIM.messenger.users[userId].idle, this.getNowDate()-idle >= 3600? 'Hdiff': 'idiff')
		}

		return message;
	}

	BX.MessengerCommon.prototype.getUserMobileStatus = function(userId)
	{
		userId = parseInt(userId);
		userId = isNaN(userId)? this.BXIM.userId: userId;

		var status = false;
		if ( this.BXIM.messenger.users[userId].mobileLastDate > 0)
		{
			var mobileLastDate = parseInt(this.BXIM.messenger.users[userId].mobileLastDate);
			if ((this.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET")))-(parseInt(mobileLastDate)+parseInt(BX.message("SERVER_TZ_OFFSET"))) < 240)
			{
				status = true;
			}
		}

		return status;
	}

	BX.MessengerCommon.prototype.getUserPosition = function(userId)
	{
		var pos = '';

		if (!this.BXIM.messenger.users[userId])
			return '';

		if (this.BXIM.messenger.users[userId].workPosition)
		{
			pos = this.BXIM.messenger.users[userId].workPosition;
		}
		else if (this.BXIM.messenger.users[userId].extranet)
		{
			pos = BX.message('IM_CL_USER_EXTRANET');
		}
		else if (this.BXIM.bitrixIntranet)
		{
			pos = BX.message('IM_CL_USER_B24');
		}
		else
		{
			pos = BX.message('IM_CL_USER');
		}
		return pos;
	}

	BX.MessengerCommon.prototype.setColor = function(color, chatId)
	{
		if (!this.BXIM.init && this.BXIM.desktop.ready())
		{
			BX.desktop.onCustomEvent("bxSaveColor", [{color: color, chatId: chatId}]);
			return false;
		}

		if (typeof(color) != "string")
		{
			return false;
		}
		else
		{
			color = color.toUpperCase();
		}
		if (typeof(chatId) != 'undefined')
		{
			if (typeof(this.BXIM.messenger.chat[chatId]) == 'undefined')
			{
				return false;
			}
		}
		else
		{
			chatId = 0;
			if (this.BXIM.userColor == color)
			{
				return false;
			}
		}

		BX.ajax({
			url: this.BXIM.pathToAjax+'?SET_COLOR&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_SET_COLOR' : 'Y', 'COLOR' : color, 'CHAT_ID': chatId, 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				if (data.ERROR == "")
				{
					if (parseInt(data.CHAT_ID) == 0)
					{
						this.BXIM.userColor = data.COLOR;
						if (this.BXIM.desktop.run())
						{
							setTimeout(function(){
								BX.desktop.setUserInfo(BX.MessengerCommon.getUserParam());
							}, 500);
						}
					}
				}
			}, this)
		});
	};

	BX.MessengerCommon.prototype.renameChat = function(chatId, title)
	{
		chatId = parseInt(chatId);
		if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online' || !title || chatId <= 0)
			return false;

		title = BX.util.trim(title);
		if (title.length <= 0 || this.BXIM.messenger.chat[chatId].name == BX.util.htmlspecialchars(title))
			return false;

		this.BXIM.messenger.chat[chatId].name = BX.util.htmlspecialchars(title);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?CHAT_RENAME&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'IM_CHAT_RENAME' : 'Y', 'CHAT_ID' : chatId, 'CHAT_TITLE': title, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				if (!this.BXIM.ppServerStatus)
					BX.PULL.updateState(true);
			}, this)
		});

		return true;
	};



	/* Section: CL & RL */
	BX.MessengerCommon.prototype.userListRedraw = function(params)
	{
		if (this.isMobile())
		{
			if (!this.MobileActionEqual('RECENT'))
			{
				return false;
			}
		}
		else
		{
			if (this.BXIM.messenger.popupMessenger == null)
				return false;
		}

		if (this.BXIM.messenger.recentList && this.BXIM.messenger.contactListSearchText != null && this.BXIM.messenger.contactListSearchText.length == 0)
		{
			this.recentListRedraw(params);
		}
		else if (this.BXIM.messenger.chatList)
		{
			this.chatListRedraw(params);
		}
		else
		{
			this.contactListRedraw(params);
			if (this.BXIM.messenger.recentListExternal)
			{
				this.recentListRedraw(params);
			}
		}
	};



	/* Section: Concact List */
	BX.MessengerCommon.prototype.contactListRedraw = function(params)
	{
		params = params || {};

		if (!this.isMobile())
		{
			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.contactList = true;
			this.BXIM.messenger.recentList = false;
			this.BXIM.messenger.contactListShowed = {};

			if (this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
			{
				this.BXIM.messenger.popupPopupMenu.close();
			}
		}

		if (this.BXIM.messenger.contactListSearchText.length > 0)
		{
			this.contactListPrepareSearch('contactList', this.BXIM.messenger.popupContactListElementsWrap, this.BXIM.messenger.contactListSearchText, params.FORCE? {}: {params: false, timeout: this.isMobile()? 500: 100})
		}
		else
		{
			if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
				clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

			this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
			BX.adjust(this.BXIM.messenger.popupContactListElementsWrap, {children: this.contactListPrepare()});

			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}

		params.SEND = params.SEND == true;
		if (!this.isMobile() && params.SEND)
		{
			BX.localStorage.set('mrd', {viewGroup: this.BXIM.settings.viewGroup, viewOffline: this.BXIM.settings.viewOffline}, 5);
		}
	};

	BX.MessengerCommon.prototype.contactListPrepareSearch = function(name, bind, search, params)
	{
		if (!bind)
			return false;

		var searchParams = {
			'groupOpen': true,
			'viewOffline': true,
			'viewGroup': true,
			'viewChat': true,
			'viewOpenChat': true,
			'viewOfflineWithPhones': false,
			'extra': false,
			'searchText': search,
			'callback': {
				'empty': function(){}
			}
		};
		if (params != false)
		{
			for (var i in params)
			{
				if (i == 'timeout' || i == 'params')
					continue;

				searchParams[i] = params[i];
			}
		}

		var timeout = params.timeout? params.timeout: 0;

		if (timeout > 0)
		{
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout[name]);
			this.BXIM.messenger.redrawContactListTimeout[name] = setTimeout(BX.delegate(function(){
				bind.innerHTML = '';
				BX.adjust(bind, {children: this.contactListPrepare(searchParams)});
				if (this.isMobile())
				{
					BitrixMobile.LazyLoad.showImages();
				}
			}, this), timeout);
		}
		else
		{
			bind.innerHTML = '';
			BX.adjust(bind, {children: this.contactListPrepare(searchParams)});
			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}
	}

	BX.MessengerCommon.prototype.contactListPrepare = function(params)
	{
		params = typeof(params) == 'object'? params: {};
		var items = [];
		var itemSearch = null;
		var groupsTmp = {};
		var groups = {};
		var unreadUsers = [];
		var userInGroup = {};

		var searchText = typeof(params.searchText) != 'undefined'? params.searchText: this.BXIM.messenger.contactListSearchText;
		var activeSearch = !(searchText != null && searchText.length == 0);
		var searchWaitBackend = this.BXIM.messenger.realSearch && !this.BXIM.messenger.realSearchFound;
		var extraEnable =  typeof(params.extra) != 'undefined'? params.extra: true;
		var groupOpen =  typeof(params.groupOpen) != 'undefined'? params.groupOpen: 'auto';
		var viewGroup =  typeof(params.viewGroup) != 'undefined'? params.viewGroup: activeSearch || !this.BXIM.settings? false: this.BXIM.settings.viewGroup;
		var viewOffline =  typeof(params.viewOffline) != 'undefined'? params.viewOffline: activeSearch || !this.BXIM.settings? true: this.BXIM.settings.viewOffline;
		var viewChat =  typeof(params.viewChat) != 'undefined'? params.viewChat: true;
		var viewOpenChat =  typeof(params.viewOpenChat) != 'undefined'? params.viewOpenChat: true;
		var viewOfflineWithPhones =  typeof(params.viewOfflineWithPhones) != 'undefined'? params.viewOfflineWithPhones: false;
		var callback =  typeof(params.callback) != 'undefined'? params.callback: {};
		if (typeof(callback.empty) != 'function')
		{
			callback.empty = function(){}
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.clearImages();
		}

		var exceptUsers = {};
		if (typeof(params.exceptUsers) != 'undefined')
		{
			for (var i = 0; i < params.exceptUsers.length; i++)
				exceptUsers[params.exceptUsers[i]] = true;
		}

		if (viewGroup)
		{
			groupsTmp = this.BXIM.messenger.groups;
			userInGroup = this.BXIM.messenger.userInGroup;
		}
		else
		{
			groupsTmp = this.BXIM.messenger.woGroups;
			userInGroup = this.BXIM.messenger.woUserInGroup;
		}

		var groupCount = 0;
		for (var i in groupsTmp)
			groupCount++;

		if (groupCount <= 0 && !this.BXIM.messenger.contactListLoad)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
			}));

			this.contactListGetFromServer();
			return items;
		}
		var arSearch = [];
		var arSearchAlt = [];
		if (activeSearch)
		{
			searchText = searchText+'';
			if (!this.isMobile() && this.BXIM.language=='ru' && BX.correctText)
			{
				var correctText = BX.correctText(searchText);
				if (correctText != searchText)
				{
					arSearchAlt = correctText.split(" ");
				}
			}
			arSearch = searchText.split(" ");
		}

		groups[0] = {'id': 0, 'name': BX.message('IM_M_CL_UNREAD'), 'status':'open'};
		for (var i in this.BXIM.messenger.unreadMessage) unreadUsers.push(i);
		userInGroup[0] = {'id':0, 'users': unreadUsers};
		for (var i in groupsTmp)
		{
			if (i != 'last' && i != 0 )
				groups[i] = groupsTmp[i];
		}
		if (viewChat || viewOpenChat)
		{
			var groupChat = [];
			for (var i in this.BXIM.messenger.chat)
			{
				if (!activeSearch && this.BXIM.messenger.chat[i].type == 'call')
					continue;

				if (viewOpenChat && this.BXIM.messenger.chat[i].type == 'open')
				{
					groupChat.push(i);
				}
				else if (viewChat)
				{
					groupChat.push(i);
				}
			}

			groupChat.sort(BX.delegate(function(a, b) {
				i = this.BXIM.messenger.chat[a].name;
				ii = this.BXIM.messenger.chat[b].name;
				if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}
			}, this));

			if (groupChat.length > 0)
			{
				userInGroup['chat'] = {'id':'chat', 'users': groupChat, 'isChat': true};
			}
		}
		else
		{
			delete userInGroup['chat'];
		}

		var sortIndex = this.recentListGetSortIndex();

		for (var i in groups)
		{
			var group = groups[i];
			if (typeof(group) == 'undefined' || !group.name || !BX.type.isNotEmptyString(group.name))
				continue;

			if (!activeSearch && group.id == 'search')
				continue;

			var userItems = [];
			var userDrowedInGroup = {};
			if (userInGroup[i] && !userInGroup[i].isChat)
			{
				var userIdShow = [];
				for (var j = 0; j < userInGroup[i].users.length; j++)
				{
					var user = this.BXIM.messenger.users[userInGroup[i].users[j]];
					if (typeof(user) == 'undefined' || this.BXIM.userId == user.id || typeof(user.name) == 'undefined' || exceptUsers[user.id] || userDrowedInGroup[i + '_' + user.id])
						continue;

					userDrowedInGroup[i + '_' + user.id] = true;

					if (activeSearch)
					{
						var userSearchString = user.name.toLowerCase() + (user.workPosition? (" " + user.workPosition).toLowerCase(): "") + (user.searchMark? " "+user.searchMark: "");
						var skipUser = false;
						for (var s = 0; s < arSearch.length; s++)
							if (userSearchString.indexOf(arSearch[s].toLowerCase()) < 0)
								skipUser = true;

						if (skipUser)
						{
							for (var s = 0; s < arSearchAlt.length; s++)
							{
								if (userSearchString.indexOf(arSearchAlt[s].toLowerCase()) < 0)
									skipUser = true;
								else
									skipUser = false;
							}
						}

						if (skipUser)
							continue;
					}

					userIdShow.push(user.id);
				}

				userIdShow.sort(function(u1, u2) {
					var i1 = sortIndex[u1]? sortIndex[u1]: 0;
					var i2 = sortIndex[u2]? sortIndex[u2]: 0;

					if (i1 > i2) { return -1; }
					else if (i1 < i2) { return 1;}
					else{ return 0;}
				});

				for (var j = 0; j < userIdShow.length; j++)
				{
					var user = this.BXIM.messenger.users[userIdShow[j]];

					var newMessage = '';
					var newMessageCount = '';
					if (extraEnable && this.BXIM.messenger.unreadMessage[user.id] && this.BXIM.messenger.unreadMessage[user.id].length>0)
					{
						newMessage = 'bx-messenger-cl-status-new-message';
						newMessageCount = '<span class="bx-messenger-cl-count-digit">'+(this.BXIM.messenger.unreadMessage[user.id].length<100? this.BXIM.messenger.unreadMessage[user.id].length: '99+')+'</span>';
					}

					var writingMessage = '';
					if (extraEnable && this.countWriting(user.id))
						writingMessage = 'bx-messenger-cl-status-writing';

					var userOnlineStatus = this.getUserStatus(user.id);
					if (viewOfflineWithPhones && user.phoneDevice && userOnlineStatus == "offline")
					{
						userOnlineStatus = 'online';
					}
					if (!activeSearch && i != 'last' && viewOffline == false && userOnlineStatus == "offline" && newMessage == '')
						continue;

					if (this.isMobile())
					{
						var lazyUserId = 'mobile-cl-avatar-id-'+user.id+'-g-'+i;
						var src = 'id="'+lazyUserId+'" src="'+this.BXIM.pathToBlankImage+'" data-src="'+user.avatar+'"';
						BitrixMobile.LazyLoad.registerImage(lazyUserId);
					}
					else
					{
						var src = '_src="'+user.avatar+'" src="'+this.BXIM.pathToBlankImage+'"';
						if (activeSearch || (group.status == "open" && groupOpen == 'auto') || groupOpen == true)
							src = 'src="'+user.avatar+'" _src="'+this.BXIM.pathToBlankImage+'"';
					}

					var avatarColor = this.isBlankAvatar(user.avatar)? 'style="background-color: '+user.color+'"': '';

					userItems.push(BX.create("a", {
						props : { className: "bx-messenger-cl-item bx-messenger-cl-id-"+user.id+" bx-messenger-cl-status-" +userOnlineStatus+ " " +newMessage+" "+writingMessage },
						attrs : { href:'#user'+user.id, 'data-userId' : user.id, 'data-name' : BX.util.htmlspecialcharsback(user.name), 'data-status' : userOnlineStatus, 'data-avatar' : user.avatar },
						html :  '<span class="bx-messenger-cl-count">'+newMessageCount+'</span>'+
								'<span class="bx-messenger-cl-avatar" title="'+user.name+'"><img class="bx-messenger-cl-avatar-img'+(this.isBlankAvatar(user.avatar)? " bx-messenger-cl-avatar-img-default": "")+'" '+src+' '+avatarColor+'><span class="bx-messenger-cl-status"></span></span>'+
								'<span class="bx-messenger-cl-user">'+
									'<div class="bx-messenger-cl-user-title'+(user.extranet? " bx-messenger-user-extranet": "")+'">'+(user.nameList? user.nameList: user.name)+'</div>'+
									'<div class="bx-messenger-cl-user-desc">'+this.getUserPosition(user.id)+'</div>'+
								'</span>'
					}));
				}

				if (userItems.length > 0)
				{
					var itemFoundBlock = BX.create("div", {
						attrs : { 'data-groupId-wrap' : group.id },
						props : { className: "bx-messenger-cl-group" +  (activeSearch || (group.status == "open" && groupOpen == 'auto') || groupOpen == true ? " bx-messenger-cl-group-open" : "")},
						children : [
							BX.create("div", {props : { className: "bx-messenger-cl-group-title"}, attrs : { 'data-groupId' : group.id, title : group.name }, html : group.name}),
							BX.create("span", {props : { className: "bx-messenger-cl-group-wrapper"}, children : userItems})
						]
					});
					if (group.id == 'search')
					{
						itemSearch = itemFoundBlock;
					}
					else
					{
						items.push(itemFoundBlock);
					}
				}
			}
			else if (userInGroup[i] && userInGroup[i].isChat)
			{
				var chatIdShow = [];
				for (var j = 0; j < userInGroup[i].users.length; j++)
				{
					var chat = this.BXIM.messenger.chat[userInGroup[i].users[j]];
					if (typeof (chat) == 'undefined' || typeof(chat.name) == 'undefined' || userDrowedInGroup[i+'_chat'+chat.id])
						continue;

					userDrowedInGroup[i+'_chat'+chat.id] = true;

					if (activeSearch)
					{
						var skipUser = false;
						for (var s = 0; s < arSearch.length; s++)
							if (chat.name.toLowerCase().indexOf(arSearch[s].toLowerCase()) < 0)
								skipUser = true;

						if (skipUser)
						{
							for (var s = 0; s < arSearchAlt.length; s++)
							{
								if (chat.name.toLowerCase().indexOf(arSearchAlt[s].toLowerCase()) < 0)
									skipUser = true;
								else
									skipUser = false;
							}
						}

						if (skipUser)
							continue;
					}
					chatIdShow.push(chat.id);
				}

				chatIdShow.sort(function(u1, u2) {
					var i1 = sortIndex['chat'+u1]? sortIndex['chat'+u1]: 0;
					var i2 = sortIndex['chat'+u2]? sortIndex['chat'+u2]: 0;

					if (i1 > i2) { return -1; }
					else if (i1 < i2) { return 1;}
					else{ return 0;}
				});

				for (var j = 0; j < chatIdShow.length; j++)
				{
					var chat = this.BXIM.messenger.chat[chatIdShow[j]];

					var writingMessage = '';
					if (extraEnable && this.countWriting('chat'+chat.id))
						writingMessage = 'bx-messenger-cl-status-writing';

					var newMessage = '';
					var newMessageCount = '';
					if (extraEnable && this.BXIM.messenger.unreadMessage['chat'+chat.id] && this.BXIM.messenger.unreadMessage['chat'+chat.id].length>0)
					{
						newMessage = 'bx-messenger-cl-status-new-message';
						newMessageCount = '<span class="bx-messenger-cl-count-digit">'+(this.BXIM.messenger.unreadMessage['chat'+chat.id].length<100? this.BXIM.messenger.unreadMessage['chat'+chat.id].length: '99+')+'</span>';
					}

					if (this.isMobile())
					{
						var lazyUserId = 'mobile-cl-avatar-id-chat-'+chat.id+'-g-'+i;
						var src = 'id="'+lazyUserId+'" src="'+this.BXIM.pathToBlankImage+'" data-src="'+chat.avatar+'"';
						BitrixMobile.LazyLoad.registerImage(lazyUserId);
					}
					else
					{
						var src = '_src="'+chat.avatar+'" src="'+this.BXIM.pathToBlankImage+'"';
						if (activeSearch || (group.status == "open" && groupOpen == 'auto') || groupOpen == true)
							src = 'src="'+chat.avatar+'" _src="'+this.BXIM.pathToBlankImage+'"';
					}

					var avatarColor = this.isBlankAvatar(chat.avatar)? 'style="background-color: '+chat.color+'"': '';

					var chatHideAvatar = avatarColor? 'bx-messenger-cl-avatar-status-hide': '';

					var chatTypeTitle = BX.message('IM_CL_CHAT_2');
					if (chat.type == 'call')
					{
						chatTypeTitle = BX.message('IM_CL_PHONE');
					}
					else if (chat.type == 'open')
					{
						chatTypeTitle = BX.message('IM_CL_OPEN_CHAT');
					}
					userItems.push(BX.create("span", {
						props : { className: "bx-messenger-cl-item bx-messenger-cl-id-chat"+chat.id+" bx-messenger-cl-status-online "+newMessage+" "+writingMessage},
						attrs : { 'data-userId' : 'chat'+chat.id,  'data-userIsChat' : 'Y', 'data-name' : chat.name, 'data-status' : 'online', 'data-avatar' : chat.avatar },
						html :  '<span class="bx-messenger-cl-count">'+newMessageCount+'</span>'+
								'<span class="bx-messenger-cl-avatar bx-messenger-cl-avatar-'+chat.type+' '+chatHideAvatar+' '+(this.BXIM.messenger.generalChatId == chat.id? "bx-messenger-cl-item-chat-general": "")+'" title="'+chat.name+'"><img class="bx-messenger-cl-avatar-img'+(this.isBlankAvatar(chat.avatar)? " bx-messenger-cl-avatar-img-default": "")+'" '+src+' '+avatarColor+'><span class="bx-messenger-cl-status"></span></span>'+
								'<span class="bx-messenger-cl-user">'+
									'<div class="bx-messenger-cl-user-title'+(chat.extranet? " bx-messenger-user-extranet": "")+'">'+chat.name+'</div>'+
									'<div class="bx-messenger-cl-user-desc">'+(chatTypeTitle)+'</div>'+
								'</span>'
					}));
				}
				if (userItems.length > 0)
				{
					var itemFoundBlock = BX.create("div", {
						attrs : { 'data-groupId-wrap' : group.id },
						props : { className: "bx-messenger-cl-group" +  (activeSearch || (group.status == "open" && groupOpen == 'auto') || groupOpen == true ? " bx-messenger-cl-group-open" : "")},
						children : [
							BX.create("div", {props : { className: "bx-messenger-cl-group-title"}, attrs : { 'data-groupId' : group.id, title : group.name }, html : group.name}),
							BX.create("span", {props : { className: "bx-messenger-cl-group-wrapper"}, children : userItems})
						]
					})
					if (group.id == 'search')
					{
						itemSearch = itemFoundBlock;
					}
					else
					{
						items.push(itemFoundBlock);
					}
				}
			}
		}

		// search by groups
		if (this.BXIM.bitrixIntranet && activeSearch)
		{
			var foundGroup = {};
			for (var i in  this.BXIM.messenger.groups)
			{
				var skipGroup = true;
				for (var s = 0; s < arSearch.length; s++)
					if (this.BXIM.messenger.groups[i].name && this.BXIM.messenger.groups[i].name.toLowerCase().indexOf(arSearch[s].toLowerCase()) >= 0)
						skipGroup = false;

				if (skipGroup)
				{
					for (var s = 0; s < arSearchAlt.length; s++)
					{
						if (this.BXIM.messenger.groups[i].name && this.BXIM.messenger.groups[i].name.toLowerCase().indexOf(arSearchAlt[s].toLowerCase()) >= 0)
							skipGroup = false;
					}
				}

				if (!skipGroup)
				{
					foundGroup[i] = {'id': i, 'name': this.BXIM.messenger.groups[i].name, 'status':'close'};
				}
			}

			for (var i in foundGroup)
			{
				var group = foundGroup[i];
				if (typeof(group) == 'undefined' || !group.name || !BX.type.isNotEmptyString(group.name))
					continue;

				var userDrowedInGroup = {};
				var userItems = [];
				if (this.BXIM.messenger.userInGroup[i] && !this.BXIM.messenger.userInGroup[i].isChat)
				{
					for (var j = 0; j < this.BXIM.messenger.userInGroup[i].users.length; j++)
					{
						var user = this.BXIM.messenger.users[this.BXIM.messenger.userInGroup[i].users[j]];
						if (typeof(user) == 'undefined' || this.BXIM.userId == user.id || typeof(user.name) == 'undefined' || exceptUsers[user.id] || userDrowedInGroup[i+'_'+user.id])
							continue;

						userDrowedInGroup[i+'_'+user.id] = true;

						var newMessage = '';
						var newMessageCount = '';
						if (extraEnable && this.BXIM.messenger.unreadMessage[user.id] && this.BXIM.messenger.unreadMessage[user.id].length>0)
						{
							newMessage = 'bx-messenger-cl-status-new-message';
							newMessageCount = '<span class="bx-messenger-cl-count-digit">'+(this.BXIM.messenger.unreadMessage[user.id].length<100? this.BXIM.messenger.unreadMessage[user.id].length: '99+')+'</span>';
						}

						var writingMessage = '';
						if (extraEnable && this.countWriting(user.id))
							writingMessage = 'bx-messenger-cl-status-writing';

						var userOnlineStatus = this.getUserStatus(user.id);
						if (viewOfflineWithPhones && user.phoneDevice && userOnlineStatus == "offline")
						{
							userOnlineStatus = 'online';
						}
						if (i != 'last' && viewOffline == false && userOnlineStatus == "offline" && newMessage == '')
							continue;

						if (this.isMobile())
						{
							var lazyUserId = 'mobile-cl-avatar-id-'+user.id+'-g-'+i;
							var src = 'id="'+lazyUserId+'" src="'+this.BXIM.pathToBlankImage+'" data-src="'+user.avatar+'"';
							BitrixMobile.LazyLoad.registerImage(lazyUserId);
						}
						else
						{
							var src = '_src="'+user.avatar+'" src="'+this.BXIM.pathToBlankImage+'"';
							if ((group.status == "open" && groupOpen == 'auto') || groupOpen == true)
								src = 'src="'+user.avatar+'" _src="'+this.BXIM.pathToBlankImage+'"';
						}

						var avatarColor = this.isBlankAvatar(user.avatar)? 'style="background-color: '+user.color+'"': '';

						userItems.push(BX.create("span", {
							props : { className: "bx-messenger-cl-item bx-messenger-cl-id-"+user.id+" bx-messenger-cl-status-" +userOnlineStatus+ " " +userOnlineStatus+ " " +newMessage+" "+writingMessage },
							attrs : { 'data-userId' : user.id, 'data-name' : BX.util.htmlspecialcharsback(user.name), 'data-status' : userOnlineStatus, 'data-avatar' : user.avatar },
							html :  '<span class="bx-messenger-cl-count">'+newMessageCount+'</span>'+
									'<span class="bx-messenger-cl-avatar"  title="'+user.name+'"><img class="bx-messenger-cl-avatar-img'+(this.isBlankAvatar(user.avatar)? " bx-messenger-cl-avatar-img-default": "")+'" '+src+' '+avatarColor+'><span class="bx-messenger-cl-status"></span></span>'+
									'<span class="bx-messenger-cl-user">'+
										'<div class="bx-messenger-cl-user-title'+(user.extranet? " bx-messenger-user-extranet": "")+'">'+(user.nameList? user.nameList: user.name)+'</div>'+
										'<div class="bx-messenger-cl-user-desc">'+this.getUserPosition(user.id)+'</div>'+
									'</span>'
						}));
					}
					if (userItems.length > 0)
					{
						items.push(BX.create("div", {
							attrs : { 'data-groupId-wrap' : group.id },
							props : { className: "bx-messenger-cl-group"+(groupOpen == true ? " bx-messenger-cl-group-open" : "") },
							children : [
								BX.create("div", {props : { className: "bx-messenger-cl-group-title"}, attrs : { 'data-groupId' : group.id, title : group.name }, html : group.name}),
								BX.create("span", {props : { className: "bx-messenger-cl-group-wrapper"}, children : userItems})
							]
						}));
					}
				}
			}
		}

		if (itemSearch)
		{
			items.push(itemSearch);
		}


		if (searchWaitBackend)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-search"},
				html : BX.message('IM_M_CL_SEARCH')
			}));
		}
		else if (items.length <= 0)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_CL_EMPTY')
			}));
			callback.empty();
		}

		return items;
	};

	BX.MessengerCommon.prototype.contactListClickItem = function(e)
	{
		this.BXIM.messenger.closeMenuPopup();

		if (this.BXIM.messenger.contactList)
		{
			BX.MessengerCommon.recentListElementToTop(BX.proxy_context.getAttribute('data-userId'));
		}
		if (this.isMobile() || !this.BXIM.messenger.chatList)
		{
			this.BXIM.messenger.popupContactListSearchInput.value = '';
			this.BXIM.messenger.contactListSearchText = '';
			BX.localStorage.set('mns', this.BXIM.messenger.contactListSearchText, 5);

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = true;
			this.BXIM.messenger.contactList = false;
			this.BXIM.messenger.contactListShowed = {};

			this.userListRedraw();
		}
		if (this.isMobile())
		{
			this.BXIM.messenger.openMessenger(BX.proxy_context.getAttribute('data-userId'), BX.proxy_context);
		}
		else
		{
			this.BXIM.messenger.openMessenger(BX.proxy_context.getAttribute('data-userId'));
		}
		return BX.PreventDefault(e);
	}

	BX.MessengerCommon.prototype.contactListToggleGroup = function()
	{
		var status = '';

		var wrapper = BX.findNextSibling(BX.proxy_context, {className: 'bx-messenger-cl-group-wrapper'});
		if (wrapper.childNodes.length > 0)
		{
			var avatarNodes = BX.findChildrenByClassName(wrapper, "bx-messenger-cl-avatar-img");
			if (BX.hasClass(BX.proxy_context.parentNode, 'bx-messenger-cl-group-open'))
			{
				status = 'close';
				BX.removeClass(BX.proxy_context.parentNode, 'bx-messenger-cl-group-open');
				if (!this.isMobile() && avatarNodes)
				{
					for (var i = 0; i < avatarNodes.length; i++)
					{
						avatarNodes[i].setAttribute('_src', avatarNodes[i].src);
						avatarNodes[i].src = this.BXIM.pathToBlankImage;
					}
				}
			}
			else
			{
				status = 'open';
				BX.addClass(BX.proxy_context.parentNode, 'bx-messenger-cl-group-open');
				if (!this.isMobile() && avatarNodes)
				{
					for (var i = 0; i < avatarNodes.length; i++)
					{
						avatarNodes[i].src = avatarNodes[i].getAttribute('_src');
						avatarNodes[i].setAttribute('_src', this.BXIM.pathToBlankImage);
					}
				}
			}
		}
		else
		{
			if (BX.hasClass(BX.proxy_context.parentNode, 'bx-messenger-cl-group-open'))
			{
				status = 'close';
				BX.removeClass(BX.proxy_context.parentNode, 'bx-messenger-cl-group-open');
			}
			else
			{
				status = 'open';
				BX.addClass(BX.proxy_context.parentNode, 'bx-messenger-cl-group-open');
			}
		}

		var id = BX.proxy_context.getAttribute('data-groupId');
		var viewGroup = this.BXIM.messenger.contactListSearchText != null && this.BXIM.messenger.contactListSearchText.length > 0? false: this.BXIM.settings.viewGroup;
		if (viewGroup)
			this.BXIM.messenger.groups[id].status = status;
		else if (this.BXIM.messenger.woGroups[id])
			this.BXIM.messenger.woGroups[id].status = status;

		BX.userOptions.save('IM', 'groupStatus', id, status);
		BX.localStorage.set('mgp', {'id': id, 'status': status}, 5);
	}

	BX.MessengerCommon.prototype.contactListGetFromServer = function()
	{
		if (this.BXIM.messenger.contactListLoad)
			return false;

		this.BXIM.messenger.contactListLoad = true;
		BX.ajax({
			url: this.BXIM.pathToAjax+'?CONTACT_LIST&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_CONTACT_LIST' : 'Y', 'IM_AJAX_CALL' : 'Y', 'DESKTOP' : (!this.isMobile() && this.BXIM.desktop && this.BXIM.desktop.ready()? 'Y': 'N'), 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					for (var i in data.USERS)
						this.BXIM.messenger.users[i] = data.USERS[i];

					for (var i in data.GROUPS)
						this.BXIM.messenger.groups[i] = data.GROUPS[i];

					for (var i in data.CHATS)
					{
						if (this.BXIM.messenger.chat[i] && this.BXIM.messenger.chat[i].fake)
							data.CHATS[i].fake = true;
						else if (!this.BXIM.messenger.chat[i])
							data.CHATS[i].fake = true;

						this.BXIM.messenger.chat[i] = data.CHATS[i];
					}

					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined')
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}

					for (var i in data.WO_GROUPS)
						this.BXIM.messenger.woGroups[i] = data.WO_GROUPS[i];

					for (var i in data.WO_USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.woUserInGroup[i]) == 'undefined')
						{
							this.BXIM.messenger.woUserInGroup[i] = data.WO_USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.WO_USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.woUserInGroup[i].users.push(data.WO_USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.woUserInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.woUserInGroup[i].users)
						}
					}

					this.userListRedraw();

					if (!this.isMobile())
					{
						this.BXIM.messenger.dialogStatusRedraw();

						if (this.BXIM.messenger.popupChatDialogContactListElements != null)
						{
							this.contactListPrepareSearch('popupChatDialogContactListElements', this.BXIM.messenger.popupChatDialogContactListElements, this.BXIM.messenger.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': this.BXIM.messenger.popupChatDialogContactListElementsType == 'MENTION'});
						}
						if (this.BXIM.webrtc.popupTransferDialogContactListElements != null)
						{
							this.contactListPrepareSearch('popupTransferDialogContactListElements', this.BXIM.webrtc.popupTransferDialogContactListElements, this.BXIM.webrtc.popupTransferDialogContactListSearch.value, {'viewChat': false, 'viewOpenChat': false, 'viewOffline': false, 'viewOfflineWithPhones': true});
						}
					}
				}
				else
				{
					this.BXIM.messenger.contactListLoad = false;
					if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(BX.delegate(this.contactListGetFromServer, this), 2000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						if (this.BXIM.desktop && this.BXIM.desktop.ready())
						{
							setTimeout(BX.delegate(this.contactListGetFromServer, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.sendAjaxTry = 0;
				this.BXIM.messenger.contactListLoad = false;
			}, this)
		});
	};

	BX.MessengerCommon.prototype.contactListRealSearch = function(text, callback)
	{
		if (!this.BXIM.messenger.realSearch)
			return false;

		this.contactListRealSearchText = text;
		clearTimeout(this.BXIM.messenger.contactListSearchTimeout);
		this.BXIM.messenger.contactListSearchTimeout = setTimeout(BX.delegate(function(){
			if (this.contactListRealSearchText.length < 3)
			{
				this.BXIM.messenger.realSearchFound = true;
				return false;
			}

			BX.ajax({
				url: this.BXIM.pathToAjax+'?CONTACT_LIST_SEARCH&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_CONTACT_LIST_SEARCH' : 'Y', 'SEARCH' : this.contactListRealSearchText, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){

					this.BXIM.messenger.realSearchFound = true;

					this.BXIM.messenger.userInGroup['search'] = {'id':'search', 'users': []};
					this.BXIM.messenger.woUserInGroup['search'] = {'id':'search', 'users': []};

					for (var i in data.USERS)
					{
						if (this.BXIM.messenger.woUserInGroup['all'].users.indexOf(i) >= 0)
							continue;

						this.BXIM.messenger.users[i] = data.USERS[i];
						this.BXIM.messenger.userInGroup['search']['users'].push(i);
						this.BXIM.messenger.woUserInGroup['search']['users'].push(i);
					}

					if (typeof(callback) != 'undefined')
					{
						callback()
					}
					else if (this.BXIM.messenger.contactList)
					{
						this.contactListRedraw({FORCE: true});
					}
				}, this),
				onfailure: BX.delegate(function()	{
					this.BXIM.messenger.realSearchFound = true;
				}, this)
			});
		}, this), 1500);

	}

	BX.MessengerCommon.prototype.contactListSearchClear = function(e)
	{
		clearTimeout(this.BXIM.messenger.contactListSearchTimeout);
		clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

		this.BXIM.messenger.realSearchFound = true;

		this.BXIM.messenger.popupContactListSearchInput.value = '';
		this.BXIM.messenger.contactListSearchText = BX.util.trim(this.BXIM.messenger.popupContactListSearchInput.value);
		BX.localStorage.set('mns', this.BXIM.messenger.contactListSearchText, 5);

		BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
		BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active bx-messenger-box-contact-hover');
		this.BXIM.messenger.popupContactListActive = false;
		this.BXIM.messenger.popupContactListHovered = false;
		clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);

		this.BXIM.messenger.chatList = false;
		this.BXIM.messenger.recentList = true;
		this.BXIM.messenger.contactList = false;
		this.BXIM.messenger.contactListShowed = {};

		this.userListRedraw();
	}

	BX.MessengerCommon.prototype.contactListSearch = function(event)
	{
		if (event.keyCode == 16 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 91) // 224, 17
			return false;

		if (event.keyCode == 37 || event.keyCode == 39)
			return true;

		if (this.BXIM.messenger.popupContactListSearchInput.value != this.BXIM.messenger.contactListSearchLastText || this.BXIM.messenger.popupContactListSearchInput.value  == '')
		{
		}
		else if (event.keyCode == 224 || event.keyCode == 18 || event.keyCode == 17)
		{
			return true;
		}

		if (event.keyCode == 38 || event.keyCode == 40)
		{
			// todo up/down select
			return true;
		}

		if (this.isMobile())
		{
			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = false;
			this.BXIM.messenger.contactList = true;
			this.BXIM.messenger.contactListShowed = {};

			if (!app.enableInVersion(10))
			{
				setTimeout(function(){
					document.body.scrollTop = 0;
				}, 100);
			}
		}
		else
		{
			if (event.keyCode == 27)
			{
				if (this.BXIM.messenger.realSearch)
				{
					this.BXIM.messenger.realSearchFound = true;
				}

				if (this.BXIM.messenger.contactListSearchText <= 0 && !this.BXIM.messenger.chatList)
				{
					this.BXIM.messenger.popupContactListSearchInput.value = "";
					if (!this.isMobile() && this.BXIM.messenger.popupMessenger && !this.BXIM.messenger.desktop.ready() && !this.BXIM.messenger.webrtc.callInit)
					{
						this.BXIM.messenger.popupMessenger.destroy();
						return true;
					}
				}
				else
				{
					this.contactListSearchClear();
					this.BXIM.messenger.popupMessengerTextarea.focus();
					return true;
				}
			}

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = false;
			this.BXIM.messenger.contactList = true;
			this.BXIM.messenger.contactListShowed = {};

			if (event.keyCode == 13)
			{
				if (this.BXIM.messenger.realSearch)
				{
					this.BXIM.messenger.realSearchFound = true;
				}
				this.BXIM.messenger.popupContactListSearchInput.value = '';
				var item = BX.findChildByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-item");
				if (item)
				{
					this.recentListElementToTop(item.getAttribute('data-userId'));
					this.BXIM.openMessenger(item.getAttribute('data-userid'));
				}
			}
		}

		if (this.BXIM.messenger.popupContactListSearchInput.value == this.BXIM.messenger.contactListSearchLastText)
		{
			return true;
		}
		this.BXIM.messenger.contactListSearchText = BX.util.trim(this.BXIM.messenger.popupContactListSearchInput.value);
		this.BXIM.messenger.contactListSearchLastText = this.BXIM.messenger.contactListSearchText;

		if (this.BXIM.messenger.realSearch)
		{
			this.BXIM.messenger.realSearchFound = this.BXIM.messenger.contactListSearchText.length < 3;
		}

		if (!this.isMobile())
		{
			BX.localStorage.set('mns', this.BXIM.messenger.contactListSearchText, 5);
		}

		if (this.BXIM.messenger.contactListSearchText == '')
		{
			if (this.BXIM.messenger.realSearch)
			{
				this.BXIM.messenger.realSearchFound = true;
			}

			this.BXIM.messenger.chatList = false;
			this.BXIM.messenger.recentList = true;
			this.BXIM.messenger.contactList = false;
			this.BXIM.messenger.contactListShowed = {};

			BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
			BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active bx-messenger-box-contact-hover');
			this.BXIM.messenger.popupContactListActive = false;
			this.BXIM.messenger.popupContactListHovered = false;
			clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);
		}
		else
		{
			BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active');
			BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-hover bx-messenger-box-contact-normal');
			this.BXIM.messenger.popupContactListActive = true;
			this.BXIM.messenger.popupContactListHovered = true;
			clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);

			this.contactListRealSearch(this.BXIM.messenger.contactListSearchText);
		}
		this.userListRedraw();
	};



	/* Section: Recent list */
	BX.MessengerCommon.prototype.recentListRedraw = function(params)
	{
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.MobileActionNotEqual('RECENT'))
			return false;

		if (this.BXIM.messenger.recentList && this.BXIM.messenger.popupMessenger)
		{
			if (!this.isMobile())
			{
				if (this.BXIM.messenger.popupMessenger == null)
					return false;

				this.BXIM.messenger.chatList = false;
				this.BXIM.messenger.recentList = true;
				this.BXIM.messenger.contactList = false;
				this.BXIM.messenger.contactListShowed = {};
			}

			if (this.BXIM.messenger.popupContactListActive)
			{
				BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-normal');
				BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active bx-messenger-box-contact-hover');
				this.BXIM.messenger.popupContactListActive = false;
				this.BXIM.messenger.popupContactListHovered = false;
				clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);
			}

			if (this.BXIM.messenger.contactListSearchText == null || this.BXIM.messenger.contactListSearchText.length > 0)
			{
				this.BXIM.messenger.contactListSearchText = '';
				this.BXIM.messenger.popupContactListSearchInput.value = '';
			}

			if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
				clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

			if (!this.isMobile() && this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
			{
				this.BXIM.messenger.popupPopupMenu.close();
			}

			this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
			BX.adjust(this.BXIM.messenger.popupContactListElementsWrap, {children: this.recentListPrepare(params)});

			if (this.BXIM.messenger.recentListExternal)
			{
				this.BXIM.messenger.recentListExternal.innerHTML = this.BXIM.messenger.popupContactListElementsWrap.innerHTML;
			}

			if (this.isMobile())
			{
				BitrixMobile.LazyLoad.showImages();
			}
		}
		else if (this.BXIM.messenger.recentListExternal)
		{
			this.BXIM.messenger.recentListExternal.innerHTML = '';
			BX.adjust(this.BXIM.messenger.recentListExternal, {children: this.recentListPrepare(params)});
		}

	};

	BX.MessengerCommon.prototype.recentListPrepare = function(params)
	{
		var items = [];
		var groups = {};
		params = typeof(params) == 'object'? params: {};

		var showOnlyChat = params.showOnlyChat;

		if (!this.BXIM.messenger.recentListLoad)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
			}));

			this.recentListGetFromServer();
			return items;
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.clearImages();
		}

		this.BXIM.messenger.recent.sort(function(i, ii) {var i1 = parseInt(i.date); var i2 = parseInt(ii.date); if (i1 > i2) { return -1; } else if (i1 < i2) { return 1;} else{ if (i > ii) { return -1; } else if (i < ii) { return 1;}else{ return 0;}}});
		this.BXIM.messenger.recentListIndex = [];
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (typeof(this.BXIM.messenger.recent[i].userIsChat) == 'undefined')
				this.BXIM.messenger.recent[i].userIsChat = this.BXIM.messenger.recent[i].recipientId.toString().substr(0,4) == 'chat';

			var item = BX.clone(this.BXIM.messenger.recent[i]);
			var chatStatus = '';
			if (item.userIsChat)
			{
				user = this.BXIM.messenger.chat[item.userId.toString().substr(4)];
				if (typeof(user) == 'undefined' || typeof(user.name) == 'undefined')
					continue;
				var userId = 'chat'+user.id;
				//if (this.BXIM.messenger.userChatBlockStatus[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.userChatBlockStatus[this.BXIM.messenger.currentTab.toString().substr(4)][this.BXIM.userId] == 'Y')
				//{
				//	chatStatus = 'bx-messenger-cl-notify-blocked';
				//}
			}
			else if (!showOnlyChat)
			{
				var user = this.BXIM.messenger.users[item.userId];
				if (typeof(user) == 'undefined' || this.BXIM.userId == user.id || typeof(user.name) == 'undefined')
					continue;

				var userId = user.id;
			}
			else
			{
				continue;
			}

			if (parseInt(item.date) > 0)
			{
				item.date = this.formatDate(item.date, this.getDateFormatType('RECENT_TITLE'));
				if (!groups[item.date])
				{
					groups[item.date] = true;
					items.push(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : item.date})
					]}));
				}
			}
			else
			{
				if (!groups['never'])
				{
					groups['never'] = true;
					items.push(BX.create("div", {props : { className: "bx-messenger-recent-group"}, children : [
						BX.create("span", {props : { className: "bx-messenger-recent-group-title"}, html : BX.message('IM_RESENT_NEVER')})
					]}));
				}
			}

			items.push(this.drawContactListElement({
				'id': userId,
				'data': user,
				'text': item.text,
				'textSenderId': item.senderId,
				'textParams': item.params
			}));
			this.BXIM.messenger.recentListIndex.push(userId);
		}

		if (items.length <= 0)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_CL_EMPTY')
			}));
		}
		return items;
	};

	BX.MessengerCommon.prototype.recentListAdd = function(params)
	{
		if (!params.skipDateCheck)
		{
			for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			{
				if (this.BXIM.messenger.recent[i].userId == params.userId && parseInt(this.BXIM.messenger.recent[i].date) > parseInt(params.date))
					return false;
			}
		}

		var newRecent = [];
		newRecent.push(params);

		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			if (this.BXIM.messenger.recent[i].userId != params.userId)
				newRecent.push(this.BXIM.messenger.recent[i]);

		this.BXIM.messenger.recent = newRecent;

		if (this.BXIM.messenger.recentList)
		{
			if (this.isMobile())
			{
				clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
				this.BXIM.messenger.redrawRecentListTimeout = setTimeout(BX.delegate(function(){
					this.recentListRedraw();
				}, this), 300);
			}
			else
			{
				this.recentListRedraw();
			}
		}
	};

	BX.MessengerCommon.prototype.recentListHide = function(userId, sendAjax)
	{
		var newRecent = [];
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			if (this.BXIM.messenger.recent[i].userId != userId)
				newRecent.push(this.BXIM.messenger.recent[i]);

		this.BXIM.messenger.recent = newRecent;
		if (this.BXIM.messenger.recentList)
			this.recentListRedraw();

		if (!this.isMobile())
			BX.localStorage.set('mrlr', userId, 5);

		sendAjax = sendAjax != false;
		if (sendAjax)
		{
			BX.ajax({
				url: this.BXIM.pathToAjax+'?RECENT_HIDE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_RECENT_HIDE' : 'Y', 'USER_ID' : userId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
			this.readMessage(userId, true, true);

			if (userId.toString().substr(0, 4) == 'chat')
			{
				if (this.isMobile())
				{
					app.onCustomEvent('onPullClearWatch', {'id': 'IM_PUBLIC_'+userId.substr(4)});
				}
				else
				{
					BX.PULL.clearWatch('IM_PUBLIC_'+userId.substr(4));
				}
				delete this.BXIM.messenger.showMessage[userId];
			}

			this.BXIM.messenger.currentTab = 0;
			this.BXIM.messenger.extraOpen(
				BX.create("div", { attrs : { style : "padding-top: 300px"}, props : { className : "bx-messenger-box-empty" }, html: BX.message('IM_M_EMPTY')})
			);
		}
	};

	BX.MessengerCommon.prototype.recentListElementUpdate = function(userId, messageId, messageText)
	{
		if (userId.toString().substr(0,4) == 'chat')
		{
			for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			{
				if (this.BXIM.messenger.recent[i].userIsChat && this.BXIM.messenger.recent[i].recipientId == userId)
				{
					if (this.BXIM.messenger.recent[i].id == messageId)
					{
						this.BXIM.messenger.recent[i].text = messageText;
					}
					break;
				}
			}
		}
		else
		{
			for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
			{
				if (!this.BXIM.messenger.recent[i].userIsChat && this.BXIM.messenger.recent[i].recipientId == userId)
				{
					if (this.BXIM.messenger.recent[i].id == messageId)
					{
						this.BXIM.messenger.recent[i].text = messageText;
					}
					break;
				}
			}
		}
	}

	BX.MessengerCommon.prototype.recentListElementToTop = function(userId)
	{
		var userFound = false;
		for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
		{
			if (this.BXIM.messenger.recent[i].userId == userId)
			{
				userFound = true;
				this.BXIM.messenger.recent[i].date = BX.MessengerCommon.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET"));

				break;
			}
		}

		if (!userFound)
		{
			var messageText = '';
			var lastMessage = this.getLastMessageInDialog(userId);
			if (lastMessage)
			{
				if (lastMessage.text)
				{
					messageText = lastMessage.text;
				}
				else if (lastMessage.params && lastMessage.params.FILE_ID.length > 1)
				{
					messageText = '['+BX.message('IM_F_FILE')+']';
				}
				else if (lastMessage.params && lastMessage.params.ATTACH.length > 1)
				{
					item.text = '['+BX.message('IM_F_ATTACH')+']';
				}
			}

			if (!messageText)
			{
				var userParam = this.getUserParam(userId);
				if (userParam.type == 'chat')
				{
					messageText = BX.message('IM_CL_CHAT_2');
				}
				else if (userParam.type == 'open')
				{
					messageText = BX.message('IM_CL_OPEN_CHAT');
				}
				else if(userParam.type == 'call')
				{
					messageText = BX.message('IM_CL_PHONE');
				}
				else
				{
					messageText = this.getUserPosition(userId)
				}
			}

			this.BXIM.messenger.recent.push({
				'id': 'tempSort'+(+new Date()),
				'date': BX.MessengerCommon.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET")),
				'skipDateCheck': true,
				'recipientId': userId,
				'senderId': userId,
				'text': BX.MessengerCommon.prepareText(messageText, true),
				'userId': userId,
				'params': {}
			});
		}

		if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
			this.recentListRedraw();

		if (!this.isMobile())
			BX.localStorage.set('mrlr', userId, 5);
	};

	BX.MessengerCommon.prototype.recentListGetSortIndex = function()
	{
		var sortIndex = {};
		var tmpIndex = 0;

		if (this.BXIM.messenger.recent.length <= 0)
		{
			this.recentListGetFromServer();
		}

		for (var item = 0; item < this.BXIM.messenger.recent.length; item++)
		{
			tmpIndex =  this.BXIM.messenger.recent.length-item;
			sortIndex[this.BXIM.messenger.recent[item].recipientId] = tmpIndex;
		}

		return sortIndex;
	}

	BX.MessengerCommon.prototype.recentListGetFromServer = function()
	{
		if (this.BXIM.messenger.recentListLoad)
			return false;

		this.BXIM.messenger.recentListLoad = true;
		BX.ajax({
			url: this.BXIM.pathToAjax+'?RECENT_LIST&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_RECENT_LIST' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					this.BXIM.messenger.recent = [];
					for (var i in data.RECENT)
					{
						data.RECENT[i].date = parseInt(data.RECENT[i].date)-parseInt(BX.message('USER_TZ_OFFSET'));
						this.BXIM.messenger.recent.push(data.RECENT[i]);
					}

					var arRecent = false;
					for(var i in this.BXIM.messenger.unreadMessage)
					{
						for (var k = 0; k < this.BXIM.messenger.unreadMessage[i].length; k++)
						{
							if (!arRecent || arRecent.SEND_DATE <= this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].date)
							{
								arRecent = {
									'ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].id,
									'SEND_DATE': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].date,
									'RECIPIENT_ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].recipientId,
									'SENDER_ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].senderId,
									'USER_ID': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].senderId,
									'SEND_MESSAGE': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].text,
									'PARAMS': this.BXIM.messenger.message[this.BXIM.messenger.unreadMessage[i][k]].params
								};
							}
						}
					}
					if (arRecent)
					{
						this.recentListAdd({
							'userId': arRecent.RECIPIENT_ID.toString().substr(0,4) == 'chat'? arRecent.RECIPIENT_ID: arRecent.USER_ID,
							'id': arRecent.ID,
							'date': arRecent.SEND_DATE,
							'recipientId': arRecent.RECIPIENT_ID,
							'senderId': arRecent.SENDER_ID,
							'text': arRecent.SEND_MESSAGE,
							'params': arRecent.PARAMS
						}, true);
					}

					for (var i in data.CHAT)
					{
						if (this.BXIM.messenger.chat[i] && this.BXIM.messenger.chat[i].fake)
							data.CHAT[i].fake = true;
						else if (!this.BXIM.messenger.chat[i])
							data.CHAT[i].fake = true;

						this.BXIM.messenger.chat[i] = data.CHAT[i];
					}

					for (var i in data.USERS)
						this.BXIM.messenger.users[i] = data.USERS[i];

					if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
						this.recentListRedraw();

					this.BXIM.messenger.smile = data.SMILE;
					this.BXIM.messenger.smileSet = data.SMILE_SET;

					this.BXIM.settingsNotifyBlocked = data.NOTIFY_BLOCKED;
					if (!this.isMobile())
						this.BXIM.messenger.dialogStatusRedraw();

					if (this.BXIM.messenger.recent.length == 0)
					{
						this.chatListPrepare();
					}
				}
				else
				{
					this.BXIM.messenger.recentListLoad = false;
					if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(BX.delegate(this.recentListGetFromServer, this), 2000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						if (this.BXIM.desktop && this.BXIM.desktop.ready())
						{
							setTimeout(BX.delegate(this.recentListGetFromServer, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.sendAjaxTry = 0;
				this.BXIM.messenger.recentListLoad = false;
			}, this)
		});
	};

	BX.MessengerCommon.prototype.drawContactListElement = function(params)
	{
		params.userIsChat = params.id.toString().substr(0,4) == 'chat';
		params.extraClass = params.extraClass || '';
		params.showLastMessage = params.showLastMessage === false? false: true;

		var chatStatus = '';
		var newMessage = '';
		var newMessageCount = '';
		if (this.BXIM.messenger.unreadMessage[params.id] && this.BXIM.messenger.unreadMessage[params.id].length>0)
		{
			newMessage = 'bx-messenger-cl-status-new-message';
			newMessageCount = '<span class="bx-messenger-cl-count-digit">'+(this.BXIM.messenger.unreadMessage[params.id].length<100? this.BXIM.messenger.unreadMessage[params.id].length: '99+')+'</span>';
		}

		var writingMessage = '';

		if (this.countWriting(params.id))
			writingMessage = 'bx-messenger-cl-status-writing';

		if (!params.data.avatar)
			params.data.avatar = this.BXIM.pathToBlankImage;

		var avatarId = '';
		var avatarLink = params.data.avatar;
		var mobileItemActive = '';
		if (this.isMobile())
		{
			if (this.BXIM.messenger.currentTab == params.id)
			{
				mobileItemActive = 'bx-messenger-cl-item-active ';
			}
			var lazyUserId = 'mobile-rc-avatar-id-'+params.data.id;
			avatarId = 'id="'+lazyUserId+'" data-src="'+params.data.avatar+'"';
			avatarLink = this.BXIM.pathToBlankImage;
			BitrixMobile.LazyLoad.registerImage(lazyUserId);
		}

		var description = '';
		if (this.BXIM.settings.viewLastMessage && params.showLastMessage)
		{
			if (this.BXIM.messenger.message[params.id] && this.BXIM.messenger.message[params.id].text)
			{
				params.text = this.BXIM.messenger.message[params.id].text;
			}
			if (!params.text && params.textParams && params.textParams['FILE_ID'] && params.textParams['FILE_ID'].length > 0)
			{
				params.text = '['+BX.message('IM_F_FILE')+']';
			}
			else if (!params.text && params.textParams && params.textParams['ATTACH'] && params.textParams['ATTACH'].length > 0)
			{
				params.text = '['+BX.message('IM_F_ATTACH')+']';
			}

			var directionIcon = '';
			if (params.textSenderId == this.BXIM.userId)
				directionIcon = '<span class="bx-messenger-cl-user-reply"></span>';

			params.text = this.prepareText(params.text);
			params.text = params.text.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
			params.text = params.text.replace(/\[[buis]\](.*?)\[\/[buis]\]/ig, '$1');
			params.text = params.text.replace(/<s>([^"]*)<\/s>/ig, '');
			params.text = params.text.replace('<br />', ' ').replace(/<\/?[^>]+>/gi, '').replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, " ["+BX.message("IM_M_QUOTE_BLOCK")+"] ");
			if (params.text.length <= 0)
			{
				params.text = BX.message('IM_M_DELETED');
			}
			description = directionIcon+''+params.text;
		}
		else
		{
			if (params.userIsChat)
			{
				if (params.data.type == 'call')
				{
					description = BX.message('IM_CL_PHONE');
				}
				else if (params.data.type == 'open')
				{
					description = BX.message('IM_CL_OPEN_CHAT');
				}
				else
				{
					description = BX.message('IM_CL_CHAT_2');
				}
			}
			else
			{
				description = this.getUserPosition(params.id);
			}
		}

		var avatarColor = this.isBlankAvatar(params.data.avatar)? 'style="background-color: '+params.data.color+'"': '';
		var chatHideAvatar = params.userIsChat && avatarColor? 'bx-messenger-cl-avatar-status-hide': '';

		return BX.create("span", {
			props : { className: "bx-messenger-cl-item  bx-messenger-cl-id-"+(params.userIsChat? 'chat':'')+params.data.id+" "+mobileItemActive+(params.userIsChat? ("bx-messenger-cl-item-chat "+newMessage+" "+writingMessage+" "+chatStatus+" "+(this.BXIM.messenger.generalChatId == params.data.id? "bx-messenger-cl-item-chat-general": "")): ("bx-messenger-cl-status-" +this.getUserStatus(params.data.id)+ " " +newMessage+" "+writingMessage))+" "+params.extraClass },
			attrs : { 'data-userId' : params.id, 'data-name' : BX.util.htmlspecialcharsback(params.data.name), 'data-status' : this.getUserStatus(params.data.id), 'data-avatar' : params.data.avatar, 'data-userIsChat' : params.userIsChat },
			html :  '<span class="bx-messenger-cl-count">'+newMessageCount+'</span>'+
					'<span title="'+params.data.name+'" class="bx-messenger-cl-avatar '+(params.userIsChat? 'bx-messenger-cl-avatar-'+params.data.type+' '+(this.BXIM.messenger.generalChatId == params.data.id? " bx-messenger-cl-item-chat-general": ""): '')+' '+chatHideAvatar+'">' +
						'<img class="bx-messenger-cl-avatar-img'+(this.isBlankAvatar(params.data.avatar)? " bx-messenger-cl-avatar-img-default": "")+'" src="'+avatarLink+'" '+avatarId+' '+avatarColor+'>' +
						'<span class="bx-messenger-cl-status"></span>' +
					'</span>'+
					'<span class="bx-messenger-cl-user">'+
						'<div class="bx-messenger-cl-user-title'+(params.data.extranet? " bx-messenger-user-extranet": "")+'">'+(params.data.nameList? params.data.nameList: params.data.name)+'</div>'+
						'<div class="bx-messenger-cl-user-desc">'+description+'</div>'+
					'</span>'
		});
	}

	/* Section: Chat list */
	BX.MessengerCommon.prototype.chatListRedraw = function(params)
	{
		if (this.MobileActionNotEqual('RECENT'))
			return false;

		BX.addClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-active');
		BX.removeClass(this.BXIM.messenger.popupContactListWrap, 'bx-messenger-box-contact-hover bx-messenger-box-contact-normal');
		this.BXIM.messenger.popupContactListActive = true;
		this.BXIM.messenger.popupContactListHovered = true;
		clearTimeout(this.BXIM.messenger.popupContactListWrapAnimation);

		if (!this.isMobile())
		{
			if (this.BXIM.messenger.popupMessenger == null)
				return false;
		}

		this.BXIM.messenger.chatList = true;
		this.BXIM.messenger.recentList = false;
		this.BXIM.messenger.contactList = false;

		clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
		clearTimeout(this.BXIM.messenger.redrawRecentListTimeout);
		if (this.BXIM.messenger.redrawContactListTimeout['contactList'])
			clearTimeout(this.BXIM.messenger.redrawContactListTimeout['contactList']);

		if (!this.isMobile() && this.BXIM.messenger.popupPopupMenu != null && this.BXIM.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
		{
			this.BXIM.messenger.popupPopupMenu.close();
		}

		this.BXIM.messenger.popupContactListElementsWrap.innerHTML = '';
		BX.adjust(this.BXIM.messenger.popupContactListElementsWrap, {children: this.chatListPrepare(params)});

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.showImages();
		}
	};

	BX.MessengerCommon.prototype.chatListPrepare = function(params)
	{
		var items = [];
		var groups = {};
		params = typeof(params) == 'object'? params: {};

		var showOnlyChat = params.showOnlyChat;

		if (!this.BXIM.messenger.contactListLoad)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
			}));

			this.contactListGetFromServer();
			return items;
		}

		if (this.isMobile())
		{
			BitrixMobile.LazyLoad.clearImages();
		}

		var contactListSize = this.BXIM.messenger.popupContactListElementsSize;
		var elementSize = 46;
		var categorySize = 29;
		var moreSize = 26;
		var categoryCount = 0;
		var minElementPerCategory = 3;

		var category = [
			{'id': 'open', 'name': BX.message('IM_CTL_CHAT_OPEN'), 'title': BX.message('IM_CL_CREATE_OPEN'), 'more': BX.message('IM_CL_MORE_OPEN'), skip: !this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet},
			{'id': 'chat', 'name': BX.message('IM_CTL_CHAT_CHAT'), 'title': BX.message('IM_CL_CREATE_CHAT'), 'more': BX.message('IM_CL_MORE_CHAT')},
			{'id': 'call', 'name': BX.message('IM_CTL_CHAT_CALL'), 'title': '', 'more': BX.message('IM_CL_MORE_CALL'),  skip: !this.BXIM.webrtc.phoneEnabled},
			{'id': 'private', 'name': BX.message('IM_CTL_CHAT_PRIVATE'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_PRIVATE')},
			{'id': 'extranet', 'name': BX.message('IM_CTL_CHAT_EXTRANET'), 'title': BX.message('IM_CL_CREATE_PRIVATE'), 'more': BX.message('IM_CL_MORE_EXTRANET')},
			{'id': 'blocked', 'name': BX.message('IM_CTL_CHAT_BLOCKED'), 'title': '', 'more': BX.message('IM_CL_MORE_EXTRANET')}
		];

		for (var i = 0; i < category.length; i++)
		{
			if (category[i].skip)
				continue;

			categoryCount++;
		}

		var availContactListSize = contactListSize-(categorySize*categoryCount);
		var maxElementElements = parseInt(availContactListSize/elementSize);
		var maxElementPerCategory = Math.max(parseInt(availContactListSize/categoryCount/elementSize), minElementPerCategory);

		var showedElements = 0;
		var extraElements = 0;

		for (var i = 0; i < category.length; i++)
		{
			category[i].countElement = 0;

			if (category[i].skip)
				continue;

			category[i].countElement = maxElementPerCategory;
		}

		var sortIndex = this.recentListGetSortIndex();
		var groupElements = {};
		var extraElementsGroup = [];
		for (var i = 0; i < category.length; i++)
		{
			if (category[i].skip)
				continue;

			groupElements[i] = [];
			if (category[i].id == 'private' || category[i].id == 'extranet' || category[i].id == 'blocked')
			{
				for (var userId in this.BXIM.messenger.users)
				{
					if (this.BXIM.messenger.users.hasOwnProperty(userId))
					{
						if (userId == this.BXIM.userId)
							continue;

						var chatId = this.BXIM.messenger.userChat[userId];
						if (category[i].id == 'blocked')
						{
							//console.log(chatId, userId);
							if (
								!this.BXIM.messenger.userChatBlockStatus[chatId]
								|| !this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId]
								|| this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] == 'N'
							)
							{
								continue;
							}
						}
						else
						{
							if (
								this.BXIM.messenger.userChatBlockStatus[chatId]
								&& this.BXIM.messenger.userChatBlockStatus[chatId][this.BXIM.userId] == 'Y'
							)
							{
								continue;
							}
						}

						if (category[i].id == 'extranet')
						{

							if (!this.BXIM.messenger.users[userId].extranet)
								continue;
						}
						else
						{

							if (this.BXIM.messenger.users[userId].extranet)
								continue;
						}

						if (sortIndex[userId])
						{
							groupElements[i].push(this.BXIM.messenger.users[userId]);
						}
					}
				}
				groupElements[i].sort(function(u1, u2) {
					var i1 = sortIndex[u1.id]? sortIndex[u1.id]: 0;
					var i2 = sortIndex[u2.id]? sortIndex[u2.id]: 0;

					if (i1 > i2) { return -1; }
					else if (i1 < i2) { return 1;}
					else{ return 0;}
				});
			}
			else if (category[i].id == 'chat' || category[i].id == 'open' || category[i].id == 'call')
			{
				for (var chatId in this.BXIM.messenger.chat)
				{
					if (this.BXIM.messenger.chat.hasOwnProperty(chatId))
					{
						if (this.BXIM.messenger.chat[chatId].type != category[i].id)
						{
							continue;
						}

						if (this.BXIM.messenger.generalChatId == chatId && (!this.BXIM.messenger.openChatEnable || this.BXIM.userExtranet))
						{
							continue;
						}
						groupElements[i].push(this.BXIM.messenger.chat[chatId]);
					}
				}
				groupElements[i].sort(BX.delegate(function(u1, u2) {
					var i1 = sortIndex['chat'+u1.id]? sortIndex['chat'+u1.id]: 0;
					var i2 = sortIndex['chat'+u2.id]? sortIndex['chat'+u2.id]: 0;

					if (this.BXIM.messenger.generalChatId == u1.id)
					{
						i1 = 10000000;
					}
					else if (this.BXIM.messenger.userChatBlockStatus[u1.id] && this.BXIM.messenger.userChatBlockStatus[u1.id][this.BXIM.userId] == 'Y')
					{
						i1 = -1;
					}

					if (this.BXIM.messenger.generalChatId == u2.id)
					{
						i2 = 10000000;
					}
					else if (this.BXIM.messenger.userChatBlockStatus[i2.id] && this.BXIM.messenger.userChatBlockStatus[i2.id][this.BXIM.userId] == 'Y')
					{
						i2 = -1;
					}

					if (i1 > i2) { return -1; }
					else if (i1 > i2) { return -1; }
					else if (i1 < i2) { return 1;}
					else{ return 0;}
				}, this));
			}
			if (category[i].countElement > groupElements[i].length)
			{
				showedElements += groupElements[i].length;
				extraElements += category[i].countElement-groupElements[i].length;
			}
			else
			{
				extraElementsGroup.push(i);
				showedElements += category[i].countElement;
			}
		}

		if (showedElements < maxElementElements)
		{
			var categoryId = 0;
			var maxCategoryId = extraElementsGroup.length;

			for (var i = 0; i < extraElements; i++)
			{
				if (extraElementsGroup[categoryId] && category[extraElementsGroup[categoryId]])
				{
					category[extraElementsGroup[categoryId]].countElement = category[extraElementsGroup[categoryId]].countElement+1;
				}
				categoryId = categoryId == maxCategoryId-1? 0: categoryId+1;
			}
		}

		for (var i = 0; i < category.length; i++)
		{
			if (category[i].skip)
				continue;

			if (groupElements[i].length <= 0 && (category[i].id == 'call' || category[i].id == 'extranet' || category[i].id == 'blocked'))
				continue;

			items.push(BX.create("div", {props : { className: "bx-messenger-chatlist-group"}, children : [
				(category[i].id == 'call' || category[i].id == 'blocked')? null: BX.create("span", {attrs: {'data-type': category[i].id}, props : { title: category[i].title, className: "bx-messenger-chatlist-group-add"}}),
				BX.create("span", {props : { className: "bx-messenger-chatlist-group-title"}, html : category[i].name})
			]}));

			if (groupElements[i].length <= 0)
			{
				continue;
			}

			var categoryItems = [];
			var countElements = 1;
			for (var j = 0; j < groupElements[i].length; j++)
			{
				var isShown = countElements <= category[i].countElement;

				countElements++;

				if (category[i].id == 'private' || category[i].id == 'extranet')
				{
					var user = groupElements[i][j];

					categoryItems.push(this.drawContactListElement({
						'id': user.id,
						'data': user,
						'showLastMessage': false,
						'extraClass': isShown? '': 'bx-messenger-hide'
					}));
				}
				else if (category[i].id == 'chat' || category[i].id == 'open' || category[i].id == 'call')
				{
					var chat = groupElements[i][j];
					categoryItems.push(this.drawContactListElement({
						'id': 'chat'+chat.id,
						'data': chat,
						'showLastMessage': false,
						'extraClass': isShown? 'bx-messenger-chatlist-chat': 'bx-messenger-chatlist-chat bx-messenger-hide'
					}));
				}
			}

			if (category[i].countElement < groupElements[i].length)
			{
				categoryItems.push(BX.create("div", {props : { className: "bx-messenger-chatlist-more-wrap"}, children : [
					BX.create("span", {attrs: {
						'data-id': category[i].id,
						'data-text': BX.message('IM_CL_MORE').replace("#COUNT#", groupElements[i].length-category[i].countElement),
						'data-title': category[i].more
					}, props : {
						title: category[i].more,
						className: "bx-messenger-chatlist-more"
					},
					html : this.BXIM.messenger.contactListShowed[category[i].id]? BX.message('IM_CL_HIDE'): BX.message('IM_CL_MORE').replace("#COUNT#", groupElements[i].length-category[i].countElement)})
				]}));
			}
			if (categoryItems.length > 0)
			{
				items.push(BX.create("div", {props : { className: "bx-messenger-chatlist-category"+(this.BXIM.messenger.contactListShowed[category[i].id]? ' bx-messenger-chatlist-show-all': '')}, children : categoryItems}));
			}
		}

		if (items.length <= 0)
		{
			items.push(BX.create("div", {
				props : { className: "bx-messenger-cl-item-empty"},
				html :  BX.message('IM_M_CL_EMPTY')
			}));
		}
		return items;
	};


	/* Section: Message */

	BX.MessengerCommon.prototype.drawMessage = function(dialogId, message, scroll, appendTop)
	{
		if (this.BXIM.messenger.popupMessenger == null || dialogId != this.BXIM.messenger.currentTab || typeof(message) != 'object' || dialogId == 0 || !this.MobileActionEqual('DIALOG'))
			return false;

		appendTop = appendTop == true;
		scroll = appendTop? false: scroll;

		if (message.senderId == this.BXIM.userId && this.BXIM.messenger.popupMessengerLastMessage < message.id)
		{
			this.BXIM.messenger.popupMessengerLastMessage = message.id;
		}
		if (typeof(message.params) != 'object')
		{
			message.params = {};
		}

		this.BXIM.messenger.openChatFlag = this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat'? true: false;

		var edited = message.params && message.params.IS_EDITED == 'Y';
		var deleted = message.params && message.params.IS_DELETED == 'Y';
		var temp = message.id.indexOf('temp') == 0;
		var retry = temp && message.retry;
		var system = message.senderId == 0;
		var isChat = this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && (this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "chat" || this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "open");
		var likeEnable = this.BXIM.ppServerStatus;
		if (this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "call")
			likeEnable = false;

		var likeCount = likeEnable && typeof(message.params.LIKE) == "object" && message.params.LIKE.length > 0? message.params.LIKE.length: '';
		var iLikeThis = likeEnable && typeof(message.params.LIKE) == "object" && BX.util.in_array(this.BXIM.userId, message.params.LIKE);

		var filesNode = BX.MessengerCommon.diskDrawFiles(message.chatId, message.params.FILE_ID);
		if (filesNode.length > 0)
		{
			filesNode = BX.create("div", { props : { className : "bx-messenger-file-box"+(message.text != ''? ' bx-messenger-file-box-with-message':'') }, children: filesNode});
		}
		else
		{
			filesNode = null;
		}

		var attachNode = BX.MessengerCommon.drawAttach(message.chatId, message.params.ATTACH);
		// TODO PARSE MESSAGE
		if (attachNode.length > 0)
		{
			attachNode = BX.create("div", { props : { className : "bx-messenger-attach-box" }, children: attachNode});
		}
		else
		{
			attachNode = null;
		}

		var addBlankNode = false;
		if (!filesNode && !attachNode && message.text.length <= 0)
		{
			addBlankNode = true;
			skipAddMessage = true;
		}

		if (message.system && message.system == 'Y')
		{
			system = true;
			message.senderId = 0;
		}

		var messageUser = this.BXIM.messenger.users[message.senderId];
		if (!system && typeof(messageUser) == 'undefined')
		{
			addBlankNode = true;
			skipAddMessage = true;
		}

		if (!this.BXIM.messenger.history[dialogId])
			this.BXIM.messenger.history[dialogId] = [];

		if (parseInt(message.id) > 0)
			this.BXIM.messenger.history[dialogId].push(message.id);

		if (!addBlankNode)
		{
			var messageId = 0;
			var skipAddMessage = false;

			var markNewMessage = false;
			if (this.BXIM.messenger.unreadMessage[dialogId] && BX.util.in_array(message.id, this.BXIM.messenger.unreadMessage[dialogId]))
				markNewMessage = true;
		}

		var insertBefore = false;
		var lastMessage = null;

		if (appendTop)
		{
			lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.firstChild;
			if (lastMessage)
			{
				if (BX.hasClass(lastMessage, "bx-messenger-content-empty") || BX.hasClass(lastMessage, "bx-messenger-content-load"))
				{
					BX.remove(lastMessage);
				}
				else if (BX.hasClass(lastMessage, "bx-messenger-content-group"))
				{
					lastMessage = lastMessage.nextSibling;
				}
			}
		}
		else
		{
			lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;

			if (lastMessage && (BX.hasClass(lastMessage, "bx-messenger-content-empty") || BX.hasClass(lastMessage, "bx-messenger-content-load")))
			{
				BX.remove(lastMessage);
			}
			else if (lastMessage && BX.hasClass(lastMessage, "bx-messenger-content-item-notify"))
			{
				if (message.senderId == this.BXIM.messenger.currentTab || !this.countWriting(this.BXIM.messenger.currentTab))
				{
					BX.remove(lastMessage);
					insertBefore = false;
					lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;
				}
				else
				{
					insertBefore = true;
					lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.lastChild.previousSibling;
				}
			}
		}

		if (!addBlankNode)
		{
			var dateGroupTitle = this.formatDate(message.date, this.getDateFormatType('MESSAGE_TITLE'));
			if (!BX('bx-im-go-'+dateGroupTitle))
			{
				var dateGroupChildren = []
				if (this.BXIM.desktop && this.BXIM.desktop.run())
				{
					dateGroupChildren = [
						BX.create("a", {attrs: {name: 'bx-im-go-'+message.date}, props : { className: "bx-messenger-content-group-link"}}),
						BX.create("a", {attrs: {id: 'bx-im-go-'+dateGroupTitle, href: "#bx-im-go-"+message.date}, props : { className: "bx-messenger-content-group-title"+(this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')}, html : dateGroupTitle})
					];
				}
				else
				{
					dateGroupChildren = [
						BX.create("a", {attrs: {name: 'bx-im-go-'+message.date}, props : { className: "bx-messenger-content-group-link"}}),
						BX.create("div", {attrs: {id: 'bx-im-go-'+dateGroupTitle}, props : { className: "bx-messenger-content-group-title"+(this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')}, html : dateGroupTitle})
					]
				}

				var dateGroupNode = BX.create("div", {props : { className: "bx-messenger-content-group"+(dateGroupTitle == BX.message('FD_TODAY')? " bx-messenger-content-group-today": "")}, children : dateGroupChildren});

				if (appendTop)
				{
					this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(dateGroupNode, this.BXIM.messenger.popupMessengerBodyWrap.firstChild);
					lastMessage = dateGroupNode.nextSibling;
				}
				else
				{
					if (insertBefore && lastMessage.nextElementSibling)
					{
						this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(dateGroupNode, lastMessage.nextElementSibling);
						lastMessage = dateGroupNode;
					}
					else
					{
						this.BXIM.messenger.popupMessengerBodyWrap.appendChild(dateGroupNode);
					}
				}
			}
			if (!system && lastMessage)
			{
				if (message.senderId == lastMessage.getAttribute('data-senderId') && parseInt(message.date)-300 < parseInt(lastMessage.getAttribute('data-messageDate')))
				{
					var lastMessageElement = BX.findChildByClassName(lastMessage, "bx-messenger-content-item-text-message");
					var newMessageElementNode = [
						BX.create("div", { props : { className : "bx-messenger-hr"}}),
						BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")}, children: [
							BX.create("span", { attrs: {title : BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"}}),
							BX.create("span", { props : { className : "bx-messenger-message"+(deleted?" bx-messenger-message-deleted": " ")+(deleted || edited?" bx-messenger-message-edited": "")}, attrs: {'id' : 'im-message-'+message.id}, html: BX.MessengerCommon.prepareText(message.text, false, true, true, (!this.BXIM.messenger.openChatFlag || message.senderId == this.BXIM.userId? false: (this.BXIM.messenger.users[this.BXIM.userId].name)))}),
							filesNode, attachNode
						]})
					];

					if (appendTop)
					{
						for (var i=0,len=newMessageElementNode.length; i<len; i++)
						{
							lastMessageElement.insertBefore(newMessageElementNode[i], lastMessageElement.firstChild);
						}
						lastMessage.setAttribute('data-blockmessageid', message.id);

						if (likeEnable)
						{
							var lastMessageLikeBox = BX.findChildByClassName(lastMessage, "bx-messenger-content-item-like");
							if (lastMessageLikeBox)
							{
								lastMessageLikeBox.className = "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')
								lastMessageLikeBox.innerHTML = '';
								BX.adjust(lastMessageLikeBox, {children: [
									BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, html: likeCount}),
									BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: BX.message(!iLikeThis? 'IM_MESSAGE_LIKE':'IM_MESSAGE_DISLIKE')})
								]});
							}
						}
					}
					else
					{
						for (var i=0,len=newMessageElementNode.length; i<len; i++)
						{
							lastMessageElement.appendChild(newMessageElementNode[i]);
						}

						var lastMessageDateElement = BX.findChildByClassName(lastMessage, "bx-messenger-content-item-date");
						lastMessageDateElement.innerHTML = (temp? BX.message('IM_M_DELIVERED'): ' &nbsp; '+this.formatDate(message.date, this.getDateFormatType('MESSAGE')));

						if (retry)
						{
							this.drawProgessMessage(message.id, {title: BX.message('IM_M_RETRY')});
						}
						else if (temp)
						{
							this.drawProgessMessage(message.id);
						}

						lastMessage.setAttribute('data-messageDate', message.date);
						lastMessage.setAttribute('data-messageId', message.id);
						lastMessage.setAttribute('data-senderId', message.senderId);
					}

					if (markNewMessage)
						BX.addClass(lastMessage, 'bx-messenger-content-item-new');

					messageId = message.id;
					skipAddMessage = true;
				}
			}
		}

		if (!skipAddMessage)
		{
			if (lastMessage)
				messageId = lastMessage.getAttribute('data-messageId');

			if (system)
			{
				var lastSystemElement = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageId': ''+message.id+''}}, false);
				if (!lastSystemElement)
				{
					var arMessage = BX.create("div", { attrs : { 'data-type': 'system', 'data-senderId' : message.senderId, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item bx-messenger-content-item-system"}, children: [
						BX.create("span", { props : { className : "bx-messenger-content-item-content"}, children : [
							typeof(messageUser) == 'undefined'? []:
							BX.create("span", { props : { className : "bx-messenger-content-item-avatar"}, children : [
								BX.create("span", { props : { className : "bx-messenger-content-item-arrow"}}),
								BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : messageUser.avatar, style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: '')}})
							]}),
							BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
								BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, children: [
									BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")}, children: [
										BX.create("span", { props : { className : "bx-messenger-message"+(deleted?" bx-messenger-message-deleted": "")+(deleted || edited?" bx-messenger-message-edited": "")}, attrs: {'id' : 'im-message-'+message.id}, html: BX.MessengerCommon.prepareText(message.text, false, true, true)}),
										filesNode, attachNode
									]})
								]}),
								BX.create("span", {  props : { className : "bx-messenger-content-item-params"}, children: [
									BX.create("span", { props : { className : "bx-messenger-content-item-date"}, html: ' &nbsp; '+this.formatDate(message.date, this.getDateFormatType('MESSAGE'))}),
									!likeEnable? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')}, children: [
										BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, html: likeCount}),
										BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: BX.message(!iLikeThis? 'IM_MESSAGE_LIKE':'IM_MESSAGE_DISLIKE')})
									]})
								]}),
								BX.create("span", { props : { className : "bx-messenger-clear"}})
							]})
						]})
					]});

					if (message.system && message.system == 'Y' && markNewMessage)
						BX.addClass(arMessage, 'bx-messenger-content-item-new');
				}
			}
			else if (message.senderId == this.BXIM.userId)
			{
				var arMessage = BX.create("div", { attrs : { 'data-type': 'self', 'data-senderId' : message.senderId, 'data-messageDate' : message.date, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item"}, children: [
					BX.create("span", { props : { className : "bx-messenger-content-item-content"}, children : [
						BX.create("span", { props : { className : "bx-messenger-content-item-avatar"}, children : [
							BX.create("span", { props : { className : "bx-messenger-content-item-arrow"}}),
							BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : messageUser.avatar, style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: '')}})
						]}),
						retry? (
							BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children:[
								BX.create("span", { attrs: { title: BX.message('IM_M_RETRY'), 'data-messageid': message.id, 'data-chat': parseInt(message.recipientId) > 0? 'Y':'N' }, props : { className : "bx-messenger-content-item-error"}, children:[
									BX.create("span", { props : { className : "bx-messenger-content-item-error-icon"}})
								]})
							]})
						):(
							BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children: temp?[
								BX.create("span", { props : { className : "bx-messenger-content-item-progress"}})
							]: []})
						),
						BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
							BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, children: [
								BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")}, children: [
									BX.create("span", { attrs: {title : BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"}}),
									BX.create("span", { props : { className : "bx-messenger-message"+(deleted?" bx-messenger-message-deleted": " ")+(deleted || edited?" bx-messenger-message-edited": "")}, attrs: {'id' : 'im-message-'+message.id}, html: BX.MessengerCommon.prepareText(message.text, false, true, true)}),
									filesNode, attachNode
								]})
							]}),
							BX.create("span", {  props : { className : "bx-messenger-content-item-params"}, children: [
								BX.create("span", { props : { className : "bx-messenger-content-item-date"}, html: (retry? BX.message('IM_M_NOT_DELIVERED') : temp? BX.message('IM_M_DELIVERED'): ' &nbsp; '+this.formatDate(message.date, this.getDateFormatType('MESSAGE')))}),
								!likeEnable? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')}, children: [
									BX.create("span", {  attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, html: likeCount}),
									BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: BX.message(!iLikeThis? 'IM_MESSAGE_LIKE':'IM_MESSAGE_DISLIKE')})
								]})
							]}),
							BX.create("span", { props : { className : "bx-messenger-clear"}})
						]})
					]})
				]});
			}
			else
			{
				var arMessage = BX.create("div", { attrs : { 'data-type': 'other', 'data-senderId' : message.senderId, 'data-messageDate' : message.date, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props: { className : "bx-messenger-content-item bx-messenger-content-item-2"+(markNewMessage? ' bx-messenger-content-item-new': '')}, children: [
					BX.create("span", { props : { className : "bx-messenger-content-item-content"}, children : [
						BX.create("span", { attrs: {title: (isChat? BX.util.htmlspecialcharsback(messageUser.name): '')}, props : { className : "bx-messenger-content-item-avatar bx-messenger-content-item-avatar-button"}, children : [
							BX.create("span", { props : { className : "bx-messenger-content-item-arrow"}}),
							BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(messageUser.avatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : messageUser.avatar, style: (this.isBlankAvatar(messageUser.avatar)? 'background-color: '+messageUser.color: '')}})
						]}),
						BX.create("span", { props : { className : "bx-messenger-content-item-status"}, children:[]}),
						BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
							BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, children: [
								BX.create("span", {  props : { className : "bx-messenger-content-item-text-wrap"+(appendTop? " bx-messenger-content-item-text-wrap-append": "")}, children: [
									BX.create("span", { attrs: {title : BX.message('IM_M_OPEN_EXTRA_TITLE').replace('#SHORTCUT#', BX.browser.IsMac()?'CMD':'CTRL')}, props : { className : "bx-messenger-content-item-menu"}}),
									BX.create("span", { props : { className : "bx-messenger-message"+(deleted?" bx-messenger-message-deleted": " ")+(deleted || edited?" bx-messenger-message-edited": "")}, attrs: {'id' : 'im-message-'+message.id}, html: BX.MessengerCommon.prepareText(message.text, false, true, true, (!this.BXIM.messenger.openChatFlag || message.senderId == this.BXIM.userId? false: (this.BXIM.messenger.users[this.BXIM.userId].name)))}),
									filesNode, attachNode
								]})
							]}),
							BX.create("span", {  props : { className : "bx-messenger-content-item-params"}, children: [
								BX.create("span", { props : { className : "bx-messenger-content-item-date"}, html: (temp? BX.message('IM_M_DELIVERED'): ' &nbsp; '+this.formatDate(message.date, this.getDateFormatType('MESSAGE')))}),
								!likeEnable? null: BX.create("span", { props : { className : "bx-messenger-content-item-like"+(iLikeThis? ' bx-messenger-content-item-liked':'')}, children: [
									BX.create("span", { attrs : {title: likeCount>0? BX.message('IM_MESSAGE_LIKE_LIST'):''}, props : { className : "bx-messenger-content-like-digit"+(likeCount<=0?' bx-messenger-content-like-digit-off':'')}, html: likeCount}),
									BX.create("span", { attrs : {'data-messageId': message.id}, props : { className : "bx-messenger-content-like-button"}, html: BX.message(!iLikeThis? 'IM_MESSAGE_LIKE':'IM_MESSAGE_DISLIKE')})
								]})
							]}),
							BX.create("span", { props : { className : "bx-messenger-clear"}})
						]})
					]})
				]});
			}
		}
		else if (addBlankNode)
		{
			arMessage = BX.create("div", {attrs : {'id' : 'im-message-'+message.id, 'data-messageDate' : message.date, 'data-messageId' : message.id, 'data-blockmessageid' : message.id }, props : { className : "bx-messenger-content-item-text-wrap bx-messenger-item-skipped"}});
		}

		if (arMessage && (!skipAddMessage || addBlankNode))
		{
			if (appendTop)
				this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(arMessage, lastMessage);
			else if (insertBefore && lastMessage.nextElementSibling)
				this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(arMessage, lastMessage.nextElementSibling);
			else
				this.BXIM.messenger.popupMessengerBodyWrap.appendChild(arMessage);
		}

		if (!addBlankNode && BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight, scroll))
		{
			if (this.BXIM.animationSupport)
			{
				if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
					this.BXIM.messenger.popupMessengerBodyAnimation.stop();
				(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
					duration : 800,
					start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop },
					finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
					step : BX.delegate(function(state){
						this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
					}, this)
				})).animate();
			}
			else
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
		}

		return messageId;
	};

	BX.MessengerCommon.prototype.drawProgessMessage = function(messageId, button)
	{
		var element = BX('im-message-'+messageId);
		if (!element)
			return false;

		BX.addClass(element.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress');
		element.parentNode.parentNode.parentNode.previousSibling.innerHTML = '';

		if (typeof (button) == 'object' || button === true)
		{
			if (this.BXIM.messenger.message[messageId])
			{
				this.BXIM.messenger.errorMessage[this.BXIM.messenger.currentTab] = true;
				BX.addClass(element.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
				button.chat = button.chat? button.chat: (parseInt(this.BXIM.messenger.message[messageId].recipientId) > 0? 'Y':'N');
				BX.adjust(element.parentNode.parentNode.parentNode.previousSibling, {children: [
					BX.create("span", { attrs: { title: button.title? button.title: '', 'data-messageid': messageId, 'data-chat': button.chat }, props : { className : "bx-messenger-content-item-error"}, children:[
						BX.create("span", { props : { className : "bx-messenger-content-item-error-icon"}})
					]})
				]});
			}
			else
			{
				BX.removeClass(element.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress');
				BX.removeClass(element.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
			}
		}
		else
		{
			BX.adjust(element.parentNode.parentNode.parentNode.previousSibling, {children: [
				BX.create("span", { props : { className : "bx-messenger-content-item-progress"}})
			]});
		}

		return true;
	}

	BX.MessengerCommon.prototype.clearProgessMessage = function(messageId)
	{
		var element = BX('im-message-'+messageId);
		if (!element)
			return false;

		BX.removeClass(element.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress');
		BX.removeClass(element.parentNode.parentNode.parentNode.parentNode, 'bx-messenger-content-item-content-progress-error');
		element.parentNode.parentNode.parentNode.previousSibling.innerHTML = '';

		return true;
	}



	/* Section: Writing status */
	BX.MessengerCommon.prototype.startWriting = function(userId, dialogId)
	{
		if (dialogId == this.BXIM.userId)
		{
			this.BXIM.messenger.writingList[userId] = true;
			this.drawWriting(userId);

			clearTimeout(this.BXIM.messenger.writingListTimeout[userId]);
			this.BXIM.messenger.writingListTimeout[userId] = setTimeout(BX.delegate(function(){
				this.endWriting(userId);
			}, this), 29500);
		}
		else
		{
			if (!this.BXIM.messenger.writingList[dialogId])
				this.BXIM.messenger.writingList[dialogId] = {};

			if (!this.BXIM.messenger.writingListTimeout[dialogId])
				this.BXIM.messenger.writingListTimeout[dialogId] = {};

			this.BXIM.messenger.writingList[dialogId][userId] = true;
			this.drawWriting(userId, dialogId);

			clearTimeout(this.BXIM.messenger.writingListTimeout[dialogId][userId]);
			this.BXIM.messenger.writingListTimeout[dialogId][userId] = setTimeout(BX.delegate(function(){
				this.endWriting(userId, dialogId);
			}, this), 29500);
		}
	};

	BX.MessengerCommon.prototype.drawWriting = function(userId, dialogId)
	{
		if (userId == this.BXIM.userId)
			return false;

		if (this.BXIM.messenger.popupMessenger != null && this.MobileActionEqual('RECENT', 'DIALOG'))
		{
			if (this.BXIM.messenger.writingList[userId] || dialogId && this.countWriting(dialogId) > 0)
			{

				var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.addClass(elements[i], 'bx-messenger-cl-status-writing');
				}
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.addClass(elements[i], 'bx-messenger-cl-status-writing');
				}

				if (this.MobileActionEqual('DIALOG') && (this.BXIM.messenger.currentTab == userId || dialogId && this.BXIM.messenger.currentTab == dialogId))
				{
					if (dialogId)
					{
						var userList = [];
						for (var i in this.BXIM.messenger.writingList[dialogId])
						{
							if (this.BXIM.messenger.writingList[dialogId].hasOwnProperty(i) && this.BXIM.messenger.users[i])
							{
								userList.push(this.BXIM.messenger.users[i].name);
							}
						}
						this.drawNotifyMessage(dialogId, 'writing', BX.message('IM_M_WRITING').replace('#USER_NAME#', userList.join(', ')));
					}
					else
					{
						if (!this.isMobile())
						{
							this.BXIM.messenger.popupMessengerPanelAvatar.parentNode.className = 'bx-messenger-panel-avatar bx-messenger-panel-avatar-status-writing';
						}
						this.drawNotifyMessage(userId, 'writing', BX.message('IM_M_WRITING').replace('#USER_NAME#', this.BXIM.messenger.users[userId].name));
					}
				}

			}
			else if (!this.BXIM.messenger.writingList[userId] || dialogId && this.countWriting(dialogId) == 0)
			{
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.removeClass(elements[i], 'bx-messenger-cl-status-writing');
				}
				var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+(dialogId? dialogId: userId));
				if (elements)
				{
					for (var i = 0; i < elements.length; i++)
						BX.removeClass(elements[i], 'bx-messenger-cl-status-writing');
				}

				if (this.MobileActionEqual('DIALOG') && (this.BXIM.messenger.currentTab == userId || this.BXIM.messenger.currentTab == dialogId))
				{
					if (!dialogId)
					{
						if (!this.isMobile())
							this.BXIM.messenger.popupMessengerPanelAvatar.parentNode.className = 'bx-messenger-panel-avatar bx-messenger-panel-avatar-status-' + this.getUserStatus(userId);
					}

					var lastMessage = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;
					if (lastMessage && BX.hasClass(lastMessage, "bx-messenger-content-item-notify"))
					{
						if (!dialogId && this.BXIM.messenger.readedList[userId])
						{
							this.drawReadMessage(userId, this.BXIM.messenger.readedList[userId].messageId, this.BXIM.messenger.readedList[userId].date, false);
						}
						else if (BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight)) // TODO mobile
						{
							if (this.BXIM.animationSupport)
							{
								if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
									this.BXIM.messenger.popupMessengerBodyAnimation.stop();
								(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
									duration : 800,
									start : {scroll : this.BXIM.messenger.popupMessengerBody.scrollTop},
									finish : {scroll : this.BXIM.messenger.popupMessengerBody.scrollTop - lastMessage.offsetHeight},
									transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
									step : BX.delegate(function (state)
									{
										this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
									}, this),
									complete : BX.delegate(function ()
									{
										BX.remove(lastMessage);
									}, this)
								})).animate();
							}
							else
							{
								this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollTop - lastMessage.offsetHeight;
								BX.remove(lastMessage);
							}
						}
						else
						{
							this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollTop - lastMessage.offsetHeight;
							BX.remove(lastMessage);
						}
					}
				}
			}
		}
	};

	BX.MessengerCommon.prototype.endWriting = function(userId, dialogId)
	{
		if (dialogId)
		{
			if (this.BXIM.messenger.writingListTimeout[dialogId] && this.BXIM.messenger.writingListTimeout[dialogId][userId])
				clearTimeout(this.BXIM.messenger.writingListTimeout[dialogId][userId]);

			if (this.BXIM.messenger.writingList[dialogId] && this.BXIM.messenger.writingList[dialogId][userId])
				delete this.BXIM.messenger.writingList[dialogId][userId];
		}
		else
		{
			clearTimeout(this.BXIM.messenger.writingListTimeout[userId]);
			delete this.BXIM.messenger.writingList[userId];
		}
		this.drawWriting(userId, dialogId);
	};

	BX.MessengerCommon.prototype.sendWriting = function(dialogId)
	{
		if (!this.BXIM.ppServerStatus || dialogId == 'create')
			return false;

		if (!this.BXIM.messenger.writingSendList[dialogId])
		{
			clearTimeout(this.BXIM.messenger.writingSendListTimeout[dialogId]);
			this.BXIM.messenger.writingSendList[dialogId] = true;
			BX.ajax({
				url: this.BXIM.pathToAjax+'?START_WRITING&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_START_WRITING' : 'Y', 'DIALOG_ID' : dialogId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data)
				{
					if (data && data.BITRIX_SESSID)
					{
						BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					}
					if (data.ERROR == 'AUTHORIZE_ERROR' && this.BXIM.desktop.ready() && this.BXIM.messenger.sendAjaxTry < 3)
					{
						this.BXIM.messenger.sendAjaxTry++;
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
					else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else
					{
						if (data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'SESSION_ERROR')
						{
							BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						}
					}
				}, this)
			});
			this.BXIM.messenger.writingSendListTimeout[dialogId] = setTimeout(BX.delegate(function(){
				this.endSendWriting(dialogId);
			}, this), 30000);
		}
	};

	BX.MessengerCommon.prototype.endSendWriting = function(dialogId)
	{
		clearTimeout(this.BXIM.messenger.writingSendListTimeout[dialogId]);
		this.BXIM.messenger.writingSendList[dialogId] = false;
	};

	BX.MessengerCommon.prototype.countWriting = function(dialogId)
	{
		var count = 0;
		if (this.BXIM.messenger.writingList[dialogId])
		{
			if (typeof(this.BXIM.messenger.writingList[dialogId]) == 'object')
			{
				for(var i in this.BXIM.messenger.writingList[dialogId])
				{
					if(this.BXIM.messenger.writingList[dialogId].hasOwnProperty(i))
					{
						count++;
					}
				}
			}
			else
			{
				count = 1;
			}
		}

		return count;
	}



	/* Section: Chats */
	BX.MessengerCommon.prototype.leaveFromChat = function(chatId, sendAjax)
	{
		if (!this.BXIM.messenger.chat[chatId])
			return false;

		sendAjax = sendAjax != false;

		if (!sendAjax)
		{
			if (this.BXIM.messenger.chat[chatId].type != 'open' || this.BXIM.messenger.users[this.BXIM.userId].extranet)
			{
				delete this.BXIM.messenger.chat[chatId];
				delete this.BXIM.messenger.userInChat[chatId];
				delete this.BXIM.messenger.unreadMessage[chatId];

				if (this.BXIM.messenger.popupMessenger != null)
				{
					if (this.BXIM.messenger.currentTab == 'chat'+chatId)
					{
						this.BXIM.messenger.currentTab = 0;
						this.BXIM.messenger.openChatFlag = false;
						this.BXIM.messenger.openCallFlag = false;
						this.BXIM.messenger.extraClose();
					}
				}
			}
			else
			{
				for(var i = 0; i < this.BXIM.messenger.userInChat[chatId].length; i++)
				{
					if (this.BXIM.userId == parseInt(this.BXIM.messenger.userInChat[chatId][i]))
					{
						delete this.BXIM.messenger.userInChat[chatId][i];
						break;
					}
				}
				this.BXIM.messenger.dialogStatusRedraw();
				delete this.BXIM.messenger.unreadMessage[chatId];
			}
			BX.MessengerCommon.userListRedraw();
		}
		else
		{
			BX.ajax({
				url: this.BXIM.pathToAjax+'?CHAT_LEAVE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_CHAT_LEAVE' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (data.ERROR == '')
					{
						this.readMessage('chat'+data.CHAT_ID, true, false);

						if (this.BXIM.messenger.chat[chatId].type != 'open')
						{
							delete this.BXIM.messenger.userInChat[data.CHAT_ID];
							delete this.BXIM.messenger.unreadMessage[data.CHAT_ID];
							delete this.BXIM.messenger.chat[data.CHAT_ID];

							if (this.BXIM.messenger.popupMessenger != null)
							{
								if (this.BXIM.messenger.currentTab == 'chat' + data.CHAT_ID)
								{
									this.BXIM.messenger.currentTab = 0;
									this.BXIM.messenger.openChatFlag = false;
									this.BXIM.messenger.openCallFlag = false;
									BX.localStorage.set('mct', this.BXIM.messenger.currentTab, 15);
									this.BXIM.messenger.extraClose();
								}
							}
						}
						else
						{
							for(var i = 0; i < this.BXIM.messenger.userInChat[chatId].length; i++)
							{
								if (this.BXIM.userId == parseInt(this.BXIM.messenger.userInChat[chatId][i]))
								{
									delete this.BXIM.messenger.userInChat[chatId][i];
									break;
								}
							}
							delete this.BXIM.messenger.unreadMessage[data.CHAT_ID];
							this.BXIM.messenger.dialogStatusRedraw();
						}

						BX.MessengerCommon.userListRedraw();
						BX.localStorage.set('mcl', data.CHAT_ID, 5);
					}
				}, this)
			});
		}
	};



	/* Section: Pull Events */
	BX.MessengerCommon.prototype.pullEvent = function()
	{
		BX.addCustomEvent((this.isMobile()? "onPull-im": "onPullEvent-im"), BX.delegate(function(command,params)
		{
			if (this.isMobile())
			{
				params = command.params;
				command = command.command;
			}

			if (command == 'generalChatId')
			{
				this.BXIM.messenger.generalChatId = params.ID;
			}
			else if (command == 'generalChatAccess')
			{
				if (this.BXIM.messenger.canSendMessageGeneralChat && params.BLOCK)
				{
					if (this.MobileActionEqual('DIALOG'))
					{
						this.BXIM.messenger.canSendMessageGeneralChat = false;
						if (this.isMobile())
						{
							this.BXIM.messenger.dialogStatusRedrawDelay();
						}
						else
						{
							this.BXIM.messenger.redrawChatHeader({userRedraw: false});
						}
					}
				}
				else if (this.isMobile() && this.MobileActionEqual('DIALOG'))
				{
					console.log('NOTICE: Window reload, because CHANGE ALLOW OPTIONS for general chat ('+params.ALLOW+')');
					//BXMobileApp.UI.Page.reloadUnique();
					location.reload();
				}
				else if (this.BXIM.desktop && this.BXIM.desktop.run())
				{
					console.log('NOTICE: Window reload, because CHANGE ALLOW OPTIONS for general chat ('+params.ALLOW+')');
					BX.desktop.windowReload();
				}
			}
			else if (command == 'desktopOffline')
			{
				this.BXIM.desktopStatus = false;
			}
			else if (command == 'desktopOnline')
			{
				this.BXIM.desktopStatus = true;
			}
			else if (command == 'readMessage')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.readMessage(params.userId, false, false);
			}
			else if (command == 'readMessageChat')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.readMessage('chat'+params.chatId, false, false);
			}
			else if (command == 'readMessageApponent')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				params.date = parseInt(params.date)+parseInt(BX.message('USER_TZ_OFFSET'));
				this.drawReadMessage(params.userId, params.lastId, params.date);
			}
			else if (command == 'startWriting')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				this.startWriting(params.senderId, params.dialogId);
			}
			else if (command == 'message' || command == 'messageChat')
			{
				if (this.MobileActionNotEqual('RECENT', 'DIALOG'))
					return false;

				if (this.BXIM.lastRecordId >= params.MESSAGE.id)
					return false;

				var data = {};
				data.MESSAGE = {};
				data.USERS_MESSAGE = {};
				params.MESSAGE.date = parseInt(params.MESSAGE.date)+parseInt(BX.message('USER_TZ_OFFSET'));
				for (var i in params.CHAT)
				{
					if (this.BXIM.messenger.chat[i] && this.BXIM.messenger.chat[i].fake)
						params.CHAT[i].fake = true;
					else if (!this.BXIM.messenger.chat[i])
						params.CHAT[i].fake = true;

					this.BXIM.messenger.chat[i] = params.CHAT[i];
				}
				for (var i in params.USER_IN_CHAT)
				{
					this.BXIM.messenger.userInChat[i] = params.USER_IN_CHAT[i];
				}
				for (var i in params.USER_BLOCK_CHAT)
				{
					this.BXIM.messenger.userChatBlockStatus[i] = params.USER_BLOCK_CHAT[i];
				}
				var userChangeStatus = {};
				for (var i in params.USERS)
				{
					if (this.BXIM.messenger.users[i] && this.BXIM.messenger.users[i].status != params.USERS[i].status && parseInt(params.MESSAGE.date)+180 > BX.MessengerCommon.getNowDate())
					{
						userChangeStatus[i] = this.BXIM.messenger.users[i].status;
						this.BXIM.messenger.users[i].status = params.USERS[i].status;
					}
				}
				if (this.MobileActionEqual('RECENT'))
				{
					for (var i in userChangeStatus)
					{
						if (!this.BXIM.messenger.users[i])
							continue;

						var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+i);
						if (elements != null)
						{
							for (var j = 0; j < elements.length; j++)
							{
								BX.removeClass(elements[j], 'bx-messenger-cl-status-' + userChangeStatus[i]);
								BX.addClass(elements[j], 'bx-messenger-cl-status-' + BX.MessengerCommon.getUserStatus(i));
								elements[j].setAttribute('data-status', BX.MessengerCommon.getUserStatus(i));
							}
						}
						var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+i);
						if (elements != null)
						{
							for (var j = 0; j < elements.length; j++)
							{
								BX.removeClass(elements[j], 'bx-messenger-cl-status-' + userChangeStatus[i]);
								BX.addClass(elements[j], 'bx-messenger-cl-status-' + BX.MessengerCommon.getUserStatus(i));
								elements[j].setAttribute('data-status', BX.MessengerCommon.getUserStatus(i));
							}
						}
					}
				}
				elements = null;
				data.USERS = params.USERS;

				if (this.MobileActionEqual('DIALOG'))
				{
					for (var i in params.FILES)
					{
						if (!this.BXIM.disk.files[params.CHAT_ID])
							this.BXIM.disk.files[params.CHAT_ID] = {};
						if (this.BXIM.disk.files[params.CHAT_ID][i])
							continue;
						params.FILES[i].date = parseInt(params.FILES[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
						this.BXIM.disk.files[params.CHAT_ID][i] = params.FILES[i];
					}
				}

				data.MESSAGE[params.MESSAGE.id] = params.MESSAGE;

				this.BXIM.lastRecordId = params.MESSAGE.id;
				if (params.MESSAGE.senderId == this.BXIM.userId)
				{
					if (this.BXIM.messenger.sendMessageFlag > 0 || this.BXIM.messenger.message[params.MESSAGE.id])
						return;

					this.readMessage(params.MESSAGE.recipientId, false, false);

					data.USERS_MESSAGE[params.MESSAGE.recipientId] = [params.MESSAGE.id];
					this.updateStateVar(data);
					BX.MessengerCommon.recentListAdd({
						'userId': params.MESSAGE.recipientId,
						'id': params.MESSAGE.id,
						'date': parseInt(params.MESSAGE.date)+parseInt(BX.message("SERVER_TZ_OFFSET")),
						'recipientId': params.MESSAGE.recipientId,
						'senderId': params.MESSAGE.senderId,
						'text': params.MESSAGE.text,
						'params': params.MESSAGE.params
					}, true);
				}
				else
				{

					data.UNREAD_MESSAGE = {};
					data.UNREAD_MESSAGE[command == 'messageChat'? params.MESSAGE.recipientId: params.MESSAGE.senderId] = [params.MESSAGE.id];
					data.USERS_MESSAGE[command == 'messageChat'?params.MESSAGE.recipientId: params.MESSAGE.senderId] = [params.MESSAGE.id];

					if (command == 'message')
						this.endWriting(params.MESSAGE.senderId);
					else
						this.endWriting(params.MESSAGE.senderId, params.MESSAGE.recipientId);

					this.updateStateVar(data);

					BX.MessengerCommon.recentListAdd({
						'userId': command == 'messageChat'? params.MESSAGE.recipientId: params.MESSAGE.senderId,
						'id': params.MESSAGE.id,
						'date': parseInt(params.MESSAGE.date)+parseInt(BX.message("SERVER_TZ_OFFSET")),
						'recipientId': params.MESSAGE.recipientId,
						'senderId': params.MESSAGE.senderId,
						'text': params.MESSAGE.text,
						'params': params.MESSAGE.params
					}, true);
				}
				BX.localStorage.set('mfm', this.BXIM.messenger.flashMessage, 80);
			}
			else if (command == 'messageUpdate' || command == 'messageDelete')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (this.BXIM.messenger.message[params.id])
				{
					if (!this.BXIM.messenger.message[params.id].params)
						this.BXIM.messenger.message[params.id].params = {};

					var dialogId = 0;
					if (command == 'messageDelete')
					{
						params.message = BX.message('IM_M_DELETED');
						this.BXIM.messenger.message[params.id].params.IS_DELETED = 'Y';
					}
					else if (command == 'messageUpdate')
					{
						this.BXIM.messenger.message[params.id].params = params.params;
					}

					this.BXIM.messenger.message[params.id].text = params.text;

					if (params.type == 'private')
					{
						dialogId = params.fromUserId == this.BXIM.userId? params.toUserId: params.fromUserId;
						this.endWriting(dialogId);
					}
					else
					{
						dialogId = 'chat' + params.chatId;
						this.endWriting(params.senderId, dialogId);
					}

					this.recentListElementUpdate(dialogId, params.id, params.text);

					if (this.BXIM.messenger.currentTab == dialogId && BX('im-message-'+params.id))
					{
						var messageBox = BX('im-message-'+params.id);
						BX.addClass(messageBox, (command == 'messageDelete'? 'bx-messenger-message-edited bx-messenger-message-deleted': 'bx-messenger-message-edited'));

						messageBox.innerHTML = BX.MessengerCommon.prepareText(this.BXIM.messenger.message[params.id].text, false, true, true);

						if (command == 'messageUpdate')
						{
							if (params.params && params.params.ATTACH)
							{
								var attachNode = BX.MessengerCommon.drawAttach(this.BXIM.messenger.message[params.id].chatId, params.params.ATTACH);
								if (attachNode.length > 0)
								{
									if (BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
									{
										messageBox.nextElementSibling.innerHTML = '';
										BX.adjust(messageBox.nextElementSibling, {children: attachNode});
									}
									else
									{
										attachNode = BX.create("div", {props : {className : "bx-messenger-attach-box"}, children : attachNode});
										if (messageBox.nextElementSibling)
										{
											messageBox.parentNode.insertBefore(attachNode, messageBox.nextElementSibling);
										}
										else
										{
											messageBox.parentNode.appendChild(attachNode);
										}
									}
								}
							}
							else if (typeof(params.params) != 'undefined' && params.params == '')
							{
								if (BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
								{
									BX.remove(messageBox.nextElementSibling);
								}
							}
						}


						BX.addClass(messageBox, 'bx-messenger-message-edited-anim');
						if (messageBox.nextSibling && BX.hasClass(messageBox.nextSibling, 'bx-messenger-file-box'))
						{
							BX.addClass(messageBox.nextSibling, 'bx-messenger-file-box-with-message');
						}
						setTimeout(BX.delegate(function(){
							BX.removeClass(messageBox, 'bx-messenger-message-edited-anim');
						}, this), 1000);
					}

					if (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal)
						this.recentListRedraw();
				}
			}
			else if (command == 'messageParamsUpdate')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				if (!this.BXIM.messenger.message[params.id])
					return false;

				if (this.BXIM.messenger.message[params.id].params && this.BXIM.messenger.message[params.id].params.IS_DELETED == 'Y')
					return false;

				this.BXIM.messenger.message[params.id].params = params.params;

				if (params.type == 'private')
				{
					dialogId = params.fromUserId == this.BXIM.userId? params.toUserId: params.fromUserId;
				}
				else
				{
					dialogId = 'chat' + params.chatId;
				}

				var messageBox = BX('im-message-'+params.id);
				if (this.BXIM.messenger.currentTab == dialogId && messageBox)
				{
					if (params.params)
					{
						if (params.params.ATTACH)
						{
							var attachNode = BX.MessengerCommon.drawAttach(this.BXIM.messenger.message[params.id].chatId, params.params.ATTACH);
							if (attachNode.length > 0)
							{
								if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
								{
									messageBox.nextElementSibling.innerHTML = '';
									BX.adjust(messageBox.nextElementSibling, {children: attachNode});
								}
								else
								{
									attachNode = BX.create("div", {props : {className : "bx-messenger-attach-box"}, children : attachNode});
									if (messageBox.nextElementSibling)
									{
										messageBox.parentNode.insertBefore(attachNode, messageBox.nextElementSibling);
									}
									else
									{
										messageBox.parentNode.appendChild(attachNode);
									}
								}
							}
						}
						if (params.params.IS_EDITED == 'Y')
						{
							BX.addClass(messageBox, 'bx-messenger-message-edited');
						}
					}
					else if (typeof(params.params) != 'undefined' && params.params == '')
					{
						if (messageBox.nextElementSibling && BX.hasClass(messageBox.nextElementSibling, 'bx-messenger-attach-box'))
						{
							BX.remove(messageBox.nextElementSibling);
						}
					}

					BX.addClass(messageBox, 'bx-messenger-message-edited-anim');
					if (messageBox.nextSibling && BX.hasClass(messageBox.nextSibling, 'bx-messenger-file-box'))
					{
						BX.addClass(messageBox.nextSibling, 'bx-messenger-file-box-with-message');
					}
					setTimeout(BX.delegate(function(){
						BX.removeClass(messageBox, 'bx-messenger-message-edited-anim');
					}, this), 1000);
				}
			}
			else if (command == 'messageLike')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				var iLikeThis = BX.util.in_array(this.BXIM.userId, params.users);
				var likeCount = params.users.length > 0? params.users.length: '';

				if  (!this.BXIM.messenger.message[params.id])
				{
					return false;
				}

				if (typeof(this.BXIM.messenger.message[params.id].params) != 'object')
				{
					this.BXIM.messenger.message[params.id].params = {};
				}

				this.BXIM.messenger.message[params.id].params.LIKE = params.users;

				if (BX('im-message-'+params.id))
				{
					var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+params.id+''}}, false);
					if (element)
					{
						var elementLike = BX.findChildByClassName(element, "bx-messenger-content-item-like");
						if (elementLike)
						{
							var elementLikeDigit = BX.findChildByClassName(elementLike, "bx-messenger-content-like-digit", false);
							var elementLikeButton = BX.findChildByClassName(elementLike, "bx-messenger-content-like-button", false);

							if (iLikeThis)
							{
								elementLikeButton.innerHTML = BX.message('IM_MESSAGE_DISLIKE');
								BX.addClass(elementLike, 'bx-messenger-content-item-liked');
							}
							else
							{
								elementLikeButton.innerHTML = BX.message('IM_MESSAGE_LIKE');
								BX.removeClass(elementLike, 'bx-messenger-content-item-liked');
							}

							if (likeCount>0)
							{
								elementLikeDigit.setAttribute('title', BX.message('IM_MESSAGE_LIKE_LIST'));
								BX.removeClass(elementLikeDigit, 'bx-messenger-content-like-digit-off');
							}
							else
							{
								elementLikeDigit.setAttribute('title', '');
								BX.addClass(elementLikeDigit, 'bx-messenger-content-like-digit-off');
							}

							if (elementLikeDigit.innerHTML < likeCount)
							{
								BX.addClass(element.firstChild, 'bx-messenger-content-item-plus-like');
								setTimeout(function(){
									BX.removeClass(element.firstChild, 'bx-messenger-content-item-plus-like');
								}, 500);
							}
							elementLikeDigit.innerHTML = likeCount;
						}
					}
				}
			}
			else if (command == 'fileUpload')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				if (this.BXIM.disk.filesProgress[params.fileTmpId])
					return false;

				if (this.BXIM.disk.files[params.fileChatId] && this.BXIM.disk.files[params.fileChatId][params.fileId])
				{
					params.fileParams['preview'] = this.BXIM.disk.files[params.fileChatId][params.fileId]['preview'];
				}
				if (!this.BXIM.disk.files[params.fileChatId])
					this.BXIM.disk.files[params.fileChatId] = {};
				this.BXIM.disk.files[params.fileChatId][params.fileId] = params.fileParams;
				BX.MessengerCommon.diskRedrawFile(params.fileChatId, params.fileId);

				if (BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight))
				{
					if (this.BXIM.animationSupport)
					{
						if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
							this.BXIM.messenger.popupMessengerBodyAnimation.stop();
						(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
							duration : 800,
							start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop },
							finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
							transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
							step : BX.delegate(function(state){
								this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
							}, this)
						})).animate();
					}
					else
					{
						this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
					}
				}
			}
			else if (command == 'fileUnRegister')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				for (var id in params.files)
				{
					if (this.BXIM.disk.filesRegister[params.chatId])
					{
						delete this.BXIM.disk.filesRegister[params.chatId][params.files[id]];
					}
					if (this.BXIM.disk.files[params.chatId])
					{
						this.BXIM.disk.files[params.chatId][params.files[id]].status = 'error';
						BX.MessengerCommon.diskRedrawFile(params.chatId, params.files[id]);
					}
					delete this.BXIM.disk.filesProgress[id];
				}
				this.drawTab(this.getRecipientByChatId(params.chatId));
			}
			else if (command == 'fileDelete')
			{
				if (this.MobileActionNotEqual('DIALOG'))
					return false;

				delete this.BXIM.disk.files[params.chatId][params.fileId];

				this.drawTab(this.getRecipientByChatId(params.chatId));
			}
			else if (command == 'chatRename')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (this.BXIM.messenger.chat[params.chatId])
				{
					this.BXIM.messenger.chat[params.chatId].name = params.chatTitle;
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatAvatar')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;
				this.BXIM.messenger.updateChatAvatar(params.chatId, params.chatAvatar);
			}
			else if (command == 'chatChangeColor')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (this.BXIM.messenger.chat[params.chatId])
				{
					this.BXIM.messenger.chat[params.chatId].color = params.chatColor;
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatUserAdd')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				for (var i in params.users)
					this.BXIM.messenger.users[i] = params.users[i];

				if (!this.BXIM.messenger.chat[params.chatId])
				{
					this.BXIM.messenger.chat[params.chatId] = {'id': params.chatId, 'name': params.chatId, 'owner': params.chatOwner, 'extranet': params.chatExtranet, 'fake': true};
				}
				else
				{
					this.BXIM.messenger.chat[params.chatId].extranet = params.chatExtranet;
					if (this.BXIM.messenger.userInChat[params.chatId])
					{
						for (i = 0; i < params.newUsers.length; i++)
							this.BXIM.messenger.userInChat[params.chatId].push(params.newUsers[i]);
					}
					else
						this.BXIM.messenger.userInChat[params.chatId] = params.newUsers;

					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'chatUserLeave')
			{
				if (this.MobileActionNotEqual('DIALOG', 'RECENT'))
					return false;

				if (params.userId == this.BXIM.userId)
				{
					this.readMessage('chat'+params.chatId, true, false);
					this.leaveFromChat(params.chatId, false);
					if (params.message.length > 0)
						this.BXIM.openConfirm({title: BX.util.htmlspecialchars(params.chatTitle), message: params.message});
				}
				else if (this.MobileActionEqual('DIALOG'))
				{
					if (!this.BXIM.messenger.chat[params.chatId] || !this.BXIM.messenger.userInChat[params.chatId])
						return false;

					var newStack = [];
					for (var i = 0; i < this.BXIM.messenger.userInChat[params.chatId].length; i++)
						if (this.BXIM.messenger.userInChat[params.chatId][i] != params.userId)
							newStack.push(this.BXIM.messenger.userInChat[params.chatId][i]);

					this.BXIM.messenger.userInChat[params.chatId] = newStack;
					this.BXIM.messenger.redrawChatHeader();
				}
			}
			else if (command == 'massDeleteMessage')
			{
				if (this.BXIM.notify.skipMassDelete)
				{
					return true;
				}
				for (var i in params.MESSAGE)
				{
					if (params.MESSAGE[i] > 0)
					{
						delete this.BXIM.notify.notify[i];
						delete this.BXIM.notify.flashNotify[i];
						delete this.BXIM.notify.unreadNotify[notifyId];
					}
				}
				this.BXIM.notify.updateNotifyCount(false);
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.notifyOpen)
					this.BXIM.notify.openNotify(true);
			}
			else if (command == 'notify')
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				if (this.BXIM.lastRecordId >= params.id)
					return false;

				params.date = parseInt(params.date)+parseInt(BX.message('USER_TZ_OFFSET'));

				var data = {};
				data.UNREAD_NOTIFY = {};
				data.UNREAD_NOTIFY[params.id] = [params.id];
				this.BXIM.messenger.notify.notify[params.id] = params;
				this.BXIM.messenger.notify.flashNotify[params.id] = params.silent != 'Y';

				if (params.settingName == "im|like" && params.original_tag.substr(0,10) == "RATING|IM|")
				{
					var messageParams = params.original_tag.split("|");
					if (this.BXIM.messenger.message[messageParams[4]] && this.BXIM.messenger.message[messageParams[4]].recipientId == this.BXIM.messenger.currentTab && this.BXIM.windowFocus)
					{
						delete data.UNREAD_NOTIFY[params.id];
						this.BXIM.notify.flashNotify[params.id] = false;
						this.BXIM.notify.viewNotify(params.id);
					}
				}

				if (params.silent == 'N')
					this.BXIM.notify.changeUnreadNotify(data.UNREAD_NOTIFY);

				BX.localStorage.set('mfn', this.BXIM.notify.flashNotify, 80);
				this.BXIM.lastRecordId = params.id;
			}
			else if (command == 'readNotify')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				this.BXIM.notify.initNotifyCount = 0;
				params.lastId = parseInt(params.lastId);
				for (var i in this.BXIM.notify.unreadNotify)
				{
					var notify = this.BXIM.notify.notify[this.BXIM.notify.unreadNotify[i]];
					if (notify && notify.type != 1 && notify.id <= params.lastId)
					{
						delete this.BXIM.notify.unreadNotify[i];
					}
				}
				this.BXIM.notify.updateNotifyCount(false);
			}
			else if (command == 'confirmNotify')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				var notifyId = parseInt(params.id);
				if (this.BXIM.notify.notify[notifyId])
				{
					if (this.isMobile())
					{
						delete this.BXIM.notify.notify[notifyId];
					}
					else
					{
						this.BXIM.notify.notify[notifyId].confirmMessages = params.messages;
					}
				}
				delete this.BXIM.notify.unreadNotify[notifyId];
				delete this.BXIM.notify.flashNotify[notifyId];
				this.BXIM.notify.updateNotifyCount(false);
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.notifyOpen)
					this.BXIM.notify.openNotify(true);
			}
			else if (command == 'readNotifyOne')  // TODO mobile
			{
				if (this.MobileActionNotEqual('NOTIFY'))
					return false;

				var notify = this.BXIM.notify.notify[params.id];
				if (notify && notify.type != 1)
					delete this.BXIM.notify.unreadNotify[params.id];

				this.BXIM.notify.updateNotifyCount(false);
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.notifyOpen)
					this.BXIM.notify.openNotify(true);

			}
		}, this));

		BX.addCustomEvent((this.isMobile()? "onPullOnline": "onPullOnlineEvent"), BX.delegate(function(command,params)
		{
			if (this.isMobile())
			{
				params = command.params;
				command = command.command;
			}
			if (command == 'user_online')
			{
				if (this.BXIM.messenger.users[params.USER_ID])
				{
					var contactListRedraw = false;

					if (typeof(this.BXIM.messenger.users[params.USER_ID].idle) == 'undefined')
					{
						this.BXIM.messenger.users[params.USER_ID].idle = 0;
					}
					if (this.BXIM.messenger.users[params.USER_ID].idle != 0)
					{
						this.BXIM.messenger.users[params.USER_ID].idle = 0;
						contactListRedraw = true;
					}

					if (typeof(params.STATUS) != 'undefined')
					{
						if (this.BXIM.messenger.users[params.USER_ID].status != params.STATUS)
						{
							if (!this.isMobile() && this.BXIM.messenger.users[params.USER_ID].status == "offline" && params.STATUS != 'offline')
							{
								if (this.BXIM.messenger.getTrackStatus(params.USER_ID))
								{
									var userParam = this.getUserParam(params.USER_ID);

									this.BXIM.messenger.showNotifyBlock({
										"senderId": params.USER_ID,
										"recipientId": this.BXIM.userId,
										"text": BX.message('IM_M_ST_ONLINE_'+(userParam.gender == 'F'? 'F': 'M')+(this.BXIM.bitrixIntranet? '_B24': ''))
									});
								}
							}

							this.BXIM.messenger.users[params.USER_ID].status = params.STATUS;
							contactListRedraw = true;
						}
					}
					if (typeof(params.MOBILE_LAST_DATE) != 'undefined')
					{
						if (this.BXIM.messenger.users[params.USER_ID].mobileLastDate != params.MOBILE_LAST_DATE)
						{
							this.BXIM.messenger.users[params.USER_ID].mobileLastDate = params.MOBILE_LAST_DATE;
							contactListRedraw = true;
						}
					}

					if (contactListRedraw)
					{
						this.BXIM.messenger.dialogStatusRedraw();
						this.userListRedraw();
					}
				}
			}
			else if (command == 'user_offline')
			{
				if (this.BXIM.messenger.users[params.USER_ID] && this.BXIM.messenger.users[params.USER_ID].status != 'offline')
				{
					this.BXIM.messenger.users[params.USER_ID].status = 'offline';
					this.BXIM.messenger.users[params.USER_ID].idle = 0;
					this.BXIM.messenger.users[params.USER_ID].mobileLastDate = 0;
					this.BXIM.messenger.dialogStatusRedraw();
					BX.MessengerCommon.userListRedraw();
				}
			}
			else if (command == 'user_status')
			{
				if (this.BXIM.messenger.users[params.USER_ID])
				{
					var contactListRedraw = false;
					if (typeof(params.IDLE) != 'undefined')
					{
						if (typeof(this.BXIM.messenger.users[params.USER_ID].idle) == 'undefined')
						{
							this.BXIM.messenger.users[params.USER_ID].idle = 0;
						}
						if (this.BXIM.messenger.users[params.USER_ID].idle != params.IDLE)
						{
							this.BXIM.messenger.users[params.USER_ID].idle = params.IDLE;
							contactListRedraw = true;
						}
					}
					if (typeof(params.MOBILE_LAST_DATE) != 'undefined')
					{
						if (typeof(this.BXIM.messenger.users[params.USER_ID].mobileLastDate) == 'undefined')
						{
							this.BXIM.messenger.users[params.USER_ID].mobileLastDate = 0;
						}
						if (this.BXIM.messenger.users[params.USER_ID].mobileLastDate != params.MOBILE_LAST_DATE)
						{
							this.BXIM.messenger.users[params.USER_ID].mobileLastDate = params.MOBILE_LAST_DATE;
							contactListRedraw = true;
						}
					}
					if (typeof(params.STATUS) != 'undefined')
					{
						if (this.BXIM.messenger.users[params.USER_ID].status != params.STATUS)
						{
							this.BXIM.messenger.users[params.USER_ID].status = params.STATUS;
							contactListRedraw = true;
						}
					}
					if (typeof(params.COLOR) != 'undefined')
					{
						if (this.BXIM.messenger.users[params.USER_ID] && this.BXIM.messenger.users[params.USER_ID].color != params.COLOR && params.COLOR != "")
						{
							this.BXIM.messenger.users[params.USER_ID].color = params.COLOR;
							contactListRedraw = true;
						}
					}
					if (contactListRedraw)
					{
						this.BXIM.messenger.dialogStatusRedraw();
						BX.MessengerCommon.userListRedraw();
					}
				}
			}
			else if (command == 'online_list')
			{
				var contactListRedraw = false;
				for (var i in this.BXIM.messenger.users)
				{
					if (typeof(params.USERS[i]) == 'undefined')
					{
						if (this.BXIM.messenger.users[i].status != 'offline')
						{
							this.BXIM.messenger.users[i].status = 'offline';
							this.BXIM.messenger.users[i].idle = 0;
							this.BXIM.messenger.users[i].mobileLastDate = 0;
							contactListRedraw = true;
						}
					}
					else
					{
						if (typeof(params.USERS[i].idle) != 'undefined')
						{
							if (typeof(this.BXIM.messenger.users[i].idle) == 'undefined')
							{
								this.BXIM.messenger.users[i].idle = 0;
							}
							if (this.BXIM.messenger.users[i].idle != params.USERS[i].idle)
							{
								this.BXIM.messenger.users[i].idle = params.USERS[i].idle;
								contactListRedraw = true;
							}
						}
						if (typeof(params.USERS[i].mobileLastDate) != 'undefined')
						{
							if (typeof(this.BXIM.messenger.users[i].mobileLastDate) == 'undefined')
							{
								this.BXIM.messenger.users[i].mobileLastDate = 0;
							}
							if (this.BXIM.messenger.users[i].mobileLastDate != params.USERS[i].mobileLastDate)
							{
								this.BXIM.messenger.users[i].mobileLastDate = params.USERS[i].mobileLastDate;
								contactListRedraw = true;
							}
						}
						if (typeof(params.USERS[i].status) != 'undefined')
						{
							if (this.BXIM.messenger.users[i].status != params.USERS[i].status)
							{
								this.BXIM.messenger.users[i].status = params.USERS[i].status;
								contactListRedraw = true;
							}
						}
					}
				}
				if (contactListRedraw)
				{
					this.BXIM.messenger.dialogStatusRedraw();
					BX.MessengerCommon.userListRedraw();
				}
			}

		}, this));
	}


	/* Section: Fetch messages */
	BX.MessengerCommon.prototype.updateStateVar = function(data, send, writeMessage)
	{
		writeMessage = writeMessage !== false;
		if (typeof(data.CHAT) != "undefined")
		{
			for (var i in data.CHAT)
			{
				if (this.BXIM.messenger.chat[i] && this.BXIM.messenger.chat[i].fake)
					data.CHAT[i].fake = true;
				else if (!this.BXIM.messenger.chat[i])
					data.CHAT[i].fake = true;

				this.BXIM.messenger.chat[i] = data.CHAT[i];
			}
		}
		if (typeof(data.USER_IN_CHAT) != "undefined")
		{
			for (var i in data.USER_IN_CHAT)
			{
				this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
			}
		}
		if (typeof(data.USER_BLOCK_CHAT) != "undefined")
		{
			for (var i in data.USER_BLOCK_CHAT)
			{
				this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
			}
		}
		if (typeof(data.USERS) != "undefined")
		{
			for (var i in data.USERS)
			{
				this.BXIM.messenger.users[i] = data.USERS[i];
			}
		}
		if (typeof(data.USER_IN_GROUP) != "undefined")
		{
			for (var i in data.USER_IN_GROUP)
			{
				if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined')
				{
					this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
				}
				else
				{
					for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
						this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

					this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
				}
			}
		}
		if (typeof(data.WO_USER_IN_GROUP) != "undefined")
		{
			for (var i in data.WO_USER_IN_GROUP)
			{
				if (typeof(this.BXIM.messenger.woUserInGroup[i]) == 'undefined')
				{
					this.BXIM.messenger.woUserInGroup[i] = data.WO_USER_IN_GROUP[i];
				}
				else
				{
					for (var j = 0; j < data.WO_USER_IN_GROUP[i].users.length; j++)
						this.BXIM.messenger.woUserInGroup[i].users.push(data.WO_USER_IN_GROUP[i].users[j]);

					this.BXIM.messenger.woUserInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.woUserInGroup[i].users)
				}
			}
		}
		if (typeof(data.MESSAGE) != "undefined")
		{
			for (var i in data.MESSAGE)
			{
				this.BXIM.messenger.message[i] = data.MESSAGE[i];
				this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
			}
		}

		this.changeUnreadMessage(data.UNREAD_MESSAGE, send);

		if (typeof(data.USERS_MESSAGE) != "undefined")
		{
			for (var i in data.USERS_MESSAGE)
			{
				data.USERS_MESSAGE[i].sort(BX.delegate(function(i, ii) {i = parseInt(i); ii = parseInt(ii); if (!this.BXIM.messenger.message[i] || !this.BXIM.messenger.message[ii]){return 0;} var i1 = parseInt(this.BXIM.messenger.message[i].date); var i2 = parseInt(this.BXIM.messenger.message[ii].date); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));
				if (!this.BXIM.messenger.showMessage[i])
					this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];

				for (var j = 0; j < data.USERS_MESSAGE[i].length; j++)
				{
					if (!BX.util.in_array(data.USERS_MESSAGE[i][j], this.BXIM.messenger.showMessage[i]))
					{
						this.BXIM.messenger.showMessage[i].push(data.USERS_MESSAGE[i][j]);
						if (this.BXIM.messenger.history[i])
							this.BXIM.messenger.history[i] = BX.util.array_merge(this.BXIM.messenger.history[i], data.USERS_MESSAGE[i]);
						else
							this.BXIM.messenger.history[i] = data.USERS_MESSAGE[i];

						if (writeMessage && this.BXIM.messenger.currentTab == i && this.MobileActionEqual('DIALOG'))
							this.drawMessage(i, this.BXIM.messenger.message[data.USERS_MESSAGE[i][j]]);
					}
				}
			}
		}
	};

	BX.MessengerCommon.prototype.changeUnreadMessage = function(unreadMessage, send)
	{
		send = send != false;

		var playSound = false;
		var contactListRedraw = false;
		var needRedrawDialogStatus = true;

		var userStatus = this.isMobile()? 'online': this.BXIM.settings.status;

		for (var i in unreadMessage)
		{
			if (i.toString().substr(0, 4) == 'chat')
			{
				if (!BX.MessengerCommon.userInChat(i.toString().substr(4)))
				{
					continue;
				}
			}

			var skipPopup = false;
			if (this.BXIM.xmppStatus && i.toString().substr(0,4) != 'chat')
			{
				if (!(this.BXIM.messenger.popupMessenger != null && this.BXIM.messenger.currentTab == i && this.BXIM.isFocus()))
				{
					contactListRedraw = true;
					if (this.BXIM.messenger.unreadMessage[i])
						this.BXIM.messenger.unreadMessage[i] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[i], unreadMessage[i]));
					else
						this.BXIM.messenger.unreadMessage[i] = unreadMessage[i];
				}
				skipPopup = true;
			}

			if (!skipPopup)
			{
				if (this.BXIM.messenger.popupMessenger != null && this.BXIM.messenger.currentTab == i && this.BXIM.isFocus())
				{
					if (typeof (this.BXIM.messenger.flashMessage[i]) == 'undefined')
						this.BXIM.messenger.flashMessage[i] = {};

					for (var k = 0; k < unreadMessage[i].length; k++)
					{
						if (this.BXIM.isFocus())
							this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;

						if (this.BXIM.messenger.message[unreadMessage[i][k]] && this.BXIM.messenger.message[unreadMessage[i][k]].senderId == this.BXIM.messenger.currentTab)
							playSound = true;
					}
					this.readMessage(i, true, true, true);
				}
				else if (this.isMobile() && this.BXIM.messenger.currentTab == i)
				{
					var dialogId = this.BXIM.messenger.currentTab;
					this.BXIM.isFocusMobile(BX.delegate(function(visible){
						if (visible)
						{
							BX.MessengerCommon.readMessage(dialogId, true, true, true);
						}
					},this));
					if (this.BXIM.messenger.unreadMessage[dialogId])
						this.BXIM.messenger.unreadMessage[dialogId] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[dialogId], unreadMessage[dialogId]));
					else
						this.BXIM.messenger.unreadMessage[dialogId] = unreadMessage[dialogId];
				}
				else
				{
					contactListRedraw = true;
					if (this.BXIM.messenger.unreadMessage[i])
						this.BXIM.messenger.unreadMessage[i] = BX.util.array_unique(BX.util.array_merge(this.BXIM.messenger.unreadMessage[i], unreadMessage[i]));
					else
						this.BXIM.messenger.unreadMessage[i] = unreadMessage[i];

					if (typeof (this.BXIM.messenger.flashMessage[i]) == 'undefined')
					{
						this.BXIM.messenger.flashMessage[i] = {};
						for (var k = 0; k < unreadMessage[i].length; k++)
						{
							var resultOfNameSearch = this.BXIM.messenger.message[unreadMessage[i][k]].text.match(new RegExp("("+this.BXIM.messenger.users[this.BXIM.userId].name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'));
							if (userStatus != 'dnd' || resultOfNameSearch)
							{
								this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = send;
							}
						}
					}
					else
					{
						for (var k = 0; k < unreadMessage[i].length; k++)
						{
							var resultOfNameSearch = this.BXIM.messenger.message[unreadMessage[i][k]].text.match(new RegExp("("+this.BXIM.messenger.users[this.BXIM.userId].name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")+")",'ig'));
							if (userStatus != 'dnd' || resultOfNameSearch)
							{
								if (!send && !this.BXIM.isFocus())
								{
									this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = false;
								}
								else
								{
									if (typeof (this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]]) == 'undefined')
										this.BXIM.messenger.flashMessage[i][unreadMessage[i][k]] = true;
								}
							}
						}
					}
				}
			}

			var arRecent = false;
			for (var k = 0; k < unreadMessage[i].length; k++)
			{
				if (!arRecent || arRecent.SEND_DATE <= parseInt(this.BXIM.messenger.message[unreadMessage[i][k]].date)+parseInt(BX.message("SERVER_TZ_OFFSET")))
				{
					arRecent = {
						'ID': this.BXIM.messenger.message[unreadMessage[i][k]].id,
						'SEND_DATE': parseInt(this.BXIM.messenger.message[unreadMessage[i][k]].date)+parseInt(BX.message("SERVER_TZ_OFFSET")),
						'RECIPIENT_ID': this.BXIM.messenger.message[unreadMessage[i][k]].recipientId,
						'SENDER_ID': this.BXIM.messenger.message[unreadMessage[i][k]].senderId,
						'USER_ID': this.BXIM.messenger.message[unreadMessage[i][k]].senderId,
						'SEND_MESSAGE': this.BXIM.messenger.message[unreadMessage[i][k]].text,
						'PARAMS': this.BXIM.messenger.message[unreadMessage[i][k]].params
					};
				}
			}
			if (arRecent)
			{
				BX.MessengerCommon.recentListAdd({
					'userId': arRecent.RECIPIENT_ID.toString().substr(0,4) == 'chat'? arRecent.RECIPIENT_ID: arRecent.USER_ID,
					'id': arRecent.ID,
					'date': arRecent.SEND_DATE,
					'recipientId': arRecent.RECIPIENT_ID,
					'senderId': arRecent.SENDER_ID,
					'text': arRecent.SEND_MESSAGE,
					'params': arRecent.PARAMS
				}, true);
			}
			if (this.MobileActionEqual('DIALOG') && this.BXIM.messenger.popupMessenger != null && this.BXIM.messenger.currentTab == i)
			{
				needRedrawDialogStatus = true;
			}
		}
		if (needRedrawDialogStatus)
		{
			this.BXIM.messenger.dialogStatusRedraw(this.isMobile()? {type: 1, slidingPanelRedrawDisable: true, 'userRedraw': false}: {'userRedraw': false});
		}

		if (this.MobileActionEqual('RECENT') && this.BXIM.messenger.popupMessenger != null && !this.BXIM.messenger.recentList && contactListRedraw)
			BX.MessengerCommon.userListRedraw();

		if (this.isMobile() && this.MobileActionEqual('RECENT') && app.enableInVersion(13))
		{
			clearTimeout(this.newMessageTimeout);
			this.newMessageTimeout = setTimeout(BX.proxy(function(){
				this.BXIM.messenger.newMessage();
			}, this), 1000);
		}
		else if (!this.isMobile())
		{
			this.BXIM.messenger.newMessage(send);
			this.BXIM.messenger.updateMessageCount(send);

			if (send && playSound && userStatus != 'dnd')
			{
				this.BXIM.playSound("newMessage2");
			}
		}
	}

	BX.MessengerCommon.prototype.redrawDateMarks = function()
	{
		if (!this.BXIM.messenger.popupMessengerBodyWrap)
			return false;

		if (typeof(this.BXIM.messenger.popupMessengerBodyWrap.getElementsByClassName) == 'undefined')
			return false;

		var element = {};
		var contentGroup = this.BXIM.messenger.popupMessengerBodyWrap.getElementsByClassName("bx-messenger-content-group");
		var marginTop = this.BXIM.messenger.popupMessengerBody.getBoundingClientRect().top;
		for (var i = 0; i < contentGroup.length; i++)
		{
			element = BX.MessengerCommon.isElementCoordsBelow(contentGroup[i], this.BXIM.messenger.popupMessengerBody, 33, true);
			if (contentGroup[i].className != "bx-messenger-content-group bx-messenger-content-group-today")
			{
				contentGroup[i].className = "bx-messenger-content-group "+(element.top? "": "bx-messenger-content-group-float");
				contentGroup[i].firstChild.nextSibling.style.marginLeft = element.top? "": Math.round(contentGroup[i].offsetWidth/2 - contentGroup[i].firstChild.nextSibling.offsetWidth/2)+'px';
				contentGroup[i].firstChild.nextSibling.style.marginTop = element.top? "": ((-element.coords.top)+14)+'px';
			}
			if (!element.top && contentGroup[i-1])
			{
				contentGroup[i-1].className = "bx-messenger-content-group";
				contentGroup[i-1].firstChild.nextSibling.style.marginLeft = '';
				contentGroup[i-1].firstChild.nextSibling.style.marginTop = '';
			}
		}
	}

	BX.MessengerCommon.prototype.unreadMessage = function(messageId) // TODO unreadMessage
	{
		if (!this.BXIM.messenger.message[messageId])
		{
			return false;
		}
		var message = this.BXIM.messenger.message[messageId];


		var dialogId = '';
		if (message.recipientId.toString().substr(0,4) == 'chat')
		{
			dialogId = message.recipientId;
		}
		else
		{
			dialogId = message.senderId;
		}
		showMessage = this.BXIM.messenger.showMessage[dialogId];
		showMessage.sort(function(i, ii) {if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}});

		var lastId = 0;
		this.BXIM.messenger.unreadMessage[dialogId] = [];
		for (var i = 0; i < showMessage.length; i++)
		{
			if (showMessage[i] >= messageId)
			{
				if (!this.BXIM.messenger.unreadMessage[dialogId])
					this.BXIM.messenger.unreadMessage[dialogId] = [];

				this.BXIM.messenger.unreadMessage[dialogId].push(showMessage[i]);
			}
			else
			{
				lastId = showMessage[i];
			}
		}

		this.skipReadMessage = true;

		this.drawTab();
		this.userListRedraw();

		setTimeout(BX.delegate(function(){
			this.skipReadMessage = false;
		},this), 1000);

		console.log('unread message - chat:', message.chatId, 'messageId:', messageId, 'lastId:', lastId);
	}

	BX.MessengerCommon.prototype.readMessage = function(userId, send, sendAjax, skipCheck)
	{
		if (!userId || this.skipReadMessage)
			return false;

		skipCheck = skipCheck == true;
		if (!skipCheck && (!this.BXIM.messenger.unreadMessage[userId] || this.BXIM.messenger.unreadMessage[userId].length <= 0))
			return false;

		send = send != false;
		sendAjax = sendAjax !== false;

		if (this.BXIM.messenger.recentListExternal)
		{
			var elements = BX.findChildrenByClassName(this.BXIM.messenger.recentListExternal, "bx-messenger-cl-id-"+userId);
			if (elements != null)
				for (var i = 0; i < elements.length; i++)
					elements[i].firstChild.innerHTML = '';
		}
		if (this.BXIM.messenger.popupMessenger != null)
		{

			var elements = BX.findChildrenByClassName(this.BXIM.messenger.popupContactListElementsWrap, "bx-messenger-cl-id-"+userId);
			if (elements != null)
				for (var i = 0; i < elements.length; i++)
					elements[i].firstChild.innerHTML = '';

			elements = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-new", false);
			if (elements != null)
				for (var i = 0; i < elements.length; i++)
					if (elements[i].getAttribute('data-notifyType') != 1)
						BX.removeClass(elements[i], 'bx-messenger-content-item-new');
		}
		var lastId = 0;
		if (Math && this.BXIM.messenger.unreadMessage[userId])
			lastId = Math.max.apply(Math, this.BXIM.messenger.unreadMessage[userId]);

		if (this.BXIM.messenger.unreadMessage[userId])
			delete this.BXIM.messenger.unreadMessage[userId];

		if (this.BXIM.messenger.flashMessage[userId])
			delete this.BXIM.messenger.flashMessage[userId];

		BX.localStorage.set('mfm', this.BXIM.messenger.flashMessage, 80);

		if (!this.isMobile())
		{
			this.BXIM.messenger.updateMessageCount(send);
		}

		if (sendAjax)
		{
			clearTimeout(this.BXIM.messenger.readMessageTimeout[userId+'_'+this.BXIM.messenger.currentTab]);
			this.BXIM.messenger.readMessageTimeout[userId+'_'+this.BXIM.messenger.currentTab] = setTimeout(BX.delegate(function(){
				var sendData = {'IM_READ_MESSAGE' : 'Y', 'USER_ID' : userId, 'TAB' : this.BXIM.messenger.currentTab, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
				if (parseInt(lastId) > 0)
					sendData['LAST_ID'] = lastId;
				var _ajax = BX.ajax({
					url: this.BXIM.pathToAjax+'?READ_MESSAGE&V='+this.BXIM.revision,
					method: 'POST',
					dataType: 'json',
					timeout: 60,
					skipAuthCheck: true,
					data: sendData,
					onsuccess: BX.delegate(function(data)
					{
						if (data && data.BITRIX_SESSID)
						{
							BX.message({'bitrix_sessid': data.BITRIX_SESSID});
						}
						if (data.ERROR != '')
						{
							if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
							{
								this.BXIM.messenger.sendAjaxTry++;
								setTimeout(BX.delegate(function(){
									this.readMessage(userId, false, true);
								}, this), 2000);
								BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
							}
							else if (data.ERROR == 'AUTHORIZE_ERROR')
							{
								this.BXIM.messenger.sendAjaxTry++;
								if (this.BXIM.desktop && this.BXIM.desktop.ready())
								{
									setTimeout(BX.delegate(function(){
										this.readMessage(userId, false, true);
									}, this), 10000);
								}
								BX.onCustomEvent(window, 'onImError', [data.ERROR]);
							}
						}
					}, this),
					onfailure: BX.delegate(function()	{
						this.BXIM.messenger.sendAjaxTry = 0;
						try {
							if (typeof(_ajax) == 'object' && _ajax.status == 0)
								BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
						}
						catch(e) {}
					}, this)
				});
			}, this), 200);
		}
		if (send)
		{
			BX.localStorage.set('mrm', userId, 5);
			BX.localStorage.set('mnnb', true, 1);
		}
	};

	BX.MessengerCommon.prototype.drawReadMessage = function(userId, messageId, date, animation)
	{
		var lastId = Math.max.apply(Math, this.BXIM.messenger.showMessage[userId]);
		if (lastId != messageId || this.BXIM.messenger.message[lastId].senderId == userId)
		{
			this.BXIM.messenger.readedList[userId] = false;
			return false;
		}

		this.BXIM.messenger.readedList[userId] = {
			'messageId' : messageId,
			'date' : date
		};
		if (!this.countWriting(userId))
		{
			animation = animation != false;

			this.drawNotifyMessage(userId, 'readed', BX.message('IM_M_READED').replace('#DATE#', this.formatDate(date)), animation);
		}
	};

	BX.MessengerCommon.prototype.drawNotifyMessage = function(userId, icon, message, animation)
	{
		if (this.BXIM.messenger.popupMessenger == null || userId != this.BXIM.messenger.currentTab || typeof(message) == 'undefined' || typeof(icon) == 'undefined' || userId == 0)
			return false;

		var lastChild = this.BXIM.messenger.popupMessengerBodyWrap.lastChild;
		if (!lastChild || BX.hasClass(lastChild, "bx-messenger-content-empty"))
			return false;

		var arMessage = BX.create("div", { attrs : { 'data-type': 'notify'}, props: { className : "bx-messenger-content-item bx-messenger-content-item-notify"}, children: [
			BX.create("span", { props : { className : "bx-messenger-content-item-content"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-item-text-center"}, children: [
					BX.create("span", {  props : { className : "bx-messenger-content-item-text-message"}, html: '<span class="bx-messenger-content-item-notify-icon-'+icon+'"></span>'+this.prepareText(message, false, true, true)})
				]})
			]})
		]});

		if (BX.hasClass(lastChild, "bx-messenger-content-item-notify"))
			BX.remove(lastChild);

		this.BXIM.messenger.popupMessengerBodyWrap.appendChild(arMessage);

		animation = animation != false;
		if (this.BXIM.messenger.popupMessengerBody && BX.MessengerCommon.enableScroll(this.BXIM.messenger.popupMessengerBody, this.BXIM.messenger.popupMessengerBody.offsetHeight))
		{
			if (this.BXIM.animationSupport && animation)
			{
				if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
					this.BXIM.messenger.popupMessengerBodyAnimation.stop();
				(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
					duration : 1200,
					start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop},
					finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
					step : BX.delegate(function(state){
						this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
					}, this)
				})).animate();
			}
			else
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
		}
	};

	BX.MessengerCommon.prototype.loadHistory = function(userId, isHistoryDialog)
	{
		isHistoryDialog = typeof(isHistoryDialog) == 'undefined'? true: isHistoryDialog;

		if (!this.BXIM.messenger.historyEndOfList[userId])
			this.BXIM.messenger.historyEndOfList[userId] = {};

		if (!this.BXIM.messenger.historyLoadFlag[userId])
			this.BXIM.messenger.historyLoadFlag[userId] = {};

		if (this.BXIM.messenger.historyLoadFlag[userId] && this.BXIM.messenger.historyLoadFlag[userId][isHistoryDialog])
		{
			if (this.isMobile())
				app.pullDownLoadingStop();
			return;
		}

		if (this.isMobile())
		{
			isHistoryDialog = false;
		}
		else
		{
			if (isHistoryDialog)
			{
				if (this.BXIM.messenger.historySearch != "" || this.BXIM.messenger.historyDateSearch != "")
					return;

				if (!(this.BXIM.messenger.popupHistoryItems.scrollTop > this.BXIM.messenger.popupHistoryItems.scrollHeight - this.BXIM.messenger.popupHistoryItems.offsetHeight - 100))
					return;
			}
			else
			{
				if (this.BXIM.messenger.popupMessengerBody.scrollTop >= 5)
					return;
			}
		}

		if (!this.BXIM.messenger.historyEndOfList[userId] || !this.BXIM.messenger.historyEndOfList[userId][isHistoryDialog])
		{
			var elements = [];
			if (isHistoryDialog)
			{
				elements = BX.findChildrenByClassName(this.BXIM.messenger.popupHistoryBodyWrap, "bx-messenger-history-item-text");
			}
			else
			{
				elements = BX.findChildrenByClassName(this.BXIM.messenger.popupMessengerBodyWrap, "bx-messenger-content-item-text-wrap");
			}

			if (!this.isMobile() && elements.length < 20)
			{
				return false;
			}

			if (elements.length > 0)
				this.BXIM.messenger.historyOpenPage[userId] = Math.floor(elements.length/20)+1;
			else
				this.BXIM.messenger.historyOpenPage[userId] = 1;

			var tmpLoadMoreWait = null;
			if (!this.isMobile())
			{
				tmpLoadMoreWait = BX.create("div", { props : { className : "bx-messenger-content-load-more-history" }, children : [
					BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
					BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_LOAD_MESSAGE')})
				]});
				if (isHistoryDialog)
				{
					this.BXIM.messenger.popupHistoryBodyWrap.appendChild(tmpLoadMoreWait);
				}
				else
				{
					this.BXIM.messenger.popupMessengerBodyWrap.insertBefore(tmpLoadMoreWait, this.BXIM.messenger.popupMessengerBodyWrap.firstChild);
				}
			}

			if (!this.BXIM.messenger.historyLoadFlag[userId])
				this.BXIM.messenger.historyLoadFlag[userId] = {};

			this.BXIM.messenger.historyLoadFlag[userId][isHistoryDialog] = true;

			BX.ajax({
				url: this.BXIM.pathToAjax+'?HISTORY_LOAD_MORE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_HISTORY_LOAD_MORE' : 'Y', 'USER_ID' : userId, 'PAGE_ID' : this.BXIM.messenger.historyOpenPage[userId], 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);

					if (this.isMobile())
						app.pullDownLoadingStop();

					this.BXIM.messenger.historyLoadFlag[userId][isHistoryDialog] = false;

					if (data.MESSAGE.length == 0)
					{
						this.BXIM.messenger.historyEndOfList[userId][isHistoryDialog] = true;
						return;
					}

					for (var i in data.FILES)
					{
						if (!this.BXIM.disk.files[data.CHAT_ID])
							this.BXIM.disk.files[data.CHAT_ID] = {};
						if (this.BXIM.disk.files[data.CHAT_ID][i])
							continue;
						data.FILES[i].date = parseInt(data.FILES[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
						this.BXIM.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					var countMessages = 0;
					for (var i in data.MESSAGE)
					{
						data.MESSAGE[i].date = parseInt(data.MESSAGE[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
						this.BXIM.messenger.message[i] = data.MESSAGE[i];

						countMessages++;
					}
					if (countMessages < 20)
					{
						this.BXIM.messenger.historyEndOfList[userId][isHistoryDialog] = true;
					}
					for (var i in data.USERS_MESSAGE)
					{
						if (isHistoryDialog)
						{
							if (this.BXIM.messenger.history[i])
								this.BXIM.messenger.history[i] = BX.util.array_merge(this.BXIM.messenger.history[i], data.USERS_MESSAGE[i]);
							else
								this.BXIM.messenger.history[i] = data.USERS_MESSAGE[i];
						}
						else
						{
							if (this.BXIM.messenger.showMessage[i])
								this.BXIM.messenger.showMessage[i] = BX.util.array_unique(BX.util.array_merge(data.USERS_MESSAGE[i], this.BXIM.messenger.showMessage[i]));
							else
								this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];
						}
					}
					if (isHistoryDialog)
					{
						for (var i = 0; i < data.USERS_MESSAGE[userId].length; i++)
						{
							var history = this.BXIM.messenger.message[data.USERS_MESSAGE[userId][i]];
							if (history)
							{
								if (BX('im-message-history-'+history.id))
									continue;

								var dateGroupTitle = BX.MessengerCommon.formatDate(history.date, BX.MessengerCommon.getDateFormatType('MESSAGE_TITLE'));
								if (!BX('bx-im-history-'+dateGroupTitle))
								{
									var dateGroupTitleNode = BX.create("div", {props : { className: "bx-messenger-content-group bx-messenger-content-group-history"}, children : [
										BX.create("div", {attrs: {id: 'bx-im-history-'+dateGroupTitle}, props : { className: "bx-messenger-content-group-title"+(this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')}, html : dateGroupTitle})
									]});
									this.BXIM.messenger.popupHistoryBodyWrap.appendChild(dateGroupTitleNode);
								}

								var history = this.BXIM.messenger.drawMessageHistory(history);
								if (history)
									this.BXIM.messenger.popupHistoryBodyWrap.appendChild(history);
							}
						}
					}
					else
					{
						var lastChildBeforeChangeDom = this.BXIM.messenger.popupMessengerBodyWrap.firstChild.nextSibling;
						lastChildBeforeChangeDom = BX('im-message-'+lastChildBeforeChangeDom.getAttribute('data-blockmessageid'));

						for (var i = 0; i < data.USERS_MESSAGE[userId].length; i++)
						{
							var history = this.BXIM.messenger.message[data.USERS_MESSAGE[userId][i]];
							if (history)
							{
								if (BX('im-message-'+history.id))
									continue;

								BX.MessengerCommon.drawMessage(userId, history, false, true);
							}
						}
						this.BXIM.messenger.popupMessengerBody.scrollTop = lastChildBeforeChangeDom.offsetTop-this.BXIM.messenger.popupMessengerBody.offsetTop-lastChildBeforeChangeDom.offsetHeight-100;
					}
				}, this),
				onfailure: BX.delegate(function(){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					if (this.isMobile())
						app.pullDownLoadingStop();
				},this)
			});
		}
	};

	BX.MessengerCommon.prototype.loadUserData = function(userId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?USER_DATA_LOAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_USER_DATA_LOAD' : 'Y', 'USER_ID' : userId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR == '')
				{
					this.BXIM.messenger.userChat[userId] = data.CHAT_ID;

					BX.MessengerCommon.getUserParam(userId, true);
					this.BXIM.messenger.users[userId].name = BX.message('IM_M_USER_NO_ACCESS');

					for (var i in data.USERS)
					{
						this.BXIM.messenger.users[i] = data.USERS[i];
					}
					for (var i in data.PHONES)
					{
						this.BXIM.messenger.phones[i] = {};
						for (var j in data.PHONES[i])
						{
							this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
						}
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined')
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}
					for (var i in data.WO_USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.woUserInGroup[i]) == 'undefined')
						{
							this.BXIM.messenger.woUserInGroup[i] = data.WO_USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.WO_USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.woUserInGroup[i].users.push(data.WO_USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.woUserInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.woUserInGroup[i].users)
						}
					}

					if (this.isMobile())
					{
						this.BXIM.messenger.dialogStatusRedrawDelay();
					}
					else
					{
						this.BXIM.messenger.dialogStatusRedraw();
					}
				}
				else
				{
					this.BXIM.messenger.redrawTab[userId] = true;
					if (data.ERROR == 'ACCESS_DENIED')
					{
						this.BXIM.messenger.currentTab = 0;
						this.BXIM.messenger.openChatFlag = false;
						this.BXIM.messenger.openCallFlag = false;
						this.BXIM.messenger.extraClose();
					}
				}
			}, this)
		});
	};

	BX.MessengerCommon.prototype.loadChatData = function(chatId)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?CHAT_DATA_LOAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_CHAT_DATA_LOAD' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR == '')
				{
					if (this.BXIM.messenger.chat[data.CHAT_ID].fake)
					{
						this.BXIM.messenger.chat[data.CHAT_ID].name = BX.message('IM_M_USER_NO_ACCESS');
					}

					for (var i in data.CHAT)
					{
						this.BXIM.messenger.chat[i] = data.CHAT[i];
					}
					for (var i in data.USER_IN_CHAT)
					{
						this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
					}
					for (var i in data.USER_BLOCK_CHAT)
					{
						this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
					}
					for (var i in data.USERS)
					{
						this.BXIM.messenger.users[i] = data.USERS[i];
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined')
						{
							this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
						}
					}
					for (var i in data.WO_USER_IN_GROUP)
					{
						if (typeof(this.BXIM.messenger.woUserInGroup[i]) == 'undefined')
						{
							this.BXIM.messenger.woUserInGroup[i] = data.WO_USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.WO_USER_IN_GROUP[i].users.length; j++)
								this.BXIM.messenger.woUserInGroup[i].users.push(data.WO_USER_IN_GROUP[i].users[j]);

							this.BXIM.messenger.woUserInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.woUserInGroup[i].users)
						}
					}
					if (this.BXIM.messenger.currentTab == 'chat'+data.CHAT_ID)
					{
						if (this.BXIM.messenger.chat[data.CHAT_ID] && this.BXIM.messenger.chat[data.CHAT_ID].type == 'call')
						{
							this.BXIM.messenger.openCallFlag = true;
						}
						this.drawTab(this.BXIM.messenger.currentTab);
					}
				}
			}, this)
		});
	};

	BX.MessengerCommon.prototype.loadLastMessage = function(userId, userIsChat)
	{
		if (this.BXIM.messenger.loadLastMessageTimeout[userId])
			return false;

		this.BXIM.messenger.historyWindowBlock = true;

		delete this.BXIM.messenger.redrawTab[userId];
		this.BXIM.messenger.loadLastMessageTimeout[userId] = true;

		var onfailure = BX.delegate(function(){
			if (this.BXIM.messenger.sendAjaxTry < 2)
			{
				this.BXIM.messenger.sendAjaxTry++;
				setTimeout(function(){
					this.BXIM.messenger.loadLastMessageTimeout[userId] = false;
					BX.MessengerCommon.loadLastMessage(userId, userIsChat);
				}, 2000);

				return true;
			}

			this.BXIM.messenger.loadLastMessageTimeout[userId] = false;
			this.BXIM.messenger.historyWindowBlock = false;
			this.BXIM.messenger.sendAjaxTry = 0;
			this.BXIM.messenger.redrawTab[userId] = true;

			this.BXIM.messenger.popupMessengerBodyWrap.innerHTML = '';

			var arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_ERROR")})
			]})];

			BX.adjust(this.BXIM.messenger.popupMessengerBodyWrap, {children: arMessage});

			if (this.isMobile() && this.MobileActionEqual('DIALOG'))
			{
				BXMobileApp.UI.Page.TopBar.title.setText(BX.message('IM_F_ERROR'));
				BXMobileApp.UI.Page.TopBar.title.setDetailText('');
			}
		}, this);

		var onsuccess = BX.delegate(function(data)
		{
			if (!this.BXIM.checkRevision(this.isMobile()? data.MOBILE_REVISION: data.REVISION))
				return false;

			this.BXIM.messenger.loadLastMessageTimeout[userId] = false;

			if (!data)
			{
				onfailure();
				return false;
			}

			if (data && data.BITRIX_SESSID)
			{
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
			}

			if (data.ERROR == '')
			{
				if (!userIsChat)
				{
					this.BXIM.messenger.userChat[userId] = data.CHAT_ID;

					BX.MessengerCommon.getUserParam(userId, true);
					this.BXIM.messenger.users[userId].name = BX.message('IM_M_USER_NO_ACCESS');
				}

				for (var i in data.USERS)
				{
					this.BXIM.messenger.users[i] = data.USERS[i];
				}

				for (var i in data.PHONES)
				{
					this.BXIM.messenger.phones[i] = {};
					for (var j in data.PHONES[i])
					{
						this.BXIM.messenger.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
					}
				}
				for (var i in data.USER_IN_GROUP)
				{
					if (typeof(this.BXIM.messenger.userInGroup[i]) == 'undefined')
					{
						this.BXIM.messenger.userInGroup[i] = data.USER_IN_GROUP[i];
					}
					else
					{
						for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
							this.BXIM.messenger.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

						this.BXIM.messenger.userInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.userInGroup[i].users)
					}
				}
				for (var i in data.WO_USER_IN_GROUP)
				{
					if (typeof(this.BXIM.messenger.woUserInGroup[i]) == 'undefined')
					{
						this.BXIM.messenger.woUserInGroup[i] = data.WO_USER_IN_GROUP[i];
					}
					else
					{
						for (var j = 0; j < data.WO_USER_IN_GROUP[i].users.length; j++)
							this.BXIM.messenger.woUserInGroup[i].users.push(data.WO_USER_IN_GROUP[i].users[j]);

						this.BXIM.messenger.woUserInGroup[i].users = BX.util.array_unique(this.BXIM.messenger.woUserInGroup[i].users)
					}
				}

				for (var i in data.READED_LIST)
				{
					data.READED_LIST[i].date = parseInt(data.READED_LIST[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
					this.BXIM.messenger.readedList[i] = data.READED_LIST[i];
				}

				if (!userIsChat && data.USER_LOAD == 'Y')
					BX.MessengerCommon.userListRedraw();

				for (var i in data.FILES)
				{
					if (!this.BXIM.messenger.disk.files[data.CHAT_ID])
						this.BXIM.messenger.disk.files[data.CHAT_ID] = {};

					data.FILES[i].date = parseInt(data.FILES[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
					this.BXIM.messenger.disk.files[data.CHAT_ID][i] = data.FILES[i];
				}

				this.BXIM.messenger.sendAjaxTry = 0;
				var messageCnt = 0;
				for (var i in data.MESSAGE)
				{
					messageCnt++;
					data.MESSAGE[i].date = parseInt(data.MESSAGE[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
					this.BXIM.messenger.message[i] = data.MESSAGE[i];
					this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
				}

				if (messageCnt <= 0)
					delete this.BXIM.messenger.redrawTab[data.USER_ID];

				for (var i in data.USERS_MESSAGE)
				{
					if (this.BXIM.messenger.showMessage[i])
						this.BXIM.messenger.showMessage[i] = BX.util.array_unique(BX.util.array_merge(data.USERS_MESSAGE[i], this.BXIM.messenger.showMessage[i]));
					else
						this.BXIM.messenger.showMessage[i] = data.USERS_MESSAGE[i];
				}
				if (userIsChat && this.BXIM.messenger.chat[data.USER_ID.substr(4)].fake)
				{
					this.BXIM.messenger.chat[data.USER_ID.toString().substr(4)].name = BX.message('IM_M_USER_NO_ACCESS');
				}

				for (var i in data.CHAT)
				{
					this.BXIM.messenger.chat[i] = data.CHAT[i];
				}
				for (var i in data.USER_IN_CHAT)
				{
					this.BXIM.messenger.userInChat[i] = data.USER_IN_CHAT[i];
				}
				for (var i in data.USER_BLOCK_CHAT)
				{
					this.BXIM.messenger.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
				}
				if (this.BXIM.messenger.currentTab == data.USER_ID)
				{
					if (this.BXIM.messenger.currentTab.toString().substr(0, 4) == 'chat' && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == 'call')
					{
						this.BXIM.messenger.openCallFlag = true;
					}
				}
				if (data.NETWORK_ID != '')
				{
					this.BXIM.messenger.currentTab = data.USER_ID;

					if (this.MobileActionEqual('RECENT'))
					{
						for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
						{
							if (this.BXIM.messenger.recent[i].userId == data.NETWORK_ID)
							{
								this.BXIM.messenger.recent[i].userId = data.USER_ID;
								this.BXIM.messenger.recent[i].recipientId = data.USER_ID;
								this.BXIM.messenger.recent[i].senderId = data.USER_ID;
							}
						}
						BX.MessengerCommon.userListRedraw();
					}
					else if (this.isMobile() && this.MobileActionEqual('DIALOG'))
					{
						app.onCustomEvent('onImDialogNetworkOpen', {NETWORK_ID: data.NETWORK_ID, USER_ID: data.USER_ID, USER: this.BXIM.messenger.users[data.USER_ID]});
					}
				}
				BX.MessengerCommon.drawTab(data.USER_ID, this.BXIM.messenger.currentTab == data.USER_ID);

				if (this.BXIM.messenger.currentTab == data.USER_ID && this.BXIM.messenger.readedList[data.USER_ID])
					BX.MessengerCommon.drawReadMessage(data.USER_ID, this.BXIM.messenger.readedList[data.USER_ID].messageId, this.BXIM.messenger.readedList[data.USER_ID].date, false);

				this.BXIM.messenger.historyWindowBlock = false;

				if (this.BXIM.isFocus())
				{
					this.readMessage(data.USER_ID, true, false);
				}
			}
			else
			{
				this.BXIM.messenger.redrawTab[userId] = true;
				if (data.ERROR == 'ACCESS_DENIED')
				{
					this.BXIM.messenger.currentTab = 0;
					this.BXIM.messenger.openChatFlag = false;
					this.BXIM.messenger.openCallFlag = false;
					this.BXIM.messenger.extraClose();
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
				{
					this.BXIM.messenger.sendAjaxTry++;
					setTimeout(BX.delegate(function(){this.loadLastMessage(userId, userIsChat)}, this), 2000);
					BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR')
				{
					this.BXIM.messenger.sendAjaxTry++;
					if (this.BXIM.desktop && this.BXIM.desktop.ready())
					{
						setTimeout(BX.delegate(function (){
							this.loadLastMessage(userId, userIsChat)
						}, this), 10000);
					}
					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
			}
		}, this);

		var xhr = BX.ajax({
			url: this.BXIM.pathToAjax+'?LOAD_LAST_MESSAGE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 90,
			data: {
				'IM_LOAD_LAST_MESSAGE' : 'Y',
				'CHAT' : userIsChat? 'Y': 'N',
				'USER_ID' : userId,
				'USER_LOAD' : userIsChat? (this.BXIM.messenger.chat[userId.toString().substr(4)] && this.BXIM.messenger.chat[userId.toString().substr(4)].fake? 'Y': 'N'): 'Y',
				'TAB' : this.BXIM.messenger.currentTab,
				'READ' : this.isMobile() || this.BXIM.isFocus()? 'Y': 'N',
				'MOBILE' : this.isMobile()? 'Y': 'N',
				'FOCUS' : !this.isMobile() || typeof BXMobileAppContext != "object" || BXMobileAppContext.isBackground()? 'N': 'Y',
				'SEARCH_MARK' : !userIsChat && this.BXIM.messenger.users[userId].searchMark? this.BXIM.messenger.users[userId].searchMark: '',
				'IM_AJAX_CALL' : 'Y',
				'sessid': BX.bitrix_sessid()
			},
			onsuccess: onsuccess,
			onprogress: function(data){
				if (data.position == 0 && data.totalSize == 0)
				{
					onfailure();
				}
			},
			onfailure: onfailure
		});
	};

	BX.MessengerCommon.prototype.openDialog = function(userId, extraClose, callToggle)
	{
		var user = BX.MessengerCommon.getUserParam(userId);
		if (user.id <= 0)
			return false;

		this.BXIM.messenger.currentTab = userId;
		if (userId.toString().substr(0,4) == 'chat')
		{
			this.BXIM.messenger.openChatFlag = true;
			if (this.BXIM.messenger.chat[userId.toString().substr(4)] && this.BXIM.messenger.chat[userId.toString().substr(4)].type == 'call')
				this.BXIM.messenger.openCallFlag = true;
		}
		BX.localStorage.set('mct', this.BXIM.messenger.currentTab, 15);

		if (this.isMobile())
		{
			this.BXIM.messenger.dialogStatusRedrawDelay();
		}
		else
		{
			this.BXIM.messenger.dialogStatusRedraw();
		}

		if (!this.isMobile())
		{
			this.BXIM.messenger.popupMessengerPanel.className  = this.BXIM.messenger.openChatFlag? 'bx-messenger-panel bx-messenger-hide': 'bx-messenger-panel';
			if (this.BXIM.messenger.openChatFlag)
			{
				this.BXIM.messenger.popupMessengerPanel2.className = this.BXIM.messenger.openCallFlag? 'bx-messenger-panel bx-messenger-hide': 'bx-messenger-panel';
				this.BXIM.messenger.popupMessengerPanel3.className = this.BXIM.messenger.openCallFlag? 'bx-messenger-panel': 'bx-messenger-panel bx-messenger-hide';
			}
			else
			{
				this.BXIM.messenger.popupMessengerPanel2.className = 'bx-messenger-panel bx-messenger-hide';
				this.BXIM.messenger.popupMessengerPanel3.className = 'bx-messenger-panel bx-messenger-hide';
			}
		}

		extraClose = extraClose == true;
		callToggle = callToggle != false;

		var arMessage = [];
		if (typeof(this.BXIM.messenger.showMessage[userId]) != 'undefined' && this.BXIM.messenger.showMessage[userId].length > 0)
		{
			if (!user.fake && this.BXIM.messenger.showMessage[userId].length >= 15)
			{
				this.BXIM.messenger.redrawTab[userId] = false;
			}
			else
			{
				this.drawTab(userId, true);
				this.BXIM.messenger.redrawTab[userId] = true;
			}
		}
		else if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_ERROR")})
			]})];
			this.BXIM.messenger.redrawTab[userId] = true;
		}
		else if (typeof(this.BXIM.messenger.showMessage[userId]) == 'undefined')
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-load"}, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message('IM_M_LOAD_MESSAGE')})
			]})];
			this.BXIM.messenger.redrawTab[userId] = true;
		}
		else if (this.BXIM.messenger.redrawTab[userId] && this.BXIM.messenger.showMessage[userId].length == 0)
		{
			BX.addClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-load"}, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message("IM_M_LOAD_MESSAGE")})
			]})];
			this.BXIM.messenger.showMessage[userId] = [];
		}
		else
		{
			BX.removeClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');
			arMessage = [BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message(this.BXIM.settings.loadLastMessage? "IM_M_NO_MESSAGE_2": "IM_M_NO_MESSAGE")})
			]})];
		}
		if (arMessage.length > 0)
		{
			this.BXIM.messenger.popupMessengerBodyWrap.innerHTML = '';
			BX.adjust(this.BXIM.messenger.popupMessengerBodyWrap, {children: arMessage});
		}

		if (extraClose)
			this.BXIM.messenger.extraClose();

		if (this.isMobile())
		{
			BXMobileApp.UI.Page.TextPanel.setText(this.BXIM.messenger.textareaHistory[userId]? this.BXIM.messenger.textareaHistory[userId]: "");
		}
		else
		{
			this.BXIM.messenger.popupMessengerTextarea.value = this.BXIM.messenger.textareaHistory[userId]? this.BXIM.messenger.textareaHistory[userId]: "";
		}

		if (this.BXIM.messenger.redrawTab[userId])
		{
			if (this.BXIM.settings.loadLastMessage)
			{
				this.loadLastMessage(userId, this.BXIM.messenger.openChatFlag);
			}
			else
			{
				if (this.BXIM.messenger.openChatFlag)
					BX.MessengerCommon.loadChatData(userId.toString().substr(4));
				else
					BX.MessengerCommon.loadUserData(userId);

				delete this.BXIM.messenger.redrawTab[userId];
				this.drawTab(userId, true);
			}
		}
		else
		{
			this.drawTab(userId, true);
		}

		if (!this.BXIM.messenger.redrawTab[userId])
		{
			if (this.isMobile())
			{
				this.BXIM.isFocusMobile(BX.delegate(function(visible){
					if (visible)
					{
						BX.MessengerCommon.readMessage(userId);
					}
				},this));
			}
			else if (this.BXIM.isFocus())
			{
				this.readMessage(userId);
			}
		}

		if (!this.isMobile())
			this.BXIM.messenger.resizeMainWindow();

		if (BX.MessengerCommon.countWriting(userId))
		{
			if (this.BXIM.messenger.openChatFlag)
				BX.MessengerCommon.drawWriting(0, userId);
			else
				BX.MessengerCommon.drawWriting(userId);
		}
		else if (this.BXIM.messenger.readedList[userId])
		{
			this.drawReadMessage(userId, this.BXIM.messenger.readedList[userId].messageId, this.BXIM.messenger.readedList[userId].date, false);
		}

		if (!this.isMobile() && callToggle)
			this.BXIM.webrtc.callOverlayToggleSize(true);

		BX.onCustomEvent("onImDialogOpen", [{id: userId}]);
		if (this.isMobile())
		{
			app.onCustomEvent('onImDialogOpen', {'id': userId});
		}
	};

	BX.MessengerCommon.prototype.drawTab = function(userId, scroll)
	{
		if (!userId)
		{
			userId = this.BXIM.messenger.currentTab;
		}

		if (this.BXIM.messenger.popupMessenger == null || userId != this.BXIM.messenger.currentTab)
			return false;

		if (this.BXIM.messenger.openChatFlag)
		{
			var chatId = userId.toString().substr(4);
			if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].type == 'open')
			{
				if (!BX.MessengerCommon.userInChat(chatId))
				{
					if (this.isMobile())
					{
						app.onCustomEvent('onPullExtendWatch', {'id': 'IM_PUBLIC_'+chatId, force: this.BXIM.messenger.redrawTab[userId]? false: true});
					}
					else
					{
						BX.PULL.extendWatch('IM_PUBLIC_'+chatId, this.BXIM.messenger.redrawTab[userId]? false: true);
					}
				}
			}
		}
		if (this.isMobile())
		{
			this.BXIM.messenger.dialogStatusRedrawDelay();
		}
		else
		{
			this.BXIM.messenger.dialogStatusRedraw();
		}
		this.BXIM.messenger.popupMessengerBodyWrap.innerHTML = '';
		BX.removeClass(this.BXIM.messenger.popupMessengerBodyWrap, 'bx-messenger-loading');

		if (!this.BXIM.messenger.showMessage[userId] || this.BXIM.messenger.showMessage[userId].length <= 0)
		{
			this.BXIM.messenger.popupMessengerBodyWrap.appendChild(BX.create("div", { props : { className : "bx-messenger-content-empty"}, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text"}, html: BX.message(this.BXIM.settings.loadLastMessage? "IM_M_NO_MESSAGE_2": "IM_M_NO_MESSAGE")})
			]}));
		}

		if (this.BXIM.messenger.showMessage[userId])
			this.BXIM.messenger.showMessage[userId].sort(BX.delegate(function(i, ii) {if (!this.BXIM.messenger.message[i] || !this.BXIM.messenger.message[ii]){return 0;} var i1 = parseInt(this.BXIM.messenger.message[i].date); var i2 = parseInt(this.BXIM.messenger.message[ii].date); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));
		else
			this.BXIM.messenger.showMessage[userId] = [];

		for (var i = 0; i < this.BXIM.messenger.showMessage[userId].length; i++)
			BX.MessengerCommon.drawMessage(userId, this.BXIM.messenger.message[this.BXIM.messenger.showMessage[userId][i]], false);

		scroll = scroll != false;
		if (scroll)
		{
			if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
				this.BXIM.messenger.popupMessengerBodyAnimation.stop();

			if (this.BXIM.messenger.unreadMessage[userId] && this.BXIM.messenger.unreadMessage[userId].length > 0)
			{
				var textElement = BX('im-message-'+this.BXIM.messenger.unreadMessage[userId][0]);
				if (textElement)
					this.BXIM.messenger.popupMessengerBody.scrollTop  = textElement.offsetTop-60-this.BXIM.messenger.popupMessengerBodyWrap.offsetTop;
				else
					this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
			else
			{
				this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
			}
		}
		delete this.BXIM.messenger.redrawTab[userId];
	};



	/* Section: Send Message */
	BX.MessengerCommon.prototype.sendMessageAjax = function(messageTmpIndex, recipientId, messageText, sendMessageToChat)
	{
		if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
			return false;

		BX.MessengerCommon.drawProgessMessage('temp'+messageTmpIndex);

		if (this.BXIM.messenger.sendMessageFlag < 0)
			this.BXIM.messenger.sendMessageFlag = 0;

		clearTimeout(this.BXIM.messenger.sendMessageTmpTimeout['temp'+messageTmpIndex]);
		if (this.BXIM.messenger.sendMessageTmp[messageTmpIndex])
			return false;

		this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = true;
		sendMessageToChat = sendMessageToChat == true;
		this.BXIM.messenger.sendMessageFlag++;

		BX.MessengerCommon.recentListAdd({
			'id': 'temp'+messageTmpIndex,
			'date': BX.MessengerCommon.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET")),
			'skipDateCheck': true,
			'recipientId': recipientId,
			'senderId': this.BXIM.userId,
			'text': BX.MessengerCommon.prepareText(messageText, true),
			'userId': recipientId,
			'params': {}
		}, true);

		var _ajax = BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_SEND&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 60,
			data: {'IM_SEND_MESSAGE' : 'Y', 'CHAT': sendMessageToChat? 'Y': 'N', 'ID' : 'temp'+messageTmpIndex, 'RECIPIENT_ID' : recipientId, 'MESSAGE' : messageText, 'TAB' : this.BXIM.messenger.currentTab, 'USER_TZ_OFFSET': BX.message('USER_TZ_OFFSET'), 'IM_AJAX_CALL' : 'Y', 'FOCUS' : !this.isMobile() || typeof BXMobileAppContext != "object" || BXMobileAppContext.isBackground()? 'N': 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				this.BXIM.messenger.sendMessageFlag--;

				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}

				if (data.ERROR == '')
				{
					this.BXIM.messenger.sendAjaxTry = 0;
					this.BXIM.messenger.message[data.TMP_ID].text = data.SEND_MESSAGE;
					this.BXIM.messenger.message[data.TMP_ID].id = data.ID;
					this.BXIM.messenger.message[data.TMP_ID].date = parseInt(data.SEND_DATE);
					if (data.SEND_MESSAGE_PARAMS)
					{
						this.BXIM.messenger.message[data.TMP_ID].params = data.SEND_MESSAGE_PARAMS;
					}

					this.BXIM.messenger.message[data.ID] = this.BXIM.messenger.message[data.TMP_ID];

					if (this.BXIM.messenger.popupMessengerLastMessage == data.TMP_ID)
						this.BXIM.messenger.popupMessengerLastMessage = data.ID;

					delete this.BXIM.messenger.message[data.TMP_ID];
					var message = this.BXIM.messenger.message[data.ID];

					var idx = BX.util.array_search(''+data.TMP_ID+'', this.BXIM.messenger.showMessage[data.RECIPIENT_ID]);
					if (this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx])
						this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx] = ''+data.ID+'';

					for (var i = 0; i < this.BXIM.messenger.recent.length; i++)
					{
						if (this.BXIM.messenger.recent[i].id == data.TMP_ID)
						{
							this.BXIM.messenger.recent[i].id = ''+data.ID+'';
							break;
						}
					}

					if (data.RECIPIENT_ID == this.BXIM.messenger.currentTab)
					{
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.TMP_ID+''}}, true);
						if (element)
						{
							element.setAttribute('data-messageid',	''+data.ID+'');
							if (element.getAttribute('data-blockmessageid') == ''+data.TMP_ID+'')
							{
								element.setAttribute('data-blockmessageid', ''+data.ID+'');
							}
							else
							{
								var element2 = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+data.TMP_ID+''}}, true);
								if (element2)
								{
									element2.setAttribute('data-blockmessageid', ''+data.ID+'');
								}
							}
						}

						var textElement = BX('im-message-'+data.TMP_ID);
						if (textElement)
						{
							textElement.id = 'im-message-'+data.ID;
							textElement.innerHTML =  BX.MessengerCommon.prepareText(data.SEND_MESSAGE, false, true, true);

							if (data.SEND_MESSAGE_PARAMS && data.SEND_MESSAGE_PARAMS.ATTACH)
							{
								var attachNode = BX.MessengerCommon.drawAttach(this.BXIM.messenger.message[data.ID].chatId, data.SEND_MESSAGE_PARAMS.ATTACH);
								if (attachNode.length > 0)
								{
									attachNode = BX.create("div", {props : {className : "bx-messenger-attach-box"},children : attachNode});
									if (textElement.nextElementSibling)
									{
										textElement.parentNode.insertBefore(attachNode, textElement.nextElementSibling);
									}
									else
									{
										textElement.parentNode.appendChild(attachNode);
									}
								}
							}
						}

						var messageUser = this.BXIM.messenger.users[message.senderId];
						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						if (lastMessageElementDate)
							lastMessageElementDate.innerHTML = ' &nbsp; '+BX.MessengerCommon.formatDate(message.date, BX.MessengerCommon.getDateFormatType('MESSAGE'));

						BX.MessengerCommon.clearProgessMessage(data.ID);
					}

					if (this.BXIM.messenger.history[data.RECIPIENT_ID])
						this.BXIM.messenger.history[data.RECIPIENT_ID].push(message.id);
					else
						this.BXIM.messenger.history[data.RECIPIENT_ID] = [message.id];

					this.BXIM.messenger.updateStateVeryFastCount = 2;
					this.BXIM.messenger.updateStateFastCount = 5;
					this.BXIM.messenger.setUpdateStateStep();

					if (BX.PULL)
					{
						BX.PULL.setUpdateStateStepCount(2,5);
					}
					BX.MessengerCommon.updateStateVar(data, true, true);
					BX.localStorage.set('msm', {'id': data.ID, 'recipientId': data.RECIPIENT_ID, 'date': data.SEND_DATE, 'text' : data.SEND_MESSAGE, 'senderId' : this.BXIM.userId, 'MESSAGE': data.MESSAGE, 'USERS_MESSAGE': data.USERS_MESSAGE, 'USERS': data.USERS, 'USER_IN_GROUP': data.USER_IN_GROUP, 'WO_USER_IN_GROUP': data.WO_USER_IN_GROUP}, 5);

					if (this.BXIM.animationSupport)
					{
						if (this.BXIM.messenger.popupMessengerBodyAnimation != null)
							this.BXIM.messenger.popupMessengerBodyAnimation.stop();
						(this.BXIM.messenger.popupMessengerBodyAnimation = new BX.easing({
							duration : 800,
							start : { scroll : this.BXIM.messenger.popupMessengerBody.scrollTop},
							finish : { scroll : this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1)},
							transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
							step : BX.delegate(function(state){
								this.BXIM.messenger.popupMessengerBody.scrollTop = state.scroll;
							}, this)
						})).animate();
					}
					else
					{
						this.BXIM.messenger.popupMessengerBody.scrollTop = this.BXIM.messenger.popupMessengerBody.scrollHeight - this.BXIM.messenger.popupMessengerBody.offsetHeight*(this.isMobile()? 0: 1);
					}

					if (!this.MobileActionEqual('RECENT') && (this.BXIM.messenger.recentList || this.BXIM.messenger.recentListExternal))
						this.recentListRedraw();
				}
				else
				{
					if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
					{
						this.BXIM.messenger.sendAjaxTry++;
						setTimeout(BX.delegate(function(){
							this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
							this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
						}, this), 2000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.BXIM.messenger.sendAjaxTry++;
						if (this.BXIM.desktop && this.BXIM.desktop.ready())
						{
							setTimeout(BX.delegate(function (){
								this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
								this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
							}, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
					else
					{
						this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': 'temp'+messageTmpIndex}}, true);
						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						if (lastMessageElementDate)
						{
							if (data.ERROR == 'SESSION_ERROR' || data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'UNKNOWN_ERROR' || data.ERROR == 'IM_MODULE_NOT_INSTALLED')
								lastMessageElementDate.innerHTML = BX.message('IM_M_NOT_DELIVERED');
							else
								lastMessageElementDate.innerHTML = data.ERROR;
						}
						BX.onCustomEvent(window, 'onImError', ['SEND_ERROR', data.ERROR, data.TMP_ID, data.SEND_DATE, data.SEND_MESSAGE, data.RECIPIENT_ID]);

						BX.MessengerCommon.drawProgessMessage('temp'+messageTmpIndex, {title: BX.message('IM_M_RETRY'), chat: sendMessageToChat? 'Y':'N'});

						if (this.BXIM.messenger.message['temp'+messageTmpIndex])
							this.BXIM.messenger.message['temp'+messageTmpIndex].retry = true;
					}
				}
			}, this),
			onfailure: BX.delegate(function()	{
				this.BXIM.messenger.sendMessageFlag--;
				this.BXIM.messenger.sendMessageTmp[messageTmpIndex] = false;
				var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': 'temp'+messageTmpIndex}}, true);
				var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
				if (lastMessageElementDate)
					lastMessageElementDate.innerHTML = BX.message('IM_M_NOT_DELIVERED');

				BX.MessengerCommon.drawProgessMessage('temp'+messageTmpIndex, {title: BX.message('IM_M_RETRY'), chat: sendMessageToChat? 'Y':'N'});

				this.BXIM.messenger.sendAjaxTry = 0;
				try {
					if (typeof(_ajax) == 'object' && _ajax.status == 0)
						BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
				}
				catch(e) {}
				if (this.BXIM.messenger.message['temp'+messageTmpIndex])
					this.BXIM.messenger.message['temp'+messageTmpIndex].retry = true;
			}, this)
		});
	};

	BX.MessengerCommon.prototype.sendMessageRetry = function()
	{
		var currentTab = this.BXIM.messenger.currentTab;
		var messageStack = [];
		for (var i = 0; i < this.BXIM.messenger.showMessage[currentTab].length; i++)
		{
			var message = this.BXIM.messenger.message[this.BXIM.messenger.showMessage[currentTab][i]];
			if (!message || message.id.indexOf('temp') != 0)
				continue;

			message.text = BX.MessengerCommon.prepareTextBack(message.text);

			messageStack.push(message);
		}
		if (messageStack.length <= 0)
			return false;

		messageStack.sort(function(i, ii) {i = i.id.substr(4); ii = ii.id.substr(4); if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}});
		for (var i = 0; i < messageStack.length; i++)
		{
			var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+messageStack[i].id+''}}, true);
			var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
			if (lastMessageElementDate)
				lastMessageElementDate.innerHTML = BX.message('IM_M_DELIVERED');

			this.sendMessageRetryTimeout(messageStack[i], 100*i);
		}
	};

	BX.MessengerCommon.prototype.sendMessageRetryTimeout = function(message, timeout)
	{
		clearTimeout(this.BXIM.messenger.sendMessageTmpTimeout[message.id]);
		this.BXIM.messenger.sendMessageTmpTimeout[message.id] = setTimeout(BX.delegate(function() {
			BX.MessengerCommon.sendMessageAjax(message.id.substr(4), message.recipientId, message.text, message.recipientId.toString().substr(0,4) == 'chat');
		}, this), timeout);
	};

	BX.MessengerCommon.prototype.getLastMessageInDialog = function(dialogId)
	{
		var result = false;

		if (this.BXIM.messenger.showMessage[dialogId] && this.BXIM.messenger.showMessage[dialogId].length > 0)
		{
			var lastId = this.BXIM.messenger.showMessage[dialogId][this.BXIM.messenger.showMessage[dialogId].length-1];
			result = this.BXIM.messenger.message[lastId];
		}

		return result;
	}

	BX.MessengerCommon.prototype.joinToChat = function(chatId)
	{
		if (this.BXIM.messenger.blockJoinChat[chatId])
			return false;

		if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].type != 'open')
			return false;

		if (BX.MessengerCommon.userInChat(chatId))
			return false;

		this.BXIM.messenger.blockJoinChat[chatId] = true;

		BX.ajax({
			url: this.BXIM.pathToAjax+'?CHAT_JOIN&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'IM_CHAT_JOIN' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;

				this.BXIM.messenger.popupMessengerTextarea.disabled = false;
				this.BXIM.messenger.popupMessengerTextarea.focus();
			}, this),
			onfailure: BX.delegate(function(){
				this.BXIM.messenger.blockJoinChat[chatId] = false;
			}, this)
		});
	};

	BX.MessengerCommon.prototype.messageLike = function(messageId, onlyDraw)
	{
		if (messageId.toString().substr(0,4) == 'temp' || !this.BXIM.messenger.message[messageId] || this.BXIM.messenger.popupMessengerLikeBlock[messageId])
			return false;

		onlyDraw = typeof(onlyDraw) == 'undefined'? false: onlyDraw;

		if (!this.BXIM.messenger.message[messageId].params)
		{
			this.BXIM.messenger.message[messageId].params = {};
		}
		if (!this.BXIM.messenger.message[messageId].params.LIKE)
		{
			this.BXIM.messenger.message[messageId].params.LIKE = [];
		}

		var iLikeThis = BX.util.in_array(this.BXIM.userId, this.BXIM.messenger.message[messageId].params.LIKE);
		if (!onlyDraw)
		{
			var likeAction = iLikeThis? 'minus': 'plus';
			if (likeAction == 'plus')
			{
				this.BXIM.messenger.message[messageId].params.LIKE.push(this.BXIM.userId);
				iLikeThis = true;
			}
			else
			{
				var newLikeArray = [];
				for (var i = 0; i < this.BXIM.messenger.message[messageId].params.LIKE.length; i++)
				{
					if (this.BXIM.messenger.message[messageId].params.LIKE[i] != this.BXIM.userId)
					{
						newLikeArray.push(this.BXIM.messenger.message[messageId].params.LIKE[i])
					}
				}
				this.BXIM.messenger.message[messageId].params.LIKE = newLikeArray;
				iLikeThis = false;
			}
		}
		var likeCount = this.BXIM.messenger.message[messageId].params.LIKE.length > 0? this.BXIM.messenger.message[messageId].params.LIKE.length: '';

		if (BX('im-message-'+messageId))
		{
			var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+messageId+''}}, false);
			var elementLike = BX.findChildByClassName(element, "bx-messenger-content-item-like");
			var elementLikeDigit = BX.findChildByClassName(element, "bx-messenger-content-like-digit", false);
			var elementLikeButton = BX.findChildByClassName(element, "bx-messenger-content-like-button", false);

			if (iLikeThis)
			{
				elementLikeButton.innerHTML = BX.message('IM_MESSAGE_DISLIKE');
				BX.addClass(elementLike, 'bx-messenger-content-item-liked');
			}
			else
			{
				elementLikeButton.innerHTML = BX.message('IM_MESSAGE_LIKE');
				BX.removeClass(elementLike, 'bx-messenger-content-item-liked');
			}

			if (likeCount>0)
			{
				elementLikeDigit.setAttribute('title', BX.message('IM_MESSAGE_LIKE_LIST'));
				BX.removeClass(elementLikeDigit, 'bx-messenger-content-like-digit-off');
			}
			else
			{
				elementLikeDigit.setAttribute('title', '');
				BX.addClass(elementLikeDigit, 'bx-messenger-content-like-digit-off');
			}

			elementLikeDigit.innerHTML = likeCount;
		}
		if (!onlyDraw)
		{
			clearTimeout(this.BXIM.messenger.popupMessengerLikeBlockTimeout[messageId]);
			this.BXIM.messenger.popupMessengerLikeBlockTimeout[messageId] = setTimeout(BX.delegate(function(){
				this.BXIM.messenger.popupMessengerLikeBlock[messageId] = true;
				BX.ajax({
					url: this.BXIM.pathToAjax+'?MESSAGE_LIKE&V='+this.BXIM.revision,
					method: 'POST',
					dataType: 'json',
					timeout: 30,
					data: {'IM_LIKE_MESSAGE' : 'Y', 'ID': messageId, 'ACTION' : likeAction, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
					onsuccess: BX.delegate(function(data) {
						if (data.ERROR == '')
						{
							this.BXIM.messenger.message[messageId].params.LIKE = data.LIKE;
						}
						this.BXIM.messenger.popupMessengerLikeBlock[messageId] = false;
						BX.MessengerCommon.messageLike(messageId, true);
					}, this),
					onfailure: BX.delegate(function(data) {
						this.BXIM.messenger.popupMessengerLikeBlock[messageId] = false;
					}, this)
				});
			},this), 1000);
		}

		return true;
	}

	BX.MessengerCommon.prototype.messageIsLike = function(messageId)
	{
		return typeof(this.BXIM.messenger.message[messageId].params.LIKE) == "object" && BX.util.in_array(this.BXIM.userId, this.BXIM.messenger.message[messageId].params.LIKE);
	}

	BX.MessengerCommon.prototype.checkEditMessage = function(id)
	{
		var result = false;
		if (
			this.BXIM.ppServerStatus && parseInt(id) != 0 && id.toString().substr(0,4) != 'temp' &&
			this.BXIM.messenger.message[id] && this.BXIM.messenger.message[id].senderId == this.BXIM.userId &&
			parseInt(this.BXIM.messenger.message[id].date)+259200 > (new Date().getTime())/1000 &&
			(!this.BXIM.messenger.message[id].params || this.BXIM.messenger.message[id].params.IS_DELETED != 'Y') &&
			BX('im-message-'+id) && BX.util.in_array(id, this.BXIM.messenger.showMessage[this.BXIM.messenger.currentTab])
		)
		{
			result = true;
		}

		return result;
	}

	BX.MessengerCommon.prototype.editMessageAjax = function(id, text)
	{
		if (this.BXIM.messenger.popupMessengerConnectionStatusState != 'online')
			return false;

		this.BXIM.messenger.editMessageCancel();
		if (!BX.MessengerCommon.checkEditMessage(id))
			return false;

		if (text == BX.MessengerCommon.prepareTextBack(this.BXIM.messenger.message[id].text, true))
			return false;

		text = text.replace('    ', "\t");
		text = BX.util.trim(text);
		if (text.length <= 0)
		{
			BX.MessengerCommon.deleteMessageAjax(id);
			return false;
		}

		text = BX.MessengerCommon.prepareMention(this.BXIM.messenger.currentTab, text);

		BX.MessengerCommon.drawProgessMessage(id);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_EDIT&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_EDIT_MESSAGE' : 'Y', ID: id, MESSAGE: text, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this),
			onfailure: BX.delegate(function() {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this)
		});
	}

	BX.MessengerCommon.prototype.deleteMessageAjax = function(id)
	{
		this.BXIM.messenger.editMessageCancel();

		if (!BX.MessengerCommon.checkEditMessage(id))
			return false;

		BX.MessengerCommon.drawProgessMessage(id);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?MESSAGE_DELETE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_DELETE_MESSAGE' : 'Y', ID: id, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data) {
				if (data.ERROR)
					return false;

				BX.MessengerCommon.clearProgessMessage(id);
			}, this),
			onfailure: BX.delegate(function() {
				BX.MessengerCommon.clearProgessMessage(id);
			}, this)
		});

		return true;
	}



	/* Section: Attach */
	BX.MessengerCommon.prototype.drawAttach = function(chatId, attachConfig, params)
	{
		if (!attachConfig || attachConfig.length == 0)
			return [];

		var attachArray = [];
		if (typeof(attachConfig) != 'object')
		{
			attachArray.push(attachConfig);
		}
		else
		{
			attachArray = attachConfig;
		}
		params = params || {};

		var userColor = this.getUserIdByChatId(chatId);

		var nodeCollection = [];
		for (var j = 0; j < attachArray.length; j++)
		{
			var attachBlock = attachArray[j];

			var color = "";
			if (typeof(attachBlock.COLOR) != 'undefined')
			{
				color = attachBlock.COLOR;
			}
			else if (userColor && this.BXIM.messenger.users[userColor])
			{
				color = this.BXIM.messenger.users[userColor].color;
			}
			else if (this.BXIM.messenger.chat[chatId])
			{
				color = this.BXIM.messenger.chat[chatId].color;
			}
			else if (this.BXIM.messenger.users[this.BXIM.userId])
			{
				color = this.BXIM.messenger.users[this.BXIM.userId].color;
			}

			if (typeof(attachBlock['BLOCKS']) != 'object')
			{
				continue;
			}

			var blockCollection = [];
			for (var k = 0; k < attachBlock['BLOCKS'].length; k++)
			{
				var attach = attachBlock['BLOCKS'][k];
				var blockNode = null;
				if (attach.USER && attach.USER.length > 0)
				{
					var userNodes = [];
					for (var i = 0; i < attach.USER.length; i++)
					{
						var linkTitle = null;
						if (attach.USER[i].NETWORK_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'network', 'data-networkId': attach.USER[i].NETWORK_ID}, html: attach.USER[i].NAME});
						}
						else if (attach.USER[i].USER_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax "+(attach.USER[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')}, attrs: {'data-entity': 'user', 'data-chatId': attach.USER[i].USER_ID}, html: attach.USER[i].NAME});
						}
						else if (attach.USER[i].CHAT_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-attach-user-name bx-messenger-ajax"}, attrs: {'data-entity': 'chat', 'data-chatId': attach.USER[i].CHAT_ID}, html: attach.USER[i].NAME});
						}
						else if (attach.USER[i].LINK)
						{
							linkTitle = BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.USER[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-attach-user-name"}, html: attach.USER[i].NAME});
						}
						else
						{
							linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-user-name"}, html: attach.USER[i].NAME})
						}

						var userNode = BX.create("span", { props : { className: "bx-messenger-attach-user"}, children: [
							BX.create("span", { props : { className: "bx-messenger-attach-user-avatar"}, children: [
								attach.USER[i].AVATAR? BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.USER[i].AVATAR)}, props : { className: "bx-messenger-attach-user-avatar-img"}}): BX.create("span", { attrs: {style: "background-color: "+color}, props : { className: "bx-messenger-attach-user-avatar-img bx-messenger-attach-"+(attach.USER[i].AVATAR_TYPE == 'CHAT'? 'chat': 'user')+"-avatar-default "}})
							]}),
							linkTitle
						]});
						userNodes.push(userNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-users"}, children: userNodes});
				}
				else if (attach.LINK && attach.LINK.length > 0)
				{
					var linkNodes = [];
					for (var i = 0; i < attach.LINK.length; i++)
					{
						var linkTitle = BX.create("span", { props : { className: "bx-messenger-attach-link-name"}, html: attach.LINK[i].NAME? attach.LINK[i].NAME: attach.LINK[i].LINK});
						if (attach.LINK[i].NETWORK_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax "}, attrs: {'data-entity': 'network', 'data-networkId': attach.LINK[i].NETWORK_ID}, children: [linkTitle]});
						}
						else if (attach.LINK[i].USER_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax "+(attach.LINK[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')}, attrs: {'data-entity': 'user', 'data-chatId': attach.LINK[i].USER_ID}, children: [linkTitle]});
						}
						else if (attach.LINK[i].CHAT_ID)
						{
							linkTitle = BX.create("span", {props : { className: "bx-messenger-ajax"}, attrs: {'data-entity': 'chat', 'data-chatId': attach.LINK[i].CHAT_ID}, children: [linkTitle]});
						}
						else
						{
							linkTitle = BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.LINK[i].LINK), 'target': '_blank'}, children: [linkTitle]});
						}

						var linkDesc = null;
						if (attach.LINK[i].DESC)
						{
							linkDesc = BX.create("span", { props : { className: "bx-messenger-attach-link-desc"}, html: attach.LINK[i].DESC});
						}

						var linkPreview = null;
						if (attach.LINK[i].HTML)
						{
							linkPreview = BX.create("div", { props : { className: "bx-messenger-attach-link-html"}, html: attach.LINK[i].HTML});
						}
						else if (attach.LINK[i].PREVIEW)
						{
							linkPreview = BX.create("span", { props : { className: "bx-messenger-file-image-src"}, children: [
								BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.LINK[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this)"}, props : { className: "bx-messenger-file-image-text"}})
							]});
							linkPreview = BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.LINK[i].LINK), 'target': '_blank'}, children: [linkPreview]});
						}
						var link = BX.create("span", {props : { className: "bx-messenger-attach-link"+(attach.LINK[i].PREVIEW? " bx-messenger-attach-link-with-preview": "")}, children: [linkTitle, linkDesc, linkPreview]})
						linkNodes.push(link);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-links"}, children: linkNodes});
				}
				else if(attach.MESSAGE && attach.MESSAGE.length > 0)
				{
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-message"}, html: attach.MESSAGE});
				}
				else if(attach.HTML && attach.HTML.length > 0)
				{
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-message"}, html: attach.HTML});
				}
				else if(attach.GRID && attach.GRID.length > 0)
				{
					var gridNodes = [];
					for (var i = 0; i < attach.GRID.length; i++)
					{
						var gridValue = attach.GRID[i].VALUE;
						if (attach.GRID[i].USER_ID)
						{
							gridValue = '<span class="bx-messenger-ajax '+(attach.GRID[i].USER_ID == this.BXIM.userId? 'bx-messenger-ajax-self': '')+'" data-entity="user" data-userId="'+attach.GRID[i].USER_ID+'">'+gridValue+'</span>';
						}
						else if (attach.GRID[i].CHAT_ID)
						{
							gridValue = '<span class="bx-messenger-ajax" data-entity="chat" data-chatId="'+attach.GRID[i].CHAT_ID+'">'+gridValue+'</span>';
						}
						else if (attach.GRID[i].LINK)
						{
							gridValue = '<a href="'+attach.GRID[i].LINK+'" target="_blank"">'+gridValue+'</a>';
						}
						var width = attach.GRID[i].WIDTH? 'width: '+attach.GRID[i].WIDTH+'px': '';
						var gridNode = BX.create("span", { props : { className: "bx-messenger-attach-block bx-messenger-attach-block-"+(attach.GRID[i].DISPLAY.toLowerCase())}, attrs: { style: attach.GRID[i].DISPLAY == 'LINE'? width: ''}, children: [
							BX.create("div", { props : { className: "bx-messenger-attach-block-name"}, attrs: { style: attach.GRID[i].DISPLAY == 'ROW'? width: ''}, html: attach.GRID[i].NAME}),
							BX.create("div", { props : { className: "bx-messenger-attach-block-value"}, attrs: { style: attach.GRID[i].COLOR? 'color: '+attach.GRID[i].COLOR: ''}, html: gridValue})
						]});
						gridNodes.push(gridNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-blocks"}, children: gridNodes});
				}
				else if (attach.DELIMITER)
				{
					var attrs = "";
					if (attach.DELIMITER.SIZE)
					{
						attrs += "width: "+attach.DELIMITER.SIZE+"px;"
					}
					if (attach.DELIMITER.COLOR)
					{
						attrs += "background-color: "+attach.DELIMITER.COLOR
					}
					if (attrs)
					{
						attrs = {style: attrs};
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-delimiter"}, attrs: attrs});
				}
				else if (attach.IMAGE && attach.IMAGE.length > 0)
				{
					var imageNodes = [];
					for (var i = 0; i < attach.IMAGE.length; i++)
					{
						if (!attach.IMAGE[i].NAME)
						{
							attach.IMAGE[i].NAME = "";
						}

						if (!attach.IMAGE[i].PREVIEW)
						{
							attach.IMAGE[i].PREVIEW = attach.IMAGE[i].LINK;
						}
						var imageNode = BX.create("a", { props : { className: "bx-messenger-file-image-src"}, attrs: {'href': BX.util.htmlspecialcharsback(attach.IMAGE[i].LINK), 'target': '_blank', 'title': attach.IMAGE[i].NAME}, children: [
							BX.create("img", { attrs:{'src': BX.util.htmlspecialcharsback(attach.IMAGE[i].PREVIEW), 'onerror': "BX.MessengerCommon.hideErrorImage(this)"}, props : { className: "bx-messenger-file-image-text"}})
						]})

						imageNodes.push(imageNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-images"}, children: imageNodes});
				}
				else if(attach.FILE && attach.FILE.length > 0)
				{
					var filesNodes = [];
					for (var i = 0; i < attach.FILE.length; i++)
					{
						var fileName = attach.FILE[i].NAME? attach.FILE[i].NAME: attach.FILE[i].LINK;
						if (this.isMobile())
						{
							if (fileName.length > 20)
							{
								fileName = fileName.substr(0, 7)+'...'+fileName.substr(fileName.length-10, fileName.length);
							}
						}
						else
						{
							if (fileName.length > 43)
							{
								fileName = fileName.substr(0, 20)+'...'+fileName.substr(fileName.length-20, fileName.length);
							}
						}
						fileName = BX.create("span", { attrs: {'title': attach.FILE[i].NAME}, props : { className: "bx-messenger-file-title"}, children: [
							BX.create("span", { props : { className: "bx-messenger-file-title-name"}, html: fileName})
						]});
						var fileNode = BX.create("div", { props : { className: "bx-messenger-file"}, children: [
							BX.create("div", { props : { className: "bx-messenger-file-attrs"}, children: [
								BX.create("a", { props : { className: "bx-messenger-file-title-href"}, attrs: {'href': BX.util.htmlspecialcharsback(attach.FILE[i].LINK), 'target': '_blank'}, children: [fileName]}),
								attach.FILE[i].SIZE? BX.create("span", { props : { className: "bx-messenger-file-size"}, html: BX.UploaderUtils.getFormattedSize(attach.FILE[i].SIZE)}): null
							]}),
							BX.create("div", { props : { className: "bx-messenger-file-download"}, children: [
								BX.create("a", {attrs: {'href': BX.util.htmlspecialcharsback(attach.FILE[i].LINK), 'target': '_blank'}, props : { className: "bx-messenger-file-download-link bx-messenger-file-download-pc"}, html: BX.message('IM_F_DOWNLOAD')})
							]})
						]});
						filesNodes.push(fileNode);
					}
					blockNode = BX.create("span", { props : { className: "bx-messenger-attach-files"}, children: filesNodes});
				}
				blockCollection.push(blockNode);
			}

			if (blockCollection.length > 0)
			{
				nodeCollection.push(BX.create("div", {
					props : { className: "bx-messenger-attach"},
					attrs: { 'style': 'border-color: '+color},
					children: blockCollection
				}));
			}
		}
		return nodeCollection
	}



	/* Section: Disk Manager */
	BX.MessengerCommon.prototype.diskDrawFiles = function(chatId, fileId, params)
	{
		if (!this.BXIM.disk.enable || !chatId || !fileId)
			return [];

		var fileIds = [];
		if (typeof(fileId) != 'object')
		{
			fileIds.push(fileId);
		}
		else
		{
			fileIds = fileId;
		}
		params = params || {};

		var urlContext = this.isMobile()? 'mobile': (this.BXIM.desktop.ready()? 'desktop': 'default');
		var enableLink = true;
		var nodeCollection = [];

		for (var i = 0; i < fileIds.length; i++)
		{
			var file = this.BXIM.disk.files[chatId] && this.BXIM.disk.files[chatId][fileIds[i]];
			if (!file)
			{
				var file = {'id': fileIds[i], 'chatId': chatId};
				var boxId = params.boxId? params.boxId: 'im-file';

				nodeCollection.push(BX.create("div", {
					attrs: { id: boxId+'-'+file.id, 'data-chatId': file.chatId , 'data-fileId': file.id, 'data-boxId': boxId},
					props : { className: "bx-messenger-file"},
					children: [BX.create("span", { props : { className: "bx-messenger-file-deleted"}, html: BX.message('IM_F_DELETED')})]
				}));

				continue;
			}

			if (params.status)
			{
				if (typeof(params.status) != 'object')
				{
					params.status = [params.status];
				}
				if (!BX.util.in_array(file.status, params.status))
				{
					continue;
				}
			}

			var preview = null;
			if (file.type == 'image' && (file.preview || file.urlPreview[urlContext]))
			{
				var imageNodeMobile = null;
				if (this.isMobile() && file.preview && typeof(file.preview) != 'string')
				{
					if (file.urlPreview[urlContext])
					{
						var imageNodeMobile = BX.create("div", { attrs:{'src': file.urlPreview[urlContext]}, props : { className: "bx-messenger-file-image-text bx-messenger-hide"}});
					}
				}
				var imageNode = null;
				if (file.preview && typeof(file.preview) != 'string')
				{
					imageNode = file.preview;
					if (file.urlPreview[urlContext])
					{
						file.preview = '';
					}
				}
				else
				{
					imageNode = BX.create("img", { attrs:{'src': file.urlPreview[urlContext]? file.urlPreview[urlContext]: file.preview}, props : { className: "bx-messenger-file-image-text"}});
				}

				if (enableLink && file.urlShow[urlContext])
				{
					if (this.isMobile() && file.urlPreview[urlContext])
					{
						preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
								BX.create("span", {events: {click: BX.delegate(function(){
									this.BXIM.messenger.openPhotoGallery(file.urlPreview[urlContext]);
								}, this)}, props : { className: "bx-messenger-file-image-src"},  children: [
									imageNodeMobile,
									imageNode
								]})
							]}),
							BX.create("br")
						]});
					}
					else
					{
						preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
								BX.create("a", {attrs: {'href': file.urlShow[urlContext], 'target': '_blank'}, props : { className: "bx-messenger-file-image-src"},  children: [
									imageNode
								]})
							]}),
							BX.create("br")
						]});
					}
				}
				else
				{
					preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
						BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
							BX.create("span", {props : { className: "bx-messenger-file-image-src"},  children: [
								imageNode
							]})
						]}),
						BX.create("br")
					]});
				}
			}
			var fileName = file.name;
			if (this.isMobile())
			{
				if (fileName.length > 20)
				{
					fileName = fileName.substr(0, 7)+'...'+fileName.substr(fileName.length-10, fileName.length);
				}
			}
			else
			{
				if (fileName.length > 43)
				{
					fileName = fileName.substr(0, 20)+'...'+fileName.substr(fileName.length-20, fileName.length);
				}
			}
			var title = BX.create("span", { attrs: {'title': file.name}, props : { className: "bx-messenger-file-title"}, children: [
				BX.create("span", { props : { className: "bx-messenger-file-title-name"}, html: fileName})
			]});
			if (enableLink && (file.urlShow[urlContext] || file.urlDownload[urlContext]))
			{
				if (this.isMobile())
					title = BX.create("span", { props : { className: "bx-messenger-file-title-href"}, events: {click: function(){ BX.localStorage.set('impmh', true, 1);  app.openDocument({url: file.urlDownload['mobile'], filename: fileName}) }}, children: [title]});
				else
					title = BX.create("a", { props : { className: "bx-messenger-file-title-href"}, attrs: {'href': file.urlShow? file.urlShow[urlContext]: file.urlDownload[urlContext], 'target': '_blank'}, children: [title]});
			}
			title = BX.create("div", { props : { className: "bx-messenger-file-attrs"}, children: [
				title,
				BX.create("span", { props : { className: "bx-messenger-file-size"}, html: BX.UploaderUtils.getFormattedSize(file.size)})
			]});

			var status = null;
			if (file.status == 'done')
			{
				if (!this.isMobile())
				{
					status = BX.create("div", { props : { className: "bx-messenger-file-download"}, children: [
						!file.urlDownload || !enableLink? null: BX.create("a", {attrs: {'href': file.urlDownload[urlContext], 'target': '_blank'}, props : { className: "bx-messenger-file-download-link bx-messenger-file-download-pc"}, html: BX.message('IM_F_DOWNLOAD')}),
						!file.urlDownload || !this.BXIM.disk.enable? null: BX.create("span", { props : { className: "bx-messenger-file-download-link bx-messenger-file-download-disk"}, html: BX.message('IM_F_DOWNLOAD_DISK'), events: {click:BX.delegate(function(){
							var chatId = BX.proxy_context.parentNode.parentNode.getAttribute('data-chatId');
							var fileId = BX.proxy_context.parentNode.parentNode.getAttribute('data-fileId');
							var boxId = BX.proxy_context.parentNode.parentNode.getAttribute('data-boxId');
							this.BXIM.disk.saveToDisk(chatId, fileId, {boxId: boxId});
						}, this)}})
					]});
				}
				else
				{
					status = BX.create("div", { props : { className: "bx-messenger-file-download"}, children: []});
				}
			}
			else if (file.status == 'upload')
			{
				var statusStyles = {};
				var styles2 = '';
				var statusDelete = null;
				var statusClassName = '';
				var statusTitle = '';
				if (file.authorId == this.BXIM.userId && file.progress >= 0)
				{
					statusTitle = BX.message('IM_F_UPLOAD_2').replace('#PERCENT#', file.progress);
					statusStyles = { width: file.progress+'%' };
					statusDelete = BX.create("span", { attrs: {title: BX.message('IM_F_CANCEL')}, props : { className: "bx-messenger-file-delete"}})
				}
				else
				{
					statusTitle = BX.message('IM_F_UPLOAD');
					statusClassName = " bx-messenger-file-progress-infinite";
				}
				status = BX.create("div", { props : { className: "bx-messenger-progress-box"}, children: [
					BX.create("span", { attrs: {title: statusTitle}, props : { className: "bx-messenger-file-progress"}, children: [
						BX.create("span", { props : { className: "bx-messenger-file-progress-line"+statusClassName}, style : statusStyles})
					]}),
					statusDelete
				]});
			}
			else if (file.status == 'error')
			{
				status = BX.create("span", { props : { className: "bx-messenger-file-status-error"}, html: file.errorText? file.errorText: BX.message('IM_F_ERROR')})
			}

			if (!status)
				return false;

			if (fileIds.length == 1 && params.showInner == 'Y')
			{
				nodeCollection = [preview, title, status];
			}
			else
			{
				var boxId = params.boxId? params.boxId: 'im-file';
				nodeCollection.push(BX.create("div", {
					attrs: { id: boxId+'-'+file.id, 'data-chatId': file.chatId , 'data-fileId': file.id, 'data-boxId': boxId},
					props : { className: "bx-messenger-file"},
					children: [preview, title, status]
				}));
			}
		}

		return nodeCollection
	}

	BX.MessengerCommon.prototype.diskRedrawFile = function(chatId, fileId, params)
	{
		params = params || {};
		var boxId = params.boxId? params.boxId: 'im-file';

		var fileBox = BX(boxId+'-'+fileId);
		if (fileBox)
		{
			var result = this.diskDrawFiles(chatId, fileId, {'showInner': 'Y', 'boxId': boxId});
			if (result)
			{
				fileBox.innerHTML = '';
				BX.adjust(fileBox, {children: result});
			}
		}
	}

	BX.MessengerCommon.prototype.diskChatDialogFileInited = function(id, file, agent)
	{
		var chatId = agent.form.CHAT_ID.value;

		if (!this.BXIM.disk.files[chatId])
			this.BXIM.disk.files[chatId] = {};

		this.BXIM.disk.files[chatId][id] = {
			'id': id,
			'tempId': id,
			'chatId': chatId,
			'date': BX.MessengerCommon.getNowDate(),
			'type': file.isImage? 'image': 'file',
			'preview': file.isImage? file.canvas: '',
			'name': file.name,
			'size': file.file.size,
			'status': 'upload',
			'progress': -1,
			'authorId': this.BXIM.userId,
			'authorName': this.BXIM.messenger.users[this.BXIM.userId].name,
			'urlPreview': '',
			'urlShow': '',
			'urlDownload': ''
		};

		if (!this.BXIM.disk.filesRegister[chatId])
			this.BXIM.disk.filesRegister[chatId] = {};

		this.BXIM.disk.filesRegister[chatId][id] = {
			'id': id,
			'type': this.BXIM.disk.files[chatId][id].type,
			'mimeType': file.file.type,
			'name': this.BXIM.disk.files[chatId][id].name,
			'size': this.BXIM.disk.files[chatId][id].size
		};

		this.diskChatDialogFileRegister(chatId);

	}

	BX.MessengerCommon.prototype.diskChatDialogFileRegister = function(chatId)
	{
		clearTimeout(this.BXIM.disk.timeout[chatId]);
		this.BXIM.disk.timeout[chatId] = setTimeout(BX.delegate(function(){
			var recipientId = 0;
			if (this.BXIM.messenger.chat[chatId] && this.BXIM.messenger.chat[chatId].type != 'private')
			{
				recipientId = 'chat'+chatId;
			}
			else
			{
				for (var userId in this.BXIM.messenger.userChat)
				{
					if (this.BXIM.messenger.userChat[userId] == chatId)
					{
						recipientId = userId;
						break;
					}
				}
			}
			if (!recipientId)
				return false;

			var paramsFileId = []
			for (var id in this.BXIM.disk.filesRegister[chatId])
			{
				paramsFileId.push(id);
			}
			var tmpMessageId = 'tempFile'+this.BXIM.disk.fileTmpId;
			this.BXIM.messenger.message[tmpMessageId] = {
				'id': tmpMessageId,
				'chatId': chatId,
				'senderId': this.BXIM.userId,
				'recipientId': recipientId,
				'date': BX.MessengerCommon.getNowDate(),
				'text': '',
				'params': {'FILE_ID': paramsFileId}
			};
			if (!this.BXIM.messenger.showMessage[recipientId])
				this.BXIM.messenger.showMessage[recipientId] = [];

			this.BXIM.messenger.showMessage[recipientId].push(tmpMessageId);
			BX.MessengerCommon.drawMessage(recipientId, this.BXIM.messenger.message[tmpMessageId]);
			BX.MessengerCommon.drawProgessMessage(tmpMessageId);

			this.recentListAdd({
				'id': tmpMessageId,
				'date': BX.MessengerCommon.getNowDate()+parseInt(BX.message("SERVER_TZ_OFFSET")),
				'skipDateCheck': true,
				'recipientId': recipientId,
				'senderId': this.BXIM.userId,
				'text': '['+BX.message('IM_F_FILE')+']',
				'userId': recipientId,
				'params': {}
			}, true);

			this.BXIM.messenger.sendMessageFlag++;
			this.BXIM.messenger.popupMessengerFileFormInput.setAttribute('disabled', true);

			this.BXIM.disk.OldBeforeUnload = window.onbeforeunload;
			window.onbeforeunload = function(){
				if (typeof(BX.PULL) != 'undefined' && typeof(BX.PULL.tryConnectDelay) == 'function') // TODO change to right code in near future (e.shelenkov)
				{
					BX.PULL.tryConnectDelay();
				}
				return BX.message('IM_F_EFP')
			};

			BX.ajax({
				url: this.BXIM.pathToFileAjax+'?FILE_REGISTER&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_FILE_REGISTER' : 'Y', CHAT_ID: chatId, RECIPIENT_ID: recipientId, MESSAGE_TMP_ID: tmpMessageId, FILES: JSON.stringify(this.BXIM.disk.filesRegister[chatId]), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data) {
					if (data.ERROR != '')
					{
						this.BXIM.messenger.sendMessageFlag--;
						delete this.BXIM.messenger.message[tmpMessageId];
						BX.MessengerCommon.drawTab(recipientId);
						window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;

						this.BXIM.disk.filesRegister[chatId] = {};

						if (this.BXIM.disk.formAgents['imDialog']["clear"])
							this.BXIM.disk.formAgents['imDialog'].clear();

						return false;
					}

					this.BXIM.messenger.sendMessageFlag--;
					var messagefileId = [];
					var filesProgress = {};
					for(var tmpId in data.FILE_ID)
					{
						var newFile = data.FILE_ID[tmpId];

						delete this.BXIM.disk.filesRegister[data.CHAT_ID][newFile.TMP_ID];

						if (parseInt(newFile.FILE_ID) > 0)
						{
							filesProgress[newFile.TMP_ID] = newFile.FILE_ID;
							this.BXIM.disk.filesProgress[newFile.TMP_ID] = newFile.FILE_ID;
							this.BXIM.disk.filesMessage[newFile.TMP_ID] = data.MESSAGE_ID;

							this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID] = {};
							for (var key in this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID])
								this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID][key] = this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID][key];
							this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID]['id'] = newFile.FILE_ID;
							delete this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID];

							this.BXIM.disk.files[data.CHAT_ID][newFile.FILE_ID]['name'] = newFile.FILE_NAME;
							if (BX('im-file-'+newFile.TMP_ID))
							{
								BX('im-file-'+newFile.TMP_ID).setAttribute('data-fileId', newFile.FILE_ID);
								BX('im-file-'+newFile.TMP_ID).id = 'im-file-'+newFile.FILE_ID;
								BX.MessengerCommon.diskRedrawFile(data.CHAT_ID, newFile.FILE_ID);
							}

							messagefileId.push(newFile.FILE_ID);
						}
						else
						{
							this.BXIM.disk.files[data.CHAT_ID][newFile.TMP_ID]['status'] = 'error';
							BX.MessengerCommon.diskRedrawFile(data.CHAT_ID, newFile.TMP_ID);
						}
					}

					this.BXIM.messenger.message[data.MESSAGE_ID] = BX.clone(this.BXIM.messenger.message[data.MESSAGE_TMP_ID]);
					this.BXIM.messenger.message[data.MESSAGE_ID]['id'] = data.MESSAGE_ID;
					this.BXIM.messenger.message[data.MESSAGE_ID]['params']['FILE_ID'] = messagefileId;

					if (this.BXIM.messenger.popupMessengerLastMessage == data.MESSAGE_TMP_ID)
						this.BXIM.messenger.popupMessengerLastMessage = data.MESSAGE_ID;

					delete this.BXIM.messenger.message[data.MESSAGE_TMP_ID];

					var idx = BX.util.array_search(''+data.MESSAGE_TMP_ID+'', this.BXIM.messenger.showMessage[data.RECIPIENT_ID]);
					if (this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx])
						this.BXIM.messenger.showMessage[data.RECIPIENT_ID][idx] = ''+data.MESSAGE_ID+'';

					if (BX('im-message-'+data.MESSAGE_TMP_ID))
					{
						BX('im-message-'+data.MESSAGE_TMP_ID).id = 'im-message-'+data.MESSAGE_ID;
						var element = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.MESSAGE_TMP_ID}}, true);
						if (element)
						{
							element.setAttribute('data-messageid',	''+data.MESSAGE_ID+'');
							if (element.getAttribute('data-blockmessageid') == ''+data.MESSAGE_TMP_ID)
								element.setAttribute('data-blockmessageid',	''+data.MESSAGE_ID+'');
						}
						else
						{
							var element2 = BX.findChild(this.BXIM.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+data.MESSAGE_TMP_ID}}, true);
							if (element2)
							{
								element2.setAttribute('data-blockmessageid', ''+data.MESSAGE_ID+'');
							}
						}
						var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
						if (lastMessageElementDate)
							lastMessageElementDate.innerHTML = ' &nbsp; '+BX.MessengerCommon.formatDate(this.BXIM.messenger.message[data.MESSAGE_ID].date, BX.MessengerCommon.getDateFormatType('MESSAGE'));
					}
					BX.MessengerCommon.clearProgessMessage(data.MESSAGE_ID);

					if (this.BXIM.messenger.history[data.RECIPIENT_ID])
						this.BXIM.messenger.history[data.RECIPIENT_ID].push(data.MESSAGE_ID);
					else
						this.BXIM.messenger.history[data.RECIPIENT_ID] = [data.MESSAGE_ID];

					this.BXIM.messenger.popupMessengerFileFormRegChatId.value = data.CHAT_ID;
					this.BXIM.messenger.popupMessengerFileFormRegMessageId.value = data.MESSAGE_ID;
					this.BXIM.messenger.popupMessengerFileFormRegParams.value = JSON.stringify(filesProgress);

					this.BXIM.disk.formAgents['imDialog'].submit();

					this.BXIM.messenger.popupMessengerFileFormInput.removeAttribute('disabled');
				}, this),
				onfailure: BX.delegate(function(){
					this.BXIM.messenger.sendMessageFlag--;
					delete this.BXIM.messenger.message[tmpMessageId];
					this.BXIM.disk.filesRegister[chatId] = {};

					BX.MessengerCommon.drawTab(recipientId);
					window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;

					if (this.BXIM.disk.formAgents['imDialog']["clear"])
						this.BXIM.disk.formAgents['imDialog'].clear();

				}, this)
			});
			this.BXIM.disk.fileTmpId++;
		}, this), 500);
	}

	BX.MessengerCommon.prototype.diskChatDialogFileStart = function(status, percent, agent, pIndex)
	{
		var fileId = this.BXIM.disk.filesProgress[status.id];
		var formFields = agent.streams.packages.getItem(pIndex).data;
		if (!this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId])
			return false;

		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].progress = parseInt(percent);
		BX.MessengerCommon.diskRedrawFile(formFields.REG_CHAT_ID, fileId);
	}

	BX.MessengerCommon.prototype.diskChatDialogFileProgress = function(status, percent, agent, pIndex)
	{
		var fileId = this.BXIM.disk.filesProgress[status.id];
		var formFields = agent.streams.packages.getItem(pIndex).data;
		if (!this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId])
			return false;

		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].progress = parseInt(percent);
		BX.MessengerCommon.diskRedrawFile(formFields.REG_CHAT_ID, fileId);
	}

	BX.MessengerCommon.prototype.diskChatDialogFileDone = function(status, file, agent, pIndex)
	{
		if (!this.BXIM.disk.files[file.file.fileChatId][file.file.fileId])
			return false;

		if (this.BXIM.disk.files[file.file.fileChatId] && this.BXIM.disk.files[file.file.fileChatId][file.file.fileId])
		{
			file.file.fileParams['preview'] = this.BXIM.disk.files[file.file.fileChatId][file.file.fileId]['preview'];
		}
		if (!this.BXIM.disk.files[file.file.fileChatId])
			this.BXIM.disk.files[file.file.fileChatId] = {};
		this.BXIM.disk.files[file.file.fileChatId][file.file.fileId] = file.file.fileParams;
		BX.MessengerCommon.diskRedrawFile(file.file.fileChatId, file.file.fileId);

		delete this.BXIM.disk.filesMessage[file.file.fileTmpId];
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
	}

	BX.MessengerCommon.prototype.diskChatDialogFileError = function(item, file, agent, pIndex)
	{
		var fileId = this.BXIM.disk.filesProgress[item.id];
		var formFields = agent.streams.packages.getItem(pIndex).data;
		if (!this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId])
			return false;

		item.deleteFile();

		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].status = "error";
		this.BXIM.disk.files[formFields.REG_CHAT_ID][fileId].errorText = file.error;
		BX.MessengerCommon.diskRedrawFile(formFields.REG_CHAT_ID, fileId);
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
	}

	BX.MessengerCommon.prototype.diskChatDialogUploadError = function(stream, pIndex, data)
	{
		var files = JSON.parse(stream.post.REG_PARAMS);
		var messages = {};
		for (var tmpId in files)
		{
			if (this.BXIM.disk.filesMessage[tmpId])
			{
				delete this.BXIM.disk.filesMessage[tmpId];
			}
			if (this.BXIM.disk.filesRegister[stream.post.REG_CHAT_ID])
			{
				delete this.BXIM.disk.filesRegister[stream.post.REG_CHAT_ID][tmpId];
				delete this.BXIM.disk.filesRegister[stream.post.REG_CHAT_ID][files[tmpId]];
			}
			if (this.BXIM.disk.files[stream.post.REG_CHAT_ID])
			{
				if (this.BXIM.disk.files[stream.post.REG_CHAT_ID][files[tmpId]])
				{
					this.BXIM.disk.files[stream.post.REG_CHAT_ID][files[tmpId]].status = 'error';
					BX.MessengerCommon.diskRedrawFile(stream.post.REG_CHAT_ID, files[tmpId]);
				}
				if (this.BXIM.disk.files[stream.post.REG_CHAT_ID][tmpId])
				{
					this.BXIM.disk.files[stream.post.REG_CHAT_ID][tmpId].status = 'error';
					BX.MessengerCommon.diskRedrawFile(stream.post.REG_CHAT_ID, tmpId);
				}

			}
			delete this.BXIM.disk.filesProgress[tmpId];
		}
		BX.ajax({
			url: this.BXIM.pathToFileAjax+'?FILE_UNREGISTER&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_FILE_UNREGISTER' : 'Y', CHAT_ID: stream.post.REG_CHAT_ID, FILES: stream.post.REG_PARAMS, MESSAGES: JSON.stringify(messages), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		});
		window.onbeforeunload = this.BXIM.disk.OldBeforeUnload;
		BX.MessengerCommon.drawTab(this.getRecipientByChatId(stream.post.REG_CHAT_ID));
	}


	/* Section: Telephony */
	BX.MessengerCommon.prototype.pullPhoneEvent = function()
	{
		BX.addCustomEvent((this.isMobile()? "onPull-voximplant": "onPullEvent-voximplant"), BX.delegate(function(command,params)
		{
			if (this.isMobile())
			{
				params = command.params;
				command = command.command;
				console.info('pull info: ', command, params);
			}

			if (command == 'invite')
			{
				if (this.isMobile() && params['PULL_TIME_AGO'] && params['PULL_TIME_AGO'] > 30)
					return false;

				if (BX.localStorage.get('viInitedCall'))
					return false;

				if (false && this.BXIM.webrtc.callInit && !this.BXIM.webrtc.callActive && params.typeConnect != 'queue') // for future
				{
					clearInterval(this.BXIM.webrtc.phoneConnectedInterval);
					BX.localStorage.remove('viInitedCall');

					if (!this.isMobile())
					{
						this.BXIM.stopRepeatSound('ringtone');
						this.BXIM.stopRepeatSound('dialtone');
					}

					this.BXIM.webrtc.callInit = false;

					this.BXIM.webrtc.phoneCallFinish();
					this.BXIM.webrtc.callAbort();
					this.BXIM.webrtc.callOverlayClose(false);
				}

				if (this.BXIM.webrtc.callInit || this.BXIM.webrtc.callActive)
					return false;

				if (this.isMobile() || this.BXIM.desktop.ready() || !this.BXIM.desktop.ready() && !this.BXIM.desktopStatus || this.BXIM.desktop.run() && !this.BXIM.desktop.ready() && this.BXIM.desktopStatus)
				{
					if (params.CRM && params.CRM.FOUND)
					{
						this.BXIM.webrtc.phoneCrm = params.CRM;
					}
					else
					{
						this.BXIM.webrtc.phoneCrm = {};
					}

					this.BXIM.webrtc.phonePortalCall = params.portalCall? true: false;
					if (this.BXIM.webrtc.phonePortalCall && params.portalCallData)
					{
						for (var i in params.portalCallData.users)
							this.BXIM.messenger.users[i] = params.portalCallData.users[i];

						for (var i in params.portalCallData.hrphoto)
							this.BXIM.messenger.hrphoto[i] = params.portalCallData.hrphoto[i];

						params.callerId = this.BXIM.messenger.users[params.portalCallUserId].name;
						params.phoneNumber = '';

						if (this.isMobile())
						{
							this.BXIM.webrtc.phoneCrm.FOUND = 'Y';
							this.BXIM.webrtc.phoneCrm.CONTACT = {
								'NAME': params.portalCallData.users[params.portalCallUserId].name,
								'PHOTO': params.portalCallData.users[params.portalCallUserId].avatar
							};
						}
					}

					this.BXIM.webrtc.phoneCallConfig = params.config? params.config: {};
					this.BXIM.webrtc.phoneCallTime = 0;

					this.BXIM.repeatSound('ringtone', 5000);

					if (!this.isMobile() && this.BXIM.desktop.run())
					{
						BX.desktop.changeTab('im');
					}

					BX.MessengerCommon.phoneCommand('wait', {'CALL_ID' : params.callId});

					this.BXIM.webrtc.phoneIncomingWait(params.chatId, params.callId, params.callerId, params.phoneNumber);
				}
				console.log('isMobile', this.isMobile()?'Y':'N', 'desktopReady', this.BXIM.desktop && this.BXIM.desktop.ready()?'Y':'N', 'isFocus', this.BXIM.isFocus('all')? 'Y':'N');
				if (!this.isMobile() && this.BXIM.desktop.ready() && !this.BXIM.isFocus('all'))
				{
					var data = {'users' : {}, 'chat' : {}, 'userInChat' : {}, 'hrphoto' : {},  'phoneCrm': params.CRM};
					this.BXIM.desktop.openTopmostWindow("callNotifyWaitDesktop", "BXIM.webrtc.phoneIncomingWaitDesktop("+params.chatId+",'"+params.callId+"', '"+params.callerId+"', '"+params.phoneNumber+"', true);", data, 'im-desktop-call');
				}
			}
			else if (command == 'answer_self')
			{
				if (this.BXIM.webrtc.callSelfDisabled || this.BXIM.webrtc.phoneCallId != params.callId)
					return false;

				this.BXIM.stopRepeatSound('ringtone');
				this.BXIM.stopRepeatSound('dialtone');

				this.BXIM.webrtc.callInit = false;
				this.BXIM.webrtc.phoneCallFinish();
				this.BXIM.webrtc.callAbort();

				this.BXIM.webrtc.callOverlayClose(true);

				this.BXIM.webrtc.callInit = true;
				this.BXIM.webrtc.phoneCallId = params.callId;
			}
			else if (command == 'timeout')
			{
				if (this.BXIM.webrtc.phoneCallId != params.callId)
					return false;

				clearInterval(this.BXIM.webrtc.phoneConnectedInterval);
				BX.localStorage.remove('viInitedCall');

				var external = this.BXIM.webrtc.phoneCallExternal;

				this.BXIM.stopRepeatSound('ringtone');
				this.BXIM.stopRepeatSound('dialtone');

				this.BXIM.webrtc.callInit = false;

				var phoneNumber = this.BXIM.webrtc.phoneNumber;
				this.BXIM.webrtc.phoneCallFinish();
				this.BXIM.webrtc.callAbort();

				if (external && params.failedCode == 486)
				{
					this.BXIM.webrtc.callOverlayProgress('offline');
					this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_ERROR_BUSY_PHONE'));
					if (this.isMobile())
					{
						this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.CALLBACK);
					}
					else
					{
						this.BXIM.webrtc.callOverlayButtons(this.BXIM.webrtc.buttonsOverlayClose);
					}
				}
				else if (external && params.failedCode == 480)
				{
					this.BXIM.webrtc.callOverlayProgress('error');
					this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_ERROR_NA_PHONE'));
					if (this.isMobile())
					{
						this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
					}
					else
					{
						this.BXIM.webrtc.callOverlayButtons([
							{
								title: BX.message(this.BXIM.webrtc.phoneDeviceCall()? 'IM_M_CALL_BTN_DEVICE_TITLE': 'IM_M_CALL_BTN_DEVICE_OFF_TITLE'),
								id: 'bx-messenger-call-overlay-button-device-error',
								className: 'bx-messenger-call-overlay-button-device'+(this.BXIM.webrtc.phoneDeviceCall()? '': ' bx-messenger-call-overlay-button-device-off'),
								events: {
									click : BX.delegate(function (){
										this.BXIM.webrtc.phoneCallFinish();
										this.BXIM.webrtc.callAbort();
										this.BXIM.webrtc.phoneDeviceCall(!this.BXIM.webrtc.phoneDeviceCall());
										this.BXIM.webrtc.phoneCall(phoneNumber);
									}, this)
								},
								hide: this.BXIM.webrtc.phoneDeviceActive && this.BXIM.webrtc.enabled? false: true
							},
							{
								text: BX.message('IM_M_CALL_BTN_CLOSE'),
								className: 'bx-messenger-call-overlay-button-close',
								events: {
									click : BX.delegate(function() {
										this.BXIM.webrtc.callOverlayClose();
									}, this)
								}
							}
						]);
					}
				}
				else
				{
					if (this.isMobile())
					{
						this.BXIM.webrtc.callOverlayProgress('error');
						this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_DECLINE'));
						this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
					}
					else
					{
						this.BXIM.webrtc.callOverlayClose(false);
					}
				}
			}
			else if (command == 'outgoing')
			{
				if (this.isMobile() && params['PULL_TIME_AGO'] && params['PULL_TIME_AGO'] > 30)
					return false;

				if (!this.isMobile() && this.BXIM.desktopStatus && !this.BXIM.desktop.ready())
					return false;

				if (!this.isMobile() && this.BXIM.desktop.ready())
				{
					BX.desktop.changeTab('im');
					BX.desktop.windowCommand("show");
				}

				this.BXIM.webrtc.phoneCallDevice = params.callDevice == 'PHONE'? 'PHONE': 'WEBRTC';
				this.BXIM.webrtc.phonePortalCall = params.portalCall? true: false;
				if (this.BXIM.webrtc.callInit && (this.BXIM.webrtc.phoneNumber == params.phoneNumber || params.phoneNumber.indexOf(this.BXIM.webrtc.phoneNumber) >= 0))
				{
					this.BXIM.webrtc.phoneNumber = params.phoneNumber;
					if (params.external && this.BXIM.webrtc.phoneCallId == params.callIdTmp || !this.BXIM.webrtc.phoneCallId)
					{
						this.BXIM.webrtc.phoneCallExternal = params.external? true: false;

						if (this.BXIM.webrtc.phoneCallExternal && this.BXIM.webrtc.phoneCallDevice == 'PHONE')
						{
							if (!this.BXIM.webrtc.phoneCallId)
							{
								this.BXIM.webrtc.callOverlayProgress('wait');
								this.BXIM.webrtc.callOverlayStatus(BX.message('IM_M_CALL_ST_WAIT_PHONE'));

								if (!this.isMobile() && this.BXIM.desktop.ready())
								{
									BX.desktop.changeTab('im');
									BX.desktop.windowCommand("show");
									this.BXIM.desktop.closeTopmostWindow();
								}
							}
							else
							{
								this.BXIM.webrtc.callOverlayProgress('connect');
								this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_WAIT_ANSWER'));
							}
						}

						this.BXIM.webrtc.phoneCallConfig = params.config? params.config: {};
						this.BXIM.webrtc.phoneCallId = params.callId;
						this.BXIM.webrtc.phoneCallTime = 0;
						this.BXIM.webrtc.phoneCrm = params.CRM;

						if (this.BXIM.webrtc.phonePortalCall && this.BXIM.messenger.users[params.portalCallUserId])
						{
							if (this.isMobile())
							{
								this.BXIM.webrtc.phoneCrm.FOUND = 'Y';
								this.BXIM.webrtc.phoneCrm.CONTACT = {
									'NAME': params.portalCallData.users[params.portalCallUserId].name,
									'PHOTO': params.portalCallData.users[params.portalCallUserId].avatar
								};
							}
							else
							{
								this.BXIM.webrtc.callOverlayTitleBlock.innerHTML = BX.message("IM_M_CALL_VOICE_TO").replace('#USER#', this.BXIM.messenger.users[params.portalCallUserId].name)
							}
						}
					}
					this.BXIM.webrtc.callOverlayDrawCrm();
					if (this.BXIM.webrtc.callNotify)
						this.BXIM.webrtc.callNotify.adjustPosition();
				}
				else if (!this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneCallDevice == 'PHONE')
				{
					this.BXIM.webrtc.phoneCallInvite(params.phoneNumber);

					this.BXIM.webrtc.phoneCallId = params.callId;
					this.BXIM.webrtc.phoneCallTime = 0;
					this.BXIM.webrtc.phoneCallConfig = params.config? params.config: {};
					this.BXIM.webrtc.phoneCrm = params.CRM;

					this.BXIM.webrtc.callOverlayDrawCrm();
					if (this.BXIM.webrtc.callNotify)
						this.BXIM.webrtc.callNotify.adjustPosition();
				}
			}
			else if (command == 'start')
			{
				this.BXIM.webrtc.callOverlayTimer('start');
				this.BXIM.stopRepeatSound('ringtone');
				if (this.BXIM.webrtc.phoneCallId == params.callId && this.BXIM.webrtc.phoneCallDevice == 'PHONE' && (this.BXIM.webrtc.phoneCallDevice == params.callDevice || this.BXIM.webrtc.phonePortalCall))
				{
					this.BXIM.webrtc.phoneOnCallConnected();
				}
				else if (this.BXIM.webrtc.phoneCallId == params.callId && params.callDevice == 'PHONE' && this.BXIM.webrtc.phoneIncoming)
				{
					if (!this.isMobile())
					{
						if (this.BXIM.desktop.ready())
						{
							BX.desktop.changeTab('im');
							BX.desktop.windowCommand("show");
						}
						this.BXIM.messenger.openMessenger(this.BXIM.messenger.currentTab);
					}
					this.BXIM.webrtc.phoneCallDevice = 'PHONE';
					this.BXIM.webrtc.phoneOnCallConnected();
				}
				if (params.CRM)
				{
					this.BXIM.webrtc.phoneCrm = params.CRM;
					this.BXIM.webrtc.callOverlayDrawCrm();
				}

				if (this.BXIM.webrtc.phoneNumber != '')
				{
					this.BXIM.webrtc.phoneNumberLast = this.BXIM.webrtc.phoneNumber;
					this.BXIM.setLocalConfig('phone_last', this.BXIM.webrtc.phoneNumber);
				}
			}
			else if (command == 'hold' || command == 'unhold')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId)
				{
					this.BXIM.webrtc.phoneHolded = command == 'hold';
				}
			}
			else if (command == 'update_crm')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId && params.CRM && params.CRM.FOUND)
				{
					this.BXIM.webrtc.phoneCrm = params.CRM;

					this.BXIM.webrtc.callOverlayDrawCrm();
					if (this.BXIM.webrtc.callNotify)
						this.BXIM.webrtc.callNotify.adjustPosition();
				}
			}
			else if (command == 'inviteTransfer')
			{
				if (this.isMobile()) // TODO MOBILE support transfer
					return false;

				if (this.isMobile() && params['PULL_TIME_AGO'] && params['PULL_TIME_AGO'] > 30)
					return false;

				if (!this.BXIM.webrtc.callInit && !this.BXIM.webrtc.callActive)
				{
					if (this.BXIM.desktop.ready() || !this.BXIM.desktop.ready() && !this.BXIM.desktopStatus || this.BXIM.desktop.run() && !this.BXIM.desktop.ready() && this.BXIM.desktopStatus)
					{
						if (params.CRM && params.CRM.FOUND)
						{
							this.BXIM.webrtc.phoneCrm = params.CRM;
						}
						this.BXIM.repeatSound('ringtone', 5000);
						BX.MessengerCommon.phoneCommand('waitTransfer', {'CALL_ID' : params.callId});
						if (this.BXIM.desktop.run())
							BX.desktop.changeTab('im');

						this.BXIM.webrtc.phoneTransferEnabled = true;

						this.BXIM.webrtc.phoneIncomingWait(params.chatId, params.callId, params.callerId);
					}
					if (this.BXIM.desktop.ready() && !this.BXIM.isFocus('all'))
					{
						var data = {'users' : {}, 'chat' : {}, 'userInChat' : {}, 'hrphoto' : {},  'phoneCrm': params.CRM};
						this.BXIM.desktop.openTopmostWindow("callNotifyWaitDesktop", "BXIM.webrtc.phoneIncomingWaitDesktop("+params.chatId+",'"+params.callId+"', '"+params.callerId+"');", data, 'im-desktop-call');
					}
				}
			}
			else if (command == 'cancelTransfer' || command == 'timeoutTransfer')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId && !this.BXIM.webrtc.callSelfDisabled)
				{
					this.BXIM.webrtc.callInit = false;
					this.BXIM.stopRepeatSound('ringtone');
					this.BXIM.webrtc.phoneCallFinish();
					this.BXIM.webrtc.callAbort();
					this.BXIM.webrtc.callOverlayClose();
				}
			}
			else if (command == 'declineTransfer')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId)
				{
					this.BXIM.webrtc.errorInviteTransfer();
				}
			}
			else if (command == 'completeTransfer')
			{
				if (this.BXIM.webrtc.phoneCallId == params.callId)
				{
					if (params.transferUserId != this.BXIM.userId || this.isMobile())
					{
						this.BXIM.webrtc.successInviteTransfer();
					}
					else
					{
						this.BXIM.webrtc.phoneTransferEnabled = false;
						BX.localStorage.set('vite', false, 1);

						if (params.callDevice == 'PHONE')
						{
							this.BXIM.stopRepeatSound('ringtone');
							if (this.BXIM.desktop.ready())
							{
								BX.desktop.changeTab('im');
								BX.desktop.windowCommand("show");
							}
							if (this.isMobile())
							{
								this.BXIM.messenger.openMessenger(this.BXIM.messenger.currentTab);
							}
							this.BXIM.webrtc.phoneCallDevice = 'PHONE';
							this.BXIM.webrtc.phoneOnCallConnected();
						}
						if (params.CRM)
						{
							this.BXIM.webrtc.phoneCrm = params.CRM;
							this.BXIM.webrtc.callOverlayDrawCrm();
						}
					}
				}
			}
			else if (command == 'phoneDeviceActive')
			{
				 this.BXIM.webrtc.phoneDeviceActive = params.active == 'Y';
			}
		}, this));
	}

	BX.MessengerCommon.prototype.phoneCommand = function(command, params, async)
	{
		if (!this.BXIM.webrtc.phoneSupport())
		return false;

		async = async != false;
		params = typeof(params) == 'object' ? params: {};

		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?PHONE_SHARED&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			async: async,
			data: {'IM_PHONE' : 'Y', 'COMMAND': command, 'PARAMS' : JSON.stringify(params), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		});

		return true;
	}

	BX.MessengerCommon.prototype.phoneCorrect = function(number)
	{
		number = BX.util.trim(number.toString());

		if (number.substr(0, 2) == '+8' && number.length > 10)
		{
			number = '008'+number.substr(2);
		}
		//number = number.replace(/[^0-9\#\*]/g, ''); TODO support * and # for call
		number = number.replace(/[^0-9]/g, '');

		if (number.substr(0, 2) == '80' || number.substr(0, 2) == '81' || number.substr(0, 2) == '82')
		{
		}
		else if (number.substr(0, 2) == '00' && number.length >= 9)
		{
			number = number.substr(2);
		}
		else if (number.substr(0, 3) == '011' && number.length >= 10)
		{
			number = number.substr(3);
		}
		else if (number.substr(0, 1) == '8' && number.length >= 11)
		{
			number = '7'+number.substr(1);
		}
		else if (number.substr(0, 1) == '0' && number.length >= 8)
		{
			number = number.substr(1);
		}

		return number;
	}

	BX.MessengerCommon.prototype.phoneOnIncomingCall = function(params)
	{
		if (this.BXIM.webrtc.phoneCurrentCall)
			return false;

		var viEvent = {};
		if (this.isMobile())
		{
			viEvent = BX.MobileVoximplantCall.events;
		}
		else
		{
			viEvent = VoxImplant.CallEvents;
		}

		this.BXIM.webrtc.phoneCurrentCall = params.call;
		this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Connected, BX.delegate(this.BXIM.webrtc.phoneOnCallConnected, this.BXIM.webrtc));
		this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Disconnected, BX.delegate(this.BXIM.webrtc.phoneOnCallDisconnected, this.BXIM.webrtc));
		this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Failed, BX.delegate(this.BXIM.webrtc.phoneOnCallFailed, this.BXIM.webrtc));
		this.BXIM.webrtc.phoneCurrentCall.answer();
	}

	BX.MessengerCommon.prototype.phoneCallStart = function()
	{
		this.BXIM.webrtc.phoneParams['CALLER_ID'] = '';
		this.BXIM.webrtc.phoneParams['USER_ID'] = this.BXIM.userId;
		this.BXIM.webrtc.phoneLog('Call params: ', this.BXIM.webrtc.phoneNumber, this.BXIM.webrtc.phoneParams);
		if (!this.BXIM.webrtc.phoneAPI.connected())
		{
			this.BXIM.webrtc.phoneOnSDKReady();
			return false;
		}

		if (!this.isMobile() && false) // TODO debug mode for testing interface
		{
			this.BXIM.webrtc.phoneCurrentCall = true;
			this.BXIM.webrtc.callActive = true;
			this.BXIM.webrtc.phoneOnCallConnected();
			this.BXIM.webrtc.phoneCrm.FOUND = 'N';
			this.BXIM.webrtc.phoneCrm.CONTACT_URL = '#';
			this.BXIM.webrtc.phoneCrm.LEAD_URL = '#';
			this.BXIM.webrtc.callOverlayDrawCrm();
		}
		else
		{
			var viEvent = {};
			if (this.isMobile())
			{
				viEvent = BX.MobileVoximplantCall.events;
			}
			else
			{
				viEvent = VoxImplant.CallEvents;
				this.BXIM.webrtc.phoneAPI.setOperatorACDStatus('ONLINE');
			}

			this.BXIM.webrtc.phoneCurrentCall = this.BXIM.webrtc.phoneAPI.call(this.BXIM.webrtc.phoneNumber, false, JSON.stringify(this.BXIM.webrtc.phoneParams));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Connected, BX.delegate(this.BXIM.webrtc.phoneOnCallConnected, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Disconnected, BX.delegate(this.BXIM.webrtc.phoneOnCallDisconnected, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.Failed, BX.delegate(this.BXIM.webrtc.phoneOnCallFailed, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.ProgressToneStart, BX.delegate(this.BXIM.webrtc.phoneOnProgressToneStart, this.BXIM.webrtc));
			this.BXIM.webrtc.phoneCurrentCall.addEventListener(viEvent.ProgressToneStop, BX.delegate(this.BXIM.webrtc.phoneOnProgressToneStop, this.BXIM.webrtc));
			if (this.isMobile())
			{
				this.BXIM.webrtc.phoneCurrentCall.start();
			}
		}

		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?PHONE_INIT&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_PHONE' : 'Y', 'COMMAND': 'init', 'NUMBER' : this.BXIM.webrtc.phoneNumber, 'NUMBER_USER' : BX.util.htmlspecialcharsback(this.BXIM.webrtc.phoneNumberUser), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				if (data.ERROR == '')
				{
					if (!(data.HR_PHOTO.length == 0))
					{
						for (var i in data.HR_PHOTO)
							this.BXIM.messenger.hrphoto[i] = data.HR_PHOTO[i];

						if (!this.isMobile())
						{
							this.BXIM.webrtc.callOverlayPhotoCompanion.setAttribute('data-userId', this.BXIM.webrtc.callOverlayUserId);
						}
						this.BXIM.webrtc.callOverlayUserId = data.DIALOG_ID;
						this.BXIM.webrtc.callOverlayUpdatePhoto();
					}
					else
					{
						this.BXIM.webrtc.callOverlayChatId = data.DIALOG_ID.substr(4);
					}
					if (!this.isMobile())
					{
						this.BXIM.messenger.openMessenger(data.DIALOG_ID);
						this.BXIM.webrtc.callOverlayToggleSize(false);
					}
				}
			}, this)
		});
	}

	BX.MessengerCommon.prototype.phoneCallFinish = function()
	{
		clearInterval(this.BXIM.webrtc.phoneConnectedInterval);
		clearInterval(this.BXIM.webrtc.phoneCallTimeInterval);

		this.BXIM.webrtc.callOverlayTimer('pause');

		if (this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneCallDevice == 'PHONE')
		{
			BX.MessengerCommon.phoneCommand('deviceHungup', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}
		else if (this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneTransferEnabled && this.BXIM.webrtc.phoneTransferUser == 0)
		{
			BX.MessengerCommon.phoneCommand('declineTransfer', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}
		else if (this.BXIM.webrtc.callInit && this.BXIM.webrtc.phoneIncoming)
		{
			BX.MessengerCommon.phoneCommand('skip', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
		}

		if (!this.isMobile())
		{
			this.BXIM.desktop.closeTopmostWindow();
		}

		if (this.BXIM.webrtc.phoneCurrentCall)
		{
			try { this.BXIM.webrtc.phoneCurrentCall.hangup(); } catch (e) {}
			this.BXIM.webrtc.phoneCurrentCall = null;
			this.BXIM.webrtc.phoneLog('Call hangup call');
		}
		else if (this.BXIM.webrtc.phoneDisconnectAfterCallFlag && this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
		{
			setTimeout(BX.delegate(function(){
				if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
					this.BXIM.webrtc.phoneAPI.disconnect();
			}, this), 500)
		}

		if (this.isMobile())
		{}
		else
		{
			if (this.BXIM.webrtc.popupKeyPad)
				this.BXIM.webrtc.popupKeyPad.close();
			if (this.BXIM.webrtc.popupTransferDialog)
				this.BXIM.webrtc.popupTransferDialog.close();

			BX.localStorage.set('vite', false, 1);
		}

		this.BXIM.webrtc.phoneRinging = 0;
		this.BXIM.webrtc.phoneIncoming = false;
		this.BXIM.webrtc.phoneCallId = '';
		this.BXIM.webrtc.phoneCallExternal = false;
		this.BXIM.webrtc.phoneCallDevice = 'WEBRTC';
		//this.BXIM.webrtc.phonePortalCall = false;
		this.BXIM.webrtc.phoneNumber = '';
		this.BXIM.webrtc.phoneNumberUser = '';
		this.BXIM.webrtc.phoneParams = {};
		this.BXIM.webrtc.callOverlayOptions = {};
		//this.BXIM.webrtc.phoneCrm = {};
		this.BXIM.webrtc.phoneMicMuted = false;
		this.BXIM.webrtc.phoneHolded = false;
		this.BXIM.webrtc.phoneMicAccess = false;
		this.BXIM.webrtc.phoneTransferUser = 0;
		this.BXIM.webrtc.phoneTransferEnabled = false;
	}

	BX.MessengerCommon.prototype.phoneAuthorize = function()
	{
		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?PHONE_AUTHORIZE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_PHONE' : 'Y', 'COMMAND': 'authorize', 'UPDATE_INFO': this.BXIM.webrtc.phoneCheckBalance? 'Y': 'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					this.BXIM.messenger.sendAjaxTry = 0;
					this.BXIM.webrtc.phoneCheckBalance = false;

					if (data.HR_PHOTO)
					{
						for (var i in data.HR_PHOTO)
							this.BXIM.messenger.hrphoto[i] = data.HR_PHOTO[i];

						this.BXIM.webrtc.callOverlayUpdatePhoto();
					}

					if (this.isMobile())
					{
						this.BXIM.webrtc.phoneLogin = data.LOGIN;
						this.BXIM.webrtc.phoneServer = data.SERVER;

						this.BXIM.webrtc.phoneLog('auth with', this.BXIM.webrtc.phoneLogin+"@"+this.BXIM.webrtc.phoneServer);
						BX.MobileVoximplant.loginWithOneTimeKey(data.LOGIN+'@'+data.SERVER, data.HASH)
					}
					else
					{
						this.BXIM.webrtc.phoneLogin = data.LOGIN;
						this.BXIM.webrtc.phoneServer = data.SERVER;
					}
					this.BXIM.webrtc.phoneCallerID = data.CALLERID;

					this.BXIM.webrtc.phoneApiInit();
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR' && (this.BXIM.desktop.ready() || this.isMobile()) && this.BXIM.messenger.sendAjaxTry < 3)
				{
					this.BXIM.messenger.sendAjaxTry++;
					setTimeout(BX.delegate(function (){
						this.phoneAuthorize();
					}, this), 5000);

					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.BXIM.messenger.sendAjaxTry < 2)
				{
					this.BXIM.messenger.sendAjaxTry++;
					setTimeout(BX.delegate(function(){
						this.phoneAuthorize();
					}, this), 2000);
					BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
				}
				else
				{
					this.BXIM.webrtc.callOverlayDeleteEvents();
					this.BXIM.webrtc.callOverlayProgress('offline');

					this.BXIM.webrtc.phoneLog('onetimekey', data.ERROR, data.CODE);
					if (data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'SESSION_ERROR')
					{
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						this.BXIM.webrtc.callAbort(BX.message('IM_PHONE_401'));
					}
					else
					{
						this.BXIM.webrtc.callAbort(data.ERROR+(this.BXIM.webrtc.debug? '<br />('+BX.message('IM_ERROR_CODE')+': '+data.CODE+')': ''));
					}
					if (!this.isMobile())
					{
						this.BXIM.webrtc.callOverlayButtons(this.BXIM.webrtc.buttonsOverlayClose);
					}
				}
			}, this),
			onfailure: BX.delegate(function() {
				this.BXIM.webrtc.phoneCallFinish();
				this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
			}, this)
		});
	}

	BX.MessengerCommon.prototype.phoneOnAuthResult = function(e)
	{
		if (e.result)
		{
			if (this.BXIM.webrtc.phoneCallDevice == 'PHONE')
				return false;

			this.BXIM.webrtc.phoneLog('Authorize result', 'success');
			if (this.BXIM.webrtc.phoneIncoming)
			{
				BX.MessengerCommon.phoneCommand((this.BXIM.webrtc.phoneTransferEnabled?'readyTransfer': 'ready'), {'CALL_ID': this.BXIM.webrtc.phoneCallId});
			}
			else if (this.BXIM.webrtc.callInitUserId == this.BXIM.userId)
			{
				BX.MessengerCommon.phoneCallStart();
			}
		}
		else if (!this.isMobile() && e.code == 302)
		{
			BX.ajax({
				url: this.BXIM.pathToCallAjax+'?PHONE_ONETIMEKEY&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_PHONE' : 'Y', 'COMMAND': 'onetimekey', 'KEY': e.key, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data)
				{
					if (data.ERROR == '')
					{
						this.BXIM.webrtc.phoneLog('auth with', this.BXIM.webrtc.phoneLogin+"@"+this.BXIM.webrtc.phoneServer);
						this.BXIM.webrtc.phoneAPI.loginWithOneTimeKey(this.BXIM.webrtc.phoneLogin+"@"+this.BXIM.webrtc.phoneServer, data.HASH);
					}
					else
					{
						this.BXIM.webrtc.phoneCallFinish();
						this.BXIM.webrtc.callOverlayProgress('offline');

						this.BXIM.webrtc.phoneLog('onetimekey', data.ERROR, data.CODE);
						if (data.CODE)
							this.BXIM.webrtc.callAbort(BX.message('IM_PHONE_ERROR_CONNECT'));
						else
							this.BXIM.webrtc.callAbort(data.ERROR+(this.debug? '<br />('+BX.message('IM_ERROR_CODE')+': '+data.CODE+')': ''));

						if (!this.isMobile())
						{
							this.BXIM.webrtc.callOverlayButtons(this.BXIM.webrtc.buttonsOverlayClose);
						}
					}
				}, this),
				onfailure: BX.delegate(function() {
					this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
					this.BXIM.webrtc.phoneCallFinish();
				}, this)
			});
		}
		else
		{
			if (e.code == 401 || e.code == 400 || e.code == 403 || e.code == 404 || e.code == 302)
			{
				this.BXIM.webrtc.callAbort(BX.message('IM_PHONE_401'));
				this.BXIM.webrtc.phoneServer = '';
				this.BXIM.webrtc.phoneLogin = '';
				this.BXIM.webrtc.phoneCheckBalance = true;
				BX.MessengerCommon.phoneCommand('authorize_error');
			}
			else
			{
				this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
			}
			this.BXIM.webrtc.callOverlayProgress('offline');
			this.BXIM.webrtc.phoneCallFinish();
			if (!this.isMobile())
			{
				this.BXIM.webrtc.callOverlayButtons(this.BXIM.webrtc.buttonsOverlayClose);
			}
			this.BXIM.webrtc.phoneLog('Authorize result', 'failed', e.code);
			this.BXIM.webrtc.phoneServer = '';
			this.BXIM.webrtc.phoneLogin = '';
		}
	}

	BX.MessengerCommon.prototype.phoneOnCallFailed = function(e)
	{
		this.BXIM.webrtc.phoneLog('Call failed', e.code, e.reason);

		var reason = BX.message('IM_PHONE_END');
		if (e.code == 603)
		{
			reason = BX.message('IM_PHONE_DECLINE');
		}
		else if (e.code == 380)
		{
			reason = BX.message('IM_PHONE_ERR_SIP_LICENSE');
		}
		else if (e.code == 436)
		{
			reason = BX.message('IM_PHONE_ERR_NEED_RENT');
		}
		else if (e.code == 438)
		{
			reason = BX.message('IM_PHONE_ERR_BLOCK_RENT');
		}
		else if (e.code == 400)
		{
			reason = BX.message('IM_PHONE_ERR_LICENSE');
		}
		else if (e.code == 401)
		{
			reason = BX.message('IM_PHONE_401');
		}
		else if (e.code == 480 || e.code == 503)
		{
			if (this.BXIM.webrtc.phoneNumber == 911 || this.BXIM.webrtc.phoneNumber == 112)
			{
				reason = BX.message('IM_PHONE_NO_EMERGENCY');
			}
			else
			{
				reason = BX.message('IM_PHONE_UNAVAILABLE');
			}
		}
		else if (e.code == 484 || e.code == 404)
		{
			if (this.BXIM.webrtc.phoneNumber == 911 || this.BXIM.webrtc.phoneNumber == 112)
			{
				reason = BX.message('IM_PHONE_NO_EMERGENCY');
			}
			else
			{
				reason = BX.message('IM_PHONE_INCOMPLETED');
			}
		}
		else if (e.code == 402)
		{
			reason = BX.message('IM_PHONE_NO_MONEY')+(this.BXIM.bitrix24Admin? '<br />'+BX.message('IM_PHONE_PAY_URL_NEW'): '');
		}
		else if (e.code == 486 && this.BXIM.webrtc.phoneRinging > 1)
		{
			reason = BX.message('IM_M_CALL_ST_DECLINE');
		}
		else if (e.code == 486)
		{
			reason = BX.message('IM_PHONE_ERROR_BUSY');
		}
		else if (e.code == 403)
		{
			reason = BX.message('IM_PHONE_403');
			this.BXIM.webrtc.phoneServer = '';
			this.BXIM.webrtc.phoneLogin = '';
			this.BXIM.webrtc.phoneCheckBalance = true;
		}

		this.BXIM.webrtc.phoneCallFinish();
		if (e.code == 408 || e.code == 403)
		{
			if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
			{
				setTimeout(BX.delegate(function(){
					if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
						this.BXIM.webrtc.phoneAPI.disconnect();
				}, this), 500)
			}
		}
		this.BXIM.webrtc.callOverlayProgress('offline');
		this.BXIM.webrtc.callAbort(reason);

		if (!this.isMobile())
		{
			this.BXIM.webrtc.callOverlayButtons(this.BXIM.webrtc.buttonsOverlayClose);
		}
	}

	BX.MessengerCommon.prototype.phoneOnCallDisconnected = function(e)
	{
		this.BXIM.webrtc.phoneLog('Call disconnected', this.BXIM.webrtc.phoneCurrentCall? this.BXIM.webrtc.phoneCurrentCall.id(): '-', this.BXIM.webrtc.phoneCurrentCall? this.BXIM.webrtc.phoneCurrentCall.state(): '-');

		if (this.BXIM.webrtc.phoneCurrentCall)
		{
			this.BXIM.webrtc.phoneCallFinish();
			this.BXIM.webrtc.callOverlayDeleteEvents();

			if (this.isMobile())
			{
				this.BXIM.webrtc.callOverlayStatus(BX.message('IM_M_CALL_ST_END'));
				this.BXIM.webrtc.callOverlayProgress('offline');
				this.BXIM.webrtc.callOverlayState(BX.MobileCallUI.form.state.FINISHED);
			}
			else
			{
				this.BXIM.webrtc.callOverlayClose();
				this.BXIM.playSound('stop');
			}
		}

		if (this.BXIM.webrtc.phoneDisconnectAfterCallFlag && this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
		{
			setTimeout(BX.delegate(function(){
				if (this.BXIM.webrtc.phoneAPI && this.BXIM.webrtc.phoneAPI.connected())
					this.BXIM.webrtc.phoneAPI.disconnect();
			}, this), 500)
		}
	}

	BX.MessengerCommon.prototype.phoneOnProgressToneStart = function(e)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall)
			return false;

		this.BXIM.webrtc.phoneLog('Progress tone start', this.BXIM.webrtc.phoneCurrentCall.id());
		this.BXIM.webrtc.callOverlayStatus(BX.message('IM_PHONE_WAIT_ANSWER'));
		this.BXIM.webrtc.phoneRinging++;
	}

	BX.MessengerCommon.prototype.phoneOnProgressToneStop = function(e)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall)
			return false;
		this.BXIM.webrtc.phoneLog('Progress tone stop', this.BXIM.webrtc.phoneCurrentCall.id());
	}

	BX.MessengerCommon.prototype.phoneOnConnectionEstablished = function(e)
	{
		this.BXIM.webrtc.phoneLog('Connection established', this.BXIM.webrtc.phoneAPI.connected());
	}

	BX.MessengerCommon.prototype.phoneOnConnectionFailed = function(e)
	{
		this.BXIM.webrtc.phoneLog('Connection failed');
		this.BXIM.webrtc.phoneCallFinish();
		this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ERR'));
	}

	BX.MessengerCommon.prototype.phoneOnConnectionClosed = function(e)
	{
		this.BXIM.webrtc.phoneLog('Connection closed');
		this.BXIM.webrtc.phoneSDKinit = false;
	}

	BX.MessengerCommon.prototype.phoneOnMicResult = function(e)
	{
		this.BXIM.webrtc.phoneMicAccess = e.result;
		this.BXIM.webrtc.phoneLog('Mic Access Allowed', e.result);

		if (!this.isMobile())
		{
			clearTimeout(this.BXIM.webrtc.callDialogAllowTimeout);
			if (this.BXIM.webrtc.callDialogAllow)
				this.BXIM.webrtc.callDialogAllow.close();
		}

		if (e.result)
		{
			this.BXIM.webrtc.callOverlayProgress('connect');
			this.BXIM.webrtc.callOverlayStatus(BX.message('IM_M_CALL_ST_CONNECT'));
		}
		else
		{
			this.BXIM.webrtc.phoneCallFinish();
			this.BXIM.webrtc.callOverlayProgress('offline');
			this.BXIM.webrtc.callAbort(BX.message('IM_M_CALL_ST_NO_ACCESS'));
			if (!this.isMobile())
			{
				this.BXIM.webrtc.callOverlayButtons(this.BXIM.webrtc.buttonsOverlayClose);
			}
		}
	}

	BX.MessengerCommon.prototype.phoneOnNetStatsReceived = function(e)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall || this.BXIM.webrtc.phoneCurrentCall.state() != "CONNECTED")
			return false;

		var percent = (100-parseInt(e.stats.packetLoss));
		var grade = this.BXIM.webrtc.callPhoneOverlayMeter(percent);

		this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'meter', 'PERCENT': percent, 'GRADE': grade}));
	}

	BX.MessengerCommon.prototype.phoneToggleHold = function(state)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall && this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			return false;

		if (typeof(state) != 'undefined')
		{
			this.BXIM.webrtc.phoneHolded = !state;
		}

		if (this.BXIM.webrtc.phoneHolded)
		{
			if (this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			{
				this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'unhold'}));
			}
			else
			{
				BX.MessengerCommon.phoneCommand('unhold', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
			}
		}
		else
		{
			if (this.BXIM.webrtc.phoneCallDevice == 'WEBRTC')
			{
				this.BXIM.webrtc.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'hold'}));
			}
			else
			{
				BX.MessengerCommon.phoneCommand('hold', {'CALL_ID': this.BXIM.webrtc.phoneCallId});
			}
		}
		this.BXIM.webrtc.phoneHolded = !this.BXIM.webrtc.phoneHolded;
	}

	BX.MessengerCommon.prototype.phoneSendDTMF = function(key)
	{
		if (!this.BXIM.webrtc.phoneCurrentCall)
			return false;

		this.BXIM.webrtc.phoneLog('Send DTMF code', this.BXIM.webrtc.phoneCurrentCall.id(), key);

		this.BXIM.webrtc.phoneCurrentCall.sendTone(key);
	}

	BX.MessengerCommon.prototype.getHrPhoto = function(userId, color)
	{
		var hrphoto = '';
		if (userId == 'phone')
		{
			hrphoto = '/bitrix/js/im/images/hidef-phone-v3.png';
		}
		else if (this.BXIM.messenger.hrphoto[userId])
		{
			hrphoto = this.BXIM.messenger.hrphoto[userId];
			if (this.BXIM.messenger.hrphoto[userId] != '/bitrix/js/im/images/hidef-avatar-v3.png')
			{
				color = '';
			}
		}
		else if (!this.BXIM.messenger.users[userId] || this.BXIM.messenger.users[userId].avatar == this.BXIM.pathToBlankImage)
		{
			hrphoto = '/bitrix/js/im/images/hidef-avatar-v3.png'
		}
		else
		{
			hrphoto = this.BXIM.messenger.users[userId].avatar;
			color = '';
		}

		return {'src': hrphoto, 'color': color};
	};

	/* Self init */
	BX.MessengerCommon = new BX.MessengerCommon();

})(window);