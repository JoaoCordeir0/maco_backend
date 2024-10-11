<?php 

namespace MacoBackend\Controllers;

use Exception;
use MacoBackend\Helpers\ArticleHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\UserModel;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserCourseModel;
use MacoBackend\Helpers\LogHelper;
use MacoBackend\Services\EmailService;
use MacoBackend\Services\PDFService;

final class UserController
{
    /**
    * Realiza a validação do login e retorna informações do usuário
    *    
    * @return Response
    */
    public function login(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $email = $parsedBody['email'];
        $password = $parsedBody['password'];                    

        if (empty($email) || empty($password)) {                        
            return ResponseController::message($response, 'error', 'Empty email or password');         
        }

        $user = new UserModel();    
        $user->select()        
             ->where("email = '{$email}' and status = 1")
             ->limit(1)                        
             ->get();          

        if (isset($user->result()->id) && password_verify($password, $user->getPassword()))
        {                   
            $infoUser = array(
                'id' => $user->getID(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'ra' => $user->getRA(),    
                'role' => UserHelper::formatUserRole($user->getRole()), 
            );
            
            $data = (object) [
                'status' => 'success',                     
                'message' => 'User login success',                   
                'token' => UserHelper::generateJWT($infoUser),   
                'user' => $infoUser,                       
            ];            

            return ResponseController::data($response, $data);                         
        }   
        return ResponseController::message($response, 'error', 'Incorrect email or password');                   
    }  

    /**
    * Admin realiza login em qualquer usuário
    *    
    * @return Response
    */
    public function loginAdmin(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();        

        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not a admin');            
        }
        
        $id = $parsedBody['user'];        

        if (empty($id)) {                        
            return ResponseController::message($response, 'error', 'Missing information');         
        }        

        $user = new UserModel();    
        $user->select()        
             ->where("id = {$id}")
             ->get();          

        if (isset($user->result()->id))
        {                   
            $infoUser = array(
                'id' => $user->getID(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'ra' => $user->getRA(),    
                'role' => UserHelper::formatUserRole($user->getRole()), 
            );
            
            $data = (object) [
                'status' => 'success',                     
                'message' => 'User login success',                   
                'token' => UserHelper::generateJWT($infoUser),   
                'user' => $infoUser,                       
            ];            

            return ResponseController::data($response, $data);                         
        }   
        return ResponseController::message($response, 'error', 'User not found');                   
    }  

    /**
    * Realiza o cadastro de um usuário
    *    
    * @return Response
    */
    public function register(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $cpf = $parsedBody['cpf'];
        $email = $parsedBody['email'];
        $password = $parsedBody['password'];           
        $ra = $parsedBody['ra'];        
        $course = $parsedBody['course'];           

        if (empty($name) || empty($cpf) || empty($email) || empty($password) || empty($ra) || empty($course)) {                        
            return ResponseController::message($response, 'error', '    ');         
        }

        if (! UserHelper::validateEmail($email)) {                         
            return ResponseController::message($response, 'error', 'Invalid email');                     
        }

        $user = new UserModel();    
        $user->data([
            'name' => $name,
            'cpf' => UserHelper::cleanDocument($cpf),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'ra' => $ra,
            'role' => RoleModel::AUTHOR,
            'status' => 1,
        ])->insert();         

        $user_course = new UserCourseModel();
        $user_course->data(['user' => $user->result()->returnid, 'course' => $course])->insert(); 

        LogHelper::log('Usuário', 'Cadastro de usuário', $request);

        if ($user->result()->status == 'success' && $user_course->result()->status == 'success') {            
            return ResponseController::message($response, $user->result()->status, 'Registration completed successfully');         
        }        
        return ResponseController::message($response, 'error', $user->result()->message . $user_course->result()->message);                     
    }     

    /**
    * Realiza a edição de um usuário
    *    
    * @return Response
    */
    public function edit(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $id = $parsedBody['id'];

        $_id = UserHelper::getUserInToken($request, 'id');

        if (empty($id)) {                        
            return ResponseController::message($response, 'error', 'Missing information');         
        }
        
        $user_role = new UserModel();
        $user_role->select(['role'])
                  ->where("id = {$_id}")
                  ->get();
        
        if ($user_role->result()->role != RoleModel::ADMIN) {
            if ($parsedBody['role'] < $user_role->result()->role) {
                return ResponseController::message($response, 'error', 'Operation denied!'); 
            }  
        } 

        $data = [
            'name' => $parsedBody['name'],
            'cpf' => UserHelper::cleanDocument($parsedBody['cpf']),
            'email' => $parsedBody['email'],            
            'ra' => $parsedBody['ra'],            
        ];

        if ($parsedBody['password'] != '') {
            $data += ['password' => password_hash($parsedBody['password'], PASSWORD_DEFAULT)];
        }

        $data += [
            'role' => $parsedBody['role'],
            'status' => $parsedBody['status'],
        ];
        
        $user = new UserModel();    
        $user->data($data)
             ->where("id = {$id}")
             ->update();    

        LogHelper::log('Usuário', 'Edição de usuário', $request);

        if ($user->result()->status == 'success') {            
            return ResponseController::message($response, $user->result()->status, 'Update completed successfully');         
        }                
        return ResponseController::message($response, 'error', $user->result()->message);                     
    }     

    /**
    * Realiza o envio da recuperação de senha caso o email do usuário for encontrado
    *    
    * @return Response
    */
    public function recoverPassword(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $email = $parsedBody['email'];        

        if (empty($email)) {            
            return ResponseController::message($response, 'error', 'Empty email');                
        }

        $user = new UserModel();    
        $user->select(['id', 'name', 'email'])        
             ->where("email = '{$email}'")             
             ->get();  

        if (isset($user->result()->id))
        {                       
            $lastname = array_reverse(explode(' ', $user->getName()))[0];
            $new_pass = UserHelper::generatePassword($lastname);     

            # EmailService::sendMail('title email', 'html email', $user->getEmail(), $user->getName());           
        }  

        LogHelper::log('Usuário', 'Recuperação de senha', $request);

        return ResponseController::message($response, 'success', 'Email sent to ' . $email);          
    } 

    /**
    * Altera a senha de um usuário
    *    
    * @return Response
    */
    public function changePassword(Request $request, Response $response)
    {                           
        return $response;
    } 

    /**
    * Realiza a listagem dos usuários
    *    
    * @return Response
    */
    public function listUsers(Request $request, Response $response, $args): Response
    {                        
        $params = (object) $request->getQueryParams();     
     
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN) && $params->mode != 'author' && $params->mode != 'advisor') {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not admin'); 
        }              

