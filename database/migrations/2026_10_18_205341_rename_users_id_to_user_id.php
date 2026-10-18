<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $foreignKeys = $this->foreignKeysReferencingUsers('id');

        $this->dropForeignKeys($foreignKeys);

        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('id', 'user_id');
        });

        $this->createForeignKeys($foreignKeys, 'user_id');
    }

    public function down(): void
    {
        $foreignKeys = $this->foreignKeysReferencingUsers('user_id');

        $this->dropForeignKeys($foreignKeys);

        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('user_id', 'id');
        });

        $this->createForeignKeys($foreignKeys, 'id');
    }

    /**
     * @return list<array{
     *     table: string,
     *     name: string|null,
     *     columns: list<string>,
     *     on_update: string|null,
     *     on_delete: string|null
     * }>
     */
    private function foreignKeysReferencingUsers(string $referencedColumn): array
    {
        $foreignKeys = [];
        $connection = Schema::getConnection();
        $schema = $connection->getDriverName() === 'sqlite'
            ? null
            : $connection->getDatabaseName();

        foreach (Schema::getTables($schema) as $table) {
            foreach (Schema::getForeignKeys($table['name']) as $foreignKey) {
                if ($foreignKey['foreign_table'] !== 'users' || $foreignKey['foreign_columns'] !== [$referencedColumn]) {
                    continue;
                }

                $foreignKeys[] = [
                    'table' => $table['name'],
                    'name' => $foreignKey['name'],
                    'columns' => $foreignKey['columns'],
                    'on_update' => $foreignKey['on_update'],
                    'on_delete' => $foreignKey['on_delete'],
                ];
            }
        }

        return $foreignKeys;
    }

    /**
     * @param  list<array{
     *     table: string,
     *     name: string|null,
     *     columns: list<string>,
     *     on_update: string|null,
     *     on_delete: string|null
     * }>  $foreignKeys
     */
    private function dropForeignKeys(array $foreignKeys): void
    {
        foreach ($foreignKeys as $foreignKey) {
            Schema::table($foreignKey['table'], function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }
    }

    /**
     * @param  list<array{
     *     table: string,
     *     name: string|null,
     *     columns: list<string>,
     *     on_update: string|null,
     *     on_delete: string|null
     * }>  $foreignKeys
     */
    private function createForeignKeys(array $foreignKeys, string $referencedColumn): void
    {
        foreach ($foreignKeys as $foreignKey) {
            Schema::table($foreignKey['table'], function (Blueprint $table) use ($foreignKey, $referencedColumn): void {
                $definition = $table->foreign($foreignKey['columns'], $foreignKey['name'])
                    ->references($referencedColumn)
                    ->on('users');

                if ($foreignKey['on_update'] !== null) {
                    $definition->onUpdate($foreignKey['on_update']);
                }

                if ($foreignKey['on_delete'] !== null) {
                    $definition->onDelete($foreignKey['on_delete']);
                }
            });
        }
    }
};
