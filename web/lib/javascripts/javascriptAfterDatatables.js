jQuery(document).ready(function($) {

	var urlSplit = window.location.href.split('/');
	var currentPath = urlSplit.slice(3);

	if((typeof currentPath[1] !== 'undefined') && currentPath[1].length) {

	  var remove_button = $('<button></button>')
	        .addClass('btn btn-default glyphicon glyphicon-trash')
	        .attr('style', 'width: 12rem;')
	        .attr('id', 'remove-records-button');

	  $('.datatables_bulk_actions').prepend(remove_button);

	}

});