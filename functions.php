<?php

function getToken()
{
    $json = file_get_contents(__DIR__ . '/token.json');
    $data = json_decode($json, true);

    return $data['access_token'];
}

function responseToJson($response)
{
    if ($response->getStatusCode() != 200) {
        stdOut($response->getReasonPhrase());
        return false;
    }

    return json_decode($response->getBody(), true);
}

function fetchLicensesLists($client, $page = 1, $perPage = 10)
{
    $response = $client->get('images/licenses', [
        'query' => [
            'page' => $page,
            'per_page' => $perPage,
            'sort' => 'newest',
        ],
    ]);

    $data = responseToJson($response);
    if ($data === false) {
        return false;
    }

    $lists = [];
    foreach ($data['data'] as $item) {
        $lists[] = [
            'id' => $item['id'],
            'imageId' => $item['image']['id'] ?? null,
            'imageSize' => $item['image']['format']['size'] ?? null,
        ];
    }

    return $lists;
}

function isImageIdExists($imageId, $path)
{
    // check file is exists
    $filePattern = sprintf('%s/shutterstock_%s*', rtrim($path, '/'), $imageId);

    if (count(glob($filePattern))) {
        stdOut("\t {$imageId} file exists, skip download");
        return true;    // exists
    }

    return false;
}

function copyImage($url, $imageId, $path)
{
    if (!strlen($url)) {
        return false;
    }

    // prepare file path
    $urlSegments = explode('/', $url);
    $filename = end($urlSegments);
    $filePath = sprintf('%s/%s', rtrim($path, '/'), $filename);

    if (file_exists($filePath)) {
        stdOut($filename . ' exists');
        return true;
    }

    // copy($url, $filePath);
    shell_exec(sprintf('curl %s -o %s > /dev/null 2>&1 &', $url, $filePath));

    logger($imageId . ' copy to ' . $filePath, true);
}

function downloadImage($client, $item, $path = null)
{
    $path = $path ?: __DIR__ . '/images/';

    if (!count($item)) {
        return false;
    }

    if (isImageIdExists($item['imageId'], $path)) {
        return true;
    }

    try {
        // get image url
        $id = $item['id'];
        $response = $client->post("images/licenses/{$id}/downloads", [
            'body' => '{}'
        ]);

        $data = responseToJson($response);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Not redownloadable') !== false) {
            stdOut($item['imageId'] . ' Not redownloadable');
            $dummyFilepath = sprintf('%s/shutterstock_%s.notredownload', rtrim($path, '/'), $item['imageId']);
            file_put_contents($dummyFilepath, '');
            return false;
        }
        if (strpos($e->getMessage(), 'Media unavailable') !== false) {
            stdOut($item['imageId'] . ' Media unavailable');
            $dummyFilepath = sprintf('%s/shutterstock_%s.mediaunavlible', rtrim($path, '/'), $item['imageId']);
            file_put_contents($dummyFilepath, '');
            return false;
        }


        throw $e;
    }

    copyImage($data['url'], $item['imageId'], $path);

    return true;
}

function batchDownloadImages($client, $imageIds, $path = null)
{
    if (!count($imageIds)) {
        return false;
    }

    $path = $path ?: __DIR__ . '/images/';

    $images = [];
    foreach ($imageIds as $imgId) {
        if (isImageIdExists($imgId, $path)) {
            continue;
        }

        $images[] = ['image_id' => $imgId];
    }

    $response = $client->post('images/licenses', [
        'query' => [
            'subscription_id' => 's25741675',
        ],
        'body' => json_encode([
            'images' => $images
        ]),
    ]);

    $data = responseToJson($response);

    if (!is_array($data)) {
        return false;
    }

    foreach ($data['data'] as $item) {
        $imageId = $item['image_id'] ?? null;
        $url = $item['download']['url'] ?? null;
        if ($url) {
            copyImage($url, $imageId, $path);
        }
    }
}

function logger($msg, $echoout = false)
{
    $txt = sprintf('[%s] %s', date('Y-m-d H:i:s'), $msg) . PHP_EOL;
    file_put_contents(__DIR__ . '/log.txt', $txt, FILE_APPEND);

    if ($echoout) {
        echo $txt;
    }
}

function stdOut($msg)
{
    if (is_array($msg)) {
        $msg = json_encode($msg);
    }
    echo $msg . PHP_EOL;
}

function rememberPage($page = null)
{
    $filename = __DIR__ . '/current_page.txt';

    if (!is_null($page)) {
        file_put_contents($filename, $page);
        return true;
    }

    if (!file_exists($filename)) {
        return 1;
    }
    return intval(file_get_contents($filename));
}
