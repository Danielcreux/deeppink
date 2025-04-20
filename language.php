<?php
  class Language {
    private static $translations = [];
    private static $currentLanguage = 'english';

    public static function init() {
        if (($handle = fopen("translations.csv", "r")) !== FALSE) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== FALSE) {
                $key = $data[0];
                foreach ($headers as $index => $language) {
                    if ($index > 0) {
                        self::$translations[$key][$language] = $data[$index];
                    }
                }
            }
            fclose($handle);
        }
        
        if (isset($_SESSION['language'])) {
            self::$currentLanguage = $_SESSION['language'];
        }
    }

    public static function setLanguage($language) {
        if (isset(self::$translations['app_title'][$language])) {
            $_SESSION['language'] = $language;
            self::$currentLanguage = $language;
        }
    }

    public static function get($key, $default = null) {
    $translation = self::$translations[$key][self::$currentLanguage] ?? null;
    
    if (!$translation) {
        error_log("Missing translation for key: $key (".self::$currentLanguage.")");
        return $default ?? $key;
    }
    
    return $translation;
}

    public static function getCurrentLanguage() {
        return self::$currentLanguage;
    }

    public static function getAllLanguages() {
        return array_keys(self::$translations['app_title'] ?? []);
    }
}
?>