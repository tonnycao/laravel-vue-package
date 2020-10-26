<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        .level{display:flex;align-items:center;}
        .flex{flex:1;}
        table{
            margin:20px 0px;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td{
            border: 1px solid #ddd;
            padding:8px;
            vertical-align:top;
        }
    </style>
</head>
<body>
<div id="app">
    Hi  <br/><br/>

    @yield('content')

    <div style="margin:20px 0px;font-weight:bold">
        Best Regards<br/>
    </div>
</div>
</body>
</html>