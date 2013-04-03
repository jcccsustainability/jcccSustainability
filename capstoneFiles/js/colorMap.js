     
   	       
        jQuery(document).ready(function($){
        	
        	//will hold the svg file to load in
        	var svgData;
        	
 			//list of all building names as an array
     	    var buildingNames = new Array("ATB","CC","CLB","COM","CSB","GEB","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");
			var buildingNamesFull = new Array("Arts & Technology","Carlsen Center","Classroom Laboratory","Commons","Campus Services","General Education","Gymnasium","Hiersteiner Child Development Center","Horticulture Science Center","Industrial Training Center","Billington Library","Nerman Museum of Contemporary Art","Office and Classroom","Police Academy","Regnier Center","Student Center","Science Building","Warehouse","Welding Laboratory Building");
			
			
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
	    	
	
		//load the svg in to the map div
		$("#map").html(svgData);
		//resize div and and perserve aspect ratio
		document.getElementById('map').style.height = (document.getElementById('map').offsetWidth)*(468/804) + "px";
		  //set the  on rollover finger aka pointer
			$('.building').css('cursor','pointer');
	
		
			//get building name and edit each class' fill in the svg <shapes>
			 for(var j = 0; j<buildingNames.length;j++)
       		{
				//get a random number to change the color to for now(testing)
				var rand = Math.floor(Math.random() * 100) + 1;
				
				//set add a # and the new colors as hex
				sun = '#'+HEXP( getRed(rand), getGreen(rand),0 ).toLowerCase();
				//get the same color as above but make it darker for shade
				shade = '#'+ HEXP(  getRed(rand)-70,  getGreen(rand)-70,  0  ).toLowerCase();
				
				//set the svg/css peroperty fill to the new colors
				//the '.' tells jQuery to find a css class
				//buildingNames[j] is the building name liek "ATB" 
				//then append sun or shade to set each classes color 
				//examle .ATBsun or .ATBshade are the two classes set up in the MAP.svg.txt and chagne it's css fill to a hex color
				$('.'+buildingNames[j]+'sun').css('fill',sun);
				$('.'+buildingNames[j]+'shade').css('fill',shade);
				
				
       		}
       		

			
//all buildings have a building class and eather a BUILDINGNAMEsun or BUILDINGNAMEshad class
//when any building is clicked this fires		
$(".building").click(function() {
	  //get the name of the building
      var name = $(this).attr('class').split(' ')[1]
      //remove the sun or shade part of the building name
      name = name.replace("sun",'');
      name = name.replace("shade",'');
      //run another function that returns the name
      //alert("building "+name+" clicked")
      for(var i = 0; i<buildingNames.length;i++ )
      {//fill-opacity:
      	if(name != buildingNames[i]){
      		$('.'+buildingNames[i]+'sun').fadeTo(1,0);
			$('.'+buildingNames[i]+'shade').fadeTo(1,0);
		}
		else{
			$("#name").html( "<h1>"+buildingNamesFull[i]+"</h1>");
			$('.'+buildingNames[i]+'sun').fadeTo(1,1);
			$('.'+buildingNames[i]+'shade').fadeTo(1,1);
		}
      }
      
      updateTablePrototype();
      	
  });
  


 
});//end of JQUERY
       		
///////////////////////////////////////////////////////////////////////////
//functions       		
       		
       		
       		
       		
function getGreen(percent){
	//if it is less than 50 it will just return full green (adusted for color intentsity,255)
	if(percent<=50)
		return 255;
	else
		//it will return some green (adusted for color intentsity)
		return (255-((percent-50)/50)*255);
}

function getRed(percent){
	//if it is less than 50 it will just return full red (adusted for color intentsity)
	if(percent<=50)
		return (percent/50)*255;
	else
		//it will return full red (adusted for color intentsity)
		return 255;
}
       		
       		
//return a hex from r, g, b vars
function HEXP(r,g,b) { 
return toHex(r)+toHex(g)+toHex(b);
}
			
function toHex(n) {
n = parseInt(n,10);
if (isNaN(n)) return "00";
n = Math.max(0,Math.min(n,255));
return "0123456789ABCDEF".charAt((n-n%16)/16)
+ "0123456789ABCDEF".charAt(n%16);
}
			
	