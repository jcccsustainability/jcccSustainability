





//runs after site has fully loaded (sort of like main... but you can have functions inside)
//use $( "#divid") to edit the div with jQuery
jQuery(document).ready(function($) {

//pass the chart object to a function that will populat the data
//and set options and div to be placed in
drawChart1(new Chart('div3') );	
drawChart2(new Chart('div1') );
drawTable(new Chart('div2') );
	
	

 
//draws a test bar chart
function drawChart1(c) {
	//c is a chart object (this of this function as a "constructor" )
	// this is populateing the three veriables in the Chart object (chart,data,options)
	// data holds all the data for the google chart, options hold all the options 
	//		(!never set width in these options they will mess with the mobile viewing)
	// chart holds the data fro the google chart
	
		//check if div id exists (if it doesnt exist all the charts on the site wont show up)
		//and worse we will be running un needed queries and the future
		//!always check for div !
		if(!isDiv(c))
			return;
		
		//load Data 
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
	
        //draws chart
		c.draw();	

}


//draws a test bar chart
function drawChart2(c) {
	if(!isDiv(c))
			return;
	//set a mySql qery
	 var q = "SELECT TABLE_NAME, AVG_ROW_LENGTH  FROM TABLES "+
     " where AVG_ROW_LENGTH > 0  ORDER BY AVG_ROW_LENGTH DESC 	LIMIT 0, 20";
    //set the database
     var db = "information_schema";
     //dbug link...
     console.debug('http://localhost/capstoneFiles/php/getData.php?q='+encodeURIComponent(q)+'&dbs='+encodeURIComponent(db));
 	//get google chart data from JSON from php
      var jsonData = $.ajax({
          url: "../capstoneFiles/php/getData.php?q="+encodeURIComponent(q)+"&dbs="+encodeURIComponent(db),
          dataType:"json",
          async: false
          }).responseText;
          
      // Create our data table out of JSON data loaded from
      c.data = new google.visualization.DataTable(jsonData);
      //set some options (charts need a height)
	  c.options = { 'title':'Testing SQL to google chart',
	  			height:'250'
                     };
      // create and draw pie chart
      c.chart = new google.visualization.PieChart(document.getElementById(c.div));
      c.draw();
    
	
}





function drawTable(c) {
	if(!isDiv(c))
			return;
			
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
	
}




	
});
//end of jquery






