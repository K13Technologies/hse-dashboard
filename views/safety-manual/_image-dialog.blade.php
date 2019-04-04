<html>
<head>
	<meta charset="UTF-8">
	<title>Image / PDF Upload Dialog</title>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
	<script> var loadingIconSrc = "{{{ URL::to('assets/img/loading.gif') }}}"; </script>
</head>
<body>
<div class="container">
	<div class="row col-md-10 col-md-offset-1">
		<div id="upload_form">
			<p>
				<!-- Change the url here to reflect your image handling controller -->
				{{ Form::open(array('url' => URL::to("safety-manual/upload-image"), 'method' => 'POST', 'files' => true, 'target' => 'upload_target')) }}
				{{ Form::file('imagefile', ['onChange' => 'this.form.submit(); ImageUpload.inProgress();', 'accept' => 'image/*, .pdf']) }}
				{{ Form::close() }}
			</p>
		</div>
		<div id="image_preview" style="display:none; font-style: helvetica, arial;">
			<iframe frameborder=0 scrolling="no" id="upload_target" name="upload_target" height=240 width=320></iframe>
		</div>
	</div>
	<script src="{{{ asset('assets/js/tinymce/plugins/imageupload/upload.js') }}}"></script>
</div>
</body>
</html>

