<?php

namespace Kamal\DummyDataGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;

class GenerateDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:dummydata {table} {count=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dummy data based on the table structure defined in migration files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');
        $count = (int) $this->argument('count');

        // Check if the table exists in the schema
        if (!Schema::hasTable($table)) {
            $this->error("Table '$table' does not exist.");
            return;
        }

        $faker = Faker::create();
        $columns = Schema::getColumnListing($table);
        $foreignKeys = $this->getForeignKeys($table); // Get foreign key constraints
        $data = [];

        for ($i = 0; $i < $count; $i++) {
            $row = [];

            // dd($columns);

            foreach ($columns as $column) {
                if (in_array($column, ['id', 'created_at', 'updated_at'])) {
                    continue; // Skip auto-increment or timestamp columns
                }

                if (array_key_exists($column, $foreignKeys)) {
                    // Handle foreign key by getting a random ID from the related table
                    $relatedTable = $foreignKeys[$column]['related_table'];
                    $relatedColumn = $foreignKeys[$column]['related_column'];

                    // Fetch a random valid ID from the related table
                    $relatedId = DB::table($relatedTable)->inRandomOrder()->value($relatedColumn);
                    $row[$column] = $relatedId ?? null; // Set null if no related data exists
                } else {
                    // Generate dummy data based on column type
                    $type = Schema::getColumnType($table, $column);

                    switch ($type) {
                        case 'varchar':
                        case 'string':
                            if ($column === 'email') {
                                $row[$column] = $faker->email;
                            } else {
                                $row[$column] = $faker->word;
                            }
                            break;
                        case 'text':
                            $row[$column] = $faker->sentence;
                            break;
                        case 'int':
                        case 'bigint':
                        case 'smallint':
                            $row[$column] = $faker->numberBetween(1, 100);
                            break;
                        case 'boolean':
                            $row[$column] = $faker->boolean;
                            break;
                        case 'date':
                            $row[$column] = $faker->date;
                            break;
                        case 'datetime':
                        case 'timestamp':
                            $row[$column] = $faker->dateTime;
                            break;
                        case 'float':
                        case 'double':
                        case 'decimal':
                            $row[$column] = $faker->randomFloat(2, 1, 100);
                            break;
                        case 'enum':
                            $row[$column] = $this->getRandomEnumValue($table, $column);
                            break;
                        default:
                            $row[$column] = null; // Default fallback
                    }
                }
            }

            $data[] = $row;
        }

        // Insert generated data into the table
        DB::table($table)->insert($data);
        $this->info("$count records inserted into the '$table' table.");
    }

    /**
     * Get foreign key constraints for a table
     *
     * @param string $table
     * @return array
     */
    protected function getForeignKeys($table)
    {
        $foreignKeys = [];

        // Query the information_schema for foreign key constraints
        $foreignKeyInfo = DB::select("
            SELECT
                kcu.COLUMN_NAME AS column_name,
                kcu.REFERENCED_TABLE_NAME AS related_table,
                kcu.REFERENCED_COLUMN_NAME AS related_column
            FROM
                information_schema.KEY_COLUMN_USAGE kcu
            JOIN
                information_schema.TABLE_CONSTRAINTS tc
            ON
                kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
            WHERE
                kcu.TABLE_NAME = ?
                AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'", [$table]);

        foreach ($foreignKeyInfo as $key) {
            // Use the correct property names as returned by the query
            $foreignKeys[$key->column_name] = [
                'related_table' => $key->related_table,
                'related_column' => $key->related_column,
            ];
        }

        return $foreignKeys;
    }

    protected function getRandomEnumValue($table, $column)
    {
        // Execute a direct query without DB::raw()
        $result = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'");

        // Ensure we got a valid result
        if (count($result) > 0 && isset($result[0]->Type)) {
            $type = $result[0]->Type;

            // Use a regular expression to extract the enum options
            preg_match('/^enum\((.*)\)$/', $type, $matches);

            if (!empty($matches[1])) {
                // Extract the enum values, which are single-quoted and comma-separated
                $enumValues = array_map(function ($value) {
                    return trim($value, "'");
                }, explode(',', $matches[1]));

                // Return a random value from the enum options
                return $enumValues[array_rand($enumValues)];
            }
        }

        return null; // Default to null if no values are found
    }
}
