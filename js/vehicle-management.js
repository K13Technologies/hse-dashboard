$(document).ready(function(){
    
    $('[rel=tooltip]').tooltip();
          
    $(".deleteVehicleLink").click(function(){
        var modal = '#modalVehicleDelete';
        var row = $(this).closest('tr');
        var vehicle_id = $(this).prop('id').replace('edit_','');
        $("#delete_vehicle_id",modal).val(vehicle_id);
        $(modal).modal('show');
    });
    
    $("#deleteVehicleButton").click(function(){
        $("#deleteVehicleButton").html('Deleting...');
        $('#deleteVehicleButton').prop('disabled', true);

        var formData = $("#deleteVehicleForm").serialize();
        var modal = '#modalVehicleDelete';
        $.post('vehicle-management/delete-vehicle', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
               location.reload();
            }
        },'json');
    });
    
    $(".editVehicleLink").click(function(){
        var modal = '#modalVehicleEdit';
        var vehicle_id = $(this).prop('id').replace('edit_','');
        $('#modalVehicleEdit .help-inline').remove();
        $('#modalVehicleEdit .control-group').removeClass('error');
        $.get(site+'vehicle-management/get-vehicle-details/'+vehicle_id, function(response){
            
            console.log(response)
            $("#edit_vehicle_id").val(response.data.vehicle_id);
            $("#edit_license_plate").val(response.data.license_plate);
            $("#edit_vehicle_number").val(response.data.vehicle_number);
            $("#edit_color").val(response.data.color);
            $("#edit_mileage").val(response.data.mileage);
            return;
        });
        $(modal).modal('show');
    });
    
    $("#editVehicleButton").click(function(){
        var formData = $("#editVehicleForm").serialize();
        var modal = '#modalVehicleEdit';
        $.post('vehicle-management/edit-vehicle', formData, function(response){
            if(response.status){
               $(modal).modal('hide');
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

});

