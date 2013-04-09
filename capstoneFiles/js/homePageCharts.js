

//runs after site has fully loaded (sort of like main... but you can have functions inside)
//use $( "#divid") to edit the div with jQuery
var pieChart;
var table;
jQuery(document).ready(function($) {

//Create a function to make a chart see Chars.js for some of the functions uses
//then run that fuction with a new Chart('div_id')
div3 = drawChart1(new Chart('div3') );	
pieChart= drawChart2(new Chart('div1') );
table = drawTable(new Chart('div2') );



 
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
	 var q = "SELECT DATE_FORMAT(TIME,'%c/%e/%Y %h%p') , CC_TEMP as CC FROM test7 "+
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
	  			height:'350'
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
function updateTablePrototype(building) {

	var c = charts[pieChart];
	//set a mySql query
	 
	 var q = "SELECT DATE_FORMAT(testFinal.TIME,'%c/%e/%Y %h%p') , testFinal."+building+"_TEMP as '"+building+" Temp' , weather3.temp as 'Outside' "+
	 " FROM testFinal LEFT JOIN weather3 ON weather3.time = testFinal.time "+
	 " WHERE testFinal.TIME BETWEEN  '2012-09-07' AND  '2012-9-14' "+
	 "AND weather3.temp > 10 ORDER BY  `testFinal`.`Time` DESC"; 

    //set the database
     var db = "mstomytest";
     //dbug link (retruns link to find php/mySQL errors)...
    // console.debug('http://localhost/capstoneFiles/php/getData.php?q='+encodeURIComponent(q)+'&dbs='+encodeURIComponent(db));
 	
 	//get google Jason from php (php/getData.php)
      var jsonData = jQuery.ajax({
          url: "../capstoneFiles/php/getData.php?q="+encodeURIComponent(q)+"&dbs="+encodeURIComponent(db),
          dataType:"json",
          async: false
          }).responseText;
          
      // Create a data table out of JSON object loaded from php
      c.data = new google.visualization.DataTable(jsonData);
      //set some options (pie charts need a height)
	  c.options = { 'title':'mySQL '+building+'building TEMP one week',
	  			height:'350',
	  			seriesType: "area",
          		series: {1: {type: "line"}},
          		colors: ['#53777A', '#542437', '#ec8f6e', '#f3b49f', '#f6c7b6']
                     };
      // create and draw pie chart
      c.chart = new google.visualization.ComboChart(document.getElementById(c.div));
      c.draw();
      
      
     


}






