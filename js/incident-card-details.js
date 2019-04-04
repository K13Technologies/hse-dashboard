$(document).ready(function () {
    
    $(".nivoSlider").nivoSlider({
        directionNav: true,         // Next & Prev navigation
        controlNav: false,          // 1,2,3... navigation
        manualAdvance: true,        // Force manual transitions
        prevText: '',               // Prev directionNav text
        nextText: '',               // Next directionNav text
        effect:'slideInRight'
    });
        
    $('.see-person-link').click(function(){
        var modal = '#modalWorker';
        var personId = $(this).prop('id').replace('see_person_','');
        $.get(site+'incident-cards/get-person-details/'+personId, function(response){
            if (response.status){
                switch (response.data.type){
                    case '0': 
                        populateWorkerModal(response.data);
                        $(workerModal).modal('show');
                        break;
                    case '1': 
                        populateWitnessModal(response.data);
                        $(witnessModal).modal('show');
                        break;
                    case '2': 
                        populateThirdPartyModal(response.data);
                        $(thirdPartyModal).modal('show');
                        break;
                    default:
                        break;
                }
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
           
    $(".action-date-picker").datepicker({
        dateFormat:"yy-mm-dd",
        showOtherMonths: true,
        selectOtherMonths: true,
        onClose:function(dateText,formInput){
            setAction(this);
        }
    }); 

    $('#selectHelpButton').click(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        alert('Please choose one or more items. To choose more than one, hold the CTRL key (Windows) or APPLE key (Mac) before clicking each new item.');
    });
    
    var Treatment = function (data) {
        var self = this;
        self.incident_treatment_id = ko.observable('');
        self.first_aid = ko.observable('');
        self.medical_aid = ko.observable('');
        self.responder_name = ko.observable('');
        self.responder_company = ko.observable('');
        self.responder_phone_number = ko.observable('');
        self.comment = ko.observable('');
        self.injuries = ko.observable('');
        self.incident_id = ko.observable('');
        self.incident_type_id = ko.observable('');
        self.type = ko.observable('');
        self.parts = ko.observableArray([]);
        this.treatmentPartsList = ko.observableArray([]); // Used for dropdown lists
         
        if (typeof data !== 'undefined' && data) { 
            self.incident_treatment_id = ko.observable(data.incident_treatment_id);
            self.first_aid = ko.observable(data.first_aid);
            self.medical_aid = ko.observable(data.medical_aid);
            self.responder_name = ko.observable(data.responder_name);
            self.responder_company = ko.observable(data.responder_company);
            self.responder_phone_number = ko.observable(data.responder_phone_number);
            self.comment = ko.observable(data.comment);
            self.injuries = ko.observable(data.injuries);
            self.incident_id = ko.observable(data.incident_id);
            self.incident_type_id = ko.observable(data.incident_type_id);
            self.type = ko.observable(data.type);
            
            // If there are original persons passed in, create them
            if (typeof data.parts !== 'undefined' && data.parts) {
                $.each(data.parts, function(i, el) {
                    self.parts.push(new IncidentObjectPart(el));
                });
            }
        }

        // If the treatmentPartsList was loaded properly
        if (typeof treatmentPartsList !== 'undefined' && treatmentPartsList) {
            $.each(treatmentPartsList, function(i, el) {
                self.treatmentPartsList.push(el);
            });
        }
        
        self.firstAidWasGiven = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.first_aid() > 0;
            },
            write: function(firstAidWasGiven) {
                // Going from NO to YES
                if (firstAidWasGiven && self.first_aid() < 1) {
                    self.first_aid(1);
                    return self.firstAidWasGiven.notifySubscribers();
                }
                else {
                    self.first_aid(0);
                    return self.firstAidWasGiven.notifySubscribers();
                }
            }
        }, this);
        
        self.medicalAidWasGiven = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.medical_aid() > 0;
            },
            write: function(medicalAidWasGiven) {
                // Going from NO to YES
                if (medicalAidWasGiven && self.medical_aid() < 1) {
                    self.medical_aid(1);
                    return self.medicalAidWasGiven.notifySubscribers();
                }
                else {
                    self.medical_aid(0);
                    self.tdg_material('');
                    return self.medicalAidWasGiven.notifySubscribers();
                }
            }
        }, this);
        
        self.thereWasBodilyInjury = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.injuries() > 0;
            },
            write: function(thereWasBodilyInjury) {
                if (!thereWasBodilyInjury && self.injuries() == 1) {
                    if (!confirm("Remove all bodily injuries?"))
                        return self.thereWasBodilyInjury.notifySubscribers();
                    else
                        //Remove all parts
                        self.parts([]);
                        self.injuries(0);
                }

                // NO to YES
                if (thereWasBodilyInjury && self.injuries() == 0) {
                    self.injuries(1);
                    self.addBodyPart();
                }
            }
        }, this);

        // ===========================================================================

        /*self.filteredBodyPartList = ko.computed({
            read: function() {

                var theFilteredList = ko.utils.arrayFilter(self.treatmentPartsList, function(part) { 
                    var match = ko.utils.arrayFirst(self.selectedIncidentSchemaPartIds, function(selectedID) {
                        alert(selectedID);
                        //return part.incident_schema_part_id === selectedID;
                    });

                    return true;
                });

                alert(JSON.stringify(theFilteredList, null, 4));
                return theFilteredList;
            }
        }, this);


        self.selectedIncidentSchemaPartIds = ko.observableArray([]);
        self.currentlySelectedBodyParts = ko.computed({
            read: function() {
                // Clear the array
                self.selectedIncidentSchemaPartIds([]);
                // Scan through selected parts and add ids to array
                $.each(self.parts(), function(i, el) {
                    self.selectedIncidentSchemaPartIds.push(el.incident_schema_part_id());
                });

                return self.selectedIncidentSchemaPartIds;
            }
        }, this);

        /*
        self.unselectedIncidentSchemaParts = ko.observableArray([]);
        self.currentlyAvailableBodyParts = ko.computed({
            read: function() {
                // Set the array to the full list
                self.unselectedIncidentSchemaParts(treatmentPartsList);

                ko.utils.arrayForEach(self.selectedIncidentSchemaPartIds, function(selectedPartID) {
                    ko.utils.arrayForEach(self.unselectedIncidentSchemaParts, function(unselectedPart) {
                        if(unselectedPart.incident_schema_part_id === selectedPartID) {
                            self.unselectedIncidentSchemaParts.remove(unselectedPart);
                        } 
                    });

                });

                return self.unselectedIncidentSchemaParts;
            }
        }, this);*/

        // ===========================================================================

        self.addBodyPart = function() {
            self.parts.push(new IncidentObjectPart(null));
        };

        self.removeBodyPart = function (bodyPart) {
            self.parts.remove(bodyPart);
        }; 
    }
    
    var IncidentObjectPart = function (data) {
        var self = this;
        this.part_statement_id = ko.observable('');
        this.incident_id = ko.observable('');
        this.incident_schema_part_id = ko.observable('');
        this.comment = ko.observable('');
        this.statementable_id = ko.observable('');
        this.statementable_type = ko.observable('');
        this.photoIds = ko.observableArray([]);
    
        if (typeof data !== 'undefined' && data) {
            this.part_statement_id = ko.observable(data.part_statement_id);
            this.incident_id = ko.observable(data.incident_id);
            this.incident_schema_part_id = ko.observable(data.incident_schema_part_id);
            this.comment = ko.observable(data.comment);
            this.statementable_id = ko.observable(data.statementable_id);
            this.statementable_type = ko.observable(data.statementable_type);
            
            // If there are original photos passed in, create them
            if (typeof data.photoIds !== 'undefined' && data.photoIds) {
                $.each(data.photoIds, function(i, el) {
                    self.photoIds.push(new PartPhoto(el));
                });
            }
        }
    }
    
    // This class is intended to be used within IncidentObjectParts so that URLS can be more elegantly generated within the view accross environments
    var PartPhoto = function (data) {
        var self = this;
        this.id = ko.observable('');
        self.photoURL = ko.computed({
            read: function() {
                return site + 'image/' +this.id(); //site is defined in parent JS files
            }
        }, this);
        
        if (typeof data !== 'undefined' && data) {
            this.id(data);   
        }
    }
    
    var MotorVehicleDamage = function (data) {
        var self = this;
        
        // Constants
        var PICKUP_TRUCK_VEHICLE_TYPE = 0;
        var TRACTOR_TRAILER_VEHICLE_TYPE = 1;

        self.incident_mvd_id = ko.observable(data.incident_mvd_id);
        self.driver_license_number = ko.observable(data.driver_license_number);
        self.insurance_company = ko.observable(data.insurance_company);
        self.insurance_policy_number = ko.observable(data.insurance_policy_number);

        // Policy expiry time
            self.policyExpiryDateTime = ko.observable(''); // Helper value
            self.policyExpiryTimeZone = ko.observable(''); // Helper value
            // Value which will actually be passed back to the DB
            self.policy_expiry_date = ko.computed({
                read: function() {
                    return self.policyExpiryDateTime() + " " + self.policyExpiryTimeZone();
                }
            }, this);

            var splitPolicyExiryDate = data.policy_expiry_date.split(" ");
            self.policyExpiryDateTime(splitPolicyExiryDate[0] + " " + splitPolicyExiryDate[1]);
            self.policyExpiryTimeZone(splitPolicyExiryDate[2]);
        // End Policy Expiry time


        self.vehicle_year = ko.observable(data.vehicle_year);
        self.make = ko.observable(data.make);
        self.model = ko.observable(data.model);
        self.vin = ko.observable(data.vin);
        self.color = ko.observable(data.color);
        self.license_plate = ko.observable(data.license_plate);


        // MVD Time
            self.timeOfMvdDateTime = ko.observable(''); // Helper value
            self.timeOfMvdTimeZone = ko.observable(''); // Helper value
            // Value which will actually be passed back to the DB
            self.time_of_incident = ko.computed({
                read: function() {
                    return self.timeOfMvdDateTime() + " " + self.timeOfMvdTimeZone();
                }
            }, this);

            var splitTimeOfMvd = data.time_of_incident.split(" ");
            self.timeOfMvdDateTime(splitTimeOfMvd[0] + " " + splitTimeOfMvd[1]);
            self.timeOfMvdTimeZone(splitTimeOfMvd[2]);
        // End MVD time


        self.tdg = ko.observable(data.tdg);
        self.tdg_material = ko.observable(data.tdg_material);
        self.thereIsTDG = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.tdg() > 0;
            },
            write: function(thereIsTDG) {
                // Going from NO to YES
                if (thereIsTDG && self.tdg() < 1) {
                    self.tdg(1);
                    return self.thereIsTDG.notifySubscribers();
                }
                else {
                    self.tdg(0);
                    self.tdg_material('');
                    return self.thereIsTDG.notifySubscribers();
                }
            }
        }, this);
        self.wearing_seatbelts = ko.observable(data.wearing_seatbelts);
        self.wearing_seatbelts_description = ko.observable(data.wearing_seatbelts_description);
        self.wearingSeatbelts = ko.computed({
            read: function() {
                return self.wearing_seatbelts() == 1;
            },
            write: function(wearingSeatbelts) {
                // Going from NO to YES
                if (wearingSeatbelts && self.wearing_seatbelts() < 1) {
                    self.wearing_seatbelts(1);
                    self.wearing_seatbelts_description('');
                    return self.wearingSeatbelts.notifySubscribers();
                }
                else {
                    self.wearing_seatbelts(0);
                    return self.wearingSeatbelts.notifySubscribers();
                }
            }
        }, this);
        self.airbags_deployed = ko.observable(data.airbags_deployed);
        
        // POLICE INVOLVEMENT FIELDS
        self.damage_exceeds_amount = ko.observable(data.damage_exceeds_amount);
        self.damageExceedsAmount = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.damage_exceeds_amount() > 0;
            },
            write: function(damageExceedsAmount) {
                // Going from NO to YES
                if (damageExceedsAmount && self.damage_exceeds_amount() < 1) {
                    self.damage_exceeds_amount(1);
                    return self.damageExceedsAmount.notifySubscribers();
                }
                else {
                    self.damage_exceeds_amount(0);
                    //reset fields
                    self.police_file_number('');
                    self.attending_police_officer(0);
                    self.police_service('');
                    self.police_name('');
                    self.police_badge_number('');
                    self.police_business_phone_number('');
                    return self.damageExceedsAmount.notifySubscribers();
                }
            }
        }, this);
        self.police_file_number = ko.observable(data.police_file_number);
        self.attending_police_officer = ko.observable(data.attending_police_officer); 
        self.police_service = ko.observable(data.police_service);
        self.police_name = ko.observable(data.police_name);
        self.police_badge_number = ko.observable(data.police_badge_number);
        self.police_business_phone_number = ko.observable(data.police_business_phone_number);
        self.thereWasAnAttendingPoliceOfficer = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.attending_police_officer() > 0;
            },
            write: function(thereWasAnAttendingPoliceOfficer) {
                // Going from NO to YES
                if (thereWasAnAttendingPoliceOfficer && self.attending_police_officer() < 1) {
                    self.attending_police_officer(1);
                    return self.thereWasAnAttendingPoliceOfficer.notifySubscribers();
                }
                else {
                    self.attending_police_officer(0);
                    //reset fields
                    self.police_service('');
                    self.police_name('');
                    self.police_badge_number('');
                    self.police_business_phone_number('');
                    return self.thereWasAnAttendingPoliceOfficer.notifySubscribers();
                }
            }
        }, this);
        
        // END POLICE INVOLVEMENT FIELDS
        
        // Tow fields
        self.vehicle_towed = ko.observable(data.vehicle_towed);
        self.tow_company = ko.observable(data.tow_company);
        self.tow_driver_name = ko.observable(data.tow_driver_name);
        self.tow_business_phone_number = ko.observable(data.tow_business_phone_number);
        self.tow_address = ko.observable(data.tow_address);
        self.vehicleWasTowed = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.vehicle_towed() > 0;
            },
            write: function(vehicleWasTowed) {
                // Going from NO to YES
                if (vehicleWasTowed && self.vehicle_towed() < 1) {
                    self.vehicle_towed(1);
                    return self.vehicleWasTowed.notifySubscribers();
                }
                else {
                    self.vehicle_towed(0);
                    //reset fields
                    self.tow_company('');
                    self.tow_driver_name('');
                    self.tow_business_phone_number('');
                    self.tow_address('');
                    return self.vehicleWasTowed.notifySubscribers();
                }
            }
        }, this);
        
        self.other_passengers = ko.observable(data.other_passengers);
        self.other_passengers_details = ko.observable(data.other_passengers_details);
        self.otherPassengersInvolved = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.other_passengers() > 0;
            },
            write: function(otherPassengersInvolved) {
                // Going from NO to YES
                if (otherPassengersInvolved && self.other_passengers() < 1) {
                    self.other_passengers(1);
                    return self.otherPassengersInvolved.notifySubscribers();
                }
                else {
                    self.other_passengers(0);
                    self.other_passengers_details('');
                    return self.otherPassengersInvolved.notifySubscribers();
                }
            }
        }, this);
        
        self.vehicles_involved = ko.observable(data.vehicles_involved);    
        self.my_vehicle_damaged = ko.observable(data.my_vehicle_damaged);
        self.vehicleWasDamaged = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.my_vehicle_damaged() > 0;
            },
            write: function(vehicleWasDamaged) {
                // Going from NO to YES
                if (vehicleWasDamaged && self.my_vehicle_damaged() < 1) {
                    self.my_vehicle_damaged(1);
                    return self.vehicleWasDamaged.notifySubscribers();
                }
                else {
                    self.my_vehicle_damaged(0);
                    return self.vehicleWasDamaged.notifySubscribers();
                }
            }
        }, this);
        
        // Comment for the damaged vehicle in general
        self.comment = ko.observable(data.comment);
        self.vehicleType = ko.observable(data.vehicleType); 
        self.incident_id = ko.observable(data.incident_id);
        self.incident_type_id = ko.observable(data.incident_type_id);
        self.type = ko.observable(data.type); //"type":{"incident_type_id":"8", "type_name":"Motor Vehicle"},
        
        self.parts = ko.observableArray([]);
        // If there are original parts passed in, create them
        if (typeof data.parts !== 'undefined' && data.parts) {
            $.each(data.parts, function(i, el) {
                self.parts.push(new IncidentObjectPart(el));
            });
        }        

        // For the mvd in general -- not the parts, which also contain these kinds of fields
        //self.photoIds = ko.observableArray(data.photoIds);
        //self.photos = ko.observableArray(data.photos); 
        
        this.truckPartsList = ko.observableArray([]);
        this.trailerPartsList = ko.observableArray([]);

        // If the truckPartsList was loaded properly
        if (typeof truckPartsList !== 'undefined' && truckPartsList) {
            $.each(truckPartsList, function(i, el) {
                self.truckPartsList.push(el);
            });
        }
        
        // If the trailerPartsList was loaded properly
        if (typeof trailerPartsList !== 'undefined' && trailerPartsList) {
            $.each(trailerPartsList, function(i, el) {
                self.trailerPartsList.push(el);
            });
        }


        // MVD PHOTOS
        self.photos = ko.observableArray([]);

        // mvdPhotos is defined in the view.
        if (typeof mvdPhotos !== 'undefined' && mvdPhotos) {
            var mvdPhotoSet = ko.utils.arrayFirst(mvdPhotos, function(photoContainer) {
                return self.incident_mvd_id() == photoContainer.incident_mvd_id;
            });

            if (mvdPhotoSet) {
                $.each(mvdPhotoSet.photoIds, function(i, el) {
                    self.photos.push(new PartPhoto(el));
                });
            }
        }

        // For dropdown lists 
        self.partsListForSelectedVehicleType = ko.computed({
            read: function() {
                var list;

                if (self.vehicleType() == PICKUP_TRUCK_VEHICLE_TYPE) {
                    list = truckPartsList;
                }
                else if (self.vehicleType() == TRACTOR_TRAILER_VEHICLE_TYPE){
                    list = trailerPartsList;
                }

                return list;
            }
        }, this);

        // FUNCTIONS
        self.addVehiclePart = function() {
            self.parts.push(new IncidentObjectPart(null));
        };

        self.removeVehiclePart = function (vehiclePart) {
            self.parts.remove(vehiclePart);
        };
    }
    
    var ReleaseSpill = function (data) {
        var self = this;
        self.incident_release_spill_id = ko.observable(data.incident_release_spill_id);
        self.commodity = ko.observable(data.commodity);
        self.release_source = ko.observable(data.release_source);
        self.release_to = ko.observable(data.release_to);
        self.quantity_released = ko.observable(data.quantity_released);
        self.quantity_released_unit = ko.observable(data.quantity_released_unit);
        self.quantity_recovered = ko.observable(data.quantity_recovered);
        self.quantity_recovered_unit = ko.observable(data.quantity_recovered_unit);
        self.comment = ko.observable(data.comment);
        self.incident_id = ko.observable(data.incident_id);
        self.potential_exposure = ko.observable(data.potential_exposure);
        self.potentialExposureToHazMat = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.potential_exposure() > 0;
            },
            write: function(potentialExposureToHazMat) {
                // Going from NO to YES
                if (potentialExposureToHazMat && self.potential_exposure() < 1) {
                    self.potential_exposure(1);
                }
                else {
                    self.potential_exposure(0);
                }
            }
        }, this);

        self.releaseUOMTypes = ko.observableArray([]);
        // If the releaseUOMTypeList (defined in view) was loaded properly
        if (typeof releaseUOMTypeList !== 'undefined' && releaseUOMTypeList) {
            self.releaseUOMTypes(releaseUOMTypeList);
            
            /*$.each(releaseUOMTypeList, function(i, el) {
                self.releaseUOMTypes.push(el);
            });*/

        }
    };

    var releaseUOMType = function (name) {
        var self = this;
        self.uomID = ko.observable(1);
        self.uomName = ko.observable(name);

    }

    // Constants used for various types of people related to an incident
    var WORKER_TYPE = 0;
    var WITNESS_TYPE = 1;
    var THIRD_PARTY_TYPE = 2;

    var Person = function(data, type){
        var self = this;

        self.incident_person_id = ko.observable('');
        self.first_name = ko.observable('');
        self.last_name = ko.observable('');
        self.phone_number = ko.observable('');
        self.company = ko.observable('');

        // Time on shift properties
        self.timeOnShiftDateTime = ko.observable('');
        self.timeOnShiftTimeZone = ko.observable('');
        self.time_on_shift = ko.computed({
            read: function() {
                return self.timeOnShiftDateTime() + " " + self.timeOnShiftTimeZone();
            }
        }, this);
        self.ts_on_shift = ko.observable(''); //Unix UTC timestamp

        // Time of incident properties
        self.timeOfIncidentDateTime = ko.observable('');
        self.timeOfIncidentTimeZone = ko.observable('');
        self.time_of_incident = ko.computed({
            read: function() {
                return self.timeOfIncidentDateTime() + " " + self.timeOfIncidentTimeZone();
            }
        }, this);
        self.ts_of_incident = ko.observable(''); //Unix UTC timestamp

        self.statement = ko.observable('');   
        self.incident_id = ko.observable('');
        // Used for dropdown list
        self.employmentTypes = ko.observableArray([{label: 'Employee', typeValue: 0 }, {label: 'Sub Contractor ', typeValue: 1 }, {label: 'Prime Contractor', typeValue: 2 }]);
        self.witnessTypes = ko.observableArray([{label: 'Employee', typeValue: 0 }, {label: 'Sub Contractor ', typeValue: 1 }, {label: 'Prime Contractor', typeValue: 2 }, {label: 'Member of the Public', typeValue: 3}]);

        // Variables which determine what type of person it is
        self.type = ko.observable('');
        self.employment_status = ko.observable('');
        
        if (typeof data !== 'undefined' && data) {
            self.incident_person_id = ko.observable(data.incident_person_id);
            self.first_name = ko.observable(data.first_name);
            self.last_name = ko.observable(data.last_name);
            self.phone_number = ko.observable(data.phone_number);
            self.company = ko.observable(data.company);
            
            // Time on shift
            var splitTimeOnShift = data.time_on_shift.split(" ");
            self.timeOnShiftDateTime(splitTimeOnShift[0] + " " + splitTimeOnShift[1]);
            self.timeOnShiftTimeZone(splitTimeOnShift[2]);
            self.ts_on_shift = ko.observable(data.ts_on_shift);
            
            // Time of incident
            var splitTimeOfIncident = data.time_of_incident.split(" ");
            self.timeOfIncidentDateTime(splitTimeOfIncident[0] + " " + splitTimeOfIncident[1]);
            self.timeOfIncidentTimeZone(splitTimeOfIncident[2]);
            self.ts_of_incident = ko.observable(data.ts_of_incident);
            
            self.statement = ko.observable(data.statement);  
            self.incident_id = ko.observable(data.incident_id);
            self.type = ko.observable(data.type);
            self.employment_status = ko.observable(data.employment_status);
        }

        // When creating a new object we need to be able to specify the type of person it is
        if (typeof type !== 'undefined' && type) {
            self.type = ko.observable(type);
        } 

        // Functions
            //debugger;
        self.isWorker = function () { return self.type() == WORKER_TYPE; };
        self.isWitness = function () { return self.type() == WITNESS_TYPE; };
        self.isThirdParty = function () { return self.type() == THIRD_PARTY_TYPE; }; 
    };
    // END MODULAR CLASSES ============================

    var incidentModel = function (incident, chosenActivityIds, persons, releaseSpill, mvds, treatments) {
        var self = this;

        // Simple attribute declarations 
        self.incident_id = ko.observable(incident.incident_id);
        self.title = ko.observable(incident.title);
        self.lsd = ko.observable(incident.lsd);
        self.utm = ko.observable(incident.utm);
        self.source_receiver_line = ko.observable(incident.source_receiver_line);
        self.latitude = ko.observable(incident.latitude);
        self.longitude = ko.observable(incident.longitude);
        self.location = ko.observable(incident.location);
        self.specific_area = ko.observable(incident.specific_area);
        self.road = ko.observable(incident.road);
        self.description = ko.observable(incident.description);
        self.root_cause = ko.observable(incident.root_cause);
        self.immediate_action = ko.observable(incident.immediate_action);
        self.releaseSpill = ko.observable(null);
        
        // Corrective Action fields
        self.corrective_action = ko.observable(incident.corrective_action);
        self.corrective_action_applied = ko.observable(incident.corrective_action_applied);
        self.corrective_action_implementation = ko.observable(incident.corrective_action_implementation);
        self.completed_on = ko.observable(incident.completed_on);
        self.action = ko.observable(incident.action);
        self.correctiveActionWasApplied = ko.computed({
            read: function() {
                // Checking if set to 1 (true)
                return self.corrective_action_applied() > 0;
            },
            write: function(correctiveActionWasApplied) {
                // Going from NO to YES
                if (correctiveActionWasApplied && self.corrective_action_applied() < 1) {
                    // Reset the reason
                    self.corrective_action_applied(1);
                    return self.correctiveActionWasApplied.notifySubscribers();
                }
                else {
                    self.corrective_action_applied(0);
                    return self.correctiveActionWasApplied.notifySubscribers();
                }
            }
        }, this);
        
        // Arrays
        self.persons = ko.observableArray([]);
        self.incidentActivityIds = ko.observableArray(chosenActivityIds);
        self.mvds = ko.observableArray([]);
        self.treatments = ko.observableArray([]);

        // Used for serializing the object before sending to the controller
        self.lastSavedJson = ko.observable('');
        self.save = function() {
            self.lastSavedJson(JSON.stringify(ko.toJS(self), null, 2));
        };
        
        // If there are original persons passed in, create them
        if (typeof persons !== 'undefined' && persons) {
            $.each(persons, function(i, el) {
                self.persons.push(new Person(el, null));
            });
        }
        
        // If there was a release or spill
        if (typeof releaseSpill !== 'undefined' && releaseSpill) {
            // Create the release object with passed-in data
            self.releaseSpill(new ReleaseSpill(releaseSpill));
        }
        
        // If there are original mvds passed in, create them
        if (typeof mvds !== 'undefined' && mvds) {
            $.each(mvds, function(i, el) {
                self.mvds.push(new MotorVehicleDamage(el));
            });
        }
        
        // If there are original treatments passed in, create them
        if (typeof treatments !== 'undefined' && treatments) {
            $.each(treatments, function(i, el) {
                self.treatments.push(new Treatment(el));
            });
        }

        // WORKER MANAGEMENT ========================
        self.addWorker = function() {
            var type = WORKER_TYPE;
            self.persons.push(new Person(null, type));
        };

        self.removeWorker = function (person) {
            self.persons.remove(person);
        }; 
        self.numWorkers = function() {
            var count = 0;
            $.each(self.persons(), function(i, el) {
                if(el.type() == 0) {
                    count ++;
                }
            });
            return count;
        };

        self.removeAllThirdParties = function () {
            self.persons.remove(function(person) { return person.type() == THIRD_PARTY_TYPE });
        }

        // WITNESS MANAGEMENT ========================
        self.addWitness = function() {
            var type = WITNESS_TYPE;
            self.persons.push(new Person(null, type));
        };

        self.removeWitness = function (person) {
            self.persons.remove(person);
        }; 
        self.numWitnesses = function() {
            var count = 0;
            $.each(self.persons(), function(i, el) {
                if(el.type() == WITNESS_TYPE) {
                    count ++;
                }
            });
            return count;
        };

        self.removeAllWitnesses = function () {
            self.persons.remove(function(person) { return person.type() == WITNESS_TYPE });
        }

        self.wereThereAnyWitnesses = ko.computed({
            read: function() {
                return self.numWitnesses() > 0;
            },
            write: function(wereThereAnyWitnesses) {
                if (!wereThereAnyWitnesses && self.numWitnesses() > 0) {
                    if (!confirm("Remove all current witnesses?"))
                        return self.wereThereAnyWitnesses.notifySubscribers();
                    else
                        self.removeAllWitnesses();
                }

                if (wereThereAnyWitnesses && self.numWitnesses() == 0)
                    self.addWitness();
                }
        }, this);

        // THIRD PARTY MANAGEMENT ========================
        self.addThirdParty = function() {
            var type = THIRD_PARTY_TYPE;
            self.persons.push(new Person(null, type));
        };

        self.removeThirdParty = function (person) {
            self.persons.remove(person);
        }; 
        self.numThirdParties = function() {
            var count = 0;
            $.each(self.persons(), function(i, el) {
                if(el.type() == THIRD_PARTY_TYPE) {
                    count ++;
                }
            });
            return count;
        };

        self.wereThereAnyThirdParties = ko.computed({
            read: function() {
                return self.numThirdParties() > 0;
            },
            write: function(wereThereAnyThirdParties) {
                if (!wereThereAnyThirdParties && self.numThirdParties() > 0) {
                    if (!confirm("Remove all current third parties?"))
                        return self.wereThereAnyThirdParties.notifySubscribers();
                    else
                        self.removeAllThirdParties();
                }

                if (wereThereAnyThirdParties && self.numThirdParties() == 0)
                    self.addThirdParty();
                }
        }, this);

        self.removeAllThirdParties = function () {
            self.persons.remove(function(person) { return person.type() == THIRD_PARTY_TYPE });
        }
        
        // CUSTOM DATETIMEPICKER BINDING
        ko.bindingHandlers.dateTimePicker = {
        init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {

            var options = ko.unwrap(valueAccessor());
            var valueObservable = allBindings.get('value');

            var defaults = {
                //defaultDate: valueObservable(), //can't set default here because parsing fails for time strings (ex:  "09:15 AM" throws error)
                format: 'YYYY-MM-DD HH:mm',
                minDate: '01/01/1986',
                stepping: 1,
                sideBySide: true,
                widgetPositioning: {
                    horizontal: 'auto',
                    vertical: 'auto'
                },
                useStrict: true,
                icons : {
                    time: 'glyphicon glyphicon-time',
                    date: 'glyphicon glyphicon-calendar',
                    up: 'glyphicon glyphicon-chevron-up',
                    down: 'glyphicon glyphicon-chevron-down',
                    previous: 'glyphicon glyphicon-chevron-left',
                    next: 'glyphicon glyphicon-chevron-right',
                    today: 'glyphicon glyphicon-screenshot',
                    clear: 'glyphicon glyphicon-trash'
                }
            };

            var config = ko.utils.extend(defaults, options); //override defaults with passed in options
            var $pickerElement = $(element).datetimepicker(config);

            $pickerElement.data("DateTimePicker").date(valueObservable()); //force initial value to be whatever is in the observable

            $pickerElement.bind('dp.change', function (eventObj) {
                var picker = $(element).data("DateTimePicker");
                if (picker) {
                    var date = picker.date();
                    var formattedDate = date ? date.format(picker.format()) : "";
                    if (formattedDate != valueObservable()) {
                        valueObservable(formattedDate);
                    }
                }
            });

            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                var picker = $(element).data("DateTimePicker");
                if (picker) {
                    picker.destroy();
                }
            });

        },
        update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {

            var picker = $(element).data("DateTimePicker");
            if (picker) {
                var valueObservable = allBindings.get('value');
                var date = picker.date();
                var formattedDate = date ? date.format(picker.format()) : "";
                if (formattedDate != valueObservable()) {
                    picker.date(valueObservable());
                }
            }
            }
        };// end of datetimepicker declaration


        ko.bindingHandlers.checkboxpicker =
        {
            init: function(element, valueAccessor){
                var val = valueAccessor();
                $(element).checkboxpicker();
                $(element).checkboxpicker().prop('checked', val()).change(function() {
                    val(this.checked);
                });
            },
            update: function(element, valueAccessor) {
                var val = valueAccessor();
                $(element).prop('checked', val());
            }
        };


    }; // end incidentModel declaration

    // BIND TO VIEW
    var incidentObj = new incidentModel(theIncident, chosenActivityIds, persons, releaseSpill, mvds, treatments);
    ko.applyBindings(incidentObj);


    $('#editIncidentForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $(".editIncidentButton").val('Saving...');
        $('.editIncidentButton').prop('disabled', true);
    
        incidentObj.save();
        var formData = incidentObj.lastSavedJson();
        var returnMessage = '';

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'incident-cards/edit-incident', formData, function(response){
            if(response.status){
                returnMessage = 'Save Successful.\n\nPlease refresh the page if you wish to view changes.';
            } else {
                returnMessage = 'Save Failed:\n\n';
                for (i = 0; i < response.errors.length; i++) { 
                    returnMessage += '- ' + response.errors[i] + '\n';
                }
            }

            alert(returnMessage);

            $(".editIncidentButton").val('Save Edits');
            $('.editIncidentButton').prop('disabled', false);
        },'json');

        return false;
    });

    // RESPONSIVE TEXT AREAS
        function h(e) {
            $(e).css({'height':'auto','overflow-y':'hidden'}).height(e.scrollHeight);
        }

        $('textarea').each(function () {
          h(this);

        }).on('input', function () {
          h(this);
        });
    // END RESPONSIBLE TEXTAREAS
});



