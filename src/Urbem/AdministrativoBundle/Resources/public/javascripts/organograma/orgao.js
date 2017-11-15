(function ($, urbem, global) {
  'use strict';

  var fieldCodOrgaoSuperior = urbem.giveMeBackMyField('codOrgaoSuperior')
    , fieldCodOrganograma = urbem.giveMeBackMyField('codOrganograma')
    , fieldFkNormasNorma = urbem.giveMeBackMyField('fkNormasNorma')
    , fieldTipoNorma = urbem.giveMeBackMyField('tipoNorma')
    , fieldCodNivel = urbem.giveMeBackMyField('codNivel')
    , codOrganograma = fieldCodOrganograma.val()
    , codTipoNorma = fieldTipoNorma.val()
    , modal = $.urbemModal();

  fieldFkNormasNorma.attr('required', 'required');
  fieldCodOrganograma.select2('disable');

  if (codOrganograma === '' || codOrganograma === undefined) {
    fieldCodNivel.empty()
      .append("<option value=\"\">Selecione</option>")
      .select2("val", "");
  } else if (fieldCodOrganograma.attr('disabled') !== 'disabled') {
    manterNivel(codOrganograma);
  }

  fieldCodOrganograma.on("change", function () {
    var id = $(this).val();
    if (id === '') {
      id = 0;
    }
    manterNivel(id);
  });

  var nivel = fieldCodNivel.val();
  if ((nivel === '') || (nivel === undefined)) {
    fieldCodOrgaoSuperior.empty()
      .append("<option value=\"\">Selecione</option>")
      .attr("disabled", true)
      .attr("required", false)
      .select2("val", "");
  } else if (fieldCodOrganograma.attr('disabled') !== 'disabled') {
    manterOrgao(codOrganograma, nivel);
  }

  fieldCodNivel.on("change", function () {
    var codOrganograma = fieldCodOrganograma.val()
      , codNivel = $(this).val();

    $("#" + UrbemSonata.uniqId + "_editNivel").val(codNivel);
    if (codNivel === '') {
      codNivel = 0;
    }

    manterOrgao(codOrganograma, codNivel);
  });

  function manterNivel(organograma) {
    $.ajax({
      url: "/administrativo/organograma/orgao/consultar-nivel",
      method: "POST",
      data: {organograma: organograma},
      dataType: "json",
      beforeSend: function (xhr) {
        modal
          .disableBackdrop()
          .setTitle('Aguarde...')
          .setBody('Buscando niveis.')
          .open();
      },
      success: function (data) {
        fieldCodNivel.empty()
          .append("<option value=\"\">Selecione</option>")
          .select2("val", "");

        $.each(data, function (index, value) {
          fieldCodNivel.append("<option value=" + index + ">" + value + "</option>");
        });

        modal.close();
      }
    });
  }

  function manterOrgao(organograma, nivel) {
    if ((nivel != 1) && (nivel != undefined) && (nivel != 0)) {
      $.ajax({
        url: "/administrativo/organograma/orgao/consultar-superior",
        method: "POST",
        data: {codOrganograma: organograma, codNivel: nivel},
        dataType: "json",
        beforeSend: function (xhr) {
          modal
            .disableBackdrop()
            .setTitle('Aguarde...')
            .setBody('Buscando orgãos superiores.')
            .open();
        },
        success: function (data) {
          fieldCodOrgaoSuperior.attr("disabled", false);
          fieldCodOrgaoSuperior.attr("required", true);
          fieldCodOrgaoSuperior.empty()
            .append("<option value=\"\">Selecione</option>")
            .select2("val", "");

          $.each(data, function (index, value) {
            fieldCodOrgaoSuperior
              .append("<option value=" + index + ">" + value + "</option>");
          });

          modal.close();
        }
      });
    } else {
      fieldCodOrgaoSuperior.empty()
        .append("<option value=\"\">Selecione</option>")
        .attr("disabled", true)
        .attr("required", false)
        .select2("val", "");
    }
  }

  function manterNormas(tipoNorma) {
      $.ajax('/administrativo/organograma/organograma/consultar-norma/{id}'.replace('{id}', tipoNorma), {
        method: 'GET',
        dataType: 'json',
        beforeSend: function (xhr) {
          modal
            .disableBackdrop()
            .setTitle('Aguarde...')
            .setBody('Buscando normas.')
            .open();
        },
        error: function (xhr, textStatus, error) {
          modal.close();

          modal
            .disableBackdrop()
            .setTitle(error)
            .setBody('Contate o administrador do Sistema.')
            .open();

          global.setTimeout(function () {
            modal.close();
          }, 5000);
        },
        success: function (data) {
          urbem.populateSelect(fieldFkNormasNorma, data, {label: 'label', value: 'value'}, fieldFkNormasNorma.val());

          fieldFkNormasNorma.select2('enable');

          modal.close();
        }
      });
  }

  console.log(codTipoNorma);
  if (codTipoNorma !== "" && codTipoNorma !== undefined) {
    manterNormas(codTipoNorma);
  } else {
    fieldFkNormasNorma.empty()
      .append("<option value=\"\">Selecione</option>")
      .attr("disabled", true)
      .attr("required", false)
      .select2("val", "");
  }

  fieldTipoNorma.on('change', function () {
    codTipoNorma = $(this).val();

    if (codTipoNorma !== "" && codTipoNorma !== undefined) {
      manterNormas(codTipoNorma);
    }
  });

  if (fieldCodOrganograma.attr('disabled') === 'disabled') {
    $("#sonata-ba-field-container-" + urbem.uniqId + "_inativacao").hide();
    $("#" + urbem.uniqId + "_inativacao").attr('required', false);

    $("#" + urbem.uniqId + "_desativar_0").on('ifChecked', function (event) {
      $("#sonata-ba-field-container-" + urbem.uniqId + "_inativacao").show();
      $("#" + urbem.uniqId + "_inativacao").attr('required', true);
    });

    $("#" + urbem.uniqId + "_desativar_1").on('ifChecked', function (event) {
      $("#sonata-ba-field-container-" + urbem.uniqId + "_inativacao").hide();
      $("#" + urbem.uniqId + "_inativacao").attr('required', false);
    });
  }

})(jQuery, UrbemSonata, window);
