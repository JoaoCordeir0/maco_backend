<?php

namespace MacoBackend\Services;
use Exception;
use MacoBackend\Helpers\FileHelper;
use Dompdf\Dompdf;

class PDFService
{    
    /**
     * Função que exporta para PDF
     * 
     * @param $data
     */
    public static function exportPDF()
    {
        try {
            $dompdf = new Dompdf();

            $dompdf->loadHtml('<!DOCTYPE html><html lang="en"><head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Document</title> <style> .layout { width: 100%; text-align: center; margin: auto; } .layout-text { max-width: 900px; margin: -650px auto; line-height: 3.5em } .unifae-text { font-size: 30px; } </style></head><body> <div class="layout"> <img src="https://macodocumentation.s3.sa-east-1.amazonaws.com/layout-certificado.png" /> <div class="layout-text"> <p class="unifae-text"> O Centro Universitário das Faculdades Associadas de Ensino - UNIFAE, no uso das suas atribuições certifica que <br> <b>João Victor Cordeiro</b>, <br> participou do(a) V Jornada de Evidências Científicas da UNIFAE - VII Jornada de Iniciação Científica da UNIFAE e III Mostra de Jogos da UNIFAE, promovido pelo(a) Propeq, com carga horária de 01:00:00 <br> São João da Boa Vista, 31 de maio de 2023 </p> </div> </div></body></html>');
            $dompdf->setPaper('A4');

            $dompdf->render();

            $dompdf->stream("teste.pdf");
        }
        catch(Exception $e) {
            throw $e;
        }
    }            
}