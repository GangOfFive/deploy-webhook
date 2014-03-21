<?php

define('TOMCAT_DIR', '/var/lib/tomcat7/webapps/');
define('HIBERNATE_CONFIG', 'HibernateConfig.java');
define('DEPLOYS_TO_KEEP', 5);

@list($_, $repoUrl, $repoName, $branchName) = $argv;

if ($repoUrl && $repoName && $branchName) {
    unlink(__DIR__.'/debug.log');
    # clone repo
    $dest = __DIR__.'/build/'.$repoName.'/'.$branchName;
    cdexec('.', 'rm -rf '.escapeshellarg($dest));
    cdexec('.', 'mkdir -p '.escapeshellarg($dest));
    cdexec($dest, 'git clone '.escapeshellarg($repoUrl));
    
    # check out branch
    $dest = $dest.'/'.$repoName;
    cdexec($dest, 'git fetch');
    cdexec($dest, 'git checkout '.escapeshellarg($branchName));
    
    # overwrite database
    $dbName = str_replace('-', '', $branchName);
    $sql = "DROP DATABASE IF EXISTS {$dbName}; CREATE DATABASE {$dbName}";
    cdexec('.', 'echo '.escapeshellarg($sql).' | mysql --user="root" --password=""');
    
    # replace configs
    $findHibernateConfig = '$(find . -name '.escapeshellarg(HIBERNATE_CONFIG).')';
    $dbUrl = preg_quote('jdbc:mysql://localhost:3306/'.$dbName, '/');
    cdexec($dest, 'sed -i \'s/url = ".*"/url = "'.$dbUrl.'"/\' '.$findHibernateConfig);
    cdexec($dest, 'sed -i \'s/username = ".*"/username = "root"/\' '.$findHibernateConfig);
    cdexec($dest, 'sed -i \'s/password = ".*"/password = ""/\' '.$findHibernateConfig);

    # package
    cdexec($dest, 'export JAVA_TOOL_OPTIONS="-Dfile.encoding=UTF8" && mvn clean package -Dmaven.test.skip=true');
    
    # stop tomcat and delete current branch
    $dest = $dest.'/target';
    cdexec('.', 'sudo service tomcat7 stop');
    cdexec('.', 'sudo chmod -R g+w '.TOMCAT_DIR.$branchName);
    cdexec('.', 'rm -rf '.TOMCAT_DIR.$branchName);
    cdexec('.', 'rm -rf '.TOMCAT_DIR.$branchName.'.war');

    # remove all but newest deployments
    foreach (getdirsbydate(TOMCAT_DIR) as $i => $f) {
        if ($i > DEPLOYS_TO_KEEP) {
            cdexec(TOMCAT_DIR, 'rm -rf '.$f.' '.$f.'.war');
        }
    }

    # deploy to tomcat
    cdexec($dest, 'cp *.war '.TOMCAT_DIR.$branchName.'.war');
    cdexec('.', 'sudo service tomcat7 start');
}

function cdexec($dest, $command) {
    $result = 1;
    $command = '(cd '.escapeshellarg($dest)." && {$command}) 2>&1";
    
    $data = date('Y-m-d H:i:s').' - '.$command."\n";
    file_put_contents(__DIR__.'/debug.log', $data, FILE_APPEND);
    echo $data;
    exec($command, &$output, $result);
    $data = implode("\n", $output)."\n====================\n";
    file_put_contents(__DIR__.'/debug.log', $data, FILE_APPEND);
    echo $data;

    return $result == 0;
}

function getdirsbydate($dir) {
    $tmp = array();
    foreach (glob($dir.'/*', GLOB_ONLYDIR) as $f){
        $tmp[basename($f)] = filemtime($f);
    }
    arsort($tmp);
    return array_keys($tmp);
}
