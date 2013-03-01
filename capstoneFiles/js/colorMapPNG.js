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
3.old image is deleted (well hidden with css)
4.new image is drawn in old image's place (well its in a differnet place but thats what it looks like it does)
5.resized color image to fit window

HTML layout:
<!-- Old (gray) image -->
<img id="grayImage" onload="drawImage()" src='map.png'/>

<!-- NEW Image holder -->
<div id="mapDiv">
	<!-- Html5 canvas (new image) placed here -->
</div>
<div id=mapSize>
<!-- this is just go get sizs the map should draw to -->
</div>
*/

//called when the "grayImage" (MAP.png) has been fully loaded

function drawImage() {
		//make new html5 canvas and set its ID
		var canvas = document.createElement('canvas');
		canvas.id = "map";
		//find holder to place the canvase into
        var divHolder = document.getElementById("mapDiv");
		
		//place canvas in holder
		divHolder.appendChild(canvas);
	
		//get convas contax
   		var ctx = canvas.getContext("2d")
		//get gray image data
		var image = document.getElementById("grayImage");
		//set cavas size to the size of map.png image here
		canvas.height = 422;
		canvas.width = 679;
		ctx.drawImage(image,0,0);
    
		var imgd = ctx.getImageData(0, 0, canvas.width,canvas.height)
		var pix = imgd.data;

		//delete old image
		document.getElementById("grayImage").style.display = "none";
	
		//each buildings current blue value
		//aka currentBuildings[0] = rgb(245,245,255)
		var currentBuildings = new Array();
		var colorStart = 245;
		for(var i = 0; i<20;i++)
		{
			currentBuildings.push(colorStart);
			//set for next image(each building is speroarted by sbtracting 10 from green and red)
			colorStart -= 10;
		}
		
		//replace color blue with a precent color of green and red from 1 to 100
		//1% is red, 50% is yellow, 100% is green
		var newBuildings = new Array();
		//populate with random numbers from 1-100
		for (var j=0;j<currentBuildings.length;j++){
			newBuildings.push(Math.floor(Math.random() * 100) + 1)
		}
		//to fix some anti-alising problems
		var aaFix = 4;
		//this is the off set from the color blue that repersents the buildings color and the buildings color in the shade
		//this is set in  in MAP.png the you proabaly cant tell but ues an color picker to see 
		//in the sun the color of blues would are for currentBuildings[] rgb(245,245,255) 
		//current shade on the building is currentBuildings[] rgb(245-shadeOld ,245-shadeOld ,255)	
		var shadeOld = 5;
		
		//repaces the old blue shade by subtracting the new building by 70rgb values for both red and green
		//this makes it much darker than the orignal MAP.png's building shadeing rgb(newBuilding%-shadeOld ,newBuilding%-shadeOld ,0)
		var shadeNew = 70;
		
	
		
			//loop through each pixel 
			//(pix[i] = red, pix[i+1] = green, pix[i+2] = blue, pix[i+3] = alpha)	
		for (var i = 0, n = pix.length; i <n; i += 4) {
			//fist skip the for loop if the color is not a blue color rgb(0-255,0-255,255(only!))
			//(if its a blue color the blue value needs to be 225) and red/green can be anything
			//or non-opaque pixcels 
		   //(MAP.png is transpaerent where there is no buildings or shade on ground)
			if(pix[i+2] >= 255-aaFix && pix[i+3] == 255){
				//loop to look for each certain blue and recolor it to the new color 
				for (var j=0;j<currentBuildings.length;j++){ 
					
		
						//check if this building's pixcel is in "sun" 
						//checks for if its the right color blue color is inside the range of aaFix
						if(pix[i] >= currentBuildings[j]-aaFix && 
						pix[i] <= currentBuildings[j]+aaFix && 
						pix[i+1] >= currentBuildings[j]-aaFix && 
						pix[i+1] <= currentBuildings[j]+aaFix )
						{ //if it is a building get the right mix of red and green
							pix[i] = getRed(newBuildings[j],255);
							pix[i+1] = getGreen(newBuildings[j],255);
							//set blue to 0
							pix[i+2] = 0;
						}
						//check if building is in "shade"
						//checks for if its the right color blue color is inside the range of aaFix
						else if(
						pix[i] >= currentBuildings[j]-shadeOld-aaFix && 
						pix[i] <= currentBuildings[j]-shadeOld+aaFix && 
						pix[i+1] >= currentBuildings[j]-shadeOld-aaFix && 
						pix[i+1] <= currentBuildings[j]-shadeOld+aaFix )
						{ 
							//gets red and green and then subtracts shadeNew to make the color darker
							pix[i] = getRed(newBuildings[j],255)-shadeNew;
							pix[i+1] = getGreen(newBuildings[j],255)-shadeNew;
							pix[i+2] = 0;
						}
					}
					
				}
		}
	
		
		//put new image in a html5 canvas 
		ctx.putImageData(imgd,0,0);
		//resize to fit content box (after window resize a function will run in Chart.js to handle the map)
		document.getElementById('map').style.width = document.getElementById('mapDiv').offsetWidth + "px";
		
}

//gets percent and finds how much green/red to add red = rg(255,0) green = rg(0,255) yellow = rg(255,255)
function getGreen(percent,color){
	//if it is less than 50 it will just return full green (adusted for color intentsity)
	if(percent<=50)
		return color;
	else
		//it will return some green (adusted for color intentsity)
		return (color-((percent-50)/50)*color);
}
function getRed(percent,color){
	//if it is less than 50 it will return some red (adusted for color intentsity)
	if(percent<=50)
		return (percent/50)*color;
	else
		//it will return full red (adusted for color intentsity)
		return color;
}
