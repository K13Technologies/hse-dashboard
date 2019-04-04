@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- MODALS START -->
<div id="modalFLHADelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteFLHALabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editFLHALabel"> Delete FLHA </h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteFLHAForm')) }}
                <div class="modal-body">
                        {{ Form::text('delete_flha_id','',array('class'=>'hide','id'=>'delete_flha_id')) }}
                        <h5> Are you sure you want to delete this FLHA? </h5>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                    {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteFLHAButton')) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- MODALS END -->

<div class='container'>    
    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif

   <div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">All FLHAs</span>
              </div>
              <div class="pull-right">
                   
              </div>
            </div>
            <div class="list-container-body">
                 <table class="table-list table-hover dataTable" id="flhaTable">
                    <thead>
                      <tr>
                        <th> Date </th>
                        <th> Title</th>
                        <th> Location</th>
                        <th> Site</th>
                        <th> LSD </th>
                        <th> Client</th>
                        <th> Status </th>
                        <th class="action-column"> Actions </th>
                      </tr>
                    </thead>
                    <tbody id="adminsTable">
                        @foreach ($flhas as $flha)
                            @if($flha->deleted_at == NULL)
                                <tr class="clickable-row">
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> 
                                        {{ WKSSDate::display($flha->ts, $flha->created_at, WKSSDate::FORMAT_LIST) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> 
                                        {{ $flha->title }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> 
                                        {{ $flha->location }} 
                                        {{ implode(", ",array_pluck($flha->locations()->get()->all(),'location')) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> {{ $flha->site }}
                                        {{ implode(", ",array_pluck($flha->sites()->get()->all(),'site')) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> 
                                        {{ $flha->lsd }} 
                                        {{ implode(", ",array_pluck($flha->lsds()->get()->all(),'lsd')) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> 
                                        {{ $flha->client }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("flha/view",array($flha->flha_id)) }}"'> 
                                        @if ($flha->completion instanceOf JobCompletion)
                                            {{ "Completed" }}
                                        @else
                                            @if (abs(strtotime($flha->created_at)- time())/3600>12)
                                                {{ "No Job Completion" }}                                         
                                            @else
                                                {{ "Started" }}    
                                            @endif
                                        @endif
                                    </td>
                                    <td class="action-column"> 
                                        <a href='{{ URL::to("flha/export",array($flha->flha_id)) }}'><i class="glyphicon glyphicon-file"></i></a>
                                        <a href="#" id="delete_{{ $flha->flha_id}}" class="deleteFLHALink"><i class="glyphicon glyphicon-trash"></i></a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>    
    </div>    
@stop


@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/wkss/flha-management.js') }}}"></script>
@stop