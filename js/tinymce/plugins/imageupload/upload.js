var ImageUpload = {
	inProgress : function() {
		document.getElementById("upload_form").innerHTML = '<br><p style="height: 20px;">Uploading File...<img src="'+loadingIconSrc+'" style="height: 20px; width: 20px; margin-left: 10px; margin-bottom: 10px;"/></p>';
	},
	uploadSuccess : function(result) {
		document.getElementById("image_preview").style.display = 'block';
		document.getElementById("upload_form").innerHTML = '<br><p>Upload Success!</p>';
		parent.tinymce.EditorManager.activeEditor.insertContent('<img src="' + result.code +'">');
	},
	uploadMultipleSuccess : function(result) {
		document.getElementById("upload_form").innerHTML = '<br><p>Upload Success!</p>';
		parent.tinymce.EditorManager.activeEditor.insertContent('<img src="' + result.code +'">');
	},
	uploadFailure : function(error) {
		document.getElementById("upload_form").innerHTML = '<br><p>' + error.code + '</p>';
	}
};