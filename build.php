<?php

define('TOMCAT_DIR', '/var/lib/tomcat7/webapps/');

@list($_, $repoUrl, $repoName, $branchName) = $argv;

if ($repoUrl && $repoName && $branchName) {
    # clone repo
    $dest = __DIR__.'/build/'.$repoName.'/'.$branchName;
    cdexec('.', 'rm -rf '.escapeshellarg($dest));
    cdexec('.', 'mkdir -p '.escapeshellarg($dest));
    cdexec($dest, 'git clone '.escapeshellarg($repoUrl));
    
    # check out branch
    $dest = $dest.'/'.$repoName;
    cdexec($dest, 'git remote update');
    cdexec($dest, 'git fetch');
    cdexec($dest, 'git checkout '.escapeshellarg($branchName));

    # package
    cdexec($dest, 'mvn package');
    
    # deploy to tomcat
    $dest = $dest.'/target';
    cdexec($dest, 'cp *.war '.TOMCAT_DIR.$branchName.'.war');
}

function cdexec($dest, $command) {
    $result = 1;
    $command = 'cd '.escapeshellarg($dest)." && {$command}";
    exec($command, $_, $result);
    file_put_contents(__DIR__.'/debug.log', time().' - '.$command."\n", FILE_APPEND);

    return $result == 0;
}
