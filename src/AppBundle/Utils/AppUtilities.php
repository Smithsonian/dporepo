<?php
// src/AppBundle/Utils/AppUtilities.php
namespace AppBundle\Utils;

class AppUtilities
{
	/**
	 * Dumper
	 *
	 * For debugging. Outputs data using var_dump(), encapsulated by the <pre> tag, with the option to die() or let it ride.
	 * If an IP address is passed, then only that IP address will be able to view the output.
	 *
	 * @param   mixed   $data         The data value
	 * @param   bool    $die          The data value
	 * @param   string  $ip_address   The data value
	 * @return  mixed   The formatted data
	 */
	public function dumper($data = false, $die = true, $ip_address=false){
		if(!$ip_address || $ip_address == $_SERVER["REMOTE_ADDR"]){
			echo '<pre>';
			var_dump($data);
			echo '</pre>';
			if($die) die();
		}
	}
}