<html>
<head>
	<meta charset="UTF-8">
	<title>Image / PDF Upload</title>
	<script> var loadingIconSrc = "{{{ URL::to('assets/img/loading.gif') }}}"; </script>
	<script src="{{{ asset('assets/js/tinymce/plugins/imageupload/upload.js') }}}"></script>
	<script type="text/javascript">

		@if(isset($locationArray) && is_array($locationArray) && !$error)

			@foreach($locationArray as $imageURL)
				window.parent.window.ImageUpload.uploadMultipleSuccess({
					code : "<?php echo($imageURL); ?>"
				});
			@endforeach

		@elseif($location && !$error)
			window.parent.window.ImageUpload.uploadSuccess({
				code : "<?php echo($location); ?>"
			});
		@else
			window.parent.window.ImageUpload.uploadFailure({
				code : "<?php echo($error); ?>"
			});
		@endif

	</script>
	<style type="text/css">
		img {
			max-height: 240px;
			max-width: 320px;
		}
	</style>
</head>
<body>
	{{--  Image preview is not necessary right now
		@if($location)
			<img src="<?php echo $location ?>">
		@endif
	--}}
</body>
</html>