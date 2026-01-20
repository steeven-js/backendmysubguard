<?php

namespace Database\Seeders;

use App\Models\CatalogueItem;
use Illuminate\Database\Seeder;

class CatalogueItemSeeder extends Seeder
{
    /**
     * Seed the catalogue_items table with 50 popular subscriptions.
     */
    public function run(): void
    {
        $items = [
            // Streaming Video (10)
            ['name' => 'Netflix', 'typical_amount' => 13.49, 'category' => 'Streaming'],
            ['name' => 'Disney+', 'typical_amount' => 8.99, 'category' => 'Streaming'],
            ['name' => 'Amazon Prime Video', 'typical_amount' => 6.99, 'category' => 'Streaming'],
            ['name' => 'Canal+', 'typical_amount' => 24.99, 'category' => 'Streaming'],
            ['name' => 'OCS', 'typical_amount' => 11.99, 'category' => 'Streaming'],
            ['name' => 'Apple TV+', 'typical_amount' => 9.99, 'category' => 'Streaming'],
            ['name' => 'Paramount+', 'typical_amount' => 7.99, 'category' => 'Streaming'],
            ['name' => 'Crunchyroll', 'typical_amount' => 5.99, 'category' => 'Streaming'],
            ['name' => 'Max (HBO)', 'typical_amount' => 9.99, 'category' => 'Streaming'],
            ['name' => 'Molotov Plus', 'typical_amount' => 4.99, 'category' => 'Streaming'],

            // Musique (8)
            ['name' => 'Spotify', 'typical_amount' => 10.99, 'category' => 'Musique'],
            ['name' => 'Apple Music', 'typical_amount' => 10.99, 'category' => 'Musique'],
            ['name' => 'Deezer', 'typical_amount' => 10.99, 'category' => 'Musique'],
            ['name' => 'YouTube Music', 'typical_amount' => 9.99, 'category' => 'Musique'],
            ['name' => 'Tidal', 'typical_amount' => 10.99, 'category' => 'Musique'],
            ['name' => 'Amazon Music', 'typical_amount' => 9.99, 'category' => 'Musique'],
            ['name' => 'SoundCloud Go+', 'typical_amount' => 9.99, 'category' => 'Musique'],
            ['name' => 'Qobuz', 'typical_amount' => 12.99, 'category' => 'Musique'],

            // Cloud & Stockage (6)
            ['name' => 'iCloud+', 'typical_amount' => 2.99, 'category' => 'Cloud'],
            ['name' => 'Google One', 'typical_amount' => 2.99, 'category' => 'Cloud'],
            ['name' => 'Dropbox Plus', 'typical_amount' => 11.99, 'category' => 'Cloud'],
            ['name' => 'OneDrive', 'typical_amount' => 2.00, 'category' => 'Cloud'],
            ['name' => 'pCloud', 'typical_amount' => 4.99, 'category' => 'Cloud'],
            ['name' => 'Mega Pro', 'typical_amount' => 4.99, 'category' => 'Cloud'],

            // Logiciels & Productivité (8)
            ['name' => 'Microsoft 365', 'typical_amount' => 7.00, 'category' => 'Logiciel'],
            ['name' => 'Adobe Creative Cloud', 'typical_amount' => 59.99, 'category' => 'Logiciel'],
            ['name' => 'Notion', 'typical_amount' => 8.00, 'category' => 'Logiciel'],
            ['name' => 'Figma', 'typical_amount' => 12.00, 'category' => 'Logiciel'],
            ['name' => '1Password', 'typical_amount' => 2.99, 'category' => 'Logiciel'],
            ['name' => 'Dashlane', 'typical_amount' => 3.33, 'category' => 'Logiciel'],
            ['name' => 'NordVPN', 'typical_amount' => 3.99, 'category' => 'Logiciel'],
            ['name' => 'ExpressVPN', 'typical_amount' => 8.32, 'category' => 'Logiciel'],

            // Gaming (6)
            ['name' => 'PlayStation Plus', 'typical_amount' => 8.99, 'category' => 'Gaming'],
            ['name' => 'Xbox Game Pass', 'typical_amount' => 12.99, 'category' => 'Gaming'],
            ['name' => 'Nintendo Switch Online', 'typical_amount' => 3.99, 'category' => 'Gaming'],
            ['name' => 'EA Play', 'typical_amount' => 4.99, 'category' => 'Gaming'],
            ['name' => 'Ubisoft+', 'typical_amount' => 14.99, 'category' => 'Gaming'],
            ['name' => 'GeForce NOW', 'typical_amount' => 9.99, 'category' => 'Gaming'],

            // Presse & News (5)
            ['name' => 'Le Monde', 'typical_amount' => 9.99, 'category' => 'Presse'],
            ['name' => 'Mediapart', 'typical_amount' => 11.00, 'category' => 'Presse'],
            ['name' => 'The New York Times', 'typical_amount' => 4.00, 'category' => 'Presse'],
            ['name' => 'Les Echos', 'typical_amount' => 19.99, 'category' => 'Presse'],
            ['name' => 'Apple News+', 'typical_amount' => 12.99, 'category' => 'Presse'],

            // Sport & Fitness (4)
            ['name' => 'Basic-Fit', 'typical_amount' => 29.99, 'category' => 'Sport'],
            ['name' => 'Fitness Park', 'typical_amount' => 24.99, 'category' => 'Sport'],
            ['name' => 'Strava', 'typical_amount' => 5.00, 'category' => 'Sport'],
            ['name' => 'Nike Training Club', 'typical_amount' => 14.99, 'category' => 'Sport'],

            // Livraison & Services (3)
            ['name' => 'Amazon Prime', 'typical_amount' => 6.99, 'category' => 'Services'],
            ['name' => 'Uber One', 'typical_amount' => 9.99, 'category' => 'Services'],
            ['name' => 'Deliveroo Plus', 'typical_amount' => 5.99, 'category' => 'Services'],
        ];

        foreach ($items as $item) {
            CatalogueItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'typical_amount' => $item['typical_amount'],
                    'category' => $item['category'],
                    'logo_url' => null, // Logos à ajouter plus tard
                ]
            );
        }

        $this->command->info('Seeded ' . count($items) . ' catalogue items.');
    }
}
