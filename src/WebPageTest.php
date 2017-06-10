<?php

namespace WidgetsBurritos\WebPageTest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

/**
 * WebPageTest class.
 */
class WebPageTest {
  private $apiKey;
  private $client;

  /**
   * Instantiates a new Web Page Test.
   */
  public function __construct($api_key, $handler = NULL) {
    $this->apiKey = $api_key;
    $client_options = [];
    if (isset($handler)) {
      $client_options['handler'] = $handler;
    }
    $this->client = new Client($client_options);
  }

  /**
   * Makes a get request on a url with specified query parameters.
   */
  private function getRequest($uri, array $query_params = []) {
    try {
      return $this->client->request('GET', $uri, ['query' => $query_params]);
    }
    catch (ClientException $e) {
      return $e->getResponse();
    }
    catch (RequestException $e) {
      return $e->getResponse();
    }
  }

  /**
   * Initializes a new test on the specified URL.
   */
  public function runTest($url) {
    $uri = 'http://www.webpagetest.org/runtest.php';
    $query_params = [
      'k' => $this->apiKey,
      'url' => $url,
      'f' => 'json',
    ];
    return $this->getRequest($uri, $query_params);
  }

  /**
   * Retrieves the status of a test with the specified id.
   */
  public function getTestStatus($test_id) {
    $uri = 'http://www.webpagetest.org/testStatus.php';
    $query_params = [
      'test' => $test_id,
      'f' => 'json',
    ];
    return $this->getRequest($uri, $query_params);
  }

  /**
   * Retrieves results of test with the specified id.
   */
  public function getTestResults($test_id) {
    $uri = 'http://www.webpagetest.org/jsonResult.php';
    $query_params = [
      'test' => $test_id,
    ];
    return $this->getRequest($uri, $query_params);
  }

}
