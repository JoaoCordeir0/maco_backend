<?php 

namespace MacoBackend\Controllers;

use MacoBackend\Helpers\ArticleHelper;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\ArticleCommentsModel;
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
    public function listArticles(Request $request, Response $response, $args): Response
    {    
        $params = (object) $request->getQueryParams();     

        $condition = ArticleHelper::conditionByList($params);  
        
        switch($args['role']) {
            case 'admin':
                if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
                    return ResponseController::message($response, 'error', 'This user is not a admin');            
                }
                break;
            case 'advisor':
                if (UserHelper::checkUserRole($request, RoleModel::ADVISOR)) {            
                    return ResponseController::message($response, 'error', 'This user is not a advisor');            
                }                
                $condition = ArticleHelper::getConditionAdvisor(UserHelper::getUserInToken($request, 'id'), $condition);
                break;
            case 'author':
                if (UserHelper::checkUserRole($request, RoleModel::AUTHOR)) {            
                    return ResponseController::message($response, 'error', 'This user is not a author');            
                }                
                $condition = ArticleHelper::getConditionAuthor(UserHelper::getUserInToken($request, 'id'), $condition);
                break;
        }

        $article = new ArticleModel();       
        $article->select(['article.*', 'user.name as author', 'article_status.name as status', 'course.name as course', 'events.name as event_name'])                
                ->innerjoin('user on article.user = user.id')           
                ->innerjoin('course on article.course = course.id')
                ->innerjoin('article_status on article.status = article_status.id')
                ->innerjoin('events on article.event = events.id')         
                ->where($condition)     
                ->orderby()
                ->get(true);                   

        $data = [];
        foreach($article->result() as $article) {        
            $articleID = $article['id'];
            $comments = new ArticleCommentsModel();
            $comments->select(['comment'])
                     ->where("article = {$articleID}")
                     ->get(true);  
            
            array_push($data, array_merge($article, [
                'comments' => $comments->result()
            ]));                                 
        }        

        return ResponseController::data($response, (object) $data);
    }  
    
    /**
    * Realiza a inserção de um artigo
    *    
    * @return Response
    */
    public function addArticle(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $user = UserHelper::getUserInToken($request, 'id');
        $event = $parsedBody['event'];
        $title = $parsedBody['title'];
        $authors = $parsedBody['authors'];  
        $advisors = $parsedBody['advisors'];             
        $keywords = $parsedBody['keywords'];  
        $summary = $parsedBody['summary'];        

        if (empty($event) || empty($title) || empty($authors) || empty($advisors) || empty($keywords) || empty($summary)) {            
            return ResponseController::message($response, 'error', 'Missing information');            
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
            'event' => $event,
            'title' => $title,
            'authors' => $authors,
            'advisors' => $advisors,
            'keywords' => $keywords,
            'summary' => $summary,
            'status' => 1, // Status recebido
        ])->insert();              
        
        if ($article->result()->status != 'success') {                            
            return ResponseController::message($response, 'error', $article->result()->message);
        }
        return ResponseController::message($response, $article->result()->status, 'Article inserted successfully');     
    } 

    /**
    * Realiza a edição do status do artigo
    *    
    * @return Response
    */
    public function updateStatus(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $id = $parsedBody['id'];
        $status = $parsedBody['status'];        

        if (empty($id) || empty($status)) {            
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleStatus = new ArticleModel();
        $articleStatus->data(['status' => $status])
                ->where("id = {$id}")
                ->update();              
                
        if ($articleStatus->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleStatus->result()->message);                                   
        }
        return ResponseController::message($response, $articleStatus->result()->status, 'Article status update successfully');
    }

    /**
    * Realiza a exclusão de um artigo
    *    
    * @return Response
    */
    public function delArticle(Request $request, Response $response, $args): Response
    {                
        $articleID = $args['id'];

        if (empty($articleID)) {
            return ResponseController::message($response, 'error', 'Missing information');            
        }        
        
        $articleDel = new ArticleModel();
        $articleDel->where("id = {$articleID} and status = 1")                   
                   ->delete();              
                
        if ($articleDel->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleDel->result()->message);                                   
        }
        return ResponseController::message($response, $articleDel->result()->status, 'Article delete successfully');
    }

    /**
    * Realiza a inserção de um comentário de revisão no artigo
    *    
    * @return Response
    */
    public function addComment(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $user = UserHelper::getUserInToken($request, 'id');
        $article = $parsedBody['article'];
        $comment = $parsedBody['comment'];          

        if (empty($article) || empty($comment)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleComment = new ArticleCommentsModel();
        $articleComment->data([
            'user' => $user,
            'article' => $article,
            'comment' => $comment,           
        ])->insert();            
        
        if ($articleComment->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleComment->result()->debug);                        
        }           
        return ResponseController::message($response, $articleComment->result()->status, 'Article comment inserted successfully');
    } 
}