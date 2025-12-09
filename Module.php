<?php

namespace ResumeCours;

use Omeka\Module\AbstractModule;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Mvc\Controller\AbstractController;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        
        // Les clés API sont maintenant intégrées directement dans le backend
        $maxUpload = $settings->get('resumecours_max_upload', 500);
        $ollamaEnabled = $settings->get('resumecours_ollama_enabled', false);
        $ollamaEndpoint = $settings->get('resumecours_ollama_endpoint', 'http://localhost:11434');
        $ollamaModel = $settings->get('resumecours_ollama_model', 'llama2');
        $responseLanguage = $settings->get('resumecours_response_language', 'fr');
        $responseDetail = $settings->get('resumecours_response_detail', 'normal');

        return $renderer->partial(
            'resume-cours/config-form',
            [
                'maxUpload' => $maxUpload,
                'ollamaEnabled' => $ollamaEnabled,
                'ollamaEndpoint' => $ollamaEndpoint,
                'ollamaModel' => $ollamaModel,
                'responseLanguage' => $responseLanguage,
                'responseDetail' => $responseDetail,
            ]
        );
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        
        // Les clés API ne sont plus configurables via l'interface
        $settings->set('resumecours_max_upload', $controller->params()->fromPost('resumecours_max_upload', 500));
        $settings->set('resumecours_ollama_enabled', (bool) $controller->params()->fromPost('resumecours_ollama_enabled', false));
        $settings->set('resumecours_ollama_endpoint', $controller->params()->fromPost('resumecours_ollama_endpoint', ''));
        $settings->set('resumecours_ollama_model', $controller->params()->fromPost('resumecours_ollama_model', 'llama2'));
        $settings->set('resumecours_response_language', $controller->params()->fromPost('resumecours_response_language', 'fr'));
        $settings->set('resumecours_response_detail', $controller->params()->fromPost('resumecours_response_detail', 'normal'));
    }
}
