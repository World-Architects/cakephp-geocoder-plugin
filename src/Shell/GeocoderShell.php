<?php
declare(strict_types = 1);

namespace Burzum\CakeGeocoder\Shell;

use Burzum\CakeGeocoder\Geocoder\Geocoder;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

/**
 * Geocoder Shell
 */
class GeocoderShell extends Shell {

	/**
	 * Start the shell and interactive console.
	 *
	 * @return int|null
	 */
	public function main() {
		$geocoder = new Geocoder();
		$geocoder->chain();

		if (isset($this->args[0]) && $this->args[0] === 'geocode' && count($this->args) > 1) {
			$location = $this->args;
			unset($location[0]);
			$location = implode(' ', $location);
			debug($geocoder->geocode($location));

			return;
		}

		$this->out('No location provided');
	}
}
