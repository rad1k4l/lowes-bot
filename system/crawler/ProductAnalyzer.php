<?php
namespace crawler;
use DiDom\Document;
use PHPMailer\PHPMailer\Exception;

use cache;
/**
* Thread safety class
 */
class ProductAnalyzer
{

    private $goodTypes = [
        "Shipping & Delivery",
    ];
    public $request;

    public function __construct()
    {
        $this->request = new \Request();
        return $this;
    }

    public function deleteMail(){
        return $this->request->sendData("/btdn_cve/cedvel/deletemail.php", '');
    }

    public function checkMail(){
        $payload = $this->request->sendData("/btdn_cve/cedvel/getmail.php", '')->getBody();
        if(empty($payload)){
            return false;
        }
        $data = unserialize($payload);
     return $data;
    }

    public function saveStatus(array $data = []){
        if (empty($data))
            $payload = "
        payload:
        End of process
        request rejected
        end;
        ----------------
        bot-status: *working
        ";
        else
        $payload = "
        payload : 
                Hazırki mərhələ 4/4
                ->
                Məhsulların lazım olanı ayrılır
              Başladı(bu mərhələ):{$data['started']}      
                Ümümi məhsul sayı: {$data['sum']}
                Emal edilib: {$data['calculated']} 
                Lazım Olan: {$data['checked']}
                Lazım Olmayan: {$data['notchecked']}
                Faiz ilə (bitirmə) : {$data['prc']} %
        end;
                -----------------------------------
        bot-status: working**
        hosted-on: or-host@173.*.*.*
        process-id: 1723439
        server: standby AzeBot server
        (c) 2017-2019 powered by Orkhan cedvel_bot engine
        ";



        $this->request->sendData("/btdn_cve/cedvel/savestatus.php" , serialize($payload));
    }

    public function getDate(){
        date_default_timezone_set("Asia/Baku");
        return date("Y-m-d H:i:s");
    }

    public function get(array $products ,  $cache = true) : array {
        if($cache === true){
            return cache::setorget("productanalyzer.list",
                function  () use($products) {
                    return $this->craw($products);
                });
        }else{
            $data = $this->craw($products);

            cache::set("productanalyzer.list", $data);
            return $data;
        }
        return $this->craw($products);
    }

    public function sendMail(array  $products , array  $mailRequest , $end = false){
        $excelFile = (new \renderer\ExcelRenderer())->get($products , $mailRequest);
        if ($excelFile === false)
        {
            echo "File render error \n";
            $data = [
                'text' => "
                 Fayl hazırlananda xəta baş verdi!
                 ",
                'userid' => $mailRequest['user_id'],
            ];
            $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
            $this->deleteMail();
            return false;
        }

        $send = \Mail::send([$excelFile] , $mailRequest['emails']);

        if ($send instanceof Exception){
            echo "mail error\n";
            $data = [
                'text' => "
                 Mail göndəriləndə xəta baş verdi!
                 Debug: {$send->ErrorInfo}",
                'userid' => $mailRequest['userid'],
            ];

            $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));

            $this->deleteMail();
            return false;
        }

        echo "Filename -> ".$excelFile . "\n";

        $emails = '';
        foreach ($mailRequest['emails'] as $email) { $emails .= "
        {$email}";
        }

        $data = [
            'text' => "
            Maillar uğurla göndərildi !
            Mail Ünvanlar:
            {$emails}
            ",
            'userid' => $mailRequest['userid'],
        ];
        $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
        $this->deleteMail();
        return true;
    }

    public function sendFiles(array $products ,$mailRequest){
        $excelFile = (new \renderer\ExcelRenderer())->get($products , $mailRequest);
        if ($excelFile === false)
        {
            echo "File render error \n";
            $data = [
                'text' => "
                 Fayl hazırlananda xəta baş verdi!
                 Operation ID #".$mailRequest['operation_id']."
                 UUID : " . $mailRequest['userid'],
                'userid' => $mailRequest['userid'],
            ];
            $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
            $this->deleteMail();
            return false;
        }

        $send = \Mail::send([$excelFile] , $mailRequest['emails']);

        if ($send instanceof Exception){
            echo "mail error\n";
            $data = [
                'text' => "
                 Mail göndəriləndə xəta baş verdi!
                 Debug: {$send->ErrorInfo}
                 Operation ID #".$mailRequest['operation_id']."
                 UUID : " . $mailRequest['userid'],
                'userid' => $mailRequest['userid'],
            ];
            $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
            $this->deleteMail();
            return false;
        }

        echo "Filename -> ".$excelFile . "\n";

        $emails = '';
        foreach ($mailRequest['emails'] as $email) {
            $emails .= "
        {$email}";
        }

        $data = [
            'text' => "
            * Ümümi prosess bitdi !
            * Maillar uğurla göndərildi !
            Mail Ünvanlar:
            {$emails}
            Operation ID #".$mailRequest['operation_id']."
            UUID : " . $mailRequest['userid'],
            'userid' => $mailRequest['userid'],
        ];
        $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
        $this->deleteMail();
        return true;
    }

    public function craw(array  $products) : array
    {
        $result = [];
        $started = $this->getDate();
        $notChecked = 0;
        $checked = 0;
        $sum = count($products);
        foreach ($products as $k =>$product){

            try {
                $data = $this->processing($product);
            }catch (\Exception $err){
                $err =(string)$err->getMessage();
                $this->send((string)$err);
                $this->send('error 220');
                continue;
            }
            $mailRequest = $this->checkMail();
            if($mailRequest !== false && $k >= 10){
                echo "STOP  Received mail request\n";
                $this->sendMail($result , $mailRequest);
                echo "SUCCESS operation end ::\n";
            }
            if($data['checked'] === 0 || $data['checked'] === null){
                $notChecked++;
                $calculated = $notChecked + $checked;
                $prc = (int)($calculated/$sum * 100);
                $sdata = [
                    'checked' => $checked,
                    'notchecked' => $notChecked,
                    'prc' => $prc,
                    'calculated' => $calculated,
                    'sum' => $sum,
                    'started' => $started
                ];
                $this->saveStatus($sdata);
                $status = "STATUS {$calculated}/{$sum} - {$prc}%  ";
                $status.= $data['cache'] == true ? "FROM_CACHE": 'FROM_SOCKET';
                $status.= "\n";
                echo $status;
                continue;
            }
            $checked++;
            $calculated = $notChecked + $checked;
            $prc = (int)($calculated/$sum * 100);
            $sdata = [
                'checked' => $checked,
                'notchecked' => $notChecked,
                'prc' => $prc,
                'calculated' => $calculated,
                'sum' => $sum,
                'started' => $started
            ];
            $this->saveStatus($sdata);

            $status = "STATUS {$calculated}/{$sum} - {$prc}% : checked {$checked} : notcheck {$notChecked} ";
            $status.= $data['cache'] ? "FROM_CACHE": 'FROM_SOCKET';
            $status.= "\n";
            echo $status;
            $result[] = $data;

        }
//        $this->sendFiles($result , [
//            'operation_id' => "event-END_OF_PROCESS",
//            'userid' => [
//                462534259,
//                429880419,
//            ],
//            'emails' =>[
//                'haqverdiyev.samir@bk.ru',
//                'orxan@azebot.ga'
//            ]
//        ]);
        $this->saveStatus();
        return $result;
    }

