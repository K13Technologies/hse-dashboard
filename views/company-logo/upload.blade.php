@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/jquery.Jcrop.min.css') }}}" rel="stylesheet"/>
     <link href="{{{ asset('assets/css/wkss/company-logo.css') }}}" rel="stylesheet"/>
@stop

@section('content')
   <div class='container'>
    <h3> Manage Company Logo <h3>
    <div class="well whiteBackground">
                @if (Session::has('message'))
                     <div style='color:green'> {{ Session::get('message') }} </div>
                @endif
                <!-- upload form -->
                <form id="upload_form"  enctype="multipart/form-data" method="post" action="{{URL::to('upload-company-logo')}}" onsubmit="return checkForm()">
                    <!-- hidden crop params -->
                    <input type="hidden" id="x1" name="x1" />
                    <input type="hidden" id="y1" name="y1" />
                    <input type="hidden" id="x2" name="x2" />
                    <input type="hidden" id="y2" name="y2" />

                    <h4>Please select image</h4>
                    <div><input type="file" name="image_file" id="image_file" onchange="fileSelectHandler()" autocomplete="off" /></div>

                    <div class="error"></div>

                    <div class="step2 row-fluid">
                        <div class="span6">
                            <br/>
                            <h4>Select area to be used</h4>
                                {{ Form::radio('photo_action', 'keep', true, array('id'=>'keep')); }}      
                                {{ Form::label('keep', 'Use the photo in the current format', array('class'=>'control-label')) }}                          
                                <br/>
                                {{ Form::radio('photo_action', 'crop', false, array('id'=>'crop')); }}                          
                                {{ Form::label('crop', 'Crop the photo', array('class'=>'control-label')) }}                         
                                <br/>
                                <br/>
                                {{ Form::submit('Upload',array('class'=>'btn-orange medium')) }}
                        </div>
                        
                        <div class="span6">
                        <h5>Photo Preview</h5>
                        <img id="preview" />

                        <div class="info hide">
                            <label>File size</label> <input type="text" id="filesize" name="filesize" />
                            <label>Type</label> <input type="text" id="filetype" name="filetype" />
                            <label>Image dimension</label> <input type="text" id="filedim" name="filedim" />
                            <label>W</label> <input type="text" id="w" name="w" />
                            <label>H</label> <input type="text" id="h" name="h" />
                        </div>

                        </div>
                        
                      
                    </div>
                {{ Form::close() }}
        </div> 
    </div>
</div>

@stop


@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/jquery.Jcrop.min.js') }}}"></script>
  <script src="{{{ asset('assets/js/wkss/script.js') }}}"></script>
@stop