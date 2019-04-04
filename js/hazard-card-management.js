$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
        
    // Launches the modal after the user clicks the delete button in the table
    $(".deleteHazardLink").click(function(){
        var modal = '#modalHazardDelete';
        var row = $(this).closest('tr');
        var hazard_id = $(this).prop('id').replace('delete_','');
        $("#delete_hazard_id",modal).val(hazard_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteHazardButton").click(function(){
        $("#deleteHazardButton").html('Deleting...');
        $('#deleteHazardButton').prop('disabled', true);

        var formData = $("#deleteHazardForm").serialize();
        var modal = '#modalHazardDelete';

        $.post('hazard-cards/delete-hazard-card', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });
});

