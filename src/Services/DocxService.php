<?php

namespace MacoBackend\Services;
use Exception;
use MacoBackend\Helpers\FileHelper;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class DocxService
{   
    private $path = 'tmp/docx/'; 

    /**
     * Função que exporta para docx
     * 
     * @param $data
     */
    public function exportDocx($data): string 
    {
        try {
            $article = (object) $data;
            
            $phpWord = new PhpWord();

            $section = $phpWord->addSection();

            $section->addText($article->title);
            $section->addLine();
            $section->addText($article->summary);
            
            FileHelper::delFiles($this->path);
            
            $docx = $this->path . FileHelper::formatFileName($article->title) . '.docx';            

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($docx);

            return $docx;
        } catch(Exception $e) {
            throw $e;
        }
    }            
}