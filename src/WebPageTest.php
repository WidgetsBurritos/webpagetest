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
  private $baseUrl;

  /**
   * Instantiates a new Web Page Test.
   */
  public function __construct($api_key, $handler = NULL, $base_url = 'http://www.webpagetest.org') {
    $this->apiKey = $api_key;
    $this->baseUrl = $base_url;
    $client_options = [];
    if (isset($handler)) {
      $client_options['handler'] = $handler;
    }
    $this->client = new Client($client_options);
  }

  /**
   * Retrieves the base url.
   */
  public function getBaseUrl() {
    return $this->baseUrl;
  }

  /**
   * Makes a get request on a url with specified query parameters.
   */
  private function getRequest($uri, array $query_params = [], $expect_json = TRUE) {
    try {
      $response = $this->client->request('GET', $uri, ['query' => $query_params]);
    }
    catch (ClientException $e) {
      $response = $e->getResponse();
    }
    catch (RequestException $e) {
      $response = $e->getResponse();
    }

    if ($response) {
      $body = (string) $response->getBody();
      if ($expect_json) {
        return json_decode($body);
      }
      return $body;
    }

    return NULL;
  }

  /**
   * Initializes a new test on the specified URL.
   */
  public function runTest($url, array $options = []) {
    $uri = "{$this->baseUrl}/runtest.php";
    $query_params = [
      'k' => $this->apiKey,
      'url' => $url,
      'f' => 'json',
    ];

    return $this->getRequest($uri, $query_params + $options);
  }

  /**
   * Retrieves the status of a test with the specified id.
   */
  public function getTestStatus($test_id, array $options = []) {
    $uri = "{$this->baseUrl}/testStatus.php";
    $query_params = [
      'test' => $test_id,
      'f' => 'json',
    ];

    return $this->getRequest($uri, $query_params + $options);
  }

  /**
   * Retrieves results of test with the specified id.
   */
  public function getTestResults($test_id, array $options = []) {
    $uri = "{$this->baseUrl}/jsonResult.php";
    $query_params = [
      'test' => $test_id,
    ];

    return $this->getRequest($uri, $query_params + $options);
  }

  /**
   * Retrieves list of locations.
   */
  public function getLocations(array $options = []) {
    $uri = "{$this->baseUrl}/getLocations.php";
    $query_params = [
      'f' => 'json',
    ];

    return $this->getRequest($uri, $query_params + $options);
  }

  /**
   * Cancels a test.
   */
  public function cancelTest($test_id, array $options = []) {
    $uri = "{$this->baseUrl}/cancelTest.php";
    $query_params = [
      'k' => $this->apiKey,
      'test' => $test_id,
    ];

    return $this->getRequest($uri, $query_params + $options, FALSE);
  }

}
