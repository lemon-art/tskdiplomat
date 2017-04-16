//alignment popup to the right
function menuCatalogAlignPopup(element, menuID)
{
	var popupObj = BX.findChild(element, {className: "bx_children_container"}, true, false);

	if (popupObj)
	{
		var widthPopup  = popupObj.clientWidth;
		var offsetRightPopup = popupObj.offsetLeft + widthPopup;
		var widthMainUl  = BX("ul_"+menuID).clientWidth;
		var offsetRightMenu = BX("ul_"+menuID).offsetLeft + widthMainUl;
		if(offsetRightPopup>=offsetRightMenu)
		{
			if (BX.lastChild(BX("ul_"+menuID)) == element)
				popupObj.style.right = "1px";
			else
				popupObj.style.right = "0";
		}
	}
}

function menuCatalogResize(menuID, menuFirstWidth)
{
	var wpasum = 0; // sum of width for all li

	var firstLevelLi = BX.findChildren(BX(menuID), {className : "bx_hma_one_lvl"}, true);

	if (firstLevelLi)
	{
		for(var i = 0; i < firstLevelLi.length; i++)
		{
			var wpa = BX.firstChild(firstLevelLi[i]).clientWidth;
			wpasum += wpa;
		}

		if(menuFirstWidth && (wpasum+20) <= menuFirstWidth)
			BX.addClass(BX(menuID), "small");   //adaptive
		else
			BX.removeClass(BX(menuID), "small");
	}

	return wpasum;
}

function menuCatalogAlign(menuID)
{
	var firstLevelLi = BX.findChildren(BX(menuID), {className : "bx_hma_one_lvl"}, true);
	var wpsum = 0;

	if (firstLevelLi)
	{
		for(var i = 0; i < firstLevelLi.length; i++)
		{
			firstLevelLi[i].removeAttribute("style");
			var wp = firstLevelLi[i].clientWidth;
			wpsum = wpsum+wp;
		}

		var cof_width = wpsum/100;

		for(var i = 0; i < firstLevelLi.length; i++)
		{
			wp = firstLevelLi[i].clientWidth;
			firstLevelLi[i].style.width = (wp/cof_width)+"%";
		}
	}
}

function menuCatalogPadding(menuID)
{
	var firstLevelLi = BX.findChildren(BX(menuID), {className : "bx_hma_one_lvl"}, true);
	var wpsum = 0;

	if (firstLevelLi)
	{
		for(var i = 0; i < firstLevelLi.length; i++)
		{
			BX.firstChild(firstLevelLi[i]).style.padding = "19px 10px";
		}
	}
}

function menuCatalogChangeSectionPicure(element)
{
	var curImgWrapObj = BX.nextSibling(element, {className: "bx_children_advanced_panel"}, true, false);
	var curImgObj = BX.clone(BX.firstChild(curImgWrapObj));
	var curDescr = element.getAttribute("data-description");
	var parentObj = BX.hasClass(element, 'bx_hma_one_lvl') ? element : BX.findParent(element, {className:'bx_hma_one_lvl'});
	var sectionImgObj = BX.findChild(parentObj, {className:'bx_section_picture'}, true, false);
	sectionImgObj.innerHTML = "";
	sectionImgObj.appendChild(curImgObj);
	var sectionDescrObj = BX.findChild(parentObj, {className:'bx_section_description'}, true, false);
	sectionDescrObj.innerHTML = curDescr;
	BX.previousSibling(sectionDescrObj).innerHTML = element.innerHTML;
}