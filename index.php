<?php
require_once('classes.php');
session_start();
if(!isset($_SESSION['organizer'])){
    $_SESSION['organizer'] = new Organizer();
}
$organizer = $_SESSION['organizer'];
if(isset($_GET['operation'])){
    try{
        $result = null;
        switch ($_GET['operation']){
            case'get_task':
                $start = isset($_POST['start']) ? $_POST['start'] : false;
                $end = isset($_POST['end']) ? $_POST['end'] : false;
                $result = $organizer->getTasks($start, $end);
                break;
            case'add_task':
                $task = isset($_POST['task']) ? $_POST['task'] : false;
                $result = $organizer->addTask($task);
                break;
            case'remove_task':
                $task = isset($_POST['task']) ? $_POST['task'] : false;
                $result = $organizer->removeTask($task);
                break;
            case'save_tasks':
                $result = $organizer->saveTasks();
                break;
            default:
                throw new Exception('Unsupported operation: ' . $_GET['operation']);
                break;
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    catch (Exception $e){
        header($_SERVER['SERVER_PROTOCOL'].' 500 Server Error');
        header('Status:  500 Server Error');
    }
    exit();
}
readfile('organizer.html');

