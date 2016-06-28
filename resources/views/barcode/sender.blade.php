<html>
    <head>
        <script>



        </script>
    </head>
    <body onload="window.scrollTo(0, 0); document.form1.elements[0].focus(); return false;">
    <form method="post" id="form1" name="form1">
        <input id="code" type="text" name="code" onchange="sendForm()">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>
    </body>
</html>

