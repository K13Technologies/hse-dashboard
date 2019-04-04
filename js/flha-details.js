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
        $.get(site+'flha/get-visitor-details/'+visitorId, function(response){
            if (response.status){
                populateVisitorModal(response.data);
                $(modal).modal('show');
            }
        });
    });
        
    $('.see-worker-link').click(function(){
        var modal = '#modalWorker';
        var workerId = $(this).prop('id').replace('see_worker_','');
        $.get(site+'flha/get-worker-details/'+workerId, function(response){
            if (response.status){
                populateWorkerModal(response.data);
                $(modal).modal('show');
            }
        });
    });
   
    $('.see-spotcheck-link').click(function(){
        var modal = '#modalSpotcheck';
        var spotcheckId = $(this).prop('id').replace('see_spotcheck_','');
        $.get(site+'flha/get-spotcheck-details/'+spotcheckId, function(response){
            if (response.status){
                populateSpotcheckModal(response.data);
                $(modal).modal('show');
            }
        });
    });
        
    function populateVisitorModal(data){
        $("#modalVisitorName").text(data.first_name+' '+data.last_name);
        $("#modalVisitorCompany").text(data.company);
        $("#modalVisitorPosition").text(data.position);
        if(data.photo){
            var url = site+'image/flha/visitor/'+data.signoff_visitor_id+'/profile';
            $("#profileVisitor").prop('src',url);
            $("#profileVisitor").parent().prop('href',url);
        }else{
            $("#profileVisitor").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
            $("#profileVisitor").parent().prop('href','#');
        }
        if(data.signature){
            var url = site+'image/flha/visitor/'+data.signoff_visitor_id+'/signature';
            $("#signatureVisitor").prop('src',url);
            $("#signatureVisitor").parent().prop('href',url);
        }else{
            $("#signatureVisitor").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
            $("#signatureVisitor").parent().prop('href','#');
        }
    }
        
    function populateWorkerModal(data){
        $("#modalWorkerName").text(data.first_name+' '+data.last_name);
        $("#modalWorkerCompany").text(data.company);
        $("#modalWorkerPosition").text(data.position);
        $("#workerBreaks").empty();
        $.each(data.breaks,function(index,value){
        var breakType = "";
            switch(value['type']){
            case '0':
                breakType = "Morning";
              break;
            case '1':
                 breakType = "Lunch";
              break;
            case '2':
                breakType = "Evening";
              break;
            default:
            }
            $("#workerBreaks").append('<div class="modal-content-description">'+value.created_at+" ("+ breakType +' break)</div>');
        });
        if(data.photo){
            var url = site+'image/flha/worker/'+data.signoff_worker_id+'/profile';
            $("#profileWorker").prop('src',url);
            $("#profileWorker").parent().prop('href',url);
        }else{
            $("#profileWorker").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
            $("#profileWorker").parent().prop('href','#');
        }
        if(data.signature){
            var url = site+'image/flha/worker/'+data.signoff_worker_id+'/signature';
            $("#signatureWorker").prop('src',url);
            $("#signatureWorker").parent().prop('href',url);
        }else{
            $("#signatureWorker").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
            $("#signatureWorker").parent().prop('href','#');
        }
    }
        
        
    function populateSpotcheckModal(data){
        $("#modalSpotcheckName").text(data.first_name+' '+data.last_name);
        $("#modalSpotcheckCompany").text(data.company);
        $("#modalSpotcheckPosition").text(data.position);
        $("#modalSpotcheckTime").text(data.created_at);
        
        $("#spotcheckValidity").text(data.flha_validity=="1"?"Yes":"No");
        if(data.flha_validity=="1"){
            $("#validityDescContainer").addClass('hide');
        }else{
            $("#validityDescContainer").removeClass('hide');
            $("#spotcheckValidityDesc").text(data.flha_validity_description);
        }
        
        $("#spotcheckCriticalHazard").text((data.critical_hazard=="1")?"Yes":"No");
        if(data.critical_hazard=="0"){
            $("#criticalHazardDescContainer").addClass('hide');
        }else{
            $("#criticalHazardDescContainer").removeClass('hide');
            $("#spotcheckCriticalHazardDesc").text(data.critical_hazard_description);
        }
        
        $("#spotcheckCrew").text((data.crew_list_complete=="1")?"Yes":"No");
        if(data.crew_list_complete=="1"){
            $("#crewlistDescContainer").addClass('hide');
        }else{
            $("#crewlistDescContainer").removeClass('hide');
            $("#spotcheckCrewDesc").text(data.crew_description);
        }
        
        
        if(data.signature){
            var url = site+'image/flha/spotcheck/'+data.spotcheck_id+'/signature';
            $("#signatureSpotcheck").prop('src',url);
            $("#signatureSpotcheck").parent().prop('href',url);
        }else{
            $("#signatureSpotcheck").prop('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
            $("#signatureSpotcheck").parent().prop('href','#');
        }
    }
        
        
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

            $.post(site+'flha/mail', formData, function(response){
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

    // BEGIN MODULAR CLASSES ============================
    // THESE MUST BE DECLARED BEFORE THEY ARE CALLED
    var Task = function(data){
        var self = this;

        self.flha_task_id = ko.observable('');
        self.title = ko.observable('');
        self.flha_id = ko.observable('');
        self.hazards = ko.observableArray([]);
        self.hazardsAreVisible = ko.observable(false);

        if (typeof data !== 'undefined') {
            self.flha_task_id = ko.observable(data.flha_task_id);
            self.title = ko.observable(data.title);
            self.flha_id = ko.observable(data.flha_id);

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
        self.flha_task_hazard_id = ko.observable(''); 
        self.description  = ko.observable('');
        self.risk_level = ko.observable(''); 
        self.eliminate_hazard = ko.observable(''); 
        self.risk_assessment = ko.observable(); 
        self.flha_task_id = ko.observable(''); 
        self.riskLevels = ko.observableArray([{label: 'Low', riskValue: 0 }, {label: 'Medium', riskValue: 1 }, {label: 'High', riskValue: 2 }]);

        if (typeof data !== 'undefined') {
            self.flha_task_hazard_id(data.flha_task_hazard_id);
            self.description(data.description);
            self.risk_level(data.risk_level);
            self.eliminate_hazard(data.eliminate_hazard);
            self.risk_assessment(data.risk_assessment);
            self.flha_task_id(data.flha_task_id);
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

    var FlhaModel = function (flha, permits, locations, lsds, sites, tasks) {
        var self = this;
        self.flha_id = ko.observable(flha.flha_id);
        self.supervisorName = ko.observable(flha.supervisor_name);
        self.supervisorNumber = ko.observable(flha.supervisor_number);
        self.title = ko.observable(flha.title);
        self.job_description = ko.observable(flha.job_description);
        self.muster_point = ko.observable(flha.muster_point);
        self.client = ko.observable(flha.client);
        self.radio_channel = ko.observable(flha.radio_channel);
        self.supervisor_name = ko.observable(flha.supervisor_name);
        self.supervisor_number = ko.observable(flha.supervisor_number);
        self.safety_rep_name = ko.observable(flha.safety_rep_name);
        self.safety_rep_number = ko.observable(flha.safety_rep_number);
        self.gloves_removed_description = ko.observable(flha.gloves_removed_description);
        self.working_alone_description = ko.observable(flha.working_alone_description);
        self.warning_ribbon_description = ko.observable(flha.warning_ribbon_description);

        // Job Completions
        if (typeof flha.completion !== 'undefined' && flha.completion) {
        	self.permit_closed_description = ko.observable(flha.completion.permit_closed_description);
        	self.hazard_remaining_description = ko.observable(flha.completion.hazard_remaining_description);
        	self.flagging_removed_description = ko.observable(flha.completion.flagging_removed_description);
        	self.concerns_description = ko.observable(flha.completion.concerns_description);
        	self.incidents_reported_description = ko.observable(flha.completion.incidents_reported_description);
        	self.equipment_removed_description = ko.observable(flha.completion.equipment_removed_description);
        }
        
        // Arrays
        self.permitNumbers = ko.observableArray(permits);
        self.locations = ko.observableArray(locations);
        self.lsds = ko.observableArray(lsds);
        self.sites = ko.observableArray(sites);
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
                flha_permit_id:"",
                permit_number: "",
                flha_id: ""
            });
        };

        self.removePermitNumber = function(permit) {
            self.permitNumbers.remove(permit);
        };

        //LOCATION MANAGEMENT ==========================
        self.addLocation = function() {
            self.locations.push({
                flha_location_id: "",
                location: "",
                flha_id: ""
            });
        };

        self.removeLocation = function(location) {
            self.locations.remove(location);
        };

        //LSD MANAGEMENT ==========================
        self.addLSD = function() {
            self.lsds.push({
                flha_lsd_id: "",
                lsd: "",
                flha_id: ""
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

        // SITE MANAGEMENT =========================  
        self.addSite = function() {
            self.sites.push({
                flha_site_id: "",
                site: "",
                flha_id: ""
            });
        };

        self.removeSite = function(site) {
            self.sites.remove(site);
        }; 
    };

    // BIND TO VIEW
    var flhaObj = new FlhaModel(theFlha, permits, locations, lsds, sites, tasks);
    ko.applyBindings(flhaObj);

    $('#editFlhaForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $(".editFlhaButton").val('Saving...');
        $('.editFlhaButton').prop('disabled', true);
    
        // Here is where we could also JSON serialize the flha object.
        flhaObj.save();///$("#editFlhaForm").serialize();
        // Here is where we could also client side validate the object
        var formData = flhaObj.lastSavedJson();
        var returnMessage = '';

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'flha/edit-flha', formData, function(response){
            if(response.status){
                returnMessage = 'Save Successful.\n\nPlease refresh the page if you wish to view changes.';
            } else {
                returnMessage = 'Save Failed:\n\n';
                for (i = 0; i < response.errors.length; i++) { 
                    returnMessage += '- ' + response.errors[i] + '\n';
                }
            }

            alert(returnMessage);

            $(".editFlhaButton").val('Save Edits');
            $('.editFlhaButton').prop('disabled', false);
        },'json');

        return false;
    });
        
    
});

