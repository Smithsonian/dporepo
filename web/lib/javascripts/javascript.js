$(document).ready(function(){
  const urlSplit = window.location.href.split('/');
  const currentPageProjects = (urlSplit.indexOf('projects') !== -1) ? true : false;
  const currentPageResources = (urlSplit.indexOf('resources') !== -1) ? true : false;

  if(currentPageProjects) {
    $("#main_side_nav li.nav-header.projects i").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
    $("#main_side_nav li.projects").show();
  }

  if(currentPageResources) {
    $("#main_side_nav li.nav-header.resources i").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
    $("#main_side_nav li.resources").show();
  }
});