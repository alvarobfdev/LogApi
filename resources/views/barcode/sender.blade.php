@extends('base')
@section('content')
    <form method="post" id="form" name="form">
        <input id="code" type="text" name="code" onchange="sendForm()">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>

@section('scripts')
    <script>
        document.getElementById("code").addEventListener('change', sendForm, false);
        setTimeout(function() { document.getElementById('code').focus(); }, 0);
        document.form.elements[0].focus();
        function sendForm() {
            document.getElementById("form").submit();
        }
    </script>
@endsection
@endsection
