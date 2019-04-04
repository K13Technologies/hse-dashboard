$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
        
    // Launches the modal after the user clicks the delete button in the table
    $(".deleteObservationLink").click(function(){
        var modal = '#modalObservationDelete';
        var row = $(this).closest('tr');
        var observation_id = $(this).prop('id').replace('delete_','');
        $("#delete_observation_id", modal).val(observation_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteObservationButton").click(function(){
        $("#deleteObservationButton").html('Deleting...');
        $('#deleteObservationButton').prop('disabled', true);

        var formData = $("#deleteObservationForm").serialize();
        var modal = '#modalObservationDelete';

        $.post('field-observations/delete-observation', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });
});

