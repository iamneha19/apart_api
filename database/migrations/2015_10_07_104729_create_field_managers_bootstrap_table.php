<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Notes: Tinyint and Enum take same space around 1 bite but enum is more flexible and readability
 * we have use enum as many places as possible
 */
class CreateFieldManagersBootstrapTable extends Migration
{
    private $prefix = 'fm_';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createLoggerTable();
        $this->createFieldTable();

        // Table related to Entity
        $this->createEntityTable();
        $this->createEntityHirarchyTable();
        $this->createEntityFieldTable();
        $this->createEntityAttributeTable();
        $this->createEntityFieldAttributeTable();

        // Table related to Node
        $this->createNodeTable();
        $this->createNodeFieldTable();
        $this->createFieldTypeIntegerTable();
        $this->createFieldTypeTextTable();
        $this->createFieldTypeDateTable();
        $this->createFieldTypeDateTimeTable();
        $this->createFieldTypeTextareaTable();
        $this->createSelectedFieldTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'logger',
            'field',

            // Table related to Entity
            'entity',
            'entity_hirarchy',
            'entity_attribute',
            'entity_field_attribute',
            'entity_field',

            // Table related to Node
            'node',
            'node_field',
            'field_type_integer',
            'field_type_text',
            'field_type_date',
            'field_type_date_time',
            'field_type_hidden',
            'field_type_textarea',
            'selected_field'
        ];

        foreach ($tables as $table)
        {
            Schema::dropIfExists($this->prefix . $table);
        }
    }

    protected function createLoggerTable()
    {
        $this->createTable('logger', function(Blueprint $table)
        {
            $table->increments('id')->biginteger()->unsigned();

            $table->datetime('created_at');
            $table->integer('created_by')
                  ->foreign()
                  ->refrences($this->prefix . 'id')
                  ->on('users');

              $table->datetime('updated_at');
              $table->integer('updated_by')
                    ->foreign()
                    ->refrences($this->prefix . 'id')
                  ->on('users');

              $table->datetime('deleted_at');
              $table->integer('deleted_by')
                    ->foreign()
                    ->refrences($this->prefix . 'id')
                  ->on('users');

            $table->text('note')->nullable();
        });
    }

    protected function createEntityTable()
    {
        $this->createTable('entity', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
        });
    }

    protected function createFieldTable()
    {
        $this->createTable('field', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });
    }

    protected function createEntityFieldTable()
    {
        $this->createTable('entity_field', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('entity_id')
                  ->foreign()
                  ->refrences($this->prefix . 'id')
                  ->on('entity_type');
            $table->integer('field_id')
                  ->foreign()
                  ->refrences($this->prefix . 'id')
                  ->on('field');
            $table->enum('mandatory', ['NO', 'YES']);
            $table->json('attributes');
        });
    }

    protected function createEntityHirarchyTable()
    {
        $this->createTable('entity_hirarchy', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('entity_id')
                  ->foreign()
                  ->refrences($this->prefix . 'entity')
                  ->on('id');
              $table->integer('parent_entity_id')
                    ->foreign()
                    ->refrences($this->prefix . 'entity')
                    ->on('id');
        });
    }

    protected function createEntityAttributeTable()
    {
        $this->createTable('entity_attribute', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('value');
        });
    }

    protected function createEntityFieldAttributeTable()
    {
        $this->createTable('entity_field_attribute', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('entity_field_id')
                  ->foreign()
                  ->refrences($this->prefix . 'id')
                  ->on('entity_field');
            $table->integer('entity_attribute_id')
                  ->foreign()
                  ->refrences($this->prefix . 'id')
                  ->on('entity_field');
        });
    }

    protected function createNodeTable()
    {
        $this->createTable('node', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('entity_type_id');
            $table->enum('status', ['ACTIVE', 'DISABLE', 'DELETED']);

            $table->foreign('entity_type_id')
                  ->refrences('id')
                  ->on('entity_type');
        });
    }

    protected function createNodeFieldTable()
    {
        $this->createTable('node_field', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('node_id')->unsigned();
            $table->integer('field_id')->unsigned();
            $table->morphs('field_type');

            $table->foreign('node_id')
                  ->refrences('id')
                  ->on('node');

          $table->foreign('field_id')
                ->refrences('id')
                ->on('field');
        });
    }

    protected function createFieldTypeIntegerTable()
    {
        $this->createTable('field_type_integer', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('value')->unsigned();
        });
    }

    protected function createFieldTypeTextTable()
    {
        $this->createTable('field_type_text', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('value');
        });
    }

    protected function createFieldTypeDateTable()
    {
        $this->createTable('field_type_date', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date('value');
        });
    }

    protected function createFieldTypeDateTimeTable()
    {
        $this->createTable('field_type_date_time', function(Blueprint $table)
        {
            $table->increments('id');
            $table->datetime('value');
        });
    }

    protected function createFieldTypeTextareaTable()
    {
        $this->createTable('field_type_textarea', function(Blueprint $table)
        {
            $table->increments('id');
            $table->longText('value');
        });
    }

    protected function createSelectedFieldTable()
    {
        $this->createTable('selected_field', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('value');
        });
    }

    private function createTable($tableName, Closure $table)
    {
        if (! Schema::hasTable($this->prefix . $tableName)) {
            Schema::create($this->prefix . $tableName, $table);
        }
    }

}
