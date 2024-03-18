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
        $params = (object) $request->getQueryParams();     
        $condition = '';

        $article = new ArticleModel();

        if (isset($params->title, $params->status)) {            
            $condition = "title like '%" . $params->title . "%' and status = " . $params->status;
        }               
        else if (isset($params->title)) {            
            $condition = "title like '%" . $params->title . "%'";
        }
        else if (isset($params->status)) {            
            $condition = "status = " . $params->status;
        }

        $article->select()
                ->where($condition)
                ->orderby()
                ->get(true);              
                
        $response->getBody()->write(json_encode($article->result()));                                     

        return $response;
    }  

    /**
    * Realiza a inserção de um artigo
    *    
    * @return Response
    */
    public function add(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $title = $parsedBody['title'];
        $author = $parsedBody['author'];  
        $advisor = $parsedBody['advisor'];             
        $keywords = $parsedBody['keywords'];  
        $summary = $parsedBody['summary'];
        $status = $parsedBody['status'];  

        if (empty($title) || empty($author) || empty($advisor) || empty($keywords) || empty($summary) || empty($status))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $article = new ArticleModel();
        $article->data([
            'title' => $title,
            'author' => $author,
            'advisor' => $advisor,
            'keywords' => $keywords,
            'summary' => $summary,
            'status' => $status,
        ])->insert();              
        
        if ($article->result()->status == 'success')
        {
            $response->getBody()->write(json_encode([
                'status' => $article->result()->status,                     
                'message' => 'Article inserted successfully',                                             
            ])); 
        }
        else
        {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $article->result()->message
            ]));  
        }         
        
        return $response;
    } 
}