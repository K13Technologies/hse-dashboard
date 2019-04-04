$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
        
    // Launches the modal after the user clicks the delete button in the table
    $(".deleteTailgateLink").click(function(){
        var modal = '#modalTailgateDelete';
        var row = $(this).closest('tr');
        var tailgate_id = $(this).prop('id').replace('delete_','');
        $("#delete_tailgate_id",modal).val(tailgate_id);
        $(modal).modal('show');
    });
    
    // For when the user actually presses the YES button
    $("#deleteTailgateButton").click(function(){
        $("#deleteTailgateButton").html('Deleting...');
        $('#deleteTailgateButton').prop('disabled', true);

        var formData = $("#deleteTailgateForm").serialize();
        var modal = '#modalTailgateDelete';

        $.post('tailgates/delete-tailgate', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   location.reload();
                }
            },'json');
    });
});

