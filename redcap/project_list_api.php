<?php

require_once "../redcap_connect.php";

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "redcap_v" . $redcap_version . DIRECTORY_SEPARATOR . "Config" . DIRECTORY_SEPARATOR . "init_functions.php";

if (!$api_enabled) exit("API is disabled!");

$db = new RedCapDB();

$username = $_GET['username'];
$token = $_GET['token'];
header('Content-Type: application/json');

$failMessage = [
    'success' => false,
    'reason' => ''
];

$availableTokens = $db->getUserSuperToken($username);
if (false && ($availableTokens != $token || trim($username) == '')) {
    $failMessage['reason'] = 'token invalid';
    exit(json_encode($failMessage));
}

function mapToDto($projectDict) {
    return [
        'id' => $projectDict->project_id,
        'name' => $projectDict->app_title,
        'description' => $projectDict->project_note,
        'created_at' => $projectDict->creation_time
    ];
}

function classPropertyHasMatchingString($classItem, $propName, $value) : bool{
    return strpos(strtolower($classItem->$propName), strtolower($value)) > -1;
}

function filter($project, $requestData) {
    $hasMatch = false;
    $hasKeys = false;
    if (array_key_exists('name', $requestData) && $requestData['name'] != null) {
        $hasKeys = true;
        $hasMatch = $hasMatch || classPropertyHasMatchingString($project, 'app_title', $requestData['name']);
    }

    if (array_key_exists('description', $requestData) && $requestData['description'] != null) {
        $hasKeys = true;
        $hasMatch = $hasMatch || classPropertyHasMatchingString($project, 'project_note', $requestData['description']);
    }

    if (!$hasKeys) {
        return true;
    }
    return $hasMatch;
}

$projects = $db->getProjects();

$results = [];
$requestData = [
    'name' => $_GET['name'],
    'description' => $_GET['description']
];

foreach ($projects as $project) {
    $dto = mapToDto($project);
    if (!filter($project, $requestData)) continue;
    $results[] = $dto;
}

$ajaxData = ['success' => 'true', 'data' => $results];
exit(json_encode($ajaxData));
