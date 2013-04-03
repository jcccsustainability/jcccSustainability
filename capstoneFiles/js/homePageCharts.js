

//runs after site has fully loaded (sort of like main... but you can have functions inside)
//use $( "#divid") to edit the div with jQuery
var pieChart;
var table;
jQuery(document).ready(function($) {

//Create a function to make a chart see Chars.js for some of the functions uses
//then run that fuction with a new Chart('div_id')
drawChart1(new Chart('div3') );	
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
	 var q = "SELECT TABLE_NAME, AVG_ROW_LENGTH  FROM TABLES "+
     " where AVG_ROW_LENGTH > 0  ORDER BY AVG_ROW_LENGTH DESC 	LIMIT 0, 20";
    //set the database
     var db = "information_schema";
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
	  c.options = { 'title':'Testing SQL to google chart',
	  			height:'250'
                     };
      // create and draw pie chart
      c.chart = new google.visualization.PieChart(document.getElementById(c.div));
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
function updateTablePrototype() {

	var c = charts[table];
		//get google Jason from php (php/getData.php)
      var jsonData = jQuery.ajax({
          url: "../capstoneFiles/php/getDataMS.php?",
          dataType:"json",
          async: false
          }).responseText;
          
      // Create a data table out of JSON object loaded from php
      c.data = new google.visualization.DataTable(jsonData);	
			
			
			
	  
	  c.options = {showRowNumber: true, width: '100%'};
	  // Create and draw the visualization.
	  c.chart = new google.visualization.Table(document.getElementById(c.div));
		
	  c.draw();
	  
	  c = charts[pieChart];
		
      c.data = new google.visualization.DataTable(jsonData);	
			
			
			
	  
	  c.options = { 'title':'Testing Temp',
	  			height:'350'
                     };
	  // Create and draw the visualization.
	 c.chart = new google.visualization.BarChart(document.getElementById(c.div));
		
	  c.draw();

}






