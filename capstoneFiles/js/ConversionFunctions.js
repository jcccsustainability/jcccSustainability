// The purpose of this class is to provide functions for converting different numbers
// into color values for the main map on the Sustainability page
// It will also provide other conversion functions

// These numbers may not be what the college has
var KW_TO_CO2 = 0.00070555; // Found this number online
var KW_TO_COAL = 1.07; // Found this number online
var COST_IN_DOLLARS = 0.0812; // Number hopefully corrected

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
	else if(colorKW <= 75)
	{
		return 1;
	}
	else if(colorKW <= 150)
	{
		return 2;
	}
	else if(colorKW <= 225)
	{
		return 3;
	}
	else if(colorKW <= 300)
	{
		return 4;
	}
	else if(colorKW <= 375)
	{
		return 5;
	}
	else if(colorKW <= 450)
	{
		return 6;
	}
	else if(colorKW <= 525)
	{
		return 7;
	}
	else if(colorKW <= 600)
	{
		return 8;
	}
	else if(colorKW <= 675)
	{
		return 9;
	}
	else
	{
		return 10;
	} // end if
} // end KWColor

// Converts temp into a number for the color selection
function TempColor(colorTemp)
{
	if(colorTemp <= 58) // For no or bad data
	{
		return 0;
	}
	else if(colorTemp <= 63)
	{
		return 1;
	}
	else if(colorTemp <= 66)
	{
		return 2;
	}
	else if(colorTemp <= 69)
	{
		return 3;
	}
	else if(colorTemp <= 72)
	{
		return 4;
	}
	else if(colorTemp <= 75)
	{
		return 5;
	}
	else if(colorTemp <= 78)
	{
		return 6;
	}
	else if(colorTemp <= 81)
	{
		return 7;
	}
	else if(colorTemp <= 84)
	{
		return 8;
	}
	else if(colorTemp <= 87)
	{
		return 9;
	}
	else
	{
		return 10;
	} // end if
} // end TempColor

// Converts CO2 tons into a number for the color selection
function CO2Color(colorCO2)
{
	if(colorCO2 <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorCO2 <= 0.05)
	{
		return 1;
	}
	else if(colorCO2 <= 0.1)
	{
		return 2;
	}
	else if(colorCO2 <= 0.15)
		return 3;
	}
	else if(colorCO2 <= 0.2)
	{
		return 4;
	}
	else if(colorCO2 <= 0.25)
	{
		return 5;
	}
	else if(colorCO2 <= 0.3)
	{
		return 6;
	}
	else if(colorCO2 <= 0.35)
	{
		return 7;
	}
	else if(colorCO2 <= 0.4)
	{
		return 8;
	}
	else if(colorCO2 <= 0.45)
	{
		return 9;
	}
	else
	{
		return 10;
	} // end if
} // end CO2Color

// Converts money into a number for the color selection
function MoneyColor(colorMoney)
{
	if(colorMoney <= 0) // For no or bad data
	{
		return 0;
	}
	else if(colorMoney <= 500)
	{
		return 1;
	}
	else if(colorMoney <= 1000)
	{
		return 2;
	}
	else if(colorMoney <= 1500)
	{
		return 3;
	}
	else if(colorMoney <= 2000)
	{
		return 4;
	}
	else if(colorMoney <= 2500)
	{
		return 5;
	}
	else if(colorMoney <= 3000)
	{
		return 6;
	}
	else if(colorMoney <= 3500)
	{
		return 7;
	}
	else if(colorMoney <= 4000)
	{
		return 8;
	}
	else if(colorMoney <= 4500)
	{
		return 9;
	}
	else
	{
		return 10;
	} // end if
} // end MoneyColor

// Selects a color based on the input number
function ColorSelect(colorNum)
{
	switch(colorNum)
	{
		case 1:
		{
			/////// color
			break;
		}
		case 2:
		{
			/////// color
			break;
		}
		case 3:
		{
			/////// color
			break;
		}
		case 4:
		{
			/////// color
			break;
		}
		case 5:
		{
			/////// color
			break;
		}
		case 6:
		{
			/////// color
			break;
		}
		case 7:
		{
			/////// color
			break;
		}
		case 8:
		{
			/////// color
			break;
		}
		case 9:
		{
			/////// color
			break;
		}
		case 10:
		{
			/////// color
			break;
		}
		default:
		{
			/////// color
		}
	} // end switch
} // end ColorSelect
