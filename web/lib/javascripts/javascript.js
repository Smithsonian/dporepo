$(document).ready(function(){

  const urlSplit = window.location.href.split('/');
  const currentPageProjects = (urlSplit.indexOf('projects') !== -1) ? true : false;
  const currentPageResources = (urlSplit.indexOf('resources') !== -1) ? true : false;
  const currentPath = urlSplit.slice(3);

  if(currentPageProjects) {
    $("#main_side_nav li.nav-header.projects i").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
    $("#main_side_nav li.projects").show();
  }

  if(currentPageResources) {
    $("#main_side_nav li.nav-header.resources i").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
    $("#main_side_nav li.resources").show();
  }

  // Set/remove favorites
  $('.custom-checkbox .glyphicon').on('click', function(e) {

    const favoritePath = '/' + currentPath.join('/');
    const pageTitle = $('h1').text();

    setTimeout(function() {

      if($('#favorite_toggle').is(':checked')) {

        $.ajax({
            type: 'POST'
            ,dataType: 'json'
            ,url: '/admin/add_favorite/'
            ,data: ({ 'favoritePath': favoritePath, 'pageTitle': pageTitle })
            ,success: function(result) {

              if(result) {
                swal({
                  title: 'Page Added',
                  text: 'This page has been added to your favorites.',
                  icon: 'success',
                  button: 'Close',
                });
              }

            }
        });

      } else {

        $.ajax({
            type: 'POST'
            ,dataType: 'json'
            ,url: '/admin/remove_favorite/'
            ,data: ({ 'favoritePath': favoritePath, 'pageTitle': pageTitle })
            ,success: function(result) {

              if(result) {
                swal({
                  title: 'Page Removed',
                  text: 'This page has been removed from your favorites.',
                  icon: 'success',
                  button: 'Close',
                });
              }

            }
        });

      }

    }, 500);

  });

});