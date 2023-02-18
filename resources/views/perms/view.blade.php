@if(isset($perm))
    <div class="container">
        <div class="row">
            <div class="col-sm-6" style="height: 400px; overflow-y: scroll">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <b>Short:</b> {{ $perm->short }}
                        </div>
                        <div class="col-sm-12">
                            <h5>File data</h5>
                            <textarea style="padding: 5px" class="data">{!! $perm->data !!}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        @if($perm->touched_by)
                            <div class="col-sm-12">
                                <h5>Touched by</h5>
                                <textarea style='padding: 5px; width: 100%'>{{ $perm->touched_by }}</textarea>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-6" style="height: 400px; overflow-y: scroll">
                <h5>Database data</h5>
                <table class='table table-striped'>
                    <thead>
                        <th>Key</th>
                        <th>Value</th>
                    </thead>
                    <tbody>
                        @foreach($perm->toArray() as $key=>$value)
                            @if($key == 'data' || $key == 'items')
                                @continue
                            @endif
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{!! $value !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row"><h5>Items</h5></div>
        @if($perm->items && $perm->items->count())
            <div class="row">
                <div class="col-sm-12">
                    <p>Found <b>{{ $perm->num_items }}</b> items in <b>{{ $perm->inventory_location }}</b> with total size of <b>{{ $perm->item_data_size }}</b> bytes
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4"><b>Short</b></div>
                <div class="col-sm-5"><b>Touchers</b></div>
                <div class="col-sm-3"></div>
            </div>

            @foreach($perm->items as $item)
                <div class="row item-row">
                    <div class="col-sm-4">{{ $item->short }}</div>
                    <div class="col-sm-5">{{ $item->touched_by }}</div>
                    <div class="col-sm-3"><button class="btn btn-info btn-sm" onclick='$("#obj_row_{{ $item->id }}").toggle();'+>View data</button></div>
                </div>
                @if($item->touched_by && 1==2)
                    <b>Touched By: </b> <span style='word-break: break-word'>{{ $item->touched_by }}</span>
                @endif
                <div class="row" style="display:none;background:#e5e5e5;padding:20px" id="obj_row_{{ $item->id }}">
                    <div class='col-sm-12'>
                        <h5>Item data</h5>
                        <textarea class='data'>{!! $item->data !!}</textarea>
                    </div>
                    <div class='col-sm-12'>
                        <h5>Database data</h5>
                        <table class='table table-striped'>
                            <thead>
                                <th>Key</th>
                                <th>Value</th>
                            </thead>
                            <tbody>
                                @foreach($item->toArray() as $key=>$value)
                                    @if($key == 'data' || $key == 'items')
                                        @continue
                                    @endif
                                    <tr>
                                        <td>{{ $key }}</td>
                                        <td>{!! $value !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
    @else
        No items found
    @endif
@else
   No data found
@endif
