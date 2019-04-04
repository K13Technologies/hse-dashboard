$(document).ready(function(){
    
    var editCompanyName = "";
    
    
    $("#addEnterpriseCompany").click(function(){
        var modal = '#modalEnterpriseCompanyAdd';
        clearAddErrors();
        $(modal).modal('show');
    });

    function clearAddErrors(){
        $('#modalEnterpriseCompanyAdd .help-inline').remove();
        $('#modalEnterpriseCompanyAdd .control-group').removeClass('error');
    }

    function clearEditErrors(){
        $('#modalCompanyEdit .help-inline').remove();
        $('#modalCompanyEdit .control-group').removeClass('error');
    }
    
    $("#addEnterpriseCompanyButton").click(function(){
        var formData = $("#addEnterpriseCompanyForm").serialize();
        var modal = '#modalEnterpriseCompanyAdd';
        clearAddErrors();
        $.post('company-management/add-enterprise-company', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               
               //firefox fix
               $("#company_name").val('');
               //end of firefox fix
               
               location.reload();
            }else{
                $.each(response.errors, function(index, value){
                    var input = $("#"+index, modal);
                    $(input).parent().parent().addClass('error');
                    $(input).parent().append('<span class="help-inline">'+value[0]+'</span>');
                }); 
           }
        },'json');
    });
    
    $(".editCompanyLink").click(function(){
        var modal = '#modalCompanyEdit';
        clearEditErrors();
        var row = $(this).closest('tr');
        var company_id = $(this).prop('id').replace('edit_','');
        var company_name = $.trim($(row).find('td:nth-child(1)').text());
        editCompanyName = company_name;
        $("#edit_company_id",modal).val(company_id);
        $("#edit_company_name",modal).val(company_name);
        $(modal).modal('show');
    });
    
    
    $("#editCompanyButton").click(function(){
        clearEditErrors();
        var company_name= $("#edit_company_name").val();
        var formData = $("#editCompanyForm").serialize();
        var modal = '#modalCompanyEdit';
        if (company_name == editCompanyName){
             $(modal).modal('hide');
        }else{
            $.post('company-management/edit-company', formData, function(response){
                if(response.status){
                   location.reload();
                }else{
                    $.each(response.errors, function(index, value){
                        var input = $("#edit_"+index, modal);
                        $(input).parent().parent().addClass('error');
                        $(input).parent().append('<span class="help-inline">'+value[0]+'</span>');
                    }); 
               }
            },'json');
        }
    });
    
   $("#edit_subscription_ends_at").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true,
        onClose:function(dateText,formInput){
            return;
        }
    });
    
    $(".editCompanyEndTime").click(function(){
        var modal = '#modalCompanyTimeEdit';
        clearEditErrors();
        var row = $(this).closest('tr');
        var company_id = $(this).prop('id').replace('edit_time_','');
        var date = $.trim($(row).find('td:nth-child(4)').text());
        $("#time_company_id",modal).val(company_id);
        $("#edit_subscription_ends_at",modal).val(date);
        $(modal).modal('show');
    });
    
    $("#editCompanyTimeButton").click(function(){
        var formData = $("#editCompanyTimeForm").serialize();
        var modal = '#modalCompanyTimeEdit';
        $.post('company-management/edit-company', formData, function(response){
            if(response.status){
               location.reload();
            }else{
                $(modal).modal('hide');
           }
        },'json');
    });

    $(".deleteCompanyLink").click(function(){
        var modal = '#modalCompanyDelete';
        var row = $(this).closest('tr');
        var company_id = $(this).prop('id').replace('edit_','');
        $("#delete_company_id",modal).val(company_id);
        $(modal).modal('show');
    });
    
    /*
    $("#deleteCompanyButton").click(function(){
        var formData = $("#deleteCompanyForm").serialize();
        var modal = '#modalCompanyDelete';
        $("#deleteError").empty();
        $.post('company-management/delete-company', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               $(modal).on('hidden', function () {
                     location.reload();
               });
            }else{
                $("#deleteError").text(response.errors);
            }
        },'json');
    });*/
    
});

