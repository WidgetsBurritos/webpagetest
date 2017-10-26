# webpagetest

[![Build Status](https://travis-ci.org/WidgetsBurritos/webpagetest.svg?branch=master)](https://travis-ci.org/WidgetsBurritos/webpagetest) [![Latest Stable Version](https://poser.pugx.org/WidgetsBurritos/webpagetest/version)](https://packagist.org/packages/widgetsburritos/webpagetest) [![License](https://poser.pugx.org/widgetsburritos/webpagetest/license)](https://packagist.org/packages/widgetsburritos/webpagetest) [![composer.lock](https://poser.pugx.org/widgetsburritos/webpagetest/composerlock)](https://packagist.org/packages/widgetsburritos/webpagetest)

A php library for interacting with webpagetest.org.

*Requires PHP 5.5+*

## Usage

### Instantiating a new instance

To obtain a key, see [Request API Key](http://www.webpagetest.org/getkey.php).

```php
<?php

use WidgetsBurritos\WebPageTest\WebPageTest;

$wpt = new WebPageTest('YOUR_API_KEY');
```
To specify an alternate connection handler:

```
<?php
use WidgetsBurritos\WebPageTest\WebPageTest;

$wpt = new WebPageTest('YOUR_API_KEY', $handler);
```

To specify an alternate hosting instance:

```
<?php
use WidgetsBurritos\WebPageTest\WebPageTest;

$wpt = new WebPageTest('YOUR_API_KEY', $handler, 'https://www.example.com');
```

### Status Codes

It is recommended to use an external library, such as [Teapot](https://github.com/shrikeh/teapot) instead of hardcoding status codes.
```
<?php
use Teapot\StatusCode;

# Examples:
StatusCode::CONTINUING;          // 100 (Test Started)
StatusCode::SWITCHING_PROTOCOLS; // 101 (Test Pending)
StatusCode::OK;                  // 200 (Test Complete)
StatusCode::BAD_REQUEST;         // 400 (Test Not Found)
StatusCode::UNAUTHORIZED;        // 401 (Test Request Not Found)
StatusCode::PAYMENT_REQUIRED;    // 402 (Test Cancelled)
?>
```

^ Note: the switching protocol and payment required status checks are not a mistake. As of 10/25/2017, [webpagetest.org returns a "101 switching protocols" status for pending tests and a "402 Payment Required" status for cancelled tests.](https://github.com/WPO-Foundation/webpagetest/blob/7d5b9136f9e85e9905aa710d8b197d10356b5799/www/testStatus.inc#L315-L327)

### Running a URL test
```php
<?php

if ($response = $wpt->runTest('https://www.google.com')) {
  if ($response->statusCode == StatusCode::OK) {
    // All test info is available in $response->data.
    $test_id = $response->data->testId;
  }
}
?>
```

The library automatically populates the `k`, `f` and `url` query string parameters. Optionally, you can supply additional parameters by passing in array.

```php
<?php
$options = [
  'label' => 'My Test Label',
  'noimages' => 1,
  'mobile' => 1,
];

if ($response = $wpt->runTest('https://www.google.com', $options)) {
  if ($response->statusCode == StatusCode::OK) {
    // All test info is available in $response->data.
    $test_id = $response->data->testId;
  }
}
?>
```

[See the Web Page Test API documentation](https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis#TOC-Parameters) for more information on supported parameters.

### Getting a test's status
```php
<?php
if ($response = $wpt->getTestStatus($test_id)) {
  // All test info is available in $response->data.
  if ($response->statusCode == StatusCode::OK) {
    // Test is complete.
  }
  else if ($response->statusCode == StatusCode::CONTINUING) {
    // Test is running.
  }
  else if ($response->startCode == StatusCode::SWITCHING_PROTOCOLS) {
    // Test is waiting to start.
  }
  else if ($response->statusCode == StatusCode::PAYMENT_REQUIRED) {
    // Test has been cancelled.
  else {
    // Test failed.
  }
}
?>
```

### Getting a test's results
```php
<?php
if ($response = $wpt->getTestResults($test_id)) {
  // All test result info is available in $response->data.
  if ($response->statusCode == StatusCode::OK) {
    // Test is complete.
  }
  else if (in_array($response->statusCode, [StatusCode::CONTINUING, StatusCode::SWITCHING_PROTOCOLS])) {
    // Test is not yet complete.
  }
  else {
    // Test failed.
  }
}
?>
```

### Getting test locations
```php
<?php
if ($response = $wpt->getLocations()) {
  if ($response->statusCode == StatusCode::OK) {
    // All locations info is available in $response->data.
  }
}
?>
```

### Cancelling a Test

You can cancel a test by simply running:
```php
<?php
$wpt->cancelTest($test_id);
?>
```
