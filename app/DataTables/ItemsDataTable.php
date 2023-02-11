<?php

namespace App\DataTables;

use App\Models\Item;
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

class ItemsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        if (!Item::count())
            return (new EloquentDataTable($query));
        $paths = Cache::get('path-nums-searchpane');
        if (!$paths) {
            $tmp = DB::table('items')->selectRaw("COUNT(*) as total, SUBSTRING_INDEX(path,'/',4) as partpath")->distinct()->groupBy("partpath")->get();
            foreach($tmp as $value) {
                if ($value->partpath)
                    $paths[] = [
                        'value' => $value->partpath,
                        'label' => $value->partpath."/..",
                        'total' => $value->total,
                        'count' => $value->total
                    ];
            }
            Cache::forever('path-nums-searchpane', $paths);
        }

        $objs = Cache::get('obj-nums-searchpane');
        if (!$objs) {
            $tmp = DB::table('items')->selectRaw("COUNT(*) as total, obj")->distinct()->groupBy("obj")->get();
            foreach($tmp as $value) {
                if ($value->obj)
                    $objs[] = [
                        'value' => $value->obj,
                        'label' => $value->obj,
                        'total' => $value->total,
                        'count' => $value->total
                    ];
            }
            Cache::forever('obj-nums-searchpane', $objs);
        }
        $toIntCols = Item::castToIntCols();

        $tableOut = (new EloquentDataTable($query))
            ->searchPane(
                'fullpath',
                $paths,
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query
                        ->where(
                            'fullpath',
                            'like',
                            $values[0]."%");
                }
            )
            ->searchPane(
                'obj',
                $objs,
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query
                        ->whereIn(
                            'obj',
                            $values);
                }
            )
            ->addColumn('action', 'items.action')
            ->setRowId('db_id');

        foreach($toIntCols as $col) {
            $tableOut->orderColumn($col, function($query, $order) use($col) {
                $query->orderByRaw('CONVERT('.$col.', SIGNED) '.$order);
            });
        }
        return $tableOut;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Item $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Item $model): QueryBuilder
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
                    ->setTableId('items-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->searchPanes(
                        SearchPane::make()
                            ->layout('columns-2')
                            ->dtOpts(['order'=>[1,'desc']])
                            //->controls(false)
                    )
                    ->dom('PBfrtip')
                    //->dom('Bfrtip')
                    ->orderBy(1,'asc')
                    ->selectStyleSingle()
                    ->buttons([
                       // Button::make('postExcelVisibleColumns'),
                        Button::make('postCsvVisibleColumns'),
                       // Button::make('pdf'), // needs https://github.com/barryvdh/laravel-snappy
                        // Button::make('print'), // prints all columns (bad)
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
                    if ($col != "action" && $col != "db_id") // Never want to export this
                        $out[] = Column::make($col);
                }
                return $out;
            }
        }
        $alwaysShow = [
            'db_id'=>['hidden'=>true],
            'fullpath'=>['searchPanes'=>true],
            'short'=>[],
            'id'=>[],
            'adj'=>[],
            'weight'=>[],
            'obj'=>['searchPanes'=>true],
          //  'updated_at'=>[],
            'action'=>[]
        ];

        $allCols = \Schema::getColumnListing('items');
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
        return 'Items_' . date('YmdHis');
    }
}
