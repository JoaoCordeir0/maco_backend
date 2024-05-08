<?php

use MacoBackend\Controllers\ArticleController;
use MacoBackend\Controllers\CourseController;
use MacoBackend\Controllers\EventController;
use MacoBackend\Controllers\HomeController;
use MacoBackend\Controllers\UserController;

// Default route
$app->get('/', [HomeController::class, 'home']);
$app->get('/ping', [HomeController::class, 'ping']);

$app->group('/user', function () use ($app) {
    $app->post('/user/login', [UserController::class, 'login']); // Login   
    $app->post('/user/register', [UserController::class, 'register']); // Register   
    $app->post('/user/recoverpassword', [UserController::class, 'recoverPassword']); // Recover password
    $app->get('/user/list', [UserController::class, 'listUsers']); // User list   
});

$app->group('/article', function () use ($app) {
    $app->get('/article/list/{role}', [ArticleController::class, 'listArticles']); // Article list      
    $app->post('/article/add', [ArticleController::class, 'addArticle']); // Article add        
    $app->post('/article/status', [ArticleController::class, 'updateStatus']); // Article update status        
    $app->post('/article/comment', [ArticleController::class, 'addComment']); // Article add comment        
    $app->delete('/article/del/{articleid}', [ArticleController::class, 'delArticle']); // Article del        
    $app->delete('/article/author/del/{articleid}/{authorid}', [ArticleController::class, 'delAuthor']); // Article del        
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