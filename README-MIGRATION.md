# Migration vers le schéma mysubguard dans Supabase

## Configuration

Le backend Laravel est configuré pour utiliser le schéma `mysubguard` dans Supabase.

### Fichier de configuration modifié
- `config/database.php` : Le `search_path` est maintenant configuré pour utiliser `mysubguard` au lieu de `public`

## Méthode 1 : Exécution SQL directe dans Supabase (Recommandé)

1. Ouvrez le **SQL Editor** dans votre dashboard Supabase
2. Copiez le contenu du fichier `database/sql/mysubguard-schema.sql`
3. Collez-le dans l'éditeur SQL
4. Exécutez le script

Cette méthode crée toutes les tables et insère les données de seed directement dans le schéma `mysubguard`.

## Méthode 2 : Via Laravel Artisan (si la connexion fonctionne)

Si votre environnement local peut se connecter à Supabase :

```bash
cd backend
php artisan migrate:fresh --seed
```

Assurez-vous que votre fichier `.env` contient :
```env
DB_CONNECTION=pgsql
DB_HOST=db.ilqiwryvbjxyptrjwfdx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_SCHEMA=mysubguard
```

## Vérification

Après l'exécution, vous devriez voir dans Supabase :
- Le schéma `mysubguard` avec toutes les tables créées
- Les données de seed dans la table `catalogue_items` (50 entrées)

## Tables créées

- `users` - Utilisateurs de l'application
- `password_reset_tokens` - Tokens de réinitialisation de mot de passe
- `sessions` - Sessions utilisateur
- `cache` - Cache de l'application
- `cache_locks` - Verrous de cache
- `jobs` - Jobs en file d'attente
- `job_batches` - Lots de jobs
- `failed_jobs` - Jobs échoués
- `personal_access_tokens` - Tokens d'accès API (Sanctum)
- `catalogue_items` - Catalogue des abonnements (avec données de seed)
- `analytics_events` - Événements d'analytics
- `suggestions` - Suggestions d'abonnements
- `user_milestones` - Jalons utilisateur
- `migrations` - Table de suivi des migrations Laravel
