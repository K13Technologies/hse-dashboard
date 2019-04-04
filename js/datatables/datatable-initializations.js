$(document).ready(function(){
	// THIS IS THE FILE USED TO CENTRALIZE THE INITILIZATION AND MANAGEMENT OF ALL DATATABLES THROUGHOUT THE APPLICATION

	// All the tables with this class will be initialized with these settings
	$(".dataTable").dataTable({
            "stateSave": true,
            "stateDuration": -1,
            "responsive": true,
            "order": [[ 0, "desc" ]]
     });

});