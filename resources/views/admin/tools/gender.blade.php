<!-- 筛选性别按钮 --!>

<div class="btn-group" data-toggle="buttons">
    @foreach($options as $option => $label)
        <label class="btn btn-default btn-sm {{ \Request::get('gender', '-1') == $option ? 'active' : '' }}">
            <input type="radio" class="user-gender" value="{{ $option }}">{{$label}}
        </label>
    @endforeach
</div>