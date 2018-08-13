<?php
require __DIR__ . '/vendor/autoload.php';

$accessToken = getToken();

$client = new GuzzleHttp\Client([
    'base_uri' => 'https://api.shutterstock.com/v2/',
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $accessToken,
    ],
]);

$path = __DIR__ . '/images/';

$filePattern = sprintf('%s/shutterstock_*.notredownload', rtrim($path, '/'));
$files = glob($filePattern);

if (!count($files)) {
    exit;
}

$pattern = '/shutterstock_([0-9]+).notredownload/';

foreach (array_chunk($files, 50) as $chunk) {
    $imageIds = [];
    $deleteFiles = [];

    foreach ($chunk as $file) {
        if (preg_match($pattern, $file, $matches) !== 1) {
            continue;
        }

        $imageIds[] = $matches[1];
        $deleteFiles[] = $file;
    }

    batchDownloadImages($client, $imageIds, $path);

    foreach ($deleteFiles as $file) {
        unlink($file);
    }

    sleep(15);
}
