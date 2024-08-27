<?php

namespace MacoBackend\Services;
use Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use MacoBackend\Helpers\FileHelper;
use MacoBackend\Helpers\UserHelper;

class PDFService
{    
    private $path = 'tmp/pdf/';
    
    /**
     * Função que exporta para PDF
     * 
     * @param $data
     */
    public function exportPDF($data): string
    {
        try {            
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            $html = $this->getHTML($data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');

            $dompdf->render();

            FileHelper::delFiles($this->path);

            $pdf = $this->path . 'Certificate-' . UserHelper::getInitialsOfName($data['authors']['name']) . '-' . FileHelper::formatFileName($data['title']) . '.pdf';
            file_put_contents($pdf, $dompdf->output());

            return $pdf;
        } catch(Exception $e) {
            throw $e;
        }
    }         
    
    /**
     * Carrega o html e faz os replace nas informações
     * 
     * @param $data
     */
    public function getHTML($data): string
    {
        setlocale(LC_TIME, 'pt_BR.UTF-8');                

        $html = file_get_contents('./layout-certificate/certificate.html');

        $html = str_replace('{{student}}', $data['authors']['name'], $html);

        $html = str_replace('{{event}}', $data['event_name'], $html);

        $html = str_replace('{{article}}', $data['title'], $html);
                
        $html = str_replace('{{date}}', strftime('%d de %B de %Y'), $html);
        
        return $html;
    }
}