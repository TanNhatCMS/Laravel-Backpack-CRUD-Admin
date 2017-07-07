<?php

namespace Backpack\CRUD\PanelTraits;

trait AutoSet
{
    // ------------------------------------------------------
    // AUTO-SET-FIELDS-AND-COLUMNS FUNCTIONALITY
    // ------------------------------------------------------

    /**
     * For a simple CRUD Panel, there should be no need to add/define the fields.
     * The public columns in the database will be converted to be fields.
     */
    public function setFromDb()
    {
        $this->setDoctrineTypesMapping();
        $this->getDbColumnTypes();

        $crudTypes = null;
        $crudFakeFields = null;

        if(!is_null($this->model->crudTypes)) {
            $crudTypes = $this->model->crudTypes;
        }

        if(!is_null($this->model->crudFakeFields)) {
            $crudFakeFields = $this->model->crudFakeFields;
        }

        //dd($crudTypes);

        array_map(function ($field) use ($crudTypes) {
            // $this->labels[$field] = $this->makeLabel($field);

            $new_field = [
                'name'       => $field,
                'label'      => ucfirst($field),
                'value'      => null,
                'type'       => $this->getFieldTypeFromDbColumnType($field),
                'values'     => [],
                'attributes' => [],
            ];

            if(!is_null($crudTypes) && isset($crudTypes[$field])) {
                if(is_array($crudTypes[$field])) {
                    foreach($crudTypes[$field] as $key => $value) {
                        $new_field[$key] = $value;
                    }
                } else {
                    $new_field['type'] = $crudTypes[$field];
                }
            }

            if($new_field['type'] == 'check') { $new_field['type'] = 'checkbox'; }

            $this->create_fields[$field] = $new_field;
            $this->update_fields[$field] = $new_field;

            if (! in_array($field, $this->model->getHidden())) {
                $this->columns[$field] = [
                    'name'  => $field,
                    'label' => ucfirst($field),
                    'type'  => $this->getFieldTypeFromDbColumnType($field),
                ];

                if(!is_null($crudTypes) && isset($crudTypes[$field])) {
                    if(is_array($crudTypes[$field])) {
                        foreach($crudTypes[$field] as $key => $value) {
                            $this->columns[$field][$key] = $value;
                        }
                    } else {
                        $this->columns[$field]['type'] = $crudTypes[$field];
                    }
                }
            }

        }, $this->getDbColumnsNames());

        if(!is_null($crudFakeFields)) {
            foreach($crudFakeFields as $key => $data) {
                $data['name'] = $key;
                $this->create_fields[$key] = $data;
                $this->update_fields[$key] = $data;

                $this->columns[$key] = $data;
            }

        }

    }

    /**
     * Get all columns from the database for that table.
     *
     * @return [array]
     */
    public function getDbColumnTypes()
    {
        $table_columns = $this->model->getConnection()->getSchemaBuilder()->getColumnListing($this->model->getTable());

        foreach ($table_columns as $key => $column) {
            $column_type = $this->model->getConnection()->getSchemaBuilder()->getColumnType($this->model->getTable(), $column);
            $this->db_column_types[$column]['type'] = trim(preg_replace('/\(\d+\)(.*)/i', '', $column_type));
            $this->db_column_types[$column]['default'] = ''; // no way to do this using DBAL?!
        }

        return $this->db_column_types;
    }

    /**
     * Intuit a field type, judging from the database column type.
     *
     * @param  [string] Field name.
     *
     * @return [string] Fielt type.
     */
    public function getFieldTypeFromDbColumnType($field)
    {
        if (! array_key_exists($field, $this->db_column_types)) {
            return 'text';
        }

        if ($field == 'password') {
            return 'password';
        }

        if ($field == 'email') {
            return 'email';
        }

        switch ($this->db_column_types[$field]['type']) {
            case 'int':
            case 'smallint':
            case 'mediumint':
            case 'longint':
                return 'number';
            break;

            case 'string':
            case 'varchar':
            case 'set':
                return 'text';
            break;

            // case 'enum':
            //     return 'enum';
            // break;

            case 'tinyint':
                return 'active';
            break;

            case 'text':
                return 'textarea';
            break;

            case 'mediumtext':
            case 'longtext':
                return 'textarea';
            break;

            case 'date':
                return 'date';
            break;

            case 'datetime':
            case 'timestamp':
                return 'datetime';
            break;
            case 'time':
                return 'time';
            break;

            default:
                return 'text';
            break;
        }
    }

    // Fix for DBAL not supporting enum
    public function setDoctrineTypesMapping()
    {
        $types = ['enum' => 'string'];
        $platform = \DB::getDoctrineConnection()->getDatabasePlatform();
        foreach ($types as $type_key => $type_value) {
            if (!$platform->hasDoctrineTypeMappingFor($type_key)) {
                $platform->registerDoctrineTypeMapping($type_key, $type_value);
            }
        }
    }

    /**
     * Turn a database column name or PHP variable into a pretty label to be shown to the user.
     *
     * @param  [string]
     *
     * @return [string]
     */
    public function makeLabel($value)
    {
        return trim(preg_replace('/(id|at|\[\])$/i', '', ucfirst(str_replace('_', ' ', $value))));
    }

    /**
     * Get the database column names, in order to figure out what fields/columns to show in the auto-fields-and-columns functionality.
     *
     * @return [array] Database column names as an array.
     */
    public function getDbColumnsNames()
    {
        // Automatically-set columns should be both in the database, and in the $fillable variable on the Eloquent Model
        $columns = $this->model->getConnection()->getSchemaBuilder()->getColumnListing($this->model->getTable());
        $fillable = $this->model->getFillable();

        if (! empty($fillable)) {
            $columns = array_intersect($columns, $fillable);
        }

        // but not updated_at, deleted_at
        return array_values(array_diff($columns, [$this->model->getKeyName(), $this->model->getCreatedAtColumn(), $this->model->getUpdatedAtColumn(), 'deleted_at']));
    }
}
