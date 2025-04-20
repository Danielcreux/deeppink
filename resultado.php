<!doctype html>
<html>
    <head>
        <style>
            .ok{width:20px;height:20px;border-radius:20px;background:green;}
            .ko{width:20px;height:20px;border-radius:20px;background:red;}
            span{display:inline-block;margin:3px;}
        </style>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">


       
    </head>
    <body>

            <h1>Danielcreux  deeppink</h1>
            <p>Introduce la url que quieres analizar </p>
                    <table>
                           <?php
                                if(isset($_POST['url'])){
                                    include "Deepink.php";
                                    // Ejecutar análisis
                                    $web = new DeepPink($_POST['url']);
                                    $web->dameTitulo();
                                    $web->dameDescripcion();
                                    $web->damePalabras();
                                    $web->nubedePalabras();
                                    for($i = 1;$i<=6;$i++){
                                        $web->dameTitulos($i);
                                    }
                                   
                                }
                            ?>
                    </table>

            <form action="?" method="POST">
                <input type="url" name="url">
                <input type="submit">
            </form>
    </body>
</html>