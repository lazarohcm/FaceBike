<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <link rel="icon" href="logo.jpg" type="image/jpg" sizes="16x16"> 
            <title>Facebike</title>
            <!-- Import OL CSS, auto import does not work with our minified OL.js build -->
            <link rel="stylesheet" type="text/css" href="http://localhost:8080/geoserver/openlayers/theme/default/style.css"/>

            <!-- Bootstrap -->
            <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>

            <!-- Basic CSS definitions -->
            <style type="text/css">
                /* General settings */
                body {
                    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
                    font-size: small;
                }
                /* Toolbar styles */
                #toolbar {
                    position: relative;
                    padding-bottom: 0.5em;
                    display: none;
                }

                #toolbar ul {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                #toolbar ul li {
                    float: left;
                    padding-right: 1em;
                    padding-bottom: 0.5em;
                }

                #toolbar ul li a {
                    font-weight: bold;
                    font-size: smaller;
                    vertical-align: middle;
                    color: black;
                    text-decoration: none;
                }

                #toolbar ul li a:hover {
                    text-decoration: underline;
                }

                #toolbar ul li * {
                    vertical-align: middle;
                }

                /* The map and the location bar */
                #map {
                    clear: both;
                    position: relative;
                    width: 100%;
                    height: 400px;
                    border: 1px solid black;
                }

                #wrapper {
                    width: 780px;
                }

                #location {
                    float: right;
                }

                #options {
                    position: absolute;
                    left: 13px;
                    top: 7px;
                    z-index: 3000;
                }

                /* Styles used by the default GetFeatureInfo output, added to make IE happy */
                table.featureInfo, table.featureInfo td, table.featureInfo th {
                    border: 1px solid #ddd;
                    border-collapse: collapse;
                    margin: 0;
                    padding: 0;
                    font-size: 90%;
                    padding: .2em .1em;
                }

                table.featureInfo th {
                    padding: .2em .2em;
                    font-weight: bold;
                    background: #eee;
                }

                table.featureInfo td {
                    background: #fff;
                }

                table.featureInfo tr.odd td {
                    background: #eee;
                }

                table.featureInfo caption {
                    text-align: left;
                    font-size: 100%;
                    font-weight: bold;
                    padding: .2em .2em;
                }
            </style>
            <!-- Import OpenLayers, reduced, wms read only version -->
            <script src="js/ol.js"></script>

            <!-- JQuery -->
            <script src="js/jquery.js" type="text/javascript"></script>

            <!-- Import OpenLayers, reduced, wms read only version -->
            <script src="js/bootstrap.min.js" type="text/javascript"></script>

            <script defer="defer" type="text/javascript">
                $(document).ready(function () {
                    var facebike = new ol.source.TileWMS({
                        url: 'http://localhost:8080/geoserver/FaceBike/wms',
                        params: {"LAYERS": 'FaceBike:rotas'},
                        serverType: 'geoserver',
                    });

                    var wmsLayer = new ol.layer.Tile({
                        source: facebike
                    });

                    var osm = new ol.layer.Tile({
                        source: new ol.source.OSM()
                    });

                    var view = new ol.View({
                        center: [-4930667.014467361, -282162.445168026],
                        zoom: 15,
                    });




                    //Linestring
                    var source = new ol.source.Vector();

                    var vector = new ol.layer.Vector({
                        source: source,
                        style: new ol.style.Style({
                            fill: new ol.style.Fill({
                                color: 'rgba(255, 255, 255, 0.2)'
                            }),
                            stroke: new ol.style.Stroke({
                                color: '#ffcc33',
                                width: 2
                            }),
                            image: new ol.style.Circle({
                                radius: 7,
                                fill: new ol.style.Fill({
                                    color: '#ffcc33'
                                })
                            })
                        })
                    });

                    map = new ol.Map({
                        target: 'map',
                        layers: [osm, wmsLayer, vector],
                        view: view,
                    });
                    var draw; // global so we can remove it later
                    map.on('singleclick', function (evt) {
                        var viewResolution = /** @type {number} */ (view.getResolution());
                        var url = facebike.getGetFeatureInfoUrl(
                                evt.coordinate, viewResolution, 'EPSG:3857',
                                {'INFO_FORMAT': 'application/json'});
                        if (url) {
                            $.post('buscar.php', {url: url}, function (response) {
                                response = response.split('|');                              
                                $('#tdNome').text(response[0].replace('"', ''));
                                $('#tdDia').text(response[1].replace('"', ''));
                                $('#tdHora').text(response[2].replace('"', ''));
                                $('#tdCriador').text(response[3].replace('"', ''));
                            });
                        }
                    });

                    //Draw
                    var typeSelect = document.getElementById('type');


                    function addInteraction() {
                        var value = 'LineString'
                        if (value !== 'None') {
                            draw = new ol.interaction.Draw({
                                source: source,
                                type: /** @type {ol.geom.GeometryType} */ (value)
                            });
                            map.addInteraction(draw);

                            draw.on('drawend', function (evt) {
                                map.removeInteraction(draw);
                                $('#modalCadastro').modal('toggle');
                            });
                        }
                    }

                    $('#salvar').on('click', function () {
                        saveData();
                    })

                    function saveData() {
                        var format = new ol.format['GeoJSON'], linestring, nome, criador, dias, hora;
                        try {
                            linestring = format.writeFeatures(vector.getSource().getFeatures());
                            linestring = linestring.substring(linestring.indexOf("coordinates") + 14, linestring.length - 5);
                            console.log(linestring);
                            nome = $('#nome').val();
                            criador = $('#criador').val();
                            dias = $('#dias').val();
                            hora = $('#hora').val();
                            var dataPost = {nome: nome, criador: criador, dias: dias, hora: hora, linestring: linestring}
                            $.post('gravar.php', dataPost, function (response) {
                                console.log(response);
                            });
                        } catch (e) {
                            console.log(e.name + ": " + e.message);
                        }
                    }
                    $('#adicionar').on('click', function () {
                        $('#nome').val('');
                        $('#criador').val('');
                        $('#dias').val('');
                        $('#hora').val('');
                        //vector.getSource().clear();
                        map.removeInteraction(draw);
                        addInteraction();
                    });
                });




            </script>
    </head>
    <body>
        <!-- Docs master nav -->
        <header class="navbar navbar-static-top bs-docs-nav" id="top" role="banner">
            <div class="container">
                <div class="navbar-header">
                    <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a href="" class="navbar-brand">
                        Facebike
                    </a>
                </div>
                <nav class="collapse navbar-collapse bs-navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li>
                            <a href="index.php">HOME</a>
                        </li>
                    </ul>
                    <button id='adicionar' type="button" class="btn btn-primary navbar-btn pull-right">Adicionar Caminho</button>
                </nav>
            </div>
        </header>
        <div id="toolbar" style="display: none;">

        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div id="map" class="map"></div>
                </div>
            </div>

        </div>

        <div id="nodelist">
            <em>Clique nos pontos para obter informações</em>
            <form class="form-inline" hidden>
                <label>Geometry type &nbsp;</label>
                <select id="type">
                    <option value="None">None</option>
                    <option value="Point">Point</option>
                    <option value="LineString">LineString</option>
                    <option value="Polygon">Polygon</option>
                </select>
            </form>
        </div>

        <div id="wrapper">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Criador</th>
                        <th>Dia</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id='tdNome'></td>
                        <td id='tdCriador'></td>
                        <td id="tdDia"></td>
                        <td id='tdHora'></td>
                    </tr>
                </tbody>
            </table>
            <div id="info"> </div>
            <div id="scale">
            </div>
        </div>


        <div class="modal fade" id="modalCadastro" tabindex="-1" role="dialog" aria-labelleddy="modalCadastro" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Cadastrar passeio</h4>
                    </div>

                    <div class="modal-body">
                        <label> Nome: </label>
                        <input name="nome" id='nome' type='text' class="form-control" placeholder="Nome do passeio"/>

                        <label> Criador: </label>
                        <input name="criador" id='criador' type='text' class="form-control" placeholder="Nome do criador"/>

                        <label> Dias da Semana: </label>
                        <input name="dias" id='dias' type='text' class="form-control" placeholder="Segunda, Terça, Sexta..."/>

                        <label> Hora: </label>
                        <input name="hora" id='hora' type='text' class="form-control" placeholder="Hora de inicio do passeio"/>

                    </div>

                    <div class="modal-footer">
                        <button id="fechar" type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                        <button id="salvar" type="button" class="btn btn-primary" data-dismiss="modal">Salvar</button>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>
