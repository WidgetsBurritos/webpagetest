# webpagetest

A php library for interacting with webpagetest.org.

*Requires PHP 7+*

## Usage

### Instantiating a new instance
```php
<?php

use WidgetsBurritos\WebPageTest\WebPageTest;

$wpt = new WebPageTest('YOUR_API_KEY');
```

### Running a URL test
```php
<?php

if ($response = $wpt->runTest('https://www.google.com')) {

  // Parse the response.
  $result = json_decode($response->getBody());

  if ($result->statusCode == 200) {
    $test_id = $result->data->testId;
  }

}
```

### Getting a test's status
```php
<?php
if ($response = $wpt->getTestStatus($test_id)) {
  $result = json_decode($response->getBody());

  if ($result->statusCode == 200) {
    // Test is complete.
  }
  else if ($result->statusCode == 100) {
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
  $result = json_decode($response->getBody());

  if ($result->statusCode == 200) {
    // Test is complete.
    // $result->data contains all the test results.
  }
  else if ($result->statusCode == 100) {
    // Test is still running.
  }
  else {
    // Test failed.
  }
}
?>
```
