$(document).ready(function(){

    // Launches the modal after the user clicks the delete button in the table
    $(".deleteJourneyLink").click(function(){
        var modal = '#modalJourneyDelete';
        var row = $(this).closest('tr');
        var journey_id = $(this).prop('id').replace('delete_','');
        $("#delete_journey_id",modal).val(journey_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteJourneyButton").click(function(){
        $("#deleteJourneyButton").html('Deleting...');
        $('#deleteJourneyButton').prop('disabled', true);

        var formData = $("#deleteJourneyForm").serialize();
        var modal = '#modalJourneyDelete';

        $.post('journey-management/delete-journey', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });


    //Old code from previous view
    var journeyId;
    $(".view-journey").click(function(){
        $(this).parent().find('tr').removeClass('selected');
        $(this).addClass('selected');
        journeyId = $(this).prop('id').replace('journey_','');
        location.href = site+'journeys/'+journeyId;
    });
    
    
});

