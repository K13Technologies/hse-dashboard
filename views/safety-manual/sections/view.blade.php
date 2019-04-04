@extends('webApp::layouts.withNav')
@section('styles')
@parent
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <style>
        .back-to-top {
            position: fixed;
            bottom: 2em;
            right: 0px;
            text-decoration: none;
            color: #000000;
            background-color: rgba(235, 235, 235, 0.80);
            font-size: 15px;
            padding: 1em;
            display: none;
        }

        .back-to-top:hover {    
            background-color: rgba(135, 135, 135, 0.50);
            text-decoration: none;
        }

        #revisionDescriptionBox {
            min-height: 50px;
        }

        .sectionContainer {
            margin-top: -25px;
        }

        .subsectionContentBox{
            /*Constrain size of box so page doesn't get too long*/
            max-height: 700px;
            overflow-y: scroll;
        }

        #tableOfContentsContainer {
            font-size: 16pt;
        }

        #addSectionBtn {
            margin-top:15px;
        }

        #tableOfContentsContainer {
            min-height: 100px;
        }

        .TOCAddSubsectionButtonContainer {
            font-size: 0;
            display: inline-block;
        }

        .TOCSubsectionList li {
            list-style-type: none;
        }

        #sectionActionContainer {
           /* position: absolute;
            top: 150px;*/
        }

       /* hide these items while loading */
       .safetyManualContainer, #footer {
           display:none;
       }
        .loadingTitle {
            margin-top: 15%;
        }
        .preloadContainer
        {
            background-color: white;
            position: fixed;
            height: 85%;
            width: 98%;
            text-align: center;
        }
        .preload { 
            top: 50%;
        }

    </style>
@stop

