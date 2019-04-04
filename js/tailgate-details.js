$(document).ready(function(){
        $(".nivoSlider").nivoSlider({
            directionNav: true,             // Next & Prev navigation
            controlNav: false,               // 1,2,3... navigation
            manualAdvance: true,           // Force manual transitions
            prevText: '',               // Prev directionNav text
            nextText: '',               // Next directionNav text
            effect:'slideInRight'
        });
        
        $('.see-visitor-link').click(function(){
            var modal = '#modalVisitor';
            var visitorId = $(this).prop('id').replace('see_visitor_','');
            $.get(site+'tailgates/get-visitor-details/'+visitorId, function(response){
                if (response.status){
                    populateVisitorModal(response.data);
                    $(modal).modal('show');
                }
            });
        });
        
        $('.see-worker-link').click(function(){
            var modal = '#modalWorker';
            var workerId = $(this).prop('id').replace('see_worker_','');
            $.get(site+'tailgates/get-worker-details/'+workerId, function(response){
                if (response.status){
                    populateWorkerModal(response.data);
                    $(modal).modal('show');
                }
            });
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
                $("#completeExportToMail").html('Sending... <img src="../../assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
                $('#completeExportToMail').prop('disabled', true);

                $.post(site+'tailgates/mail', formData, function(response){
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

        $(".riskHelpBtn").click(function(){
            alert('[LOW] Reduce as practical. No Further action required.\n'+
                  '[MEDIUM] Undesirable. Take risk reduction measures. Action required.\n'+
                  '[HIGH] Critical. Take immediate risk reduction measures. Urgent action required.');
        });

        function populateVisitorModal(data){
            $("#modalVisitorName").text(data.first_name+' '+data.last_name);
            if(data.signature){
                var url = site+'image/tailgates/visitor/'+data.signoff_visitor_id+'/signature';
                $("#signatureVisitor").prop('src',url);
                $("#signatureVisitor").parent().prop('href',url);
            }else{
                $("#signatureVisitor").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
                $("#signatureVisitor").parent().prop('href','#');
            }
        }
        
        function populateWorkerModal(data){
            $("#modalWorkerName").text(data.first_name+' '+data.last_name);
            if(data.signature){
                var url = site+'image/tailgates/worker/'+data.signoff_worker_id+'/signature';
                $("#signatureWorker").prop('src',url);
                $("#signatureWorker").parent().prop('href',url);
            }else{
                $("#signatureWorker").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
                $("#signatureWorker").parent().prop('href','#');
            }
        }

    // BEGIN MODULAR CLASSES ============================
    // THESE MUST BE DECLARED BEFORE THEY ARE CALLED
    var Task = function(data){
        var self = this;

        self.tailgate_task_id = ko.observable('');
        self.title = ko.observable('');
        self.tailgate_id = ko.observable('');
        self.hazards = ko.observableArray([]);
        self.hazardsAreVisible = ko.observable(false);

        if (typeof data !== 'undefined') {
            self.tailgate_task_id = ko.observable(data.tailgate_task_id);
            self.title = ko.observable(data.title);
            self.tailgate_id = ko.observable(data.tailgate_id);

            // If the task has hazards, create and populate them
            if (typeof data.hazards !== 'undefined') {
                $.each(data.hazards, function(i, el) {
                    self.hazards.push(new Hazard(el));
                });
            }
        }

        self.removeHazard = function(hazard) {
            self.hazards.remove(hazard);
        };
        self.addHazard = function() {
            //console.log("added");
            self.hazards.push(new Hazard());
        };
        self.changeHazardVisibility = function () {
            // This is the syntax used to change the values of observables. = are not used.
            self.hazardsAreVisible(!self.hazardsAreVisible());
        };
    };


    // Hazard class
    var Hazard = function(data) {
        var self = this;
        self.tailgate_task_hazard_id = ko.observable(''); 
        self.description  = ko.observable('');
        self.risk_level = ko.observable(''); 
        self.eliminate_hazard = ko.observable(''); 
        self.risk_assessment = ko.observable(); 
        self.tailgate_task_id = ko.observable(''); 
        self.riskLevels = ko.observableArray([{label: 'Low', riskValue: 0 }, {label: 'Medium', riskValue: 1 }, {label: 'High', riskValue: 2 }]);

        if (typeof data !== 'undefined') {
            self.tailgate_task_hazard_id(data.tailgate_task_hazard_id);
            self.description(data.description);
            self.risk_level(data.risk_level);
            self.eliminate_hazard(data.eliminate_hazard);
            self.risk_assessment(data.risk_assessment);
            self.tailgate_task_id(data.tailgate_task_id);
        }

        this.hazardRiskLevelColour = ko.computed(function() {
            if (self.risk_assessment()==0) {
                return 'green';
            }

            if (self.risk_assessment()==1) {
                return '#E69B31'; //Yellow
            }

            if (self.risk_assessment()==2 ) {
                return '#D73530'; //Red
            }

            return '';
           }, this);       
    }
    // END MODULAR CLASSES ============================

    var TailgateModel = function (supervisors, permitNumbers, locations, lsds, tasks) {
        var self = this;
        self.supervisors = ko.observableArray(supervisors);
        self.permitNumbers = ko.observableArray(permitNumbers);
        self.locations = ko.observableArray(locations);
        self.lsds = ko.observableArray(lsds);
        self.tasks = ko.observableArray([]);

        self.save = function() {
            // In case we want to switch to JSON saving
            self.lastSavedJson(JSON.stringify(ko.toJS(self), null, 2));
        };
        
        self.lastSavedJson = ko.observable('');

        // If there are original tasks passed in, create them
        if (typeof tasks !== 'undefined') {
            $.each(tasks, function(i, el) {
                self.tasks.push(new Task(el));
            });
        }

        // PERMIT MANAGEMENT ==========================
        self.addPermitNumber = function() {
            self.permitNumbers.push({
                tailgate_permit_id:"",
                permit_number: "",
                tailgate_id: ""
            });
        };

        self.removePermitNumber = function(permit) {
            self.permitNumbers.remove(permit);
        };

        // SUPERVISOR MANAGEMENT ==========================
        self.addSupervisor = function() {
            self.supervisors.push({
                tailgate_supervisor_id: "",
                name: "",
                tailgate_id: ""
            });
        };

        self.removeSupervisor = function(supervisor) {
            self.supervisors.remove(supervisor);
        };

        //LOCATION MANAGEMENT ==========================
        self.addLocation = function() {
            self.locations.push({
                tailgate_location_id: "",
                location: "",
                tailgate_id: ""
            });
        };

        self.removeLocation = function(location) {
            self.locations.remove(location);
        };

        //LSD MANAGEMENT ==========================
        self.addLSD = function() {
            self.lsds.push({
                tailgate_lsd_id: "",
                lsd: "",
                tailgate_id: ""
            });
        };

        self.removeLSD = function(lsd) {
            self.lsds.remove(lsd);
        };

        // TASK MANAGEMENT ========================
        self.addTask = function() {
            self.tasks.push(new Task());
        };

        self.removeTask = function (task) {
            self.tasks.remove(task);
        };   
    };

    // BIND TO VIEW
    ko.applyBindings(new TailgateModel(originalSupervisors, originalPermits, originalLocations, originalLSDs, originalTasks));

    $('#editTailgateForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $(".editTailgateButton").val('Saving...');
        $('.editTailgateButton').prop('disabled', true);
    
        // Here is where we could also JSON serialize the tailgate object.
        var formData = $("#editTailgateForm").serialize();
        var returnMessage = '';

        // ===========================================================================
        // *** HERE IS WHERE THE OBJECT WOULD BE VALIDATED BEFORE POSTING ***
        // ===========================================================================

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'tailgates/edit-tailgate', formData, function(response){
            if(response.status){
                returnMessage = 'Save Successful.\n\nPlease refresh the page if you wish to view changes.';
            } else {
                returnMessage = 'Save Failed:\n\n';
                for (i = 0; i < response.errors.length; i++) { 
                    returnMessage += '- ' + response.errors[i] + '\n';
                }
            }

            alert(returnMessage);

            $(".editTailgateButton").val('Save Edits');
            $('.editTailgateButton').prop('disabled', false);
        },'json');

        return false;
    });
});

