@if(isset($perm))
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h5>File data</h5>
                <textarea class="data">{!! $perm->data !!}</textarea>
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
            <div class="col-sm-6"><b>Touchers</b></div>
        </div>

        @foreach($perm->items as $item)
            <div class="row item-row">
                <div class="col-sm-4">{{ $item->short }}</div>
                <div class="col-sm-6">{{ $item->touched_by }}</div>
            </div>
            @if($item->touched_by && 1==2)
                <b>Touched By: </b> <span style='word-break: break-word'>{{ $item->touched_by }}</span>
            @endif
            <div class="row" style="display:none">
                <textarea class='data'>{!! $item->data !!}</textarea>
            </div>
        @endforeach
    @else
        No items found
    @endif
@else
   No data found
@endif
