# ResumeCours Module

Module Omeka-S pour la transcription et l'analyse automatique de cours audio. Intègre la transcription via Whisper API et l'analyse avec Ollama (IA locale).

## Installation

## 📋 Prérequis

Avant d'installer le module, assurez-vous d'avoir :

- **Omeka-S** version 3.0.0+ ou 4.0.0+ installé et en fonctionnement
- **PHP** 7.4+ avec les extensions :
  - `curl` (pour les appels API)
  - `json` (pour traiter les réponses JSON)
  - `fileinfo` (pour vérifier les types de fichiers)
- **Ollama** installé localement (optionnel mais recommandé) pour l'IA locale
  - Télécharger depuis https://ollama.ai
  - Modèle recommandé : `llama2`, `mistral`, ou `neural-chat`

## 🔧 Installation Détaillée

### Étape 1 : Télécharger et Placer le Module

```bash
# Cloner ou télécharger le module dans le répertoire des modules d'Omeka-S
cd /chemin/vers/omeka-s/modules
git clone https://github.com/salmaMamouni/Omeka-S-module-ResumeCours.git ResumeCours

# Ou manuellement : télécharger le ZIP et extraire dans modules/ResumeCours
```

### Étape 2 : Vérifier les Permissions

```bash
# Assurer les bonnes permissions sur le répertoire du module
chmod -R 755 /chemin/vers/omeka-s/modules/ResumeCours
```

### Étape 3 : Activer le Module via l'Interface d'Administration

1. Accédez à l'administration Omeka-S : `http://localhost/omk_thyp_25-26_clone/admin`
2. Naviguez vers **Modules** dans le menu latéral
3. Trouvez **ResumeCours** dans la liste
4. Cliquez sur **Installer**
5. Le module apparaîtra ensuite dans le menu d'administration sous **ResumeCours**

### Étape 4 : Configurer le Module

#### Via l'Interface Web (Recommandé)

