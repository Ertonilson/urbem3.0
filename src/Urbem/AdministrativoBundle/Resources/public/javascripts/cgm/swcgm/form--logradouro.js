(function ($, global, urbem) {
  'use strict';

  var logradouroApi = global.logradouroApiUrl;

  var fieldSwLogradouro = urbem.giveMeBackMyField('swLogradouro')
    , fieldSwLogradouroCorresp = urbem.giveMeBackMyField('swLogradouroCorresp')
    , fieldSwBairro = urbem.giveMeBackMyField('swBairro')
    , fieldSwBairroCorresp = urbem.giveMeBackMyField('swBairroCorresp')
    , fieldSwCep = urbem.giveMeBackMyField('swCep')
    , fieldSwCepCorresp = urbem.giveMeBackMyField('swCepCorresp');

  function populateBairrosCeps(codLogradouro) {
    if (codLogradouro !== undefined && codLogradouro !== '') {
      var modal = $.urbemModal();

      $.ajax({
        url: logradouroApi.replace('{id}', codLogradouro),
        method: 'GET',
        dataType: 'json',
        beforeSend: function (xhr) {
          modal
            .disableBackdrop()
            .setTitle('Aguarde...')
            .setBody('Buscando dados adicionais do Logradouro.')
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

          urbem.populateSelect(fieldSwBairro, data.bairros, {
            label: 'nom_bairro',
            value: 'value'
          }, fieldSwBairro.val());

          urbem.populateSelect(fieldSwCep, data.ceps, {
            label: 'cep',
            value: 'value'
          }, fieldSwCep.val());

          fieldSwBairro.select2('enable');
          fieldSwCep.select2('enable');

          $('div.fkSwMunicipio_fkSwUf > div').text(data.uf);
          $('div.fkSwMunicipio > div').text(data.municipio);

          modal.close();
        }
      });
    }
  }

  fieldSwLogradouro.on('change', function (e) {
    populateBairrosCeps($(this).val());
  });

  function populateBairrosCepsCorresp(codLogradouroCorresp) {
    if (codLogradouroCorresp !== undefined && codLogradouroCorresp !== '') {
      var modal = $.urbemModal();

      $.ajax({
        url: logradouroApi.replace('{id}', codLogradouroCorresp),
        method: 'GET',
        dataType: 'json',
        beforeSend: function (xhr) {
          modal
            .disableBackdrop()
            .setTitle('Aguarde...')
            .setBody('Buscando dados adicionais do Logradouro.')
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

          urbem.populateSelect(fieldSwBairroCorresp, data.bairros, {
            label: 'nom_bairro',
            value: 'value'
          }, fieldSwBairroCorresp.val());

          urbem.populateSelect(fieldSwCepCorresp, data.ceps, {
            label: 'cep',
            value: 'value'
          }, fieldSwCepCorresp.val());

          fieldSwBairroCorresp.select2('enable');
          fieldSwCepCorresp.select2('enable');

          $('div.fkSwMunicipio_fkSwUf1 > div').text(data.uf);
          $('div.fkSwMunicipio1 > div').text(data.municipio);

          modal.close();
        }
      });
    }
  }

  fieldSwLogradouroCorresp.on('change', function (e) {
    populateBairrosCepsCorresp($(this).val());
  });

  $(document).ready(function () {
    populateBairrosCeps(fieldSwLogradouro.val());
    populateBairrosCepsCorresp(fieldSwLogradouroCorresp.val());
  });

})(jQuery, window, UrbemSonata);
