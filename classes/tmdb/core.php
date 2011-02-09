<?php defined('SYSPATH') or die('No direct script access.');

/**
 * A class to access and retieve information from the fantastic TMDb API.
 * For more information the api, obtaining and api key and the terms of use,
 * please visit http://api.themoviedb.org/2.1.
 *
 * @author Erik van der Wal <erikvdwal@gmail.com>
 * @copyright Erik van der Wal, 8 February, 2011
 * @package Tmdb
 **/

class Tmdb_Core
{
	/**
	 * API base URL
	 *
	 * @var string
	 */
	const BASE_URL = 'http://api.themoviedb.org/';

	/**
	 * API version
	 * 
	 * @var string
	 */
	const API_VERSION = '2.1';

	/**
	 * JSON format
	 *
	 * @var string
	 */
	const FORMAT_JSON = 'json';

	/**
	 * XML format
	 *
	 * @var string
	 */
	const FORMAT_XML = 'xml';

	/**
	 * YAML format
	 *
	 * @var string
	 */
	const FORMAT_YAML = 'yaml';
	
	/**
	 * HTTP POST method
	 */
	const METHOD_POST = 'POST';
	
	/**
	 * HTTP GET method
	 */
	const METHOD_GET = 'GET';

	/**
	 * API key
	 *
	 * @var string api key
	 */
	private $apikey;

	/**
	 * Format to be retrieve
	 *
	 * @var string format
	 */
	private $format = 'json';

	/**
	 * Perferred language
	 *
	 * @var string
	 */
	private $language = 'en';
	
	/**
	 * Constructor.
	 *
	 * @param string $apikey api key
	 */
	public function __construct($apikey = null)
	{
		if (isset($apikey)) {
			$this->set_api_key($apikey);
		}
	}

	/**
	 * Factory method
	 *
	 * @param string $apikey api key
	 * @return TMDb
	 */
	public static function factory($apikey = null)
	{
		return new Tmdb($apikey);
	}

	/**
	 * Set the API key
	 *
	 * @param string $apikey api key
	 * @return Kohana_TMDb
	 */
	public function set_api_key($apikey)
	{
		$this->apikey = (string) $apikey;
		return $this;
	}

	/**
	 * Get the API key
	 *
	 * @return string api key
	 */
	public function get_api_key()
	{
		return $this->apikey;
	}

	/**
	 * Set format to request. This can be set to either 'json',
	 * 'xml' or 'yaml'.
	 *
	 * @param string $format format
	 * @return Kohana_TMDb
	 */
	public function set_format($format)
	{
		if (in_array($format, array(self::FORMAT_JSON, self::FORMAT_XML, self::FORMAT_YAML))) {
			$this->format = $format;
		}
		return $this;
	}

	/**
	 * Get the set format
	 *
	 * @return string
	 */
	public function get_format()
	{
		return $this->format;
	}

	/**
	 * Set the preferred language
	 *
	 * @param string $language language code
	 * @return Kohana_TMDb
	 */
	public function set_language($language)
	{
		$this->language = (string) $language;
		return $this;
	}

	/**
	 * Get the current language setting
	 *
	 * @return string
	 */
	public function get_language()
	{
		return $this->language;
	}

	/**
	 * Perform a search. The name may contain titles, as well release years.
	 *
	 * @param string $query search query
	 * @return array|simple_xml_object|string
	 */
	public function search_movie($name)
	{
		return $this->request('Movie.search', (string) $name);
	}

	/**
	 * Get info on a specific movie. The supplied id may be either
	 * the TMDb id or the IMDb id.
	 *
	 * @param string $id movie id
	 * @return array|simple_xml_object|string
	 */
	public function movie_info($id)
	{
		return $this->request('Movie.getInfo', (int) $id);
	}

	/**
	 * Get all images for a specific movie.
	 *
	 * @param string $id movie id
	 * @return array|simple_xml_object|string
	 */
	public function movie_images($id)
	{
		$data = $this->request('Movie.getImages', (int) $id);
		return ($data[0] ? $data[0] : null);
	}

	/**
	 * Get last added movie.
	 *
	 * @return array|simple_xml_object|string
	 */
	public function latest_movie()
	{
		$data = $this->request('Movie.getLatest', null);
		return $data[0];
	}

	/***
	 * Get available translations for a specific movie.
	 *
	 * @return array|simple_xml_object|string
	 */
	public function movie_translations($id)
	{
		$data = $this->request('Movie.getTranslations', (int) $id);
		return ($data[0] ? $data[0] : null);
	}

	/**
	 * Get the timestamp of the last update for one or multiple
	 * movies.
	 *
	 * This is useful if you've already called the object
	 * sometime in the past and simply want to do a quick check for
	 * updates. This method supports calling anywhere between 1 and 50
	 * items at a time.
	 *
	 * @param array|string $ids id or array with id's
	 * @return array|simple_xml_object|string
	 */
	public function movie_version(array $ids)
	{
		return $this->request('Movie.getVersion', implode(',', $ids));
	}

