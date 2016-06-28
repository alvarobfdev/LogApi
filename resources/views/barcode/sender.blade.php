@extends('base')
@section('content')
    <form method="post" id="form">
        <input id="code" type="text" name="code" onchange="sendForm()">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>

@section('scripts')
    <script>
        document.getElementById("code").addEventListener('change', sendForm, false);
        document.getElementById("code").focus();

        function sendForm() {
            document.getElementById("form").submit();
        }
    </script>
@endsection
@endsection
