<?php
/**
 * @package ImpressPages
 *
 *
 */
namespace Ip\Internal\Config;




class AdminController extends \Ip\Controller{

    public function index()
    {


        ipAddJs('Ip/Internal/Config/assets/config.js');

        $form = Forms::getForm();
        $data = array (
            'form' => $form
        );
        return ipView('view/configWindow.php', $data)->render();

    }


    public function saveValue()
    {
        $request = \Ip\ServiceLocator::request();

        $request->mustBePost();

        $post = $request->getPost();
        if (empty($post['fieldName'])) {
            throw new \Exception('Missing required parameter');
        }
        $fieldName = $post['fieldName'];
        if (!isset($post['value'])) {
            throw new \Exception('Missing required parameter');
        }
        $value = $post['value'];

        if (!in_array($fieldName, array('automaticCron', 'cronPassword', 'websiteTitle', 'websiteEmail'))) {
            throw new \Exception('Unknown config value');
        }

        $emailValidator = new \Ip\Form\Validator\Email();
        $error = $emailValidator->getError(array('value' => $value), 'value', \Ip\Form::ENVIRONMENT_ADMIN);
        if ($fieldName === 'websiteEmail' && $error !== false) {
            return $this->returnError($error);
        }

        $numberValidator = new \Ip\Form\Validator\Number();
        $error = $numberValidator->getError(array('value' => $value), 'value', \Ip\Form::ENVIRONMENT_ADMIN);


        if (in_array($fieldName, array('websiteTitle', 'websiteEmail'))) {
            if (!isset($post['languageId'])) {
                throw new \Exception('Missing required parameter');
            }
            $languageId = $post['languageId'];
            ipSetOptionLang('Config.' . $fieldName, $value, $languageId);
        } else {
            ipSetOption('Config.' . $fieldName, $value);
        }


        return new \Ip\Response\Json(array(1));

    }

    private function returnError($errorMessage)
    {
        $data = array(
            'error' => $errorMessage
        );
        return new \Ip\Response\Json($data);
    }
}
