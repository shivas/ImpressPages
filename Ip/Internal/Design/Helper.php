<?php
/**
 * @package ImpressPages
 *
 */

namespace Ip\Internal\Design;


use \Ip\Form as Form;

class Helper
{

    protected function __construct()
    {

    }

    /**
     * @return Helper
     */
    public static function instance()
    {
        return new Helper();
    }



    public function cpDir( $source, $destination ) {
        $source = $this->removeTrailingSlash($source);
        $destination = $this->removeTrailingSlash($destination);
        if (is_dir( $source ) ) {
            @mkdir($destination);
            $directory = dir( $source );
            while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
                if ( $readdirectory == '.' || $readdirectory == '..' ) {
                    continue;
                }
                $pathDir = $source . '/' . $readdirectory;
                if ( is_dir( $pathDir ) ) {
                    $this->cpDir( $pathDir, $destination . '/' . $readdirectory );
                    continue;
                }
                copy( $pathDir, $destination . '/' . $readdirectory );
            }

            $directory->close();
        } else {
            copy( $source, $destination );
        }
    }

    private function removeTrailingSlash($path)
    {
        return preg_replace('{/$}', '', $path);
    }

    public function getFirstDir($path)
    {
        $files = scandir($path);
        if (!$files) {
            return false;
        }
        foreach($files as $file) {
            if ($file != '.' && $file != '..' && is_dir($path . '/' . $file)) {
                return $file;
            }
        }
    }

    public function extractZip($archivePath, $destinationDir)
    {
        if (class_exists('\\ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($archivePath) === true) {
                $zip->extractTo($destinationDir);
                $zip->close();
            } else {
                throw new \Ip\Exception('Theme extraction failed.');
            }
        } else {
            require_once(ipFile('Ip/Internal/PclZip.php'));
            $zip = new \PclZip($archivePath);
            if (!$zip->extract(PCLZIP_OPT_PATH, $destinationDir)) {
                throw new \Ip\Exception('Theme extraction failed.');
            }
        }
    }

    /**
     * Clean comments of json content and decode it with json_decode().
     * Work like the original php json_decode() function with the same params
     *
     * @param   string  $json    The json string being decoded
     * @param   bool    $assoc   When TRUE, returned objects will be converted into associative arrays.
     * @param   integer $depth   User specified recursion depth. (>=5.3)
     * @param   integer $options Bitmask of JSON decode options. (>=5.4)
     * @return  string
     */
    function json_clean_decode($json, $assoc = false, $depth = 512, $options = 0) {

        // search and remove comments like /* */ and //
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t](//).*)#", '', $json);

        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $json = json_decode($json, $assoc, $depth, $options);
        } else {
            $json = json_decode($json, $assoc, $depth);
        }

        return $json;
    }


    public function generateAdminUrl($moduleId)
    {
        return ipActionUrl(array('admin' => 1, 'module_id' => $moduleId, 'security_token' => $this->backendSecurityToken()));
    }

    private function backendSecurityToken()
    {
        if(!isset($_SESSION['backend_session']['security_token'])){
            $_SESSION['backend_session']['security_token'] =  md5(uniqid(rand(), true));
        }
        return $_SESSION['backend_session']['security_token'];
    }

    public static function getConfigurationBoxHtml()
    {
        $configModel = ConfigModel::instance();

        $form = $configModel->getThemeConfigForm(ipConfig()->theme());
        $form->removeClass('ipModuleForm');
        $variables = array(
            'form' => $form
        );
        $optionsBox = ipView('view/optionsBox.php', $variables);
        return $optionsBox->render();
    }

}
