<?php

use \app\facade\Route;

Route::get('/web/index/<name>/<id?>', function (\frame\core\Request $request, $name, $id = 100) {
    var_dump($request->getRoute());
    return "this is web index name:{$name} id:{$id} software:{$request->serverSoftware()}";
});