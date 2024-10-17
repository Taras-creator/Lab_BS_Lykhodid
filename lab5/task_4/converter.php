<?php


function getConversionRate($fromCurrency, $toCurrency)
{
    global $fromAmount;

    $file = simplexml_load_file("https://bank.gov.ua/NBU_Exchange/exchange?date=" . date("d/m/Y"));


    if ($file === false) {
        return false;
    }

    if (($toCurrency == 'UAH' && $fromCurrency == 'UAH') || $fromAmount == 0) {
        return 1;
    } else {
        if ($fromCurrency == 'UAH') {
            $xmlToCurrency = $file->xpath("//ROW[CurrencyCodeL=\"$toCurrency\"]");
            $toValute = $xmlToCurrency[0]->Amount;
            $cnt = $xmlToCurrency[0]->Units;
            $toValute /= $cnt;

            $fromValute = $fromAmount;
        } elseif ($toCurrency == 'UAH') {
            $xmlFromCurrency = $file->xpath("//ROW[CurrencyCodeL=\"$fromCurrency\"]");
            $fromValute = $xmlFromCurrency[0]->Amount;
            $cnt = $xmlFromCurrency[0]->Units;
            $fromValute /= $cnt;
        } else {
            $xmlFromCurrency = $file->xpath("//ROW[CurrencyCodeL=\"$fromCurrency\"]");
            $xmlToCurrency = $file->xpath("//ROW[CurrencyCodeL=\"$toCurrency\"]");
            $fromValute = $xmlFromCurrency[0]->Amount;
            $toValute = $xmlToCurrency[0]->Amount;
            $cnt = $xmlToCurrency[0]->Units;
            $toValute /= $cnt;
        }


        if ($toCurrency != 'UAH' && $fromCurrency != 'UAH') {
            return $toValute / $fromValute;
        } else {
            return $fromCurrency == 'UAH' ? (($fromValute / $toValute) / $fromValute) : $fromValute;
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $fromAmount = $data['fromAmount'];
    $fromCurrency = $data['fromCurrency'];
    $toCurrency = $data['toCurrency'];


    try {
        $conversionRate = getConversionRate($fromCurrency, $toCurrency);
        $toAmount = $fromAmount * $conversionRate;
        echo json_encode(['toAmount' => $toAmount]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
