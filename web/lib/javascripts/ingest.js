var jobId,
    resultsContainer = $('#panel-spreadsheets').find('.col-md-12'),
    loadingGif = $('<img />').attr('src', '/lib/images/spinner.gif').attr('alt', 'loading animation').attr('style', 'width: 140px;'),
    loadingGifContainer = $('<div />').addClass('center-block').attr('style', 'width: 140px;').append(loadingGif),
    textureMapFileNameParts = ['diffuse', 'normal', 'occlusion'];

// Texture map pre-validation error container.
let fileContainer = $('<div />')
    .addClass('alert alert-danger files-validation-error')
    .attr('role', 'alert')
    .html('<h4>Texture Maps Pre-validation</h4>');
// Texture map pre-validation unordered list.
let fileOL = $('<ol />');

// Dropzone.js
// Get the template HTML and remove it from the document.
// var previewNode = document.querySelector("#template");
// previewNode.id = "";
// var previewTemplate = previewNode.parentNode.innerHTML;
// previewNode.parentNode.removeChild(previewNode);

// See: http://www.dropzonejs.com/#configuration-options
var uploadsDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
  url: dropzoneUrl, // Set the url
  // parallelUploads: 1, // Default is 2
  // previewTemplate: previewTemplate,
  autoQueue: false, // Make sure the files aren't queued until manually added
  previewsContainer: "#previews", // Define the container to display the previews
  // clickable: ".fileinput-button", // Define the element that should be used as click trigger to select files.
  createImageThumbnails: false,
  maxFilesize: 20000, // how many files this Dropzone handles
  ignoreHiddenFiles: true,
  timeout: 300000, // The timeout for the XHR requests in milliseconds (default is 30000 - 30 seconds)
  chunking: true,
  // forceChunking: true,
  // parallelChunkUploads: true,
  chunkSize: 5000000,
  retryChunks: true,
  retryChunksLimit: 2000,
  acceptedFiles: acceptedFiles,
  dictInvalidFileType: "File type not allowed",
  accept: function(file, done) {

    for (var t = 0; t < textureMapFileNameParts.length; t++) {
      // Only check files which have diffuse, normal, or occlusion in the file name.
      if (file.name.indexOf(textureMapFileNameParts[t]) !== -1) {
        // FileReader() asynchronously reads the contents of files (or raw data buffers) stored on the user's computer.
        var reader = new FileReader();
        reader.onload = (function(entry) {
          // The Image() constructor creates a new HTMLImageElement instance.
          var image = new Image(); 
          image.src = entry.target.result;
          image.onload = function() {
            // Check to see if the WIDTH is a power of 2.
            if (!powerOf2(this.width)) {
              var widthLI = $('<li />').text('Width is not a power of 2 (' + file.name + ')');
              fileOL.append(widthLI);
              // done('Width is not a power of 2 (' + file.name + ')');
            }
            // Check to see if the HEIGHT is a power of 2.
            if (!powerOf2(this.height)) {
              var heightLI = $('<li />').text('Height is not a power of 2 (' + file.name + ')');
              fileOL.append(heightLI);
              // done('Height is not a power of 2 (' + file.name + ')');
            }
          };
        });
        reader.readAsDataURL(file);
      }
    }

    // Append the ordered list to the fileContainer.
    fileContainer.append(fileOL);
    done();
  }
});

// Display the texture map pre-validation error container (if there are errors).
setTimeout(function() {
  if (fileOL.find('li').length) {
    // Append the fileContainer to the panel-body container.
    $('.panel-validation-results').find('.panel-body').append();
  }
}, 5000);

// Add the ability to select directories from a file selection dialog window (when clicking the "Add Files" button).
$('body').find('input.dz-hidden-input').attr('webkitdirectory', '').attr('directory', '');

uploadsDropzone.on("drop", function(ev) {
  // Format directories and files hierarchically.
  if (ev.dataTransfer.items) {
    // Use DataTransferItemList interface to access the file(s)
    for (var i = 0; i < ev.dataTransfer.items.length; i++) {
      // Get the item's properties via webkitGetAsEntry().
      let item = ev.dataTransfer.items[i].webkitGetAsEntry();
          listing = $('#previews ul');
      // console.log('itemitemitemitem');
      // console.log(item);
      // Add the file size and modified date to 'item'.
      item.detail = ev.dataTransfer.files[i];

      // console.log(item);

      // Recursively scan files and add to the DOM.
      scanFiles(i, item, listing, uploadsDropzone.options);
    }
  }
});

uploadsDropzone.on("addedfiles", function(files) {

  var fileInput = $('body').find('.dz-hidden-input'),
      items = fileInput[0].files;

  // console.log(items);

  simulateDrop({ 
      dataTransfer: { items: [ items ] },
      preventDefault: function () {}
  });
});

function simulateDrop(ev) {
  ev.preventDefault();

  // console.log(ev.dataTransfer);

  // Format directories and files hierarchically.
  if (ev.dataTransfer.items[0].length) {
    // Use DataTransferItemList interface to access the file(s)
    for (var i = 0; i < ev.dataTransfer.items[0].length; i++) {

      // Get the item's properties via webkitGetAsEntry().
      let item = ev.dataTransfer.items[0][i],
          listing = $('#previews ul');

      // console.log(item);

      // Recursively scan files and add to the DOM.
      scanFiles(i, item, listing, uploadsDropzone.options);
    }
  }
}

