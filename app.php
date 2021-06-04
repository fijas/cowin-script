<?php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

require __DIR__ . '/vendor/autoload.php';

$sleepTime = 4; //seconds


function checkResult($data, $minAge) {
    $hasSessions = false;
    foreach ($data['centers'] as $center) {
        foreach ($center['sessions'] as $session) {
            if ($session['min_age_limit'] == $minAge) {
                if ($session['available_capacity_dose1'] > 0) {
                    echo "\n--------\n";
                    echo $center['center_id'] , ' - ' . $center['name'] . "\n";
                    echo $center['fee_type'] . "\n";
                    echo $session['date'] . "\n";
                    echo $session['min_age_limit'] . "+\n";
                    echo $session['available_capacity_dose1'] . ':' . $session['available_capacity_dose2'];
                    echo "--------\n\n";
                    $hasSessions = true;
                }
            }
        }
    }
    if ($hasSessions) {
        exec("mpg123 -q beep-02.mp3");
        exec("mpg123 -q beep-02.mp3");
    }
    unset($data);
}

function checkAvailability($minAge)
{
    $endpoint = "https://cdn-api.co-vin.in/api/v2/appointment/sessions/calendarByDistrict";
    $authorizationToken = "";
    $districtId = '307';

    $client = new Client([
        'timeout'  => 4.0,
    ]);
    try {
        $response = $client->get($endpoint, [
            'headers' => [
                'authorization' => 'Bearer ' . $authorizationToken,
                'accept' => 'application/json',
                'origin' => 'https://selfregistration.cowin.gov.in',
                'referer' => 'https://selfregistration.cowin.gov.in/',
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36'
            ],
            'http_errors' => false,
            'query' => ['district_id' => $districtId, 'date' => date('d-m-Y')]
        ]);


        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), true);
            checkResult($result, $minAge);
        } else {
            echo $response->getStatusCode() . ' - ';
            echo $response->getReasonPhrase() . "\n";
        }
    } catch (ConnectException $e) {
        echo "Connection Exception\n";
        unset($e);
    }
    unset($client, $response);
}

$minAge = 18;
while (true) {
    checkAvailability($minAge);
    echo ".";
    sleep($sleepTime);
}