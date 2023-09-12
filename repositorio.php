<?php
require_once 'api-drive/vendor/autoload.php';

define("RAIZ", "1TE512vI9m2SpfILntpPBzxKWqDaofBtM");//https://drive.google.com/drive/folders/1TE512vI9m2SpfILntpPBzxKWqDaofBtM
putenv('GOOGLE_APPLICATION_CREDENTIALS='.$_SERVER["DOCUMENT_ROOT"]."/drive/api-drive/almacenamiento-397704-b068b9f0ef91.json");

class Drive{

    static public function servicioGoogleDrive(){
        try{ 
            $cliente = new Google_Client();
            $cliente->useApplicationDefaultCredentials();
            $cliente->setScopes(['https://www.googleapis.com/auth/drive.file']);
            $servicio = new Google_Service_Drive($cliente);
            return ["estado"=>true,"dato"=> $servicio];
        }catch(Google_Service_Exception $error){
            return ["estado"=>false,"error"=>$error->getMessage()];
        }
    }    

    static public function crearCarpeta($nombre,$carpetaPadre,$servicio){  
        try{ 
            $carpeta = new Google_Service_Drive_DriveFile();

            $carpeta->setName($nombre);
            $carpeta->setParents([$carpetaPadre]);
            $carpeta->setDescription('Directorio creado por PHP API GOOGLE DRIVE');
            $carpeta->setMimeType('application/vnd.google-apps.folder');

            $parametros = [
                'fields' => 'id', 
                'supportsAllDrives' => true,
            ];

            $nueva_carpeta = $servicio->files->create($carpeta, $parametros);           
           
            return ["estado"=>true,"id"=>$nombre,"datos"=>$nueva_carpeta->id];

        }catch(Google_Service_Exception $error){
            return array("estado"=>false,"error"=>$error->getMessage());
        }
    }

    static public function crearArchivo($nombre,$documento,$descripcion,$carpetaPadre,$servicio){

        try{ 
            $archivo = new Google_Service_Drive_DriveFile();
            $archivo->setName($nombre);            
            $tipo_mime = self::mime_type($nombre);
            $archivo->setParents([$carpetaPadre]);
            $archivo->setDescription($descripcion);
            $archivo->setMimeType($tipo_mime);

            $parametros = [
                'data' => file_get_contents($documento),
                'mimeType' => $tipo_mime,
                'uploadType' => 'media',
            ];

            $nuevo_archivo = $servicio->files->create($archivo,$parametros);
            return array("estado"=>true,"id"=>$nuevo_archivo->id,"carpetaPadre"=>$carpetaPadre);
            
        }catch(Google_Service_Exception $error){
            return array("estado"=>false,"error"=>$error->getMessage()); 
        }

    }

    static public function datosArchivo($id,$servicio){
        try {
            $archivo = $servicio->files->get($id);
            return [
                "estado"=>true,
                "nombre"=>$archivo->getName(),
                "descripcion"=>$archivo->getDescription(),
                "tipo_mime"=>$archivo->getMimeType(),
                "miniatura_img"=>$archivo->getThumbnailLink()
            ];

        } catch (Exception $error) {
            return [
                "estado"=>true,
                "error"=>$error->getMessage(),                
            ];
        }
    } 
    
    static public function eliminarArchivo($id, $servicio) { 
        
        try { 
            $content = $servicio->files->delete($id);
            return [
                "estado"=>true,
                "mensaje"=>"Eliminado Correctamente"
            ];
        } catch(Exception $error) {
            return [
                "estado"=>false,
                "mensaje"=>"No se pudo eliminar el archivo",
                "error"=>$error->getMessage()
            ];
        }
        
    }

    static public function eliminarCarpeta($id, $servicio) { 
        try { 
            $servicio->files->delete($id);
            return [
                "estado" => true,
                "mensaje" => "Eliminada correctamente",
            ];
        } catch (Exception $error) {
            return [
                "estado" => false,
                "mensaje" => "No se pudo eliminar la carpeta",
                "error" => $error->getMessage(),
            ];
        }
    }

    
    static public function listarCarpeta($id, $servicio) {
        try {
            $resultados = [];
            $pagina = null;
    
            do {
                $parametros = [
                    'q' => "'$id' in parents",
                    'pageSize' => 1000, 
                    'fields' => 'nextPageToken, files(id, name, mimeType)',
                    'pageToken' => $pagina,
                ];
    
                $archivos = $servicio->files->listFiles($parametros);
    
                foreach ($archivos->getFiles() as $archivo) {
                    $resultados[] = [
                        "id" => $archivo->getId(),
                        "nombre" => $archivo->getName(),
                        "tipo" => $archivo->getMimeType() === 'application/vnd.google-apps.folder' ? 'carpeta' : $archivo->getMimeType(),
                        "miniatura" =>$archivo->getThumbnailLink()
                    ];
                }
    
                $pagina = $archivos->getNextPageToken();
            } while ($pagina != null);
    
            return [
                "estado" => true,
                "contenido" => $resultados,
            ];
        } catch (Exception $error) {
            return [
                "estado" => false,
                "mensaje" => "Error al listar el contenido de la carpeta",
                "error" => $error->getMessage(),
            ];
        }
    }

    

