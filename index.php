<?php
if(isset($_POST['url'])){
    include "Deepink.php";
    // Ejecutar análisis
    $web = new DeepPink($_POST['url']);

    echo "<h3><strong>📘 Título:</strong><br></h3>";
    $web->dameTitulo();

    $web->dameDescripcion();


    echo "<h3><br><strong>🔗 Enlaces encontrados:</strong><br><br></h3>";
    $web->dameEnlaces();
}
?>
<!Doctype html>
<html>
    <head>
       
    </head>
    <body>

<h1>Danielcreux  deeppink</h1>
<p>Introduce la url que quieres analizar </p>

<form action="?" method="POST">
    <input type="url" name="url">
    <input type="submit">
</form>
</body>
</html>