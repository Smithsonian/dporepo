jQuery(document).ready(function($) {

  // TODO: Convert the unit ID in the unit options to the ISNI id?
  // Right now, can't change over to ISNI once a unit ID has been chosen and recorded in the database.
  $('select.stakeholder-chosen-select').chosen().change(function(evt, params){
    // console.log(params.selected);
  });

  // Disable the subject_form[holding_entity_guid] form field.
  $('#subject_form_holding_entity_guid').attr('disabled', 'disabled');

  // Set variables.
  var resultsContainer = $('#search-results')
      loadingGif = $('<img />').attr('src', '/lib/images/spinner.gif').attr('alt', 'loading animation').attr('style', 'width: 140px;'),
      loadingGifContainer = $('<div />').addClass('center-block').attr('style', 'width: 140px;').append(loadingGif);

  // Search ISNI button click handler.
  $('#isni-search-query-button').on('click', function(e) {

    var searchQuery = $('#search-query').val();

    // Validate - search query.
    if(!searchQuery.length) {
      swal({
        title: "Search Term is Empty",
        text: "Please enter a search term.",
      });
      return;
    }

    // Remove previous search results and search value, if present.
    resultsContainer.empty();
    // Add the loading gif.
    resultsContainer.append(loadingGifContainer);

    // Make an AJAX request to the ISNI endpoint.
    // Example: http://127.0.0.1:8000/admin/isni/NASM/1/50
    $.ajax({
      type: 'GET'
      ,dataType: 'json'
      ,url: '/admin/isni/' + searchQuery + '/1/50'
      ,success: function(result) {

        if(result) {

          // Remove the loading gif.
          loadingGifContainer.remove();

          // Populate the search results.
          for (var key in result) {

            var isniIdHeader = $('<h4 />').text('ISNI ID'),
                isniId = $('<a />')
                    .attr('href','http://www.isni.org/' + result[key].isniId)
                    .attr('title','View full record (opens in a new tab/window)')
                    .attr('target','_blank')
                    .text(result[key].isniId),
                isniOrgTypeHeader = $('<h4 />').text('Organization Type'),
                isniOrgTypeContainer = $('<div />').text(result[key].organisationType),
                isniLabelsHeader = $('<h4 />').text('Organization Names'),
                isniLabelsContainer = buildLabelRadioButtons(result[key].isniId, result[key].organisationName),
                containerDivLeft = $('<div />')
                    .attr('id', result[key].isniId)
                    .addClass('col-sm-10 col-md-10 well')
                    .append(isniLabelsHeader, isniLabelsContainer, isniOrgTypeHeader, isniOrgTypeContainer, isniIdHeader, isniId),
                radioButton = $('<input class="isni-chosen" name="isni_chosen" type="radio" value="' + result[key].isniId + '">'),
                radioButtonIcon = $('<i />').addClass('glyphicon hidden')
                    .on('click', function() {
                        var thisCheckboxIcon = $(this);
                        $('.isni-picker i').removeClass('glyphicon-check').addClass('hidden');
                        thisCheckboxIcon.removeClass('hidden').addClass('glyphicon-check');
                        $('.well').removeAttr('style');
                        thisCheckboxIcon.parent().parent().parent().find('.well').attr('style', 'border-color: #3797fc;');
                        thisCheckboxIcon.parent().parent().find('.isni-chosen').trigger('click');
                  }),
                radioButtonIconHeader = $('<h3 />').addClass('isni-picker').append(radioButtonIcon),
                containerDivRight = $('<div />').addClass('col-sm-2 col-md-2').append(radioButton, radioButtonIconHeader),
                containerDivRow = $('<div />').addClass('row').append(containerDivLeft, containerDivRight);

            resultsContainer.append(containerDivRow);

          }
        
        }

      }
    });

  });

  // Save the stakeholder data (populate the display area and hidden fields in the parent form).
  $('#project_form_stakeholder_guid_picker').on('change', function(){
    var stakeholderId = this.value;
    var stakeholderName = $( "#stakeholder_guid_picker" ).val();

    $('#project_form_stakeholder_guid').val('');
    $('#project_form_stakeholder_label').val(stakeholderName);

    $('#stakeholder-display').empty().append(stakeholderName);
  });

  $('#save-changes').on('click', function(){

    // Set all of the variables.
    var chosenIsniGuid = $("input:radio[name='isni_chosen']:checked").val(),
        chosenIsniLabel = $("input:radio[name='" + chosenIsniGuid + "']:checked").val(),
        chosenSiGuid = $('#stakeholder_guid_picker').val(),
        isniValuePresent = (typeof chosenIsniGuid !== 'undefined') ? true : false,
        isniLabelPresent = (typeof chosenIsniLabel !== 'undefined') ? true : false,
        isniInfo = 'ISNI ID: <a href="http://www.isni.org/' + chosenIsniGuid + '" title="View full record (opens in a new tab/window)" target="_blank">' + chosenIsniGuid + '</a>',
        displayText = chosenIsniLabel + '<br> ' + isniInfo;

    // Validation - ISNI record
    if(!isniValuePresent) {
      swal('ISNI Choice is Empty', 'Please choose an ISNI record.');
      return;
    }
    // Validation - ISNI label
    if(!isniLabelPresent) {
      swal('ISNI Organization Name Label is Empty', 'Even though an ISNI record has been chosen, a name label must also be chosen.');
      return;
    }

    // Populate the display area and hidden fields in the parent form.
    if(isniValuePresent) {
      // Support for the Project form.
      $('#project_form_stakeholder_label').val(chosenIsniLabel);
      $('#project_form_stakeholder_guid').val(chosenIsniGuid);
      // Support for the Subject form.
      $('#subject_form_holding_entity_guid').val(chosenIsniGuid);
      $('#stakeholder_guid_picker').val(chosenIsniGuid);
      $('#stakeholder-display').empty().append(displayText);
      $('#isniSearchModal').modal('hide');
    }

  });

  // ISNI radio buttons click handler
  $('#isniSearchModal').on('click', '.isni-label', function(){
    var thisCheckboxContainer = $(this).parent().parent().parent().parent().find('.isni-picker');
        thisCheckboxIcon = thisCheckboxContainer.find('i');
    $('.isni-label').prop('checked', false);
    $(this).prop('checked', true);
    $('.isni-picker i').removeClass('glyphicon-check').addClass('hidden');
    thisCheckboxIcon.removeClass('hidden').addClass('glyphicon-check');
    $('.well').removeAttr('style');
    thisCheckboxIcon.parent().parent().parent().find('.well').attr('style', 'border-color: #3797fc;');
    thisCheckboxIcon.parent().parent().find('.isni-chosen').trigger('click');
  });

  // When the modal is shown...
  $('#isniSearchModal').on('show.bs.modal', function (e){
    // Remove previous search results and search value, if present.
    resultsContainer.empty();
    $('#search-query').val('');
    // Set focus on the search input.
    setTimeout(function(){
      $('#isniSearchModal input#search-query').focus();
    }, 500);
    // Enable keyboard enter/return capability on search query submit.
    $(document).keypress(function(e) {
      if(e.which === 13) {
        if($('#search-query:focus').length) {
          $('#isni-search-query-button').trigger('click');
        }
      }
    });

  });

  // When the modal is hidden...
  $('#isniSearchModal').on('hidden.bs.modal', function (e){
    // Remove previous search results and search value, if present.
    resultsContainer.empty();
    $('#search-query').val('');
  });

  function buildLabelRadioButtons(isniId, array) {
    // Declare the isniLabelsContainer variable.
    var isniLabelsContainer = '';
    // Loop through the ISNI names array.
    for (var key in array) {
      if(array[key].length) {
        // var checked = (parseInt(key) === 0) ? true : false;
        var radioButtonLabel = $('<label />').attr('for', isniId).attr('style', 'font-weight: normal;').text(array[key]),
            radioButtonLabelDiv = $('<div />').addClass('col-sm-10 col-md-10').append(radioButtonLabel),
            radioButton = $('<input />').attr('name', isniId).attr('type', 'radio').attr('value', array[key]).addClass('isni-label'),
            radioButtonDiv = $('<div />').addClass('col-sm-2 col-md-2').append(radioButton),
            radioButtonContainer = $('<div />').append(radioButtonLabelDiv, radioButtonDiv).html();
        // Populate the ISNI labels container.
        isniLabelsContainer += '<div class="row isni-labels">' + radioButtonContainer + '</div>';
      }
    }
    return isniLabelsContainer;
  }

});