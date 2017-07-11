<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo $message ?></title>
  <style type="text/css">
  body {
    font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
    margin: 20px;
  }
  h1 {
    font-family: Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑";
    font-size: 28px;
    font-weight: 500;
    line-height: 32px;
    color: #333;
  }
  p {
    font-size: 18px;
    color: #868686;
  }
  h1, p {
    border-bottom-width: 1px;
    border-bottom-style: solid;
    border-bottom-color: #CCC;
    padding: 10px;
    margin: 0px;
  }
  </style>
</head>
<body>

<h1><?php echo $message ?></h1>

<?php if(!empty($file)): ?>
<p><?php echo $file ?> line <?php echo $line ?></p>
<p><?php echo str_replace("\n", '<br>', $trace); ?></p>
<?php endif; ?>

</body>
</html>
