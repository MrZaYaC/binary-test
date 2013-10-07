<?php
/**
 * Created by: Denis Zajchenko (denis.zaichenko@gmail.com)
 * Date: 07.10.13 20:56
 */


require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'templates.path' => 'templates',
));

/**
 *  index page
 */
$app->get('/', function() use ($app) {
    $app->render('index.php');
});

/**
 * upload file action
 */
$app->post('/', function() use ($app) {
    $type = $app->request()->post('type');
    $file = $_FILES['file'];

    $mimeTypes = array('application/json', 'text/xml', 'text/csv');

    // check is file uploaded
    if($file['error'] !== 0){
        $error = array(
            'file' => 'File is empty. Please choose a file',
        );
        $app->render('index.php', array('error' => $error));
        $app->stop();
    }
    // check file mime type
    if(!in_array($file['type'], $mimeTypes)){
        $error = array(
            'file' => 'Wrong file type. Please choose json, xml or csv file type',
        );
        $app->render('index.php', array('error' => $error));
        $app->stop();
    }

    $app->render('index.php');
});

$app->run();
