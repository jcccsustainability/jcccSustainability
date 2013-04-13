//runs after site has fully loaded (sort of like main... but you can have functions inside)

var type = "temp";
var date = Array("2012-09-07","2012-09-14");
var range = "Week";
var building = "all";

jQuery(document).ready(function($) {
	
    $( "#type" ).buttonset();
    $( "#timeRange" ).buttonset();
    $( "#datepicker" ).datepicker( { 
      dateFormat: "yy-mm-dd",
      minDate: new Date(2012, 9-1, 07), 
      maxDate: new Date(2013, 3-1, 31),
      defaultDate: "2012-09-07",
    }
   );
  
	
});//end of jQuery

function updateFilters(){
	
	(function($) {
		 if($('#temp').is(':checked')) { 
		 	type = "temp";
		 }
		 else
		 {
		 	type = "kwh";
		 }
		 var datePicked = $( "#datepicker" ).datepicker( "getDate" );
		 if($('#day').is(':checked')) { 
		 	range = "Day";
		 	date[0] = datePicked.getFullYear() + "-" +
		 			  (datePicked.getMonth()+1) + "-" +
		 			  datePicked.getDate();
		 			  
		 	datePicked.setDate(datePicked.getDate() + 1);
		 	
		 	date[1] = datePicked.getFullYear() + "-" +
		 			  (datePicked.getMonth()+1) + "-" +
		 			  datePicked.getDate();		  
		 	
		 	
		 }
		 else if($('#week').is(':checked')) { 
		 	range = "Week";
		 	date[0] = datePicked.getFullYear() + "-" +
		 			  (datePicked.getMonth()+1) + "-" +
		 			  datePicked.getDate();
		 			  
		 	datePicked.setDate(datePicked.getDate() + 7);
		 	
		 	date[1] = datePicked.getFullYear() + "-" +
		 			  (datePicked.getMonth()+1) + "-" +
		 			  datePicked.getDate();		  

		 }
		 else{
		 	range = "Month";
		 	date[0] = datePicked.getFullYear() + "-" +
		 			  (datePicked.getMonth()+1) + "-" +
		 			  datePicked.getDate();
		 			  
		 	datePicked.setMonth(datePicked.getMonth() + 2);
		 	
		 	date[1] = datePicked.getFullYear() + "-" +
		 			  (datePicked.getMonth()+1) + "-" +
		 			  datePicked.getDate();
		 }
		//alert(date[0]+" - "+date[1])
		updateMaps();
		updateTablePrototype(false); 
	})(jQuery);
}

