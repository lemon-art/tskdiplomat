{"version":3,"file":"script.min.js","sources":["script.js"],"names":["BX","window","BXMobileApp","namespace","repo","formId","gridId","initSelect","d","select","eventNode","container","this","click","delegate","callback","init","prototype","multiple","options","length","setAttribute","bind","hasAttribute","initValues","titles","values","defaultTitles","ii","push","innerHTML","value","e","show","PreventDefault","UI","SelectPicker","multiselect","default_value","data","keys","jj","html","removeAttribute","util","in_array","message","onCustomEvent","initDatetime","node","type","formats","format","inner","datetime","time","date","bitrix","visible","eventCancelBubble","res","start_date","getStrDate","DatePicker","setParams","delButton","style","display","makeDate","str","Date","isNotEmptyString","dateR","RegExp","timeR","m","test","exec","setDate","setMonth","setFullYear","setHours","setMinutes","parseDate","str_pad_left","getDate","toString","getMonth","getFullYear","getHours","getMinutes","DATETIME_FORMAT","convertBitrixFormat","DATE_FORMAT","TIME_FORMAT","substr","trim","indexOf","replace","id","proxy","drop","getAttribute","initSelectUser","showDrop","urls","list","profile","actualizeNodes","showMenu","Table","url","table_settings","markmode","return_full_mode","skipSpecialChars","modal","alphabet_index","outsection","okname","cancelname","proxy_context","remove","findParent","tagName","className","buildNodes","items","user","existedUsers","Math","min","htmlspecialchars","join","ij","f","childNodes","setTimeout","a_users","initSelectGroup","superclass","constructor","apply","arguments","extend","b_groups","initText","app","attachButton","attachFileSettings","attachedFiles","extraData","mentionButton","smileButton","text","htmlspecialcharsback","okButton","name","cancelButton","initBox","change","Mobile","Grid","Form","params","Page","LoadingScreen","hide","nodes","obj","restrictedMode","pop","bindElement","elements","addCustomEvent","cancel","TopBar","updateButtons","bar_type","position","ok","addClass","removeClass","result","tag","toLowerCase","event","keyCode","found","form","focus","hasClass","nextSibling","Disk","UFMobile","getByName","save","input","submit","ajax","restricted","method","onsuccess","onfailure","onprogress","create","attrs","appendChild","submitAjax","getByFormId","getByGridId"],"mappings":"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"}