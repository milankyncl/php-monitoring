<?php


namespace MilanKyncl\Monitoring;

/**
 * Class Watcher
 * @package MilanKyncl\Monitoring
 */

class Watcher {

	private $url;

	private $parameters = [];

	/**
	 * Register function
	 *
	 * @param string $url
	 * @param array $urlParameters
	 *
	 * @throws \Exception
	 */

	public function watch($url, $urlParameters = []) {

		$this->url = $url;

		foreach($urlParameters as $parameter => $value) {

			$this->parameters[$parameter] = $value;
		}

		if(!headers_sent())
			register_shutdown_function([$this, '_watcher']);
		else
			throw new \Exception('Headers were already sent, start watcher before sending headers, please.');
	}

	/**
	 * Internal watcher service
	 */

	private function _watcher() {

		$error = error_get_last();

		if($error['type'] == E_ERROR) {

			if(http_response_code() != 200) {

				$this->_createRequest($error);
			}
		}
	}

	/**
	 * Internal function for creating request
	 *
	 * @param array $error
	 *
	 * @return array
	 */

	private function _createRequest(Array $error) {

		$curl = curl_init();

		$this->parameters['error'] = json_encode($error);

		$i = 0;

		foreach($this->parameters as $parameter => $value) {

			if($i == 0)
				$this->url .= '?';
			else
				$this->url .= '&';

			$this->url .= urlencode($parameter) . '=' . urlencode($value);
		}

		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $this->url,
			CURLOPT_VERBOSE => 1,
			CURLOPT_HEADER => 1
		));

		$response = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		return [
			'response' => $response,
			'httpCode' => $httpCode
		];
	}

}