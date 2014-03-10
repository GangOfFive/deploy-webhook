<?php

define('TOMCAT_DIR', '/var/lib/tomcat7/webapps/');
define('HIBERNATE_CONFIG', 'HibernateConfig.java');

@list($_, $repoUrl, $repoName, $branchName) = $argv;

if ($repoUrl && $repoName && $branchName) {
    # clone repo
    $dest = __DIR__.'/build/'.$repoName.'/'.$branchName;
    cdexec('.', 'rm -rf '.escapeshellarg($dest));
    cdexec('.', 'mkdir -p '.escapeshellarg($dest));
    cdexec($dest, 'git clone '.escapeshellarg($repoUrl));
    
    # check out branch
    $dest = $dest.'/'.$repoName;
    cdexec($dest, 'git fetch');
    cdexec($dest, 'git checkout '.escapeshellarg($branchName));
    
    # create database if it doesn't exist
    $dbName = str_replace('-', '', $branchName);
    $sql = "CREATE DATABASE IF NOT EXISTS {$dbName}";
    cdexec('.', 'echo '.escapeshellarg($sql).' | mysql --user="root" --password=""');
    
    # replace configs
    $findHibernateConfig = '$(find . -name '.escapeshellarg(HIBERNATE_CONFIG).')';
    $dbUrl = preg_quote('jdbc:mysql://localhost:3306/'.$dbName, '/');
    cdexec($dest, 'sed -i \'s/url = ".*"/url = "'.$dbUrl.'"/\' '.$findHibernateConfig);
    cdexec($dest, 'sed -i \'s/username = ".*"/username = "root"/\' '.$findHibernateConfig);
    cdexec($dest, 'sed -i \'s/password = ".*"/password = ""/\' '.$findHibernateConfig);

    # package
    cdexec($dest, 'export JAVA_TOOL_OPTIONS="-Dfile.encoding=UTF8" && mvn clean package');
    
    # deploy to tomcat
    $dest = $dest.'/target';
    cdexec('.', 'sudo service tomcat7 stop');
    cdexec('.', 'rm -rf '.TOMCAT_DIR.$branchName);
    cdexec('.', 'rm -rf '.TOMCAT_DIR.$branchName.'.war');
    cdexec($dest, 'cp *.war '.TOMCAT_DIR.$branchName.'.war');
    cdexec('.', 'sudo service tomcat7 start');
}

function cdexec($dest, $command) {
    $result = 1;
    $command = '(cd '.escapeshellarg($dest)." && {$command}) 2>&1";
    
    $data = date('m-d H:i:s').' - '.$command."\n";
    file_put_contents(__DIR__.'/debug.log', $data, FILE_APPEND);
    echo $data;
    exec($command, $output=array(), $result);
    $data = implode("\n", $output)."\n====================\n";
    file_put_contents(__DIR__.'/debug.log', $data, FILE_APPEND);
    echo $data;

    return $result == 0;
}

