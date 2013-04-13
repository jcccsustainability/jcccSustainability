

//list of all building names as an array (are global vars and will work in other scripts)
var buildingNames = new Array("ATB","CC","CLB","GEB__COM","CSB","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");
var buildingNamesFull = new Array("Arts & Technology","Carlsen Center","Classroom Laboratory","Commons / General Education","Campus Services","Gymnasium","Hiersteiner Child Development Center","Horticulture Science Center","Industrial Training Center","Billington Library","Nerman Museum of Contemporary Art","Office and Classroom","Police Academy","Regnier Center","Student Center","Science Building","Warehouse","Welding Laboratory Building");
			

var ie = false;
//tell IE users Internet Explorer 8 or lower is stupid..
if(navigator.appName == "Microsoft Internet Explorer")
{
  ie = true;
  version = parseFloat(navigator.appVersion.split("MSIE")[1]);
  if(version <= 8)
  alert("Sorry, your browser does not support this site, upgrade Internet Explorer or use a different browser");
}	  	  	

	
   	    //run when page loads 
   	   if(!ie)
        jQuery(document).ready(function($){
        	//if browser is IS IE
       
        	
        	//will hold the svg file to load in
        	var svgData;
        	
        	
 			
			//hide building info div
			$("#buildingInfo").slideToggle(0);
			$('#ja-content').height(0);
			
			//gets the the custom MAP svg (with css classes applied to each svg <path> and removed the style from each svg <path> )
			$.ajax({
	        url: "../capstoneFiles/images/MAP.svg.txt",
	        async: false,   
	        cache: false,   
	        dataType: "text",  // jQuery will infer this, but you can set explicitly
	        success: function( data, textStatus, jqXHR ) {
	            svgData = data;
	        }
	    	});
	    	console.log("type="+type+"&date1="+date[0]+"&date2="+date[1]);
	    	
	
		//load the svg in to the map div
		$("#map").html(svgData);
		//resize div and and perserve aspect ratio
		document.getElementById('map').style.height = (document.getElementById('map').offsetWidth)*(468/804) + "px";
		 
		//set colors
		updateMaps();
       		

			
//all buildings have a building class and eather a BUILDINGNAMEsun or BUILDINGNAMEshad class

//when any building is clicked this funs	
$(".building").click(function() {
	  //get the name of the building
      var name = $(this).attr('class').split(' ')[1]
      if($('.'+name).css('cursor') == 'pointer')
      {
      //remove the sun or shade part of the building name
      name = name.replace("sun",'');
      name = name.replace("shade",'');
      
      //run another function that returns the name
      
      // focus on a single bilding (by setting other buildings transparent)
      for(var i = 0; i<buildingNames.length;i++ )
      {//fill-opacity:
      	if(name == buildingNames[i]){
      		
			$("#name").html( "<h1>"+buildingNamesFull[i]+"</h1>");
		
		}
      }
      //run a function in homePageCharts to update google charts 
      building = name;
      updateTablePrototype(true);
     }
      	
  });

  

 
});//end of JQUERY



function updateMaps(){
	
	(function($) {
		
		 //holds colors for buildings by php and an sql query
        	var colorData;
		//get building temps from php as space sperrated string in order of building names array
	    	$.ajax({
	        url: "../capstoneFiles/php/getColors.php?type="+type+"&date1="+date[0]+"&date2="+date[1],
	        async: false,   
	        cache: false,   
	        dataType: "text",  // jQuery will infer this, but you can set explicitly
	        success: function( data, textStatus, jqXHR ) {
	            colorData = data;
	        }
	    	});
	    	
	    	//get each value as an array
	    	 var colorPercent = colorData.split(' ')
	    	
		
		
			//get building name and edit each css class' fill in the svg image
			 for(var j = 0; j<buildingNames.length;j++)
       		{
				//get a random number to change the color to for now(testing)
				console.log(buildingNames[j]+", "+colorPercent[j]);
				if(colorPercent[j] != "gray" ){
					setBuildingColor(buildingNames[j], colorPercent[j]);
				}
				else 
				{
					setBuildingGray(buildingNames[j]);
					
				}
				
	
       		}
       		
	})(jQuery);
}

//set a buildings color 
function setBuildingColor(bld, percent) {
	(function($) {
	  //if css class exists
      	if( $('.'+bld+"sun") )
      	{	//get hex code from rgb code 
      		sun = '#'+HEX( getRed(percent), getGreen(percent),0 ).toLowerCase();
			shade = '#'+ HEX(  getRed(percent)-70,  getGreen(percent)-70,  0  ).toLowerCase();
			//set css fill atrabute to the new color
			$('.'+bld+'sun').css('fill',sun);
			$('.'+bld+'shade').css('fill',shade);
			
			$('.'+bld+'sun').css('cursor','pointer');
			$('.'+bld+'shade').css('cursor','pointer');
			$('.'+bld).css('cursor','pointer');
      	}
      	})(jQuery);
  }
//set a building gray  
function setBuildingGray(bld) {
	(function($) {
	  	//if css class esists
      	if( $('.'+bld+"sun") )
      	{	//set grays for sun and shade
      		sun = '#'+HEX( 125, 125,125 ).toLowerCase();
			shade = '#'+ HEX(  125-70,  125-70,  125-70  ).toLowerCase();
			//set css fill atrabute to the new color
			$('.'+bld+'sun').css('fill',sun);
			$('.'+bld+'shade').css('fill',shade);
			
			$('.'+bld+'sun').css('cursor','default');
			$('.'+bld+'shade').css('cursor','default');
			$('.'+bld).css('cursor','default');
      	}
      	})(jQuery);
  }




  		
///////////////////////////////////////////////////////////////////////////
//functions       		
       		
       		
//converts a percentage to a number between 0 and 255
//red = rgb(255,0,0) yellow = rgb(255,255,0) green = rgb(0,255,0)  (yellowish-green could be something like rgb(200,255,0))  (orange could be something like (255,200,0))  		
 //returns the ammount that should be green for a percent      		
function getGreen(percent){
	//if it is less than 50 it will just return full green (adusted for color intentsity,255)
	if(percent<=50)
		return 255;
	else
		//it will return some green (adusted for color intentsity)
		return (255-((percent-50)/50)*255);
}
//sets the ammount red from percent
function getRed(percent){
	//if it is less than 50 it will just return full red (adusted for color intentsity)
	if(percent<=50)
		return (percent/50)*255;
	else
		//it will return full red (adusted for color intentsity)
		return 255;
}
       		
       		
//return a hex from r, g, b vars
function HEX(r,g,b) {
//get r g b as hex and add and return 
return toHex(r)+toHex(g)+toHex(b);
}
//get a single color to hex			
function toHex(n) {
n = parseInt(n,10);
if (isNaN(n)) return "00";
n = Math.max(0,Math.min(n,255));
return "0123456789ABCDEF".charAt((n-n%16)/16)
+ "0123456789ABCDEF".charAt(n%16);
}
			
	