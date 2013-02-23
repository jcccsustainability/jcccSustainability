//load google chart packages
google.load("visualization", "1", {packages:["corechart",'table']});

//simeple debug sort hand function with toggle
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
		
	//stores the new chart to charts array if div exists
	if(isDiv(div)) 
	charts.push(this);
}
//Chart Method (draws the chart)
Chart.prototype.draw = function(){
	if(isDiv(this))
		this.chart.draw(this.data, this.options);
}
/////////////////////////////////////////////
////////////////////////////////////////////










//checks if div exists
function isDiv(c){
	//checks if its a chart object and if javascript cant find the id
	//this is for something like isDiv('chart_ID');
	if(charts.indexOf( c ) == -1 && document.getElementById(c) == null )
	{ dbg("div with  id = \""+c.div+"\"  not found");
		return false;
	}
		
	//if it is a hart and the div_id is not found it will send a debug and delete
	else if(charts.indexOf( c ) >= 0 && document.getElementById(c.div) === null)
		{   //send debug
			dbg("Chart with <div id = \""+c.div+"\" > not found, Chart Deleted");
			//delets c from charts array
			charts.splice(charts.indexOf( c ),1);
			return false;
		}
	//is some type of div
		return true;
	
}


//when window is ever resized this will be called
window.onresize = function(event) {
	//checks is campus map is showing and resizes it
	if(document.getElementById('map') !== null)
		document.getElementById('map').style.width = document.getElementById('mapSize').offsetWidth + "px";
	//resize each chart in charts array
	for (var i = 0; i < charts.length; i++){
    	charts[i].draw();	
	}
	
};
/////////////////////////////////////////////





