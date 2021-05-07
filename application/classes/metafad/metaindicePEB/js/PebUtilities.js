$(document).ready(function(){
    $('input[name="entity"]').on('change',function(){
        initializeSelectAfterParent($(this),'properties');
    })
});

function formatThesaurusSelection(data) {
   return data.text+'<br><small>'+data.path+'</small>';
}
    
function formatThesaurusResult(data) {
    return data.text +'<br><small>'+data.path+'</small>';
}

function initializeSelectAfterParent(el,name)
    {
        if (el.select2('data')) {
            var parentKey = el.select2('data').id;
            var rsec = el.closest('.GFERowContainer').find('input[name='+name+']');
            var proxyParams = rsec.data('proxy_params');
            proxyParams = proxyParams.replace("##}","##,##entity##:##"+parentKey+"##}");
            var instance = rsec.data('instance');
            var value = instance.getValue();
            instance.$element.data('proxy_params',proxyParams);
            instance.initialize(instance.$element);
            if (value) {
                instance.setValue(value);
            }
        }
    }