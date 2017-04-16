$(window).load(function() {
    $.ajax({
        type: 'POST',
        url: "/bitrix/components/sotbit/seo.meta/statistics.php", 
        data: { from: document.referrer, to: window.location.href},     
    });      
});