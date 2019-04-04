$(document).ready(function(){
    var prev = "";
    
    $(".nivoSlider").nivoSlider({
        directionNav: true,             // Next & Prev navigation
        controlNav: false,               // 1,2,3... navigation
        manualAdvance: true,           // Force manual transitions
        prevText: '',               // Prev directionNav text
        nextText: '',               // Next directionNav text
        effect:'slideInRight'
    });
        
    $("#completeExportToMail").click(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();

        var formData = $("#mailExportForm").serialize();
        var modal = '#modalEmailSave';
        $("#addError").empty();
        $('#modalEmailSave .help-inline').remove();
        $('#modalEmailSave .control-group').removeClass('error');
        validateForm();
        if($("#mailExportForm").valid()){
            // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
            $("#completeExportToMail").html('Sending... <img src="../../assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $('#completeExportToMail').prop('disabled', true);

            $.post(site+'hazard-cards/mail', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   $(modal).on('hidden', function () {
                      $(modal+' #email').val('');
                   });
                }else{
                    $("#mailError").text(response.errors);
               }
               //Button reset whether or not email actually sends
                $("#completeExportToMail").html('Send');
                $('#completeExportToMail').prop('disabled', false);
            },'json');
        }
        // This return allows the button to act as expected after a second click
        // For example, the case wehere if the form is not valid and they click, validate then click again
        return false;
    });

    $('#selectHelpButton').click(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        alert('Please choose one or more items. To choose more than one, hold the CTRL key (Windows) or APPLE key (Mac) before clicking each new item.');
    });

    $("#exportPDFButton").click(function(event){
            // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
            event.preventDefault();
            // Send download request
            window.location.replace(site+"hazard-cards/export/"+ $('[name="hazard_id"]').val());
    });

    $('#actionSlider').change(function(){
        if($('#actionSlider').prop('checked')) {
            $('#actionItemsContainer').prop('hidden', true);
            $('[name="corrective_action_implementation"]').prop('disabled',true);
        } else {
            $('#actionItemsContainer').prop('hidden', false); 
            $('[name="corrective_action_implementation"]').prop('disabled',false);
        }
    });

    $('#editHazardForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $(".editHazardButton").val('Saving...');
        $('.editHazardButton').prop('disabled', true);
  
        var formData = $("#editHazardForm").serialize();
        var returnMessage = '';

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'hazard-cards/edit-hazard-card', formData, function(response){
            if(response.status){
                returnMessage = 'Save Successful.\n\nPlease refresh the page if you wish to view changes.';
            } else {
                 returnMessage = 'Save Failed:\n\n'+ response.errors;
            }

            alert(returnMessage);

            $(".editHazardButton").val('Save Edits');
            $('.editHazardButton').prop('disabled', false);
        },'json');

        return false;
    });
        
    $(".action-date-picker").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true
    });
});

