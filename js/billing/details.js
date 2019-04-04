$(document).ready(function(){
   $("#cancelSubscription").click(function(){
        var companyId = $("#companyId").val();
        var modal = '#modalSubscriptionCancel';

        $('#cancelSubscription').prop('disabled', true);
        $('#cancelSubscription').html('Cancelling... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');

        $.post(site+'billing/cancel-subscription/'+companyId, [], function(response){
            if(response.status){
               $.alert({
                   title: 'Sorry to see you go!',
                   content: 'Your subscription has been cancelled.',
                   confirmButtonClass: 'btn-primary',
                   confirm: function(){
                     location.reload();
                   },
                   cancel: function(){
                     location.reload();
                   }
               }); 
            }
        },'json');
    }); 
    
    $("#resumeSubscription").click(function(){
        var companyId = $("#companyId").val();

        $('#resumeSubscription').prop('disabled', true);
        $('#resumeSubscription').html('Resuming... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');

        $.post(site+'billing/resume-subscription/'+companyId, [], function(response){
            if(response.status){
                $.alert({
                    title: 'Success!',
                    content: 'Your subscription has resumed.',
                    confirmButtonClass: 'btn-primary',
                    confirm: function(){
                      location.reload();
                    },
                    cancel: function(){
                      location.reload();
                    }
                });   
            }
        },'json');
    }); 
   
    $(".deleteFAQLink").click(function(){
        var modal = '#modalDelete';
        var faqId = $(this).prop('id').replace('delete_','');
        $("#delete_faq_id").val(faqId);
        $(modal).modal('show');
    });
   
    $("#deleteFAQButton").click(function(){
        var formData = $("#deleteFAQ").serialize();
        var modal = '#modalDelete';
        $.post(site+'faq/delete', formData, function(response){
            if(response.status){
               location.reload();
            }
        },'json');
    });

    $("#validateCouponBtn").click(function(){
        var formData = $("#couponForm").serialize();
        // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
        $("#validateCouponBtn").html('Applying... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
        $('#validateCouponBtn').prop('disabled', true);

        $.post(site+'billing/apply-subscription-coupon', formData, function(response){
            if(response.status){
               $.alert({
                   title: 'Coupon Added!',
                   content: 'The page will now be refreshed.',
                   confirmButtonClass: 'btn-primary',
                   confirm: function(){
                        location.reload(false);
                   },
                   cancel:function(){
                        location.reload(false);
                   }
               });
            }else{
                $.alert({
                    title: 'Invalid Coupon!',
                    content: 'You entered an invalid coupon.',
                    confirmButtonClass: 'btn-primary',
                    confirm: function(){
                      //
                    }
                });

                //Button reset whether or not email actually sends
                $("#validateCouponBtn").html('Apply');
                $('#validateCouponBtn').prop('disabled', false);
           }
            
        },'json');
        // This return allows the button to act as expected after a second click
        // For example, the case wehere if the form is not valid and they click, validate then click again
        return false;
    });
    

    $("#removeCouponBtn").click(function(){
        $.confirm({
            title: 'Confirm',
            content: 'Are you sure you want to remove this coupon from your subscription?',
            confirmButton: 'Remove',
            confirmButtonClass: 'btn-danger',
            cancelButton: 'Cancel',
            confirm: function(){
                var formData = null;
                // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
                $("#removeCouponBtn").html('Removing... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
                $('#removeCouponBtn').prop('disabled', true);

                $.post(site+'billing/remove-subscription-coupon', formData, function(response){
                    if(response.status){
                       $.alert({
                           title: 'Coupon Removed!',
                           content: 'The page will now be refreshed.',
                           confirmButtonClass: 'btn-primary',
                           confirm: function(){
                                location.reload(false);
                           },
                           cancel:function(){
                                location.reload(false);
                           }
                       });
                    } else{
                        $.alert({
                            title: 'Unable to Remove Coupon!',
                            content: response.errors,//response.errors,
                            confirmButtonClass: 'btn-primary',
                            confirm: function(){
                              //
                            }
                        });

                        //Button reset whether or not email actually sends
                        $("#removeCouponBtn").html('Remove');
                        $('#removeCouponBtn').prop('disabled', false);
                   }
                    
                },'json');
            },
            cancel: function(){
                // Do nothing
            }
        });    
    });
});

