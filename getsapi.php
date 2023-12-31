<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$mycookie = $_POST['cookie'];
$digitoken = $_POST['token'];
$rcaptcha = $_POST['captcha'];

$day = $_POST['day'];
$days = $_POST['days'];
$currentDate = date("Y/m/d") . date("h:i:sa");
$orgDate = $day;
$newDate1 = date("m-d-Y", strtotime($orgDate));
$date = str_replace('-', '/', $newDate1);
$newDate = date("m/d/Y", strtotime($date));

$dateVal = $day;
$dropDate = date('m/d/Y', strtotime($dateVal. ' + ' . $days . ' days'));

$adds = explode('/', $_POST['add']);
$links = explode(',', $_POST['link']);
$outputs = [];
for ($c = 0; $c < count($adds); $c ++){
    $add = strtoupper($adds[$c]);
    $link = $links[$c];

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.avis.com/webapi/reservation/vehicles',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{"rqHeader":{"brand":"","locale":"en_US"},"nonUSShop":true,
    "pickInfo":"' . $add . '","pickDate":"' . $date . '",
    "pickTime":"12:00 PM","dropInfo":"' . $add . '","dropDate":"' . $dropDate . '",
    "dropTime":"12:00 PM","couponNumber":"","couponInstances":"","couponRateCode":"","discountNumber":"","rateType":"","residency":"US","age":25,"wizardNumber":"","lastName":"","userSelectedCurrency":"","selDiscountNum":"","promotionalCoupon":"","preferredCarClass":"","membershipId":"","noMembershipAvailable":false,"corporateBookingType":"","enableStrikethrough":"true","amazonGCPayLaterPercentageVal":"","amazonGCPayNowPercentageVal":"","corporateEmailID":""}',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'userName: AVISCOM',
        'digital-token: ' . $digitoken,
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
        'cookie: ' . $mycookie,
        'g-recaptcha-response: ' . $rcaptcha,
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $datar = json_decode($response, false);
    $state = '-';
    $city = '-';
    $Address = '-';
    $fullAddr = '-';
    if (property_exists($datar, 'reservationSummary')){
        $state = $datar->reservationSummary->pickLoc->address->state;
        $city = $datar->reservationSummary->pickLoc->address->city;
        $Address = $datar->reservationSummary->pickLoc->address->address1;
        $fullAddr = $datar->reservationSummary->pickLoc->address->address1 . " "
                    . $datar->reservationSummary->pickLoc->address->city  . " "
                    . $datar->reservationSummary->pickLoc->address->state  . " "
                    . $datar->reservationSummary->pickLoc->address->zipCode  . " "
                    . $datar->reservationSummary->pickLoc->address->country;
    }
    if (property_exists($datar, 'vehicleSummaryList')){
        $carAmount = count($datar->vehicleSummaryList);
        for ($ca=0; $ca < $carAmount; $ca++) {
            $eachCar = $datar->vehicleSummaryList[$ca];
            $Class = $eachCar->carGroup;
            $Model = $eachCar->makeModel;
            $paynow = '-';
            $paylater = '-';
            $paynowtotal = '-';
            $paylatertotal = '-';
            if (property_exists($eachCar, 'payNowRate')) {
                $paynow = $eachCar->payNowRate->amount;
                $paynowtotal = $eachCar->payNowRate->totalRateAmount;
            }
            if (property_exists($eachCar, 'payLaterRate')) {
                $paylater = $eachCar->payLaterRate->amount;
                $paylatertotal = $eachCar->payLaterRate->totalRateAmount;
            }
            $output = array(
                'PickDate' => $date,
                'DropDate' => $dropDate,
                'LocationCode' => $add,
                'State' => $state,
                'City' => $city,
                'Fulladdress' => $fullAddr,
                'Class' => $Class,
                'Type' => $Model,
                'payNowPrice' => $paynow,
                'payLaterPrice' => $paylater,
                'payNowTotal' => $paynowtotal,
                'payLaterTotal' => $paylatertotal,
                'Link' => $link
            );
            array_push($outputs, $output);
        }
    }
    
}
echo json_encode($outputs);
} else {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
}

?>