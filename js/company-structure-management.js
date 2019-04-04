$(document).ready(function(){
    $('.btn-orange.small').click(function(){
        clearErrors();
    });
    
    var selectedDivision;
    var selectedBU;
    
    $('[rel=tooltip]').tooltip();
    
    $("#selectDivision").on('click','.view-division',function(){
        $(this).parent().find('tr').removeClass('selected');
        $(this).addClass('selected');
        var divisionId = $(this).prop('id').replace('division_','');
        selectedDivision = divisionId;
        $('.selectBusinessUnitDivision option[value="'+divisionId+'"]').prop("selected","selected");
        var select = $("#selectBusinessUnit,#selectGroup,.selectGroupBusinessUnit");
        $(select).empty();
        $.post(site+'company-management/division-business-units', {division:divisionId}, function(response){
            if(response.status){
                $(select).empty();
                $.each(response.data, function(index, value){
                    appendAddedBusinessUnit(value,false);
                    appendBUFORGROUP(value,false);
                }); 
            }
        },'json');
    });
    
     $("#selectDivision").on('click',".editDivisionLink",function(){
        var modal = '#modalDivisionEdit';
        var row = $(this).closest('tr');
        var divisionId = $(this).prop('id').replace('edit_division_','');
        var divisionName = $.trim($(row).find('td:nth-child(1)').text());
        $("#edit_division_id",modal).val(divisionId);
        $("#edit_division_name",modal).val(divisionName);
        $(modal).modal('show');
    });
    
    $("#selectDivision").on('click',".deleteDivisionLink",function(){
        var modal = '#modalDivisionDelete';
        var divisionId = $(this).prop('id').replace('delete_division_','');
        $("#delete_division_id",modal).val(divisionId);
        $(modal).modal('show');
    });
    
    
    
    $("#selectBusinessUnit").on('click','.view-business-unit',function(){
        $(this).parent().find('tr').removeClass('selected');
        $(this).addClass('selected');
        
        var businessUnitId = $(this).prop('id').replace('business_unit_','');
        selectedBU = businessUnitId;
        $('.selectGroupBusinessUnit option[value="'+businessUnitId+'"]').prop("selected","selected");
        var select = $("#selectGroup");
        $(select).empty();
        
        $.post(site+'company-management/business-unit-groups', {business_unit:businessUnitId}, function(response){
            if(response.status){
                $(select).empty();
                $.each(response.data, function(index, value){
                    appendAddedGroup(value,false);
                }); 
            }
        },'json');
        
    });
    
    $("#selectBusinessUnit").on('click',".editBusinessUnitLink",function(){
        var modal = '#modalBusinessUnitEdit';
        var row = $(this).closest('tr');
        var businessUnitId = $(this).prop('id').replace('edit_business_unit_','');
        var businessUnitName = $.trim($(row).find('td:nth-child(1)').text());
        $("#edit_business_unit_id",modal).val(businessUnitId);
        $("#edit_business_unit_name",modal).val(businessUnitName);
        $(".selectBusinessUnitDivision option[value='"+selectedDivision+"']").prop('selected','selected');
        $(modal).modal('show');
    });
    
    $("#selectBusinessUnit").on('click',".deleteBusinessUnitLink",function(){
        var modal = '#modalBusinessUnitDelete';
        var businessUnitId = $(this).prop('id').replace('delete_business_unit_','');
        $("#delete_business_unit_id",modal).val(businessUnitId);
        $(modal).modal('show');
    });
    
  
    
    $("#selectGroup").on('click',".view-group",function(){
        $(this).parent().find('tr').removeClass('selected');
        $(this).addClass('selected');
    });
    
    $("#selectGroup").on('click',".editGroupLink",function(){
        var modal = '#modalGroupEdit';
        clearErrors();
        var row = $(this).closest('tr');
        var groupId = $(this).prop('id').replace('edit_group_','');
        var groupName = $.trim($(row).find('td:nth-child(1)').text());
        $("#edit_group_id",modal).val(groupId);
        $("#edit_group_name",modal).val(groupName);
        $(modal).modal('show');
    });
    

    $("#selectGroup").on('click',".deleteGroupLink",function(){
        var modal = '#modalGroupDelete';
        clearErrors();
        var groupId = $(this).prop('id').replace('delete_group_','');
        $("#delete_group_id",modal).val(groupId);
        $(modal).modal('show');
    });
    
    
    $("#addDivisionButton").click(function(){
        var formData = $("#addDivisionForm").serialize();
        var modal = '#modalDivisionAdd';
        clearErrors();
        $.post(site+'company-management/add-division', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                appendAddedDivision(response);
                updateDropdownForBusinessUnits(response);
            }else{
               $("#addDivisionError").text(response.errors);
           }
        },'json');
    });
    
    $("#editDivisionButton").click(function(){
        var formData = $("#editDivisionForm").serialize();
        var modal = '#modalDivisionEdit';
        clearErrors();
        $.post(site+'company-management/edit-division', formData, function(response){
            if(response.status){
                $(modal).modal('hide');
                updateEditedDivision(response);
                updateDropdownForBusinessUnits();
            }else{
               $("#editDivisionError").text(response.errors);
           }
        },'json');
    });
    
    $("#deleteDivisionButton").click(function(){
        var formData = $("#deleteDivisionForm").serialize();
        var modal = '#modalDivisionDelete';
        clearErrors();
        $.post(site+'company-management/delete-division', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                var row = "#division_"+response.data.division_id;
                $(row).remove();
                updateDropdownForBusinessUnits();
            }else{
                $("#deleteDivisionError").text(response.errors);
            }
        },'json');
    });
    
    
    $("#addBusinessUnitButton").click(function(){
        var formData = $("#addBusinessUnitForm").serialize();
        var modal = '#modalBusinessUnitAdd';
        clearErrors();
        $.post(site+'company-management/add-business-unit', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                if (response.data.division_id == selectedDivision){
                    appendAddedBusinessUnit(response.data);
                }
                updateDropdownForProjects(selectedDivision);
            }else{
                $("#addBUError").text(response.errors);
           }
        },'json');
    });
    
    
    
    $("#editBusinessUnitButton").click(function(){
        var formData = $("#editBusinessUnitForm").serialize();
        var modal = '#modalBusinessUnitEdit';
        clearErrors();
        $.post(site+'company-management/edit-business-unit', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                if (response.data.division_id == selectedDivision){
                    updateEditedBusinessUnit(response);
                } else{
                    var row = "#business_unit_"+response.data.business_unit_id;
                    $(row).remove();
                }
//               updateEditedBusinessUnit(response);
               updateDropdownForProjects(selectedDivision);
            }else{
                 $("#editBUError").text(response.errors);
           }
        },'json');
    });
    
    $("#deleteBusinessUnitButton").click(function(){
        var formData = $("#deleteBusinessUnitForm").serialize();
        var modal = '#modalBusinessUnitDelete';
        clearErrors();
        $.post(site+'company-management/delete-business-unit', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               var row = "#business_unit_"+response.data.business_unit_id;
               $(row).remove();
               updateDropdownForProjects(selectedDivision);
            }else{
                 $("#deleteBUError").text(response.errors);
            }
        },'json');
    });
    
    
    
    
     $("#addGroupButton").click(function(){
        var formData = $("#addGroupForm").serialize();
        var modal = '#modalGroupAdd';
        clearErrors();
        $.post(site+'company-management/add-group', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                if (response.data.business_unit_id == selectedBU){
                    appendAddedGroup(response.data,true);
                }
            }else{
                $("#addGroupError").text(response.errors);
           }
        },'json');
    });
    
    $("#editGroupButton").click(function(){
        var formData = $("#editGroupForm").serialize();
        var modal = '#modalGroupEdit';
        clearErrors();
        $.post(site+'company-management/edit-group', formData, function(response){
            if(response.status){
                $(modal).modal('hide');
                if (response.data.business_unit_id == selectedBU){
                    updateEditedGroup(response);
                } else{
                    var row = "#group_"+response.data.group_id;
                    $(row).remove();
                }
            }else{
                $("#editGroupError").text(response.errors);
           }
        },'json');
    });
    
    $("#deleteGroupButton").click(function(){
        var formData = $("#deleteGroupForm").serialize();
        var modal = '#modalGroupDelete';
        clearErrors();
        $.post(site+'company-management/delete-group', formData, function(response){
            if(response.status){
                $(modal).modal('hide');
                var row = "#group_"+response.data.group_id;
                $(row).remove();
            }else{
                $("#deleteGroupError").text(response.errors);
            }
        },'json');
    });
    
    
    function appendAddedDivision(response){
        var id = "division_"+response.data.division_id;
        var divisionName = response.data.division_name;
        var row = "<tr class='view-division clickable-row' id='"+id+"'><td>\n\
                "+divisionName+"</td>\n\
                     <td class='action-column'>\n\
                        <a href='#' id='edit_"+id+"' class='editDivisionLink'><i class='glyphicon glyphicon-pencil'></i></a>\n\
                        <a href='#' id='delete_"+id+"' class='deleteDivisionLink'><i class='glyphicon glyphicon-trash'></i></a>\
                    </td>\
                </tr>";
        $("#selectDivision").append(row);
        clearErrors();
        $("#"+id).trigger('click');
    }
    
    function appendAddedBusinessUnit(bu, markAsSelected){
        var id = "business_unit_"+bu.business_unit_id;
        var businessUnitName = bu.business_unit_name;
        var row = "<tr class='clickable-row view-business-unit' id='"+id+"'><td>\n\
                "+businessUnitName+"</td>\n\
                     <td class='action-column'>\n\
                        <a href='#' id='edit_"+id+"' class='editBusinessUnitLink'><i class='glyphicon glyphicon-pencil'></i></a>\n\
                        <a href='#' id='delete_"+id+"' class='deleteBusinessUnitLink'><i class='glyphicon glyphicon-trash'></i></a>\
                    </td>\
                </tr>";
        $("#selectBusinessUnit").append(row);
        if(markAsSelected){
            $("#"+id).trigger('click');
        }
        clearErrors();
    }
    
    function appendAddedGroup(group, markAsSelected){
        var id = "group_"+group.group_id;
        var groupName = group.group_name;
        var row = "<tr class='view-group clickable-row' id='"+id+"'><td>\n\
                "+groupName+"</td>\n\
                     <td class='action-column'>\n\
                        <a href='"+site+"daily-signin/"+group.group_id+"' id='edit_"+id+"'><i class='glyphicon glyphicon-file' title='Daily signin report'></i></a>\n\
                        <a href='#' id='edit_"+id+"' class='editGroupLink'><i class='glyphicon glyphicon-pencil'></i></a>\n\
                        <a href='#' id='delete_"+id+"' class='deleteGroupLink'><i class='glyphicon glyphicon-trash'></i></a>\
                    </td>\
                </tr>";
        $("#selectGroup").append(row);
        if(markAsSelected){
            $("#"+id).trigger('click');
        }
        clearErrors();
    }
    
    
    function updateDropdownForProjects(divisionId){
        $.post(site+'company-management/division-business-units', {division:divisionId}, function(response){
            if(response.status){
                $('.selectGroupBusinessUnit').empty();
                $.each(response.data, function(index, value){
                    if (selectedBU == value.business_unit_id){
                        appendBUFORGROUP(value,true);
                    }else{
                         appendBUFORGROUP(value,false);
                    }
                });
            }
        },'json');
    }


    function updateDropdownForBusinessUnits(){
            var company_id = $("#thisCompanyId").val();
            var select = $(".selectBusinessUnitDivision");
            $.post(site+'company-management/company-divisions', {company:company_id}, function(response){
                if(response.status){
                    $(select).empty();
                    $.each(response.data, function(index, value){
                        if (value.division_id == selectedDivision){
                            $(select).append('<option value="'+value.division_id+'" selected="selected">'+value.division_name+'</option>');
                        }else{
                            $(select).append('<option value="'+value.division_id+'">'+value.division_name+'</option>');
                        }
                    });
//                $( "#selectDivisionAdd" ).trigger( "change" );
                }
            },'json');
    }



    function appendBUFORGROUP(value, selected){
        var id = value.business_unit_id;
        var businessUnitName = value.business_unit_name;

        if (selected){
            var row = "<option value='"+ id +"' selected='selected'>"+businessUnitName+"</option>";
        }else{
            var row = "<option value='"+ id +"'>"+businessUnitName+"</option>";
        }

        $(".selectGroupBusinessUnit").append(row);
    }
    
    function updateEditedDivision(response){
        var id = response.data.division_id;
        var divisionName = response.data.division_name;
        $("#division_"+id).find('td:nth-child(1)').text(divisionName);
        clearErrors();
//        $("#"+id).trigger('click');
    }
    
    function updateEditedBusinessUnit(response){
        var id = response.data.business_unit_id;
        var businessUnitName = response.data.business_unit_name;
        $("#business_unit_"+id).find('td:nth-child(1)').text(businessUnitName);
        clearErrors();
//        $("#"+id).trigger('click');
    }
    
    function updateEditedGroup(response){
        var id = response.data.group_id;
        var groupName = response.data.group_name;
        $("#group_"+id).find('td:nth-child(1)').text(groupName);
        clearErrors();
//        $("#"+id).trigger('click');
    }
    
    function clearErrors(){
        $(".control-group").removeClass('error');
        $('.help-inline').remove();
        $('.modal #division_name').val('');
        $('.modal #business_unit_name').val('');
        $('.modal #group_name').val('');
        $("#addGroupError").empty();
        $("#editGroupError").empty();
        $("#deleteGroupError").empty();
        $("#addBUError").empty();
        $("#editBUError").empty();
        $("#deleteBUError").empty();
        $("#addDivisionError").empty();
        $("#editDivisionError").empty();
        $("#deleteDivisionError").empty();
    }
    

    $("#addPhoneButton").click(function(){
        var formData = $("#addPhoneForm").serialize();
        var modal = '#modalPhoneAdd';
        clearErrors();
        $.post(site+'company-management/add-helpline', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                appendAddedPhone(response);
            }else{
                if (typeof response.errors !== "object"){
                    $("#addPhoneError").text(response.errors);
                }else{
                    $.each(response.errors, function(index, value){
                        var input = $("#"+index, modal);
                        $(input).parent().parent().addClass('error');
                        $(input).parent().append('<span class="help-inline">'+value[0]+'</span>');
                    });
                }
           }
        },'json');
    });
    
    
    function appendAddedPhone(response){
        var id = response.data.helpline_id;
        var contact = response.data.title;
        var number = response.data.value;
        var row = "<tr id='helpline_"+id+"'><td>\n\
                "+contact+"</td><td>\n\
                "+number+"</td>\n\
                     <td class='action-column'>\n\
                        <a href='#' id='delete_phone"+id+"' class='deletePhoneLink'><i class='glyphicon glyphicon-trash'></i></a>\
                    </td>\
                </tr>";
        $("#phoneList").append(row);
        clearErrors();
        $("#"+id).trigger('click');
    }
    
    
    $("#addRadioButton").click(function(){
        var formData = $("#addRadioForm").serialize();
        var modal = '#modalRadioAdd';
        clearErrors();
        $.post(site+'company-management/add-helpline', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
                appendAddedRadio(response);
            }else{
                if (typeof response.errors !== "object"){
                    $("#addRadioError").text(response.errors);
                }else{
                    $.each(response.errors, function(index, value){
                        var input = $("#"+index, modal);
                        $(input).parent().parent().addClass('error');
                        $(input).parent().append('<span class="help-inline">'+value[0]+'</span>');
                    });
                }
           }
        },'json');
    });
    
    
    function appendAddedRadio(response){
        var id = response.data.helpline_id;
        var contact = response.data.title;
        var number = response.data.value;
        var row = "<tr id='helpline_"+id+"'><td>\n\
                "+contact+"</td><td>\n\
                "+number+"</td>\n\
                     <td class='action-column'>\n\
                        <a href='#' id='delete_radio_"+id+"' class='deleteRadioLink'><i class='glyphicon glyphicon-trash'></i></a>\
                    </td>\
                </tr>";
        $("#radioList").append(row);
        clearErrors();
        $("#"+id).trigger('click');
    }
    
    
    $("#phoneList").on('click',".deletePhoneLink",function(){
        var modal = '#modalPhoneDelete';
        clearErrors();
        var phoneId = $(this).prop('id').replace('delete_phone_','');
        $("#delete_phone_id",modal).val(phoneId);
        $(modal).modal('show');
    });
    
    $("#deletePhoneButton").click(function(){
        var formData = $("#deletePhoneForm").serialize();
        var modal = '#modalPhoneDelete';
        clearErrors();
        $.post(site+'company-management/delete-helpline', formData, function(response){
            if(response.status){
                $(modal).modal('hide');
                var row = "#helpline_"+response.data.helpline_id;
                $(row).remove();
            }else{
                $("#deletePhoneError").text(response.errors);
            }
        },'json');
    });
    
    
    
    $("#radioList").on('click',".deleteRadioLink",function(){
        var modal = '#modalRadioDelete';
        clearErrors();
        var radioId = $(this).prop('id').replace('delete_radio_','');
        $("#delete_radio_id",modal).val(radioId);
        $(modal).modal('show');
    });
    
    $("#deleteRadioButton").click(function(){
        var formData = $("#deleteRadioForm").serialize();
        var modal = '#modalRadioDelete';
        clearErrors();
        $.post(site+'company-management/delete-helpline', formData, function(response){
            if(response.status){
                $(modal).modal('hide');
                var row = "#helpline_"+response.data.helpline_id;
                $(row).remove();
            }else{
                $("#deleteRadioError").text(response.errors);
            }
        },'json');
    });
});