// uploadsDropzone.on("error", function(file, errorMessage, xhrObject) {
//   console.log('UPLOAD ERROR');
//   console.log(file);
//   console.log(errorMessage);
//   console.log(xhrObject);
// });

uploadsDropzone.on("uploadprogress", function(file) {
  // Find the list item for this file.
  let fileListItem = $('#previews').find('[data-file-name="' + file.name + '"]');
  // Update the progress indicator for a chunked file upload while uploading (while progress is < 100%).
  if (file.upload.chunked && (Math.floor(file.upload.progress) !== 100)) {
    fileListItem.find('.badge').text(Math.floor(file.upload.progress) + '%');
  }
  // Update the progress indicator for a chunked file upload once it reaches 100%.
  if (file.upload.chunked && (file.upload.chunks.length === file.upload.totalChunkCount) && (Math.floor(file.upload.progress) === 100)) {
    fileListItem.find('.badge').text(Math.floor(file.upload.progress) + '%');
  }
  // Update the progress indicator for a non-chunked file upload.
  if (!file.upload.chunked) {
    fileListItem.find('.badge').text(Math.floor(file.upload.progress) + '%');
  }
});

// CSV Help Dialog modal trigger.
$('.csv-modal-trigger').on('click', function(e) {
  $('#csv-modal').modal('show');
});

// Prevalidate click handler.
$('.prevalidate-trigger').on('click', function(e) {

  let csvList = [];

  // Set the parentRecordId.
  let parentRecordId = parentRecordChecker();
  // Check if the parent record has been selected.
  if(!parentRecordId) return;

  // Get queued files.
  let queuedFiles = uploadsDropzone.getAcceptedFiles();
  // Check if files have been added to the queue.
  // Also check to see if there are actual files (e.g. photogrammetry scans, models), and not just CSVs.
  if(!queuedFilesChecker(queuedFiles)) return;

  // Generate a temporary job ID.
  generateJobId(parentRecordId, true);

  // Get the parent record type.
  let parentRecordType = getRecordType();

  // Display the progress modal.
  $('#uploading-modal-title').empty();
  $('#uploading-modal-title').append('Pre-Validating...');
  $('#uploading-modal-message').empty();
  $('#uploading-modal-message').append('Pre-validation in progress');
  $('#uploading-modal').modal('show');
  
  for (var i = 0; i < queuedFiles.length; i++) {

    // Set a custom parameter, 'parentRecordId', so it can be passed to the back-end.
    queuedFiles[i].parentRecordId = parentRecordId;

    // Set a custom parameter, 'parentRecordType', so it can be passed to the back-end.
    queuedFiles[i].parentRecordType = parentRecordType;

    // Get the extension of the file.
    let fileExtension = getExtension(queuedFiles[i].name);

    // Pre-validate CSV files, files count, and file names.
    if (fileExtension === 'csv') {
      // Set a custom parameter, 'prevalidate', so it can be used within the 'success' function.
      queuedFiles[i].prevalidate = true;
      // Start the upload.
      uploadsDropzone.enqueueFile(queuedFiles[i]);
      // // Don't display the progress bar.
      // document.querySelector("#total-progress").style.opacity = "0";
      csvList.push(queuedFiles[i].name)
    }

    // Pre-validate files count and names.
    if((queuedFiles[i].name === 'manifest-sha1.txt') || (queuedFiles[i].name === 'manifest-md5.txt')) {

      let allManifestFiles = [],
          allAcceptedFiles = [],
          reader = new FileReader();

      // Read the manifest file.
      reader.readAsText(queuedFiles[i]);
      reader.addEventListener('loadend', function(event) {
        let manifest = event.target.result,
            manifestArray = manifest.split(/\r?\n/),
            acceptedFiles = uploadsDropzone.getAcceptedFiles();

        // Process the manifest to get all of the files count and names.
        for (var i = 0; i < manifestArray.length; i++) {
          if(manifestArray[i].length) {
            let currentLineArray = manifestArray[i].trim().split(/\s+/),
                currentFullFilePath = currentLineArray[1].split('/');
            // Add the file to the allManifestFiles array.
            allManifestFiles.push(currentFullFilePath[currentFullFilePath.length - 1]);
          }
        }

        // Process the acceptedFiles to get all of the file names.
        for (var i = 0; i < acceptedFiles.length; i++) {
          if((acceptedFiles[i].name.indexOf('.txt') === -1) && (acceptedFiles[i].name.indexOf('.csv') === -1)) {
            allAcceptedFiles.push(acceptedFiles[i].name);
          }
        }

        // Get the difference between the two arrays of files, 
        // using the manifest as the authoritative source.
        if (allManifestFiles.length) {
          var diff = $(allManifestFiles).not(allAcceptedFiles).get();

          // If there are file-based errors, populate the panel-body.
          if(diff.length) {
            let fileMessageContainer = $('<div />').addClass('alert alert-danger files-validation-error').attr('role', 'alert').html('<h4>BagIt Manifest Pre-validation</h4>');
            let fileMessageOrderedList = $('<ol />');
            for (var i = 0; i < diff.length; i++) {
              // Ignore hidden files. For now, just Apple's .DS_Store.
              // TODO: Add more hidden file types?
              if(diff[i] !== '.DS_Store') {
                fileMessageOrderedList.append('<li>Missing file: ' + diff[i] + '</li>');
              }
              if(diff[i] === '.DS_Store') diff.splice(i, 1);
            }
            // Append the ordered list to the fileMessageContainer.
            if(fileMessageOrderedList.find('li').length) {
              fileMessageContainer.append(fileMessageOrderedList);
              // Append the fileMessageContainer to the panel-body container.
              $('.panel-validation-results').find('.panel-body').append(fileMessageContainer);
            }
          }

          // If there are no file-based errors, display a message.
          if(!diff.length) {
            // The message.
            let message = $('<div />').addClass('alert alert-success files-validation-success').attr('role', 'alert').html('<h4>BagIt Manifest Pre-validation</h4><p>No file validation errors found.</p>');
            // Append the message to the panel-body container.
            $('.panel-validation-results').find('.panel-body').append(message);
          }
        }

        // If the BagIt manifest is empty, display an error.
        if (!allManifestFiles.length) {
          let fileMessageContainer = $('<div />').addClass('alert alert-danger files-validation-error').attr('role', 'alert').html('<h4>BagIt Manifest Pre-validation</h4>');
          let fileMessageOrderedList = $('<ol />');
          fileMessageOrderedList.append('<li>BagIt manifest is empty</li>');
          fileMessageContainer.append(fileMessageOrderedList);
          // Append the fileMessageContainer to the panel-body container.
          $('.panel-validation-results').find('.panel-body').append(fileMessageContainer);
        }

      });
      
    }
    
  }

  // Run the required CSV validation.
  requiredCsvValidation(parentRecordType, csvList);

  // If there are no errors, change the states of the action buttons.
  setTimeout(function() {
    // Check to see if there are errors.
    let fileErrors = $('.panel-validation-results .panel-body').find('.files-validation-error'),
        csvErrors = $('.panel-validation-results .panel-body').find('.cvs-validation-error');
    // No errors? Change the states of the action buttons.
    if(!fileErrors.length && !csvErrors.length) {
      // Disable the "Pre-Validate" and "Clear Upload Stage" buttons.
      // $('.fileinput-button, .cancel, .prevalidate-trigger').attr('disabled', 'disabled');
      // Reveal the "Start Upload" button.
      $('.start, .pause, .cancel-upload').removeClass('hidden');
    }
  }, 2000);

  // Hide the progress modal.
  setTimeout(function() {
    $('#uploading-modal').modal('hide');
  }, 3000);

});

