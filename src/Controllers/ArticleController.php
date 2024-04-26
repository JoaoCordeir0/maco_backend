<?php 

namespace MacoBackend\Controllers;

use MacoBackend\Helpers\ArticleHelper;
use MacoBackend\Helpers\UserHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\ArticleModel;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserCourseModel;

final class ArticleController
{
    /**
    * Realiza a listagem dos artigos
    *    
    * @return Response
    */
    public function listByAdmin(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'This user is not a admin']));
            return $response;
        }

        $params = (object) $request->getQueryParams();     

        $condition = ArticleHelper::conditionByList($params);  
       
        $article = new ArticleModel();       
        $article->select(['article.*', 'user.name as author', 'article_status.name as status', 'course.name as course'])                
                ->innerjoin('user on article.user = user.id')           
                ->innerjoin('course on article.course = course.id')
                ->innerjoin('article_status on article.status = article_status.id')         
                ->where($condition)     
                ->orderby()
                ->get(true);              
                
        $response->getBody()->write(json_encode($article->result()));                                     

        return $response;
    }  

    /**
    * Realiza a listagem dos artigos de um revisor
    *    
    * @return Response
    */
    public function listByAdvisor(Request $request, Response $response, $args): Response
    {                   
        if (UserHelper::checkUserRole($request, RoleModel::ADVISOR)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'This user is not a advisor']));
            return $response;
        }

        $advisor_id = $args['id'];
        
        $params = (object) $request->getQueryParams();   
        
        $condition = ArticleHelper::conditionByListByAdvisor($params);

        $article = new ArticleModel();
        $article->select(['article.*', 'user.name as author', 'article_status.name as status', 'course.name as course'])                                
                ->innerjoin('user on article.user = user.id')           
                ->innerjoin('course on article.course = course.id')
                ->innerjoin('article_status on article.status = article_status.id')           
                ->where("article.status = 2 and course.id in (select course from user_course where user = {$advisor_id})" . $condition)     
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

        if (empty($user) || empty($title) || empty($authors) || empty($advisors) || empty($keywords) || empty($summary)) {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $userCourse = new UserCourseModel();
        $userCourse->select(['course'])
                   ->where("user = {$user}")
                   ->orderby('id', 'DESC')         
                   ->limit(1)          
                   ->get();  

        $article = new ArticleModel();
        $article->data([
            'user' => $user,
            'course' => $userCourse->result()->course,
            'title' => $title,
            'authors' => $authors,
            'advisors' => $advisors,
            'keywords' => $keywords,
            'summary' => $summary,
            'status' => 1, // Status recebido
        ])->insert();              
        
        if ($article->result()->status != 'success') {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $article->result()->message
            ]));
            return $response;                        
        }

        $response->getBody()->write(json_encode([
            'status' => $article->result()->status,                     
            'message' => 'Article inserted successfully',                                             
        ]));        
        
        return $response;
    } 

    /**
    * Realiza a edição do status do artigo
    *    
    * @return Response
    */
    public function status(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $id = $parsedBody['id'];
        $status = $parsedBody['status'];        

        if (empty($id) || empty($status))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $article = new ArticleModel();
        $article->data(['status' => $status])
                ->where("id = {$id}")
                ->update();              
                
        if ($article->result()->status != 'success') {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $article->result()->message
            ]));
            return $response;                        
        }

        $response->getBody()->write(json_encode([
            'status' => $article->result()->status,                     
            'message' => 'Article status update successfully',                                             
        ]));        
        
        return $response;            
    }
}