	/**
	 * Browse the movie database. This method is probably the most
	 * powerful single method on the entire TMDb API.
	 *
	 * @param string $order_by field to order by: rating, title or released
	 * @param string $order direction to order in: asc or desc
	 * @param int $page page number
	 * @param int $per_page items per page
	 * @param array $params extra parameters
	 * @return array|simple_xml_object|string
	 */
	public function browse_movie($order_by = 'rating', $order = 'asc', $page = 1, $per_page = 10, $params = array())
	{
		$params = (is_array($params) ? $params : array());
		if (in_array($order_by, array('rating', 'release', 'title')) && in_array($order, array('asc', 'desc'))) {
			$params = array_merge($params, array(
				'order_by'  => (string) $order_by,
				'order'		=> (string) $order,
				'page'		=> (int) $page,
				'per_page'	=> (int) $per_page
			));
			return $this->request('Movie.browse', $params);
		} else {
			return null;
		}
	}

	/**
	 * Lookup a movie by it's IMDb id.
	 *
	 * @param string $id imdb id
	 * @return array|simple_xml_object|string
	 */
	public function imdb_lookup($id)
	{
		$data = $this->request('Movie.imdbLookup', (string) $id);
		return ((!$data[0] instanceof stdClass) ? null : $data[0]);
	}

	/**
	 * Get info about a specific person. This method is used to retrieve
	 * the full filmography, known movies, images and things like
	 * birthplace for a specific person in the TMDb database.
	 *
	 * @param int $id person id
	 * @return array|simple_xml_object|string
	 */
	public function person_info($id)
	{
		$data = $this->request('Person.getInfo', (string) $id);
		return ($data[0] ? $data[0] : null);
	}

	/**
	 * Returns last added person
	 *
	 * @return array|simple_xml_object|string
	 */
	public function latest_person()
	{
		$data = $this->request('Person.getLatest', null);
		return $data[0];
	}

	/**
	 * Get the timestamp of the last update for one or multiple
	 * people.
	 *
	 * This is useful if you've already called the object sometime
	 * in the past and simply want to do a quick check for updates.
	 * This method supports calling anywhere between 1 and 50
	 * items at a time.
	 *
	 * @param array $ids person id or id's
	 * @return array
	 */
	public function person_version(array $ids)
	{
		return $this->request('Person.getVersion', implode(',', $ids));
	}

	/**
	 * Search for an actor, actress or production member.
	 *
	 * @param string $name
	 * @return array
	 */
	public function search_person($name)
	{
		return $this->request('Person.search', (string) $name);
	}
	
	public function movie_add_rating($id, $rating, $session)
	{
		$params = array(
			'id'          => (int) $id,
			'rating'      => (float) $rating,
			'session_key' => (string) $session_key
		);
		
		$this->request('Movie.addRating', $params, null, self::METHOD_POST);
	}
		
	/**
	 * Get a token
	 *
	 * @return string
	 */
	public function get_token()
	{
		$data = $this->request('Auth.getToken');
		return (string) $data->token;
	}

	/**
	 * Get the url for a user to grant access th their account.
	 *
	 * @param string $token access token
	 * @return string url to redirect to
	 */
	public function auth_url($token)
	{
		return sprintf('http://themoviedb.org/auth/%s', $token);
	}
	
	/**
	 * Get session
	 *
	 * @param string $token token
	 * @return array|simple_xml_object|string
	 */
	public function get_session($token)
	{
		return $this->request('Auth.getSession', $token);
	}

	/**
	 * Request data from the specified URL and convert it to
	 * an array for json data or simple_xml_object for xml data.
	 *
	 * @param string $url to call
	 * @return array|simple_xml_object|string
	 */
	private function request($function, $params = null, $format = null, $method = 'GET')
	{
		$format = (isset($format) ? $format : $this->get_format());

		if ($method == self::METHOD_GET) {
			$url = self::BASE_URL . self::API_VERSION . '/' . (string) $function
				 . ((substr($function, 0, 4) != 'Auth') ? '/' . $this->get_language() : '')
				 . '/' . $this->get_format()
				 . '/' . $this->get_api_key();

			if (isset($params)) {
				$url .= (is_array($params) ? '?' . http_build_query($params, null, '&amp;') : '/' . urlencode($params));
			}

			$data = Remote::get($url);
			
		} else {

			$url = self::BASE_URL . self::API_VERSION . '/' . (string) $function;

			$params = (isset($params) ? (array) $params : array());
			$params = array_merge($params, array(
				'api_key' => $this->get_api_key(),
				'type'	  => $format
			));

			Remote::get($url, array('CURL_POST' => true, 'CURL_POSTFIELDS' => $params));
		}

		switch ($this->get_format()) {
			case self::FORMAT_JSON:
				return json_decode($data);
				break;

			case self::FORMAT_XML:
				return simplexml_load_string($data);
				break;

			case self::FORMAT_YAML:
			default:
				return $data;
				break;
		}
	}
}