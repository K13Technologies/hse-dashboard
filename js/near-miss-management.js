$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
        
    // Launches the modal after the user clicks the delete button in the table
    $(".deleteNearMissLink").click(function(){
        var modal = '#modalNearMissDelete';
        var row = $(this).closest('tr');
        var near_miss_id = $(this).prop('id').replace('delete_','');
        $("#delete_near_miss_id",modal).val(near_miss_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteNearMissButton").click(function(){
        $("#deleteNearMissButton").html('Deleting...');
        $('#deleteNearMissButton').prop('disabled', true);

        var formData = $("#deleteNearMissForm").serialize();
        var modal = '#modalNearMissDelete';

        $.post('near-misses/delete-near-miss', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });
});

