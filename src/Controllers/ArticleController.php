<?php 

namespace MacoBackend\Controllers;

use MacoBackend\Helpers\ArticleHelper;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\ArticleAdvisorsModel;
use MacoBackend\Models\ArticleAuthorsModel;
use MacoBackend\Models\ArticleCommentsModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\ArticleModel;
use MacoBackend\Models\ArticleReferencesModel;
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
                    return ResponseController::message($response, 'error', 'Operation denied! User is not a admin');            
                }
                break;
            case 'advisor':
                if (UserHelper::checkUserRole($request, RoleModel::ADVISOR)) {            
                    return ResponseController::message($response, 'error', 'Operation denied! User is not a advisor');            
                }                
                $condition = ArticleHelper::getConditionAdvisor(UserHelper::getUserInToken($request, 'id'), $condition);
                break;
            case 'author':
                if (UserHelper::checkUserRole($request, RoleModel::AUTHOR)) {            
                    return ResponseController::message($response, 'error', 'Operation denied! User is not a author');            
                }                
                $condition = ArticleHelper::getConditionAuthor(UserHelper::getUserInToken($request, 'id'), $condition);
                break;
        }

        $article = new ArticleModel();       
        $article->select(['article.*', 'article_status.name as status', 'event.name as event_name'])                                                
                ->innerjoin('article_status on article.status = article_status.id')
                ->innerjoin('event on article.event = event.id')         
                ->where($condition)     
                ->orderby()
                ->get(true);              
    
        $data = [];
        foreach($article->result() as $article) {        
            $articleID = $article['id'];
            
            $comments = new ArticleCommentsModel();
            $comments->select(['user.id as user_id', 'user.name as user_name', 'comment', 'article_comments.created_at'])
                     ->innerjoin('user on user.id = article_comments.user')
                     ->where("article_comments.article = {$articleID}")
                     ->get(true);  

            $authors = new ArticleAuthorsModel();
            $authors->select(['user.id', 'user.name', 'user.cpf', 'user.email', 'user.ra', 'article_authors.course', 'course.name as course_name'])
                    ->innerjoin('user on user.id = article_authors.user')
                    ->innerjoin('course on course.id = article_authors.course')
                    ->where("article = {$articleID}")
                    ->orderby()
                    ->get(true);    
                    
            $advisors = new ArticleAdvisorsModel();
            $advisors->select(['user.id', 'user.name', 'user.cpf', 'user.email', 'user.ra', 'article_advisors.is_coadvisor'])
                     ->innerjoin('user on user.id = article_advisors.user')                     
                     ->where("article = {$articleID}")
                     ->orderby()
                     ->get(true);            
            
            $references = new ArticleReferencesModel();
            $references->select(['id', 'reference'])
                       ->where("article = {$articleID}")
                       ->get(true);

            array_push($data, array_merge($article, [
                'authors' => $authors->result(),
                'advisors' => $advisors->result(),
                'comments' => $comments->result(),
                'references' => $references->result(),
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
        $advisors = $parsedBody['advisors'];  
        $co_advisors = $parsedBody['co_advisors'];             
        $keywords = $parsedBody['keywords'];  
        $summary = $parsedBody['summary'];        

        if (empty($event) || empty($title) || empty($advisors) || empty($keywords) || empty($summary)) {            
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
            'event' => $event,
            'title' => $title, 
            'advisors' => $advisors,
            'co_advisors' => $co_advisors,
            'keywords' => $keywords,
            'summary' => $summary,
            'status' => 1, // Status recebido
        ])->insert();              
        
        $author = new ArticleAuthorsModel();
        $author->data([
            'article' => $article->getReturnID(),
            'user' => $user,
            'course' => $userCourse->getCourse(),
        ])->insert();

        if ($article->result()->status != 'success' || $author->result()->status != 'success') {                            
            return ResponseController::message($response, 'error', $article->result()->message);
        }
        return ResponseController::data($response, (object) [
            'status' => $article->result()->status, 
            'message' => 'Article inserted successfully',
            'returnid' => $article->getReturnID(),
        ]);     
    } 

    /**
    * Realiza a edição do status do artigo
    *    
    * @return Response
    */
    public function editStatus(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $article = $parsedBody['article'];
        $status = $parsedBody['status'];        

        if (empty($article) || empty($status)) {            
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleStatus = new ArticleModel();
        $articleStatus->data(['status' => $status])
                      ->where("id = {$article}")
                      ->update();              
                
        if ($articleStatus->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleStatus->result()->message);                                   
        }
        return ResponseController::message($response, $articleStatus->result()->status, 'Article status update successfully');
    }

    /**
    * Realiza a edição do titulo e resumo do artigo
    *    
    * @return Response
    */
    public function editArticle(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $article = $parsedBody['article'];
        $title = $parsedBody['title'];    
        $summary = $parsedBody['summary'];    

        if (empty($article) || empty($title) || empty($summary)) {            
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleEdit = new ArticleModel();
        $articleEdit->data(['title' => $title, 'summary' => $summary])
                    ->where("id = {$article}")
                    ->update();              
                
        if ($articleEdit->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleEdit->result()->message);                                   
        }
        return ResponseController::message($response, $articleEdit->result()->status, 'Article data update successfully');
    }

    /**
    * Realiza a edição da palavras chave do artigo
    *    
    * @return Response
    */
    public function editKeywords(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $article = $parsedBody['article'];
        $keywords = $parsedBody['keywords'];        

        if (empty($article)) {            
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleKeys = new ArticleModel();
        $articleKeys->data(['keywords' => $keywords])
                    ->where("id = {$article}")
                    ->update();              
                
        if ($articleKeys->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleKeys->result()->message);                                   
        }
        return ResponseController::message($response, $articleKeys->result()->status, 'Article keywords update successfully');
    }

    /**
    * Realiza a exclusão de um artigo
    *    
    * @return Response
    */
    public function delArticle(Request $request, Response $response, $args): Response
    {                
        $articleID = $args['articleid'];

        if (empty($articleID)) {
            return ResponseController::message($response, 'error', 'Missing information');            
        }        
        
        $authorDel = new ArticleAuthorsModel();
        $authorDel->where("article = {$articleID}")                   
                  ->delete();    

        $advisorDel = new ArticleAdvisorsModel();
        $advisorDel->where("article = {$articleID}")                   
                   ->delete();  

        $referenceDel = new ArticleReferencesModel();
        $referenceDel->where("article = {$articleID}")                   
                     ->delete();

        $articleDel = new ArticleModel();
        $articleDel->where("id = {$articleID} and status = 1")                   
                   ->delete();                            
                
        if ($authorDel->getStatus() != 'success' && $advisorDel->getStatus() != 'success' && $referenceDel->getStatus() != 'success' && $articleDel->getStatus() != 'success') {            
            return ResponseController::message($response, 'error', $articleDel->result()->message);                                   
        }
        return ResponseController::message($response, $articleDel->result()->status, 'Article delete successfully');
    }

    /**
    * Realiza a inserção de autores no artigo
    *    
    * @return Response
    */
    public function addAuthor(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $article = $parsedBody['article'];
        $author = $parsedBody['author'];          

        if (empty($article) || empty($author)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $authorCourse = new UserCourseModel();
        $authorCourse->select(['course'])
                     ->where("user = {$author}")
                     ->orderby('id', 'DESC')
                     ->limit(1)
                     ->get();   

        $articleAuthor = new ArticleAuthorsModel();
        $articleAuthor->data([
            'user' => $author,
            'article' => $article,
            'course' => $authorCourse->getCourse(),           
        ])->insert();            
        
        if ($articleAuthor->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleAuthor->result());                        
        }           
        return ResponseController::message($response, $articleAuthor->result()->status, 'Author inserted successfully');
    } 

    /**
    * Realiza a exclusão de um autor do artigo
    *    
    * @return Response
    */
    public function delAuthor(Request $request, Response $response, $args): Response
    {                        
        $articleID = $args['articleid'];
        $userID = $args['authorid'];

        if (empty($articleID) || empty($userID)) {
            return ResponseController::message($response, 'error', 'Missing information');            
        }        
        
        $authorDel = new ArticleAuthorsModel();
        $authorDel->where("article = {$articleID} and user = {$userID}")                   
                  ->delete();              
                
        if ($authorDel->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $authorDel->result()->message);                                   
        }
        return ResponseController::message($response, $authorDel->result()->status, 'Author delete successfully');
    }

    /**
    * Realiza a inserção de revisor no artigo
    *    
    * @return Response
    */
    public function addAdvisor(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $article = $parsedBody['article'];
        $advisor = $parsedBody['advisor'];   
        $coadvisor = $parsedBody['coadvisor'];          

        if (empty($article) || empty($advisor)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }       

        $advisorAuthor = new ArticleAdvisorsModel();
        $advisorAuthor->data([
            'user' => $advisor,
            'article' => $article,        
            'is_coadvisor' => $coadvisor,     
        ])->insert();            
        
        if ($advisorAuthor->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $advisorAuthor->result());                        
        }           
        return ResponseController::message($response, $advisorAuthor->result()->status, 'Advisor inserted successfully');
    } 

    /**
    * Realiza a exclusão de um revisor do artigo
    *    
    * @return Response
    */
    public function delAdvisor(Request $request, Response $response, $args): Response
    {                        
        $articleID = $args['articleid'];
        $advisorID = $args['advisorid'];

        if (empty($articleID) || empty($advisorID)) {
            return ResponseController::message($response, 'error', 'Missing information');            
        }        
        
        $advisorDel = new ArticleAdvisorsModel();
        $advisorDel->where("article = {$articleID} and user = {$advisorID}")                   
                   ->delete();              
                
        if ($advisorDel->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $advisorDel->result()->message);                                   
        }
        return ResponseController::message($response, $advisorDel->result()->status, 'Advisor delete successfully');
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
    
    /**
    * Realiza a inserção de um comentário de revisão no artigo
    *    
    * @return Response
    */
    public function addReference(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();
        
        $article = $parsedBody['article'];
        $reference = $parsedBody['reference'];          

        if (empty($article) || empty($reference)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleRef = new ArticleReferencesModel();
        $articleRef->data([            
            'article' => $article,
            'reference' => $reference,           
        ])->insert();            
        
        if ($articleRef->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleRef->result()->debug);                        
        }           
        return ResponseController::message($response, $articleRef->result()->status, 'Article reference inserted successfully');
    } 

    /**
    * Realiza a exclusão de uma referência de revisão no artigo
    *    
    * @return Response
    */
    public function delReference(Request $request, Response $response, $args): Response
    {        
        $article = $args['articleid'];
        $refID = $args['refid'];          

        if (empty($article) || empty($refID)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleRef = new ArticleReferencesModel();
        $articleRef->where("article = {$article} and id = {$refID}")
                   ->delete();            
        
        if ($articleRef->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleRef->result()->debug);                        
        }           
        return ResponseController::message($response, $articleRef->result()->status, 'Article reference deleted successfully');
    } 

    
    /**
    * Realiza a edição das referências bibliograficas de um artigo
    *    
    * @return Response
    */
    public function editReference(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();
        
        $article = $parsedBody['article'];
        $refID = $parsedBody['ref_id'];
        $refStr = $parsedBody['ref_str'];        

        if (empty($article)) {            
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $articleKeys = new ArticleReferencesModel();
        $articleKeys->data(['reference' => $refStr])
                    ->where("id = {$refID} and article = {$article}")
                    ->update();              
                
        if ($articleKeys->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleKeys->result()->message);                                   
        }
        return ResponseController::message($response, $articleKeys->result()->status, 'Article references update successfully');
    }
}