$(document).ready(function () {

    var fields = {
        '#audiovisivo_TipologiaSpecifica': '#audiovisivo_specificazione_tipologia',
        '#audiovisivo_tecnica': '#audiovisivo_specificazione', '#audiovisivo_formato': '#audiovisivo_specificazione_formato',
        '#cartografia_tipologiaSpecifica': '#cartografia_specificazione', '#cartografia_tecnica': '#cartografia_specificazioneTecnica',
        '#cartografia_indicatoreColore': '#cartografia_specificazioneColore', '#cartografia_tipoScala': '#cartografia_specificazioneScala',
        '#foto_tecnica': '#foto_specificazione', '#foto_indicatoreColore': '#foto_specificazioneColore', '#grafica_tipologiaSpecifica': '#grafica_specificazione',
        '#grafica_materia_listMateria': '#grafica_materia_specificazione', '#grafica_tecnica_listTecnica': '#grafica_tecnica_specificazione',
        '#grafica_indicatoreColore': '#grafica_specificazioneColore', '#modalitaRedazioneScelta': '#specificazioneModalita'
    };

    hideFields(fields);

    $('#tipoSpecifico').change(function (e) {
        hideFields(fields);
    });

    for (var key in fields) {
        $(key).on("change", function (e) {
            var fieldValue = $(this).val();   
            if (fieldValue == 'Altro') {
                $(fields['#' + $(this).attr('id')]).closest('.form-group').show();
            } else {
                $(fields['#' + $(this).attr('id')]).closest('.form-group').hide();
            }
        });
    }
});

function hideFields(fields) {
    for (var key in fields) {
        if ($(key).val() != 'Altro') {
            $(fields[key]).closest('.form-group').hide();
        }
    }
}