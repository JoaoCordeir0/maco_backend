<?php

use MacoBackend\Controllers\ArticleController;
use MacoBackend\Controllers\CourseController;
use MacoBackend\Controllers\EventController;
use MacoBackend\Controllers\HomeController;
use MacoBackend\Controllers\UserController;
use MacoBackend\Controllers\ReportController;

// Default route
$app->group('/public', function () use ($app) {    
    $app->get('/public', [HomeController::class, 'home']);
    $app->get('/public/ping', [HomeController::class, 'ping']);    
    $app->get('/public/course/list', [CourseController::class, 'listCoursesPublic']); // Public course list           
    $app->post('/public/token', [UserController::class, 'validToken']);
});

$app->group('/user', function () use ($app) {
    $app->post('/user/login', [UserController::class, 'login']); // Login   
    $app->post('/user/register', [UserController::class, 'register']); // Register   
    $app->post('/user/edit', [UserController::class, 'edit']); // Edit   
    $app->post('/user/recoverpassword', [UserController::class, 'recoverPassword']); // Recover password
    $app->get('/user/list', [UserController::class, 'listUsers']); // User list       
    $app->post('/user/certificate/export', [UserController::class, 'export']); // Article export
});

$app->group('/article', function () use ($app) {
    $app->get('/article/list/{role}', [ArticleController::class, 'listArticles']); // Article list    
    $app->post('/article/add', [ArticleController::class, 'addArticle']); // Article add     
    $app->post('/article/edit/data', [ArticleController::class, 'editArticle']); // Article title and summary       
    $app->post('/article/edit/status', [ArticleController::class, 'editStatus']); // Article update status        
    $app->post('/article/edit/keywords', [ArticleController::class, 'editKeywords']); // Article update keywords        
    $app->post('/article/add/comment', [ArticleController::class, 'addComment']); // Article add comment            
    $app->post('/article/add/author', [ArticleController::class, 'addAuthor']); // Article add author        
    $app->post('/article/add/advisor', [ArticleController::class, 'addAdvisor']); // Article add advisor        
    $app->post('/article/add/reference', [ArticleController::class, 'addReference']); // Article add reference
    $app->post('/article/edit/reference', [ArticleController::class, 'editReference']); // Article edit reference
    $app->post('/article/export', [ArticleController::class, 'export']); // Article export
    $app->delete('/article/del/{articleid}', [ArticleController::class, 'delArticle']); // Article del        
    $app->delete('/article/author/del/{articleid}/{authorid}', [ArticleController::class, 'delAuthor']); // Article del        
    $app->delete('/article/advisor/del/{articleid}/{advisorid}', [ArticleController::class, 'delAdvisor']); // Article del        
    $app->delete('/article/reference/del/{articleid}/{refid}', [ArticleController::class, 'delReference']); // Article del        
});

$app->group('/course', function () use ($app) {    
    $app->get('/course/list', [CourseController::class, 'listCourses']); // Course list               
    $app->post('/course/add', [CourseController::class, 'addCourse']); // Course add
    $app->post('/course/edit', [CourseController::class, 'editCourse']); // Course edit    
});

$app->group('/event', function () use ($app) {
    $app->get('/event/list', [EventController::class, 'listEvents']); // Event list    
    $app->post('/event/add', [EventController::class, 'addEvent']); // Event add                       
    $app->post('/event/edit', [EventController::class, 'editEvent']); // Event edit
});

$app->group('/report', function () use ($app) {
    $app->get('/report/logs', [ReportController::class, 'listLogs']); // Logs list        
});