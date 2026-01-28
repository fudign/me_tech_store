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

        // Disable server-side prepared statements for pgbouncer/pooler compatibility
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        // Execute DEALLOCATE ALL to clear any cached plans (for transaction pooling mode)
        try {
            $connection->exec('DEALLOCATE ALL');
        } catch (\PDOException $e) {
            // Ignore errors - DEALLOCATE ALL might not be allowed in session mode
        }

        return $connection;
    }
}
