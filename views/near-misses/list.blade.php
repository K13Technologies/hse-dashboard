@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- START MODALS !-->
<div id="modalNearMissDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteNearMissLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteNearMissLabel"> Delete Near Miss </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteNearMissForm')) }}
                    {{ Form::text('delete_near_miss_id','',array('class'=>'hide','id'=>'delete_near_miss_id')) }}
                    <h5> Are you sure you want to delete this near miss? </h5>
            </div>
            <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                    {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteNearMissButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!-- END MODALS !-->
             
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
                  <span class="list-component">All Near Misses</span>
              </div>
              <div class="pull-right">
                   
              </div>
            </div>
            <div class="list-container-body">
                 <table class="table-list table-hover dataTable" id="nearMissTable">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Action Req'd</th>
                        <th>Action Completed</th>
                        <th>LSD</th>
                        <th>Activity</th>
                        <th>Hazard Categories</th>
                        <th class="action-column">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="adminsTable">
                        @foreach ($nearMisses as $n)
                            @if($n->deleted_at == NULL) 
                                <tr class="clickable-row">
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'> 
                                        {{ WKSSDate::display($n->ts,$n->created_at, WKSSDate::FORMAT_LIST) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'> 
                                        {{ $n->title }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'> 
                                        {{ $n->site }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'>
                                        @if ($n->corrective_action_applied==0 && $n->completed_on == NULL)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'>
                                        @if ($n->corrective_action_applied==0)
                                            @if ($n->completed_on != NULL)
                                                {{ $n->completed_on }}
                                            @else
                                                Outstanding
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'> 
                                        {{ $n->lsd }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'> 
                                        {{ $n->hazard_activity->activity_name }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("near-misses/view",array($n->near_miss_id)) }}"'> 
                                        @if (count($n->hazard_categories)) 
                                            {{ implode(', ',array_pluck($n->hazard_categories->toArray(),'category_name')) }}
                                        @endif
                                    </td>
                                    <td class="action-column">
                                        <a href='{{ URL::to("near-misses/export",array($n->near_miss_id)) }}'><i class="glyphicon glyphicon-file"></i></a>
                                        <a href="#" id="delete_{{ $n->near_miss_id}}" class="deleteNearMissLink"><i class="glyphicon glyphicon-trash"></i></a>
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
  <script src="{{{ asset('assets/js/wkss/near-miss-management.js') }}}"></script>
@stop