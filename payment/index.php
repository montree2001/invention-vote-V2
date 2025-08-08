<?php
require 'vendor/autoload.php';

use Endroid\QrCode\QrCode;

function generatePromptPayQR($receiver, $amount = null) {
    $payloadFormatIndicator = '000201';
    $pointOfInitiationMethod = '010211';
    $merchantAccountInformationTemplateId = '29';
    $merchantAccountInformation = '0016A000000677010112000011'; // Static part of merchant info (ID)

    if (strlen($receiver) == 13) { // National ID
        $receiverType = '02';
    } else if (strlen($receiver) == 10 && substr($receiver, 0, 2) == '06') { // Mobile Number
        $receiver = '00666' . substr($receiver, 1);
        $receiverType = '01';
    } else {
        throw new Exception("Invalid receiver identifier");
    }

    $merchantAccountInformation .= $receiverType . str_pad(strlen($receiver), 2, '0', STR_PAD_LEFT) . $receiver;

    $countryCode = '5802TH';
    $currency = '5303764';
    
    if ($amount) {
        $transactionAmount = '54' . str_pad(strlen(number_format($amount, 2, '.', '')), 2, '0', STR_PAD_LEFT) . number_format($amount, 2, '.', '');
    } else {
        $transactionAmount = '';
    }

    $additionalDataFieldTemplate = '6304';
    
    $dataToHash = $payloadFormatIndicator . $pointOfInitiationMethod . '0016A000000677010112000011' . $merchantAccountInformation . $countryCode . $currency . $transactionAmount . '5802TH';

    $crc = strtoupper(dechex(crc16($dataToHash . $additionalDataFieldTemplate)));

    if (strlen($crc) == 3) {
        $crc = '0' . $crc;
    }

    $fullString = $dataToHash . $additionalDataFieldTemplate . $crc;

    $qrCode = new QrCode($fullString);
    $qrCode->setSize(300);

    header('Content-Type: ' . $qrCode->getContentType());
    echo $qrCode->writeString();
}

function crc16($data) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
        $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
        $x ^= $x >> 4;
        $crc = (($crc << 8) & 0xFFFF) ^ ($x << 12) ^ (($x << 5) & 0xFFFF) ^ ($x & 0xFF);
    }
    return $crc & 0xFFFF;
}

$receiver = '0812345678'; // PromptPay ID (Phone Number)
$amount = 100.00; // Optional amount

generatePromptPayQR($receiver, $amount);
?>
