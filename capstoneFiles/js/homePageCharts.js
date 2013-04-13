

//runs after site has fully loaded (sort of like main... but you can have functions inside)
//use $( "#divid") to edit the div with jQuery
var div1;
var table;
jQuery(document).ready(function($) {

//Create a function to make a chart see Chars.js for some of the functions uses
//then run that fuction with a new Chart('div_id')
//div3 = drawChart1(new Chart('div3') );	
div1 = drawChart2(new Chart('div1') );
//table = drawTable(new Chart('div2') );



 
//draws a test area chart returns the index number of this chart in charts array in Charts.js
function drawChart1(c) {
	//c is a chart object (this of this function as a "constructor" to populate chart data)
	// this is populateing the three veriables in the Chart object (chart,data,options)
	// data holds all the data for the google chart, options hold all the options 
	//		(!never set width to anything other than '100%' in these options they will mess with the mobile viewing)
	// chart holds the data for the google chart
	
		
		//load Data from google dataTable
		c.data = google.visualization.arrayToDataTable([
          		['Year', 'Sales', 'Expenses'],
          		['2004',  1000,      400],
          		['2005',  1170,      460],
         		['2006',  660,       1120],
         		['2007',  1030,      540]
       		]);
	
		//set Options !!!DON'T SET WIDTH!!!
	 	c.options = {
		 		height:'150',
          		title: 'Company Performance',
          		hAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
        	};
	
		//creat area chart and load it into the div by id
		c.chart = new google.visualization.AreaChart(document.getElementById(c.div));
	
        //draw the chart
		c.draw();	
		
		// you can save the index number of the Chart with this to update the c.data later... not working yet
		return charts.indexOf( c );
}


//draws a test SQL to PHP that returns JASON object
function drawChart2(c) {

	//set a mySql query
	 var q = "SELECT DATE_FORMAT(TIME,'%c/%e/%Y %h%p') , CC_TEMP as CC FROM buildings "+
	 "WHERE TIME BETWEEN  '2012-09-07' AND  '2012-09-08' "+
	 "AND CC_TEMP IS NOT NULL "+
	 "ORDER BY  Time ASC "; 
    //set the database
     var db = "mstomytest";
     //dbug link (retruns link to find php/mySQL errors)...
    // console.debug('http://localhost/capstoneFiles/php/getData.php?q='+encodeURIComponent(q)+'&dbs='+encodeURIComponent(db));
 	
 	//get google Jason from php (php/getData.php)
      var jsonData = $.ajax({
          url: "../capstoneFiles/php/getData.php?q="+encodeURIComponent(q)+"&dbs="+encodeURIComponent(db),
          dataType:"json",
          async: false
          }).responseText;
          
      // Create a data table out of JSON object loaded from php
      c.data = new google.visualization.DataTable(jsonData);
      //set some options (pie charts need a height)
	  c.options = { 'title':'Testing SQL TEMP one week',
	  			height:'250'
                     };
      // create and draw pie chart
      c.chart = new google.visualization.AreaChart(document.getElementById(c.div));
      c.draw();
    
	// you can save the index number of the Chart with this to update the c.data later... not working yet
		return charts.indexOf( c );
}





function drawTable(c) {

			
	  c.data = google.visualization.arrayToDataTable([
		['Name', 'Height', 'Smokes'],
		['Tong Ning mu', 174, true],
		['Huang Ang fa', 523, false],
		['Teng nu', 86, true]
	  ]);
	  c.options = {showRowNumber: true, width: '100%'};
	  // Create and draw the visualization.
	  c.chart = new google.visualization.Table(document.getElementById(c.div));
		
	  c.draw();
	// you can save the index number of the Chart with this to update the c.data later... not working yet
		return charts.indexOf( c );
}




	
});
//end of jquery 


function updateTablePrototype(toggle) {
//joomal turns off jquery this function($) is to get the jQuerys $ back	
(function($) {
	var c = charts[div1];
	
	//set a mySql query
	 var table = "buildings";
	 //set the database
     var db = "mstomytest";
     
	 //get building as query or get all building quiry
	 if(building != "all" ){
		 var q = "SELECT DATE_FORMAT(TIME,'%c/%e %h%p') , "+building+"_"+type+" as '"+building+" "+type+"' , weather as 'Outside' "+
		 " from "+table+
		 " WHERE TIME BETWEEN  '"+date[0]+"' AND  '"+date[1]+"' and weather is not null"+
		 " ORDER BY time ASC "; 
		 
		 
	 	//get google Jason from php (php/getData.php)
	      var jsonData = jQuery.ajax({
	          url: "../capstoneFiles/php/getData.php?q="+encodeURIComponent(q)+"&dbs="+encodeURIComponent(db),
	          dataType:"json",
	          async: false
	          }).responseText;
	          
	      // Create a data table out of JSON object loaded from php
	      c.data = new google.visualization.DataTable(jsonData);
	      //set some options (pie charts need a height)
	      if(type == "temp")
	      	var titleStr = "TEMPITUER";
	      else
	      	var titleStr = "Kilowatts per hour"
		  c.options = { 'title': building+' building (one '+range+')',
		  			height:'300',
		  			vAxis: {title: titleStr},
		  			seriesType: "area",
	          		series: {1: {type: "line"}},
	          		colors: [ '#542437','#CAA91E', '#ec8f6e', '#f3b49f', '#f6c7b6']
	                     };
	      // create and draw pie chart
	      c.chart = new google.visualization.ComboChart(document.getElementById(c.div));
	      c.draw();
      
	}
		if(toggle)
		{ $("#map").slideToggle(1000);
			$("#buildingInfo").slideToggle(1000);
       		
       		if(isDown){
       			isDown = false;
				
				
       		}
       		else
       		{
       		 isDown = true;
       		 
       		}
       		 
       		 
       		//document.getElementById('map').style.height = (document.getElementById('map').offsetWidth)*(468/804) + "px";

		}
		

		
    
     	
       //set html for building info here
       if(building != "all"){
       	//show and hide animation for 1000 milisconds
       
      
       
       	var image = '<div style=" float: left; background-color: '+ $('.'+building+'sun').css('fill') +'; display: inline-block; height:105px; width:210px">'+
			'<img height="100%" width="100%" src="../../capstoneFiles/images/builidngsFixed/'+building+'.png" /> </div>';	
		

      	var lipsum = image+'<button class="button" onclick=\'building = "all"; updateTablePrototype(true); \'>BACK</button> Info about '+building+' building here <br>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam id ipsum nibh, eu accumsan turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nam iaculis scelerisque eros, in pretium leo convallis a. Duis congue, justo non auctor vulputate, quam augue vulputate nulla, at vulputate est felis dictum nisl. Nullam aliquet purus ut metus eleifend sed facilisis urna pulvinar. <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam id ipsum nibh, eu accumsan turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nam iaculis scelerisque eros, in pretium leo convallis a.s';
       $("#buildingInfo").html(lipsum);
       
       }
       else 
       {
       	$("#name").html( "<h1>Pick a building</h1>");
       }
       
       
       
       
		
	})(jQuery);
	
}





