<?php

namespace App\Controllers;

use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Services\PDFService;

class ApiController
{

    private $token = 'Token 0f3515522fb50cf117740094b9e3f6cf2b155c15';

    private $pdfService;

    private $tesseact;

    public function __construct()
    {
        $this->pdfService = new PDFService();
        $this->tesseact = new TesseractOCR();
        header('Content-Type: application/json');
        try {
            $headers = apache_request_headers();

            if (!isset($headers['Authorization']) || $headers['Authorization'] != $this->token) {
                throw new \Exception('Authorization header not found', 401);
            }
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code($e->getCode());
            exit;
        }
    }

    public function run()
    {
        return $this;
    }

    public function execPOST($data)
    {

        $img_jpg = $this->pdfService->base64ToPDF($data);
        $coverter = $this->pdfService->convert($img_jpg);
        $this->pdfService->revomerArquivo($img_jpg);
        
        echo json_encode($coverter);


    }
    

}