    static public function mdlVerificarExistenciaCarpeta($idCarpeta,$service){
    
        try{
            if($idCarpeta!="" && $idCarpeta!=null){
                $folder = $service->files->get($idCarpeta);   
                $respuesta=array(
                      "estado"=>true,
                      "mensaje"=>"Carpeta ".$folder->name,
                      "datos"=>$folder->id 
                );
                return  $respuesta;  
            }else{
                $respuesta=array(
                    "estado"=>false,
                    "mensaje"=>"No existe la carpeta"                    
                );
                return  $respuesta;            }    
        } catch (Exception $e) {
            $respuesta=array(
                  "estado"=>false,
                  "mensaje"=>$e->getMessage(),                
            );
            return  $respuesta;
            
        }
       
    }


    static public function mime_type($filename) {

          $mime_types = array(
        	 'txt' => 'text/plain',
        	 'htm' => 'text/html',
        	 'html' => 'text/html',
        	 'css' => 'text/css',
        	 'json' => array('application/json', 'text/json'),
        	 'xml' => 'text/xml',
        	 'swf' => 'application/x-shockwave-flash',
        	 'flv' => 'video/x-flv',
        
        	 'hqx' => 'application/mac-binhex40',
        	 'cpt' => 'application/mac-compactpro',
        	 'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
        	 'bin' => 'application/macbinary',
        	 'dms' => 'application/octet-stream',
        	 'lha' => 'application/octet-stream',
        	 'lzh' => 'application/octet-stream',
        	 'exe' => array('application/octet-stream', 'application/x-msdownload'),
        	 'class' => 'application/octet-stream',
        	 'so' => 'application/octet-stream',
        	 'sea' => 'application/octet-stream',
        	 'dll' => 'application/octet-stream',
        	 'oda' => 'application/oda',
        	 'ps' => 'application/postscript',
        	 'smi' => 'application/smil',
        	 'smil' => 'application/smil',
        	 'mif' => 'application/vnd.mif',
        	 'wbxml' => 'application/wbxml',
        	 'wmlc' => 'application/wmlc',
        	 'dcr' => 'application/x-director',
        	 'dir' => 'application/x-director',
        	 'dxr' => 'application/x-director',
        	 'dvi' => 'application/x-dvi',
        	 'gtar' => 'application/x-gtar',
        	 'gz' => 'application/x-gzip',
        	 'php' => 'application/x-httpd-php',
        	 'php4' => 'application/x-httpd-php',
        	 'php3' => 'application/x-httpd-php',
        	 'phtml' => 'application/x-httpd-php',
        	 'phps' => 'application/x-httpd-php-source',
        	 'js' => array('application/javascript', 'application/x-javascript'),
        	 'sit' => 'application/x-stuffit',
        	 'tar' => 'application/x-tar',
        	 'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
        	 'xhtml' => 'application/xhtml+xml',
        	 'xht' => 'application/xhtml+xml',             
        	 'bmp' => array('image/bmp', 'image/x-windows-bmp'),
        	 'gif' => 'image/gif',
        	 'jpeg' => array('image/jpeg', 'image/pjpeg'),
        	 'jpg' => array('image/jpeg', 'image/pjpeg'),
        	 'jpe' => array('image/jpeg', 'image/pjpeg'),
        	 'png' => array('image/png', 'image/x-png'),
        	 'tiff' => 'image/tiff',
        	 'tif' => 'image/tiff',
        	 'shtml' => 'text/html',
        	 'text' => 'text/plain',
        	 'log' => array('text/plain', 'text/x-log'),
        	 'rtx' => 'text/richtext',
        	 'rtf' => 'text/rtf',
        	 'xsl' => 'text/xml',
        	 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        	 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        	 'word' => array('application/msword', 'application/octet-stream'),
        	 'xl' => 'application/excel',
        	 'eml' => 'message/rfc822',
        
        	 // images
        	 'png' => 'image/png',
        	 'jpe' => 'image/jpeg',
        	 'jpeg' => 'image/jpeg',
        	 'jpg' => 'image/jpeg',
        	 'gif' => 'image/gif',
        	 'bmp' => 'image/bmp',
        	 'ico' => 'image/vnd.microsoft.icon',
        	 'tiff' => 'image/tiff',
        	 'tif' => 'image/tiff',
        	 'svg' => 'image/svg+xml',
        	 'svgz' => 'image/svg+xml',
        
        	 // archives
        	 'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
        	 'rar' => 'application/x-rar-compressed',
        	 'msi' => 'application/x-msdownload',
        	 'cab' => 'application/vnd.ms-cab-compressed',
        
        	 // audio/video
        	 'mid' => 'audio/midi',
        	 'midi' => 'audio/midi',
        	 'mpga' => 'audio/mpeg',
        	'mp2' => 'audio/mpeg',
        	 'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
        	 'aif' => 'audio/x-aiff',
        	 'aiff' => 'audio/x-aiff',
        	 'aifc' => 'audio/x-aiff',
        	 'ram' => 'audio/x-pn-realaudio',
        	 'rm' => 'audio/x-pn-realaudio',
        	 'rpm' => 'audio/x-pn-realaudio-plugin',
        	 'ra' => 'audio/x-realaudio',
        	 'rv' => 'video/vnd.rn-realvideo',
        	 'wav' => array('audio/x-wav', 'audio/wave', 'audio/wav'),
        	 'mpeg' => 'video/mpeg',
        	 'mpg' => 'video/mpeg',
        	 'mpe' => 'video/mpeg',
        	 'qt' => 'video/quicktime',
        	 'mov' => 'video/quicktime',
        	 'avi' => 'video/x-msvideo',
        	 'movie' => 'video/x-sgi-movie',
        
        	 // adobe
        	 'pdf' => 'application/pdf',
        	 'psd' => array('image/vnd.adobe.photoshop', 'application/x-photoshop'),
        	 'ai' => 'application/postscript',
        	 'eps' => 'application/postscript',
        	 'ps' => 'application/postscript',
        
        	 // ms office
        	 'doc' => 'application/msword',
        	 'rtf' => 'application/rtf',
        	 'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
        	 'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
        
        	 // open office
        	 'odt' => 'application/vnd.oasis.opendocument.text',
        	 'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
          );
        
          $ext = explode('.', $filename);
          $ext = strtolower(end($ext));
         
          if (array_key_exists($ext, $mime_types)) {
        	return (is_array($mime_types[$ext])) ? $mime_types[$ext][0] : $mime_types[$ext];
          } else if (function_exists('finfo_open')) {
        	 if(file_exists($filename)) {
        	   $finfo = finfo_open(FILEINFO_MIME);
        	   $mimetype = finfo_file($finfo, $filename);
        	   finfo_close($finfo);
        	   return $mimetype;
        	 }
          }
         
          return 'application/octet-stream';
    }
        
        
          
    
}

