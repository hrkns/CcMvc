<?php

/**
 * Copyright (C) 2016 Enyerber Franco
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  
 */

namespace Cc\Mvc;

use Cc\Mvc;

/**
 * Controllers                                                       
 *  CLASE BASE PARA TODOS LOS CONTROLADORES.                                    
 *  LOS METODOS PUBLICOS PODRAN SER LLAMADOS VIA GET O PATH SEGUN LA CONFIGURACION      
 *  LOS PARAMETROS DE DICHOS METODOS SERAN PASADOS AUTOMATICAMENTE 	   
 *  SOLO SERAN DEFINIDOS PUBLIC LOS METODOS QUE NESESITEN SER EJECUTADO POR     
 *  EL CLIENTE                                                                 
 *                                                                              
 *                                                                              
 *                                                              
 *                                                           
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores
 * @example ../examples/CERQU/protected/controllers/Cindex.php EJEMPLO DE UNA CLASE CONTROLADORA EXTENDIDA DE Controllers #1  
 * @example ../examples/cine/protected/controllers/Cpelicula.php EJEMPLO DE UNA CLASE CONTROLADORA EXTENDIDA DE Controllers #2
 *
 * @property ViewController $view Controlador de vistas   
 * @property LayautManager $Layaut Controlador de layauts                                                
 */
abstract class Controllers implements InfoController
{

    /**
     * Controlador de vistas    
     * @var ViewController 
     */
    public static $View;

    /**
     * representacion de variables del layaut
     * @var LayautManager 
     */
    public static $Layaut;

    /**
     * representacion de variables del layaut
     * @var Request 
     */
    public static $Request;

    /**
     * representacion de variables del layaut
     * @var Config 
     */
    public static $Config;

    /**
     * 
     * @param string $name
     * @return mixes
     */
    public final function &__get($name)
    {
        $NULL = NULL;
        switch (strtolower($name))
        {
            case 'view':
                return static::$View;
            case 'layaut':
                return static::$Layaut;
            case 'request':
                return static::$Request;
            case 'config':
                return static::$Config;
            default :

                ErrorHandle::Notice("EL ATRIBUTO " . static::class . '::$' . $name . " NO ESTA DEFINIDO ");
                return $NULL;
        }
    }

    /**
     * 
     * @param string $name
     * @param array $arguments
     * @return mixes
     * @throws Exception
     */
    public final function __call($name, $arguments)
    {
        if (method_exists(static::class, $name))
        {
            return static::$name(...$arguments);
        } else
        {
            throw new Exception("EL METODO " . static::class . '::' . $name . " NO ESTA DEFINIDO ");
        }
    }

    /**
     * DESTRUYE EL OBJETO ResponseConten QUE ADMINISTRA EL CONTENIDO
     * @uses ResponseConten::Destroy() 
     */
    protected final static function DestroyResponseObject()
    {
        Mvc::App()->Response->Destroy();
    }

    /**
     * CAMBIA EN Content-type QUE SE ENVIARA SI EXISTE UNA CLASE MANEJADORA DE RESPUESTA PARA ESTE Content-type ESTA SERA INSTANCIADA Y REMPLAZARA AL ACTUAL OBJETO DE RESPUESTA
     * @param string $conten_type
     * @return ResponseConten Objeto de respuesta
     */
    protected final static function &ChangeContenType($conten_type)
    {
        if (Mvc::App()->Content_type != $conten_type)
            Mvc::App()->ChangeResponseConten($conten_type);

        header('Content-type: ' . $conten_type);

        return Mvc::App()->Response;
    }

    /**
     * Cambia el objeto de respuesta basado en la extencion pasada por parametro
     * @param string $ext
     * @return bool
     */
    protected final static function ChangeExt($ext)
    {
        if (isset(Mvc::App()->Config()->Response['ExtencionContenType'][$ext]))
        {
            return self::ChangeContenType(Mvc::App()->Config()->Response['ExtencionContenType'][$ext]);
        }
    }

