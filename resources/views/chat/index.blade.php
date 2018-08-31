<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>简易聊天室</title>

    <link rel="stylesheet" href="{{ URL::asset('/') }}css/bootstrap.min.css">
    <script language="JavaScript" src="{{ URL::asset('/') }}js/jquery-3.3.1.min.js"></script>
    <script language="JavaScript" src="{{ URL::asset('/') }}js/bootstrap.min.js"></script>
    <script type="text/javascript">
        //var userToken = sessionStorage.getItem('Authorization');
        //if(typeof userToken == "undefined" || userToken == null || userToken == "") {
        //    window.location.href = "/chat/login";
        //}
    </script>
    <style>
        #chatbox {
            background-color: #F5F5F5;
            height: 300px;
            width: 100%;
            overflow-y:auto;
            padding: 5px;
        }
        #textbox {
            height: 100px;
        }
        .broadcast {
            font-size: 12px;
            margin: 5px;
            width: 160px;
            padding: 5px 10px;
            border: 0px;
            background-color: #DADADA;
        }
        .message {
            padding: 5px;
            margin: 5px 0px;
            background-color: #F6F6F6;
            white-space:normal;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container">
        <p class="text-center"><h1>简易聊天室DEMO</h1></p>

        <div class="container">
            <div class="row" id="chatbox"></div>
        </div>
        <hr>
        <form class="form" method="post" target="iframe">
            <div class="form-group">
                <input type="text" class="form-control" id="textbox" placeholder="请输入聊天内容...">
            </div>
            <button type="button" class="btn btn-default" onclick="sendMsg();" id="send_btn">发送</button>
        </form>
        <iframe id="frame" name="iframe" style="display:none;"></iframe>
    </div>
</body>
</html>
<script type="text/javascript">
    var ws;

    //  自动执行
    $(function(){
        link();
    });

    //  回车键触发 发送 按钮
    $(document).keyup(function(event){
        if(event.keyCode ==13){
            $("#send_btn").trigger("click");
        }
    });

    //  连接方法
    function link () {
        //this.ws = new WebSocket("ws://127.0.0.1:{{ config('laravels.listen_port') }}");
        this.ws = new WebSocket("ws://127.0.0.1:{{ config('laravels.listen_port') }}/ws");

        this.ws.onopen = function(event){
            //console.log(event);
        };

        this.ws.onmessage = function (event) {
            var data = $.parseJSON(event.data);
            if (data.uid === 0) {
                $("#chatbox").append('<pre class="broadcast">' + data.content + '</pre>');
            } else {
                $("#chatbox").append('<pre class="message">用户 ' + data.uid +': ' + data.content + '</pre>');
            }
        };

        this.ws.onclose = function(event){
            if (typeof(event.data) != "undefined") {
                var data = $.parseJSON(event.data);
                $("#chatbox").append('<pre class="broadcast">' + data.content + '</pre>');
            }
        };

        this.ws.onerror = function(event){
            alert("WebSocket异常！");
        };
    }

    //  发送消息
    function sendMsg() {

        var msg = $("#textbox").val();
        if (msg.length === 0) {
            alert('请输入些内容...');
            return;
        }
        this.ws.send(msg);
        $("#textbox").val('');
        $("#textbox").focus();
    }

</script>