@section('content')
<!--=============== START MODALS ===============-->
<div class="modal" id="sendEmailModal">
  <div class="modal-dialog">
    <div class="modal-content">
        {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="modalEmailExport"> Email Safety Manual </h4> 
            </div>
            <div class="modal-body">
                <h5> Please enter an email address (multiple separated by commas):</h5>
                <input type='text' class='hide' name='sectionId'/>
                <input type='text' class='hide' name='subsectionId'/>
                {{ Form::textarea('email','',array('placeholder'=>'email@address.com','class'=>'form-control','id'=>'email')) }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Cancel</button>
                {{ Form::button('Send',array('class'=>'btn btn-orange medium pull-right','id'=>'completeExportToMail')) }}
                <span id='mailError' style="color:red"> </span>
            </div>
        {{ Form::close() }}
    </div>
  </div>
</div>
<!--=============== END MODALS ===============-->


<!--===============  START VISIBLE CONTENT ===============-->

<div class="preloadContainer">
    <h1 class='loadingTitle'>Loading Safety Manual Section...<h1>
    <div class="preload">
        <img src="{{{ URL::to('assets/img/loading_black.gif')}}}">
    </div>
</div>

<div class='container safetyManualContainer'>
    <a href="#" class="back-to-top"><i class='glyphicon glyphicon-arrow-up'></i></a>

    <div class='row well whiteBackground'>
        <div class='col-md-12'>
            <div class='row' id='sectionActionContainer'>
                <div class='col-md-12'>
                    <div class='text-right'>
                        <button type="button" class="btn btn-success editSafetyManualBtn">Save Manual</button>
                        <div class="btn-group">
                          <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Export <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu">
                            <li><a data-bind='attr: {href: site + "safety-manual/export/" + {{ $sectionId }} }'> Export Section to PDF</a></li>
                            <li><a href="#sendEmailModal" data-toggle="modal" type='button' data-bind='attr: {"data-section-id": {{ $sectionId }}}'>Send Section via Email</a></li>
                          </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div data-bind='foreach: sections'>
                <div data-bind='if: $data.section_id() == {{ $sectionId }}' class='sectionContainer'>
                    <br/>
                    <div class="section-header-label" style='font-size:20px;' data-bind='text: ($index() + 1)+ ". " + section_title()'></div>
                    <br/>
                    <input class='form-control' type="text" data-bind="attr: {'name':'tasks['+ $index()+'][title]'}, value: section_title, valueUpdate: 'afterkeydown'" required placeholder="Section Title"></input>
                    <hr/>

                    <div class='row'>
                        <div class='col-md-8' id="tableOfContentsContainer">
                            <div class="list-component" style='font-size:18px;'>Table of Contents</div>
                            <div class='row'>
                                <ul>
                                    <!-- ko foreach: subsections -->
                                        <!-- ko if: isDeleted() == false -->
                                            <li>
                                                <a data-bind="attr: {'href':'#subsection'+ ($parentContext.$index() + 1) + ($index() + 1)}, text: ($parentContext.$index() + 1) + '.' + ($index() + 1) + '. ' + subsection_title()" style='font-size:16px;'></a>
                                            </li>
                                        <!-- /ko -->
                                    <!-- /ko -->
                                </ul> 
                            </div>
                            <button type="button" class="btn btn-xs btn-primary" data-bind='click: addSubsection'>Add Subsection</button>
                        </div>
                    </div>
                    <hr/>

                    <div data-bind="foreach: subsections">
                        <!-- ko if: isDeleted() == false -->
                            <div class="subsectionContainer col-md-12" data-bind="attr: {'id':'subsection'+ ($parentContext.$index() + 1) + ($index() + 1)}"> 
                                <span class="list-component" data-bind="text:( ($parentContext.$index() + 1) + '.' + ($index() + 1) + '. ' + subsection_title() )"></span>
                                <span class='actionContainer pull-right'>
                                    <button type="button" class="btn btn-success editSafetyManualBtn">Save Manual</button>
                                    <button type="button" class="btn btn-primary" data-bind='click: showEditorSwitch, disable: showEditor() == true'>Edit Subsection Content</button>
                                    <div class="btn-group" data-bind='visible: subsection_id() != 0'>
                                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Export <span class="caret"></span>
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a href="#sendEmailModal" data-toggle="modal" type='button' data-bind='attr: {"data-section-id": $parent.section_id(), "data-subsection-id": subsection_id }'>Send Subsection via Email</a></li>
                                        <li><a data-bind='attr: {href: site + "safety-manual/export/" + $parent.section_id() + "/" + subsection_id() }'>Export Subsection to PDF</a></li>
                                      </ul>
                                    </div>
                                    <button type="button" class="btn btn-warning" data-bind='click: $parent.removeSubsection'>Delete Subsection</button>
                                </span>
                                <br/><br/>
                                <input class='form-control' type="text" data-bind="attr: {'name':'tasks['+ $index()+'][title]'}, value: subsection_title, valueUpdate: 'afterkeydown'" required placeholder="Subsection Title"></input>
                                <br/>
                                <!-- ko if: showEditor() == false -->
                                    <!--<div data-bind='html: subsection_content, click: showEditorSwitch' class='subsectionContentBox well whiteBackground' data-toggle="tooltip" title="Click to edit this subsection" href="#"></div>-->
                                    <div data-bind='html: subsection_content' class='well whiteBackground subsectionContentBox'></div>
                                <!-- /ko -->
                                <!-- ko if: showEditor() == true -->
                                    <textarea data-bind="wysiwyg: subsection_content, wysiwygConfig: $root.wysiwygOptions"></textarea>
                                <!-- /ko -->

                                <br/>
                                <hr/>
                            </div>
                        <!-- /ko -->
                    </div>
                    <button type="button" class="btn btn-primary" data-bind='click: addSubsection'>Add Subsection</button>
                    <br/>
                    <br/>
                    <br/>
                </div>
            </div> 
        </div>
    </div>
    <form id="my_form"  enctype="multipart/form-data" method="post" action="{{URL::to('safety-manual/upload-image')}}" style="width:0px;height:0;overflow:hidden">
        <input name="image" type="file" onchange="$('#my_form').submit();this.value='';">
    </form>
</div> <!-- container-fluid--> 
@stop

@section('scripts')
@parent 
<script src="{{{ asset('assets/js/wkss/safety-manual.js') }}}"></script>
<script src="{{{ asset('assets/js/tinymce/tinymce.min.js') }}}"></script>
<script src="{{{ asset('assets/js/tinymce/tinymce-knockout-binding.min.js') }}}"></script>
<script src="{{{ asset('assets/js/tinymce/jquery.tinymce.min.js') }}}"></script>
<script>
    var safetyManual = {{ json_encode($safetyManual) }}; 
</script>
@stop