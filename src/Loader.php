<?php

namespace Wilkques\EzLoader;

/**
 * @see [wilkques](https://github.com/wilkques/EzLoader)
 * 
 * create by: wilkques
 * 
 * support php >= 5.4
 */
class Loader
{
    /** @var array */
    protected static $classes = [];

    public static function autoRegister()
    {
        spl_autoload_register(array(get_called_class(), 'autoLoaded'));
    }

    /**
     * @param string $file
     */
    public static function autoLoaded($class)
    {
        $classes = static::getClasses();

        array_key_exists($class, $classes) && require $classes[$class];
    }

    /**
     * @return array
     */
    public static function getClasses()
    {
        empty(static::$classes) && static::getRequireMaps();

        return static::$classes;
    }

    /**
     * @return array
     */
    public static function getRequireMaps()
    {
        $classesPath = dirname(__FILE__) . "/class_maps.php";

        file_exists($classesPath) && static::$classes = require_once $classesPath;
    }

    /**
     * for php < 7
     * 
     * @param mixed $expression
     * 
     * @return array
     */
    public static function varexport($expression)
    {
        $export = var_export($expression, TRUE);

        $export = preg_replace("/^([ ]*)(.*)/m", "$1$1$2", $export);

        $array = preg_split("/\r\n|\n|\r/", $export);

        $array = preg_replace(["/\s=>\s$/", "/'/"], [" => [", "\""], $array);

        $export = join(PHP_EOL, $array);

        return $export;
    }

    /**
     * @param string $dir
     * 
     * @return array
     */
    public static function makeRequireMaps($dir = null)
    {
        !$dir && $dir = getcwd();

        static::requireMaps(static::getAllPHPPath($dir));

        $content = static::varexport(static::$classes);

        $fileContent = <<<EOF
<?php

\$serverRoot = getcwd();

return $content;

EOF;

        file_put_contents(__DIR__ . "/class_maps.php", $fileContent);
    }

    /**
     * requre php file array
     * 
     * @param array|string $paths
     * @param array $result
     * 
     * @return array
     */
    protected static function requireMaps($path, &$result = array())
    {
        $serverRoot = getcwd();

        array_map(function ($item) use (&$result, $serverRoot) {
            if (is_array($item)) {
                static::requireMaps($item, $result);

                return;
            }

            $documentDir = str_replace('/', '\/', $serverRoot);

            preg_match("/($documentDir)\/(?:vendor\/|)([\w\/]+)/i", $item, $matches);

            $path = preg_replace("/($documentDir)/i", '{$serverRoot}', $item);

            $class = str_replace('/src', '', $matches[2]);

            $class = ucfirst(str_replace('/', '\\', $class));

            static::$classes[$class] = $path;
        }, $path);
    }

    /**
     * find all php file
     * 
     * @param string $dir
     * 
     * @return array
     */
    protected static function getAllPHPPath($dir)
    {
        return array_filter(array_map(function ($path) use ($dir) {
            if (!in_array($path, array(".", ".."))) {
                $findPath = $dir . DIRECTORY_SEPARATOR;
                if (is_dir($findPath . $path)) {
                    return static::getAllPHPPath($findPath . $path);
                } else {
                    if (preg_match('/php/i', $path)) {
                        return $dir . DIRECTORY_SEPARATOR . $path;
                    }
                }
            }
        }, scandir($dir)));
    }
}
