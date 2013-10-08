<?php
/**
 * Created by: Denis Zajchenko (denis.zaichenko@gmail.com)
 * Date: 07.10.13 20:56
 */


require 'vendor/autoload.php';
require 'helpers/Converter.php';

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

    $extensions = array('json', 'xml', 'csv');

    // check is file uploaded
    if($file['error'] !== 0){
        $error = array(
            'file' => 'File is empty. Please choose a file',
        );
        $app->render('index.php', array('error' => $error));
        $app->stop();
    }

    // check file mime type
    $ext = end(explode(".", $file['name']));
    if(!in_array($ext, $extensions)){
        $error = array(
            'file' => 'Wrong file type. Please choose json, xml or csv file type',
        );
        $app->render('index.php', array('error' => $error));
        $app->stop();
    }
    $filename = '';
    $converter = new Converter($file);
    switch($type) {
        case 'xml':
            $filename = $converter->toXml();
            break;
        case 'csv':
            $filename = $converter->toCsv();
            break;
        case 'json':
            $filename = $converter->toJson();
            break;
    }

    $app->render('index.php', array('filename' => $filename));
});

$app->run();
