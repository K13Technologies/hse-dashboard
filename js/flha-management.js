$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
        
    // Launches the modal after the user clicks the delete button in the table
    $(".deleteFLHALink").click(function(){
        var modal = '#modalFLHADelete';
        var row = $(this).closest('tr');
        var flha_id = $(this).prop('id').replace('delete_','');
        $("#delete_flha_id",modal).val(flha_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteFLHAButton").click(function(){
        $("#deleteFLHAButton").html('Deleting...');
        $('#deleteFLHAButton').prop('disabled', true);

        var formData = $("#deleteFLHAForm").serialize();
        var modal = '#modalFLHADelete';

        $.post('flha/delete-flha', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });
});

