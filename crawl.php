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

$page = rememberPage();
while (true) {
    stdout('=== Page ' . $page . ' ===');
    $lists = fetchLicensesLists($client, $page, 50);
    rememberPage($page);

    if ($lists === false) {  // error happened
        continue;
    }

    if (!count($lists)) {    // no results
        break;
    }

    logger(sprintf('Page %2d (%d): %s', $page, count($lists), json_encode($lists)));
    $page += 1;

    $imageIds = array_map(function ($item) {
        return $item['imageId'];
    }, $lists);
    batchDownloadImages($client, $imageIds);

    // for test
    // if ($page >= 3) {
    //     break;
    // }
}
