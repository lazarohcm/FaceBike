<?php
    $url = $_POST['url'];
    if(isset($url)){
        $json = file_get_contents($url);
	$obj = json_decode($json);
	$dadosRota = $obj->features[0]->properties;
        echo json_encode($dadosRota->nome.'|'.$dadosRota->dias.'|'.$dadosRota->horario.'|'.$dadosRota->criador, TRUE);
    }else{
        echo 'Busca vazia';
    }

