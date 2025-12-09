<?php

namespace ResumeCours\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;

/**
 * StudyWhis API Controller
 * Gère les appels API depuis la page apiOmk.html intégrée
 */
class StudyWhisApiController extends AbstractRestfulController
{
    public function indexAction()
    {
        $method = $this->getRequest()->getMethod();
        
        if ($method === 'POST') {
            return $this->handleUploadAndProcess();
        } elseif ($method === 'GET') {
            return $this->getResources();
        }
        
        return new JsonModel(['error' => 'Méthode non supportée']);
    }

    /**
     * Traite l'upload d'audio depuis apiOmk.html
     */
    private function handleUploadAndProcess()
    {
        try {
            $files = $this->getRequest()->getFiles()->toArray();
            $params = $this->getRequest()->getPost()->toArray();

            if (!isset($files['audio_file'])) {
                throw new \Exception('Aucun fichier audio trouvé');
            }

            $audioFile = $files['audio_file'];
            $title = $params['title'] ?? 'Sans titre';
            $description = $params['description'] ?? '';
            $language = $params['language'] ?? 'fr';
            $studentName = $params['student_name'] ?? '';

            // Vérifications
            if ($audioFile['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Erreur lors de l\'upload: ' . $audioFile['error']);
            }

            $settings = $this->settings();
            $maxSize = $settings->get('resumecours_max_upload', 500) * 1024 * 1024;

            if ($audioFile['size'] > $maxSize) {
                throw new \Exception('Le fichier est trop volumineux');
            }

            // Appel au traitement audio (existant dans ResumeCours)
            $result = $this->processAudioWithResumeCours(
                $audioFile,
                $language,
                $settings->get('resumecours_ollama_model', 'llama2')
            );

            // Création de l'item Omeka-S avec les templates Study-Whis
            $itemId = $this->createStudyWhisItem(
                $title,
                $description,
                $audioFile['name'],
                $result['transcription'],
                $result['summary'],
                $result['questions'],
                $language,
                $studentName
            );

            return new JsonModel([
                'success' => true,
                'message' => 'Ressource créée avec succès',
                'itemId' => $itemId,
                'transcription' => $result['transcription'],
                'summary' => $result['summary'],
                'questions' => $result['questions']
            ]);

        } catch (\Exception $e) {
            return new JsonModel([
                'success' => false,
                'error' => $e->getMessage()
            ], false);
        }
    }

    /**
     * Récupère les ressources créées
     */
    private function getResources()
    {
        try {
            $om = $this->getServiceLocator()->get('Omeka\EntityManager');
            $repository = $om->getRepository('Omeka\Entity\Item');
            
            // Récupérer les items avec le template 'Audio'
            $items = $repository->findAll();
            
            $resources = [];
            foreach ($items as $item) {
                $resources[] = [
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'date' => $item->getCreated()->format('Y-m-d'),
                    'status' => 'En ligne'
                ];
            }

            return new JsonModel($resources);
        } catch (\Exception $e) {
            return new JsonModel([
                'error' => $e->getMessage()
            ], false);
        }
    }

    /**
     * Traite l'audio avec le module ResumeCours
     */
    private function processAudioWithResumeCours($file, $language, $model)
    {
        // Réutiliser la logique du IndexController de ResumeCours
        $filePath = $file['tmp_name'];
        $filename = $file['name'];

        // Appel Whisper API (clés intégrées dans ResumeCours)
        $transcription = $this->callWhisperAPI($filePath, $filename);

        // Analyse avec Ollama
        $analysis = $this->analyzeWithOllama($transcription, $model, $language);

        return [
            'transcription' => $transcription,
            'summary' => $analysis['summary'],
            'questions' => $analysis['questions']
        ];
    }

    /**
     * Crée un item Omeka-S avec les templates Study-Whis
     */
    private function createStudyWhisItem($title, $description, $fileName, $transcription, $summary, $questions, $language, $studentName)
    {
        try {
            $om = $this->getServiceLocator()->get('Omeka\EntityManager');
            $acl = $this->getServiceLocator()->get('Omeka\Acl');
            
            // Créer l'item avec le template Audio
            $itemManager = $this->getServiceLocator()->get('Omeka\Api\Manager');
            
            $itemData = [
                'o:title' => $title,
                'o:description' => $description,
                'o:public' => true,
                'o:resource_template' => ['o:id' => $this->getResourceTemplateId('Audio')],
                'dcterms:language' => $language,
                'dcterms:created' => ['@value' => date('Y-m-d\TH:i:s')],
                'dcterms:title' => [['@value' => $title, '@language' => $language]],
                'dcterms:description' => [['@value' => $description]],
                'dcterms:creator' => [['@value' => $studentName ?: 'StudyWhis']],
            ];

            // Ajouter les métadonnées Study-Whis
            $itemData['sw:titre'] = [['@value' => $title]];
            $itemData['sw:fichier'] = [['@value' => $fileName]];
            $itemData['sw:dateUpload'] = [['@value' => date('Y-m-d')]];
            $itemData['sw:duree'] = [['@value' => 'À calculer']];
            $itemData['sw:importePar'] = [['@value' => $studentName ?: 'API']];

            // Créer item Audio
            $response = $itemManager->create('items', $itemData);
            $audioItemId = $response->getContent()->id();

            // Créer item Transcription lié
            $transcriptionData = [
                'o:title' => $title . ' (Transcription)',
                'o:resource_template' => ['o:id' => $this->getResourceTemplateId('Transcription')],
                'sw:contenu' => [['@value' => $transcription]],
                'sw:transcritDe' => [['value_resource_id' => $audioItemId]],
            ];

            $response = $itemManager->create('items', $transcriptionData);
            $transcriptionItemId = $response->getContent()->id();

            // Créer item Analyse lié
            $analyseData = [
                'o:title' => $title . ' (Analyse)',
                'o:resource_template' => ['o:id' => $this->getResourceTemplateId('Analyse')],
                'sw:resume' => [['@value' => $summary]],
                'sw:questions' => [['@value' => $questions]],
                'sw:analyseDe' => [['value_resource_id' => $audioItemId]],
            ];

            $response = $itemManager->create('items', $analyseData);
            
            return $audioItemId;

        } catch (\Exception $e) {
            throw new \Exception('Erreur création item Omeka: ' . $e->getMessage());
        }
    }

    /**
     * Récupère l'ID du template de ressource par nom
     */
    private function getResourceTemplateId($templateName)
    {
        try {
            $om = $this->getServiceLocator()->get('Omeka\EntityManager');
            $repository = $om->getRepository('Omeka\Entity\ResourceTemplate');
            $template = $repository->findOneBy(['label' => $templateName]);
            
            return $template ? $template->getId() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Appel à l'API Whisper (simplifié)
     */
    private function callWhisperAPI($filePath, $filename)
    {
        // Pour la démo, retourner un texte exemple
        // En production, utiliser cURL avec les clés API
        return "Transcription automatique du fichier: " . $filename . 
               "\n\nCe contenu provient de l'API Whisper. " .
               "En production, cette transcription serait remplacée par le résultat réel de Whisper API.";
    }

    /**
     * Analyse avec Ollama (simplifié)
     */
    private function analyzeWithOllama($text, $model, $language)
    {
        return [
            'summary' => 'Résumé généré: ' . substr($text, 0, 100) . '...',
            'questions' => "1. Question de révision 1\n2. Question de révision 2\n3. Question de révision 3"
        ];
    }

    /**
     * Accès aux settings Omeka
     */
    protected function settings()
    {
        return $this->getServiceLocator()->get('Omeka\Settings');
    }
}
