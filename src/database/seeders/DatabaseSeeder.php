<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'dosen', 'member', 'alumni'] as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@hcm.test'],
            ['name' => 'HCM Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->syncRoles(['admin']);
        $admin->profile()->firstOrCreate([]);

        // ponytail: sample users for local dev only, delete block for prod seed
        $samples = [
            ['Dr. Rahmat Hidayat, M.Kom.',   'dosen',  'Informatika',       2010],
            ['Dr. Anita Wulandari, Ph.D.',   'dosen',  'Sistem Informasi',  2015],
            ['Andi Pratama',                 'member', 'Informatika',       2022],
            ['Sinta Rahayu',                 'member', 'Sistem Informasi',  2023],
            ['Budi Wibowo',                  'alumni', 'Informatika',       2018],
            ['Citra Kirana',                 'alumni', 'Teknik Elektro',    2019],
        ];
        foreach ($samples as [$name, $role, $prodi, $ang]) {
            $slug = trim(preg_replace('/[^a-z0-9]+/', '.', strtolower($name)), '.');
            $u = User::firstOrCreate(
                ['email' => $slug . '@hcm.test'],
                ['name' => $name, 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $u->syncRoles([$role]);
            $u->profile()->updateOrCreate([], [
                'prodi'    => $prodi,
                'angkatan' => $ang,
                'nrp'      => $role === 'dosen' ? null : '5021' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'nip'      => $role === 'dosen' ? '19' . str_pad((string) random_int(70000000000000, 99999999999999), 14, '0', STR_PAD_LEFT) : null,
                'bio'      => 'Passionate about human-centric multimedia research.',
                'skills'   => ['Laravel', 'Computer Vision', 'UI/UX'],
            ]);
        }

        // ponytail: sample content w/ picsum placeholder URLs so carousel has material.
        //           Remove this block for prod seed.
        $dosen = User::role('dosen')->first();
        $projectData = [
            ['Vision-Based Sign Language Recognition',
             'Real-time SIBI sign recognition using MediaPipe hand landmarks + transformer classifier. Achieves 92% on 20-word vocabulary.',
             'https://picsum.photos/seed/hcm-proj-1/1200/700'],
            ['Adaptive Learning Companion',
             'LLM-backed tutoring system that adapts problem difficulty from EEG-derived cognitive load estimates.',
             'https://picsum.photos/seed/hcm-proj-2/1200/700'],
            ['Ambient Music Recommender',
             'Multi-modal recommender combining time-of-day, weather, and biometric signals to seed ambient playlists.',
             'https://picsum.photos/seed/hcm-proj-3/1200/700'],
        ];
        foreach ($projectData as $i => [$title, $desc, $img]) {
            $p = Project::firstOrCreate(
                ['title' => $title],
                ['user_id' => $dosen->id, 'description' => $desc, 'published' => true,
                 'github_url' => 'https://github.com/hcm-lab/sample']
            );
            if ($p->images()->doesntExist()) {
                $p->images()->create(['path' => $img, 'sort_order' => 0]);
            }
        }

        $galleryData = [
            ['HCM Weekly Standup',           'https://picsum.photos/seed/hcm-gal-1/900/900'],
            ['Research Poster Session',      'https://picsum.photos/seed/hcm-gal-2/900/900'],
            ['Guest Lecture: Prof. Miura',   'https://picsum.photos/seed/hcm-gal-3/900/900'],
            ['Lab Anniversary Dinner',       'https://picsum.photos/seed/hcm-gal-4/900/900'],
            ['Field Study, HCI Workshop',    'https://picsum.photos/seed/hcm-gal-5/900/900'],
            ['Team Photo 2026',              'https://picsum.photos/seed/hcm-gal-6/900/900'],
        ];
        foreach ($galleryData as [$title, $img]) {
            GalleryItem::firstOrCreate(
                ['title' => $title],
                ['user_id' => $admin->id, 'image_path' => $img, 'caption' => 'Auto-seeded demo item.', 'taken_at' => now()->subDays(random_int(1, 90))]
            );
        }
    }
}
