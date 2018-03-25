<?php


namespace MilanKyncl\Monitoring;


class Watcher {

	private $url;

	/**
	 * Register function
	 */

	public function watch($url, $configParameters = []) {

		$this->url = $url;

		register_shutdown_function([$this, '_watcher']);
	}

	private function _watcher() {

		$error = error_get_last();

		if($error['type'] == E_ERROR) {

			if(http_response_code() != 200) {

				$this->_createRequest($error);
			}
		}
	}

	private function _createRequest(Array $error) {

		$curl = curl_init();

		/**
		 * TODO: Resolve URL
		 */

		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $this->url,
			CURLOPT_VERBOSE => 1,
			CURLOPT_HEADER => 1
		));

		$this->_response = curl_exec($curl);

		$this->_httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

	}

}