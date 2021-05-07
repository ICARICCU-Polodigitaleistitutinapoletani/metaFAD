$(document).ready(function () {
    if ($('#readOnly').val() == "true") {
        var fields = getFields();
        var obFields = getObjectFields();
        $('input, textarea').each(function () {
            var el = $(this);
            if (fieldControl(el, fields)) {
                return;
            }
            if ($(this).data('select2')) {
                var instance = $(this).data('instance');
                instance.$element.select2('disable');
            } else {
                $(this).attr('readonly', 'readonly');
            }
        });
        $('select').each(function () {
            var el = $(this);
            if (fieldControl(el, fields)) {
                return;
            }
            $(this).attr('disabled', 'true');
        });
        $('div.trashButtonDiv, div.GFEButtonContainer').each(function () {
            var el = $(this).closest('fieldset');
            if (fieldControl(el, obFields)) {
                return;
            }
            $(this).remove();
        })
    }
});

function getFields() {
    //Aggiungere nell'array l'id o il name degli eventuali campi da rendere modificabili
    var fields = ['visibility', 'linkedStruMag', 'uuidImagePrimary', 'relatedStruMag', 'uuidImageSecondary', 'osservazioni', 'ecommerceLicenses', 'linkedMediaEcommerce'];
    return fields;
}

function getObjectFields() {
    //Aggiungere nell'array l'id dell'oggetto per mostrare i pulsanti di aggiunta e cancellazione
    var fields = ['secondaryStruMag'];
    return fields;
}

function fieldControl(el, fields) {
    if ($.inArray(el.attr('id'), fields) !== -1 || $.inArray(el.attr('name'), fields) !== -1) {
        return true;
    }
    return false;
}
