<?php

namespace Baka\TestCase;

use Phalcon\Di;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;
use function Baka\appPath;

class Phinx
{
    /**
     * Setup Phinx.
     *
     * @return TextWrapper
     */
    protected static function getPhinx(string $configFile = 'phinx.php', string $parser = 'php') : TextWrapper
    {
        $phinxApp = new PhinxApplication();
        $phinxTextWrapper = new TextWrapper($phinxApp);

        $phinxTextWrapper->setOption('configuration', appPath($configFile));
        $phinxTextWrapper->setOption('parser', $parser);

        return $phinxTextWrapper;
    }

    /**
     * Run migration.
     *
     * @return string
     */
    public static function migrate() : string
    {
        return self::getPhinx()->getMigrate();
    }

    /**
     * Run seed.
     *
     * @return void
     */
    public static function seed()
    {
        return self::getPhinx()->getSeed();
    }

    /**
     * Drop all tables.
     *
     * CAUTION ONLY RUN ON BAKA TEST
     *
     * @return bool
     */
    public static function dropTables($dbProvider = 'db') : bool
    {
        if (Di::getDefault()->has($dbProvider)) {
            $db = Di::getDefault()->get($dbProvider);
            $query = $db->query('SHOW TABLES');
            $tables = $query->fetchAll();

            foreach ($tables as $table) {
                $query = $db->query('DROP TABLE IF EXISTS ' . $table[0]);
            }
        }

        return false;
    }
}
