@if(isset($perm))
    <h4>File: /perms/perm_objs/{{ $perm->filename }}</h4>
    <textarea class='data'>{!! $perm->data !!}</textarea>
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
    @if($perm->items && $perm->items->count())
        @foreach($perm->items as $item)
            <h5>Object: {{ $item->object }}</h5>
            <b>Path: </b> {{ $item->pathname }}<br/>
            <b>Short: </b> {{ $item->short }}</br>
            @if($item->touched_by)
                <b>Touched By: </b> <span style='word-break: break-word'>{{ $item->touched_by }}</span>
            @endif
            <textarea class='data'>{!! $item->data !!}</textarea>
        @endforeach
    @else
        No items found
    @endif
@else
   No data found
@endif
