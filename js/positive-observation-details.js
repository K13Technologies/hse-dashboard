$(document).ready(function(){
    
    var prev ="";
    
    $(".nivoSlider").nivoSlider({
        directionNav: true,             // Next & Prev navigation
        controlNav: false,               // 1,2,3... navigation
        manualAdvance: true,           // Force manual transitions
        prevText: '',               // Prev directionNav text
        nextText: '',               // Next directionNav text
        effect:'slideInRight'
    });
        
    $("#completeExportToMail").click(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();

        var formData = $("#mailExportForm").serialize();
        var modal = '#modalEmailSave';
        $("#addError").empty();
        $('#modalEmailSave .help-inline').remove();
        $('#modalEmailSave .control-group').removeClass('error');
        validateForm();
        if($("#mailExportForm").valid()){
            // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
            $("#completeExportToMail").html('Sending... <img src="../../assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $('#completeExportToMail').prop('disabled', true);

            $.post(site+'field-observations/mail', formData, function(response){
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
    
    $("#exportPDFButton").click(function(event){
            // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
            event.preventDefault();
            // Send download request
            window.location.replace(site+"field-observations/export/"+ $('[name="positive_observation_id"]').val());
    });
    
    /*
    $('#btAdd').click(function(event) {
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();

        var count = $("#peopleObservedContainer div").length;
        var newID = count + 1;

        $('#btRemove').prop('disabled', false);

        var personObject = '<div class="personWell person'+newID+'"> ' +
                                '<label for="personsObserved['+newID+'][name]" class="content-label required">Name</label>' +
                                '<input placeholder="Person Name" required="required" name="personsObserved['+newID+'][name]" type="text" value="" id="personsObserved['+newID+'][name]" required>'+
                                '<label for="personsObserved['+newID+'][company]" class="content-label required">Company</label>' +
                                '<input placeholder="Company Name" required="required" name="personsObserved['+newID+'][company]" type="text" value="" id="personsObserved['+newID+'][company]" required>'+
                                '<input name="personsObserved['+newID+'][positive_observation_id]" type="hidden" value="">'+
                            '</div>';

        $('#peopleObservedContainer').append(personObject).last('div').fadeIn();
    });

    $('#btRemove').click(function(event) {  
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();

        var count = $("#peopleObservedContainer div").length;

        // At least one person must be observed
        if (count>1) {
            $('#btRemove').prop('disabled', false);
            // Fade out then remove the element
            $('#peopleObservedContainer div:last').fadeOut("normal", function() {
                $(this).remove();
            });
        } else  {
            $('#btRemove').prop('disabled', true);
        }
    });*/

    $('#btnAddTask').click(function(event) {
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        //event.preventDefault();

        var count = $("#taskContainer .taskObject").length;
        var newID = count + 1;

        if (newID>1) {
            $('#btnRemoveTask').prop('disabled', false);
        }

        if (count == 3) {
            $('#btnAddTask').prop('disabled', true);
        } else {
            $('#btnAddTask').prop('disabled', false);

            var taskObject =    '<div class="taskObject">' +
                                    '<div class="content-numbering pull-left"> '+newID+' </div>' +
                                    '<div class="pull-left numbered-content">' +
                                        '<div class="content-label"><label for="task_'+newID+'_title" class="content-label">Title</label></div>' +
                                        '<div class="content-description"> ' +
                                            '<input maxlength="100" class="form-control" required="required" placeholder="Title" name="task_'+newID+'_title" type="text" value="" id="task_'+newID+'_title"> ' +
                                        '</div>' +
                                        '<div class="content-label"><label for="task_'+newID+'_description" class="content-label">Description</label></div>' +
                                        '<div class="content-description">' +
                                        '   <textarea rows="4" class="action-description form-control" placeholder="Description for this task" required="required" maxlength="400" name="task_'+newID+'_description" cols="50" id="task_'+newID+'_description"></textarea>' +
                                        '</div>' +
                                    '</div>' +
                                '</div' +
                                '<div class="clearfix"></div>';

            $('#taskContainer').append(taskObject).last('div').fadeIn();
        } 
    });

    $('#btnRemoveTask').click(function(event) {  
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();

        var count = $("#taskContainer .taskObject").length;

        // At least one person must be observed
        if (count>1) {
            $('#btnRemoveTask').prop('disabled', false);
            // Fade out then remove the element
            $('#taskContainer .taskObject:last').fadeOut("normal", function() {
                $(this).remove();
            });
        } else  {
            $('#btnRemoveTask').prop('disabled', true);
        }

        // Now that we have taken away one check to see that they can delete again
        if (count - 1 < 3) {
            $('#btnAddTask').prop('disabled', false);
        }
    });

    $('#selectHelpButton').click(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        alert('Please choose one or more items. To choose more than one, hold the CTRL key (Windows) or APPLE key (Mac) before clicking each new item.');
    });

    $('#positiveObservationSwitch').change(function(){
        if($('#positiveObservationSwitch').prop('checked')) {
            
            $('#correctAtRiskContainer').prop('hidden', true);
            $('[name="correct_on_site"]').prop('disabled',true);
            $('#isPODetailsLabel').html('Positive observation description');

        } else {
            
            $('#correctAtRiskContainer').prop('hidden', false);
            $('[name="correct_on_site"]').prop('disabled',false);
            $('#isPODetailsLabel').html('Corrective action for at risk behaviour');
        }

        // Reset box contents on switch
        $('[name="is_po_details"]').val('');
    });

    $('#editObservationForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $(".editObservationButton").val('Saving...');
        $('.editObservationButton').prop('disabled', true);
    
        var formData = $("#editObservationForm").serialize();
        var returnMessage = '';

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'field-observations/edit-observation', formData, function(response){
            if(response.status){
                returnMessage = 'Save Successful.\n\nPlease refresh the page if you wish to view changes.';
            } else {
                 returnMessage = 'Save Failed:\n\n'+ response.errors;
            }

            alert(returnMessage);

            $(".editObservationButton").val('Save Edits');
            $('.editObservationButton').prop('disabled', false);
        },'json');

        return false;
    });

    $(".action-date-picker").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true
    });


    /* === KNOCKOUT.JS BEGIN === */
    var ObservedPersonModel = function(people) {

        var self = this;
        self.people = ko.observableArray(people);

        var observationID = $('[name="positive_observation_id"]').val();
     
        self.addPerson = function() {
            self.people.push({
                person_id:"",
                name: "",
                company: ""
            });
        };
     
        self.removePerson = function(person) {
            self.people.remove(person);   
        };
    };
    
    // JSON string is input value. In this case, the var originalPeopleObserved is defined in the view
    var people = new ObservedPersonModel(originalPeopleObserved);
    ko.applyBindings(people);
     
    // Activate jQuery Validation
    //$("#editObservationForm").validate({ submitHandler: viewModel.save });

    /* === END KNOCKOUT.JS === */
});

