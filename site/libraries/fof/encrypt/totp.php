<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/**
 * This class provides an RFC6238-compliant Time-based One Time Passwords,
 * compatible with Google Authenticator (with PassCodeLength = 6 and TimePeriod = 30).
 */
class FOFEncryptTotp {
    private $passCodeLength = 6;
    private $pinModulo;
    private $secretLength = 10;
	private $timeStep = 30;
    
	/**
	 * Initialises an RFC6238-compatible TOTP generator. Please note that this
	 * class does not implement the constraint in the last paragraph of §5.2
	 * of RFC6238. It's up to you to ensure that the same user/device does not
	 * retry validation within the same Time Step.
	 * 
	 * @param int $timeStep The Time Step (in seconds). Use 30 to be compatible with Google Authenticator.
	 * @param int $passCodeLength The generated passcode length. Default: 6 digits.
	 * @param int $secretLength The length of the secret key. Default: 10 bytes (80 bits).
	 */
    public function __construct($timeStep = 30, $passCodeLength = 6, $secretLength = 10) {
		$this->timeStep = $timeStep;
		$this->passCodeLength = $passCodeLength;
		$this->secretLength = $secretLength;
        $this->pinModulo = pow(10, $this->passCodeLength);
    }
	
	/**
	 * Get the time period based on the $time timestamp and the Time Step
	 * defined. If $time is skipped or set to null the current timestamp will
	 * be used.
	 * 
	 * @param int|null $time Timestamp
	 * @return int The time period since the UNIX Epoch
	 */
	public function getPeriod($time = null)
	{
		if(is_null($time)) {
			$time = time();
		}
		
		$period = floor($time / $this->timeStep);
		
		return $period;
	}
    
	/**
	 * Check is the given passcode $code is a valid TOTP generated using secret
	 * key $secret
	 * 
	 * @param string $secret The Base32-encoded secret key
	 * @param string $code The passcode to check
	 * @return boolean True if the code is valid
	 */
    public function checkCode($secret, $code) {
        $time = $this->getPeriod();
        for ( $i = -1; $i <= 1; $i++) {
            
            if ($this->getCode($secret, $time + $i) == $code) {
                return true;
            }
        }
        
        return false;
        
    }
    
	/**
	 * Gets the TOTP passcode for a given secret key $secret and a given UNIX
	 * timestamp $time
	 * 
	 * @param string $secret The Base32-encoded secret key
	 * @param int $time UNIX timestamp
	 * @return string
	 */
    public function getCode($secret, $time = null) {
        
		$period = $this->getPeriod($time);
		$base32 = new FOFEncryptBase32;
        $secret = $base32->decode($secret);
        
        $time = pack("N", $period);
        $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);
        
        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord(substr($hash,-1));
        $offset = $offset & 0xF;
        
        $truncatedHash = $this->hashToInt($hash, $offset) & 0x7FFFFFFF;
        $pinValue = str_pad($truncatedHash % $this->pinModulo, $this->passCodeLength, "0", STR_PAD_LEFT);
        return $pinValue;
    }
    
	/**
	 * Extracts a part of a hash as an integer
	 * 
	 * @param type $bytes
	 * @param type $start
	 * @return type
	 */
    protected  function hashToInt($bytes, $start) {
        $input = substr($bytes, $start, strlen($bytes) - $start);
        $val2 = unpack("N",substr($input,0,4));
        return $val2[1];
    }
    
	/**
	 * Returns a QR code URL for easy setup of TOTP apps like Google Authenticator
	 * 
	 * @param string $user
	 * @param string $hostname
	 * @param string $secret
	 * @return string
	 */
    public function getUrl($user, $hostname, $secret) {
        $url =  sprintf("otpauth://totp/%s@%s?secret=%s", $user, $hostname, $secret);
        $encoder = "https://chart.googleapis.com/chart?chs=200x200&chld=Q|2&cht=qr&chl=";
		$encoderURL = $encoder.urlencode($url);
        
        return $encoderURL;
        
    }
    
	/**
	 * Generates a (semi-)random Secret Key for TOTP generation
	 * 
	 * @return string
	 */
    public function generateSecret() {
        $secret = "";
        for($i = 1;  $i<= $this->secretLength;$i++) {
            $c = rand(0,255);
            $secret .= pack("c",$c);
        }
        $base32 = new FOFEncryptBase32;
        return  $base32->encode($secret);
        
        
    }
    
}

