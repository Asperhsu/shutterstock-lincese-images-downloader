# Shutterstock License Images Downloader

## Requirment
- Linux
- cURL
- php 7

## First, get access token
[Official Guide](https://developers.shutterstock.com/authentication#authentication)
- register app at [Shutterstock Develpoer](https://developers.shutterstock.com/)
- get client id and client secret, put into shell_scripts/oauth.sh variable
- run oauth.sh, it will output a url. copy it to browser.
- enter username and password
- after authencated, it will redirect to webhook url, but webhook url is not exist. just check url in browser find query parameter named 'CODE'.
- copy code value paste into shell_scripts/token.sh, anr run it.
- it will return a json object string with access token field.
- copy it all, paste into token.json
- done!

## Usage
- php crawl.php

## Workflow
- create client instance using [Guzzle](http://docs.guzzlephp.org/en/stable/)
- set init page (could be exception interrupt process)
- fetch all linceses images list using function fetchLicensesLists.
- list will contains id, image id, image type in array
- using function batchDownloadImages to compelete download request.
- downloaded images default will be images, name by [image id]_[image type].[ext]

## Resources
- [Official PHP Example](https://github.com/shutterstock/api/blob/master/examples/php-curl/v2.php)
- [Api Resouces](https://developers.shutterstock.com/images/apis/)