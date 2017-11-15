$(document).ready(function() {
    // Fields
    var entidadeSelect = $("#" + UrbemSonata.uniqId + "_entidade");
    var orgaoSelect = $("#" + UrbemSonata.uniqId + "_orgao");
    var unidadeSelect = $("#" + UrbemSonata.uniqId + "_unidade");
    var tipoRecursoSelect = $("#" + UrbemSonata.uniqId + "_tipoRecurso");
    var recursoSelect = $("#" + UrbemSonata.uniqId + "_recurso");
    var acaoSelect = $("#" + UrbemSonata.uniqId + "_acao");
    var tipoEducacaoInfantilSelect = $("#" + UrbemSonata.uniqId + "_tipoEducacaoInfantil");

    // Convert objects in collection
    var objects = [entidadeSelect, orgaoSelect, unidadeSelect, tipoRecursoSelect, recursoSelect, acaoSelect, tipoEducacaoInfantilSelect];

    // ObjectModal
    var modal = $.urbemModal();

    // Function Test
    function carregaAcaoTeste() {
        modal.disableBackdrop()
            .setTitle('Aguarde...')
            .setBody('Buscando informações importantes...')
            .open();

        $.post(UrlServiceProviderTCE, $("form").serializeArray())
            .success(function (data) {

                console.log(data);

                modal.close();
            }
        );
    }

    // Load Objects
    for(var key = 0; key < objects.length; key++) {
        // Disabled selects
        objects[key].prop('disabled', true);

        // OnChange
        objects[key].on("change", function() {
            carregaAcaoTeste();
        });
    }

    // Enabled entidade type
    entidadeSelect.prop('disabled', false);

    // Onload
    UrbemSonata.waitForFunctionToBeAvailableAndExecute("UrlServiceProviderTCE", carregaAcaoTeste);
}());