@extends('webApp::layouts.withNav')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/nivo-slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/vehicle-management/vehicle-details.css') }}}" rel="stylesheet">
@stop

@section('content')
                 
<!-- MODALS START -->
  <div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                  <h4 id="modalEmailExport"> Email this Inspection Card</h4>
              </div>
              {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                <div class="modal-body">
                      <h5> Please enter an email address (multiple emails separated by commas):</h5>

                      {{ Form::text('vehicle_id',$vehicle->vehicle_id,array('class'=>'hide')) }}
                      @if ($inspection)
                          {{ Form::text('inspection_id',$inspection->inspection_id,array('class'=>'hide','id'=>'inspection_id')) }}
                      @endif
                      {{ Form::textarea('email','',array('placeholder'=>'email@address.com','rows'=>'3','class'=>'form-control','id'=>'email')) }}
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
<!-- MODALS END -->

<div class='container'>
  <div class="row">
    <div class='col-md-6'>
      <div class='well whiteBackground'>
        <p class="list-component">Vehicle Information</p>
        <input type='hidden' value='{{$vehicle->vehicle_id}}' id='vehicleId'/>
        <div class='row'>
          <div class='col-md-4'>
            @if ($vehicle->photos()->count())
              <div id="list-container-vehicle-photo" class="well well-sm">
                  <?php $i=0;?>
                  @foreach ($vehicle->photos as $p)
                    <a href="{{ Photo::generic($p->name) }}" data-lightbox="vehicle-photos" <?php echo ($i>0)?'class="hide"':''; $i++?> >
                        <img style='height: 100%; width: 100%;' src="{{ Photo::generic($p->name) }}" />
                    </a>
                  @endforeach 
              </div>
            @else
                <div id="list-container-vehicle-photo" class="well well-sm">
                  <img style='height: 100%; width: 100%;' src="{{ URL::to('image/no-photo') }}" />
                </div>
            @endif
          </div>
          <div class='col-md-4'>
            <div class="content-label">License plate</div>
            <div class="content-description"> {{ $vehicle->license_plate }} </div>
            <div class="content-label">Vehicle number</div>
            <div class="content-description"> {{ $vehicle->vehicle_number }} </div>
            <div class="content-label">Vehicle color</div>
            <div class="content-description"> {{ $vehicle->color }} </div>
          </div>
          <div class='col-md-4'>
            <div class="content-label">Make </div>
            <div class="content-description"> {{ $vehicle->make }} </div>
            <div class="content-label">Model </div>
            <div class="content-description"> {{ $vehicle->model }} </div>
            <div class="content-label">Mileage </div>
            <div class="content-description"> {{ $vehicle->mileage }} </div>
          </div>
        </div>
      </div>

      <div class='row'>
        <div class='col-md-12'>
          <div class='well whiteBackground'>
            <p class="list-component">All Inspections</p>
            <br/>
            <table class="table-list table-hover dataTable" id="vehicleInspectionsTable">
              @if (count($vehicle->inspections))
              <thead>
                  <tr>
                      <th>Inspection Date</th>
                      <th>Location</th>
                      <th>Mileage</th>
                      <!--<th class="action-column">ACTION</th>-->
                  </tr>
              </thead>
              <tbody>
                @foreach ($vehicleInspections as $i)
                    <tr class="clickable-row view-inspection <?php echo($inspection->inspection_id == $i->inspection_id)?'selected':'' ?>" id="inspection_{{ $i->inspection_id }}" >
                        <td>
                            {{ WKSSDate::display($i->ts, $i->created_at) }}
                        </td>
                        <td> {{ $i->location }} </td>
                        <td> {{ $i->mileage }} </td>
                            <!--<td class="action-column"!-->
                            <!--<a href="#" class="editInspectionLink"><i class="icon-pencil"></i></a>-->
                            <!--<a href="#" class="deleteInspectionLink"><i class="icon-trash"></i></a>-->
                        <!--</td>-->
                    </tr>
                @endforeach
              </tbody>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>  
      
    <div class="col-md-6">
      @if ($inspection)
        <div class='well whiteBackground'>
          <span class="list-component">Inspection Details</span>
          <span class="pull-right export-button">
              <a href='{{ URL::to("vehicle-management/export",array($vehicle->vehicle_id,$inspection->inspection_id)) }}'>
                         <button class="btn btn-orange small">
                             Export to PDF
                             <i class="glyphicon glyphicon-file"></i>
                         </button>
                   </a>
          </span>
          <span class="pull-right export-button">
              <a href="#modalEmailSave" data-toggle="modal">
                  <button class="btn btn-orange small">
                      Send via email
                      <i class="glyphicon glyphicon-envelope"></i>
                  </button>
              </a>
          </span>

          <div id="inspection-details-container">
            <div class="inspection-header-label"> Date </div>
            <div class="inspection-details-content">
              <span title="{{ $inspection->created_at }}"> 
                  {{ WKSSDate::display($inspection->ts, $inspection->created_at) }} 
              </span> 
            </div>
                      <div class="inspection-header-label"> Engine </div>
                      <div class="inspection-details-content" id="engine-content">
                          <?php $issues = 0; ?>
                          @foreach($inspection->getProperties() as $p)
                                @if(preg_match("/^engine/",$p))
                                  @if(trim($inspection->$p)!='')
                                      <?php $issues++; ?>
                                      <div class="inspection-label"> {{ Inspection::getPartName($p) }}</div>
                                      <div class="content-description"> {{ $inspection->$p }}</div>
                                      @if (array_key_exists($p, $inspection->componentPhotos) && count($inspection->componentPhotos[$p]))
                                      <?php $i = 0; ?>
                                          @foreach ($inspection->componentPhotos[$p] as $photo) 
                                          <a href="{{ Photo::generic($photo->name) }}"  data-lightbox="{{ $p."_photo" }}"> <button class="btn-orange small {{ $i?'hidden':' ' }}"> View Pictures <i class="icon-picture icon-white"></i></button></a>
                                              <?php $i++; ?>
                                          @endforeach 
                                      @endif
                                      <div class="required-action-item">
                                          <?php $currentAction =  $inspection->actions[$p]; ?>
                                          <form class="action-form" inspection_action_id="{{ $currentAction->inspection_action_id }}">
                                              <p class="required-action-completed-text"> Completed on </p>
                                              <input type='text' name='completed_on' class="form-control action-date-picker" placeholder="yyyy-mm-dd" value='{{ $currentAction->completed_on }}'/>
                                              <br/>
                                              {{ Form::textarea('action',$currentAction->action ,array('rows'=>4,'class'=>'form-control','placeholder'=>'Describe the required action for this item')) }}
                                          </form>
                                      </div>
                                  @else
                                          <div class="inspection-label"> {{ Inspection::getPartName($p) }}</div>
                                          @if($inspection->$p===NULL)
                                              <div class="content-description"> N/A </div>
                                          @else
                                              <div class="content-description"> Pass. </div>
                                          @endif
                                          @if (array_key_exists($p, $inspection->componentPhotos) && count($inspection->componentPhotos[$p]))
                                          <?php $i = 0; ?>
                                              @foreach ($inspection->componentPhotos[$p] as $photo) 
                                              <a href="{{ Photo::generic($photo->name) }}"  data-lightbox="{{ $p."_photo" }}"> <button class="btn-orange small {{ $i?'hidden':' ' }}"> View Pictures <i class="icon-picture icon-white"></i></button></a>
                                                  <?php $i++; ?>
                                              @endforeach 
                                          @endif
                                  @endif
                                @endif
                          @endforeach
                      </div>
                      <div class="inspection-header-label"> Interior </div>
                      <div class="inspection-details-content" id="interior-content">
                          <?php $issues = 0; ?>
                          @foreach($inspection->getProperties() as $p)
                                @if(preg_match("/^interior/",$p))
                                  @if( trim($inspection->$p)!='')
                                          <?php $issues++; ?>
                                                <div class="inspection-label"> {{ Inspection::getPartName($p) }}</div>
                                                <div class="content-description"> {{ $inspection->$p }}</div>
                                                @if (array_key_exists($p, $inspection->componentPhotos) && count($inspection->componentPhotos[$p]))
                                                <?php $i = 0; ?>
                                                    @foreach ($inspection->componentPhotos[$p] as $photo) 
                                                    <a href="{{ Photo::generic($photo->name) }}"  data-lightbox="{{ $p."_photo" }}"> <button class="btn-orange small {{ $i?'hidden':' ' }}"> View Pictures <i class="icon-picture icon-white"></i></button></a>
                                                        <?php $i++; ?>
                                                    @endforeach 
                                                @endif
                                                <div class="required-action-item"> 
                                                    <?php $currentAction = $inspection->actions[$p]; ?>
                                                    <form class="action-form" inspection_action_id="{{ $currentAction->inspection_action_id }}">
                                                        <p class="required-action-completed-text"> Completed on </p>
                                                        <input type='text' name='completed_on' class="form-control action-date-picker" placeholder="yyyy-mm-dd" value='{{ $currentAction->completed_on }}'/>
                                                        <br/>
                                                        {{ Form::textarea('action',$currentAction->action ,array('rows'=>4,'class'=>'form-control','placeholder'=>'Describe the required action for this item')) }}
                                                    </form>
                                                </div>
                                      @else
                                          <div class="inspection-label"> {{ Inspection::getPartName($p) }}</div>
                                          @if($inspection->$p===NULL)
                                              <div class="content-description"> N/A </div>
                                          @else
                                              <div class="content-description"> Pass. </div>
                                          @endif
                                          @if (array_key_exists($p, $inspection->componentPhotos) && count($inspection->componentPhotos[$p]))
                                          <?php $i = 0; ?>
                                              @foreach ($inspection->componentPhotos[$p] as $photo) 
                                              <a href="{{ Photo::generic($photo->name) }}"  data-lightbox="{{ $p."_photo" }}"> <button class="btn-orange small {{ $i?'hidden':' ' }}"> View Pictures <i class="icon-picture icon-white"></i></button></a>
                                                  <?php $i++; ?>
                                              @endforeach 
                                          @endif
                                      @endif
                                @endif
                          @endforeach
                      </div>
                      <div class="inspection-header-label"> Exterior </div>
                      <div class="inspection-details-content" id="exterior-content">
                          <?php $issues = 0; ?>
                          @foreach($inspection->getProperties() as $p)
                                @if(preg_match("/^visual/",$p))
                                      @if(trim($inspection->$p !=""))
                                          <?php $issues++; ?>
                                             <div class="inspection-label"> {{ Inspection::getPartName($p) }} </div>
                                             <div class="content-description"> {{ $inspection->$p }}</div>
                                             @if (array_key_exists($p, $inspection->componentPhotos) && count($inspection->componentPhotos[$p]))
                                             <?php $i = 0; ?>
                                                 @foreach ($inspection->componentPhotos[$p] as $photo) 
                                                 <a href="{{ Photo::generic($photo->name) }}"  data-lightbox="{{ $p."_photo" }}"> <button class="btn-orange small {{ $i?'hidden':' ' }}"> View Pictures <i class="icon-picture icon-white"></i></button></a>
                                                     <?php $i++; ?>
                                                 @endforeach 
                                             @endif
                                             <div class="required-action-item"> 
                                                 <?php $currentAction =  $inspection->actions[$p]; ?>
                                                 <form class="action-form" inspection_action_id="{{ $currentAction->inspection_action_id }}">
                                                     <p class="required-action-completed-text"> Completed on </p>
                                                     <input type='text' name='completed_on' class="form-control action-date-picker" placeholder="yyyy-mm-dd" value='{{ $currentAction->completed_on }}'/>
                                                     <br/>
                                                     {{ Form::textarea('action',$currentAction->action ,array('rows'=>4,'class'=>'form-control','placeholder'=>'Describe the required action for this item')) }}
                                                 </form>
                                             </div>
                                      @else  
                                              <div class="inspection-label"> {{ Inspection::getPartName($p) }}</div>
                                              @if($inspection->$p===NULL)
                                                  <div class="content-description"> N/A </div>
                                              @else
                                                  <div class="content-description"> Pass. </div>
                                              @endif
                                              @if (array_key_exists($p, $inspection->componentPhotos) && count($inspection->componentPhotos[$p]))
                                              <?php $i = 0; ?>
                                                  @foreach ($inspection->componentPhotos[$p] as $photo) 
                                                  <a href="{{ Photo::generic($photo->name) }}"  data-lightbox="{{ $p."_photo" }}"> <button class="btn-orange small {{ $i?'hidden':' ' }}"> View Pictures <i class="icon-picture icon-white"></i></button></a>
                                                      <?php $i++; ?>
                                                  @endforeach 
                                              @endif
                                      @endif
                              @endif
                                
                          @endforeach
                      </div>
                      <div class="inspection-section-header">
                          <div class="inspection-header-label"> Comment </div>
                          <div id="inspection-comment"> {{ $inspection->comment }}</div>
                      </div>  
                      <div class="inspection-section-header">
                          <div class="inspection-header-label"> Location </div>
                          <div class="inspection-details-content">
                              <div class="inspection-label">Location</div>
                              <div id="inspection-location" class="content-description"> {{ $inspection->location }} </div>
                              <div class="inspection-label">Mileage</div>
                              <div id="inspection-mileage" class="content-description"> {{ $inspection->mileage }} </div>
                          </div>
                      </div>  
                      <div class="section-header">
                        <div class="section-header-label"> Performed by </div>
                          <div class="details-content">
                              <div class="content-label"> Worker name  </div>
                              <div class="content-description"> {{ $inspection->addedBy->first_name.' '.$inspection->addedBy->last_name }} </div>
                          </div>
                         <div class="details-content">
                              <div class="content-label"> Signature  </div>
                              @if ($inspection->addedBy->signature())
                                  <div class="content-description center"> 
                                      <img src="{{ URL::to('image/worker/signature',$inspection->addedBy->auth_token) }} " class="signature-image">
                                  </div>
                              @else
                                  <div class="content-description"> 
                                      No signature available
                                  </div>
                              @endif
                          </div>
                      </div>
                      <div class="section-header">
                          <div class="section-header-label"> Review by Management</div>
                          
                          @if (!$inspection->review)
                              <form id="review-details" class="hidden">
                                  <input type="text" name="resource_type" value="{{ get_class($inspection) }}"/>
                                  <input type="text" name="resource_id" value="{{ $inspection->inspection_id }}"/>
                              </form>
                              <div class="content-label" style="padding-right:5px;">
                                  <button class="btn btn-info" id="review-button"> <b>Review this form as {{ Auth::user()->first_name.' '.Auth::user()->last_name }} </b></button>
                              </div>
                          @else
                              <div class="details-content">
                                  <div class="content-label"> Reviewed by: 
                                      <span class="content-description"> {{ $inspection->review->reviewer_name }} </span>
                                      <br/>
                                      On:
                                      <span class="content-description"> {{ WKSSDate::display($inspection->review->ts,$inspection->review->created_at) }} </span>
                                  </div>
                              </div>
                              <div class="details-content">
                                  <div class="content-label"> Signature  </div>
                                  @if ($inspection->review->signature())
                                      <div class="content-description center"> 
                                          <img src="{{ URL::to('image/review/signature',$inspection->review->form_review_id ) }}" class="admin-signature-image">
                                      </div>
                                  @else
                                      <div class="content-description"> 
                                          No signature available
                                      </div>
                                  @endif
                              </div>
                          
                          @endif
                      </div>
                      </div> 
                      <br/>
                  </div>
          </div>
      @endif
    </div>
  </div>
</div>
@stop


@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
  <script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
  <script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
  <script src="{{{ asset('assets/js/wkss/vehicle-details.js') }}}"></script>
@stop