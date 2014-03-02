<?php

$data = file_get_contents('php://input');
file_put_contents(__DIR__.'/gh.log', $data."\n", FILE_APPEND);

$data = json_decode($data, true);

if (!empty($data)) {
    $repoUrl = $data['repository']['url'];
    $repoName = $data['repository']['name'];
    
    $ref = $data['ref']; 
    $refarr = explode('/', $ref);
    $branchName = end($refarr);
    
    exec(sprintf("%s > %s 2>&1 &",
        'php '.__DIR__.'/build.php '.escapeshellarg($repoUrl).' '.escapeshellarg($repoName).' '.escapeshellarg($branchName),
        '/dev/null'));
    
    header('Content-Type: application/json');
    echo json_encode(array('status' => 'OK'));
}
