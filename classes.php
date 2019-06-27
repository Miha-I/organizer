<?php
$dataFile = 'data.dat';
// Класс задача
class Task
{
    // Название
    public $title;
    // Описание
    public $description;
    // Дата начала
    public $startDate;
    // Дата окончания
    public $endDate;
    // Время начала
    public $startTime;
    // Время окончания
    public $endTime;
    // Иницилизация полей
    public function __construct($_title, $_description, $_startDate, $_endDate)
    {
        $this->title = $_title;
        $this->description = $_description;
        $this->startDate = $_startDate;
        $this->endDate = $_endDate;
    }
}

// Класс список задач
class ListTask
{
    // Список задач
    private $listTask;
    // Иницилизация списка
    public function __construct()
    {
        $this->listTask = array();
    }
    // Добавление задачи
    public function addTask($title, $description, $startDate, $endDate)
    {
           $this->listTask[] = new Task($title, $description, $startDate, $endDate);
           return array_search(current($this->listTask), $this->listTask);
    }
    // Удаление задачи
    public function removeTask($key)
    {
        if(isset($this->listTask[$key])){
            unset($this->listTask[$key]);
        }
        else{
            throw new Exception('Value with the key not found');
        }
    }
    // Получение списка задач по диапазону дат
    /*public function getTasks($startDate, $endDate)
    {
        if($startDate && $endDate) {
            return array_filter($this->listTask, function ($task) use ($startDate, $endDate) {
                return strtotime($task->endDate) >= strtotime($startDate) && strtotime($task->startDate) <= strtotime($endDate);
            });
        }
        else{
            throw new Exception('Parameters of the all not specified');
        }
    }*/
    // Список всех задач
    public function getAllTasks()
    {
        return $this->listTask;
    }
}

// Класс органайзер
class Organizer
{
    // Объект для охранения списка задач
    private $db;
    // Объект список задач
    private $listTask;
    // Иницилизация списка задач
    public function __construct()
    {
        global $dataFile;
        if(file_exists($dataFile)){
            $this->openTasks();
        }
        else{
            $this->listTask = new ListTask();
        }
    }
    // Добавление задач в список
    public function addTask($task)
    {
        if(!$task){
            throw new Exception('No data');
        }
        $task = json_decode($task);
        // Проверка на соответствие типу
        if(gettype($task->title) === 'string' && (!property_exists($task, 'description') || gettype($task->description) === 'string')
                                    && gettype($task->start) === 'string' && gettype($task->end) === 'string') {
            // Проверка на соответствие всех параметров
            if(!$task->title || !$task->start || !$task->end) {
                throw new Exception('Parameters of the all not specified');
            }
            $pattern = '/^[0-2][0-9]{3}-[0,1][0-9]-[0-3][0-9](T[0-2][0-9]:[0-5][0-9]:[0-5][0-9])?$/';
            // Проверка корректности дат
            if(!preg_match($pattern, $task->start) && !preg_match($pattern, $task->end)){
                throw new Exception('Invalid date format');
            }
            // Добавление задачи
            $id = $this->listTask->addTask($task->title, $task->description, $task->start, $task->end);
            return array('status' => 'OK', 'id' => $id);
        }
        else{
            throw new Exception('Data does not match the type');
        }
    }
    // Удаление задачи из списка
    public function removeTask($task)
    {
        $task = json_decode($task);
        if(!isset($task->id)){
            throw new Exception('No data');
        }
        if( gettype($task->id) === 'integer'){
            $this->listTask->removeTask($task->id);
            return array('status' => 'OK');
        }
        else{
            throw new Exception('Data does not match the type');
        }
    }
    // Получение задач за определённый период
    public function getTasks($start = '', $end = '')
    {
        if(!$start && !$end){
            throw new Exception('Range is not selected');
        }
        $pattern = '/^[0-2][0-9]{3}-[0,1][0-9]-[0-3][0-9]$/';
        // Проверка корректности дат
        if(!preg_match($pattern, $start) && !preg_match($pattern, $end)){
            throw new Exception('Invalid date format');
        }
        // Создание списка задач
        $result = array();
        foreach ($this->listTask->getAllTasks() as $key=>$task){
            if(strtotime($task->endDate) >= strtotime($start) && strtotime($task->startDate) <= strtotime($end)){
                $result[] = array('id' => $key, 'title' => $task->title, 'start' => $task->startDate, 'end' => $task->endDate, 'description' => $task->description );
            }
        }
        return $result;
    }
    // Сохранение списка задач
    public function saveTasks()
    {
        global $dataFile;
        $this->db = new DB($dataFile);
        $this->db->saveListTask($this->listTask);
        session_destroy();
        return array('status' => 'OK');
    }
    // Открытие списка задач
    private function openTasks()
    {
        global $dataFile;
        $this->db = new DB($dataFile);
        $this->listTask = $this->db->openListTask();
        return array('status' => 'OK');
    }
}

// Класс сохранения списка задач
class DB
{
    private $file;
    // Иницилизация имени файла
    public function __construct($fileName)
    {
        $this->file = $fileName;
    }
    // Сохранение списка в файл
    public function saveListTask($listTask)
    {
        $dateStr = serialize($listTask);
        file_put_contents($this->file, $dateStr);
    }
    // Загрузка списка из файла
    public function openListTask()
    {
        $dateStr = file_get_contents($this->file);
        return unserialize($dateStr);
    }
}