// Set the spreadsheet count so unique IDs can be assigned to the Handsontable containers.
let spreadsheetCount = 1;

uploadsDropzone.on("success", function(file, responseText) {

  // console.log(file);

  let fileExtension = getExtension(file.name);

  // If the uploaded file is being pre-validated...
  if((typeof file.prevalidate !== 'undefined') && file.prevalidate && (fileExtension === 'csv')) {

    // Set the file status back to 'added' and prevalidate to false.
    file.status = 'added';
    file.prevalidate = false;

    // Import limitation validation
    importLimitationValidation(file.parentRecordType, file.name);

    // Empty CSV warning
    if(responseText.csv && !JSON.parse(responseText.csv).length && (file.name !== 'file_name_map.csv')) {
      swal({
        title: 'Error',
        text: file.name + ': CSV is empty',
        icon: 'warning',
      });
    }

    // If no errors are returned, display a message.
    if(responseText.csv && JSON.parse(responseText.csv).length && (typeof responseText.error === 'undefined')) {
      // The message.
      let message = $('<div />').addClass('alert alert-success cvs-validation-success').attr('role', 'alert').html('<h4>CSV Pre-validation</h4><p><strong>' + file.name + '</strong></p><p>No CSV validation errors found.</p>');
      // Append the message to the panel-body container.
      $('.panel-validation-results').find('.panel-body').append(message);
    }

    // If errors are returned, populate the Pre-Validation Results container.
    if(responseText && (typeof responseText.error !== 'undefined')) {

      let validationErrors = JSON.parse(responseText.error);

      // Display a general summary of validation errors.
      if(validationErrors.length) {
        let csvMessageContainer = $('<div />').addClass('alert alert-danger files-validation-error').attr('role', 'alert').html('<h4>CSV Pre-validation</h4><p><strong>' + file.name + '</strong></p>');
        let csvMessageOrderedList = $('<ol />');
        // Loop through the errors and append to the message.
        for (var i = 0; i < validationErrors.length; i++) {
          listItem = $('<li />').text(validationErrors[i].row + ': ' + validationErrors[i].error);
          csvMessageOrderedList.append(listItem);
        }
        // Append the ordered list to the csvMessageContainer.
        csvMessageContainer.append(csvMessageOrderedList);
        // Append the csvMessageContainer to the panel-body container.
        $('.panel-validation-results').find('.panel-body').append(csvMessageContainer);
      }

    }

    if(responseText.csv && JSON.parse(responseText.csv).length) {
      // Display the CSV within a spreadsheet interface, highlighting errors.
      // TODO: Make it possible to edit the spreadsheet and resubmit for pre-validation.
      // See: Handsontable
      // https://github.com/handsontable/handsontable
      let container,
          hotVarName = 'hot',
          panel = $('<div />').addClass('panel panel-default panel-spreadsheet'),
          panelHeading = $('<div />').addClass('panel-heading').text(file.name),
          panelHeadingContent = $('<span />')
            .attr('style', 'font-size: 1.5rem; font-weight: normal;')
            .html('<i class="glyphicon glyphicon-info-sign" style="margin-left: 1.5rem;"></i> For now, only for representation of the data. The goal is to allow for edits and resubmission.'),
          csvsRowCount = responseText.csv_row_count,
          csvsRowCountParsed = JSON.parse(csvsRowCount),
          panelBody = $('<div />')
              .addClass('panel-body')
              .attr('id', 'csv-spreadsheet-' + spreadsheetCount)
              .attr('style', 'height: ' + (csvsRowCountParsed*40) + 'px');
          

      // Show the panel-spreadsheet container.
      $('#panel-spreadsheets').removeClass('hidden');
      // Populate the panel with the heading and body.
      panelHeading.append(panelHeadingContent);
      panel.append(panelHeading, panelBody);
      // Add the panel to the results container.
      resultsContainer.append(panel);

      // Initialize Handsontable.
      container = document.getElementById('csv-spreadsheet-' + spreadsheetCount);
      // Note: window[hotVarName + spreadsheetCount] is a dynamic variable.
      // This basically allows for multiple Handsontable instances.
      // See: https://stackoverflow.com/a/28130158/1298317
      window[hotVarName + spreadsheetCount] = new Handsontable(container, {
        data: JSON.parse(responseText.csv),
        rowHeaders: true,
        colHeaders: true,
        outsideClickDeselects: false,
        selectionMode: 'multiple',
      });

      // Style rows which have errors.
      // https://docs.handsontable.com/2.0.0/demo-selecting-ranges.html#page-styling
      // First, make sure there are errors to parse.
      // If errors are returned, populate the Pre-Validation Results container.
      if(responseText && (typeof responseText.error !== 'undefined')) {

        let validationErrors = JSON.parse(responseText.error);

        if((typeof validationErrors !== 'undefined') && validationErrors.length) {

          for (var i = 0; i < validationErrors.length; i++) {
            // validationErrors[i].row
            // validationErrors[i].error
            let row = validationErrors[i].row.match(/\d/g);
            row = row.join('');
            // Select the rows with errors.
            window[hotVarName + spreadsheetCount].selectRows(parseInt(row));

            var selected = window[hotVarName + spreadsheetCount].getSelected();

            for (var index = 0; index < selected.length; index += 1) {
              var item = selected[index];
              var startRow = Math.min(item[0], item[2]);
              var endRow = Math.max(item[0], item[2]);
              var startCol = Math.min(item[1], item[3]);
              var endCol = Math.max(item[1], item[3]);

              for (var rowIndex = startRow; rowIndex <= endRow; rowIndex += 1) {
                for (var columnIndex = startCol; columnIndex <= endCol; columnIndex += 1) {
                  // Set the text-danger CSS class on the row containing the error.
                  window[hotVarName + spreadsheetCount].setCellMeta(rowIndex, columnIndex, 'className', 'text-danger');
                }
              }
            }

          }
          window[hotVarName + spreadsheetCount].deselectCell();
          window[hotVarName + spreadsheetCount].render();
        }
      }
    }

  }

  spreadsheetCount++;
});