$servicio=Drive::servicioGoogleDrive();


switch ($_POST["peticion"]) {
    case "listarCarpeta":

        $respuesta = Drive::listarCarpeta($_POST["directorio"],$servicio["dato"]);
        echo json_encode($respuesta,JSON_UNESCAPED_UNICODE);
        break;

    case "crearCarpeta":

        $respuesta = Drive::crearCarpeta($_POST["nombre"],$_POST["directorio"],$servicio["dato"]);
        echo json_encode($respuesta,JSON_UNESCAPED_UNICODE);
        break;

    case "crearArchivo":  

        if ($_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $archivo=$_FILES['archivo']['tmp_name'];
            $nombre=$_FILES['archivo']['name'];
            $carpeta=$_POST["carpeta"];
            $respuesta = Drive::crearArchivo($nombre,$archivo,"hola",$carpeta,$servicio["dato"]);
            echo json_encode($respuesta,JSON_UNESCAPED_UNICODE);
        }        
        break;

    case "eliminarArchivo":     
        
        $archivo=$_POST['archivo'];        
        $respuesta = Drive::eliminarArchivo($archivo,$servicio["dato"]);
        echo json_encode($respuesta,JSON_UNESCAPED_UNICODE);              
        break;

    case "eliminarCarpeta":     
    
        $archivo=$_POST['directorio'];        
        $respuesta = Drive::eliminarCarpeta($archivo,$servicio["dato"]);
        echo json_encode($respuesta,JSON_UNESCAPED_UNICODE);              
        break;        
}


