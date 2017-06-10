<?php

use WidgetsBurritos\WebPageTest\WebPageTest;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

/**
 * Tests for WebPageTest class.
 */
class WebPageTestUnit extends TestCase {

  /**
   * Test runTest() functionality.
   */
  public function testRunTest() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/runtest-success.json')),
      new Response(400, [], file_get_contents(__DIR__ . '/fixtures/runtest-badkey.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a successful connection.
    $response = $wpt->runTest('https://www.google.com');
    $result = json_decode($response->getBody());
    $this->assertEquals(200, $result->statusCode);
    $expected_url = 'http://www.webpagetest.org/jsonResult.php?test=ABC123';
    $this->assertEquals($expected_url, $result->data->jsonUrl);

    // Test a bad API key.
    $response = $wpt->runTest('https://www.google.com');
    $result = json_decode($response->getBody());
    $this->assertEquals(400, $result->statusCode);
    $this->assertEquals('Invalid API Key', $result->statusText);

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
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/teststatus-complete.json')),
      new Response(100, [], file_get_contents(__DIR__ . '/fixtures/teststatus-started.json')),
      new Response(400, [], file_get_contents(__DIR__ . '/fixtures/teststatus-invalid.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a completed test.
    $response = $wpt->getTestStatus('ABC123');
    $result = json_decode($response->getBody());
    $this->assertEquals(200, $result->statusCode);
    $this->assertEquals('Test Complete', $result->statusText);
    $this->assertEquals(1, $result->data->testsCompleted);

    // Test a running test.
    $response = $wpt->getTestStatus('ABC123');
    $result = json_decode($response->getBody());
    $this->assertEquals(100, $result->statusCode);
    $this->assertEquals('Test Started 7 seconds ago', $result->statusText);
    $this->assertEquals(0, $result->data->testsCompleted);

    // Test an invalid test.
    $response = $wpt->getTestStatus('invalid');
    $result = json_decode($response->getBody());
    $this->assertEquals(400, $result->statusCode);
    $this->assertEquals('Test not found', $result->statusText);

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
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/testresults-complete.json')),
      new Response(100, [], file_get_contents(__DIR__ . '/fixtures/testresults-started.json')),
      new Response(400, [], file_get_contents(__DIR__ . '/fixtures/testresults-invalid.json')),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    $wpt = new WebPageTest('phonykey', $handler);

    // Test a completed test.
    $response = $wpt->getTestResults('ABC123');
    $result = json_decode($response->getBody());
    $this->assertEquals(200, $result->statusCode);
    $this->assertEquals('https://www.google.com', $result->data->testUrl);
    $this->assertEquals(1, count($result->data->runs));
    $this->assertEquals(3834, $result->data->average->firstView->fullyLoaded);
    $this->assertEquals(3390, $result->data->average->repeatView->fullyLoaded);

    // Test a running test.
    $response = $wpt->getTestResults('ABC123');
    $result = json_decode($response->getBody());
    $this->assertEquals(100, $result->statusCode);
    $this->assertEquals('Test Started 15 seconds ago', $result->statusText);

    // Test an invalid test.
    $response = $wpt->getTestResults('invalid');
    $result = json_decode($response->getBody());
    $this->assertEquals(400, $result->statusCode);
    $this->assertEquals('Test not found', $result->statusText);

    // Test a connection failure.
    $response = $wpt->getTestResults('ABC123');
    $this->assertNull($response);
  }

}
