<?php

namespace Ip\Module\Design;


/**
 * Class ThemeDownloader
 * @package Ip\Module\Design
 *
 * Downloads and extracts theme into themes directory.
 */
class ThemeDownloader
{
    private $publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC1iChGp4XVbDk7O6jhMrFpCW0W
vkdcVUCTTo7CD8LBm47m4IW5Q+6OvV8WwrI5COaCr3nJV/AzmjnlVrg+gPRA3rUN
K04RAeg9+OOQ+cTfdlf3koPFbA6Z6Et5+CaiIX5BGBmo18oPIsPobg0NnrZFQens
tf1Tcb4xZFMMKDn/WwIDAQAB
-----END PUBLIC KEY-----';

    public function __construct()
    {
        if (!defined('IP_DESIGN_PHPSECLIB_DIR')) {
            define('IP_DESIGN_PHPSECLIB_DIR', __DIR__.'/phpseclib/');
        }
        require_once IP_DESIGN_PHPSECLIB_DIR . 'Crypt/RSA.php';
    }

    public function downloadTheme($name, $url, $signature)
    {
        $model = Model::instance();
        //download theme
        $net = \Ip\Internal\NetHelper::instance();
        $themeTempFilename = $net->downloadFile($url, \Ip\Config::temporarySecureFile(''), $name . '.zip');

        if (!$themeTempFilename) {
            throw new \Ip\CoreException('Theme file download failed.');
        }

        $archivePath = \Ip\Config::temporarySecureFile($themeTempFilename);

        //check signature
        $fileMd5 = md5_file($archivePath);

        $rsa = new \Crypt_RSA();
        $rsa->loadKey($this->publicKey);
        $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        $verified = $rsa->verify($fileMd5, base64_decode($signature));

        if (!$verified) {
            throw new \Ip\CoreException('Theme signature verification failed.');
        }

        //extract
        $helper = Helper::instance();
        $tmpExtractedDir = \Ip\Internal\File\Functions::genUnoccupiedName($name, \Ip\Config::temporarySecureFile(''));
        $helper->extractZip(\Ip\Config::temporarySecureFile($themeTempFilename), \Ip\Config::temporarySecureFile($tmpExtractedDir));
        unlink($archivePath);

        //install
        $extractedDir = $helper->getFirstDir(\Ip\Config::temporarySecureFile($tmpExtractedDir));
        $installDir = $model->getThemeInstallDir();
        $newThemeDir = \Ip\Internal\File\Functions::genUnoccupiedName($name, $installDir);
        rename(\Ip\Config::temporarySecureFile($tmpExtractedDir . '/' . $extractedDir), $installDir . $newThemeDir);

    }



}