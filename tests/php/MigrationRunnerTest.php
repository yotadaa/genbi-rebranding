<?php

declare(strict_types=1);

use App\Core\MigrationRunner;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

final class FakeStatement
{
    /** @param array<int, mixed> $rows */
    public function __construct(private array $rows, private ?FakePdo $db = null)
    {
    }

    /** @return array<int, mixed>|mixed */
    public function fetchAll(int $mode = 0): array
    {
        return $this->rows;
    }

    public function fetchColumn(): mixed
    {
        return $this->rows[0] ?? false;
    }

    /** @param array<string, mixed> $params */
    public function execute(array $params = []): bool
    {
        if (isset($params['migration'], $params['batch']) && $this->db instanceof FakePdo) {
            $this->db->executed[] = (string) $params['migration'];
            $this->db->batch = (int) $params['batch'];
        }

        return true;
    }
}

final class FakePdo extends PDO
{
    /** @var array<int, string> */
    public array $executed = [];
    /** @var array<int, string> */
    public array $sql = [];
    public int $batch = 0;
    private bool $transaction = false;

    public function __construct()
    {
    }

    public function exec(string $statement): int|false
    {
        $this->sql[] = $statement;

        return 0;
    }

    public function prepare(string $query, array $options = []): mixed
    {
        if (str_contains($query, 'SELECT migration')) {
            return new FakeStatement($this->executed);
        }

        if (str_contains($query, 'SELECT MAX(batch)')) {
            return new FakeStatement([$this->batch]);
        }

        return new FakeStatement([], $this);
    }

    public function beginTransaction(): bool
    {
        $this->transaction = true;

        return true;
    }

    public function commit(): bool
    {
        $this->transaction = false;

        return true;
    }

    public function rollBack(): bool
    {
        $this->transaction = false;

        return true;
    }

    public function inTransaction(): bool
    {
        return $this->transaction;
    }
}

$tmp = sys_get_temp_dir() . '/genbi-migrations-' . bin2hex(random_bytes(4));
mkdir($tmp);
file_put_contents($tmp . '/2026_05_06_000001_first.php', <<<'PHP'
<?php
return ['up' => static function (PDO $db): void { $db->exec('CREATE TABLE first_table (id INT)'); }];
PHP);
file_put_contents($tmp . '/2026_05_06_000002_second.php', <<<'PHP'
<?php
return ['up' => static function (PDO $db): void { $db->exec('CREATE TABLE second_table (id INT)'); }];
PHP);

$db = new FakePdo();
$runner = new MigrationRunner($db, $tmp);

$pending = array_map('basename', $runner->pending());
assert($pending === ['2026_05_06_000001_first.php', '2026_05_06_000002_second.php']);

$applied = $runner->run();
assert($applied === ['2026_05_06_000001_first.php', '2026_05_06_000002_second.php']);
assert($db->executed === $applied);
assert($runner->pending() === []);

array_map('unlink', glob($tmp . '/*.php') ?: []);
rmdir($tmp);

echo "PHP migration runner tests passed\n";
