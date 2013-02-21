jQuery(document).ready(function($) {


	$( "#buttons button" )
      .button()
      .click(function( event ) {
        event.preventDefault();
      });
	  	$( "#typeButtons button" )
      .button()
      .click(function( event ) {
        event.preventDefault();
      });
 	//make date picker
	$( "#buttons").fadeTo(1, 0).hide();
    $( "#from" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
	  changeYear: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
	 $( "#to" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
	  changeYear: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#from" ).datepicker( "option", new Date(), selectedDate );
      }
    });

	
	
	
//for testing the filter by box (buttons, date picker and loading bar	
    var progressbar = $( "#progressbar" ),
      progressLabel = $( "#progress-label" );
 
    progressbar.progressbar({
      value: false,
      change: function() {
		  progressLabel.text( "Loading Data: "+progressbar.progressbar( "value" ) + "%" );
      },
      complete: function() {
        progressLabel.text( "Complete!" );
		 progressbar.delay(800).fadeTo('slow', 0, function() {
     	 progressbar.hide();
		 $( "#buttons").show().fadeTo("slow", 1);
			 
  	  	});
      }
    });
 
    function progress() {
      var val = progressbar.progressbar( "value" ) || 0;
 
      progressbar.progressbar( "value", val + 1 );
 
      if ( val < 99 ) {
        setTimeout( progress, 100 );
      }
    }
	

//for fake loading
    setTimeout( progress, 1000 );
	
	
});
//end of jquery