    /**
     * CARGA E INTERPRETA DOCUMENTO VISTA DEL DIRECTORIO SITIO ESTABLECIDA 
     * @param string $page nombre del documento sin extencion
     * @param array $agrs ARRAY ASOCIATIVO CON LAS VARIABLES QUE TENDRA DISPONIBLE EL DOCUMENTO VISTA EL OBJETO DE CONTROL DE CONTENIDO SE PARASA EN LA VARIABLE $OBjconten COMO REFERENCIA
     * POR LO QUE NO SERA NESEARIO PARASALO EN EL ARRAY
     * @example ../examples/CERQU/protected/view/estudiantes/index.php ejemplo de un archivo view o vista 
     * @uses ViewController::Load()
     * 
     */
    protected static function LoadView($page, array ...$agrs)
    {
        self::$View->Load($page, ...$agrs);
    }

    /**
     * CARGA E INTERPRETA Y RETORNA EL CONTENIDO DE UN DOCUMENTO VISTA DEL DIRECTORIO SITIO ESTABLECIDA 
     * @param string $page nombre del documento sin extencion
     * @param array $agrs ARRAY ASOCIATIVO CON LAS VARIABLES QUE TENDRA DISPONIBLE EL DOCUMENTO VISTA EL OBJETO DE CONTROL DE CONTENIDO SE PARASA EN LA VARIABLE $OBjconten COMO REFERENCIA
     * POR LO QUE NO SERA NESEARIO PARASALO EN EL ARRAY
     * @example ../examples/CERQU/protected/view/estudiantes/index.php ejemplo de un archivo view o vista 
     * @uses ViewController::Load()
     * 
     */
    protected static function FetchView($page, array ...$agrs)
    {
        return self::$View->Fetch($page, ...$agrs);
    }

    /**
     * carga un view de error establecido y cierra la ejecucion 
     * @param int $errno numero de error Http
     * @param string $msj mesaje para tiempos de depuracion

     */
    protected static function HttpError($errno, $msj = '')
    {
        Mvc::App()->LoadError($errno, $msj);
        exit;
    }

    /**
     * REDIRECCIONA LA PAGINA 
     * @param  string $page EL NOMBRE DE LA CLASE Y METODO DONDE SE REDIRECCIONARA ESTE DEVE CUMPLIR CON LA SINTAXIS ESTABLECIDA PARA LA NAVEGACION DE CONTROLADORES
     * EN EL DOCUMENTO DE CONFIGURACION
     * @param array $get VARIABLES QUE SERAN ENVIADAS MEDIANTE GET
     * 
     * @uses Mvc::Redirec()
     */
    protected final static function Redirec($page, array $get = [])
    {

        Mvc::Redirec($page, $get);
    }

    /**
     * 
     * @return \ReflectionClass
     */
    public final static function GetReflectionClass()
    {
        return Mvc::App()->SelectorController->GetReflectionController();
    }

    /**
     * retorna el contrlador actual 
     * @return array  ['controller'=>string,'method'=>string,'paquete'=>string,'extencion'=>string]
     */
    public final static function SelfControler()
    {
        return Mvc::App()->GetController();
    }

    /**
     * crea un link para el controlador actual 
     * @param array $get variables get 
     * @param array $page indices a cabiar del controlador
     * @return string
     */
    public final static function SelfHref($get = [], $page = [])
    {
        return Router::Href($page + Mvc::App()->GetController(), $get);
    }

    /**
     * CONSTRULLE UN LINK HACIA UNCONTROLADOR 
     * @param mixes $page un string que siga la sintaxis de llamada por get o path donde se especifiqen 
     * el controlado y el metodo si es un array deve contener los indices [paquete] indicara el paquete 
     * [class] indicara el controlador [method]indicara el metodos que sera llamado
     * @param array $get variables que que tendra el link 
     * 
     * @return string link valido para usa en link html
     */
    public final static function Href($page, $get = [])
    {
        return Router::Href($page, $get);
    }

    /**
     * Retorna la instancia del controlador actual 
     * @return Controllers 
     */
    public final static function GetInstance()
    {
        return Mvc::App()->SelectorController->GetController();
    }

}
