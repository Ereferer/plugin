// Javascript functions

function isubmission_confirm_delete(txt) {
    var r = confirm("Suppression de " + txt);
    if (r == true) {
        return true;
    } else {
        return false;
    }
}