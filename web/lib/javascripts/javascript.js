jQuery(document).ready(function($) {

  const urlSplit = window.location.href.split('/');
  const currentPageProjects = (urlSplit.indexOf('projects') !== -1) ? true : false;
  const currentPageResources = (urlSplit.indexOf('resources') !== -1) ? true : false;
  const currentPath = urlSplit.slice(3);
  const formErrors = $('body').data('errors');

  /**
   * Highlight form fields with validation errors.
   */
  for (var key in formErrors) {
    $('#' + key).attr('style', 'border-color: red;');
  }

  /**
   * Using the Chosen jQuery plugin for Select Form Fields
   * https://harvesthq.github.io/chosen/
   */
  $('select').chosen({
    max_selected_options: 1,
    width: '60%',
    allow_single_deselect: true,
    no_results_text: 'Oops, nothing found!'
  });
  
  /**
   * Set the Active Navigation Tab
   */
  $('.nav-tabs li').each(function(e) {
    const thisItem = $(this);
    thisItem.removeClass('active');
    if(currentPath[1] === thisItem.attr('id')) {
      thisItem.addClass('active');
    } else if(currentPath[1].length === 0) {
      $('.nav-tabs li#admin').addClass('active');
    } else if (currentPath[1] === 'projects') {
      $('.nav-tabs li#workspace').addClass('active');
    }
  });

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
      // Remove width, our class already has it.
      el.css('width', '');
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
        var target = ui.element,
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

});