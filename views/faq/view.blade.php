@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/faq.css') }}}" rel="stylesheet"/>
@stop

@section('content')
    <div id="modalDelete" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteFAQLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 id="deleteFAQLabel"> Delete FAQ </h4>
        </div>
        <div class="modal-body">
            {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteFAQ')) }}

                {{ Form::text('delete_faq_id','',array('class'=>'hide','id'=>'delete_faq_id')) }}

                <h5> Are you sure you want to delete this FAQ ? </h5>

        </div>
        <div class="modal-footer">
            <button class="btn-grey pull-left" data-dismiss="modal" aria-hidden="true">NO</button>
            {{ Form::button('YES',array('class'=>'btn-orange medium pull-right','id'=>'deleteFAQButton')) }}
            {{ Form::close() }}
        </div>
    </div>


    <div class='container'>

     @if ($shouldAdd)
     <h3> Manage FAQ's <h3>
        <div class="well whiteBackground">
            {{ Form::open(array('url'=>URL::to('faq/add'))) }}
            <h5 class="faq-lbl">Question</h5>
            <input type="text" id="question" name ='question'/><br/>
            <h5 class="faq-lbl">Answer</h5> 
           <textarea rows="3" id="answer" name='answer'></textarea> <br/>
           <div class="center"> 
            @if(Session::has('addError'))
                <div style="font-size:14px;color:red"> {{ Session::get('addError') }} </div>
            @endif
           {{ Form::submit('Add this FAQ',array('class'=>'btn-orange medium')) }}
           </div> 
            {{ Form::close() }}
            
        </div>
     @else
        <h3> Frequently Asked Questions <h3>  
     @endif

     @foreach($faqs as $faq)
        <div class="well whiteBackground">
            <?php if (Session::has('editError')){
                $error = Session::get('editError');
                $id = $error['id'];
                $text= $error['text'];
            }?>
            @if (Session::has('editError') && $id == $faq->faq_id)
                <div class="static-content hide">
            @else
                <div class="static-content">
            @endif
                    <h4> {{ $faq->question }} </h4>
                    <p> {{ nl2br($faq->answer) }}</p>
                </div>
           
           
            @if ($shouldAdd)
                @if (Session::has('editError') && $id == $faq->faq_id)
                    <div class="edit-content ">
                @else
                    <div class="edit-content hide">
                @endif
            {{ Form::open(array('url'=>URL::to('faq/edit'))) }}
                <h5 class="faq-lbl">Question</h5>
                <input type="hidden" name ='faq_id' value="{{ $faq->faq_id }}"/><br/>
                <input type="text" class="question" name ='question' value="{{ $faq->question }}"/><br/>
                <h5 class="faq-lbl">Answer</h5> 
               <textarea rows="3" class="answer" name='answer'>{{ $faq->answer }}</textarea> <br/>
               <div class="center"> 
                @if (Session::has('editError') && $id == $faq->faq_id)
                    <div style="font-size:14px;color:red"> {{ $text }} </div>
                @endif
               {{ Form::submit('Save this FAQ',array('class'=>'btn-orange medium')) }}
               </div> 
            {{ Form::close() }}
            </div>
            
            <div class="top-right-action-bar">
                <button id="edit_{{ $faq->faq_id }}" class="editFAQLink btn-grey small" title="Edit this FAQ"><i class="icon-white icon-pencil"></i></button> <br/>
                <button id="delete_{{ $faq->faq_id }}" class="deleteFAQLink btn-grey small" title="Delete this FAQ"><i class="icon-white icon-trash"></i></button> 
            </div>
            @endif
        </div>
     @endforeach

   </div>

@stop


@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/wkss/faq.js') }}}"></script>
@stop