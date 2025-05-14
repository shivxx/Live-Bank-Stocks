<?php

function getStockPrice($symbol) {
    $apiKey = 'YOUR_API_KEY'; // Replace with your real API key
    $url = "http://api.marketstack.com/v1/eod/latest?access_key=$apiKey&symbols=$symbol";

    $response = file_get_contents($url);
    if ($response === FALSE) {
        return "Error fetching data";
    }

    $data = json_decode($response, true);
    if (isset($data['data'][0]['close'])) {
        return '$' . $data['data'][0]['close'];
    }

    return "Unavailable";
}
