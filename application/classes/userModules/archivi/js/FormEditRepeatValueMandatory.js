//Per effettuare il controllo sul valore, aggiungere a cssClass del campo appartenente all'oggetto "control-value-<valore da controllare(tutto minuscolo)>"
Pinax.oop.declare("pinax.FormEdit.FormEditRepeatValueMandatory", {
    $extends: Pinax.oop.get('pinax.FormEdit.repeat'),
    message: null,
    
    getOptions: function () {
        this.$super();
        this.message = this.$element.attr('data-message');
    },  

    isValid: function () {
        var isValid = this.$super();

        if (isValid) {
            var groupFieldsets = $(this.$element).find('input[class*="control-value-"]');
            if (groupFieldsets.length > 0) {
                var classes = groupFieldsets[0].className.split(' ');
                for (var j = 0; j < classes.length; j++) {
                    if (classes[j].search("control-value-") > -1) {
                        var matchValue = classes[j].substr(classes[j].lastIndexOf("-") + 1);
                        break;
                    }
                }
            }

            var groupFieldsetsId = groupFieldsets.context.id;
            var containerId = groupFieldsetsId + '-' + groupFieldsetsId + '0';
            var idDiv = "VAL_" + matchValue + "-" + containerId;
            var errMessage = $("#" + containerId).find("#" + idDiv);

            for (var i = 0; i < groupFieldsets.length; i++) {
                var value = groupFieldsets[i].value;
                if (value.toLowerCase() === matchValue) {
                    if (errMessage !== 0) {
                        $("#" + idDiv).remove();
                    }
                    return true;
                }
            }

            if (errMessage.length === 0) {
                $("#" + containerId).prepend("<span class='valueMandatory' style=display:block;margin:20px;color:#f00;font-size:14px id=" + idDiv + ">" + this.message + "</span>");
            }
            return false;
        }
        return false;
    }
});
