@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')
<div class='container'>
    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif

    <div class='row well whiteBackground'>
        <p class="section-header-label">Safety Manual Revisions</p>
        <br/>
         <table class="table-list table-hover dataTable" id="revisionTable">
            <thead>
              <tr>
                <th> Date </th>
                <th> Version </th>
                <th> Revision Description </th>
                <th> Administrator Name </th>
                <th> IP Address </th>
              </tr>
            </thead>
            <tbody>
              @foreach ($revisions as $r)
                  <tr class="clickable-row">
                     <td>{{ WKSSDate::display(strtotime($r->created_at), $r->created_at) }}</td>
                     <td>{{ $r->major_version_number }}.{{ $r->minor_version_number }}</td>
                     <td>{{ $r->revision_description ? $r->revision_description : "No description provided" }}</td>
                     <td>{{ $r->admin->first_name }} {{ $r->admin->last_name }} </td>
                     <td>{{ $r->ip_address }}</td>
                  </tr>
              @endforeach
            </tbody>
        </table>
    </div> 
</div>      
@stop

@section('scripts')
    @parent  
@stop