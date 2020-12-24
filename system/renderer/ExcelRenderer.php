<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 17.05.2019
 * Time: 23:00
 */

namespace renderer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelRenderer
{
    
    public function get(array $products , array $payload){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($products as $k => $product) {
            $sheet->setCellValue('A' . ($k+1), "https://www.lowes.com" . isset($product['url']) ? $product['url'] : "warn");
            $sheet->setCellValue('B' . ($k+1), isset($product['type']) ? $product['type'] : "warn");
            $sheet->setCellValue('C' . ($k+1), isset($product['brand']) ? $product['brand'] : "warn");
            $sheet->setCellValue('D' . ($k+1), isset($product['amount']) ? $product['amount'] : "warn");
            $sheet->setCellValue('E' . ($k+1), isset($product['currency']) ? $product['currency'] : "warn");
//            $sheet->setCellValue('F' . ($k+1), $product['symbol']);
        }
        $writer = new Xlsx($spreadsheet);
        $operation_id = $payload['operation_id'];
        $filename = __DIR__ .
            DIRECTORY_SEPARATOR .".." .
            DIRECTORY_SEPARATOR . ".." .
            DIRECTORY_SEPARATOR . "output" .
            DIRECTORY_SEPARATOR .'Operation-id-#'.$operation_id . '-' . $this->getDate() .'.xlsx';
        try {
            $writer->save($filename);
        }catch (\Exception $error){ echo $error->getMessage() ."\n"; return false; }
        return $filename;
    }

    public function getDate(){
        date_default_timezone_set("Asia/Baku");
        return date("Y-m-d");
    }

}