<?php

use App\OAuth2\Server;
use App\Restler\Restler;
use Luracast\Restler\Filter\RateLimit;
use Luracast\Restler\Resources;

require __DIR__ . '/../bootstrap/autoload.php';

$isProduction = getenv('APP_ENV') == 'production';

$api = new Restler($isProduction, false, $app);
$api->setAPIVersion(1);

// $api->setSupportedFormats('JsonFormat', 'XmlFormat');
$api->setOverridingFormats('JsonFormat', 'HtmlFormat', 'UploadFormat');

foreach ($apiResources as $path => $resource) {
    $api->addAPIClass($resource, $path);
}

$api->addFilterClass(RateLimit::class);

if(!$isProduction) {
    Resources::$useFormatAsExtension = false;
    Resources::$hideProtected = false;
    $api->addAPIClass(Resources::class);
}

$api->addAuthenticationClass(Server::class, 'oauth');

$api->handle();
