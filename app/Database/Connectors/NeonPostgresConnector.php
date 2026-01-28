<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;
use PDO;

class NeonPostgresConnector extends PostgresConnector
{
    /**
     * Create a DSN string from a configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        // Add Neon endpoint option if specified
        if (isset($config['endpoint'])) {
            $dsn .= ";options='endpoint=".$config['endpoint']."'";
        }

        return $dsn;
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = parent::getOptions($config);

        // Disable prepared statements for connection pooler compatibility
        $options[PDO::ATTR_EMULATE_PREPARES] = true;
        $options[PDO::ATTR_PERSISTENT] = false;

        // Force statement class to prevent caching
        $options[PDO::ATTR_STATEMENT_CLASS] = [\PDOStatement::class];

        return $options;
    }

    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  array   $config
     * @param  array   $options
     * @return \PDO
     */
    public function createConnection($dsn, array $config, array $options)
    {
        $connection = parent::createConnection($dsn, $config, $options);

        // Aggressively disable server-side prepared statements
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Clear any existing prepared statements and temp data
        try {
            // DISCARD ALL is more aggressive than DEALLOCATE ALL
            // It clears prepared statements, temp tables, session settings, etc.
            $connection->exec('DISCARD ALL');
        } catch (\PDOException $e) {
            // If DISCARD ALL fails, try DEALLOCATE ALL
            try {
                $connection->exec('DEALLOCATE ALL');
            } catch (\PDOException $e2) {
                // Ignore errors - might not be supported in transaction pooling mode
            }
        }

        return $connection;
    }
}
