$( document ).ready(function() {
    // TEMPORARY
    $( ":checkbox").checkboxpicker();

    $(".chzn-select").chosen();

    var TicketImage = function(parent, data) {
        var self = this;

        // This number will be used to differentiate between the different canvases on the page if there are multiple images
        self.uniqueId = ko.observable(Math.floor((Math.random() * 100000) + 1)); // Number between 1 and 100,000

        self.fileData = ko.observable({
            dataURL: ko.observable(''),
            modifiedDataURL: ko.observable('')
        });

        self.darkRoomEditor = null;

        // Unused with current configuration because the button is disabled, but leaving just in case. 
        self.onClear = function(fileData){
            if(confirm('Are you sure you want to clear this image?')){
                fileData.clear && fileData.clear();
            }                            
        };

        // dataURL has changed (the image has loaded).. now initialize editor!
        // self.fileData().dataURL.subscribe(function(dataURL){
        //     //Disable the upload button now -- the user will have to remove the image and re-add to change the photo
        //     $('#imageContainer' + self.uniqueId()).find('.custom-file-input-wrapper').hide();
              
        //     //var picture = $('#thepicture');
        //     var picture = $('#target' + self.uniqueId());

        //     // Make sure the image is completely loaded before calling the plugin
        //     picture.one('load', function(){
        //         var dkrm = new Darkroom('#target' + self.uniqueId(), {
        //             // Size options
        //             minWidth: 400,
        //             minHeight: 300,
        //             maxWidth: 400,
        //             maxHeight: 300,
        //             ratio: 4/3,
        //             backgroundColor: '#000',

        //             // Plugins options
        //             plugins: {
        
        //                 crop: {
        //                   //quickCropKey: 67, //key "c"
        //                   //minWidth: 400,
        //                   //minHeight: 300,
                          
        //                   ratio: 4/3
        //                 },
        //                 //save: false, // This would disable saving
        //                 save: {
        //                     callback: function() {
        //                         this.darkroom.selfDestroy();
        //                         var newImage = dkrm.canvas.toDataURL();
        //                         self.fileData().modifiedDataURL = newImage;
        //                     }
        //                 }
        //             },

        //           // Post initialize script
        //           initialize: function() {
        //             var cropPlugin = this.plugins['crop'];
        //             // cropPlugin.selectZone(170, 25, 300, 300);
        //             // This  makes cropping appear automatically
        //             //cropPlugin.requireFocus();

        //             // Add custom listener
        //             this.addEventListener('core:transformation', function() { 
        //                 // User has applied transformation to image
        //                 // Here we can perform more actions if needed
        //             });

        //           }
        //         });
        //     }); // when picture is loaded
        // }); // end subscription

        if (typeof data !== 'undefined' && data) {
            self.fileData().dataURL(data);
        }
        
    }; // end TicketImage declaration

    var TicketsViewModel = function (workers) {
        var self = this;

        self.expiry_date = ko.observable('');
        self.issued_internally = ko.observable(0); // default will be external ticket
        self.newPhotos = ko.observableArray([]); 
        self.issuer_organization_name = ko.observable('');
        self.worker_id = ko.observable('');
        self.description = ko.observable('');
        self.type_name = ko.observable('');
        // self.workerList = ko.observableArray([{label: 'Low', riskValue: 0 }, {label: 'Medium', riskValue: 1 }, {label: 'High', riskValue: 2 }]);

        // Used for serializing the object before sending to the controller
        self.lastSavedJson = ko.observable(''); 
        
        self.addImage = function() {
            self.newPhotos.push(new TicketImage(self));
        };

        self.addImageWithData = function(data) {
            var imageObject = new TicketImage(self, data)
            self.newPhotos.push(imageObject);
            return imageObject;
        };

        self.removeImage = function (ticketImage) {
            self.newPhotos.remove(ticketImage);
        }; 

        self.save = function() {     
            self.newPhotos().forEach(function (element, index, array) {
                if(element.darkRoomEditor != null) {
                    // Ensure each image edit is finalized before submitting
                    var editor = element.darkRoomEditor();
                    var newImage = editor.canvas.toDataURL();
                    element.fileData().modifiedDataURL = newImage;
                    // Editor reference no longer required
                    element.darkRoomEditor = null;
                }
            });

            self.lastSavedJson(JSON.stringify(ko.toJS(self), null, 2));
        };

        self.isInternalTicket = ko.computed({
            read: function() {
                return self.issued_internally() > 0;
            },
            write: function(isInternalTicket) {
                // No to Yes
                if (isInternalTicket && self.issued_internally() < 1) {
                    self.issued_internally(1); 
                    self.issuer_organization_name('');
                    return self.isInternalTicket.notifySubscribers();
                }
                else {
                    self.issued_internally(0);
                    return self.isInternalTicket.notifySubscribers();
                }
            }
        }, this);

        //  EASY IMAGE UPLOAD ==================================================================
        File.prototype.convertToBase64 = function(callback){
                var FR= new FileReader();
                FR.onload = function(e) {
                     callback(e.target.result)
                };       
                FR.readAsDataURL(this);
        }

        $("#imageUpload").on('change',function(base64){
            var selectedFile = this.files[0];
            selectedFile.convertToBase64(function(base64){
                var imageObject = self.addImageWithData(base64);
                loadImageEditorForImageObject(imageObject);
            }) 
        });
        // END EASY IMAGE UPLOAD ==================================================================

    }; // end TicketsViewModel declaration


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
            },
            viewMode: 'years'
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


    var ticketsObj = new TicketsViewModel([]);
    ko.applyBindings(ticketsObj);

    // Disable the clear button on file upload because we have a different way of removing
    ko.fileBindings.defaultOptions.clearButton = false;

    $('#addTicketForm').submit(function(event){
        // preventDefault stops the regular synchronous submit from happening -- we don't want that. we want an async AJAX request
        event.preventDefault();
        
        $("#submitTicketBtn").html('Adding Ticket... <img src="'+site+'assets/img/loading.gif" alt="." style="width:15px; height:15px; margin-top:-4px;"></img>');
        $("#submitTicketBtn").prop('disabled', true);
        
        ticketsObj.save();
        var formData = ticketsObj.lastSavedJson();

        var returnMessage = '';

        // Validation is done with HTML5 --  server side validation will happen in this call and error messages will be returned if the data is bad
        $.post(site+'tickets/create', formData, function(response){
            if(response.status){
                $.confirm({
                    title: 'Ticket Added!',
                    content: 'Add another?',
                    confirmButton: 'Yes',
                    cancelButton: 'No',
                    confirmButtonClass: 'btn-primary',
                    keyboardEnabled: true, // User can press enter or ESC
                    confirm: function(){
                        window.location.replace(site + 'tickets/create-ticket-view');
                    },
                    cancel:function(){
                        window.location.replace(site + 'tickets');
                    }
                });
                
                return false;
            } else {
                for (i = 0; i < response.errors.length; i++) { 
                    returnMessage += response.errors[i] + '\n';
                }

                $.alert({
                    title: 'Save Failed!',
                    content: returnMessage,
                    confirmButton: 'Ok',
                    confirmButtonClass: 'btn-primary',
                    cancelButton: false,
                    keyboardEnabled: true, // User can press enter or ESC
                    confirm: function(){
                        // Nothing
                    }
                });
            }

            $("#submitTicketBtn").html('Add Ticket');
            $("#submitTicketBtn").prop('disabled', false);
        },'json');

        return false; // return false to cancel form action
    });  

    function loadImageEditorForImageObject(imageObject) {
        var imageObject = imageObject;
        var picture = $('#target' + imageObject.uniqueId());

        // Make sure the image is completely loaded before calling the plugin
        picture.one('load', function()
        {
            var dkrm = new Darkroom('#target' + imageObject.uniqueId(), {
                // Size options
                minWidth: 400,
                minHeight: 300,
                maxWidth: 400,
                maxHeight: 300,
                ratio: 4/3,
                backgroundColor: '#FFF',

                // Plugins options
                plugins: {
                    crop: {
                      ratio: 4/3,
                      maxWidth: 400,
                      maxHeight: 300
                    },
                    save: false
                },

              // Post initialize script
              initialize: function() {
                var cropPlugin = this.plugins['crop'];
                // cropPlugin.selectZone(170, 25, 300, 300);
                // This  makes cropping appear automatically
                //cropPlugin.requireFocus();

                // Add custom listener
                this.addEventListener('core:transformation', function() { 
                    // User has applied transformation to image
                    // Here we can perform more actions if needed
                });

              }
            });
            
            // Assign a reference to the editor so that it can be accessed later, such as when saving the ticket, to loop through and ensure the save event is called on all images.
            imageObject.darkRoomEditor = function() { return dkrm };

        }); // picture.load
    }  


    // PDF UPLOAD =============================================================================
    var pdf = document.getElementById('pdf');

    pdf.onchange = function(ev) 
    {
        if (file = document.getElementById('pdf').files[0]) 
        {
            fileReader = new FileReader();

            // file reader will load the PDF file asynchronously so as to not block UI  (fileReader.readAsArrayBuffer(file);)
            fileReader.onload = function(ev) 
            { 
                /*We don't recommend disableWorker mode = true -- the performance of PDF.js will suffer and causes UI to lock up. If you uses this mode
                 explicitly, you have to set PDFJS.workerSrc, so pdfjs.worker.js could be located properly (otherwise it needs script tag, 
                    see https://github.com/mozilla/pdf.js/blob/master/src/pdf.js#L41) As alternative, you can build single file pdf.js via node make singlefile.*/
                PDFJS.disableWorker = false; // due to CORS

                var canvas = document.createElement('canvas'), // single off-screen canvas
                    ctx = canvas.getContext('2d'),             // to render to
                    pages = [],
                    currentPage = 1;

                PDFJS.getDocument(fileReader.result).then(function (pdf) {

                    PROGRESS.max = pdf.numPages; 
                    PROGRESS.value = 1; 
                    
                    // init parsing of first page
                    if (currentPage <= pdf.numPages) getPage();

                    // main entry point/function for loop
                    function getPage() {
                        // when promise is returned do as usual
                        pdf.getPage(currentPage).then(function(page) {

                            var scale = 1.5;
                            var viewport = page.getViewport(scale);

                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            var renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };

                            // now, tap into the returned promise from render:
                            page.render(renderContext).then(function() {

                                // store compressed image data in array
                                pages.push(canvas.toDataURL());

                                if (currentPage < pdf.numPages) {
                                    currentPage++;
                                    PROGRESS.value = currentPage; // just for demo
                                    getPage();        // get next page
                                }
                                else {
                                    done();           // call done() when all pages are parsed
                                }
                            });
                        });
                    }
                });

                function done() {
                    // NOTE: Just for demo - correct order is not guaranteed here
                    // as the drawPage is async. use same method as above to make
                    // sure the order is correct (not for-loop, but use the callback
                    // to get next page). To present a single page it won't be
                    // a problem though... (just use drawPage() directly)
                    for(var i = 0; i < pages.length; i++) {
                        drawPage(i, addPage);
                    }
                }

                function addPage(img) {
                    var imageObject = ticketsObj.addImageWithData(img.src);
                    loadImageEditorForImageObject(imageObject);
                    // img.style.width = '100px';
                    // img.style.height = '120px';
                    // document.body.appendChild(img);
                }

                function drawPage(index, callback) {
                    var img = new Image;
                    img.onload = function() {
                        ctx.drawImage(this, 0, 0, ctx.canvas.width, ctx.canvas.height);
                        callback(this);          // invoke callback when we're done
                    }
                    img.src = pages[index];  // start loading the data-uri as source
                }

            } // FILE READER ON LOAD

            fileReader.readAsArrayBuffer(file);

        } // IF FILE

    } // PDF UPLOAD BUTTON ON CHANGE 
    // PDF UPLOAD ============================================================================= 


}); // document ready