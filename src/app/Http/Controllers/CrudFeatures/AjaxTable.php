<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

use Illuminate\Support\Collection;

trait AjaxTable
{
    /**
     * Respond with the JSON of one or more rows, depending on the POST parameters.
     * @return JSON Array of cells in HTML form.
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        // create an array with the names of the searchable columns
        $columns = $this->searchableColumns()->toArray();

        // structure the response in a DataTable-friendly way
        $dataTable = new \LiveControl\EloquentDataTable\DataTable($this->crud->query, $columns);

        // make the datatable use the column types instead of just echoing the text
        $dataTable->setFormatRowFunction(function ($entry) {
            // get the actual HTML for each row's cell
            $row_items = $this->crud->getRowViews($entry, $this->crud);

            // add the buttons as the last column
            if ($this->crud->buttons->where('stack', 'line')->count()) {
                $row_items[] = \View::make('crud::inc.button_stack', ['stack' => 'line'])
                                ->with('crud', $this->crud)
                                ->with('entry', $entry)
                                ->render();
            }

            // add the details_row buttons as the first column
            if ($this->crud->details_row) {
                array_unshift($row_items, \View::make('crud::columns.details_row_button')
                                ->with('crud', $this->crud)
                                ->with('entry', $entry)
                                ->render());
            }

            return $row_items;
        });

        return $dataTable->make();
    }

    /**
     * Gets the searchable columns.
     *
     * @return Collection
     */
    protected function searchableColumns()
    {
        // create an array with the names of the searchable columns
        $columns = collect($this->crud->columns)
            // Excludes fields that are not searchable
            ->reject(function ($column, $key) {
                return array_get($column, 'searchable', true) === false;
            })
            // Excludes fields on a relation
            ->reject(function ($column, $key) {
                return array_key_exists('entity', $column);
            })
            // Excludes field types with no column
            ->reject(function ($column, $key) {
                return in_array(array_get($column, 'type'), ['select_multiple', 'model_function', 'model_function_attribute']);
            })
            ->pluck('name')
            // add the primary key, otherwise the buttons won't work
            ->merge($this->crud->model->getKeyName());

        return $columns;
    }
}
