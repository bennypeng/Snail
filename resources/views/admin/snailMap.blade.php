<style>
    #snailTb td {
        width: 20px;
        text-align: center;
        font-weight: bold;
    }
</style>

<table class="table table-bordered" style="margin-bottom: 0;" id="snailTb">
    <tbody>
    @foreach($data as $k => $datum)
        <tr>
            @foreach($datum as $key => $value)
                @if($value == '[]')
                    <td class="bg-info">[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</td>
                @elseif(strstr($value, '1]'))
                    <td class="bg-primary">{{ $value }}</td>
                @else
                    <td class="bg-success">{{ $value }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>