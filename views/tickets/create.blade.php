@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/darkroom.css') }}}" rel="stylesheet">
     <link href="{{{ asset('assets/css/bootstrap-datetimepicker.css') }}}" rel='stylesheet'>
     <link href="{{{ asset('assets/css/chosen/bootstrap-chosen.css') }}}" rel="stylesheet">
     <style>
        .frame{
            border:1px solid #ccc;
            padding:5px;
        }
        .frame>img{
            display:block;
            width:100%;
        }
        #controls{text-align:center}
        #controls a{display:inline-block;padding:0 5%;height:50px;line-height:50px;font-size:20px;font-weight:300;color:#888}
        #controls a:hover{color:#fff;text-decoration:none}

        .filedrag {
            border: 2px dashed #AAA;
            border-radius: 7px;
            cursor: default;
            padding: 15px;
            margin-bottom: 20px;
        }

        .filedrag .drag-label {
            font-weight: bold;
            text-align: center;
            display: block;
            color: #AAA;
            margin-top: 10px;
            margin-bottom: -10px;
        }

        /*.filedrag.hover {
            border-color: #00CC00;
            border-style: solid;
            box-shadow: inset 0 3px 4px #888;
        }*/

        .filedrag.hover .drag-label {
            color: #00CC00;
        }

        .custom-file-input-wrapper {
            position: relative;
        }

        .custom-file-input-wrapper .custom-file-input-button {
            position: relative;
            overflow: hidden;
        }

        .custom-file-input-wrapper .custom-file-input-button * {
            cursor: pointer;
        }

        .custom-file-input-wrapper .custom-file-input-button input[type="file"] {
            position: absolute;
            right: 0;
            top: 0;
            cursor: pointer;
            opacity: 0;
        }

        .btn-upload {
            margin-right: 5px;
            position: relative;
            overflow: hidden;
        }
        .btn-upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }

        #PROGRESS {
            margin-right: 5px;
            width: 170px;
        }
     </style>
@stop

@section('content')

<!-- MODALS START -->

<!-- MODALS END -->


<div class='container'>

{{-- Form::open(array('url' => URL::to("tickets/create"), 'method' => 'POST', 'files' => true, 'target' => 'upload_target', 'id'=>'addTicketForm')) --}}

@if(!isset($workers))
    <div class='well whiteBackground'>
        <h1>You have no workers!<h1>
    </div>
@else

{{ Form::open(array('id'=>'addTicketForm', 'name' => "createTicketForm")) }} 

    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif

    <div class='well whiteBackground'>
        <div class="section-header-label"> Add Ticket </div>

        <div class="content-label">Who is this ticket for?</div>
        {{ Form::select('worker_id', [ null => 'Select a Worker'] + $workers, [null => 'Select a Worker'], ['class'=>'chzn-select form-control', 'data-bind' => 'chosen: null , value: worker_id']) }}
        
        <div class="content-label">Ticket Name</div>
        {{ Form::text('type_name', null, array('class'=>'form-control', 'required', 'data-bind' => 'value: type_name')) }}
        
        <div class="content-label">Is this ticket specific to your company?</div>
        <input type='checkbox' name='issued_internally' data-bind='checkboxpicker: isInternalTicket'></input>

        <div data-bind='visible: !isInternalTicket()'>
            <div class="content-label">Ticket Issuer Organization Name</div>
            {{ Form::text('issuer_organization_name', null, array('class'=>'form-control', 'data-bind' => 'value: issuer_organization_name')) }}
        </div>

        <div class="content-label">Expiry Date</div>
        <div class='input-group date'>
            {{ Form::text('expiry_date', null, array('class'=>'form-control', 'pattern' => '\d\d\d\d-\d\d-\d\d', 'required' , 'data-bind' => 'dateTimePicker: { format: "YYYY-MM-DD" }, value: expiry_date')) }}
        </div>

        <div class="content-label">Ticket Description</div>
        {{ Form::textarea('description', null, array('class'=>'form-control', 'data-bind' => 'value: description')) }}

        <br/>

        <div data-bind='foreach: newPhotos'>
            <div class="well" data-bind="fileDrag: fileData, attr: { id: 'imageContainer' + uniqueId() }">
                <div class='row text-right'>
                    <button type='button' style='margin-right:13px;' class='btn btn-warning' data-bind='click: $root.removeImage'>Remove Photo</button>
                    <br/><br/>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <div data-bind="ifnot: fileData().dataURL">
                            <label class="drag-label">Drag Image Here</label>
                        </div>
                        <div class='well' data-bind='visible: fileData().dataURL'>
                            <div style='width:100%; height: 100%;' class='text-center'>
                                <!-- dark room-->
                                <img data-bind="attr: { src: fileData().dataURL, id: 'target' + uniqueId() }" style="max-width: 400px; max-height: 300px;">
                            </div>   
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!--<input type="file" style='height:35px;' data-bind="fileInput: fileData, 
                                                                                      customFileInput: {
                                                                                      buttonClass: 'btn btn-success',
                                                                                      fileNameClass: 'disabled form-control',
                                                                                      onClear: onClear
                                                                                  }" accept="image/*">-->

                        <div class='content-label'>
                            <h4>Image Editing Instructions (please read carefully for best results):</h4>
                            <ul>
                                <li>Rotate as needed</li>
                                <li>If you crop, be sure to press the checkmark or X to confirm changes</li>
                                <li>If images appear blurry it is because you need to crop the image because it is too large</li>
                                <li><b>*Exactly what you see in the editor is exactly what will be saved*</b></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr/>
        <div class='content-label text-right'>
            <!-- <button type='button' class='btn btn-primary pull-right' data-bind='click: addImage'>Add Image</button> -->
            <progress id="PROGRESS" value=0></progress>
            <br/>
            <span class="btn btn-default btn-upload">
                Add PDF <input type="file" id='pdf' accept="application/pdf"> 
            </span>  

            <span class="btn btn-default btn-upload">
                Add Image <input type="file" id='imageUpload' accept="image/*">
            </span> 
            <br/>
        </div>
   </div>

    <div class='well whiteBackground'>
        <div class='text-center content-label'>
            * By creating this ticket, you as {{ $user->first_name }} {{ $user->last_name }} (Management) approve and certify that this ticket is valid.
        </div>
        <br/>
        <button id="submitTicketBtn" type='submit' class='form-control btn btn-info'>Add Ticket</button>
    </div>

   {{ Form::close() }}   
</div> 
@endif 

@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/knockout-3.4.0.js') }}}"></script>
    <script src="{{{ asset('assets/js/bootstrap-checkbox.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/knockout-file-bindings.js') }}}"></script>
    <script src="{{{ asset('assets/js/fabric.js') }}}"></script>
    <script src="{{{ asset('assets/js/darkroom.js') }}}"></script>
    <script src="{{{ asset('assets/js/moment-js/moment-with-locales.js') }}}"></script>
    <script src="{{{ asset('assets/js/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/chosen/chosen.jquery.js') }}}"></script>
    <script src="{{{ asset('assets/js/processing-api.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/ticket-create.js') }}}"></script>
    <script src="{{{ asset('assets/js/pdf-js/compatibility.js') }}}"></script>
    <script src="{{{ asset('assets/js/pdf-js/pdf.combined.js') }}}"></script>
@stop