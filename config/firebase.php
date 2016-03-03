<?php
if (isset($_SERVER['HTTP_HOST'])) {
    $firebaseConfig = array(
        "FIREBASE_TOKEN" => "xLK7Un6YDi6E88UFD2FwGw43fGmOnc29XNoiA4N0",
        "FIREBASE_APP_URL" => "https://blazing-heat-1335.firebaseio.com/",
        "daily_reset_hour" => 21
    );
} else {
    $firebaseConfig = array(
        "FIREBASE_TOKEN" => "vB1ADrPE8R1gyw5P5n8WvKwl1OIJiO2OHn3z7sj4",
        "FIREBASE_APP_URL" => "https://dazzling-heat-8296.firebaseio.com/",
        "daily_reset_hour" => 20
    );
}
