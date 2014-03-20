<?php
date_default_timezone_set('America/Costa_Rica');
function getdirsbydate($dir) {
    $tmp = array();
    foreach (glob($dir.'/*', GLOB_ONLYDIR) as $f){
        $tmp[basename($f)] = filemtime($f);
    }
    asort($tmp);
    return array_reverse($tmp);
}
function getlog() {
    $lines = file('debug.log');
    $ret = array();
    foreach ($lines as $line) {
       $line = trim($line);
       if (!$line || $line[0]==='=') continue;
       if (strpos($line,') 2>&1')!==false) $line = substr($line,strpos($line,'&&')+2,strpos($line,') 2>&1')-strlen($line));
       $ret[] = trim($line);
    }
    return $ret;
}

$apps = getdirsbydate('/var/lib/tomcat7/webapps');
$log = getlog();
?>
<html>
<head>
<title>Falafel Status</title>
<style>
body,html{margin:0;padding:0; background: #222;font-family: "Droid Sans","Helvetica",sans-serif;}
#wrap{max-width:1200px;min-width:800px;margin: 0 auto;color: #7af;}
h1,h2,h3,h4,h5,h6{margin: .5em 0; font-weight: bold;}
h3{opacity:.5;}
#apps{width:40%;float:left;}
#log{width:59%;float:right;}
#log-container{background:#1a1a1a;font-size:13px;font-family:"Liberation Mono","Consolas",monospace;overflow-y:scroll;overflow-x:hidden;height:600px;}
.line{padding:.25em .5em; border-bottom: #2a2a2a 1px dashed;}
.app{display:block; border-top: #469 1px dashed; padding: .5em 0;}
a:link,a:visited{text-decoration: none;color: #7af;}
a:hover{color: #9cf;background: #333;}
</style>
</head>
<body>
<div id="wrap">
<h1>Falafel Status</h1>
<div id="apps">
<h3>Branches</h3>
<?php foreach ($apps as $app => $time): ?>
<a href="http://falafel.villarreal.co.cr:8080/<?php echo $app; ?>" class="app">
    <h4><?php echo $app; ?></h4>
    <p>Deployed: <?php echo date('F d Y h:i:s A', $time); ?></p>
</a>
<?php endforeach; ?>
</div>
<div id="log">
<h3>Command Log</h3>
<div id="log-container">
<?php foreach ($log as $l): ?>
<div class="line"><?php echo $l; ?></div>
<?php endforeach; ?>
</div>
</div>
</div>
<script>
var s=document.getElementById('log-container');s.scrollTop=s.scrollHeight;
</script>
</body>
</html>
