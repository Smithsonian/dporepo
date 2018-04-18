$(document).ready(function(){
	validatebag();
	updatebag();
	function validatebag(){
		$('#btn-validate').on("click",function(){
			$('#message div').html('');
			var folderpath = $('#bagiturl').val();
			var url = '/admin/bagit/check';
			var data = {folderpath:folderpath};
			if (folderpath == '') {
				$('#message div').hide().html('<p class="alert alert-warning">Folder Path is required...</p>').slideDown(500);
			}else{
				$.post(url,data,function(response){
					$("#btn-update").attr("disabled","disabled");
					$('#message div').html('');
					if(response.files && response.files.length > 0){
						var fileslength = '';
						var wording = '';
						if (response.files.length == 1) {
							fileslength = 'is';
							wording = 'file';
						}else{
							fileslength = 'are';
							wording = 'files';
						}
						$('#message div').hide().html('<div class="alert alert-warning">There '+fileslength+' '+response.files.length+' required missing '+wording+'<br> <ul id="list-missing"></ul></div>').slideDown(200);
						for (var i = 0; i < response.files.length; i++) {
							$('#list-missing').append("<li>"+response.files[i]+"</li>");
						}
						$("#btn-update").removeAttr("disabled");
					}else{
						$('#message div').hide().html('<p class="alert alert-success">This folder does not required updating.</p>').slideDown(500);
					}
				},'json');
			}
		});
	}
	function updatebag(){
		$('#btn-update').on("click",function(){
			$("#btn-update").attr("disabled","disabled");
			$('#message div').html('');
			var folderpath = $('#bagiturl').val();
			var url = '/admin/bagit/update';
			var data = {folderpath:folderpath};
			if (folderpath == '') {
				$('#message div').hide().html('<p class="alert alert-warning">Folder Path is required...</p>').slideDown(500);
			}else{
				$.post(url,data,function(response){
					$('#message div').html('');
					if(response.flag == true){
						$('#message div').hide().html('<p class="alert alert-success">Folder Updated with required files</p>').slideDown(500);
					}
					else {
            $('#message div').hide().html('<p class="alert alert-fail">No no no! Failed apocalyptically.</p>').slideDown(500);
          }
				},'json');
			}
		});
	}
})