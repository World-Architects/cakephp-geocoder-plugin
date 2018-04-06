<?php
declare(strict_types = 1);

namespace Burzum\CakeGeocoder\Geocoder;

/**
 * Geocoder Service
 *
 * @link https://github.com/geocoder-php/Geocoder
 */
interface GeocoderInterface {

	/**
	 *
	 */
	public function reverse($coordinates);

	/**
	 * Gets the geographic information from a geolocation query string
	 *
	 * @param string $location Location / Address string
	 * @return bool|array
	 */
	public function geocode($location);
}
