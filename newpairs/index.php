<?php

require '../db_connect.php';

$bscScan = new Bscscan($db);
//$bscScan->makeUrls('0x1FD9Af4999De0d61c2a6CBD3d4892b675a082999');
$bscScan->startLoad();


class Bscscan {

    private $db;

    const APIURL = 'https://api.bscscan.com/api';
    private $apiParam = [
        'module'    => 'account',
        'action'    => 'txlistinternal',
        'contractaddress'   => '0x05fF2B0DB69458A0750badebc4f9e13aDd608C7F',
        'sort'      => 'asc',
        'startblock'=> '4850499',
        'endblock'  => '4850499',
        'apikey'    => '62QC9K42NE8TDVE9GZ4BNB54WHI4PKA3MH',
    ];

    public function __construct( $db )  {
        $this->db = $db;
    }

    public function makeUrls(){
        //$this->setLastBlockId();
        $apiUlr = self::APIURL . '?';
        foreach ($this->apiParam as $param=>$value){
            $apiUlr .= $param . '=' . $value . '&';
        }
        echo '<pre>' . print_r($apiUlr, 1) . '</pre>';
        return $apiUlr;
    }

    public function startLoad(){

        $apiUrl = $this->makeUrls();
        $jsonTx = file_get_contents($apiUrl);

        $txList = json_decode($jsonTx, true);

        $txList = $txList['result'];
        $this->findPairs($txList);

    }

    private function findPairs($txList){
        $pairs = [];
        foreach ($txList as $txn){
            if ($txn['type'] == 'create2')
                $pairs[] = $txn['contractAddress'];

            $lastblock = $txn['blockNumber'];
        }

        echo '<pre>' . print_r($pairs, 1) . '</pre>';
        //$this->updateLastBlockId($lastblock);
    }

    private function updateLastBlockId($blockId){
        $id = 1;
        $this->db->query('UPDATE options SET', [
            'blockId' => $blockId,
        ], 'WHERE id = ?', $id);
    }



    public function setLastBlockId(){
        $result = $this->db->query('SELECT blockId FROM options WHERE id = 1');
        $blockId = $result->fetch()->blockId;
        $this->apiParam['startblock'] = $blockId;
        echo '<pre>' . print_r($blockId, 1) . '</pre>';
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