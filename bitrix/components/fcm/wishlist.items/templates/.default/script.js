
jQuery(document).ready(function(){
    
    jQuery(".wishlist-items .remove").on('click',function(){
        
        if(confirm('Удалить товар из листа желаний?')){
            
            listitem = jQuery(this).parents('.item');
            console.log(listitem);
            id = jQuery(this).attr('data-id');
            console.log(id);
            list = jQuery(listitem).parents('.wishlist-items');
            console.log(list);
            
            jQuery.getJSON("/personal/wishlist/index.php",{id:id,ajax_wishlist:'Y',action:'del'},function(resp){
                console.log(resp);
                if(resp['STATUS'] == 'OK'){
                    jQuery(listitem).empty();
                }else{
                    alert(resp['MESSAGE']);
                }
            }).fail(function( jqxhr, textStatus, error ) {
                console.log(jqxhr);
                var err = textStatus + ", " + error;
                alert('Что то пошло не так! Мы не смогли удалить товар из листа желаний....');
                console.log( "Request Failed: " + err );
            });
        };
        return false;
    })
});
