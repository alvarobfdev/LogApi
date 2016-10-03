@extends('base')
@section('content')
    <div class="web-app">
        {{--<div class="row cabecera">
            <div class="col-md-12">
                <div class="col-md-1">
                    <img src="{{asset("/logival/img/logo-transparente.png")}}" style="width: 100px;">
                </div>
            </div>
        </div>--}}
        <nav class="navbar navbar-default menu-app">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li><a href="pedidos/ultimosPedidos">Pedidos <span class="sr-only">(current)</span></a></li>
                        <li><a href="#">Albaranes</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Almacén <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">Separated link</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">One more separated link</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configuración <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">Separated link</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">One more separated link</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Estadísticas <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">Separated link</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">One more separated link</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        <div class="row shortkeys" style="margin: 0">
            <div class="col-sm-12" id="keysInfo">
                <span>(B) Buscar por cliente</span>
                <span>(F2) Listar pedidos</span>
                <span>(F3) Encontrar ubicaciones</span>
            </div>
        </div>
        <div id="central-content" class="central-content">
            SELECCIONE UNA OPCIÓN DEL MENÚ
        </div>
    </div>

@endsection
@section('scripts')
    <script>

        var WEBAPP = WEBAPP || {};

        WEBAPP.common = {
            pagesStack: new stack(),
            shortkeys: true,
            cachedData: [],
            loadedTemplates: {},
            keyLogs: {},
            currentSection:null,
            currentPage:null,
            currentArgs:{},
            currentPageData:{},
            tempData:{},

            advice:{
                ERROR:'error',
                send:function(type, msg) {

                    var msgOptions = {
                        text: msg,
                        type: type,
                        timeout: 5000,
                        animation: {
                            open: {height: 'toggle'}, // jQuery animate function property object
                            close: {height: 'toggle'}, // jQuery animate function property object
                            easing: 'swing', // easing
                            speed: 500 // opening & closing animation speed
                        }
                    };



                    var n = noty(msgOptions);
                }
            },

            hasTempData:function(name) {
                return this.tempData.hasOwnProperty(name) && this.tempData[name] != null;
            },

            getTempData:function(name) {
                return this.tempData[name];
            },

            addTempData:function(name, value) {
                this.tempData[name] = value;
            },

            deleteTempData:function(name) {
                this.tempData[name] = null;
            },

            deleteAllTempData:function() {
                setTimeout(function() {
                    console.log("DELETING TEMP DATA");
                    WEBAPP.common.tempData = {};
                    WEBAPP.common.deleteAllTempData();
                },60000);
            },
            pushPage:function(seccion, page, args, pageData, template) {
                var data = {};
                if(seccion != null) {
                    data['seccion'] = seccion;
                    data['page'] = page;
                    data['args'] = args;
                    var copiedObject = jQuery.extend({}, pageData)
                    data['pageData'] = copiedObject;
                    data['template'] = template;
                    this.pagesStack.push(data);
                }
            },

            isTemplateCached:function(seccion, page) {
                if(!this.loadedTemplates.hasOwnProperty(seccion) ||
                        !this.loadedTemplates[seccion].hasOwnProperty(page)) {
                    return false;
                }

                return true;
            },
            prepareCachedTemplate:function(seccion, page) {
                if(!this.loadedTemplates.hasOwnProperty(seccion)) {
                    this.loadedTemplates[seccion] = {};
                }
                if(!this.loadedTemplates[seccion].hasOwnProperty(seccion)) {
                    this.loadedTemplates[seccion][page] = {};

                }
            },
            callbackLoadedTemplate:function(funcion, seccion, page, args) {
                this.currentSection = seccion;
                this.currentPage = page;
                this.currentArgs = args;
                this.currentPageData = {};
                funcion(this.loadedTemplates[seccion][page]);
                this.templateLoaded(args);
            },
            loadTemplate: function (seccion, page, funcion, args, noStack) {
                var thisCommon = this;
                if(typeof noStack === 'undefined') {
                    thisCommon.savePage();
                }
                thisCommon.resetKeyLog();
                thisCommon.unloadTemplate();
                if (!thisCommon.isTemplateCached(seccion, page)) {
                    thisCommon.prepareCachedTemplate(seccion, page);
                    $.get('{{url('web-app/template')}}', {seccion: seccion, page: page}, function (data) {
                        thisCommon.loadedTemplates[seccion][page] = data;
                        thisCommon.callbackLoadedTemplate(funcion, seccion, page, args);
                    });
                }
                else {
                    thisCommon.callbackLoadedTemplate(funcion, seccion, page, args);
                }



            },

            savePage:function() {
                this.pushPage(this.currentSection, this.currentPage, this.currentArgs, this.currentPageData ,$('#central-content').html())
            },
            handleShortKey:function(keyCode) {
                var key = this.getKeyFromKeyCode(keyCode);
                if(this.shortkeys || key == "f1") {
                    if (key != null && this.keyLogs.hasOwnProperty(key)) {
                        var callback = this.keyLogs[key];
                        callback();
                    }

                    if(key == "esc") {
                        this.backPage();
                    }
                }

                else if(key == "esc") {
                    $(':focus').blur();
                }


            },
            templateLoaded:function(){},
            unloadTemplate:function() {},
            restoreTemplate:function(){},
            getKeyFromKeyCode:function(keyCode) {
                for(var key in keymapping)
                {
                    if(keymapping[key]==keyCode)
                        return key;
                }
                return null;

            },
            getKeyCodeFromKey:function(key) {
                if(keymapping.hasOwnProperty(key))
                    return keymapping[key];
                return null;
            },
            addKeyLog:function(key, callback, description) {
                if(!this.keyLogs.hasOwnProperty(key)) {
                    $('#keysInfo').append('<span>('+key.toUpperCase()+') '+description+'</span>');
                }
                this.keyLogs[key] = callback;
            },
            resetKeyLog:function () {
                this.keyLogs = {};
                $('#keysInfo').html('');
            },

            loadPage:function(section, page, args, noStack) {
                var thisApp = this;
                thisApp.loadTemplate(section, page, function (template) {
                    $('#central-content').html(template);
                }, args, noStack);
            },
            backPage:function() {
                var data = this.pagesStack.pop();
                if(data != null) {
                    this.loadPage(data.seccion, data.page, data.args, true);
                    if(data.template != null) {
                        $('#central-content').html(data.template);
                        this.currentPageData = data.pageData;
                        this.restoreTemplate(data.template);
                    }
                }

            },

            manageAjaxError:function(data) {
                var jsonError = this.parseJsonOrError(data.responseText);
                if(jsonError == false) {
                    this.advice.send(this.advice.ERROR, "Error desconocido. Consulte un técnico.");
                }
                else {
                    for(var i=0; i<jsonError.errors; i++) {
                        this.advice.send(this.advice.ERROR, jsonError.errors[i]);
                    }
                }
            },

            parseJsonOrError:function(str) {

                try {
                    return JSON.parse(str);
                } catch (e) {
                    return false;
                }

            }

        };


        $(function() {

            $('body').on('keydown', keyDownHandlerBase);

            $('body').on('focusout', 'input, textarea, select', function(e) {
                WEBAPP.common.shortkeys = true;
            });

            $('body').on('focusin', 'input, textarea, select', function(e) {
                WEBAPP.common.shortkeys = false;
            });

            $('body').on('click', 'a', function(e) {
                e.preventDefault();
                var enlace = $(this).attr('href');
                enlace = enlace.split("/");
                WEBAPP.common.loadTemplate(enlace[0], enlace[1], function(template){
                    $('#central-content').html(template);
                });
            });

            WEBAPP.common.deleteAllTempData();

        });

        function keyDownHandlerBase(e) {
            WEBAPP.common.handleShortKey(e.keyCode);
        }

        var keymapping = {};
        keymapping["esc"] = 27;
        keymapping["b"] = 66;
        keymapping["g"] = 71;
        keymapping["l"] = 76;
        keymapping["m"] = 77;
        keymapping["p"] = 80;
        keymapping["r"] = 82;
        keymapping["f1"] = 112;






    </script>
@endsection