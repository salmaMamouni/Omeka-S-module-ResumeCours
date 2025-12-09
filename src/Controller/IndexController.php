<?php

namespace ResumeCours\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    // Clés API intégrées directement dans le backend
    private const WHISPER_API_KEY = 'BwGgF5D4RQL36jy0u8H37i1vmcopffaa';
    private const WHISPER_API_SECRET = 'qRNGNuUExBpKN3nmiqSPcz91969Wd8ko';
    
    public function indexAction()
    {
        set_time_limit(300);
        
        $settings = $this->settings();
        $maxUpload = $settings->get('resumecours_max_upload', 500);
        
        $result = null;
        $error = null;
        
        // Traiter l'upload et la transcription
        if ($this->getRequest()->isPost()) {
            try {
                $files = $this->getRequest()->getFiles()->toArray();
                // Récupérer les paramètres depuis la configuration
                $ollamaModel = $settings->get('resumecours_ollama_model', 'llama2');
                $language = $settings->get('resumecours_response_language', 'fr');
                
                if (isset($files['audio_file']) && $files['audio_file']['error'] == UPLOAD_ERR_OK) {
                    $result = $this->processAudioFile(
                        $files['audio_file'], 
                        $ollamaModel, 
                        $language
                    );
                } else {
                    $error = 'Aucun fichier audio n\'a été téléchargé ou une erreur est survenue.';
                }
            } catch (\Exception $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }
        
        $view = new ViewModel([
            'title' => 'ResumeCours : Transcription et Analyse de Cours Audio',
            'maxUpload' => $maxUpload,
            'result' => $result,
            'error' => $error,
        ]);
        
        return $view;
    }
    
    private function processAudioFile($file, $ollamaModel, $language)
    {
        // Vérifier la taille du fichier
        $maxSize = $this->settings()->get('resumecours_max_upload', 500) * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new \Exception('Le fichier est trop volumineux.');
        }
        
        // Vérifier le format
        $allowedFormats = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/x-m4a', 'audio/flac'];
        if (!in_array($file['type'], $allowedFormats)) {
            throw new \Exception('Format de fichier non supporté.');
        }
        
        $filename = $file['name'];
        $tmpPath = $file['tmp_name'];
        
        // Appel à l'API Whisper pour la transcription
        $transcription = $this->callWhisperAPI($tmpPath, $filename);
        
        // Traitement avec Ollama
        $analysis = $this->analyzeWithOllama($transcription, $ollamaModel, $language);
        
        return [
            'filename' => $filename,
            'transcription' => $transcription,
            'summary' => $analysis['summary'],
            'questions' => $analysis['questions'],
            'model_used' => $ollamaModel,
            'language' => $language,
            'detail_level' => $this->settings()->get('resumecours_response_detail', 'normal'),
        ];
    }
    
    private function callWhisperAPI($filePath, $filename)
    {
        // Simulation de l'appel API Whisper
        // En production, utilisez les vraies clés API définies dans les constantes
        
        // Exemple d'appel avec cURL:
        /*
        $ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
        $cfile = new \CURLFile($filePath, mime_content_type($filePath), $filename);
        $data = [
            'file' => $cfile,
            'model' => 'whisper-1'
        ];
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . self::WHISPER_API_KEY
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['text'] ?? '';
        */
        
        // Pour la démo, retourne un texte exemple
        return "Transcription du cours audio: Ce cours traite des concepts fondamentaux de la programmation, incluant les variables, les boucles, et les fonctions. Les structures de données sont essentielles pour organiser l'information...";
    }
    
    private function analyzeWithOllama($text, $model, $language)
    {
        $ollamaEndpoint = $this->settings()->get('resumecours_ollama_endpoint', 'http://localhost:11434');
        $detailLevel = $this->settings()->get('resumecours_response_detail', 'normal');
        
        // Déterminer les instructions selon le niveau de détail
        $detailInstructions = [
            'concise' => $language === 'fr' ? 'de manière très brève et concise (2-3 phrases maximum)' : 'very briefly and concisely (2-3 sentences maximum)',
            'normal' => $language === 'fr' ? 'de manière équilibrée avec les points essentiels' : 'in a balanced way with essential points',
            'detailed' => $language === 'fr' ? 'de manière détaillée avec tous les concepts importants' : 'in detail with all important concepts',
            'extensive' => $language === 'fr' ? 'de manière exhaustive avec explications approfondies et exemples' : 'extensively with in-depth explanations and examples',
        ];
        
        $instruction = $detailInstructions[$detailLevel] ?? $detailInstructions['normal'];
        
        // Préparer les prompts selon la langue et le niveau de détail
        $prompts = [
            'summary' => $language === 'fr' 
                ? "Résume ce texte en français {$instruction}:\n\n{$text}" 
                : "Summarize this text {$instruction}:\n\n{$text}",
            'questions' => $language === 'fr'
                ? "Génère 5 questions de révision en français basées sur ce texte:\n\n{$text}"
                : "Generate 5 revision questions based on this text:\n\n{$text}"
        ];
        
        $summary = $this->callOllama($ollamaEndpoint, $model, $prompts['summary']);
        $questions = $this->callOllama($ollamaEndpoint, $model, $prompts['questions']);
        
        return [
            'summary' => $summary,
            'questions' => $questions,
        ];
    }
    
    private function callOllama($endpoint, $model, $prompt)
    {
        // Simulation de l'appel Ollama
        // En production, utilisez cURL pour appeler l'API Ollama
        
        /*
        $ch = curl_init($endpoint . '/api/generate');
        $data = json_encode([
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false
        ]);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['response'] ?? '';
        */
        
        // Pour la démo, retourne un texte exemple
        if (strpos($prompt, 'questions') !== false || strpos($prompt, 'Questions') !== false) {
            return "1. Quels sont les concepts fondamentaux de la programmation mentionnés?\n2. Pourquoi les structures de données sont-elles importantes?\n3. Quelle est la différence entre une variable et une fonction?\n4. Comment les boucles facilitent-elles la programmation?\n5. Donnez un exemple d'utilisation d'une structure de données.";
        } else {
            return "Ce cours couvre les bases de la programmation: variables pour stocker des données, boucles pour répéter des actions, et fonctions pour organiser le code. Les structures de données permettent une gestion efficace de l'information.";
        }
    }

    public function dashboardAction()
    {
        $view = new ViewModel([
            'title' => 'Mes Cours',
        ]);
        
        return $view;
    }
}
