$(document).ready(function(){

  $('.finalPurchaseConfirmationBtn').click(function(e) {
    e.preventDefault();
    var $clickedButton = $(this);
    $clickedButton.prop('disabled', true);
    $clickedButton.html('<b>PURCHASING...</b> <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
    var $form = $('#purchaseInfoForm');
     
    $.post(site + 'purchase-services',  $form.serialize(), function(response){
        if(response.status){
            // successful purch
            $.alert({
                title: 'Purchase Successful!',
                content: 'Thank you for your purchase. A receipt should be emailed to you shortly.',
                confirmButtonClass: 'btn-primary',
                confirmButton: 'Okay',
                backgroundDismiss: false,
                theme: 'supervan',
                confirm: function() {
                    $clickedButton.html('Successfully Purchased!');
                    window.location.replace(site);
                }
            });
        } else {
            $.alert({
                title: 'Error!',
                content: response.errors,
                confirmButtonClass: 'btn-primary',
                confirmButton: 'Okay',
                backgroundDismiss: false,
                theme: 'supervan',
                confirm: function() {
                    $clickedButton.prop('disabled', false);
                    $clickedButton.html('<b>CONFIRM PURCHASE</b>');
                }
            });
       }
    },'json'); 

  }); // .finalPurchaseConfirmationBtn click

  $('.purchaseServiceBtn').click(function(e) {
      e.preventDefault();

      var $clickedButton = $(this);

      if(hasCC){
        var serviceId = $(this).val();
        //$button.prop('disabled', true);
        
        //var $form = $('#purchaseServiceForm');
        // $form.serialize()

        // Set the value of the hidden field in the form to the service they have selected
        $('.hiddenServiceIdentifier').val(serviceId);
        // Disable ability to dismiss modal
        $('#moreInfoPurchase').modal({backdrop: 'static', keyboard: false});
        $('#moreInfoPurchase').modal('show');


          // $.confirm({
          //     title: 'Confirm Purchase',
          //     content: 'Do you want to buy this package?',
          //     confirmButton: 'Yes',
          //     cancelButton: 'No',
          //     confirmButtonClass: 'btn-success',
          //     theme: 'supervan',
          //     backgroundDismiss: false,
          //     confirm: function() {
          //       $button.html('Purchasing... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
          //       $.post(site + 'purchase-services',  { service_code: serviceId }, function(response){
          //           if(response.status){
          //               // successful purch
          //               $.alert({
          //                   title: 'Purchase Successful!',
          //                   content: 'Thank you for your purchase. A receipt should be emailed to you shortly.',
          //                   confirmButtonClass: 'btn-primary',
          //                   confirmButton: 'Okay',
          //                   backgroundDismiss: false,
          //                   theme: 'supervan',
          //                   confirm: function() {
          //                       $button.html('Successfully Purchased!');
          //                   }
          //               });
          //           } else {
          //               $.alert({
          //                   title: 'Error!',
          //                   content: response.errors,
          //                   confirmButtonClass: 'btn-primary',
          //                   confirmButton: 'Okay',
          //                   backgroundDismiss: false,
          //                   theme: 'supervan',
          //                   confirm: function() {
          //                       $button.prop('disabled', false);
          //                   }
          //               });
          //          }
          //       },'json'); 
          //     },
          //     cancel: function() {
          //         $button.prop('disabled', false);
          //     }
          // });

      } else {
          $.confirm({
              title: 'Please Subscribe',
              content: 'It looks like you haven\'t yet entered a credit card and subscribed to our service. Would you like to do that now?',
              confirmButton: 'Yes',
              cancelButton: 'No',
              confirmButtonClass: 'btn-primary',
              backgroundDismiss: false,
              confirm: function() {
                  window.location.replace(site+'/billing/credit-card-details');
              },
              onClose: function() {
                $clickedButton.prop('disabled', false);
              }
          });
      }
  });


  $('.upgradeToBtn').click(function(e) {
      e.preventDefault();

      var $button = $(this)
      $button.prop('disabled', true);
      
      var serviceId = $(this).val();
      var $form = $('#purchaseServiceForm');

      $.confirm({
          title: 'Confirm Upgrade',
          content: 'Do you want to upgrade to this subscription?',
          confirmButton: 'Yes',
          cancelButton: 'No',
          confirmButtonClass: 'btn-success',
          theme: 'supervan',
          backgroundDismiss: false,
          confirm: function() {
            $button.html('Upgrading... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $.post(site + 'purchase-services',  { service_code: serviceId }, function(response){
                if(response.status){
                    // successful purch
                    $.alert({
                        title: 'Upgrade Successful!',
                        content: 'Thank you for your purchase.',
                        confirmButtonClass: 'btn-primary',
                        confirmButton: 'Okay',
                        backgroundDismiss: false,
                        theme: 'supervan',
                        confirm: function() {
                            $button.html('Successfully Upgraded!');
                        }
                    });
                } else {
                    $.alert({
                        title: 'Error!',
                        content: response.errors,
                        confirmButtonClass: 'btn-primary',
                        confirmButton: 'Okay',
                        backgroundDismiss: false,
                        theme: 'supervan',
                        confirm: function() {
                            $button.prop('disabled', false);
                        }
                    });
               }
            },'json'); 
          },
          cancel: function() {
              $button.prop('disabled', false);
          }
      });

  }); // /$('.upgradeToBtn').click

  // TOOLTIPS
  // http://www.jqueryscript.net/tooltip/Bootstrap-Popover-Enhancement-Plugin-with-jQuery-WebUI-Popover.html
  // http://www.jqueryscript.net/demo/Bootstrap-Popover-Enhancement-Plugin-with-jQuery-WebUI-Popover/
  // https://github.com/sandywalker/webui-popover
    var fullServiceExplanation = "<ol><li>Are you just starting out with us? </li><li>Did you have a large group of new hires?</li></ol>";
    $('.fullServiceSetup').webuiPopover({title: 'Let us help you:', content:fullServiceExplanation,trigger:'hover', style:'inverse'});
  // END TOOLTIPS

});