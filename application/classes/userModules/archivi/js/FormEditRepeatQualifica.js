Pinax.oop.declare("pinax.FormEdit.repeatQualifica", {
    $extends: Pinax.oop.get('pinax.FormEdit.repeat'),

    addRow: function (fieldSet, footer, id, justCreated, noVerifySelectWithTarget, value, newRow) {
        $container = this.$super(fieldSet, footer, id, justCreated, noVerifySelectWithTarget, value, newRow)
        
        if (id == 0) {
            $container.find('input[name=qualificaDataAggiunta]').parents('div.form-group').hide();
        } else {
            $container.find('input[name=qualificaData]').parents('div.form-group').hide();
        }

        return $container;
    }
});