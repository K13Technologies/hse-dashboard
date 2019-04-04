$(document).ready(function(){
    $("#addAdminButton").click(function(){
        var formData = $("#addAdminForm").serialize();
        var modal = '#modalAdminAdd';
        $.post('admin-management/add-admin', formData, function(response){
            clearAddErrors();
            if(response.status){
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
    
    
    $(".editAdminLink").click(function(){
        clearEditErrors();
        var modal = '#modalAdminEdit';
        var row = $(this).closest('tr');
        var admin_id = $(this).prop('id').replace('edit_','');
        var email = $.trim($(row).find('td:nth-child(1)').text());
        var first_name = $.trim($(row).find('td:nth-child(2)').text());
        var last_name = $.trim($(row).find('td:nth-child(3)').text());
        var role = $(row).find('td:nth-child(4)').text();
        var company = $(row).find('td:nth-child(5)').text();
        $("#edit_admin_id",modal).val(admin_id);
        $("#edit_email",modal).val(email);
        $("#edit_first_name",modal).val(first_name);
        $("#edit_last_name",modal).val(last_name);
        $.each($('option','#edit_company_id '),function(index, value){
            if ($.trim($(value).text()) == $.trim(company)){
                $(value).prop("selected","selected");
            }
        });
        $.each($('option','#edit_role_id '),function(index, value){
            if ($.trim($(value).text()) == $.trim(role)){
                $(value).prop("selected","selected");
            }
        });
        $(modal).modal('show');
    });
    
    
    $("#editAdminButton").click(function(){
        var formData = $("#editAdminForm").serialize();
        var modal = '#modalAdminEdit';
        $.post('admin-management/edit-admin', formData, function(response){
            clearEditErrors();
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
    });
    
    
    
    $(".deleteAdminLink").click(function(){
        var modal = '#modalAdminDelete';
        var row = $(this).closest('tr');
        var admin_id = $(this).prop('id').replace('edit_','');
        $("#delete_admin_id",modal).val(admin_id);
        $(modal).modal('show');
    });
    
    $("#deleteAdminButton").click(function(){
        var formData = $("#deleteAdminForm").serialize();
        var modal = '#modalAdminDelete';
        $.post('admin-management/delete-admin', formData, function(response){
            if(response.status){
               location.reload();
            }
        },'json');
    });
    
});


function clearAddErrors(){
    $('#modalAdminAdd .help-inline').remove();
    $('#modalAdminAdd .control-group').removeClass('error');
}

function clearEditErrors(){
    $('#modalAdminEdit .help-inline').remove();
    $('#modalAdminEdit .control-group').removeClass('error');
}

