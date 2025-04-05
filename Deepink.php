<?php

class DeepPink {
    private $contenido;
    private $doc;
    private $url;

    public function __construct($url) {
        $this->url = $url;
        $this->contenido = @file_get_contents($url); // con @ para evitar warnings

        if ($this->contenido === false) {
            echo "❌ No se pudo obtener contenido de: $url<br>";
        } else {
            $this->doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $this->doc->loadHTML($this->contenido);
            libxml_clear_errors();
        }
    }

    public function dameTitulo() {
        if (empty($this->contenido)) {
            echo "⚠️ No hay contenido cargado, no se puede mostrar el título.<br>";
            return;
        }

        $titles = $this->doc->getElementsByTagName('title');

        if ($titles->length > 0) {
        
            foreach ($titles as $valor) {
                echo $valor->textContent . "<br>";
            }
        } else {
            echo "❗ No se encontró ningún título en la página.<br>";
        }
    }
   public function dameDescripcion() {
    if (empty($this->contenido)) {
        echo "⚠️ No hay contenido cargado, no se puede mostrar la descripción.<br>";
        return;
    }

    $metaTags = $this->doc->getElementsByTagName('meta');
    $descripcionEncontrada = false;

    foreach ($metaTags as $meta) {
        $nameAttr = strtolower($meta->getAttribute('name'));
        if ($nameAttr === 'description') {
            $descripcion = $meta->getAttribute('content');
            echo "<br><strong>📝 Descripción:</strong><br>$descripcion<br>";
            $descripcionEncontrada = true;
            break;
        }
    }

    if (!$descripcionEncontrada) {
        echo "❗ No se encontró ninguna meta descripción en la página.<br>";
    }
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