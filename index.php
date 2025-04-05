<?php
    $contenido = file_get_contents("https://elpais.com/");


    libxml_use_internal_errors(true); // Avoid warnings on malformed HTML

    $doc = new DOMDocument();
    $doc->loadHTML($contenido);

    $titles = $doc->getElementsByTagName('title');
    
    foreach($titles as $valor){
        echo $valor->textContent;
    }
    
    $enlaces = $doc->getElementsByTagName('a');

    foreach($enlaces as $valor){
        echo $valor->textContent;
        echo " - ";
        echo $valor->getAttribute("href");
        echo "<br>";
    }
?>