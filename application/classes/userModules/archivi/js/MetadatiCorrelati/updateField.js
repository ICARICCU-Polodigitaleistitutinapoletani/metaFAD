$(document).ready(function () {
    readMetaValue('#linkedStruMag', '#uuidImagePrimary');
    readMetaValue('input[name="relatedStruMag"]', 'input[name="uuidImageSecondary"]');

    $('#linkedStruMag').change(function () {
        readMetaValue('#linkedStruMag', '#uuidImagePrimary');
        resetUuid('#uuidImagePrimary');
    });

    $('input[name="relatedStruMag"]').change(function () {
        readMetaValue('input[name="relatedStruMag"]', 'input[name="uuidImageSecondary"]');
        resetUuid('input[name="uuidImageSecondary"]');
    });

    $('#secondaryStruMag-addRowBtn').click(function () {
        $('input[name="relatedStruMag"]').change(function () {
            readMetaValue('input[name="relatedStruMag"]', 'input[name="uuidImageSecondary"]');
            resetUuid('input[name="uuidImageSecondary"]');
        });
    });

    function updateValue(value, $tag) {
        var uuidParams = $($tag);
        var proxyParams = uuidParams.data('proxy_params');
        if (proxyParams === undefined)
            return;
        proxyParams = proxyParams.replace("##}", "##,##metadato##:##" + value + "##}");
        uuidParams.attr("data-proxy_params", proxyParams);
        var $data = uuidParams.select2('data');
        var instance = uuidParams.data('instance');
        instance.$element.data('proxy_params', proxyParams);
        instance.initialize(instance.$element);
        instance.$element.select2('data', $data);
    }

    function readMetaValue($metaTag, $idTag) {
        var value = $($metaTag).prop("value");
        var $tag = $idTag;
        if (value !== undefined)
            updateValue(value, $tag);
    }

    function resetUuid($tag) {
        var uuidParams = $($tag);
        var instance = uuidParams.data('instance');
        instance.$element.select2('data', null);
    }
});