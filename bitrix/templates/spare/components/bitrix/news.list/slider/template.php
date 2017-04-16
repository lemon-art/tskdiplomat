<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(count($arResult["ITEMS"]) > 0):?>
<div id="tmnivoslider">
	<div id="slider">
	<?foreach($arResult["ITEMS"] as $key => $Item):
		if(strlen($Item["PROPERTIES"]["LINK"]["VALUE"]) > 0):?>
			<a href="<?=$Item["PROPERTIES"]["LINK"]["VALUE"]?>" class="nivo-imageLink">
				<img src="<?=$Item["PREVIEW_PICTURE"]["SRC"]?>" alt="" title="#htmlcaption<?=$key+1?>">
			</a>
		<?else:?>
				<img src="<?=$Item["PREVIEW_PICTURE"]["SRC"]?>" alt="" title="#htmlcaption<?=$key+1?>">
		<?endif;
	endforeach;?>
	</div>
		<?foreach($arResult["ITEMS"] as $key => $Item):?>
			<div id="htmlcaption<?=$key+1?>" class="nivo-html-caption">
				<h2><?=$Item["PROPERTIES"]["TITLE"]["VALUE"]?></h2>
				<h3><?=$Item["PROPERTIES"]["SUBTITLE"]["VALUE"]?></h3>
				<h4><?=$Item["PREVIEW_TEXT"]?></h4>
				<h5><?=$Item["PROPERTIES"]["PRICE"]["VALUE"]?></h5>
				<a class="slide_btn" href="<?=$Item["PROPERTIES"]["LINK"]["VALUE"]?>">Подробнее</a>
			</div>
		<?endforeach;?>
</div>
<script type="text/javascript">
$(window).load(function() {
	$('#slider').nivoSlider({
		effect:'fade', //Specify sets like: 'fold,fade,sliceDown'
		slices:10,
		animSpeed:500, //Slide transition speed
		pauseTime:5000,
		startSlide:0, //Set starting Slide (0 index)
		directionNav:false, //Next & Prev
		directionNavHide:false, //Only show on hover
		controlNav:true, //1,2,3...
		controlNavThumbs:true, //Use thumbnails for Control Nav
      	controlNavThumbsFromRel:false, //Use image rel for thumbs
		controlNavThumbsSearch: '.jpg', //Replace this with...
		controlNavThumbsReplace: '_thumb.jpg', //...this in thumb Image src
		keyboardNav:true, //Use left & right arrows
		pauseOnHover:true, //Stop animation while hovering
		manualAdvance:false, //Force manual transitions
		captionOpacity:1.0, //Universal caption opacity
		beforeChange: function(){},
		afterChange: function(){},
		slideshowEnd: function(){} //Triggers after all slides have been shown
	});
});

</script>
<?endif; //items?>
