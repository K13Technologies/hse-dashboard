$(document).ready(function () {

    $("#completeExportToMail").click(function(){
        var formData = $("#mailExportForm").serialize();
        var modal = '#sendEmailModal';
        $("#addError").empty();
        $('#sendEmailModal .help-inline').remove();
        $('#sendEmailModal .control-group').removeClass('error');
        validateForm();

        if($("#mailExportForm").valid()){
            // Having these style items here ensures that if the user clicks SEND with no emails, the button doesn't change
            $("#completeExportToMail").html('Sending... <img src="' + site + 'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
            $('#completeExportToMail').prop('disabled', true);

            $.post(site+'tickets/mail', formData, function(response){
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

    //triggered when modal is about to be shown
    $('#sendEmailModal').on('show.bs.modal', function(e) {

        //get data-id attribute of the clicked element
        var ticketId = $(e.relatedTarget).data('ticket-id');

        //populate the textbox
        $(e.currentTarget).find('input[name="ticketId"]').val(ticketId);
    });
   
    var TicketPhoto = function (data) {
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

    var TicketReview = function(data) {
        var self = this;
        self.form_review_id = ko.observable('');
        self.reviewer_name = ko.observable('');
        self.created_at = ko.observable('');
        self.ts = ko.observable('');

        if (typeof data !== 'undefined' && data) {
            self.form_review_id = ko.observable(data.form_review_id);
            self.ts = ko.observable(data.ts);
            self.created_at = ko.observable(data.created_at);
            self.reviewer_name = ko.observable(data.reviewer_name);  
        }

        self.signatureURL = ko.computed({
            read: function() {
                return site + 'image/review/signature/' + self.form_review_id(); 
            }
        }, this);
    }

    var Ticket = function (data) {
        var self = this;
        self.ticket_id = ko.observable('');
        self.type_name = ko.observable('');
        self.issued_internally = ko.observable(''); // assume it isn't
        self.issuer_organization_name = ko.observable('');
        self.expiry_date = ko.observable('');
        self.description = ko.observable('');
        self.managementReview = ko.observable(null);
        self.photos = ko.observableArray([]);

        self.ticketDetailsAreVisible = ko.observable(false);
        self.ticketDescriptionIsVisible = ko.observable(false);

        if (typeof data !== 'undefined' && data) {
            self.ticket_id = ko.observable(data.ticket_id);
            self.type_name = ko.observable(data.type_name);
            self.issued_internally = ko.observable(data.issued_internally);
            self.issuer_organization_name = ko.observable(data.issuer_organization_name);
            self.expiry_date = ko.observable(data.expiry_date);
            self.description = ko.observable(data.description);

            // If there are original photos passed in, create them
            if (typeof data.photoIds !== 'undefined' && data.photoIds) {
                $.each(data.photoIds, function(i, el) {
                    self.photos.push(new TicketPhoto(el));
                });
            }

            if (typeof data.review !== 'undefined' && data.review && data.review !== null) {
                self.managementReview = new TicketReview(data.review);
            }
        }

        self.lengthRestrictedTicketName = ko.computed(function() {
            if(!self.ticketDetailsAreVisible()){
                if(self.type_name().length >= 70){
                    var shortenedString = self.type_name().substring(0, 63) + '...';

                    return shortenedString;
                }
            }

            return self.type_name();
  
        }, self);

        self.showTicketDescription = function () { 
            self.ticketDescriptionIsVisible(!self.ticketDescriptionIsVisible());
        };

        self.descriptionButtonText = function () {
            if(self.ticketDescriptionIsVisible()){
                return 'Hide Description';
            }
            else {
                return 'Show Description';
            }
        }

        self.showTicketDetails = function () { 
            self.ticketDetailsAreVisible(!self.ticketDetailsAreVisible());
        };

        self.showFullDescription = function () { 
            self.ticketDescriptionIsVisible(!self.ticketDescriptionIsVisible());
        };

        self.infoButtonText = function () {
            if(self.ticketDetailsAreVisible()){
                return 'Hide Details';
            }
            else {
                return 'Show Details';
            }
        }

        self.descriptionButtonText = function () {
            if(self.ticketDescriptionIsVisible()){
                return 'Hide Description';
            }
            else {
                return 'Show Full Description';
            }
        }

        self.ticketPanelClasses = function() {
            var urgency = self.expirationUrgencyValue();
            var classNames = '';

                 if (urgency == 0) { classNames = 'panel panel-success panelSuccessCustom'; } 
            else if (urgency == 1) { classNames = 'panel panel-info panelInfoCustom'; } 
            else if (urgency == 2) { classNames = 'panel panel-warning panelWarningCustom'; } 
            else if (urgency == 3) { classNames = 'panel panel-danger panelDangerCustom'; } 
            else {
                // urgency == 3 .. or an error if something went wrong in the preceding functions
                classNames = 'panel panel-danger panel-expired';
            }

            if(self.ticketDetailsAreVisible()){
                classNames += ' expandedTicket';
            }
            else {
                classNames += ' contractedTicket';
            }

            return classNames;
        }

        self.expirationUrgencyValue =  function() {
            var expiresInDays = self.expiresInThisManyDays();
            var expirationUrgency = 0;

            if (expiresInDays > 60) { 
                expirationUrgency = 0; 
            } else
            if (expiresInDays > 29 && expiresInDays < 61) { 
                expirationUrgency = 1; 
            } else
            if (expiresInDays > 13 && expiresInDays < 31) { 
                expirationUrgency = 2; 
            } else 
            if (expiresInDays < 14 && expiresInDays >= 0) {
                expirationUrgency = 3; 
            }
            else {
                // -1 or less
                expirationUrgency = 4; // EXPIRED
            }

            return expirationUrgency;
        }

        self.expiresInThisManyDays = function () {
            var readableString = self.expiry_date().substr(0, self.expiry_date().indexOf(' ')); //gets the YYYY-MM-DD
            var dateItems = readableString.split("-");
            // months are indexed from 0 in js, hence the need to subtract one from the month
            var expiryDate = new Date(dateItems[0], dateItems[1] - 1 , dateItems[2]);
            var currentDate = new Date();

            // Gets the difference in days... and is subject to errors on some edge cases. Adding + 1 at end to make it human readable.
            // Be careful using Math.floor(). Days can have fewer than 24 hours.
            // With daylight savings a day can be between 23-25 hours. With historical dates 22-26 hours. Also, a minute can be between 59-61 seconds.
            // You get the difference days (or NaN if one or both could not be parsed). The parse date gives the result in milliseconds and to get it by DAY you have to divide it by 24 * 60 * 60 * 1000
            var diff =  Math.floor(( Date.parse(expiryDate) - Date.parse(currentDate) ) / 86400000) + 1;

            return diff;
        };
    }


    var Worker = function(data){
        var self = this;

        self.worker_id = ko.observable('');
        self.auth_token = ko.observable('');
        self.first_name = ko.observable('');
        self.last_name = ko.observable('');
        self.phone_number = ko.observable('');
        self.company_id = ko.observable('');
        self.tickets = ko.observableArray([]);
        self.profileThumbnailSource = ko.observable('');

        self.ticketsAreVisible = ko.observable(false);
        
        if (typeof data !== 'undefined' && data) {
            self.worker_id = ko.observable(data.worker_id);
            self.first_name = ko.observable(data.first_name);
            self.last_name = ko.observable(data.last_name);
            self.phone_number = ko.observable(data.phone_number);
            self.company_id = ko.observable(data.company_id);
            self.auth_token = ko.observable(data.auth_token);


            // If there are original workers passed in, create them
            if (typeof data.tickets !== 'undefined' && data.tickets) {
                $.each(data.tickets, function(i, el) {
                    self.tickets.push(new Ticket(el));
                });
            }
        }

        self.deleteTicket = function(ticket) {
            if(confirm("Are you sure you want to delete this ticket?")){
                $.post(site + 'tickets/delete', ticket.ticket_id(), function(response){
                    if(response.status){
                        returnMessage = 'Ticket was deleted successfully!';
                        // Now remove from view
                        self.tickets.remove(ticket);
                    } else {
                        returnMessage = response.errors;
                    }

                    alert(returnMessage);

                },'json');
            } 
        };

        self.changeTicketsVisibility = function () {
            // This is the syntax used to change the values of observables. = are not used.
            self.ticketsAreVisible(!self.ticketsAreVisible());
        };
    };
    // END MODULAR CLASSES ============================

    var TrainingMatrixModel = function (workers) {
        var self = this;

        self.lastNameSearch = ko.observable('');
        self.firstNameSearch = ko.observable('');
        
        // Arrays
        self.workers = ko.observableArray([]);

        // Used for serializing the object before sending to the controller
        self.lastSavedJson = ko.observable('');
        self.save = function() {
            self.lastSavedJson(JSON.stringify(ko.toJS(self), null, 2));
        };
        
        // If there are original workers passed in, create them
        if (typeof workers !== 'undefined' && workers) {
            $.each(workers, function(i, el) {
                self.workers.push(new Worker(el));
            });
        }
        
        // WORKER MANAGEMENT ========================
        self.addWorker = function() {
            var type = WORKER_TYPE;
            self.workers.push(new Worker());
        };

        self.removeWorker = function (worker) {
            self.workers.remove(worker);
        }; 
        self.numWorkers = function() {
            var count = 0;
            $.each(self.workers(), function(i, el) {
                if(el.type() == 0) {
                    count ++;
                }
            });
            return count;
        };


        ko.bindingHandlers.checkboxpicker =
        {
            init: function(element, valueAccessor){
                var val = valueAccessor();
                $(element).checkboxpicker();
                $(element).checkboxpicker().prop('checked', val()).change(function() {
                    val(this.checked);
                });
                $(element).disabled();
            }

            /*,
            update: function(element, valueAccessor) {
                var val = valueAccessor();
                $(element).prop('checked', val());
            }*/
        };

        self.filteredWorkers = ko.computed(function() {

            var lastNameSearch = self.lastNameSearch().toLowerCase(),
                firstNameSearch = self.firstNameSearch().toLowerCase();

            return ko.utils.arrayFilter(self.workers(), function (item) {
                return ( (lastNameSearch.length == 0 || 
                         ko.utils.stringStartsWith(item.last_name().toLowerCase(), lastNameSearch) ) &&
                         (firstNameSearch.length == 0 || 
                         ko.utils.stringStartsWith(item.first_name().toLowerCase(), firstNameSearch) ))
            });

        }, self);


        ko.utils.stringStartsWith = function(string, startsWith) {
            string = string || "";
            if (startsWith.length > string.length) return false;
            return string.substring(0, startsWith.length) === startsWith;
        };

        ko.bindingHandlers.fadeVisible = {
            init: function(element, valueAccessor) {
                // Initially set the element to be instantly visible/hidden depending on the value
                var value = valueAccessor();
                $(element).toggle(ko.unwrap(value)); // Use "unwrapObservable" so we can handle values that may or may not be observable
            },
            update: function(element, valueAccessor) {
                // Whenever the value subsequently changes, slowly fade the element in or out
                var value = valueAccessor();
                ko.unwrap(value) ? $(element).fadeIn() : $(element).fadeOut();
            }
        };

        self.clearSearchFields = function () {
            // This is the syntax used to change the values of observables. = are not used.
            self.lastNameSearch('');
            self.firstNameSearch('');
        };

        ko.bindingHandlers.fadeIn = {
            init: function(element) {
                $(ko.virtualElements.childNodes(element))
                    .filter(function () { return this.nodeType == 1; })
                    .hide()
                    .fadeIn();
            }
        };
        ko.virtualElements.allowedBindings.fadeIn = true; // allow the use of KO comments to trigger this fade (by default you can't)


        self.readableDate = function (dateString) {
            var readableString = dateString.substr(0, dateString.indexOf(' ')); //gets the YYYY-MM-DD
            var dateItems = readableString.split("-");
            // months are indexed from 0 in js, hence the need to subtract one from the month
            var dateObject = new Date(dateItems[0], dateItems[1] - 1 ,dateItems[2]);

            var monthNames = [
              "Jan", "Feb", "Mar",
              "Apr", "May", "June", "July",
              "Aug", "Sept", "Oct",
              "Nov", "Dec"
            ];

            var day = dateObject.getDate();
            var monthIndex = dateObject.getMonth();
            var year = dateObject.getFullYear();

            return year + "-" + monthNames[monthIndex] + "-" + day;
        };

    }; // end TrainingMatrixModel declaration

    // BIND TO VIEW
    var trainingMatrixObj = new TrainingMatrixModel(workers);
    ko.applyBindings(trainingMatrixObj);

});