// // Update the total progress bar
// uploadsDropzone.on("totaluploadprogress", function(progress) {
//   document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
// });

uploadsDropzone.on("sending", function(file, xhr, formData) {

  // // Show the total progress bar when upload starts
  // document.querySelector("#total-progress").style.opacity = "1";

  xhr.timeout = 99999999;

  if((typeof file.prevalidate !== 'undefined') && !file.prevalidate) {
    // If not prevalidating, disable all actionable buttons.
    $('.start, .cancel, .prevalidate-trigger').attr('disabled', 'disabled');
  }

  // Append custom data to formData.
  // NOTE: can't console.log formData after appending.
  // If you want to view formData, pass it to the server side then dump/echo.
  // See: https://github.com/enyo/dropzone/issues/1075

  // Add the jobId to the formData
  jobId = $('.prevalidate-trigger').attr('data-jobid');
  formData.append('jobId', jobId);

  // Add the prevalidate value to the formData
  if((typeof file.prevalidate !== 'undefined') && file.prevalidate) {
    formData.append('prevalidate', file.prevalidate);
  }

  // Add the parentRecordId value to the formData
  if((typeof file.parentRecordId !== 'undefined') && file.parentRecordId) {
    formData.append('parentRecordId', file.parentRecordId);
  }

  // Add the parentRecordType value to the formData
  if((typeof file.parentRecordType !== 'undefined') && file.parentRecordType) {
    formData.append('parentRecordType', file.parentRecordType);
  }

  // If the file is actually a folder, add the fullPath to the formData.
  if(file.fullPath){
    formData.append('fullPath', file.fullPath);
  }

});

