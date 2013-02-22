
var debug = true;

function dbg(e){
	if (debug)
	console.debug(e);
}

//////////////////////////////////////
/////Chart Object////////////////////
//holds an array of charts
charts = new Array();
//chart object
function Chart( div){
	//div ID
	this.div = div;
		
	//holds a google chart
	this.chart;
		
	//holds chart data
	this.data;
		
	//holds chart options
	this.options;
		
	//stores the new chart to charts array 
	charts.push(this);
}
//Chart Method (draws the chart)
Chart.prototype.draw = function(){
	if(isDiv(this))
		this.chart.draw(this.data, this.options);
}
/////////////////////////////////////////////
////////////////////////////////////////////





//runs after site has fully loaded (sort of like main... but you can have functions inside)
//use $( "#divid") to edit the div with jQuery
jQuery(document).ready(function($) {
google.load("visualization", "1", {packages:["corechart",'table']});

});
//end of jquery


//checks if div exists
function isDiv(c){
	
	if(charts.indexOf( c ) == -1 && document.getElementById(c) == null )
	{
	
		return false;
	}
		
	//is a chart and not a div
	else if(charts.indexOf( c ) >= 0 && document.getElementById(c.div) === null)
		{   //delets from array
			charts.splice(charts.indexOf( c ),1);
			return false;
		}
	//is some type of div
		return true;
	
}


//when window is ever resized this will be called
window.onresize = function(event) {
	//resized campus map
	if(document.getElementById('map') !== null)
	document.getElementById('map').style.width = document.getElementById('mapSize').offsetWidth + "px";
	//resize the charts
	
	for (var i = 0; i < charts.length; i++){
    	charts[i].draw();
		
	}
	
};
/////////////////////////////////////////////





