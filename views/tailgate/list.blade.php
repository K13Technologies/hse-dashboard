@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- MODALS BEGIN -->
<div id="modalTailgateDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteTailgateLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editTailgateLabel"> Delete Tailgate </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteTailgateForm')) }}
                    {{ Form::text('delete_tailgate_id','',array('class'=>'hide','id'=>'delete_tailgate_id')) }}
                    <h5> Are you sure you want to delete this Tailgate? </h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteTailgateButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!-- MODALS END -->

<div class='container'>
    <div class="list-container">
      @if (Session::get('error'))
          <div style="color:red"> {{ Session::get('error') }}</div><br/>
      @endif
      @if (Session::get('message'))
          <div style="color:green"> {{ Session::get('message') }}</div><br/>
      @endif
      <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component">All Tailgates</span>
        </div>
        <div class="pull-right">
             
        </div>
      </div>
      <div class="list-container-body">
        <table class="table-list table-hover dataTable" id="tailgateTable">
              <thead>
                <tr>
                  <th> Date</th>
                  <th> Title</th>
                  <th> Locations </th>
                  <th> STARS Site </th>
                  <th> LSDS </th>
                  <th> Status </th>
                  <th class="action-column"> Actions </th>
                </tr>
              </thead>
              <tbody id="adminsTable">
                  @foreach ($tailgates as $tailgate)
                    @if($tailgate->deleted_at == NULL)
                      <tr class="clickable-row">
                          <td onclick='window.document.location="{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}"'> 
                            {{ WKSSDate::display($tailgate->ts, $tailgate->created_at, WKSSDate::FORMAT_LIST) }} 
                          </td>
                          <td onclick='window.document.location="{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}"'> 
                            {{ $tailgate->title }} 
                          </td>
                          <td onclick='window.document.location="{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}"'> 
                            {{ implode(", ",array_pluck($tailgate->locations()->get()->all(),'location')) }}  
                          </td>
                          <td onclick='window.document.location="{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}"'> 
                            {{ $tailgate->stars_site }} 
                          </td>
                          <td onclick='window.document.location="{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}"'> 
                            {{ implode(", ",array_pluck($tailgate->lsds()->get()->all(),'lsd')) }}  
                          </td>
                          <td onclick='window.document.location="{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}"'> 
                              @if ($tailgate->completion instanceOf JobCompletion)
                                  {{ "Completed" }}
                              @else
                                  {{ "No Job Completion" }}                                         
                              @endif
                          </td>
                          <td class="action-column"> 
                              <!--<a href='{{ URL::to("tailgates/view",array($tailgate->tailgate_id)) }}'><i class="icon-eye-open"></i></a>!-->
                              <a href='{{ URL::to("tailgates/export",array($tailgate->tailgate_id)) }}'><i class="glyphicon glyphicon-file"></i></a>
                              <a href="#" id="delete_{{ $tailgate->tailgate_id}}" class="deleteTailgateLink"><i class="glyphicon glyphicon-trash"></i></a>
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
    <script src="{{{ asset('assets/js/wkss/tailgate-management.js') }}}"></script>
@stop