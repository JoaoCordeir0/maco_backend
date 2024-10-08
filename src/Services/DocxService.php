<?php

namespace MacoBackend\Services;
use Exception;
use MacoBackend\Helpers\CourseHelper;
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

            // Configs do docx
            $phpWord->setDefaultFontSize(12);
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultParagraphStyle(['lineHeight' => 1.5]);

            // Configs da página
            $section = $phpWord->addSection([
                'marginLeft'   => 3.0 * 567,
                'marginTop'    => 3.0 * 567,
                'marginRight'  => 2.0 * 567,
                'marginBottom' => 2.0 * 567,
            ]);
            
            // Styles
            $bold = ['bold' => true, 'noProof' => true];            
            $center = ['alignment' => 'center', 'noProof' => true];
            $both = ['alignment' => 'both'];

            // Título
            $section->addText(mb_strtoupper($article->title), $bold, $center);
            $section->addText('');

            // Autores
            foreach($article->authors as $author) {                
                $section->addText($author['name'] . ' - ' . $author['email']);
            }  
            $section->addText('');

            // Orientadores / Co-orientadores            
            foreach($article->advisors as $advisor) {      
                if ($advisor['is_coadvisor']) {
                    $textrun1 = $section->addTextRun();
                    $textrun1->addText('Co-orientador(a): ', $bold);
                    $textrun1->addText($advisor['name'] . ' - ' . $advisor['email']);    
                } else {
                    $textrun2 = $section->addTextRun();
                    $textrun2->addText('Orientador(a): ', $bold);
                    $textrun2->addText($advisor['name'] . ' - ' . $advisor['email']);
                }                
            }  
            //$section->addText('');

            // Curso
            $courses = [];
            $textrun3 = $section->addTextRun();
            $textrun3->addText('Curso(s): ', $bold);
            foreach($article->authors as $author) {                                
                $course = CourseHelper::getCourseByID($author['course']);
                if (!in_array($course, $courses)) {                 
                    array_push($courses, $course);
                }                
            }  
            $textrun3->addText(implode(', ', $courses));
            $section->addText('');

            // Resumo
            $section->addText("Resumo:", $bold);
            $section->addText($article->summary, null, $both);
            $section->addText('');

            // Palavras chaves
            $section->addText("Palavras chaves:", $bold);
            $section->addText(trim(str_replace(';', ', ', $article->keywords)));
            $section->addText('');

            // Referências
            $section->addText("Referências:", $bold);
            foreach($article->references as $reference) {          
                $section->addText(htmlspecialchars($reference['reference']), null, $both);
                //$section->addText('');
            }            
            
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
