<?php
$ready = '🚀 Ready';
$archived = '📦 Archived';
$categories = ['Work', 'Uni', 'Personal'];
date_default_timezone_set('Asia/Jerusalem');

/*

$$\      $$\                     $$\        $$$$$$\  $$\                                   
$$ | $\  $$ |                    $$ |      $$  __$$\ $$ |                                  
$$ |$$$\ $$ | $$$$$$\   $$$$$$\  $$ |  $$\ $$ /  \__|$$ | $$$$$$\  $$\  $$\  $$\ $$\   $$\ 
$$ $$ $$\$$ |$$  __$$\ $$  __$$\ $$ | $$  |$$$$\     $$ |$$  __$$\ $$ | $$ | $$ |$$ |  $$ |
$$$$  _$$$$ |$$ /  $$ |$$ |  \__|$$$$$$  / $$  _|    $$ |$$ /  $$ |$$ | $$ | $$ |$$ |  $$ |
$$$  / \$$$ |$$ |  $$ |$$ |      $$  _$$<  $$ |      $$ |$$ |  $$ |$$ | $$ | $$ |$$ |  $$ |
$$  /   \$$ |\$$$$$$  |$$ |      $$ | \$$\ $$ |      $$ |\$$$$$$  |\$$$$$\$$$$  |\$$$$$$$ |
\__/     \__| \______/ \__|      \__|  \__|\__|      \__| \______/  \_____\____/  \____$$ |
                                                                                 $$\   $$ |
                                                                                 \$$$$$$  |
                                                                                  \______/ 
$$$$$$$\            $$\ $$\            $$\                                                 
$$  __$$\           $$ |$$ |           $$ |                                                
$$ |  $$ |$$\   $$\ $$ |$$ | $$$$$$\ $$$$$$\                                               
$$$$$$$\ |$$ |  $$ |$$ |$$ |$$  __$$\\_$$  _|                                              
$$  __$$\ $$ |  $$ |$$ |$$ |$$$$$$$$ | $$ |                                                
$$ |  $$ |$$ |  $$ |$$ |$$ |$$   ____| $$ |$$\                                             
$$$$$$$  |\$$$$$$  |$$ |$$ |\$$$$$$$\  \$$$$  |                                            
\_______/  \______/ \__|\__| \_______|  \____/                                             
                                                                                           
                                                                                           
                                                                                           
   $$$$$\                                                   $$\                            
   \__$$ |                                                  $$ |                           
      $$ | $$$$$$\  $$\   $$\  $$$$$$\  $$$$$$$\   $$$$$$\  $$ |                           
      $$ |$$  __$$\ $$ |  $$ |$$  __$$\ $$  __$$\  \____$$\ $$ |                           
$$\   $$ |$$ /  $$ |$$ |  $$ |$$ |  \__|$$ |  $$ | $$$$$$$ |$$ |                           
$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |      $$ |  $$ |$$  __$$ |$$ |                           
\$$$$$$  |\$$$$$$  |\$$$$$$  |$$ |      $$ |  $$ |\$$$$$$$ |$$ |                           
 \______/  \______/  \______/ \__|      \__|  \__| \_______|\__|                           
                                                                                           
*/

require_once 'vendor/autoload.php';

use WorkFlowyPHP\WorkFlowy;
use WorkFlowyPHP\WorkFlowyException;
use WorkFlowyPHP\WorkFlowyList;

echo "🚀  Starting Workflowy Bullet Journal\n\n";

try
{
    $email = getenv('EMAIL');
    $password = getenv('PASSWORD');
    if (empty($email) || empty($password)) {
        exit('⚠️  Please set the EMAIL and PASSWORD envvars!');
    }

    echo "🔐  Logging in to Workflowy\n";
    $session_id = WorkFlowy::login($email, $password);
    $list_request = new WorkFlowyList($session_id);
    echo "📥  Fetching data\n";
    $root = $list_request->getList();

    echo "🔍  Finding lists\n";
    $todo = $root->searchSublist('/^Todo List$/');
    if ($todo === false) {
        exit('⚠️  Could not find todo list');
    }
    $month = $todo->searchSublist("/^" . date('F Y') . "$/");
    if ($month === false) {
        exit('⚠️  Could not find monthly list');
    }

    $yesterday = $month->searchSublist("/^" . date('d l', time() - 24 * 60 * 60) . "$/");
    if ($yesterday === false) {
        exit("⚠️  Could not find yesterday's list");
    }

    $today = $month->searchSublist("/^" . date('d l') . "$/");
    if ($today === false) {
        exit("⚠️  Could not find today's list");
    }
    if ($today->getDescription() == $ready) {
        exit("🚀  Already processed today's list");
    }

    foreach ($categories as $category) {
        $tasks = $yesterday->searchSublist("/^${category}$/");
        if ($tasks === false) {
            echo "📭  No ${category} tasks logged yesterday\n";
            continue;
        }

        $tasks = array_filter($tasks->getSublists(), function($task) {
            return !$task->isComplete() && mb_substr($task->getName(), -1, 1) != '→';
        });
        
        echo (count($tasks) > 0 ? "📬" : "📭") . "  Found " . count($tasks) . " ${category} tasks\n";
        $todays = $today->searchSublist('/^' . $category . '$/');
        foreach($tasks as $task) {
            $todays->createSublist($task->getName(), '', 0);
            $task->setName($task->getName() . "→");
        }
    }

    $yesterday->setDescription($archived);
    $today->setDescription($ready);
    exit('🚀  Done');
}
catch (WorkFlowyException $e)
{
    exit($e);
}