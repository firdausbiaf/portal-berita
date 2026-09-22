<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();

        $categories = Category::all()->keyBy('slug');
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all()->keyBy('slug');
        }

        $sampleArticles = [
            [
                'title' => 'Pemerintah Resmikan Pusat Data Nasional AI Pertama di Nusantara',
                'category_slug' => 'teknologi',
                'excerpt' => 'Fasilitas superkomputer ramah lingkungan ini ditargetkan mempercepat transformasi digital dan kedaulatan data nasional.',
                'content' => '<p>Pemerintah meresmikan fasilitas pusat data nasional terpadu yang didukung oleh komputasi kecerdasan buatan mutakhir.</p>',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 8420,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subHours(2),
            ],
            [
                'title' => 'Pertumbuhan Ekonomi Kuartal Ini Capai 5,2 Persen Ditopang Ekspor',
                'category_slug' => 'bisnis',
                'excerpt' => 'Sektor manufaktur dan perdagangan mencatatkan surplus perdagangan yang konsisten selama enam bulan berturut-turut.',
                'content' => '<p>Badan Pusat Statistik mengumumkan rilis kinerja perekonomian nasional yang mencatatkan ekspansi positif.</p>',
                'is_featured' => true,
                'is_trending' => false,
                'view_count' => 6150,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subHours(4),
            ],
            [
                'title' => 'Timnas Indonesia Raih Kemenangan Bersejarah di Kualifikasi Piala Dunia',
                'category_slug' => 'olahraga',
                'excerpt' => 'Gol tunggal pada menit ke-78 memastikan langkah skuad Garuda menuju babak playoff dengan optimisme tinggi.',
                'content' => '<p>Stadion Gelora Bung Karno bergemuruh saat peluit panjang ditiupkan menandai kemenangan krusial tim nasional.</p>',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 14320,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subHours(5),
            ],
            [
                'title' => 'Reformasi Birokrasi Digital Pangkas Waktu Layanan Publik Menjadi 1 Jam',
                'category_slug' => 'nasional',
                'excerpt' => 'Integrasi portal layanan satu pintu resmi diberlakukan serentak di 38 provinsi di seluruh Indonesia.',
                'content' => '<p>Kementerian Pendayagunaan Aparatur Negara meluncurkan sistem perizinan digital terpadu nasional.</p>',
                'is_featured' => true,
                'is_trending' => false,
                'view_count' => 4200,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subHours(8),
            ],
            [
                'title' => 'Festival Film Asia Nobatkan Karya Sutradara Muda Indonesia Sebagai Film Terbaik',
                'category_slug' => 'hiburan',
                'excerpt' => 'Kisah humanis berlatar budaya lokal berhasil memukau dewan juri internasional di Tokyo Film Awards.',
                'content' => '<p>Prestasi membanggakan kembali ditorehkan oleh perfilman Indonesia di kancah perfilman internasional.</p>',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 9750,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subHours(10),
            ],
            [
                'title' => 'Tren Gaya Hidup Slow Living Mulai Diminati Kalangan Pekerja Urban',
                'category_slug' => 'lifestyle',
                'excerpt' => 'Menyeimbangkan ritme kerja cepat dengan mindful living menjadi pilihan untuk menjaga kesehatan mental generasi muda.',
                'content' => '<p>Praktik hidup melambat dan mengapresiasi momen sehari-hari kian marak di kalangan profesional kota besar.</p>',
                'is_featured' => false,
                'is_trending' => true,
                'view_count' => 3800,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subHours(12),
            ],
            [
                'title' => 'Inovasi Panel Surya Generasi Baru Berdaya Efisiensi Tinggi Dikembangkan di Bandung',
                'category_slug' => 'teknologi',
                'excerpt' => 'Teknologi sel surya perovskite lokal ini diklaim mampu menyerap cahaya optimal bahkan di cuaca mendung.',
                'content' => '<p>Para peneliti perguruan tinggi teknik di Bandung berhasil merampungkan prototipe sel surya mutakhir.</p>',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 2900,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Pasar Modal Menguat 1,4 Persen Menyambut Kebijakan Suku Bunga Global',
                'category_slug' => 'bisnis',
                'excerpt' => 'Indeks Harga Saham Gabungan (IHSG) melesat didorong oleh aksi beli investor domestik pada saham berkapitalisasi besar.',
                'content' => '<p>Perdagangan saham ditutup di zona hijau dengan volume transaksi yang meningkat signifikan.</p>',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 1850,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Kejuaraan Bulu Tangkis Indonesia Open Siap Digelar Pekan Depan',
                'category_slug' => 'olahraga',
                'excerpt' => 'Ratusan pebulu tangkis kelas dunia dari 24 negara telah mengonfirmasi keikutsertaan mereka di Istora Senayan.',
                'content' => '<p>Persiapan arena dan akomodasi atlet mancanegara telah mencapai 95 persen menjelang pembukaan kejuaraan.</p>',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 5400,
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Rancangan Dokumen Strategis Kebijakan Fiskal 2027 (Draft Internal)',
                'category_slug' => 'bisnis',
                'excerpt' => 'Dokumen rahasia persiapan kajian ekonomi belum boleh terpublikasi ke ranah umum.',
                'content' => '<p>Isi dokumen internal ini hanya untuk konsumsi rapat terbatas tim perumus anggaran.</p>',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 99999,
                'status' => ArticleStatus::DRAFT,
                'published_at' => null,
            ],
        ];

        foreach ($sampleArticles as $item) {
            $cat = $categories->get($item['category_slug']);
            if (! $cat) {
                continue;
            }

            Article::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'author_id' => $admin->id,
                    'category_id' => $cat->id,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'status' => $item['status'],
                    'is_featured' => $item['is_featured'],
                    'is_trending' => $item['is_trending'],
                    'view_count' => $item['view_count'],
                    'published_at' => $item['published_at'],
                ]
            );
        }
    }
}