// Called when all files in the queue finish uploading.
uploadsDropzone.on("queuecomplete", function(file) {

  let jobId = $('.prevalidate-trigger').attr('data-jobid'),
      // For the Simple Ingest, the value is stored in $('body').data('project_repository_id')
      // For the Bulk Ingest, the value is stored in $('#uploads_parent_picker_form_parent_picker').attr('data-project-id')
      parentProjectId = $('body').data('project_repository_id')
          ? $('body').data('project_repository_id')
          : $('#uploads_parent_picker_form_parent_picker').attr('data-project-id'),
      parentRecordId = parentRecordChecker(),
      parentRecordType = getRecordType();

  setTimeout(function() {
    $.ajax({
      'type': 'GET'
      ,'dataType': 'json'
      ,'url': '/admin/get_job_status/' + jobId
      ,success: function(result) {
        if (result && (result !== 'cancelled') && (result !== 'paused')) {
          // Set the status of the job from 'uploading' to 'bagit validation starting'.
          $.ajax({
            'type': 'GET'
            ,'dataType': 'json'
            ,'url': '/admin/set_job_status/' + jobId + '/bagit validation starting'
            ,success: function(result) {
              if(result && result.statusSet) {
                // Redirect to the Upload overview page.
                document.location.href = '/admin/ingest/' + jobId + '/' + parentProjectId + '/' + parentRecordId + '/' + parentRecordType;
              } else {
                // TODO: catch error?
              }
            }
          });
        }
      }
    });
  }, 2000);
  
  
});

// Start the internet connectivity checker.
var connectionCheck = setInterval(checkStatus, 2000);

// Begin uploads.
document.querySelector("#actions .start").onclick = function() {
  startUpload(false);
};


var dropzoneFilesCopy = [];

// Pause/Resume uploading.
$('#actions .pause').on('click', function(e) {

  let jobId = $('.prevalidate-trigger').attr('data-jobid');
  let thisTrigger = $(this);

  // Pause the upload process.
  if (!$(this).hasClass('paused')) {
    $.ajax({
      'type': 'GET'
      ,'dataType': 'json'
      ,'url': '/admin/set_job_status/' + jobId + '/paused'
      ,success: function(result) {
        if(result && result.statusSet) {
          thisTrigger.find('span').empty().text('Resume');
          thisTrigger.addClass('paused');
          // Make a copy of all files.
          dropzoneFilesCopy = uploadsDropzone.files.slice(0);
          // Remove all files.
          uploadsDropzone.removeAllFiles();
        }
      }
    });
  }
  // Resume the upload process.
  else {
    startUpload(true, dropzoneFilesCopy);
    thisTrigger.find('span').empty().text('Pause');
    thisTrigger.removeClass('paused');
  }

});


// Cancel uploading.
document.querySelector("#actions .cancel-upload").onclick = function() {

  swal('Are you sure you wish to cancel uploading?', {
    buttons: {
      cancel: 'No',
      catch: {
        text: 'Yes',
        value: 'yes',
      }
    },
  })
  .then((value) => {
    switch (value) {
      case "yes":
        // Set the status of the job from 'uploading' to 'failed'.
        let jobId = $('.prevalidate-trigger').attr('data-jobid'),
            // For the Simple Ingest, the value is stored in $('body').data('project_repository_id')
            // For the Bulk Ingest, the value is stored in $('#uploads_parent_picker_form_parent_picker').attr('data-project-id')
            parentProjectId = $('body').data('project_repository_id')
                ? $('body').data('project_repository_id')
                : $('#uploads_parent_picker_form_parent_picker').attr('data-project-id'),
            parentRecordId = parentRecordChecker(),
            parentRecordType = getRecordType();
        
        $.ajax({
          'type': 'GET'
          ,'dataType': 'json'
          ,'url': '/admin/set_job_status/' + jobId + '/cancelled'
          ,success: function(result) {
            if(result && result.statusSet) {
              // Remove all files.
              uploadsDropzone.removeAllFiles(true);
              // Redirect to the Upload overview page.
              document.location.href = '/admin/ingest/' + jobId + '/' + parentProjectId + '/' + parentRecordId + '/' + parentRecordType;
            } else {
              // TODO: catch error?
            }
          }
        });
        break;
      default:
    }
  });

};

// Remove files from the uploads stage.
document.querySelector("#actions .cancel").onclick = function() {
  // Remove files from Dropzone.
  uploadsDropzone.removeAllFiles(true);
  $('#previews ul.directory-structure').empty();
  // Clear previous validation results from the validation panel.
  $('.panel-validation-results').find('.panel-body').empty();
  // Hide the Start, Pause, and Cancel buttons.
  $('.start, .pause, .cancel-upload').addClass('hidden');
  // Remove spreadsheets.
  resultsContainer.empty();
};

