<?php

include_once "CommentService.php";
require_once 'RequestHandler.php';

$requestHandler = new RequestHandler();

?>

<html>
<head>
    <title>Comment service</title>
</head>
<body>
    <h1>Comment service.</h1>
    <?php $requestHandler->handleRequest(); ?>
</body>
</html>
