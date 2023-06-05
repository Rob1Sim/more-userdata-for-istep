<?php

namespace MUDF_ISTEP\Tests\Acceptation;

use Dotenv\Dotenv;
use PDO;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();
class DatabaseTest extends TestCase
{
    protected string $dbHost;
    protected string $dbName;
    protected string $dbUser;
    protected string $dbPassword;

    protected function setUp(): void
    {
        $this->dbHost = $_ENV['DB_HOST'];
        $this->dbName = $_ENV['MYSQL_DATABASE'];
        $this->dbUser = $_ENV['MYSQL_USER'];
        $this->dbPassword = $_ENV['MYSQL_PASSWORD'];
    }


    /**
     * @return void
     */
    public function testTableExist()
    {
        //TODO: Essayer d'éxécuter des test sur la base de donnée
    }
}
