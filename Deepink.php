<?php

class DeepPink {
    private $contenido;
    private $doc;
    private $url;

    public function __construct($url) {
        $this->url = $url;
        $this->contenido = @file_get_contents($url);

        if ($this->contenido === false) {
            throw new Exception("❌ No se pudo obtener contenido de: $url");
        } else {
            $this->doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $this->doc->loadHTML($this->contenido);
            libxml_clear_errors();
        }
    }

    public function generateReport() {
        ob_start();
        ?>
        <table>
            <?php 
            $this->dameTitulo();
            $this->dameDescripcion();
            $this->damePalabras();
            $this->nubedePalabras();
            for($i = 1; $i <= 6; $i++) {
                $this->dameTitulos($i);
            }
            ?>
        </table>
        <?php
        return ob_get_clean();
    }

    public function dameTitulo() {
    $titles = $this->doc->getElementsByTagName('title');
        echo "<tr>";
            echo '<td>';
             if ($titles->length > 0) { 
                echo "<div class='ok'></div>";
            } else {
                echo "<div class='ko'></div>";
            }
            echo '</td>';
            echo '<td>';
            echo "<h4>Título del sitio</h4>";
            echo '</td>';
            echo '<td>';
             if ($titles->length > 0) {     
                    foreach ($titles as $valor) {
                        echo $valor->textContent . "<br>";
                    }
                }  echo '</td>';
        echo "</tr>";
    }
     public function dameTitulos($nivel) {
         echo "<tr>";
    $titles1 = $this->doc->getElementsByTagName('h'. $nivel);
        echo '<td>';
             if ($titles1->length > 0) { 
                echo "<div class='ok'></div>";
            } else {
                echo "<div class='ko'></div>";
            }
            echo '</td>';
            echo '<td>';
            echo "<h4>Etiquetas de tipo H".$nivel."</h4>";
            echo '</td>';
            echo '<td>';
             if ($titles1->length > 0) {     
                    foreach ($titles1 as $valor) {
                        echo $valor->textContent . "<br>";
                    }
                }  echo '</td>';
        echo "</tr>";
    }
    
   public function dameDescripcion() {
       echo "<tr>";
       echo '<td>';

       $metaTags = $this->doc->getElementsByTagName('meta');
        
        foreach ($metaTags as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                echo "<div class='ok'></div>";
            }
        }


        echo '</td>';
                echo '<td>';
                echo "<h4>Descripcion del sitio</h4>";
                echo '</td>';
                echo '<td>';
               $metaTags = $this->doc->getElementsByTagName('meta');

                foreach ($metaTags as $meta) {
                    if ($meta->getAttribute('name') === 'description') {
                        echo $meta->getAttribute('content');
                        return;
                    }
                }
        echo '</td>';
       