1. Cliquez sur **ResumeCours** → **Configuration** (ou l'icône ⚙️)
2. Configurez les paramètres suivants :

| Paramètre | Valeur par Défaut | Description |
|-----------|-------------------|-------------|
| **Max Upload Size** | 500 MB | Taille maximale des fichiers audio à télécharger |
| **Enable Ollama** | Désactivé | Activer l'IA locale Ollama pour l'analyse |
| **Ollama Endpoint** | http://localhost:11434 | URL de l'instance Ollama |
| **Ollama Model** | llama2 | Modèle Ollama à utiliser (llama2, mistral, neural-chat, etc.) |
| **Response Language** | fr | Langue des réponses (fr, en, es, etc.) |
| **Response Detail** | normal | Niveau de détail (rapide, normal, détaillé) |

#### Configuration via PHP (Alternative)

Éditer `config/module.ini` si nécessaire :

```ini
[info]
name = "ResumeCours"
version = 1.0.0
author = "SALMA MAMOUNI-ALAOUI"
configurable = true
description = "Transcription et analyse automatique de cours audio"
module_link = "http://localhost/omk_thyp_25-26_clone"
author_link = "http://localhost"
omeka_version_constraint = "^3.0.0 || ^4.0.0"
```

### Étape 5 : Configuration des Clés API (Intégrées)

Les clés API Omeka-S sont **déjà intégrées** dans le module et stockées de manière sécurisée :

- **Identity Key** : Utilisée pour l'authentification Omeka-S
- **API Key** : Utilisée pour accéder à l'API Omeka-S

Ces clés sont configurées dans `src/Controller/IndexController.php` (classe constante).

### Étape 6 : Configurer Ollama (Optionnel mais Recommandé)

#### Installation d'Ollama

```bash
# Télécharger Ollama depuis https://ollama.ai
# Ou via brew (macOS) :
brew install ollama

# Ou via Linux :
curl https://ollama.ai/install.sh | sh
```

#### Lancer Ollama

```bash
# Démarrer le service Ollama (écoute sur http://localhost:11434 par défaut)
ollama serve

# Dans un autre terminal, télécharger un modèle
ollama pull llama2
# Autres modèles populaires :
ollama pull mistral
ollama pull neural-chat
```

#### Configuration dans ResumeCours

1. Allez à **ResumeCours** → **Configuration**
2. Cochez **Enable Ollama**
3. Vérifiez que **Ollama Endpoint** est `http://localhost:11434`
4. Sélectionnez le **Ollama Model** que vous avez téléchargé

## 🚀 Utilisation

### Via l'Interface Web (Admin)

1. Cliquez sur **ResumeCours** dans le menu d'administration
2. Cliquez sur **Nouveau** pour créer une nouvelle analyse
3. Remplissez le formulaire :
   - **Titre** : Nom du cours
   - **Description** : Brève description
   - **Fichier Audio** : Sélectionnez MP3, WAV, OGG, M4A, ou FLAC
   - **Langue** : Sélectionnez la langue du cours
4. Cliquez sur **Analyser**

### Résultats Attendus

Après le traitement, vous obtiendrez :

- **Transcription** : Texte complet du cours transcrit
- **Résumé** : Résumé automatique généré par Ollama
- **Questions** : Questions pédagogiques suggérées
- **Mots-clés** : Extraction automatique des concepts clés

## 📁 Structure du Module

```
ResumeCours/
├── Module.php                      # Classe principale du module
├── README.md                        # Cette documentation
├── config/
│   ├── module.config.php           # Configuration Laminas/Zend
│   └── module.ini                  # Métadonnées du module
├── src/
│   └── Controller/
│       ├── IndexController.php     # Interface admin principale
│       └── StudyWhisApiController.php # API pour Study-Whis
└── view/
    └── resume-cours/
        ├── index/
        │   └── index.phtml         # Template d'interface
        └── config-form.phtml       # Template de configuration
```

## 🔌 Intégration Study-Whis

Le module est intégré avec le projet **Study-Whis** pour une synchronisation complète :

### Étapes d'Intégration

1. **Frontend Study-Whis** appelle l'API ResumeCours
2. **ResumeCours** traite le fichier audio
3. **Résultats** sont créés comme des items Omeka-S
4. **Study-Whis** affiche et gère les ressources

### API Endpoints

- `POST /admin/study-whis-api/process` : Traiter un fichier audio
- `GET /admin/study-whis-api/resources` : Récupérer les ressources
- `GET /admin/study-whis-api/analyses` : Récupérer les analyses

## 🛠️ Dépannage

### Le module n'apparaît pas après activation

- Vérifier que le répertoire `modules/ResumeCours` existe
- Vérifier les permissions du répertoire
- Vider le cache Omeka-S : `sudo rm -rf /chemin/omeka-s/application/cache/*`
- Redémarrer le serveur Apache/Nginx

### Ollama ne répond pas

```bash
# Vérifier que Ollama est lancé
curl http://localhost:11434/api/tags

# Redémarrer Ollama
pkill ollama
ollama serve
```

### Erreur lors de l'upload

- Vérifier la taille du fichier (ne dépasse pas 500 MB par défaut)
- Vérifier que le format audio est supporté (MP3, WAV, OGG, M4A, FLAC)
- Vérifier que le répertoire de cache Omeka-S est accessible en écriture

### Performance lente

- Augmentez les limites de temps PHP dans `php.ini` :
  ```ini
  max_execution_time = 600  # 10 minutes
  upload_max_filesize = 500M
  post_max_size = 500M
  ```
- Réduisez le **Ollama Model** ou passez à un modèle plus léger

## 📚 Ressources Supplémentaires

- **Omeka-S Docs** : https://omeka.org/s/docs/
- **Instance Locale** : http://localhost/omk_thyp_25-26_clone/
- **Ollama** : https://ollama.ai
- **Study-Whis** : Consulter la documentation du projet principal

## 📄 Licence

Ce module est fourni sous licence MIT.

## 👤 Auteur

**SALMA MAMOUNI-ALAOUI**

Pour les questions ou contributions, consultez le dépôt GitHub du projet.
