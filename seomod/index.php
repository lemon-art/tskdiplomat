<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if($USER->isAdmin()){
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
$( document ).ready(function() {
	$(".btn_i").click(function() {
		var type_data;
		var btn_i;
		
		btn_i = $(this);
		type_data = $(this).attr('title');
		$(".msg").html('');
		
		//alert(type_data);
		$.ajax({
			type: "POST",
			url: '/seomod/'+type_data+'.php', 
			data: ({ data : type_data }),	
			dataType : "json",                    
			success: function (data) { 
			//alert(data.msg);
				if(data.msg == 'yes') {
					$(".msg").parent().addClass('h_m');
					$(".msg").html("<div class=\"good\">OK</div>");
					
					if(type_data != 'infotype'){
						btn_i.append("<br>iblock ID: <span style='font-size: 16px;'>"+data.id+"</span>");
						btn_i.attr("title", '');
					}
				} else {
					$(".h_m").removeClass();
					$(".msg").html("<div class=\"error\">Error!<br>"+data.msg+"</div>");				
				}
			}
		});
	});
});
</script>
<style>
.btn_i{
	width:300px;
	margin: auto auto 10px;
	}
	
.btn_i a{ 
	color:white; width:300px;
	}
	
div.btn_i {
  font-weight: 700;
  color: white;
  text-decoration: none;
  padding: 15px;
  border-radius: 3px;
  background: #408ac6;
  transition: 0.2s;
} 

div.btn_i:hover { 
	background: #36709F; cursor:pointer;
	}
	
div.btn_i:active {
  background: rgb(33,147,90);
  box-shadow: 0 3px rgb(33,147,90) inset;
}

.h1_seo{
	color: black;
	font-size: 24px;
    text-align: center;
	}
	
.main{ 
	width:100%; text-align:center;
	}
	
.good{
	background: none repeat scroll 0 0 #47a77c;
    border-radius: 3px;
    color: white;
    font-size: 16px;
    margin: auto;
    padding: 15px;
    width: 300px;
	}
	
.error{
	background: none repeat scroll 0 0 #f28f8f;
    border-radius: 3px;
    color: black;
    margin: auto;
    padding: 15px;
    width: 300px;
	}
	
.h_m{
	height:90px;
	}
</style>

<div class="main">
<div class="h_m">
<div class="msg"></div>
</div>
<h1 class="h1_seo">1)Create InfoBlock Type</h1>
<div class="btn_i" title="infotype">Create InfoBlock Type</div>
<br>
<h1 class="h1_seo">2)Create Infoblocks</h1>
<div class="btn_i" title="infotags">Create InfoBlock "Meta Tags"</div>
<div class="btn_i" title="inforedir">Create InfoBlock "Redirects"</div>
<div class="btn_i" title="infotext">Create InfoBlock<br> "Text"</div>
</div>
<?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>