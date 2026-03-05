OBJECTIF :

Développer une application web complète, dynamique et professionnelle pour la gestion des agences de transport de biens et services.

⚠️ IMPORTANT :

Ne pas générer une démo

Ne pas générer du code partiel

Ne pas générer un prototype

Générer une application complète prête pour production

Code structuré, sécurisé, optimisé et professionnel

📌 1. CONTEXTE DU PROJET

Application web nommée :

TRANSLOG AGENCY MANAGER

Système complet de gestion des agences de transport permettant :

Gestion des agences

Gestion des employés

Gestion des clients

Gestion des véhicules

Gestion des chauffeurs

Gestion des trajets

Gestion des colis (biens)

Gestion des services

Gestion des paiements

Gestion des dépenses

Gestion des rapports

Gestion des utilisateurs et rôles

📌 2. TECHNOLOGIES OBLIGATOIRES

Le projet doit utiliser uniquement :

HTML5

CSS3 moderne

JavaScript simple (pas de framework, pas de React, pas de Vue, pas de Vanilla framework)

PHP procédural (pas de Laravel, pas de framework)

MySQL

AJAX en JS simple si nécessaire

Architecture MVC recommandée mais en PHP classique.

📌 3. STRUCTURE OBLIGATOIRE DU PROJET
/config
    database.php
/includes
    header.php
    footer.php
    sidebar.php
    auth.php
/modules
    agences/
    employes/
    clients/
    vehicules/
    chauffeurs/
    trajets/
    colis/
    services/
    paiements/
    depenses/
    rapports/
/assets
    /css
    /js
    /images
index.php
login.php
logout.php
dashboard.php

Toutes les relations doivent utiliser des clés étrangères.

📌 4. FONCTIONNALITÉS OBLIGATOIRES
🔐 Authentification complète

Login sécurisé

Mot de passe hashé avec password_hash()

Gestion des sessions

Protection contre accès direct

📊 Dashboard dynamique

Afficher :

Nombre total agences

Nombre employés

Nombre colis en cours

Revenus du mois

Dépenses du mois

Graphique JS simple

🏢 Gestion complète CRUD pour :

Agences

Employés

Clients

Véhicules

Chauffeurs

Trajets

Colis

Services

Paiements

Dépenses

Chaque module doit inclure :

Ajouter

Modifier

Supprimer

Lister

Recherche

Pagination

Validation côté serveur

📈 Module Rapport

Rapport mensuel

Rapport annuel

Revenus vs Dépenses

Export PDF (optionnel)

Impression

📌 5. EXIGENCES PROFESSIONNELLES

Le code doit :

Être sécurisé contre SQL Injection (requêtes préparées)

Être structuré

Être commenté

Être responsive

Utiliser CSS moderne (Flexbox ou Grid)

Utiliser JS uniquement pour interaction (modals, confirmation, AJAX)

Utiliser SweetAlert si nécessaire (CDN autorisé)

📌 6. DESIGN

Interface :

Moderne

Sidebar fixe

Navbar

Dashboard cartes statistiques

Tableaux stylisés

Formulaires propres

Responsive mobile et desktop donc type de Mobile First

📌 7. SÉCURITÉ OBLIGATOIRE

Validation côté serveur

Protection XSS

Protection CSRF

Sessions sécurisées

Contrôle des rôles

📌 8. LIVRABLES ATTENDUS

Le générateur doit fournir :

Code complet de tous les fichiers

Script SQL complet

Instructions d’installation

Compte admin par défaut :

email : admin@translog.com

mot de passe : Admin@123

📌 10. INTERDICTIONS

❌ Pas de code incomplet
❌ Pas de données fictives inutiles
❌ Pas de "à compléter plus tard"
❌ Pas de démo
❌ Pas de framework

🎯 OBJECTIF FINAL

Application prête à être déployée sur :

XAMPP


Serveur mutualisé

100% fonctionnelle.