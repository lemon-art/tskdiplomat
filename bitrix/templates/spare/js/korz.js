
    $(document).ready(
        function(){
            buy_btns = $('a[href*="ADD2BASKET"]');
            buy_btns.each(
                function(){
                    $(this).attr("rel", $(this).attr("href"));
                }
            );
            buy_btns.attr("href","javascript:void(0);");
            function getBasketHTML(html)
            {
                txt = html.split('<!--start--><div id="bid">');
                txt = txt[2];
                txt = txt.split('</div><!--end-->');
                txt = txt[0];
                return txt;
            }

            $('a[rel*="ADD2BASKET"]').click(                
                function(){
                    $.ajax({
                      type: "GET",
                      url: $(this).attr("rel"),
                      dataType: "html",
                      success: function(out){
                                $("#bid").html(getBasketHTML(out));
                                alert("Товар добавлен в корзину");
                      }

                    });
                }
            );
            
        }
    );

