$(document).ready(function(){
    
    $('#selectCompanyAdd').on('change', function() {
        company_id = this.value;
        $.post(site+'company-management/company-projects', {company:company_id}, function(response){
            var select = $("#selectGroupAdd");
            if(response.status){
                $(select).empty();
                $(select).append('<option value="">Please select a project</option>');
                $.each(response.data, function(index, value){
                    $(select).append('<option value="'+value.group_id+'">'+value.group_name+'</option>');
                }); 
            }
        },'json');
    });
    
    $(".range-date-picker").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true,
        onClose:function(dateText,formInput){
            inputId = formInput.id;
            $('#'+inputId).siblings('.help-inline').remove();
            $('#'+inputId).parent().parent().removeClass('error');
        }
    });
    
    $("#selectTimeframe").change(function(){
        var timeframe = $("#selectTimeframe").val();
        if ($.trim(timeframe) !== ""){
            $(this).parent().parent().removeClass('error');
            $(this).siblings('.help-inline').remove();
        }
    });
    
    $(".showSignins").click(function(e){
        e.preventDefault();
        var groupId = $("#selectGroupAdd").val();
        var refDate = $("#ref_date").val();
        var timeframe = $("#selectTimeframe").val();
        if ($.trim(refDate) === ""){
            $("#ref_date").parent().parent().addClass('error');
            $("#ref_date").parent().append('<span class="help-inline">'+"Please select a date"+'</span>');
        }else if ($.trim(timeframe) === ""){
            $("#selectTimeframe").parent().parent().addClass('error');
            $("#selectTimeframe").parent().append('<span class="help-inline">'+"Please select a timeframe"+'</span>');
        }else if(groupId >0 ){
                location.href= site+'company-management/stats/'+groupId+'/'+refDate+'/'+timeframe;
        }
    });
    
    $("#completeExportToMail").click(function(){
        var formData = $("#mailExportForm").serialize();
        var modal = '#modalEmailSave';
        var refDate = $("#email_refDate").val();
        var groupId = $("#email_groupId").val();
        var timeframe = $("#selectTimeframe").val();
        $("#addError").empty();
        $('#modalEmailSave .help-inline').remove();
        $('#modalEmailSave .control-group').removeClass('error');
        validateForm();
        if($("#mailExportForm").valid()){
            // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
            $("#completeExportToMail").html('Sending... <img src="' + site + 'assets/img/loading.gif"' + ' alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $('#completeExportToMail').prop('disabled', true);

            $.post(site+'company-management/stats/mail/'+groupId+"/"+refDate+"/"+timeframe, formData, function(response){
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
});

