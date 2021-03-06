





//runs after site has fully loaded (sort of like main... but you can have functions inside)
//use $( "#divid") to edit the div with jQuery
jQuery(document).ready(function($) {

//pass the chart object to a function that will populat the data
//and set options and div to be placed in
drawChart1(new Chart('div1') );	
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
	
	  c.data = google.visualization.arrayToDataTable([
		['Month',   'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda'],
		['2004/05',    165,      938,         522,             998,           450],
		['2005/06',    135,      1120,        599,             1268,          288],
		['2006/07',    157,      1167,        587,             807,           397],
		['2007/08',    139,      1110,        615,             968,           215],
		['2008/09',    136,      691,         629,             1026,          366]
	  ]);
	
		c.options =  {
				title : 'Monthly Coffee Production by Country',
				isStacked: true,
				height: 400,
				vAxis: {title: "Cups"},
				hAxis: {title: "Month"}
			 };
	  // Create and draw the visualization.
	  c.chart = new google.visualization.AreaChart(document.getElementById(c.div));
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






