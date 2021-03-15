<?php

require 'db_connect.php';

$bscScan = new Bscscan($db);
//$bscScan->makeUrls('0x1FD9Af4999De0d61c2a6CBD3d4892b675a082999');
$bscScan->startLoad();




class Bscscan {

    private $db;
    private $listAddress = [
        'brew_busd' => '0x1FD9Af4999De0d61c2a6CBD3d4892b675a082999',
        'brew_bnb'  => '0x723203E821f1fF2d0e396d5DD2EA390f3C9d42cF',
        'brew_cake' => '0xfc4ad134129a7AF5e90673db18b2b067a5Ac9821',
        'brew_flp'  => '0x25bc28d49b3E3E162E6FDC1E38A8991Cf5c40F51',
    ];

    const APIURL = 'https://api.bscscan.com/api';
    private $apiParam = [
        'module'    => 'account',
        'action'    => 'tokentx',
        'address'   => '',
        'sort'      => 'asc',
        'apikey'    => '62QC9K42NE8TDVE9GZ4BNB54WHI4PKA3MH',
    ];



    public function makeUrls($address){
        $this->apiParam['address'] = $address;
        $apiUlr = self::APIURL . '?';
        foreach ($this->apiParam as $param=>$value){
            $apiUlr .= $param . '=' . $value . '&';
        }

        $apiUlr .= $this->setStartEndParam();

        //echo '<pre>' . print_r($apiUlr, 1) . '</pre>';
        return $apiUlr;
    }

    private function setStartEndParam(){


        return 'startblock=4764606&endblock=4765897';

        $result = $this->getLastTxn();
        if ($result->getRowCount() == 0)
            return '';

        return 'startblock=' . $result->fetch()->blockNumber;
    }

    public function startLoad(){
        foreach ($this->listAddress as $key=>$address){
            $apiUrl = $this->makeUrls($address);
            $jsonTx = file_get_contents($apiUrl);

            $txList = json_decode($jsonTx, true);

            $txList = $txList['result'];
            $this->insertDB($txList, $key);
        }
    }

    public function __construct( $db )  {
        $this->db = $db;
    }

    public function getLastTxn(){
        $result = $this->db->query('SELECT * FROM brew ORDER BY id DESC LIMIT 1');
        return $result;
    }


    private function insertDB($txList, $pairs){
        try {
            $this->db->begin();

            foreach ($txList as $tx) {
                $this->db->query('INSERT INTO brew', [
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
                    'pairs' => $pairs,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

    }





}