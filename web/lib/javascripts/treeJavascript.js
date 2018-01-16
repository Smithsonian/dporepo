jQuery(document).ready(function($) {

  const urlParts = window.location.href.split('/');
  const isProjectsPage = (urlParts.indexOf('projects') !== -1) ? true : false;

  // Only initialize jsTree for project pages.
  // if(isProjectsPage) {

    // Modify jsTree's default configuration.
    $.jstree.defaults.core.themes.name = 'proton';
    $.jstree.defaults.core.themes.dots = false;

    // Initialize jsTree.
    $('#jstree').jstree({
      'core' : {
        'data' : {
            'url' : function (node) {
              return node.id === '#' ? '/admin/projects/get_projects' : '/admin/projects/get_subjects/' + node.id;
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
    $('#main_side_nav').on('click', '#jstree li a', function(e) {
      document.location.href = this.href;
    });

  // }

});