<table class="table" style="margin-bottom: 0;">
    {{--<thead>--}}
    {{--<tr>--}}
        {{--@foreach($titles as $column => $title)--}}
            {{--<th>{{ $title }}</th>--}}
        {{--@endforeach--}}
    {{--</tr>--}}
    {{--</thead>--}}
    <tbody>
    @foreach($data as $k => $datum)
        <tr>
            @foreach($datum as $key => $value)
                @if($value == '[]')
                    <td>{{ $value }}</td>
                @elseif(strstr($value, '1]'))
                    <td class="info">{{ $value }}</td>
                @else
                    <td class="success">{{ $value }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>