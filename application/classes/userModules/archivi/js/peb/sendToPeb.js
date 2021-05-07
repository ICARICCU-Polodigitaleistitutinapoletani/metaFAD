$(document).ready(function(){
    $('body').on('click','.js-send-to-peb',function(){
        var id = $(this).attr('data-id');
        var value = $(this).parents('tr').find('.js-denominazione').html();
        value = value.split("<br>");
        value = value[0];
        
        if(value == '' ){
            value = 'N.D.';
        }
        
        var event = {
            type: 'metafadContent',
            message: {id:id, value:value}
        };
        console.log(event);
        window.top.postMessage(event, '*');
    });
});