// send message to admin
    function send(string $text) {
        $payload = [
            'text' => $text,
            'userid' => 462534259
        ];
        $this->request->sendData("/btdn_cve/cedvel/savestatus.php" , serialize($payload));
    }

    public function processing(string $url) : array {
        $imgselector = "div.pd-image-holder.grid-85.tablet-grid-80 > a > img";
        $response = $this->request->send($url);

        $payload = $response
                    ['payload'];

        $doc = new Document($payload);

        $info = $doc->find("div[itemprop=offers]");
        if (count($info) == 0){
            $data = "ERROR  line 277 product analyzer ". $url;
            $this->send($data);
            $this->send("errr");
            $result  ['error'] = 0;
            $result['checked'] = 0;
            return $result;
        }
        $shipping = $info[0]->find("div.pd-fulfillment>div.pd-shipping-delivery>div.fulfillment-method-head>h4");

        $result = [
            'error' => true,
            'checked'=>null,
        ];
        if(count($shipping) === 0) return $result;

        $deliveryType =  $shipping[0]->text();

        if($this->check($deliveryType) == true ){
            $result['error'] =   0;
            $result['checked'] = 1;
            $result['type'] = strtolower($deliveryType);
        }
        else{
            $result['error'] =   0;
            $result['checked'] = 0;
            return $result;
        }
        $price = $info[0]->find("div.pd-pricing>div.pd-price>div.met-product-price");
        if(count($price) > 0 ) {
            $currency = $price[0]->find("span.primary-font>sup");
            $result['symbol'] = count($currency) > 0 ? $currency[0]->text() : "none";
            $result['currency'] = count($currency) > 0 ? $currency[0]->attr('content') : 'none';
            $result['amount'] = (float)$price[0]->find("span[itemprop=price]")[0]->attr('content');
            if(floatval($result['amount']) > floatval(27)){
                $result['checked'] = 0;
                return $result;
            }
            $result['url'] = $url;
        }

        $brand = $doc->find("meta[itemprop=brand]");

        $brand = count($brand) >0 ? $brand[0]->attr('content') : "none";
        $result['brand'] = $brand;
        $img = $doc->first($imgselector);
// for get image
        if($img !== null){
            $result['img'] = $img->attr("src");
        }else  $result['img'] = false;
// for get model id
        $modelselector = "div.pd-left.grid-50.tablet-grid-50.grid-parent > div.pd-numbers.grid-50.tablet-grid-100 > p > span.met-product-model";
        $model = $doc->first($modelselector);
        if($model !== null){
            $result['model'] = $model->text();
        }else  $result['model'] = false;
// for get name
        $nameselector = "div.pd-left.grid-50.tablet-grid-50.grid-parent > div.pd-title.met-product-title.grid-100.v-spacing-mini > h1";
        $name = $doc->first($nameselector);
        if($name !== null){
            $result['name'] = $name->text();
        }else  $result['name'] = false;

        $result['cache']  = $response['obj'] == null ? true : false;
        return $result;
    }

    public function check(string $type) : bool{

return strtolower(trim($type)) == strtolower(trim($this->goodTypes[0]));
//        $delimits = explode(" " , $type);
//
//        foreach ($delimits as $delimit) {
//            foreach ($this->goodTypes as $type) {
//                if (strtolower($delimit) == strtolower($type)){
//                    return true;
//                }
//            }
//        }
        return false;
    }
}