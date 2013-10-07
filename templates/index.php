<?php
/**
 * @var $error array
 * @var $filename string
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>binary studio test</title>
    <!-- include css -->
    <link type="text/css" rel="stylesheet" href="../css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="../css/bootstrap-theme.min.css">
    <!-- include javaScript -->
    <script type="text/javascript" src="../js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <h2 class="text-center">JSON, XML, CSV Converter</h2>
            <div class="well">
                <form class="form-horizontal" role="form" enctype="multipart/form-data" method="post">
                    <div class="form-group">
                        <label for="file" class="col-lg-5">Choose file (json, xml, csv):</label>
                        <div class="col-lg-7">
                            <input type="file" name="file" id="file">
                        </div>
                    </div>
                    <?php if(isset($error['file'])) : ?>
                        <div class="alert alert-danger"><?= $error['file']?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="col-lg-5">Choose output file type:</label>
                        <div class="col-lg-7">
                            <label class="radio-inline">
                                <input type="radio" name="type" id="typeJson" value="json" checked> json
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="type" id="typeXml" value="xml"> xml
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="type" id="typeCsv" value="csv"> csv
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-5 col-lg-7">
                            <button type="submit" class="btn btn-default">Send</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php if(isset($filename) && $filename !== null): ?>
            <div class="alert alert-success">
                <h4 class="text-center">Link to converted file</h4>
                <div class="text-center">
                    <a href="upload/<?=$filename?>" target="_blank"><?=$filename?></a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- upload form -->
</body>
</html>