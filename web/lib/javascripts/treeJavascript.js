jQuery(document).ready(function($) {

  const urlParts = window.location.href.split('/');

  // Modify jsTree's default configuration.
  $.jstree.defaults.core.themes.name = 'proton';
  $.jstree.defaults.core.themes.dots = false;

  // Initialize jsTree.
  $('#jstree').jstree({
    'core' : {
      'data' : {
          'url' : function (node) {

            switch(true) {
              case (node.id === '#'):
                return '/admin/projects/get_stakeholder_guids';
                break;
              case $.isNumeric(node.id):
                var numberFirst = $('#name-number-first').find('button.btn-primary').attr('id') === 'number-first' ? true : false;
                return '/admin/projects/get_subjects/' + node.id + '/' + numberFirst;
                break;
              default:
                return '/admin/projects/get_stakeholder_projects/' + node.id;
            }

          },
          'data' : function (node) {
            return { 'id' : node.id };
          },
          'complete': function(result) {
            if(result.status === 200) {
              return result.responseJSON;
            } else {
              console.log('Not found. Status code: ' + result.status);
            }
          }
      }
    }
  });

  // On page load, expand the target node if there's a project ID in the URL route. 
  $('#jstree').on('ready.jstree', function(e, data) {
      // Invoked after jstree has loaded.
      if(urlParts[5] !== 'undefined') $(this).jstree('open_node', urlParts[5]);
  });

  // jsTree prevents the default anchor href action, so it need to be overridden.
  $('#jstree').on('click', 'a', function(e) {
    document.location.href = this.href;
  });

  // Name First / Number First Setup on Page Load
  if (typeof(Storage) !== 'undefined') {
    var nameNumberFirst = localStorage.getItem('name-number-first');
    if(!nameNumberFirst) {
      $('#name-first').addClass('btn-primary');
    } else {
      $('#name-number-first').find('button').removeClass('btn-primary').addClass('btn-default');
      $('#name-number-first').find('#' + nameNumberFirst).addClass('btn-primary');
    }
  }

  // Name First / Number First Click Handler
  $('#name-number-first').on('click', 'button', function(e) {
    var thisButton = $(this);
    var thisButtonId = thisButton.attr('id');
    $('#name-number-first').find('button').removeClass('btn-primary').addClass('btn-default');
    thisButton.addClass('btn-primary');

    if (typeof(Storage) !== 'undefined') {
      localStorage.setItem('name-number-first', thisButtonId);
    }
  });

});