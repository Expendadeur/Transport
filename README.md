# TRANSLOG – Gestion d'Agence de Transport

**TRANSLOG** est une application web complète de gestion d'agence de transport, conçue pour automatiser et centraliser les opérations quotidiennes telles que les réservations de billets, l'expédition de colis, le suivi de la flotte de véhicules et la gestion financière.

## 🚀 Fonctionnalités Principales

- **Tableau de Bord Intuitif** : Vue d'ensemble des statistiques clés (revenus, dépenses, réservations).
- **Gestion des Réservations** : Système complet pour enregistrer et suivre les voyages des passagers.
- **Expédition de Colis** : Module de logistique pour le suivi des envois entre agences.
- **Gestion de la Flotte** : Suivi des véhicules (immatriculation, état, affectation).
- **Ressources Humaines** : Gestion des employés (agents, gestionnaires, chauffeurs) avec contrôle d'accès par rôle.
- **Gestion Financière** : Enregistrement des paiements (recettes) et des dépenses opérationnelles.
- **Multi-Agences** : Support pour plusieurs localisations avec des rapports centralisés.

## 🛠️ Stack Technique

- **Langage** : PHP (Architecture Procédurale MVC-like)
- **Base de données** : MySQL / MariaDB (PDO pour la sécurité)
- **Design** : HTML5, CSS3 Moderne (Flexbox/Grid), JavaScript
- **Icônes** : Font Awesome 6.0

## 📦 Installation & Configuration

1. **Prérequis** :
   - Serveur local (XAMPP recommandé, WAMP ou MAMP).
   - PHP 7.4 ou version supérieure.

2. **Installation** :
   - Clonez le dépôt ou copiez les fichiers dans votre dossier `htdocs`.
   - Importez la base de données : Utilisez **phpMyAdmin** pour importer le fichier `transport1 (1).sql`.
   - Configurez la connexion : Modifiez le fichier `config/database.php` avec vos identifiants SQL.

3. **Accès par défaut** :
   - **Lien** : `http://localhost/Gestion_agence_transport/`
   - **Email Admin** : `desire@gmail.com`
   - **Mot de passe** : `desire123`

## 👨‍💻 Pour les Développeurs

Le projet est structuré de manière modulaire :
- `modules/` : Contient la logique spécifique à chaque fonctionnalité.
- `includes/` : Composants réutilisables (header, sidebar, navigation).
- `assets/` : Fichiers CSS et JavaScript de l'interface.
- `config/` : Paramètres système et connexion DB.

---

## 📈 Guide de Publication sur GitHub

Si vous souhaitez envoyer ce projet sur votre propre dépôt GitHub, suivez ces étapes dans votre terminal (à la racine du projet) :

1. **Initialiser Git** :
   ```bash
   git init
   ```

2. **Ajouter les fichiers** :
   ```bash
   git add .
   ```

3. **Valider les changements** :
   ```bash
   git commit -m "Premier commit : Système de gestion TRANSLOG"
   ```

4. **Lier à votre dépôt GitHub** :
   *(Créez d'abord un dépôt vide sur GitHub, puis copiez son URL)*
   ```bash
   git remote add origin https://github.com/VOTRE_PSEUDO/NOM_DU_DEPOT.git
   ```

5. **Envoyer (Push)** :
   ```bash
   git branch -M main
   git push -u origin main
   ```

---
*Développé pour simplifier le transport et la logistique.*
