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
    
    
    $(".showSignins").click(function(e){
        e.preventDefault();
        var groupId = $("#selectGroupAdd").val();
        var signDate = $("#sign_date").val();
        if ($.trim(signDate)==""){
            $("#sign_date").parent().parent().addClass('error');
            $("#sign_date").parent().append('<span class="help-inline">'+"Please select a date"+'</span>');
        }else{
            if(groupId >0){
                location.href= site+'daily-signin/'+groupId+'/'+signDate;
            }
        }
    });
    
    
    $("#completeExportToMail").click(function(){
        var formData = $("#mailExportForm").serialize();
        var modal = '#modalEmailSave';
        var signDate = $("#email_signDate").val();
        var groupId = $("#email_groupId").val();
        $("#addError").empty();
        $('#modalEmailSave .help-inline').remove();
        $('#modalEmailSave .control-group').removeClass('error');
        validateForm();
        if($("#mailExportForm").valid()){
            $.post(site+'daily-signin/mail/'+groupId+"/"+signDate, formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   $(modal).on('hidden', function () {
                      $(modal+' #email').val('');
                   });
                }else{
                    $("#mailError").text(response.errors);
               }
            },'json');
        }
    });
});

