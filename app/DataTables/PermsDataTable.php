<?php

namespace App\DataTables;

use App\Models\Perm;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\SearchPane;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class PermsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        if (!Perm::count())
            return (new EloquentDataTable($query));

        $types = Cache::get('perm-perm_type-nums-searchpane');
        if (!$types) {
            $tmp = DB::table('perms')->selectRaw("COUNT(*) as total, perm_type")->distinct()->groupBy("perm_type")->get();
            foreach($tmp as $value) {
                if ($value->perm_type)
                    $types[] = [
                        'value' => $value->perm_type,
                        'label' => $value->perm_type,
                        'total' => $value->total,
                        'count' => $value->total
                    ];
            }
            Cache::forever('perm-perm_type-nums-searchpane', $types);
        }
        $objects = Cache::get('perm-object-nums-searchpane');
        if (!$objects) {
            $tmp = DB::table('perms')->selectRaw("COUNT(*) as total, object")->distinct()->groupBy("object")->get();
            foreach($tmp as $value) {
                if ($value->object)
                    $objects[] = [
                        'value' => $value->object,
                        'label' => $value->object,
                        'total' => $value->total,
                        'count' => $value->total
                    ];
            }
            Cache::forever('perm-object-nums-searchpane', $types);
        }

        $tableOut = (new EloquentDataTable($query))
            ->searchPane(
                'perm_type',
                $types,
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query
                        ->where(
                            'perm_type',
                            'like',
                            $values[0]."%");
                }
            )
            ->searchPane(
                'object',
                $objects,
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query
                        ->where(
                            'object',
                            'like',
                            "%".$values[0]."%");
                }
            )

            ->addColumn('action', 'perms.action')
            ->setRowId('db_id');

        return $tableOut;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Perm $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Perm $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('perms-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->searchPanes(
                        SearchPane::make()
                            ->layout('columns-2')
                            ->dtOpts(['order'=>[1,'desc']])
                            //->controls(false)
                    )
                    ->dom('PBfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('postCsvVisibleColumns'),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make('colvis')
                    ])
                    ->parameters([
                        'initComplete' => "function() {
                            if (typeof postInitFuncs == 'function')
                                postInitFuncs();
                        }",

                    ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        $out = [];
        if ($this->request && in_array($this->request->get('action'), ['excel', 'csv'])) {
            if ($this->request->get('visible_columns')) {
                $out = [];
                foreach($this->request->get('visible_columns') as $col) {
                    if ($col != "action") // Never want to export this
                        $out[] = Column::make($col);
                }
                return $out;
            }
        }
        $alwaysShow = [
            'filename'=>[],
            'location'=>[],
            'object'=>['searchPanes'=>true],
            'perm_type'=>['searchPanes'=>true],
            'x'=>[],
            'y'=>[],
            'z'=>[],
            'action'=>[]
        ];

        $allCols = \Schema::getColumnListing('perms');
        foreach($alwaysShow as $column=>$opts) {
            $col = Column::make($column);
            if (isset($opts["hidden"]) && $opts["hidden"] == true)
                $col->hidden();
            if (isset($opts['searchPanes']) && $opts['searchPanes'] == true)
                $col->searchPanes(true);
            $out[] = $col;
        }
        foreach($allCols as $c) {
            if (in_array($c,array_keys($alwaysShow))===false)
                $out[] = Column::make($c)->hidden();
        }
        return $out;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Perms_' . date('YmdHis');
    }
}
