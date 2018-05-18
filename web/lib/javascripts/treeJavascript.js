jQuery(document).ready(function($) {

  const urlParts = window.location.href.split('/');

  // Modify jsTree's default configuration.
  $.jstree.defaults.core.themes.name = 'proton';
  $.jstree.defaults.core.themes.dots = false;

  // console.log(urlParts);

  // Initialize jsTree.
  // See: https://www.jstree.com/
  $('#jstree').jstree({
    'cache': false,
    'state': { 'key': 'dpoJsTree' },
    'plugins' : [
      'state'
    ],
    'core' : {
      'data' : {
          'url' : function (node) {

            switch(true) {
              // Projects
              case (node.id.indexOf('stakeholderGuid-') !== -1):
                return '/admin/projects/get_stakeholder_projects_tree_browser/' + node.id.replace('stakeholderGuid-', '');
                break;
              // Subjects
              case (node.id.indexOf('projectId-') !== -1):
                var projectId = node.id.replace('projectId-', ''),
                    numberFirst = $('#name-number-first').find('button.btn-primary').attr('id') === 'number-first' ? true : false;
                return '/admin/projects/get_subjects/' + projectId + '/' + numberFirst;
                break;
              // Items
              case (node.id.indexOf('subjectId-') !== -1):
                var subjectId = node.id.replace('subjectId-', ''),
                    numberFirst = $('#name-number-first').find('button.btn-primary').attr('id') === 'number-first' ? true : false;
                return '/admin/projects/get_items/' + subjectId;
                break;
              // Datasets
              case (node.id.indexOf('itemId-') !== -1):
                var itemId = node.id.replace('itemId-', ''),
                    numberFirst = $('#name-number-first').find('button.btn-primary').attr('id') === 'number-first' ? true : false;
                return '/admin/projects/get_datasets/' + itemId;
                break;
              // Dataset Elements
              case (node.id.indexOf('datasetId-') !== -1):
                var datasetId = node.id.replace('datasetId-', ''),
                    numberFirst = $('#name-number-first').find('button.btn-primary').attr('id') === 'number-first' ? true : false;
                return '/admin/projects/get_dataset_elements/' + datasetId;
                break;
              // Tree Root
              default:
                return '/admin/projects/get_stakeholder_guids';
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

  // jsTree: close rest of the branches when opening a branch.
  // Taken from: https://stackoverflow.com/questions/33629671/jstree-close-rest-of-the-branches-when-opening-a-branch
  $('#jstree').on('open_node.jstree', function (e, data) {

    var nodesToKeepOpen = [];

    // Get all parent nodes to keep open.
    $('#' + data.node.id).parents('.jstree-node').each(function() {
      nodesToKeepOpen.push(this.id);
    });

    // Add current node to keep open.
    nodesToKeepOpen.push(data.node.id);

    // Close all other nodes.
    $('.jstree-node').each( function() {
      if( nodesToKeepOpen.indexOf(this.id) === -1 ) {
        $("#jstree").jstree().close_node(this.id);
      }
    });

  });

  // jsTree prevents the default anchor href action, so it need to be overridden.
  $('#jstree').on('click', 'a', function(e) {
    document.location.href = this.href;
  });

  // jsTree: On page load, expand the target node if there's a project ID in the URL route. 
  $('#jstree').on('ready.jstree', function(e, data) {
    // Invoked after jstree has loaded.
    if(urlParts[5] !== 'undefined') $(this).jstree('open_node', urlParts[5]);
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