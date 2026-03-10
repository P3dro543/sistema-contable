<?php

/* 
 * Controlador Base
 * Carga los modelos y las vistas
 */
class Controller
{

    // Cargar modelo
    public function model($model)
    {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    // Cargar vista
    public function view($view, $data = [])
    {
        // Chequear si existe el archivo de vista
        if (file_exists('../views/' . $view . '.php')) {
            // Pasar data a la vista extraíendola del array (opcional, extrae las variables del array $data)
            extract($data);
            require_once '../views/' . $view . '.php';
        } else {
            // La vista no existe
            die('La vista no existe');
        }
    }
}
