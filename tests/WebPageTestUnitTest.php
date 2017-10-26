<?php

use WidgetsBurritos\WebPageTest\WebPageTest;
use PHPUnit\Framework\TestCase;
use Teapot\StatusCode;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

/**
 * Tests for WebPageTest class.
 */
class WebPageTestUnitTest extends TestCase {

  /**
   * Test runTest() functionality.
   */
  public function testRunTest() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(StatusCode::OK, [], file_get_contents(__DIR__ . '/fixtures/runtest-success.json')),
      new Response(StatusCode::BAD_REQUEST, [], file_get_contents(__DIR__ . '/fixtures/runtest-badkey.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a successful connection.
    $response = $wpt->runTest('https://www.google.com');
    $this->assertEquals(StatusCode::OK, $response->statusCode);
    $expected_url = 'http://www.webpagetest.org/jsonResult.php?test=ABC123';
    $this->assertEquals($expected_url, $response->data->jsonUrl);

    // Test a bad API key.
    $response = $wpt->runTest('https://www.google.com');
    $this->assertEquals(StatusCode::BAD_REQUEST, $response->statusCode);
    $this->assertEquals('Invalid API Key', $response->statusText);

    // Test a connection failure.
    $response = $wpt->runTest('https://www.google.com');
    $this->assertNull($response);
  }

  /**
   * Test getTestStatus() functionality.
   */
  public function testGetTestStatus() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(StatusCode::OK, [], file_get_contents(__DIR__ . '/fixtures/teststatus-complete.json')),
      new Response(StatusCode::CONTINUING, [], file_get_contents(__DIR__ . '/fixtures/teststatus-started.json')),
      new Response(StatusCode::PAYMENT_REQUIRED, [], file_get_contents(__DIR__ . '/fixtures/teststatus-cancelled.json')),
      new Response(StatusCode::BAD_REQUEST, [], file_get_contents(__DIR__ . '/fixtures/teststatus-invalid.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a completed test.
    $response = $wpt->getTestStatus('ABC123');
    $this->assertEquals(StatusCode::OK, $response->statusCode);
    $this->assertEquals('Test Complete', $response->statusText);
    $this->assertEquals(1, $response->data->testsCompleted);

    // Test a running test.
    $response = $wpt->getTestStatus('ABC123');
    $this->assertEquals(StatusCode::CONTINUING, $response->statusCode);
    $this->assertEquals('Test Started 7 seconds ago', $response->statusText);
    $this->assertEquals(0, $response->data->testsCompleted);

    // Test a cancelled test.
    $response = $wpt->getTestStatus('ABC123');
    $this->assertEquals(StatusCode::PAYMENT_REQUIRED, $response->statusCode);
    $this->assertEquals('Test Cancelled', $response->statusText);

    // Test an invalid test.
    $response = $wpt->getTestStatus('invalid');
    $this->assertEquals(StatusCode::BAD_REQUEST, $response->statusCode);
    $this->assertEquals('Test not found', $response->statusText);

    // Test a connection failure.
    $response = $wpt->getTestStatus('ABC123');
    $this->assertNull($response);
  }

  /**
   * Test getTestResults() functionality.
   */
  public function testGetTestResults() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(StatusCode::OK, [], file_get_contents(__DIR__ . '/fixtures/testresults-complete.json')),
      new Response(StatusCode::CONTINUING, [], file_get_contents(__DIR__ . '/fixtures/testresults-started.json')),
      new Response(StatusCode::BAD_REQUEST, [], file_get_contents(__DIR__ . '/fixtures/testresults-invalid.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a completed test.
    $response = $wpt->getTestResults('ABC123');
    $this->assertEquals(StatusCode::OK, $response->statusCode);
    $this->assertEquals('https://www.google.com', $response->data->testUrl);
    $this->assertEquals(1, count($response->data->runs));
    $this->assertEquals(3834, $response->data->average->firstView->fullyLoaded);
    $this->assertEquals(3390, $response->data->average->repeatView->fullyLoaded);

    // Test a running test.
    $response = $wpt->getTestResults('ABC123');
    $this->assertEquals(StatusCode::CONTINUING, $response->statusCode);
    $this->assertEquals('Test Started 15 seconds ago', $response->statusText);

    // Test an invalid test.
    $response = $wpt->getTestResults('invalid');
    $this->assertEquals(StatusCode::BAD_REQUEST, $response->statusCode);
    $this->assertEquals('Test not found', $response->statusText);

    // Test a connection failure.
    $response = $wpt->getTestResults('ABC123');
    $this->assertNull($response);
  }

  /**
   * Test getLocations() functionality.
   */
  public function testGetLocations() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(StatusCode::OK, [], file_get_contents(__DIR__ . '/fixtures/locations.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a completed test.
    $response = $wpt->getLocations();
    $this->assertEquals(StatusCode::OK, $response->statusCode);
    $this->assertObjectHasAttribute('Dulles_MotoE', $response->data);

    // Test a connection failure.
    $response = $wpt->getLocations();
    $this->assertNull($response);
  }

  /**
   * Test alternate hostnames.
   */
  public function testAlternateHost() {
    $wpt = new WebPageTest('phonykey', NULL);
    $this->assertEquals('http://www.webpagetest.org', $wpt->getBaseUrl());

    $wpt = new WebPageTest('phonykey', NULL, 'http://www.example.com/wpt');
    $this->assertEquals('http://www.example.com/wpt', $wpt->getBaseUrl());
  }

}
