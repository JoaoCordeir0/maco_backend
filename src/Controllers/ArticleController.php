<?php 

namespace MacoBackend\Controllers;

use MacoBackend\Helpers\ArticleHelper;
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

        $condition = ArticleHelper::conditionByList($params);

        $article = new ArticleModel();
       
        $article->select(['article.*', 'user.name as user', 'article_status.name as status', 'course.name as course'])                
                ->innerjoin('user_course on article.user = user_course.user')           
                ->innerjoin('user on article.user = user.id')           
                ->innerjoin('course on user_course.course = course.id')
                ->innerjoin('article_status on article.status = article_status.id')           
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

        $user = $parsedBody['user'];
        $title = $parsedBody['title'];
        $authors = $parsedBody['authors'];  
        $advisors = $parsedBody['advisors'];             
        $keywords = $parsedBody['keywords'];  
        $summary = $parsedBody['summary'];        

        if (empty($user) || empty($title) || empty($authors) || empty($advisors) || empty($keywords) || empty($summary))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $article = new ArticleModel();
        $article->data([
            'user' => $user,
            'title' => $title,
            'authors' => $authors,
            'advisors' => $advisors,
            'keywords' => $keywords,
            'summary' => $summary,
            'status' => 1, // Status recebido
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