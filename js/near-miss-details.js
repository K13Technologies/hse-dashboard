$(document).ready(function(){
    var prev ="";
    
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

                $.post(site+'near-misses/mail', formData, function(response){
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
            window.location.replace(site+"near-misses/export/"+ $('[name="near_miss_id"]').val());
    });
    
    //$(".editNearMissButton").click(function(){
    $('#editNearMissForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $(".editNearMissButton").val('Saving...');
        $('.editNearMissButton').prop('disabled', true);
  
        // if form good, do stuff. if not, don;t do NUTTIN
        var formData = $("#editNearMissForm").serialize();

        var returnMessage = '';

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'near-misses/edit-near-miss', formData, function(response){
            if(response.status){
                returnMessage = 'Save Successful.\n\nPlease refresh the page if you wish to view changes.';
            } else {
                 returnMessage = 'Save Failed:\n\n'+ response.errors;
            }

            alert(returnMessage);

            $(".editNearMissButton").val('Save Edits');
            $('.editNearMissButton').prop('disabled', false);
        },'json');

        return false;
    });

    /*
    $('#actionSlider').click(function(){

        if($('#actionSlider').prop('checked')) {
            $('#actionItemsContainer').prop('hidden', true);
            $('[name="corrective_action_implementation"]').prop('disabled',true);
            $('[name="completed_on"]').prop('disabled',true);
            $('[name="action"]').prop('disabled',true);
        } else {
            $('#actionItemsContainer').prop('hidden', false); 
            $('[name="corrective_action_implementation"]').prop('disabled',false);
            $('[name="completed_on"]').prop('disabled',false);
            $('[name="action"]').prop('disabled',false); 
        }
    });*/

    $('#actionSlider').change(function(){

        if($('#actionSlider').prop('checked')) {
            $('#actionItemsContainer').prop('hidden', true);
            $('[name="corrective_action_implementation"]').prop('disabled',true);
            //$('[name="completed_on"]').prop('disabled',true);
            //$('[name="action"]').prop('disabled',true);
        } else {
            $('#actionItemsContainer').prop('hidden', false); 
            $('[name="corrective_action_implementation"]').prop('disabled',false);
            //$('[name="completed_on"]').prop('disabled',false);
            //$('[name="action"]').prop('disabled',false); 
        }
    });

    //$('#actionSlider').on('switchChange.bootstrapSwitch', function (event, state) {}); 

    // Conditional view formatting
    /*$('#actionSlider').on('switchChange.bootstrapSwitch', function (event) {
        //alert($('#actionSlider').bootstrapSwitch('state'));
        // Corrective action was applied -- hide elaboration fields
       if($('#actionSlider').bootstrapSwitch('state')) {
            $('#actionItemsContainer').prop('hidden', true);

            $('[name="corrective_action_implementation"]').prop('disabled',true);
            $('[name="completed_on"]').prop('disabled',true);
            $('[name="action"]').prop('disabled',true);
        } // NOT applied -- show elaboration fields
        else {
            $('#actionItemsContainer').prop('hidden', false); 

            $('[name="corrective_action_implementation"]').prop('disabled',false);
            $('[name="completed_on"]').prop('disabled',false);
            $('[name="action"]').prop('disabled',false); 
        }
    });*/

    $(".action-date-picker").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true,

        //This ends up being an asynchronous call which we no longer need
        /*onClose:function(dateText,formInput){
            setAction(this);
        }*/   
    });

    $('#exampleCheckBox').change(function() {
        if($('#exampleCheckBox').prop('checked')) {
         alert('YES!');
         }
         else {
         alert('NOPE!');
        }
    });

     /*
        This chunk was previously used to asynchronously save changes to the action fields -- no longer needed now that we use a save button
    
    $(".action-description").on("focus",function(){
        prev = $(this).val();
    });
    
    $(".action-description").on("focusout", function(){
        if ($(this).val() !== prev){
           setAction(this);
        }
    });
    
    function setAction(formInput){
        var formData = "near_miss_id="+$(formInput).parent().prop('id')+'&'; 
        formData+=$(formInput).prop('name')+"="+$(formInput).val();
        $.post(site+'near-misses/add-action', formData, function(response){
            if(response.status && response.data && $(formInput).prop('name') === "action"){
               $(formInput).parent().find('.action-date-picker').val('');
            }
        });
    }*/
});

