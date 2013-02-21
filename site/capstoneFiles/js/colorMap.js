/*
Michael Read
colorMap.js

gets info about an png image of buildings that are a grayscale
(each building is a certain gray) aka rgb(10,10,10) 
html5 image canvas is made and data is coppied with new colors  instead of grays
	replaces each color of gray with a color beetween red and green
	the color is a precent red and green red=bad , green=good, yellow=normal
overview setps:
1.get png grays from old image
2.replace grays to a color in new image
3.old image is deleted
4.new image is drawn in old image's place
5.resized to fit window

HTML layout:
<!-- Old (gray) image -->
<img id="grayImage" onload="drawImage()" src='map.png'/>

<!-- NEW Image holder -->
<div id="mapDiv">
	<!-- Html5 canvas (new image) placed here -->
</div>
*/

	
function drawImage() {
		//make new canvas
		var canvas = document.createElement('canvas');
		canvas.id = "map";
		//find holder
        var divHolder = document.getElementById("mapDiv");
		
		//place canvas in holder
		divHolder.appendChild(canvas);
	
		//get convas contax
   		var ctx = canvas.getContext("2d")
		//get image data
		var image = document.getElementById("grayImage");
		//set cavas size to the size of map.png image
		canvas.height = 435;
		canvas.width = 700;
		ctx.drawImage(image,0,0);
    
		var imgd = ctx.getImageData(0, 0, canvas.width,canvas.height)
		var pix = imgd.data;

		
	
		//current gray 
		//aka currentBuildings[0] = 10 = rgb(10,10,10) 
		var currentBuildings = new Array(10,20,30,40,50,60);
		//replace with a precent with color from 1 to 100
		//1% is effecint engery use 100% is bad use
		var newBuildings = new Array(1,73,64,100,50,30);
		
		//image options
		var colorAlpha = 100; //alpha %
		var colorIntent = 200; //intensity of colors 0 = dark, 255 = bright
		
	
		
			//loop though each pixel 
			//(pix[i] = red, pix[i+1] = green, pix[i+2] = blue, pix[i+3] = alpha)	
		for (var i = 0, n = pix.length; i <n; i += 4) {
			
				//remove semi-transparent pixels
				if(pix[i+3] > 0 && pix[i+3] < 255  ){ 
					pix[i] = 0;
					pix[i+1] = 0;
					pix[i+2] = 0;
					pix[i+3] = 0;
				}
			
				//replace grays with colors
				for (var j=0;j<currentBuildings.length;j++){ 
					
					
				   if(pix[i] == currentBuildings[j] &&pix[i+1] == currentBuildings[j] &&pix[i+2] == currentBuildings[j]){ 
						pix[i]   = 	getRed(newBuildings[j],colorIntent);
						pix[i+1] = getGreen(newBuildings[j],colorIntent);
						pix[i+2] = 0;
						pix[i+3] = (colorAlpha/100)*255;
					}
				  
				}
		}
	
		//delete old image
		document.getElementById("grayImage").style.display = "none";
		//put new image in a html5 canvas 
		ctx.putImageData(imgd,0,0);
		//resize to fit content box
		document.getElementById('map').style.width = document.getElementById('mapDiv').offsetWidth + "px";
		

}

function getGreen(percent,color){
	if(percent<=50)
		return color;
	else
		return (color-((percent-50)/50)*color);
}
function getRed(percent,color){
	if(percent<=50)
		return (percent/50)*color;
	else
		return color;
}
