jQuery(document).ready(function($) {

  var remove_button = $('<a />')
        .addClass('btn btn-default btn-sm')
        .attr('id', 'remove-records-button')
        .attr('href', 'javascript:void(0);')
        .html('<span class="glyphicon glyphicon-trash"></span> Remove Records');

  $('.datatables_bulk_actions').prepend(remove_button);

});