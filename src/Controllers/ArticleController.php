<?php 

namespace MacoBackend\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Helpers\ArticleHelper;
use MacoBackend\Helpers\LogHelper;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\ArticleAdvisorsModel;
use MacoBackend\Models\ArticleAuthorsModel;
use MacoBackend\Models\ArticleCommentsModel;
use MacoBackend\Models\ArticleModel;
use MacoBackend\Models\ArticleReferencesModel;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserCourseModel;
use MacoBackend\Services\DocxService;

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
            default:
                throw new Exception('Role not informed');
                break;
        }

        $data = ArticleHelper::getArticle($condition);      

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
        $keywords = $parsedBody['keywords'];  
        $summary = $parsedBody['summary'];        

        if (empty($event) || empty($title) || empty($keywords) || empty($summary)) {            
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
        
        LogHelper::log('Article', 'add_article', $request);

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
                
        LogHelper::log('Article', 'edit_status', $request);

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
                
        LogHelper::log('Article', 'edit_article', $request);
        
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
                
        LogHelper::log('Article', 'edit_keywords', $request);

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
                
        LogHelper::log('Article', 'del_article', $request);

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
        
        LogHelper::log('Article', 'add_author', $request);

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
                
        LogHelper::log('Article', 'del_author', $request);

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
        
        LogHelper::log('Article', 'add_advisor', $request);

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
                
        LogHelper::log('Article', 'del_advisor', $request);

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
        
        LogHelper::log('Article', 'add_comment', $request);

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
        
        LogHelper::log('Article', 'add_reference', $request);

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
        
        LogHelper::log('Article', 'del_reference', $request);

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
                
        LogHelper::log('Article', 'edit_reference', $request);

        if ($articleKeys->result()->status != 'success') {            
            return ResponseController::message($response, 'error', $articleKeys->result()->message);                                   
        }
        return ResponseController::message($response, $articleKeys->result()->status, 'Article references update successfully');
    }

    /**
     * Realiza o export do artigo
     * 
     * @return Response
     */
    public function export(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        
        $article = $parsedBody['article'];
        $type = $parsedBody['type'];
        
        $data = (array) ArticleHelper::getArticle("article.id = {$article}");
        
        LogHelper::log('Article', 'export_' . $type, $request);

        try {
            switch($type) {
                case 'docx':
                    $docx = DocxService::exportDocx($data[0]);
                    return ResponseController::data($response, (object) ['file' => $docx]);
                    break;
                default:         
                    throw new Exception('Type export not informed');
                    break;
            }   
        } catch(Exception $e) {
            return ResponseController::message($response, 'error', 'Error generating file');
        }                
    }
}