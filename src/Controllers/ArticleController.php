<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\ArticleModel;

final class ArticleController
{
    /**
    * Realiza a listagem dos artigos
    *    
    * @return Response
    */
    public function list(Request $request, Response $response, $args): Response
    {        
        $article = new ArticleModel();
        $article->select()
                ->get();              
                
        $response->getBody()->write(json_encode($article->result()));                                     

        return $response;
    }  
}