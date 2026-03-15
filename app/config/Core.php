<?php

/*
 * App Core Class
 * Mapear URLs a Controladores
 * Formato URL: /controlador/metodo/parametros
 */
class Core
{
    protected $currentController = 'TerceroController';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->getUrl();

        // Buscar el controlador en la ruta
        // Usar ucwords para capitalizar el controlador
        if (isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')) {
            // Setear como actual
            $this->currentController = ucwords($url[0]) . 'Controller';
            // Destruir variable del array
            unset($url[0]);
        }

        // Requerir el controlador
        require_once '../app/controllers/' . $this->currentController . '.php';

        // Instanciar el controlador
        $this->currentController = new $this->currentController;

        // Comprobar la segunda parte de la url (el método)
        if (isset($url[1])) {
            // Comprobar si el método existe en el controlador instanciado
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                // Destruir variable
                unset($url[1]);
            }
        }

        // Obtener parámetros restantes
        $this->params = $url ? array_values($url) : [];

        // Llamar la función con parámetros usando call_user_func_array
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