        if ((($params->mode == 'author' || $params->mode == 'advisor') && $params->user_info == '') && UserHelper::getUserInToken($request, 'id') != $params->user_id) {
            return ResponseController::message($response, 'error', 'Operation denied!'); 
        }

        $condition = UserHelper::conditionByList($params);

        $users = new UserModel();
        $users->select(['user.*'])                             
              ->where($condition)     
              ->orderby()
              ->get(true);                           
        
        $data = [];
        foreach($users->result() as $user) {        
            $userID = $user['id'];                         
    
            $courses = new UserCourseModel();
            $courses->select(['course.id', 'course.name', 'course.description', 'user_course.created_at'])
                    ->innerjoin('course on course.id = user_course.course')
                    ->where("user = {$userID}")                    
                    ->get(true);

            array_push($data, array_merge($user, [
                'courses' => $courses->result(),
            ]));                                 
        }       
                
        return ResponseController::data($response, (object) $data);
    }  
    
    /**
     * Função que valida um token
     * 
     * @return Response
     */
    public function validToken(Request $request, Response $response, $args): Response
    {
        $token = ($request->getParsedBody())['token'];

        if (empty($token)) {            
            return ResponseController::message($response, 'error', 'Token must not be empty');                
        }

        if (UserHelper::isValidToken($token)) {                         
            return ResponseController::message($response, 'success', 'Valid token');                     
        }
        return ResponseController::message($response, 'error', 'Invalid token');    
    }

    /**
     * Realiza o export do certificado
     * 
     * @return Response
     */
    public function export(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();                                
        
        $article = $parsedBody['article'];
        $user = $parsedBody['user'];           

        if (empty($article) || empty($user)) {                        
            return ResponseController::message($response, 'error', 'Missing information');         
        }    

        $article = (array) ArticleHelper::getArticle("article.id = {$article}");
        
        if ($article[0]['status'] != 'finished') {            
            return ResponseController::message($response, 'error', 'Article not finished');
        }

        $userInArticle = false;
        foreach((array) $article[0]['authors'] as $author) {
            if ($user == $author['id']) {
                $userInArticle = true;
                $article[0]['authors'] = $author;
            }
        }                
        
        if (! $userInArticle) {
            return ResponseController::message($response, 'error', 'User not in article');
        }
        
        LogHelper::log('Artigo', 'Exportação do certificado para PDF', $request);

        try {
            $pdfService = new PDFService();
            $pdf = $pdfService->exportPDF($article[0]);
            return ResponseController::data($response, (object) ['status' => 'success', 'file' => $pdf]);        
        } catch(Exception $e) {
            return ResponseController::message($response, 'error', $e->getMessage());
        }                
    }

    /**
    * Realiza a adição de um curso ao usuário
    *    
    * @return Response
    */
    public function addCourse(Request $request, Response $response, $args): Response
    {                        
        $parsedBody = $request->getParsedBody();

        $user = $parsedBody['user'];
        $course = $parsedBody['course'];

        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied!'); 
        }           
        
        if (empty($user) || empty($course)) {                        
            return ResponseController::message($response, 'error', 'Missing information');         
        }   
                
        $user_course = new UserCourseModel();
        $user_course->data(['user' => $user, 'course' => $course])->insert(); 

        LogHelper::log('Usuário', 'Vinculação de curso no usuário', $request);

        if ($user_course->result()->status == 'success') {            
            return ResponseController::message($response, $user_course->result()->status, 'Course registred success');         
        }        
        return ResponseController::message($response, 'error', $user_course->result()->debug);    
    }  

    /**
    * Realiza a remoção de um curso do usuário
    *    
    * @return Response
    */
    public function removeCourse(Request $request, Response $response, $args): Response
    {                        
        $user = $args['userid'];
        $course = $args['courseid'];          

        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied!'); 
        }    

        if (empty($user) || empty($course)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $user_course = new UserCourseModel();
        $user_course->where("user = {$user} and course = {$course}")
                    ->delete();            
        
        LogHelper::log('Usuário', 'Remoção de curso do usuário', $request);

        if ($user_course->result()->status == 'success') {            
            return ResponseController::message($response, $user_course->result()->status, 'Course removed success');                           
        }           
        return ResponseController::message($response, 'error', $user_course->result()->debug);
    }  
}