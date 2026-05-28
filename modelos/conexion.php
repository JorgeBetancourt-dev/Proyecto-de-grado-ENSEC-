<?php

class Conexion{
    static public function conectar(){
        $link = new PDO("mysql:host=localhost;dbname=sistema_clinico_codigo",
                        "root",
                        "");
// utf8
        $link->exec("set names utf8");
        return $link;
    }
}
