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

  /**
   * Set/Remove Favorites
   */
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

  /**
   * Resizable Columns
   */
  $(function() {
    var resizableEl = $('.resizable').not(':last-child'),
        columns = 12,
        fullWidth = resizableEl.parent().width(),
        columnWidth = fullWidth / columns,
        totalCol, // This is filled by start event handler.
    updateClass = function(el, col) {
      el.css('width', ''); // Remove width, our class already has it.
      // Set the offset value.
      var offsetClassValue = '';
      if(el.hasClass('main')) {
        offsetClassValue = (col <= 8) ? ' col-sm-offset-4' : ' col-sm-offset-3';
        offsetClassValue = (col <= 7) ? ' col-sm-offset-5' : offsetClassValue;
        offsetClassValue = (col === 9) ? ' col-sm-offset-3' : offsetClassValue;
        offsetClassValue = (col > 9) ? ' col-sm-offset-2' : offsetClassValue;
      }
      el.removeClass(function(index, className) {
        return (className.match(/(^|\s)col-\S+/g) || []).join(' ');
      }).addClass('col-sm-' + col + offsetClassValue);
    };
    // jQuery UI Resizable
    resizableEl.resizable({
      handles: 'e',
      start: function(event, ui) {
        var
          target = ui.element,
          next = target.next(),
          targetCol = Math.round(target.width() / columnWidth),
          nextCol = Math.round(next.width() / columnWidth);
        // Set totalColumns globally.
        totalCol = targetCol + nextCol;
        target.resizable('option', 'minWidth', columnWidth);
        target.resizable('option', 'maxWidth', 560);
      },
      resize: function(event, ui) {
        var target = ui.element,
            next = target.next(),
            targetColumnCount = Math.round(target.width() / columnWidth),
            nextColumnCount = Math.round(next.width() / columnWidth),
            targetSet = totalCol - nextColumnCount,
            nextSet = totalCol - targetColumnCount;

        updateClass(target, targetSet);
        updateClass(next, nextSet);
      },
    });
  });

  /**
   * Left Navigation
   */
  $("#main_side_nav li.nav-header").on("click",function(){
    $(this).nextUntil("li.nav-header").slideToggle('fast');
    $(this).find("i").toggleClass("glyphicon glyphicon-chevron-down");
    $(this).find("i").toggleClass("glyphicon glyphicon-chevron-right");
  });
  $("#hide_side_nav").on('click',function(){
    var nav_bar = $(this).closest(".sidebar-nav");
    nav_bar.removeClass("col-sm-3, col-md-2").hide();
    var content_bar = nav_bar.next("div");
    content_bar.removeClass("col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2");
    content_bar.addClass("col-sm-12 col-md-12");
    $("#show_side_nav").show();
  });
  $("#show_side_nav").on('click',function(){
    var nav_bar = $("#hide_side_nav").closest(".sidebar-nav");
    nav_bar.addClass("col-sm-3, col-md-2").show();
    var content_bar = nav_bar.next("div");
    content_bar.removeClass("col-sm-12 col-md-12");
    content_bar.addClass("col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2");
    $("#show_side_nav").hide();
  });

});