@extends('base')
@section('content')
    <form class="form-horizontal" method="post">
    <fieldset>

        <!-- Form Name -->
        <legend>{{$title}}</legend>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="code_number">{{$label}}</label>
            <div class="col-md-4">
                <input id="code_number" name="code_number"  type="text" placeholder="Inserte código" class="form-control input-md" required="">
            </div>
        </div>

        @if(isset($data))
            @if($data["success"] == true)
        <div class="form-group">
            <div class="col-md-4">

            </div>
            <div class="col-md-4">
                {!! $data["img"] !!}
            </div>
        </div>
            @endif
        @endif


        <!-- Button -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="comenzar"></label>
            <div class="col-md-4">
                <button id="comenzar" name="comenzar" class="btn btn-primary">Obtener código de barras</button>
            </div>
        </div>

        <input type="hidden" name="_token" value="{{ csrf_token() }}">


    </fieldset>
</form>
    @section('scripts')
    <script>
        @if(isset($data))
            @if($data["success"] == false)
                alert("{{$data["msgError"]}}");
            @endif
        @endif
    </script>
    @endsection
@endsection