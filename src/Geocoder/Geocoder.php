<?php
declare(strict_types = 1);

namespace Burzum\CakeGeocoder\Geocoder;

use Cake\I18n\I18n;
use Geocoder\Dumper\Dumper as DumperInterface;
use Geocoder\Dumper\GeoArray;
use Geocoder\Formatter\StringFormatter as Formatter;
use Geocoder\Location;
use Geocoder\ProviderAggregator;
use Geocoder\Provider\Chain\Chain as ChainProvider;
use Geocoder\Provider\Nominatim\Nominatim as NominatimProvider;
use Geocoder\Provider\Provider as ProviderInterface;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Cake\Client as HttpClient;
use RuntimeException;

/**
 * Geocoder Service
 *
 * @link https://github.com/geocoder-php/Geocoder
 */
class Geocoder implements GeocoderInterface {

	/**
	 * Http Client
	 *
	 * @var \Http\Client\HttpClient
	 */
	protected $_httpClient;

	/**
	 * Provider
	 *
	 * @var \Geocoder\Provider\Provider
	 */
	protected $_provider;

	/**
	 * Chain Provider
	 */
	protected $_chainProvider;

	/**
	 * Geocoder
	 */
	protected $_geocoder;

	/**
	 * Formatter
	 */
	protected $_formatter;

	/**
	 * Dumper
	 *
	 * @var \Geocoder\Dumper\Dumper
	 */
	protected $_dumper;

	/**
	 * Constructor
	 *
	 * @param null|Provider $provider Provider
	 */
	public function __construct($provider = null) {
		$this->_httpClient = new HttpClient();

		if (empty($provider)) {
			$this->_provider = new NominatimProvider(
				$this->_httpClient,
				'http://nominatim.openstreetmap.org/search/'
			);
		}

		if (!$this->_provider instanceof ProviderInterface) {
			throw new RuntimeException(sprintf(
				'Invalid provider, it must implement %s',
				ProviderInterface::class
			));
		}

		$this->_geocoder = new StatefulGeocoder($this->_provider, I18n::getLocale());
	}

	public function chain() {
		$this->_geocoder = new ProviderAggregator();

		$chain = new ChainProvider([
			$this->_provider
		]);

		$this->_geocoder->registerProvider($chain);
	}

	public function reverse($coordinates) {
		$result = $this->_geocoder->reverseQuery(ReverseQuery::fromCoordinates($coordinates));
		dd($result);
	}

	/**
	 * Gets the geographic information from a geolocation query string
	 *
	 * @param string $location
	 * @return bool|array
	 */
	public function geocode($location) {
		$collection = $this->_geocoder->geocodeQuery(GeocodeQuery::create($location));
		if ($collection->isEmpty()) {
			return false;
		}

		//debug($collection->first());
		return $this->getDumper()->dump($collection->first());
	}

	/**
	 * Gets the geo dumper
	 *
	 * @return \Geocoder\Dumper\Dumper
	 */
	public function getDumper() {
		if (empty($this->_dumper)) {
			$this->_dumper = new GeoArray();
		}

		return $this->_dumper;
	}

	/**
	 * Sets the geo dumper
	 *
	 * @param \Geocoder\Dumper\Dumper
	 * @return $this
	 */
	public function setDumper(DumperInterface $dumper) {
		$this->_dumper = $dumper;

		return $this;
	}

	public function formatLocation(Location $location) {
		return $this->getFormatter()->format($location);
	}

	/**
	 * Gets a location formatter instance
	 *
	 * @return mixed
	 */
	public function getFormatter() {
		if (empty($this->_formatter)) {
			$this->_formatter = new Formatter();
		}

		return $this->_formatter;
	}

	/**
	 * @param mixed $formatter
	 * @return $this
	 */
	public function setFormatter($formatter) {
		$this->_formatter = $formatter;

		return $this;
	}
}
