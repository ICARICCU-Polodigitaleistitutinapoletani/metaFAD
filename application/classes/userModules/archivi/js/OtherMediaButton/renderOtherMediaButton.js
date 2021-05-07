$(document).ready(function () {
    var element = $('input[name="mediaCollegatiNoImg"]');
    var json = element.val();
    try {
        var decoded = JSON.parse(json);
        if (decoded.type != 'IMAGE') {
            var div = element.parent().parent();
            $(div).append('<a id="provaaa" class="btn btn-flat btn-info col-sm-2" href="' + decoded.src + '" target=_blank>Vedi</a>');
        }
    } catch (e) {
        return false;
    }
});
