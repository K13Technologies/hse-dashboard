$(document).ready(function(){
   $(".editFAQLink").click(function(){
       $(".edit-content").hide();
       $(".static-content").show();
         var labels = $(this).closest(".hero-unit").find('.static-content');
         var inputs = $(this).closest(".hero-unit").find('.edit-content');
         $(labels).fadeOut(500,function(){
             $(inputs).fadeIn();
         });
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
               $(modal).modal('hide');
               $(modal).on('hidden', function () {
                     location.reload();
               });
            }
        },'json');
    });
   
   
});

