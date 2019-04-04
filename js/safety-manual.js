$(document).ready(function () {

    $(document).on('click', '.editSafetyManualBtn', function(e) { 
        $.confirm({
            title: 'Change Comments',
            content: 'Describe the changes you have made to the safety manual (recommended for auditing purposes) <br/><br/> <textarea id="revisionDescriptionBox" class="form-control" maxlength=5000></textarea>',
            cancelButton: false,
            confirmButton: 'Save Manual',
            confirmButtonClass: 'btn-success',
            keyboardEnabled: true, // User can press enter or ESC
            backgroundDismiss: false,
            confirm: function(){
                safetyManualObj.revision_description($('#revisionDescriptionBox').val());
                safetyManualObj.save();
                var formData = safetyManualObj.lastSavedJson();
                var returnMessage = '';

                this.close;

                $.post(site+'safety-manual/edit-safety-manual', formData, function(response){
                    if(response.status){ 
                        $.alert({
                            title: 'Save Successful',
                            content: 'The page will now be reloaded.',
                            confirmButton: 'Ok',
                            confirmButtonClass: 'btn-primary',
                            confirm: function(){
                                location.reload(false);
                            },
                            cancel: function(){
                                location.reload(false);
                            }
                        });

                    } else {
                        $.alert({
                            title: 'Save Failed',
                            content: response.errors,
                            confirmButtonClass: 'btn-primary',
                            confirmButton: 'Ok',
                            confirm: function(){
                                // Do nothing
                            }
                        });
                    }

                    //$(".editSafetyManualBtn").val('Save Edits');
                    //$('.editSafetyManualBtn').prop('disabled', false);
                },'json');
            }
        });

        return false;
    });

    $("#completeExportToMail").click(function(){
        var formData = $("#mailExportForm").serialize();
        var modal = '#modalEmailSave';
        $("#addError").empty();
        $('#modalEmailSave .help-inline').remove();
        $('#modalEmailSave .control-group').removeClass('error');
        validateForm();

        if($("#mailExportForm").valid()){
            // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
            $("#completeExportToMail").html('Sending... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $('#completeExportToMail').prop('disabled', true);

            $.post(site+'safety-manual/mail', formData, function(response){
                if(response.status){
                   $(modal).modal('hide');
                   $(modal+' #email').val('');
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

    var Section = function(data){
        var self = this;
        self.section_id = ko.observable(0);
        self.safety_manual_id = ko.observable(0);
        self.section_title = ko.observable('Untitled Section');
        self.is_SJP = ko.observable(0);
        self.is_SWP = ko.observable(0);
        self.subsections = ko.observableArray([]);
        self.subsectionsAreVisible = ko.observable(false);
        self.isDeleted = ko.observable(false);

        if (typeof data !== 'undefined') {
            self.section_title = ko.observable(data.section_title);
            self.section_id = ko.observable(data.section_id);
            self.safety_manual_id = ko.observable(data.safety_manual_id);
            self.is_SJP = ko.observable(data.is_SJP);
            self.is_SWP = ko.observable(data.is_SWP);

            if (typeof data.subsections !== 'undefined') {
                $.each(data.subsections, function(i, el) {
                    self.subsections.push(new Subsection(el));
                });
            }
        }

        self.removeSubsection = function(subsection) {
            subsection.isDeleted(true);
        };

        self.addSubsection = function() {
            self.subsections.push(new Subsection());
        };

        self.changeSubsectionVisibility = function () {
            // This is the syntax used to change the values of observables. = are not used.
            self.subsectionsAreVisible(!self.subsectionsAreVisible());
        };

        self.nonDeletedSubsectionCount = function () {
            // This is the syntax used to change the values of observables. = are not used.
            self.subsectionsAreVisible(!self.subsectionsAreVisible());
        };

        self.nonDeletedSubsections = ko.computed(function() {
            return ko.utils.arrayFilter(self.subsections(), function (subsection) {
                return ( subsection.isDeleted() == false );
            });

        }, self);
    };


    var Subsection = function(data) {
        var self = this;
        self.subsection_id = ko.observable(0);
        self.section_id = ko.observable(0);
        self.safety_manual_id = ko.observable(0);
        self.subsection_title = ko.observable('Untitled Subsection'); 
        self.subsection_content  = ko.observable('');
        self.showEditor = ko.observable(false);
        self.isDeleted = ko.observable(false);

        if (typeof data !== 'undefined') {
            self.subsection_id = ko.observable(data.subsection_id);
            self.section_id = ko.observable(data.section_id);
            self.safety_manual_id = ko.observable(data.safety_manual_id);
            self.subsection_title(data.subsection_title);
            self.subsection_content(data.subsection_content);
        }  

        self.showEditorSwitch = function() {
            self.showEditor(!self.showEditor()); // Switch it to opposite
        };  
    }  

    var SafetyManualModel = function (safetyManual) {
        var self = this;
        self.safety_manual_id = ko.observable(safetyManual.safety_manual_id);
        self.revision_description = ko.observable('');
        self.major_version_number = ko.observable(1);
        self.minor_version_number = ko.observable(0);
        self.sections = ko.observableArray([]);

        if (typeof safetyManual !== 'undefined') {
            self.major_version_number(safetyManual.major_version_number);
            self.minor_version_number(safetyManual.minor_version_number);
        }

        // If there are original tasks passed in, create them
        if (typeof safetyManual.sections !== 'undefined') {
            $.each(safetyManual.sections, function(i, el) {
                self.sections.push(new Section(el));
            });
        }

        self.lastSavedJson = ko.observable('');

        self.save = function() {
            self.lastSavedJson(JSON.stringify(ko.toJS(self), null, 2));
        };

        self.removeSection = function(section) {
            section.isDeleted(true);

            // Set each child subsection to also be deleted 
            section.subsections().forEach(function (subsection, index, array) {
                subsection.isDeleted(true);
            });
        };

        self.addSection = function() {
            //console.log("added");
            self.sections.push(new Section());
        };

        self.wysiwygOptions = {    
                schema: 'html5',
                inline: false,
                toolbar: 'bold italic underscore', /*image*/
                menubar: true,
                plugins: [
                            "advlist autolink lists link hr anchor pagebreak",
                            "searchreplace wordcount visualblocks visualchars code fullscreen",
                            "insertdatetime nonbreaking table directionality",
                            "template paste textcolor colorpicker textpattern autoresize imageupload" /*image  contextmenu*/
                         ], /*imagetools removed for now*/
                /*contextmenu: "paste link inserttable | cell row column deletetable",*/
                autoresize_max_height: 800,
                paste_data_images: false,
                browser_spellcheck : true,
                /*image_advtab: true,*/
                toolbar1: "undo redo | styleselect | bold italic underline fontselect fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | imageupload",
                tools: "inserttable fullscreen", /*image, imagetools removed for now*/
                responsive: true,
                fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 18pt 24pt 36pt",
                paste_retain_style_properties: "all",
                relative_urls: false,
                remove_script_host : false,
                convert_urls : true
            }; 
    };

    // BIND TO VIEW
    var safetyManualObj = new SafetyManualModel(safetyManual);
    ko.applyBindings(safetyManualObj);


    // START BACK TO TOP TOOL ====================================
    var offset = 220;
    var duration = 500;
    jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
    });
   
    jQuery('.back-to-top').click(function(event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, duration);
        return false;
    });
    // END BACK TO TOP TOOL ======================================


    // LOADING SCREEN ========================================================
    //triggered when modal is about to be shown
    $('#sendEmailModal').on('show.bs.modal', function(e) {

        //get data-id attribute of the clicked element
        var sectionId = $(e.relatedTarget).data('section-id');
        var subsectionId = $(e.relatedTarget).data('subsection-id');

        //populate the textbox
        $(e.currentTarget).find('input[name="sectionId"]').val(sectionId);
        $(e.currentTarget).find('input[name="subsectionId"]').val(subsectionId);
    });

    $(window).bind("load", function() {
       $(".preloadContainer").fadeOut(1000, function() {           
       });
       $(".container").fadeIn(2000, function() {           
       });
       $("#footer").fadeIn(2000, function() {           
       });
    });

    // END LOADING SCREEN ========================================================


    // RESPONSIVE TEXT AREAS ========================================================
        function h(e) {
            $(e).css({'height':'auto','overflow-y':'hidden'}).height(e.scrollHeight);
        }

        $('textarea').each(function () {
          h(this);

        }).on('input', function () {
          h(this);
        });
    // END RESPONSIBLE TEXTAREAS ========================================================

}); // end document ready