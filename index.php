#!/usr/bin/php -q
<?php

$urlRegex = '/^(http(s?):\/\/)?(((www\.)?+[a-zA-Z0-9\.\-\_]+(\.[a-zA-Z]{2,3})+)|(\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b))(\/[a-zA-Z0-9\_\-\s\.\/\?\%\#\&\=]*)?$/i';
$fileContents = file_get_contents('./input.txt');

$urls = explode(PHP_EOL, $fileContents);
$chunks = array_chunk($urls, 20);

function requestUrl(string $url)
{
    $curl = curl_init($url);
    @curl_setopt($curl, CURLOPT_HEADER, true);
    @curl_setopt($curl, CURLOPT_NOBODY, true);
    @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // follow the redirects
    @curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    @curl_setopt($curl, CURLOPT_TIMEOUT,10);
    $output = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
    curl_close($curl);

    if (!$httpCode) {
        echo "{$url} - HTTP CODE: {$httpCode} - NO RESPONSE!\n";
    } else {
        echo "{$url} - HTTP CODE: {$httpCode}\n";
    }
    
    if ($httpCode == 302 && $redirectUrl) {
        echo "REDIRECTED to: {$redirectUrl}\n";
        requestUrl($redirectUrl);
    }
}

foreach ($chunks as $urls)
{
    $urls = array_unique($urls, SORT_REGULAR);
    foreach ($urls as $url) 
    {
        $url = trim($url);
        if (!$url || 
            !is_string($url) || 
            !preg_match($urlRegex, $url)) {
            continue;
        }

        requestUrl($url);
    }
}