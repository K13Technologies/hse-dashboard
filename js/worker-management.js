$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
    
    $("#addWorkerLink").click(function(){
        var modal = '#modalWorkerAdd';
        $("#addError").empty();
        $(modal).modal('show');
    });
    
    $("#addWorkerButton").click(function(){
        var formData = $("#addWorkerForm").serialize();
        var modal = '#modalWorkerAdd';
        $("#addError").empty();
        $('#modalWorkerAdd .help-inline').remove();
        $('#modalWorkerAdd .control-group').removeClass('error');

        // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
        $("#addWorkerButton").html('Creating Worker... <img src="' + site + 'assets/img/loading.gif"' + ' alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
        $('#addWorkerButton').prop('disabled', true);

        $.post(site+'worker-management/add-worker', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               location.reload(true); // Reload page from server; not from cache
            }else{
                if (typeof response.errors !== "object"){
                    $("#addError").text(response.errors);
                }else{
                    $.each(response.errors, function(index, value){
                        var input = $("#"+index, modal);
                        $(input).parent().parent().addClass('error');
                        $(input).parent().append('<span class="help-inline">'+value[0]+'</span>');
                    });
                }
           }

           //Button reset whether or not email actually sends
            $("#addWorkerButton").html('Send');
            $('#addWorkerButton').prop('disabled', false);

        },'json');

        // This return allows the button to act as expected after a second click
        // For example, the case wehere if the form is not valid and they click, validate then click again
        return false;
    });
    
    $(".editWorkerLink").click(function(){
        var modal = '#modalWorkerEdit';
        var auth_token = $(this).prop('id').replace('edit_','');
        $("#editError").empty();
        if (!$("#emergencyContacts").hasClass('hidden')){
            $("#emergencyContacts").addClass('hidden');
        }
        $.get(site+'worker-management/get-worker-details/'+auth_token, function(response){
            $(modal+ " :input").each(function(){
                var id;
                id = $(this).prop('id');
                if (id != '' && id != 'editWorkerButton'){
                    var input = "#"+id;
                    var key = input.replace('#edit_', '');
                    $(input).val(response.data[key]);
                }
            });
            if (response.data.emergency_contacts.length>0 || response.data.next_of_kin != ''){
                $("#emergencyContacts").removeClass('hidden');
                $(response.data.emergency_contacts).each(function(id,elem){
                    var row = "<tr><td>"+elem.name+"</td><td>"+elem.contact+"</td><td>"+elem.relationship+"</td></tr>";
                    $("#emergencyContactsTable").append(row);
                });
                if (response.data.next_of_kin != ''){ 
                    var row = "<tr><td>"+response.data.next_of_kin+"</td><td>"+response.data.next_of_kin_contact+"</td><td>"+response.data.next_of_kin_relationship+"</td></tr>";
                    $("#emergencyContactsTable").append(row);
                }
            }
            
            var companyId = response.data.company_id;
            var divisionId = response.data.division_id;
            var BUId = response.data.business_unit_id;
            var groupId = response.data.group_id;
            populateDivisionEditForm(companyId,divisionId,BUId,groupId);
        });
        $(modal).modal('show');
    });
    
    $("#editWorkerButton").click(function(){
        var formData = $("#editWorkerForm").serialize();
        var modal = '#modalWorkerEdit';
        $.post('worker-management/edit-worker', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               location.reload(true); // Reload page from server; not from cache
            }else{
                $("#editError").text(response.errors);
           }
        },'json');
    });
    
    
    $(".disableWorkerLink").click(function(){
        var modal = '#modalWorkerDisable';
        var row = $(this).closest('tr');
        var auth_token = $(this).prop('id').replace('edit_','');
        $("#disable_auth_token",modal).val(auth_token);
        $(modal).modal('show');
    });
    
    $("#disableWorkerButton").click(function(){
        var formData = $("#disableWorkerForm").serialize();
        var modal = '#modalWorkerDisable';
        $.post('worker-management/disable-worker', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               location.reload();
            }
        },'json');
    });
    
    $(".deleteWorkerLink").click(function(){
        var modal = '#modalWorkerDelete';
        var row = $(this).closest('tr');
        var auth_token = $(this).prop('id').replace('edit_','');
        $("#delete_auth_token",modal).val(auth_token);
        $(modal).modal('show');
    });
    
    $("#deleteWorkerButton").click(function(){
        var formData = $("#deleteWorkerForm").serialize();
        var modal = '#modalWorkerDelete';
        $.post('worker-management/delete-worker', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               location.reload(true); // Reload page from server; not from cache
            }
        },'json');
    });
    
    $(".enableWorkerLink").click(function(){
        var modal = '#modalWorkerEnable';
        var row = $(this).closest('tr');
        var auth_token = $(this).prop('id').replace('edit_','');
        $("#enable_auth_token",modal).val(auth_token);
        $(modal).modal('show');
    });
    
    $("#enableWorkerButton").click(function(){
        var formData = $("#enableWorkerForm").serialize();
        var modal = '#modalWorkerEnable';
        $.post('worker-management/enable-worker', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               location.reload(true); // Reload page from server; not from cache
            }
        },'json');
    });
    
    
    $('#selectCompanyAdd').on('change', function() {
        company_id = this.value;
        var select = $("#selectDivisionAdd");
        $("#selectBusinessUnitAdd").empty();
        $("#selectGroupAdd").empty();
        $(select).empty();
        
        $.post(site+'company-management/company-divisions', {company:company_id}, function(response){
            if(response.status){
                $(select).empty();
                $(select).append('<option value="">Please select a division</option>');
                $.each(response.data, function(index, value){
                    $(select).append('<option value="'+value.division_id+'">'+value.division_name+'</option>');
                }); 
//                $( "#selectDivisionAdd" ).trigger( "change" );
            }
        },'json');
       
    });
    
    
    $('#selectDivisionAdd').on('change', function() {
        division_id = this.value;
        var select = $("#selectBusinessUnitAdd");
        $("#selectGroupAdd").empty();
        $(select).empty();
        
        $.post(site+'company-management/division-business-units', {division:division_id}, function(response){
            if(response.status){
                $(select).empty();
                $(select).append('<option value="">Please select a business unit</option>');
                $.each(response.data, function(index, value){
                    $(select).append('<option value="'+value.business_unit_id+'">'+value.business_unit_name+'</option>');
                }); 
//                $( "#selectBusinessUnitAdd" ).trigger( "change" );
              
            }
        },'json');
       
    });
    
    
    $('#selectBusinessUnitAdd').on('change', function() {
        business_unit_id = this.value;
        var select = $("#selectGroupAdd");
        $(select).empty();
        
        $.post(site+'company-management/business-unit-groups', {business_unit:business_unit_id}, function(response){
            if(response.status){
                $(select).empty();
                $(select).append('<option value="">Please select a project</option>');
                $.each(response.data, function(index, value){
                    $(select).append('<option value="'+value.group_id+'">'+value.group_name+'</option>');
                }); 
            }
        },'json');
       
    });
    
    
    function populateDivisionEditForm(companyId, selectedDivisionId, selectedBUId, selectedGroupId){
        var select= "#selectDivisionEdit";
        $(select).empty();
        
        $.post(site+'company-management/company-divisions', {company:companyId}, function(response){
            if(response.status){
                $(select).empty();
                $.each(response.data, function(index, value){
                    if (value.division_id == selectedDivisionId){
                        $(select).append('<option value="'+value.division_id+'" selected="selected">'+value.division_name+'</option>');
                    }else{
                        $(select).append('<option value="'+value.division_id+'">'+value.division_name+'</option>');
                    }
                }); 
//                $( "#selectDivisionAdd" ).trigger( "change" );
            }
            populateBUEditForm(selectedDivisionId, selectedBUId, selectedGroupId);
        },'json');
    }
    
    
    function populateBUEditForm(selectedDivisionId, selectedBUId, selectedGroupId){
        var select= "#selectBusinessUnitEdit";
        $(select).empty();
        
        $.post(site+'company-management/division-business-units', {division:selectedDivisionId}, function(response){
            if(response.status){
                $(select).empty();
                $.each(response.data, function(index, value){
                    if (value.business_unit_id == selectedBUId){
                        $(select).append('<option value="'+value.business_unit_id+'" selected="selected">'+value.business_unit_name+'</option>');
                    }else{
                        $(select).append('<option value="'+value.business_unit_id+'">'+value.business_unit_name+'</option>');
                    }
                }); 
//                $( "#selectDivisionAdd" ).trigger( "change" );
            }
            populateGroupEditForm(selectedBUId, selectedGroupId);
        },'json');
    }
    
    function populateGroupEditForm(selectedBUId, selectedGroupId){
        var select= "#selectGroupEdit";
        $(select).empty();
        
        $.post(site+'company-management/business-unit-groups', {business_unit:selectedBUId}, function(response){
            if(response.status){
                $(select).empty();
                $.each(response.data, function(index, value){
                    if (value.group_id == selectedGroupId){
                        $(select).append('<option value="'+value.group_id+'" selected="selected">'+value.group_name+'</option>');
                    }else{
                        $(select).append('<option value="'+value.group_id+'">'+value.group_name+'</option>');
                    }
                }); 
            }
        },'json');
    }
    
    $('#selectDivisionEdit').on('change', function() {
        division_id = this.value;
        var select = $("#selectBusinessUnitEdit");
        $("#selectGroupEdit").empty().append('<option value="">Please select a Project</option>');
        $(select).empty();
        
        $.post(site+'company-management/division-business-units', {division:division_id}, function(response){
            if(response.status){
                $(select).empty();
                $(select).append('<option value="">Please select a Business Unit</option>');
                $.each(response.data, function(index, value){
                    $(select).append('<option value="'+value.business_unit_id+'">'+value.business_unit_name+'</option>');
                }); 
//                $( "#selectBusinessUnitAdd" ).trigger( "change" );
            }
        },'json');
       
    });
    
    
    $('#selectBusinessUnitEdit').on('change', function() {
        business_unit_id = this.value;
        var select = $("#selectGroupEdit");
        $(select).empty();
        
        $.post(site+'company-management/business-unit-groups', {business_unit:business_unit_id}, function(response){
            if(response.status){
                $(select).empty();
                $(select).append('<option value="">Please select a Project</option>');
                $.each(response.data, function(index, value){
                    $(select).append('<option value="'+value.group_id+'">'+value.group_name+'</option>');
                }); 
            }
        },'json');
    });
    
});