function startUpload(restart, dropzoneFilesCopy) {

  dropzoneFilesCopy = (typeof dropzoneFilesCopy !== 'undefined') ? dropzoneFilesCopy : [];

  // Check if the parent record has been selected.
  let parentRecordId = parentRecordChecker();
  // Get queued files.
  let queuedFiles = uploadsDropzone.getAcceptedFiles();

  // If this is not a restart...
  if(!restart) {
    // Check if files have been added to the queue.
    // Also check to see if there are actual files (e.g. photogrammetry scans, models), and not just CSVs.
    if(!queuedFilesChecker(queuedFiles)) return;
    // Generate a real job ID.
    generateJobId(parentRecordId, false);
  }

  // Display the uploading overlay.
  $('.overlay-uploading').removeClass('hidden');

  // Give some time (2 seconds) to create the job record in the database.
  setTimeout(function() {

    // Bring back the progress badges.
    $('#previews').find('.badge-temp').removeClass('badge-temp').addClass('badge');
    // Get the job ID from the data attribute of the prevalidate-trigger.
    let jobId = $('.prevalidate-trigger').attr('data-jobid');

    // // If this is a Simple Ingest, we still need to add the capture_dataset to the job_import_record metadata storage.
    // if ($('body').data('ajax')) {

    //   var postData = {
    //     'record_table': 'capture_dataset',
    //     'job_uuid': jobId,
    //     'project_repository_id': $('body').data('project_repository_id'),
    //     'capture_dataset_repository_id': $('body').data('capture_dataset_repository_id')
    //   };

    //   // AJAX: add the capture_dataset to the job_import_record metadata storage.
    //   $.ajax({
    //     'type': 'POST'
    //     ,'dataType': 'json'
    //     ,'url': '/admin/post_job_import_record'
    //     ,'data': postData
    //     ,success: function(data) {
    //       if(data) {
    //         // Response data handler.
    //         if(data.id) {
    //           // Does anything need to be returned. Not really.
    //         } else {
    //           // Error handling???
    //         }
    //       }
    //     }
    //   });
      
    // }

    // Begin the uploading process.
    if(restart) {

      // Set the status back to 'uploading'.
      $.ajax({
        'type': 'GET'
        ,'dataType': 'json'
        ,'url': '/admin/set_job_status/' + jobId + '/uploading'
        ,success: function(result) {
          if(result && result.statusSet) {

            if (!dropzoneFilesCopy.length) {
              // Make a copy of all files.
              dropzoneFilesCopy = uploadsDropzone.files.slice(0);
              // Remove all files.
              uploadsDropzone.removeAllFiles();
            }

            var fileNames = [];

            // Add back files which haven't been uploaded.
            $.each(dropzoneFilesCopy, function(i, file) {

              // Remove files with the status of "uploading".
              if (file.status === 'uploading') {
                uploadsDropzone.removeFile(file);
              }

              // Add the remaining files back to Dropzone.
              if ((file.status !== 'success') && (fileNames.indexOf(file.name) === -1)) {
                file.status = 'added';
                file.accepted = true;
                file.prevalidate = false;
                file.processing = false;
                uploadsDropzone.addFile(file);
              }

              fileNames.push(file.name);

            });

            uploadsDropzone.enqueueFiles(uploadsDropzone.getFilesWithStatus(Dropzone.ADDED));
          }
        }
      });

    }

    if(!restart) {
      uploadsDropzone.enqueueFiles(uploadsDropzone.getFilesWithStatus(Dropzone.ADDED));
    }

  }, 2000);
}

function parentRecordChecker() {
  // Parent record ID value.
  // For the Simple Ingest, the value is stored in $('body').data('item_repository_id')
  // For the Bulk Ingest, the value is stored in $('#uploads_parent_picker_form_parent_picker')
  let parentRecordId = $('body').data('item_repository_id')
      ? $('body').data('item_repository_id')
      : $('#uploads_parent_picker_form_parent_picker').val();
  // If there is no parent ID selected, then display an alert.
  if(!parentRecordId.length) {
    swal({
      title: 'Select Parent Record',
      text: 'Please select a parent record.',
      icon: 'warning',
    });
    return false;
  } else {
    return parentRecordId;
  }
}

function getRecordType() {
  // Parent record text value.
  // For the Simple Ingest, the value is automatically '[ item ]'.
  // For the Bulk Ingest, the value is stored in $('#uploads_parent_picker_form_parent_picker_text').val()
  let parentRecordText = $('body').data('ajax') ? '[ item ]' : $('#uploads_parent_picker_form_parent_picker_text').val();
  // Get the string found between the brackets (e.g. [ subject ] would return 'subject').
  parentRecordText = parentRecordText.substring(parentRecordText.lastIndexOf("[")+1,parentRecordText.lastIndexOf("]"));
  // Clean it up...
  parentRecordText = parentRecordText.toLowerCase();
  parentRecordText = parentRecordText.trim();
  parentRecordText = parentRecordText.replace(' ', '_');
  return parentRecordText;
}

