# webpagetest

[![Build Status](https://travis-ci.org/WidgetsBurritos/webpagetest.svg?branch=master)](https://travis-ci.org/WidgetsBurritos/webpagetest) [![Latest Stable Version](https://poser.pugx.org/WidgetsBurritos/webpagetest/version)](https://packagist.org/packages/widgetsburritos/webpagetest) [![License](https://poser.pugx.org/widgetsburritos/webpagetest/license)](https://packagist.org/packages/widgetsburritos/webpagetest) [![composer.lock](https://poser.pugx.org/widgetsburritos/webpagetest/composerlock)](https://packagist.org/packages/widgetsburritos/webpagetest)

A php library for interacting with webpagetest.org.

*Requires PHP 5.5+*

## Usage

### Instantiating a new instance
```php
<?php

use WidgetsBurritos\WebPageTest\WebPageTest;

$wpt = new WebPageTest('YOUR_API_KEY');
```

To obtain a key, see [Request API Key](http://www.webpagetest.org/getkey.php).

### Status Codes

It is recommended to use an external library, such as [Teapot](https://github.com/shrikeh/teapot) instead of hardcoding status codes.
```
<?php
use Teapot\StatusCode;

# Examples:
StatusCode::CONTINUING;   // 100
StatusCode::OK;           // 200
StatusCode::BAD_REQUEST;  // 400
?>
```

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
    // Test is still running.
  }
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
  else if ($response->statusCode == StatusCode::CONTINUING) {
    // Test is still running.
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
