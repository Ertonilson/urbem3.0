(function() {
    'use strict';

    if (! UrbemSonata.checkModule('processo')) {
        return;
    }

    $("#" + UrbemSonata.uniqId + "_codClassificacao").on("change", function() {
        var codClassificacao = $(this).val();

        $.ajax({
            url: '/administrativo/protocolo/processo/assunto-classificacao',
            method: 'POST',
            data: {
              codClassificacao: codClassificacao
            },
            dataType: 'json',
            success: function (data) {
                $("#" + UrbemSonata.uniqId + "_codAssunto")
                .empty()
                .append("<option value=\"\">Selecione</option>");
                for (var assunto in data) {
                    $("#" + UrbemSonata.uniqId + "_codAssunto")
                    .append("<option selected value=" + assunto + ">" + data[assunto] + "</option>");
                }
            }
        });
    });

    $("button[name='btn_salvar_relatorio']").on("click", function() {
        $("form[role='form']").attr("action", $(this).data("action") + "?uniqid=" + UrbemSonata.uniqId);
        $("form[role='form']").submit();
    });

    var modulo = $('form').attr('name');
    if (typeof(modulo) == "undefined") {
        modulo = UrbemSonata.uniqId;
    }

    function cascadeOrganograma() {
        $(".orgao-dinamico").each(function() {
            var selectId = $(this).attr('id');
            var nclass = $(this).attr('class');
            var match = nclass.search(/nivel-(\d+)/g);
            var nivel = parseInt(nclass.substr((match + 6), 1));
            var selectedVal = $(this).val();

            $("#" + selectId + " option").each(function(){
                if ($(this).attr('selected')) {
                    selectedVal = $(this).val();
                }
            });

            if ($(this).hasClass( "first" )) {
                $(this).attr('required', 'required');
                getOrgaos(nivel, selectId, selectedVal);
            }

            $(this).on("change", function() {
                var orgao = $(this).val();
                var campo = modulo + '_orgao_' + (nivel + 1);

                if ($("select#" + campo).length) {
                    selectedVal = $("select#" + campo).val();
                    getSubOrgaos(nivel, campo, orgao, selectedVal);
                }
            });
        });
    }

    function getOrgaos(nivel, selectId, selectedVal) {
        var f1 = $.ajax({
            url: "/administrativo/organograma/orgao/consultar-orgaos",
            method: "POST",
            data: {
                nivel: nivel,
                codOrganograma: $("#" + UrbemSonata.uniqId + "_codOrganograma").val(),
            },
            dataType: "json",
            success: function (data) {
                $("select#" + selectId)
                    .empty()
                    .append("<option value=\"\">Selecione</option>")
                    .select2("val", "");

                $.each(data, function (index, value) {
                    if (selectedVal == index) {
                        $("select#" + selectId).append("<option value=" + index + " selected>" + value + "</option>");
                    } else {
                        $("select#" + selectId).append("<option value=" + index + ">" + value + "</option>");
                    }
                });

                $("select#" + selectId).select2().trigger("change");
            }
        });
    }

    function getSubOrgaos(nivel, campo, orgao, selectedVal) {
        if (orgao != '') {
            $.ajax({
                url: "/administrativo/organograma/orgao/consultar-sub-orgaos",
                method: "POST",
                data: {orgao: orgao},
                dataType: "json",
                success: function (data) {
                    $("select#" + campo)
                        .empty()
                        .append("<option value=\"\">Selecione</option>")
                        .select2("val", "");

                    $.each(data, function (index, value) {
                        var selectedOption = '';
                        if (selectedVal == index) {
                            selectedOption = 'selected';
                        }
                        $("select#" + campo)
                            .append("<option value=" + index + " " + selectedOption + ">" + value + "</option>");
                    });

                    $("select#" + campo).select2().trigger("change");
                }
            });
        } else {
            $("select#" + campo)
                .empty()
                .append("<option value=\"\">Selecione</option>")
                .select2("val", "")
                .trigger("change");
        }
    }

    if (typeof($("#" + UrbemSonata.uniqId + "_codOrgao").val()) == "undefined") {
        cascadeOrganograma();
    }

    $("#" + UrbemSonata.uniqId + "_codOrganograma").on("change", function() {
        cascadeOrganograma();
    });

    $(".orgao-dinamico").on("change", function() {
        var selectedVal = "";
        var nivel = $(this).data('nivel');
        var orgao = $(this).val();
        var campo = modulo + '_orgao_' + (nivel + 1);

        if ($("select#" + campo).length) {
            selectedVal = $("select#" + campo).val();
            getSubOrgaos(nivel, campo, orgao, selectedVal);
        }
    });
}());
