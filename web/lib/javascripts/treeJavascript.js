jQuery(document).ready(function($) {

  const urlParts = window.location.href.split('/');
  const isProjectsPage = (urlParts.indexOf('projects') !== -1) ? true : false;

  // Only initialize jsTree for project pages.
  if(isProjectsPage) {

    // Add a list item and jsTree container div to the left navigation.
    $('#main_side_nav').find('li a:contains("Browse Projects")').filter(function(index) {
      const thisListItem = $(this).parent(),
          thisStyle = thisListItem.is(':visible') ? '' : ' style="display: none;"';
      thisListItem.after('<li style="padding: 0 0 0 18px;' + thisStyle + '"><div id="jstree"' + thisStyle + '></div></li>');
    });

    // Modify jsTree's default configuration.
    $.jstree.defaults.core.themes.name = 'proton';
    $.jstree.defaults.core.themes.dots = false;

    // Initialize jsTree.
    $('#jstree').jstree({
      'core' : {
        'data' : {
            'url' : function (node) {
              return node.id === '#' ? '/projects/get_projects' : '/projects/get_subjects/' + node.id;
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

  }

});