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

            $dompdf->loadHtml('<!DOCTYPE html><html lang="en"><head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Document</title> <style> .layout { width: 100vh; margin: auto; } .unifae-text { font-size: 30px; } </style></head><body> <div class="layout"> <img src="layout.png" /> <div class="unifae-text"> O Centro Universitário das Faculdades Associadas de Ensino - UNIFAE, no uso das suas atribuições certifica que </div> </div></body></html>');

            $dompdf->setPaper('A4');

            $dompdf->render();

            $dompdf->stream("teste.pdf");
        }
        catch(Exception $e) {
            throw $e;
        }
    }            
}