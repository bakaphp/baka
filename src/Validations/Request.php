<?php
declare(strict_types=1);

namespace Baka\Validations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use function Baka\appPath;

class Request
{
    protected string $lang = 'en';
    protected Factory $factory;
    protected static ?Request $instance = null;

    /**
     * Constructor.
     */
    public function __construct(string $lang = 'en')
    {
        $translator = $this->setupTranslator();
        $this->lang = $lang;
        $this->factory = new Factory($translator);
    }

    /**
     * Singleton implementation.
     *
     * @return self
     */
    public static function getInstance(string $lang = 'en') : self
    {
        if (!self::$instance) {
            self::$instance = new self($lang);
        }

        return self::$instance;
    }

    /**
     * Setup the translation string.
     *
     * @return Translator
     */
    protected function setupTranslator() : Translator
    {
        $basePath = appPath('storage/lang');

        $loader = new FileLoader(new Filesystem(), $basePath);
        $translator = new Translator($loader, $this->lang);

        return $translator;
    }

    /**
     * Directly call the laravel functions.
     *
     * @param mixed $method
     * @param mixed $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(
            [
                $this->factory,
                $method
            ],
            $args
        );
    }
}