       echo "<div class='ko'></div>";
              
    }
    
   public function damePalabras() {
    $stopwords = array(
        'a', 'acá', 'ahí', 'al', 'algo', 'algunas', 'algunos', 'allá', 'allí', 'ambos',
        'ante', 'antes', 'aquel', 'aquella', 'aquellas', 'aquello', 'aquellos', 'aquí', 'arriba', 'así',
        'atrás', 'aun', 'aunque', 'bajo', 'bastante', 'bien', 'casi', 'cada', 'cual', 'cuales', 'cualquier',
        'cualquiera', 'como', 'con', 'conmigo', 'contigo', 'contra', 'cosa', 'cosas', 'cuál', 'cuáles', 'cuándo',
        'cuanto', 'cuantos', 'cuánta', 'cuántas', 'de', 'del', 'demás', 'dentro', 'desde', 'donde', 'dos', 'el',
        'él', 'ella', 'ellas', 'ellos', 'en', 'encima', 'entonces', 'entre', 'era', 'erais', 'eran', 'eras',
        'eres', 'es', 'esa', 'esas', 'ese', 'eso', 'esos', 'esta', 'estaba', 'estabais', 'estaban', 'estabas',
        'estad', 'estada', 'estadas', 'estado', 'estados', 'estáis', 'estamos', 'están', 'estás', 'este', 'esto',
        'estos', 'estoy', 'etc', 'fue', 'fueron', 'fui', 'fuimos', 'ha', 'había', 'habíais', 'habíamos', 'habían',
        'habías', 'han', 'has', 'hasta', 'hay', 'haya', 'he', 'hemos', 'hicieron', 'hizo', 'hoy', 'hubo', 'la',
        'las', 'le', 'les', 'lo', 'los', 'más', 'me', 'mi', 'mis', 'mía', 'mías', 'mientras', 'mío', 'míos',
        'muy', 'nada', 'ni', 'no', 'nos', 'nosotras', 'nosotros', 'nuestra', 'nuestras', 'nuestro', 'nuestros',
        'o', 'os', 'otra', 'otras', 'otro', 'otros', 'para', 'pero', 'poco', 'por', 'porque', 'que', 'quien',
        'quienes', 'qué', 'se', 'sea', 'sean', 'ser', 'será', 'serán', 'si', 'sí', 'sido', 'siempre', 'siendo',
        'sin', 'sobre', 'sois', 'solamente', 'solo', 'somos', 'soy', 'su', 'sus', 'suya', 'suyas', 'suyo', 'suyos',
        'tal', 'también', 'tampoco', 'tan', 'tanta', 'tantas', 'tanto', 'tantos', 'te', 'tenéis', 'tenemos', 'tener',
        'tengo', 'ti', 'tiene', 'tienen', 'toda', 'todas', 'todo', 'todos', 'tu', 'tus', 'tuya', 'tuyas', 'tuyo',
        'tuyos', 'un', 'una', 'uno', 'unos', 'usted', 'ustedes', 'va', 'vais', 'valor', 'vamos', 'van', 'varias',
        'varios', 'vaya', 'verdad', 'vosotras', 'vosotros', 'voy', 'ya', 'yo'
    );

    $body = $this->doc->getElementsByTagName('body')->item(0);
    if (!$body) {
        die("Error: No se puede obtener el contenido del cuerpo del HTML");
    }

    // Quitar nodos <script> y <style> del body
    $tagsToRemove = ['script', 'style'];
    foreach ($tagsToRemove as $tag) {
        $nodes = $body->getElementsByTagName($tag);
        // Tenemos que recorrer al revés para evitar errores al eliminar
        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $node = $nodes->item($i);
            $node->parentNode->removeChild($node);
        }
    }

    $textContent = $body->textContent;
    $cleanText = preg_replace('/[^\p{L}\s]/u', '', $textContent);
    $words = preg_split('/\s+/', strtolower($cleanText));

    // Eliminar palabras vacías y stopwords
    $words = array_filter($words, function ($word) use ($stopwords) {
        return $word !== '' && !in_array($word, $stopwords);
    });

    $wordCount = array_count_values($words);
    arsort($wordCount);

    echo "<tr>";
    echo '<td>';
    echo !empty($wordCount) ? "<div class='ok'></div>" : "<div class='ko'></div>";
    echo '</td>';
    echo '<td>';
    echo "<h4>Palabras más frecuentes</h4>";
    echo '</td>';
    echo '<td>';

    foreach ($wordCount as $word => $count) {
        if($count > 2) 
        echo htmlspecialchars($word) . ": " . $count . "<br>";
    }

    echo '</td>';
    echo "</tr>";
}
    
      
   public function nubedePalabras() {
    $stopwords = array(
        'a', 'acá', 'ahí', 'al', 'algo', 'algunas', 'algunos', 'allá', 'allí', 'ambos',
        'ante', 'antes', 'aquel', 'aquella', 'aquellas', 'aquello', 'aquellos', 'aquí', 'arriba', 'así',
        'atrás', 'aun', 'aunque', 'bajo', 'bastante', 'bien', 'casi', 'cada', 'cual', 'cuales', 'cualquier',
        'cualquiera', 'como', 'con', 'conmigo', 'contigo', 'contra', 'cosa', 'cosas', 'cuál', 'cuáles', 'cuándo',
        'cuanto', 'cuantos', 'cuánta', 'cuántas', 'de', 'del', 'demás', 'dentro', 'desde', 'donde', 'dos', 'el',
        'él', 'ella', 'ellas', 'ellos', 'en', 'encima', 'entonces', 'entre', 'era', 'erais', 'eran', 'eras',
        'eres', 'es', 'esa', 'esas', 'ese', 'eso', 'esos', 'esta', 'estaba', 'estabais', 'estaban', 'estabas',
        'estad', 'estada', 'estadas', 'estado', 'estados', 'estáis', 'estamos', 'están', 'estás', 'este', 'esto',
        'estos', 'estoy', 'etc', 'fue', 'fueron', 'fui', 'fuimos', 'ha', 'había', 'habíais', 'habíamos', 'habían',
        'habías', 'han', 'has', 'hasta', 'hay', 'haya', 'he', 'hemos', 'hicieron', 'hizo', 'hoy', 'hubo', 'la',
        'las', 'le', 'les', 'lo', 'los', 'más', 'me', 'mi', 'mis', 'mía', 'mías', 'mientras', 'mío', 'míos',
        'muy', 'nada', 'ni', 'no', 'nos', 'nosotras', 'nosotros', 'nuestra', 'nuestras', 'nuestro', 'nuestros',
        'o', 'os', 'otra', 'otras', 'otro', 'otros', 'para', 'pero', 'poco', 'por', 'porque', 'que', 'quien',
        'quienes', 'qué', 'se', 'sea', 'sean', 'ser', 'será', 'serán', 'si', 'sí', 'sido', 'siempre', 'siendo',
        'sin', 'sobre', 'sois', 'solamente', 'solo', 'somos', 'soy', 'su', 'sus', 'suya', 'suyas', 'suyo', 'suyos',
        'tal', 'también', 'tampoco', 'tan', 'tanta', 'tantas', 'tanto', 'tantos', 'te', 'tenéis', 'tenemos', 'tener',
        'tengo', 'ti', 'tiene', 'tienen', 'toda', 'todas', 'todo', 'todos', 'tu', 'tus', 'tuya', 'tuyas', 'tuyo',
        'tuyos', 'un', 'una', 'uno', 'unos', 'usted', 'ustedes', 'va', 'vais', 'valor', 'vamos', 'van', 'varias',
        'varios', 'vaya', 'verdad', 'vosotras', 'vosotros', 'voy', 'ya', 'yo','y'
    );

    $body = $this->doc->getElementsByTagName('body')->item(0);
    if (!$body) {
        die("Error: No se puede obtener el contenido del cuerpo del HTML");
    }

    // Quitar nodos <script> y <style> del body
    $tagsToRemove = ['script', 'style'];
    foreach ($tagsToRemove as $tag) {
        $nodes = $body->getElementsByTagName($tag);
        // Tenemos que recorrer al revés para evitar errores al eliminar
        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $node = $nodes->item($i);
            $node->parentNode->removeChild($node);
        }
    }

    $textContent = $body->textContent;
    $cleanText = preg_replace('/[^\p{L}\s]/u', '', $textContent);
    $words = preg_split('/\s+/', strtolower($cleanText));

    // Eliminar palabras vacías y stopwords
    $words = array_filter($words, function ($word) use ($stopwords) {
        return $word !== '' && !in_array($word, $stopwords);
    });

    $wordCount = array_count_values($words);
    arsort($wordCount);

    echo "<tr>";
    echo '<td>';
    echo !empty($wordCount) ? "<div class='ok'></div>" : "<div class='ko'></div>";
    echo '</td>';
    echo '<td>';
    echo "<h4>Palabras más frecuentes</h4>";
    echo '</td>';
    echo '<td>';

    foreach ($wordCount as $word => $count) {
        if($count > 2) 
        echo "<span style='font-size:"  .  ($count*5)  . "px'>" .htmlspecialchars($word) . "</span>";
    }

    echo '</td>';
    echo "</tr>";
}



    public function dameEnlaces() {
        if (empty($this->contenido)) {
            echo "⚠️ No hay contenido cargado, no se pueden mostrar los enlaces.<br>";
            return;
        }

        $enlaces = $this->doc->getElementsByTagName('a');

        if ($enlaces->length > 0) {
           
            foreach ($enlaces as $valor) {
                $texto = trim($valor->textContent);
                $href = $valor->getAttribute("href");

                if (!empty($href)) {
                    echo ($texto ?: "[sin texto]") . " - $href<br>";
                }
            }
        } else {
            echo "❗ No se encontraron enlaces en la página.<br>";
        }
    }
}

?>