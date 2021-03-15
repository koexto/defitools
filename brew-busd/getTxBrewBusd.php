<?php
require 'db_connect.php';

$apiKey = '&apikey=62QC9K42NE8TDVE9GZ4BNB54WHI4PKA3MH';
$address = '&address=0x1FD9Af4999De0d61c2a6CBD3d4892b675a082999';
$url = 'https://api.bscscan.com/api?module=account&action=tokentx';
$sort = '&sort=asc';

$startBlock = '';
//$startBlock = '&startblock=4785916';
//$endBlock = '&endblock=4785918';



$result = $db->query('SELECT * FROM brew_busd ORDER BY id DESC LIMIT 1');

echo '<pre>' . print_r($result, 1) . '/<pre>';

//var_dump($result->fetch()->blockNumber);


if ($result->getRowCount() > 0)
    $startBlock = '&startblock=' . $result->fetch()->blockNumber;

//$startBlock = '&startblock=4903865';

$jsonTx = file_get_contents($url . $address . $sort . $apiKey . $startBlock);

$txList = json_decode($jsonTx, true);

$txList = $txList['result'];

//echo '<pre>' . print_r($txList, 1) . '/<pre>';

try {
    $db->begin();

    foreach ($txList as $tx) {
        $db->query('INSERT INTO brew_busd', [
            'blockNumber' => $tx['blockNumber'],
            'timeStamp' => date('Y-m-d H:i:s',$tx['timeStamp']),
            'hash' => $tx['hash'],
            'nonce' => $tx['nonce'],
            'blockHash' => $tx['blockHash'],
            'fromAddress' => $tx['from'],
            'contractAddress' => $tx['contractAddress'],
            'toAddress' => $tx['to'],
            'value' => $tx['value'],
            'tokenName' => $tx['tokenName'],
            'tokenSymbol' => $tx['tokenSymbol'],
            'tokenDecimal' => $tx['tokenDecimal'],
            'transactionIndex' => $tx['transactionIndex'],
            'gas' => $tx['gas'],
            'gasPrice' => $tx['gasPrice'],
            'gasUsed' => $tx['gasUsed'],
            'cumulativeGasUsed' => $tx['cumulativeGasUsed'],
            'input' => $tx['input'],
            'confirmations' => $tx['confirmations'],
        ]);
    }

    $db->commit();
} catch (\Throwable $e) {
    $db->rollback();
    throw $e;
}



