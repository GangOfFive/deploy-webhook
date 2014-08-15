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

$apps = getdirsbydate('/var/lib/tomcat7/webapps');
?>
<html>
<head>
<title>Falafel Status</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body,html{margin:0;padding:0;background-image:url('bg.jpg');font-family: "Droid Sans","Helvetica Neue","Helvetica",sans-serif;}
body{padding:2em;}
#wrap{max-width:800px;background:#e0e0e0;background:rgba(255,240,255,.9);margin: 0 auto;color: #333;padding:.25em .5em;border-radius:5px;}
h1,h2,h3,h4,h5,h6{margin: .2em 0 .2em 1rem; font-weight: bold;}
h1{float:left;font-size:1.4em;}
h3{opacity:.5;}
#apps{clear:both;}
.app{display:block; border-top: #ccc 1px solid; padding: 1em;}
a:link,a:visited{transition:all .3s;text-decoration: none;color: #38c;}
a:hover{color: #49d;background: #e8e8e8;background:rgba(255,240,255,.5);}
.app p{margin: 0;margin-top:.6em;}
.app h4 {margin: 0}
nav{float:right;font-size:1.4em;font-weight:bold;margin-right:1rem;}
nav a{font-size:.7em;margin-left:4px;}
@media (max-width:420px) {body{padding:0;}}
</style>
</head>
<body>
<div id="wrap">
<div id="header">
<h1>Falafel Status</h1>
<nav>
<a href="http://falafel.villarreal.co.cr:8000/">Jenkins</a>
<a href="dash/">Dashboard</a>
</nav>
</div>
<div id="apps">
<h3>Deployments</h3>
<?php foreach ($apps as $app => $time): ?>
<a href="http://falafel.villarreal.co.cr:8080/<?php echo $app; ?>" class="app">
    <h4><?php echo $app; ?></h4>
    <p>Deployed: <?php echo date('F d Y h:i:s A', $time); ?></p>
</a>
<?php endforeach; ?>
</div>
</div>
</body>
</html>
