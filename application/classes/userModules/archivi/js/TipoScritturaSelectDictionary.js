Pinax.oop.declare("pinax.FormEdit.selectTipoScrittura", {
    $extends: Pinax.oop.get('pinax.FormEdit.dynamicselectfrom'),

    initialize: function (element) {
        this.model = element.data('model');
        this.field = element.data('textfield');

        element.data('instance', this);
        this.$element = element;

        element.removeAttr('value');
        element.css('width', '500px');


        var fieldName = element.data('field') || element.attr('name');
        this.multiple = element.data('multiple');
        var addNewValues = element.data('add_new_values');
        var model = element.data('model');
        var query = element.data('query');
        var proxy = element.data('proxy');
        var proxyParams = element.data('proxy_params');
        if (proxyParams) {
            proxyParams = proxyParams.replace(/##/g, '"');
        }
        var placeholder = element.data('placeholder');
        var originalName = element.data('originalName');
        var getId = element.data('get_id');
        
        var selectedCallback = element.data('selected_callback');
        var minimumInputLength = element.data('min_input_length') || 0;
        var formatSelection = element.data('format_selection');
        var formatResult = element.data('format_result');

        if (originalName !== undefined && element.data('override') !== false) {
            fieldName = originalName;
        }

        element.select2({
            width: 'off',
            multiple: this.multiple,
            minimumInputLength: minimumInputLength,
            placeholder: placeholder === undefined ? '' : placeholder,
            allowClear: true,
            ajax: {
                url: Pinax.ajaxUrl + "&controllerName=pinaxcms.contents.controllers.autocomplete.ajax.FindTerm",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        fieldName: fieldName,
                        model: model,
                        query: query,
                        term: term,
                        proxy: proxy,
                        proxyParams: proxyParams,
                        getId: getId
                    };
                },
                results: function (data, page) {
                    return { results: data.result }
                }
            },
            createSearchChoice: function (term, data) {
                if (!addNewValues) {
                    return false;
                }

                if ($(data).filter(function () {
                    return this.text.localeCompare(term) === 0;
                }).length === 0) {
                    return { id: term, text: term };
                }
            },
            formatResult: function (data) {
                return formatResult === undefined ? data.text : window[formatResult](data);
            },
            formatSelection: function (data) {    
                    var term = data.text;
                    var tipoDiScrittura = element.closest('.GFERowContainer').find('input[name=tipoDiScrittura]');
                    var proxyParams = tipoDiScrittura.data('proxy_params');
                    proxyParams = proxyParams.replace("##}","##,##parentKey##:##"+term+"##}");
                    tipoDiScrittura.attr("data-proxy_params", proxyParams);
                    var instance = tipoDiScrittura.data('instance');
                    instance.$element.data('proxy_params',proxyParams);
                    instance.initialize(instance.$element);
                                                          
                return formatSelection === undefined ? data.text : window[formatSelection](data);
            },
            formatNoMatches: function () { return PinaxLocale.selectfrom.formatNoMatches; },
            formatSearching: function () { return PinaxLocale.selectfrom.formatSearching; }
        });

        if (this.multiple) {
            element.parent().find("ul.select2-choices").sortable({
                containment: 'parent',
                start: function () { element.select2("onSortStart"); },
                update: function () { element.select2("onSortEnd"); }
            });
        }
    },
});