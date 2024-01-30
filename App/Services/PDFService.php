<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PDFService
{
    public function read($path)
    {
        try {
            $file = fopen($path, 'r');
            $fileSize = filesize($path);
            $content = fread($file, $fileSize);
            fclose($file);

            return $content;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function upload($file)
    {
        $file_name = uniqid() . '_' . time() . '_' . str_replace(array('!', "@", '#', '$', '%', '^', '&', ' ', '*', '(', ')', ':', ';', ',', '?', '/' . '\\', '~', '`', '-'), '_', strtolower($file['name']));

        if (move_uploaded_file($file['tmp_name'], 'uploads/' . $file_name)) {
            return 'uploads/' . $file_name;
        } else {
            return false;
        }
    }

    public function convert($path)
    {

        //convert pdf to text 
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        $ret_dados = [
            'placas' => $this->getPlacas([$text]),
            'mic' => $this->getMic([$text]),
            'conhecimento_carga' => $this->getConhecimentoCarga([$text]),
            'peso_total' => $this->getPesoTotal([$text]),
        ];

        return $ret_dados;

    }


    public function base64ToPDF($hash)
    {
        //base64ToPDF sem salvar arquivo
        $file_name = uniqid() . '_' . time() . '.pdf';
        $file = fopen('uploads/' . $file_name, 'w');
        fwrite($file, base64_decode($hash));
        fclose($file);

        return 'uploads/' . $file_name;


    }

    public function revomerArquivo($path)
    {
        unlink($path);
    }

    public function getPlacas($dados)
    {
        $placas = [];

        //modelos de placas  SEMPRE LETRAS E NUMEROS  - PARAGUAY, BRASIL, ARGENTINA - modelos novos e antigos: exemplos: CAV880, CCR827, ATZ 936, AAHD 174
        $placas_regex = [
            //Placa Brasil
            '/[A-Z]{3}[0-9][0-9A-Z][0-9]{2}/',
            //Placa Paraguay
            '/[A-Z]{3,4}[0-9]{3}/',
            '/[A-Z]{3,4}-\\d{4}/',
            '/[A-z]{3}-\d[A-j0-9]\d{2}/',
            '/[A-Z]{3}-?\d{1,}[A-J]\d{1,}/',
            '/[A-Z]{3}-?\d{1,}[A-J]\d{2}/',
        ];
        //explode dados por linha
        $explode_dados = explode("\n", $dados[0]);

        //remover linhas que conter caracteres especiais ou letras em minusculo, palavras com menos de 3 caracteres, linhas vazias e palavras com mais de 7 caracteres
        foreach ($explode_dados as $key => $value) {
            if (preg_match('/[a-z]/', $value) || preg_match('/[!@#$%^&*()_+{}|:"<>?]/', $value) || strlen($value) < 6 || strlen($value) > 20 || empty($value)) {
                unset($explode_dados[$key]);
            }
        }
        //replace string 	
        foreach ($explode_dados as $key => $value) {
            $explode_dados[$key] = str_replace('	', ' ', $value);
        }

        //remover linhas  onde que cada palavra tenha mais de 3 caracteres e menos de 8 caracteres
        foreach ($explode_dados as $key => $value) {
            $explode_palavras = explode(' ', $value);
            foreach ($explode_palavras as $key2 => $value2) {
                if (strlen($value2) <= 8 && strlen($value2) >= 3) {
                    //verifa se é placa
                    foreach ($placas_regex as $key3 => $value3) {
                        if (preg_match($value3, $value2)) {
                            $placas[] = $value2;
                        }
                        //compara se a posição da atual com a proxima é uma placa não pode ser maior que 8 caracteres
                        if (isset($explode_palavras[$key2 + 1])) {
                            if (preg_match($value3, $value2 . $explode_palavras[$key2 + 1]) && strlen($value2 . $explode_palavras[$key2 + 1]) <= 8) {
                                $placas[] = $value2 . $explode_palavras[$key2 + 1];
                            }
                        }
                    }
                }
            }
        }
        $unique_placas = array_unique($placas);

        return count($unique_placas) > 0 ? $unique_placas : false;
    }

    public function getMic($dados)
    {
        //Mic é unico com com BR ou PY ou então 2 digitos ai BR ou PY até  pode terminar um uma letra no final e vai até 18 caracteres
        $regex = "/^(((BR|PY)\d{5,20}\d[A-Za-z])|(\d{2}(BR|PY)\d{5,20}\d[A-Za-z]))$/";

        //explode dados por linha
        $explode_dados = explode("\n", $dados[0]);
        //buscar mic
        $mic = [];
        foreach ($explode_dados as $key => $value) {
            $value = str_replace('	', ' ', $value);
            $explode = explode(' ', $value);
            foreach ($explode as $key2 => $value2) {
                $value2 = str_replace('Nº', '', $value2);
                if (preg_match($regex, $value2)) {
                    $mic[] = $value2;
                }
            }
        }


        return array_unique($mic) ? array_unique($mic)[0] : false;

    }

    protected function getConhecimentoCarga($dados)
    {
        $regex = "/^((\d{2}(BR|PY)\d{5,20})|((BR|PY)\d{5,20}))$/";
        $explode_dados = explode("\n", $dados[0]);
        //buscar mic
        $conhecimento = [];
        foreach ($explode_dados as $key => $value) {
            $value = str_replace('	', ' ', $value);
            $explode = explode(' ', $value);
            foreach ($explode as $key2 => $value2) {
                if (preg_match($regex, $value2)) {
                    $conhecimento[] = $value2;
                }
            }
        }

        return array_unique($conhecimento) ? array_unique($conhecimento)[0] : false;
    }

    protected function getPesoTotal($dados)
    {

        $explode_dados = explode("\n", $dados[0]);
        //buscar mic
        $peso = [];
        foreach ($explode_dados as $key => $value) {
            $value = str_replace('	', ' ', $value);
            $explode = explode(' ', $value);
            foreach ($explode as $key2 => $value2) {
                if (preg_match("/KG./", $value2)) {
                    $peso[] = $explode[$key2 + 1];
                }
            }

        }

        return array_unique($peso) ? floatval(array_unique($peso)[0]) : false;
    }

}