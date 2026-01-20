#!/bin/bash

# Script pour migrer les tables vers le schéma mysubguard dans Supabase

echo "🔧 Configuration du schéma mysubguard..."

# Vérifier que la connexion à la base de données fonctionne
php artisan db:show 2>&1 || {
    echo "❌ Erreur de connexion à la base de données. Vérifiez votre fichier .env"
    exit 1
}

echo "📦 Exécution des migrations dans le schéma mysubguard..."

# Exécuter les migrations fresh (supprime toutes les tables et les recrée)
php artisan migrate:fresh

echo "🌱 Exécution des seeders..."

# Exécuter les seeders
php artisan db:seed

echo "✅ Migration et seeding terminés avec succès!"
echo "📊 Les tables ont été créées dans le schéma: mysubguard"