function queuedFilesChecker(files) {

  // Display an alert if there are no files queued.
  if(!files.length) {
    swal({
      title: 'No Files Found',
      text: 'Please add files to the upload queue.',
      icon: 'warning',
    });
    return false;
  }

  // Display an alert if the 'manifest-sha1.txt' or 'manifest-md5.txt' file isn't found in the queue.
  let filenames = [];
  // Build-out an array of files present.
  for (var i = 0; i < files.length; i++) {
    // Get the extension of the file.
    let fname = files[i].name;
    // Add it to the filenames array.
    filenames.push(fname);
  }

  if((filenames.indexOf('manifest-sha1.txt') === -1) && (filenames.indexOf('manifest-md5.txt') === -1)) {
    var span = document.createElement('span');
    span.innerHTML = 'Please add bagged (via BagIt) capture datasets and/or model files to the upload queue.';
    swal({
      title: 'File bag not found',
      content: span,
      icon: "warning",
    });
    return false;
  }

  return true;
}

function generateJobId(parentRecordId, temporary) {

  if((typeof temporary !== 'undefined') && temporary) {
    // Generate a temporary jobId.
    var tempJobId = 'temp-' + Math.floor(Math.random() * 2000) + 1;
    // Add the job ID as a data attribute of the prevalidate-trigger.
    $('.prevalidate-trigger').attr('data-jobid', tempJobId);
  } else {

    // Get the parent record type.
    let recordType = getRecordType();

    // Generate a real jobId.
    $.ajax({
      'type': 'GET'
      ,'global': false
      ,'dataType': 'text'
      ,'url': '/admin/create_job/' + parentRecordId + '/' + recordType
      ,success: function(result) {
        if(result) {
          var res = JSON.parse(result);
          // Add the job ID as a data attribute of the prevalidate-trigger.
          $('.prevalidate-trigger').attr('data-jobid', res.uuid);
          // Add the parent Project's id as a data attribute of the prevalidate-trigger.
          $('#uploads_parent_picker_form_parent_picker').attr('data-project-id', res.projectId);
        }
      }
    });
  }

}

function getExtension(fileName) {
  let fileNameArray = fileName.split('.');
  return fileNameArray[fileNameArray.length-1].toLowerCase();
}

// Import limitation validation
function importLimitationValidation(parentRecordType, fileName) {

  let generateWarning = false;

  // Subject import limitation message, based on the 'subject' parent record type.
  if((parentRecordType === 'subject') && (fileName === 'subjects.csv')) {
    generateWarning = true;
  }

  // Subject and Item import limitation message, based on the 'item' parent record type.
  if((parentRecordType === 'item') && ((fileName === 'subjects.csv') || (fileName === 'items.csv'))) {
    generateWarning = true;
  }

  // Subject, Item, and Capture Dataset import limitation message, based on the 'capture_dataset' parent record type.
  if((parentRecordType === 'capture_dataset') && ((fileName === 'subjects.csv') || (fileName === 'items.csv') || (fileName === 'capture_datasets.csv'))) {
    generateWarning = true;
  }

  if(generateWarning) {
    // The message.
    let importLimitationMessage = $('<div />')
        .addClass('alert alert-warning cvs-validation-error')
        .attr('role', 'alert')
        .html('<strong>' + fileName + '</strong>: Warning! The chosen parent record type is a "' + parentRecordType + '", therefore data within the "' + fileName + '" file will not be imported.');
    // Append the message to the panel-body container.
    $('.panel-validation-results').find('.panel-body').append(importLimitationMessage);
  }

}

// Required CSV validation
function requiredCsvValidation(parentRecordType, csvList) {

  let generateWarning = false,
      requiredCsvFilenames = ['subjects.csv','items.csv','capture_datasets.csv','models.csv','file_name_map.csv'],
      csvTargetFile = '',
      errorText = '';

  if (parentRecordType.length && csvList.length) {

    if (parentRecordType === 'project') {
      requiredCsvs = 'file_name_map.csv, subjects.csv, items.csv, capture_datasets.csv';
    }
    if (parentRecordType === 'subject') {
      requiredCsvs = 'file_name_map.csv, items.csv, capture_datasets.csv';
    }
    if (parentRecordType === 'item') {
      requiredCsvs = 'file_name_map.csv, capture_datasets.csv';
    }

    // File name map - no file_name_map.csv found
    if ( csvList.indexOf('file_name_map.csv') === -1 ) {
      csvTargetFile = 'file_name_map.csv';
      generateWarning = true;
    }

    // Project as parent record - no subjects.csv found
    if ( (parentRecordType === 'project') && (csvList.indexOf('subjects.csv') === -1) ) {
      csvTargetFile = 'subjects.csv';
      generateWarning = true;
    }

    // Project as parent record - no items.csv found
    if ( (parentRecordType === 'project') && (csvList.indexOf('items.csv') === -1) ) {
      csvTargetFile = 'items.csv';
      generateWarning = true;
    }

    // Project as parent record - no capture_datasets.csv found
    if ( (parentRecordType === 'project') && (csvList.indexOf('capture_datasets.csv') === -1) ) {
      csvTargetFile = 'capture_datasets.csv';
      generateWarning = true;
    }

    // Subject as parent record- no items.csv found
    if ( (parentRecordType === 'subject') && (csvList.indexOf('items.csv') === -1) ) {
      csvTargetFile = 'items.csv';
      generateWarning = true;
    }

    // Subject as parent record- no capture_datasets.csv found
    if ( (parentRecordType === 'subject') && (csvList.indexOf('capture_datasets.csv') === -1) ) {
      csvTargetFile = 'capture_datasets.csv';
      generateWarning = true;
    }

    // Item as parent record - no capture_datasets.csv found
    if ( (parentRecordType === 'item') && (csvList.indexOf('capture_datasets.csv') === -1) ) {
      csvTargetFile = 'capture_datasets.csv';
      generateWarning = true;
    }

    if (generateWarning) {
      errorText = '<strong>' + csvTargetFile + ':</strong> Not found. Required CSV files: ' + requiredCsvs;
    }

    // Check to see if all CSVs are named correctly.
    let csvNamesDiff = $(csvList).not(requiredCsvFilenames).get();

    if (csvNamesDiff.length) {
      // Loop through the result.
      for (var i = 0; i < csvNamesDiff.length; i++) {
        csvTargetFile = csvNamesDiff[i];
        errorText += '<br><br><strong>' + csvTargetFile + ':</strong> CSV file name does not meet file naming requirements. Acceptable file names: ' + requiredCsvFilenames.join(', ');
        generateWarning = true;
      }
    }

    if (generateWarning) {
      // The message.
      let requiredCsvMessage = $('<div />')
          .addClass('alert alert-danger cvs-validation-error')
          .attr('role', 'alert')
          .html(errorText);
      // Append the message to the panel-body container.
      $('.panel-validation-results').find('.panel-body').append(requiredCsvMessage);
    }

  }

}

