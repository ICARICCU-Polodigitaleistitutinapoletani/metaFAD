window.onload = function () {
    $('.conservatoreDiv').each(function () {
        $('#' + this.id + '-addRowBtn').hide();
        var trashButton = $('#' + this.id).find('.trashButton').each(function () { $(this).hide() });
    });
}

Pinax.oop.declare("pinax.FormEdit.selectConservatori", {
    $extends: Pinax.oop.get('pinax.FormEdit.dynamicselectfrom'),

    initialize: function (element) {
        //TODO da cambiare con variabili
        window.idCons = '#' + element.data('idconservatore');
        window.idConsSelect = window.idCons + '-conservatore';
        window.idComplessoSelect = element.data('idcomplesso') + '-complessi';
        this.model = element.data('model');
        this.controller = element.data('controller');
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
                if (selectedCallback) {
                    var term = data.text;

                    $.ajax({
                        url: Pinax.ajaxUrl + "&controllerName=" + selectedCallback,
                        dataType: 'json',
                        quietMillis: 250,
                        indexValue: element,
                        data: {
                            fieldName: fieldName,
                            model: model,
                            query: query,
                            term: term,
                            proxy: proxy,
                            proxyParams: proxyParams,
                            getId: getId
                        },
                        type: "POST",
                        success: function (data, page) {
                            var element = this.indexValue;
                            var parent = element.closest('.GFERowContainer');
                            var id = parent.attr('id');
                            var complDiv = $('#' + id).find('.select2-search-choice-close');
                            complDiv.hide();
                            var inputNum = id.substr(-1);
                            $('#complessiLinked').val(parseInt(inputNum) + 1);
                            var consDiv = $(window.idConsSelect + inputNum);
                            if (consDiv.length === 0) {
                                $(window.idCons + '-addRowBtn').hide();
                                $(window.idCons + '-addRowBtn').click();
                                var consDiv = $(window.idConsSelect + inputNum);
                                var trash = consDiv.find('.trashButton');
                                $(trash).hide();
                            }
                            var field = consDiv.find('a').find('span');
                            field.text(data.result['text']);
                            var input = consDiv.find('input.form-control');
                            var count = 0;
                            $('input[name="soggettoConservatore"]').each(function() {
                                if($(this).val() == data.result['id']) {
                                    if(input.val() == '') {
                                        data.result = '';
                                        return false;
                                    }
                                }
                              });
                            input.select2('data', { id: data.result['id'], text: data.result['text'] });
                            if (data.result == '') {
                                consDiv.hide();
                            } else {
                                consDiv.show();
                            }
                        },
                        error: function (xhr, status, error) {
                            var err = xhr.responseText;
                        }
                    });
                }
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

        $('.select2-search-choice-close').on('mousedown', function () {
            var selectContainer = $(this).closest('.GFERowContainer');
            var id = selectContainer.attr('id');
            if (id.substr(0, id.length - 1) == window.idComplessoSelect) {
                var inputNum = id.substr(-1);
                var consDiv = $(window.idConsSelect + inputNum);
                var consClear = consDiv.find('.select2-search-choice-close');
                $(consClear).mousedown();

            }
        });

        $('.trashButtonDiv').on('click', function () {
            var selectContainer2 = $(this).closest('.GFERowContainer');
            var id2 = selectContainer2.attr('id');
            consQuantity = $('#complessi .GFERowContainer').length;
            $('#complessiLinked').val(consQuantity - 1);
            if (id2.substr(0, id2.length - 1) == window.idComplessoSelect) {
                var inputNum2 = id2.substr(-1);
                var consDiv2 = $(window.idConsSelect + inputNum2);
                var consClear2 = consDiv2.find('.trashButton');
                $(consClear2).click();
                $(window.idCons + '-addRowBtn').hide();
            }
        });

    },
});