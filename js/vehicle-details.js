$(document).ready(function(){
    
    var prevAction ="";
    
    slide();
    function slide(){
        $(".nivoSlider").nivoSlider({
            directionNav: true,             // Next & Prev navigation
            controlNav: false,               // 1,2,3... navigation
            manualAdvance: true,           // Force manual transitions
            prevText: '',               // Prev directionNav text
            nextText: '',               // Next directionNav text
            effect:'slideInRight'
        });
    }

    $('#vehicleInspectionsTable').on('click','.view-inspection',function () {
        $(this).parent().find('tr').removeClass('selected');
        $(this).addClass('selected');
        var inspectionId = $(this).prop('id').replace('inspection_','');
        var vehicleId = $("#vehicleId").val();
        window.location.href=site+'vehicle-management/view/'+vehicleId+'/'+inspectionId;
    });
    
    $("#completeExportToMail").click(function(){
        var formData = $("#mailExportForm").serialize();

        var modal = '#modalEmailSave';
        $("#addError").empty();
        $('#modalEmailSave .help-inline').remove();
        $('#modalEmailSave .control-group').removeClass('error');
        validateForm();
        
        if($("#mailExportForm").valid()){
            // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
            $("#completeExportToMail").html('Sending... <img src="../../../assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $('#completeExportToMail').prop('disabled', true);

            // It would actually be best to check the email size and notify the user first because it could be too big.
            $.post(site+'vehicle-management/mail', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   $(modal).on('hidden', function () {
                      $(modal+' #email').val('');
                   });
                }
                else{
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
    
    $(".action-date-picker").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true,
        onClose:function(dateText,formInput){
            setInspectionAction(this);
        }
    });
    
    $(".action-description").on("focus",function(){
        prevAction = $(this).val();
    });
    
    $(".action-description").on("focusout", function(){
        if ($(this).val() !== prevAction){
            setInspectionAction(this);
        }
    });
    
    function setInspectionAction(formInput){
        var formData = "inspection_action_id="+$(formInput).parent().attr('inspection_action_id')+'&'; 
        formData+=$(formInput).prop('name')+"="+$(formInput).val();
        $.post(site+'vehicle-management/add-action', formData, function(response){
            if(response.status && response.data && $(formInput).prop('name') === "action"){
               $(formInput).parent().find('.action-date-picker').val('');
            }
        });
    }
});
