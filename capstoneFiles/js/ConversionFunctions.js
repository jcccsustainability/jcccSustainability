// The purpose of this class is to provide functions for converting different numbers
// into color values for the main map on the Sustainability page
// It will also provide other conversion functions

// These numbers may not be what the college has
var KW_TO_CO2 = 0.00070555; // Found this number online
var KW_TO_COAL = 1.07; // Found this number online
var COST_IN_DOLLARS = 0.0812; // Number hopefully corrected
var KW_COLOR = (750.0 / 10); // For KW color, To change interval update number on the left, the 10 is for the color selection
var TEMP_COLOR = 58.0; // For temp color
var TEMP_COLOR_RANGE = 3.0; // The range between temperature values
var CO2_COLOR = (0.5 / 10); // For CO2 color, To change interval update number on the left, the 10 is for the color selection
var MONEY_COLOR = (60.0 / 10); // For Money color, To change interval update number on the left, the 10 is for the color selection
var COAL_COLOR = (800.0 / 10); // For Coal color, To change interval update number on the left, the 10 is for the color selection
var AVG_TEMP_COLOR = (80.0 / 10); // For Average Temperature color, To change interval update number on the left, the 10 is for the color selection

// Converts kilowatts into CO2 metric tons
function KWtoCO2(co2)
{
	return co2 * KW_TO_CO2;
} // end KWtoCO2

// Converts kilowatts into money
function KWtoMoney(money)
{
	return money * COST_IN_DOLLARS;
} // end KWtoMoney

// Converts kilowatts into coal
function KWtoCoal(coal)
{
	return coal * KW_TO_COAL;
} // end KWtoCoal

// These function will output a number based on the input number to allow us to change
// the color of the buildings on the map

// Converts kilowatts into a number for the color selection
function KWColor(colorKW)
{
	if(colorKW <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorKW <= KW_COLOR)
	{
		return 10;
	}
	else if(colorKW <= KW_COLOR * 2)
	{
		return 20;
	}
	else if(colorKW <= KW_COLOR * 3)
	{
		return 30;
	}
	else if(colorKW <= KW_COLOR * 4)
	{
		return 40;
	}
	else if(colorKW <= KW_COLOR * 5)
	{
		return 50;
	}
	else if(colorKW <= KW_COLOR * 6)
	{
		return 60;
	}
	else if(colorKW <= KW_COLOR * 7)
	{
		return 70;
	}
	else if(colorKW <= KW_COLOR * 8)
	{
		return 80;
	}
	else if(colorKW <= KW_COLOR * 9)
	{
		return 90;
	}
	else
	{
		return 100;
	} // end if
} // end KWColor

// Converts temp into a number for the color selection
function TempColor(colorTemp)
{
	if(colorTemp <= TEMP_COLOR) // For no or bad data
	{
		return 0;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE)))
	{
		return 10;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 2)))
	{
		return 20;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 3)))
	{
		return 30;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 4)))
	{
		return 40;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 5)))
	{
		return 50;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 6)))
	{
		return 60;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 7)))
	{
		return 70;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 8)))
	{
		return 80;
	}
	else if(colorTemp <= (TEMP_COLOR + (TEMP_COLOR_RANGE * 9)))
	{
		return 90;
	}
	else
	{
		return 100;
	} // end if
} // end TempColor

// Converts CO2 tons into a number for the color selection
function CO2Color(colorCO2)
{
	if(colorCO2 <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorCO2 <= CO2_COLOR)
	{
		return 10;
	}
	else if(colorCO2 <= CO2_COLOR * 2)
	{
		return 20;
	}
	else if(colorCO2 <= CO2_COLOR * 3)
	{
		return 30;
	}
	else if(colorCO2 <= CO2_COLOR * 4)
	{
		return 40;
	}
	else if(colorCO2 <= CO2_COLOR * 5)
	{
		return 50;
	}
	else if(colorCO2 <= CO2_COLOR * 6)
	{
		return 60;
	}
	else if(colorCO2 <= CO2_COLOR * 7)
	{
		return 70;
	}
	else if(colorCO2 <= CO2_COLOR * 8)
	{
		return 80;
	}
	else if(colorCO2 <= CO2_COLOR * 9)
	{
		return 90;
	}
	else
	{
		return 100;
	} // end if
} // end CO2Color

// Converts money into a number for the color selection
function MoneyColor(colorMoney)
{
	if(colorMoney <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorMoney <= MONEY_COLOR)
	{
		return 10;
	}
	else if(colorMoney <= MONEY_COLOR * 2)
	{
		return 20;
	}
	else if(colorMoney <= MONEY_COLOR * 3)
	{
		return 30;
	}
	else if(colorMoney <= MONEY_COLOR * 4)
	{
		return 40;
	}
	else if(colorMoney <= MONEY_COLOR * 5)
	{
		return 50;
	}
	else if(colorMoney <= MONEY_COLOR * 6)
	{
		return 60;
	}
	else if(colorMoney <= MONEY_COLOR * 7)
	{
		return 70;
	}
	else if(colorMoney <= MONEY_COLOR * 8)
	{
		return 80;
	}
	else if(colorMoney <= MONEY_COLOR * 9)
	{
		return 90;
	}
	else
	{
		return 100;
	} // end if
} // end MoneyColor

// Converts Coal pounds into a number for the color selection
function CoalColor(colorCoal)
{
	if(colorCoal <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorCoal <= COAL_COLOR)
	{
		return 10;
	}
	else if(colorCoal <= COAL_COLOR * 2)
	{
		return 20;
	}
	else if(colorCoal <= COAL_COLOR * 3)
	{
		return 30;
	}
	else if(colorCoal <= COAL_COLOR * 4)
	{
		return 40;
	}
	else if(colorCoal <= COAL_COLOR * 5)
	{
		return 50;
	}
	else if(colorCoal <= COAL_COLOR * 6)
	{
		return 60;
	}
	else if(colorCoal <= COAL_COLOR * 7)
	{
		return 70;
	}
	else if(colorCoal <= COAL_COLOR * 8)
	{
		return 80;
	}
	else if(colorCoal <= COAL_COLOR * 9)
	{
		return 90;
	}
	else
	{
		return 100;
	} // end if
} // end CoalColor

// Converts Average Temperature into a number for the color selection
function AvgTempColor(colorAvgTemp)
{
	if(colorAvgTemp <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR)
	{
		return 10;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 2)
	{
		return 20;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 3)
	{
		return 30;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 4)
	{
		return 40;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 5)
	{
		return 50;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 6)
	{
		return 60;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 7)
	{
		return 70;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 8)
	{
		return 80;
	}
	else if(colorAvgTemp <= AVG_TEMP_COLOR * 9)
	{
		return 90;
	}
	else
	{
		return 100;
	} // end if
} // end AvgTempColor