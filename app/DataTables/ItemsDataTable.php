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
            $tmp = DB::table('items')->selectRaw("COUNT(*) as total, itm_obj")->distinct()->groupBy("itm_obj")->get();
            foreach($tmp as $value) {
                if ($value->itm_obj)
                    $objs[] = [
                        'value' => $value->itm_obj,
                        'label' => $value->itm_obj,
                        'total' => $value->total,
                        'count' => $value->total
                    ];
            }
            Cache::forever('obj-nums-searchpane', $objs);
        }
        return (new EloquentDataTable($query))
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
                'itm_obj',
                $objs,
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query
                        ->whereIn(
                            'itm_obj',
                            $values);
                }
            )
            ->orderColumn('itm_weight', function($query, $order) {
                $query->orderByRaw('CONVERT(itm_weight, SIGNED) '.$order);
            })
            ->addColumn('action', 'items.action')
            ->setRowId('id');
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
                    ->searchPanes(SearchPane::make()->layout('columns-2'))
                    ->dom('PBfrtip')
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                       // Button::make('pdf'), // needs https://github.com/barryvdh/laravel-snappy
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make('colvis')
                    ])
                    ->parameters([
                        'initComplete' => "function() {
                            if (typeof postInitFuncs == 'function')
                                postInitFuncs();
                        }"
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        $alwaysShow = [
            'id'=>[],
            'fullpath'=>['searchPanes'=>true],
            'short'=>[],
            'itm_id'=>[],
            'itm_adj'=>[],
            'itm_weight'=>[],
            'itm_obj'=>['searchPanes'=>true],
            'updated_at'=>[],
            'action'=>[]
        ];
        $allCols = \Schema::getColumnListing('items');
        $out = [];
        foreach($alwaysShow as $column=>$opts) {
            $col = Column::make($column);
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
