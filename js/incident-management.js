$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
        
    // Launches the modal after the user clicks the delete button in the table
    $(".deleteIncidentLink").click(function(){
        var modal = '#modalIncidentDelete';
        var row = $(this).closest('tr');
        var incident_id = $(this).prop('id').replace('delete_','');
        $("#delete_incident_id",modal).val(incident_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteIncidentButton").click(function(){
        $("#deleteIncidentButton").html('Deleting...');
        $('#deleteIncidentButton').prop('disabled', true);

        var formData = $("#deleteIncidentForm").serialize();
        var modal = '#modalIncidentDelete';

        $.post('incident-cards/delete-incident', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });
});

