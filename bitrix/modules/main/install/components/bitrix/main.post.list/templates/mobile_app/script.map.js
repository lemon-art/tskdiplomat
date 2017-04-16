{"version":3,"file":"script.min.js","sources":["script.js"],"names":["window","BX","repo","entityId","text","form","list","comments","makeId","ENTITY_XMIL_ID","ID","setText","type","isNotEmptyString","res","localStorage","get","set","getText","addCustomEvent","isArray","inner","keyBoardIsShown","mention","appendToForm","fd","key","val","ii","hasOwnProperty","append","app","exec","commentObj","id","attachments","this","mentions","prototype","node","replace","RegExp","getInstance","join","___id","removeInstance","comment","MPFForm","bindEvents","handlerId","entitiesId","handler","handlerEvents","onMPFUserIsWriting","delegate","writing","onMPFHasBeenDestroyed","reboot","visible","bindHandler","removeCustomEvent","closeWait","onCustomEvent","windowEvents","OnUCUserReply","authorId","authorName","parseInt","initComment","simpleForm","writingParams","show","OnUCAfterRecordEdit","data","act","showError","showNote","oldObj","newObj","linkEntity","_linkEntity","f","proxy","str","reinitComment","init","BXMobileApp","UI","Page","TextPanel","submitClear","submitStart","submit","c","entityHdl","post_data","getForm","ENTITY_XML_ID","REVIEW_TEXT","NOREDIRECT","MODE","AJAX_POST","sessid","bitrix_sessid","SITE_ID","message","LANGUAGE_ID","post","MobileAjaxWrapper","FormData","ij","length","Wrap","method","url","processData","start","preparePost","callback","callback_failure","xhr","send","addClass","bind","e","unbindAll","removeClass","handleAppData","showWait","hide","link","mobileShowActions","event","isKeyboardShown","enableInVersion","platform","BXMobileAppContext","target","tagName","toUpperCase","getAttribute","eventCancelBubble","PreventDefault","menu","action","push","title","reply","like","RatingLikeComments","getById","vote","voted","List","hidden","ActionSheet","buttons","mobileReply","MPL","params","staticParams","formParams","superclass","constructor","apply","arguments","template","thumb","thumbForFile","postCounter","ENTITY_ID","obj","makeThumb","pullNewRecords","add","clearThumb","module_id","command","pullNewRecord","pullNewAuthor","extend","txt","container","isString","util","htmlspecialchars","html","fcParseTemplate","messageFields","FULL_ID","POST_MESSAGE_TEXT","POST_TIMESTAMP","Date","getTime","DATE_TIME_FORMAT","RIGHTS","rights","ob","processHTML","create","attrs","className","style","opacity","height","overflow","HTML","appendChild","curPos","pos","scrollTo","top","scroll","GetWindowScrollPos","size","GetWindowInnerSize","duration","finish","scrollHeight","transition","easing","makeEaseOut","transitions","quart","step","state","scrollTop","innerHeight","complete","display","cssText","animate","cnt","func","childNodes","ajax","processScripts","SCRIPT","defer","newId","setAttribute","setTimeout","BitrixMobile","LazyLoad","showImages","nav","parentNode","build","createInstance","entity_xml_id"],"mappings":"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"}