function scanFiles(i, item, container, dzOptions, existingCount) {

  // Ignore hidden files if Dropzone's 'ignoreHiddenFiles' option is set to true.
  if (dzOptions.ignoreHiddenFiles && item.name.substring(0, 1) === '.') {
    return;
  }

  let itemIcon = item.isDirectory ? 'folder-close' : 'file',
      itemExtension = !item.isDirectory ? item.name.split('.').pop() : '',
      fileTypeWarning = (itemExtension.length && (dzOptions.acceptedFiles.indexOf(itemExtension.toLowerCase()) === -1)) ? 'Invalid file type. This file will not be uploaded.' : '',
      spanIcon = $('<span />').addClass('glyphicon glyphicon-' + itemIcon).attr('style', 'margin-right: 4px;'),
      allItemsCount = $('#previews').find('li.list-group-item').length,
      thisCount = (allItemsCount+1),
      listItem = $('<li />')
          .addClass('list-group-item')
          .attr('data-count', thisCount)
          .attr('data-file-name', item.name)
          .attr('style', 'border-left: none; border-right: none; border-top-left-radius: 0; border-top-right-radius: 0;')
          .text(item.name),
      progressBadge = '<span class="badge-temp"></span>',
      errorMessageContainer = '<div class="pull-right text-danger" style="font-size: 1.3rem;">' + fileTypeWarning + '</div>';

  // Add elements to the list item.
  listItem.prepend(spanIcon).append(errorMessageContainer, progressBadge);
  // Add the list item to the container.
  container.append(listItem);
  
  // Directory handler
  if (item.isDirectory) {
    let directoryReader = item.createReader();
    let directoryContainer = $('<ul />').attr('style', 'padding-left: 15px;').attr('data-file-name', item.name);
    // Add an unordered list to serve as the directory container.
    container.append(directoryContainer);
    // Read file entries within a directory.
    directoryReader.readEntries(function(entries) {
        entries.forEach(function(entry) {
          scanFiles(i++, entry, directoryContainer, dzOptions, existingCount);
      });
    });
  }

}

function checkStatus() {

  var online = navigator.onLine;

  if(!online) {

    swal('Detected lost internet connection. Would you like to resume the upload process?', {
      buttons: {
        cancel: 'No',
        catch: {
          text: 'Yes',
          value: 'yes',
        }
      },
    })
    .then((value) => {
      switch (value) {
        case "yes":
          startUpload(true);
          break;
        default:
          clearInterval(checkStatus);

          // Set the status of the job from 'uploading' to 'failed'.
          let jobId = $('.prevalidate-trigger').attr('data-jobid'),
              // For the Simple Ingest, the value is stored in $('body').data('project_repository_id')
              // For the Bulk Ingest, the value is stored in $('#uploads_parent_picker_form_parent_picker').attr('data-project-id')
              parentProjectId = $('body').data('project_repository_id')
                  ? $('body').data('project_repository_id')
                  : $('#uploads_parent_picker_form_parent_picker').attr('data-project-id'),
              parentRecordId = parentRecordChecker(),
              parentRecordType = getRecordType();
          
          $.ajax({
            'type': 'GET'
            ,'dataType': 'json'
            ,'url': '/admin/set_job_status/' + jobId + '/cancelled'
            ,success: function(result) {
              if(result && result.statusSet) {
                // Display the confirmation message.
                swal('Upload process cancelled. Redirecting to the overview page...');
                // Redirect to the Upload overview page.
                setTimeout(function() {
                  document.location.href = '/admin/ingest/' + jobId + '/' + parentProjectId + '/' + parentRecordId + '/' + parentRecordType;
                }, 3000);
              } else {
                // TODO: catch error?
              }
            }
          });
      }
    });

  }

}

// Determine whether an integer is a power of 2.
function powerOf2(num) {
  if (typeof num !== 'number') 
      return false;
  return Number.isInteger(Math.log2(num));
}