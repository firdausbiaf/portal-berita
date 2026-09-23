<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DemoArticleSeeder extends Seeder
{
    /**
     * Run the database seeds for demo/presentation articles.
     */
    public function run(): void
    {
        // 1. Copy demo article assets to public storage safely
        $sourceDir = database_path('seeders/assets/articles');
        $targetDir = storage_path('app/public/articles');

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::isDirectory($sourceDir)) {
            foreach (File::files($sourceDir) as $file) {
                $destination = $targetDir.'/'.$file->getFilename();
                File::copy($file->getPathname(), $destination);
            }
        }

        // 2. Resolve admin author
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->admin()->create([
                'name' => 'Redaksi JatimNusa',
                'email' => 'admin@portalberita.test',
            ]);
        }

        // 3. Resolve categories
        $categories = Category::all()->keyBy('slug');
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all()->keyBy('slug');
        }

        // 4. Resolve tags
        $tags = Tag::all()->keyBy('slug');
        if ($tags->isEmpty()) {
            $this->call(TagSeeder::class);
            $tags = Tag::all()->keyBy('slug');
        }

        // 5. Define 12 diverse, category-balanced demo articles with local featured images
        $demoArticles = [
            [
                'title' => 'Pemerintah Resmikan Pusat Data Nasional AI Pertama di Jawa Timur',
                'category_slug' => 'teknologi',
                'excerpt' => 'Fasilitas superkomputer ramah lingkungan ini ditargetkan mempercepat transformasi digital, kedaulatan data, dan ekosistem komputasi cerdas.',
                'content' => '<p>Pemerintah meresmikan fasilitas pusat data nasional terpadu yang didukung oleh infrastruktur komputasi kecerdasan buatan mutakhir di Jawa Timur.</p><p>Fasilitas ini diharapkan mampu melayani kebutuhan analitik data sektor publik maupun riset akademik secara efisien dan aman.</p>',
                'featured_image' => 'articles/demo-teknologi-datacenter.svg',
                'image_caption' => 'Ilustrasi arsitektur pusat data terpadu dan jaringan komputasi cerdas.',
                'image_alt' => 'Pusat Data Nasional AI',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 18450,
                'published_at' => now()->subHours(1),
                'tag_slugs' => ['teknologi', 'inovasi'],
            ],
            [
                'title' => 'Pertumbuhan Ekonomi Jawa Timur Kuartal Ini Capai 5,4 Persen Ditopang Manufaktur',
                'category_slug' => 'bisnis',
                'excerpt' => 'Sektor industri pengolahan dan perdagangan antarpulau mencatatkan kinerja ekspansif yang melampaui rata-rata pertumbuhan nasional.',
                'content' => '<p>Badan Pusat Statistik mencatat tren pertumbuhan ekonomi regional Jawa Timur tumbuh impresif sebesar 5,4 persen secara tahunan.</p><p>Permintaan domestik yang kuat serta peningkatan efisiensi rantai pasok menjadi katalis pendorong utama kinerja kuartal ini.</p>',
                'featured_image' => 'articles/demo-bisnis-ekonomi.svg',
                'image_caption' => 'Grafik pertumbuhan ekonomi dan ekspansi neraca perdagangan regional.',
                'image_alt' => 'Pertumbuhan Ekonomi Jawa Timur',
                'is_featured' => true,
                'is_trending' => false,
                'view_count' => 9320,
                'published_at' => now()->subHours(3),
                'tag_slugs' => ['ekonomi', 'kebijakan'],
            ],
            [
                'title' => 'Timnas Indonesia Raih Kemenangan Krusial di Babak Kualifikasi Piala Dunia',
                'category_slug' => 'olahraga',
                'excerpt' => 'Disiplin taktik dan determinasi tinggi memastikan skuad Garuda mengamankan tiga poin penting di hadapan puluhan ribu pendukung setia.',
                'content' => '<p>Pertandingan berlangsung sengit dengan intensitas tinggi sejak peluit babak pertama ditiupkan oleh wasit.</p><p>Gol penentu kemenangan lahir melalui skema serangan balik cepat yang dieksekusi dengan sempurna di paruh kedua pertandingan.</p>',
                'featured_image' => 'articles/demo-olahraga-timnas.svg',
                'image_caption' => 'Ilustrasi atmosfer pertandingan dan taktik dinamis skuad Garuda.',
                'image_alt' => 'Kemenangan Timnas Indonesia',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 24100,
                'published_at' => now()->subHours(4),
                'tag_slugs' => ['timnas', 'olahraga'],
            ],
            [
                'title' => 'Reformasi Birokrasi Digital Pangkas Waktu Perizinan Publik Menjadi 1 Hari',
                'category_slug' => 'nasional',
                'excerpt' => 'Implementasi sistem satu pintu terintegrasi mempermudah pelaku usaha dan masyarakat memperoleh dokumen legalitas secara transparan.',
                'content' => '<p>Kementerian terkait resmi memberlakukan standardisasi layanan digital terpadu di seluruh kantor wilayah pelayanan publik.</p><p>Langkah ini diharapkan mampu menekan biaya logistik administrasi dan menutup celah pungutan liar.</p>',
                'featured_image' => 'articles/demo-nasional-birokrasi.svg',
                'image_caption' => 'Simbol gerbang layanan publik transparan dan transformasi digital birokrasi.',
                'image_alt' => 'Reformasi Birokrasi Digital',
                'is_featured' => true,
                'is_trending' => false,
                'view_count' => 6700,
                'published_at' => now()->subHours(6),
                'tag_slugs' => ['nasional', 'kebijakan'],
            ],
            [
                'title' => 'Festival Film Asia Nobatkan Karya Sineas Muda Surabaya Sebagai Film Terbaik',
                'category_slug' => 'hiburan',
                'excerpt' => 'Kisah humanis dengan eksplorasi sinematografi otentik berhasil memikat dewan juri internasional dalam ajang kompetisi tahunan.',
                'content' => '<p>Karya independen berlatar lanskap perkotaan Jawa Timur ini berhasil menyisihkan puluhan kandidat dari berbagai negara partisipan.</p><p>Penghargaan ini menjadi bukti semakin diakuinya kualitas narasi perfilman karya sineas muda di kancah global.</p>',
                'featured_image' => 'articles/demo-hiburan-festival.svg',
                'image_caption' => 'Piala penghargaan dan sorotan panggung kompetisi sinematografi internasional.',
                'image_alt' => 'Festival Film Terbaik',
                'is_featured' => true,
                'is_trending' => true,
                'view_count' => 11250,
                'published_at' => now()->subHours(8),
                'tag_slugs' => ['film', 'budaya'],
            ],
            [
                'title' => 'Gaya Hidup Slow Living dan Urban Farming Kian Diminati Warga Perkotaan',
                'category_slug' => 'lifestyle',
                'excerpt' => 'Menemukan ketenangan di tengah hiruk-pikuk kota melalui berkebun mandiri dan pengelolaan waktu yang penuh kesadaran.',
                'content' => '<p>Praktik hidup melambat dan mendekatkan diri dengan alam semakin populer sebagai cara menjaga keseimbangan fisik dan mental.</p><p>Banyak komunitas warga mulai memanfaatkan ruang atap rumah untuk budidaya sayuran organik dan tanaman herbal.</p>',
                'featured_image' => 'articles/demo-lifestyle-slowliving.svg',
                'image_caption' => 'Komposisi tenang seni botanik dan filosofi hidup melambat yang harmonis.',
                'image_alt' => 'Gaya Hidup Slow Living',
                'is_featured' => false,
                'is_trending' => true,
                'view_count' => 5400,
                'published_at' => now()->subHours(10),
                'tag_slugs' => ['lifestyle', 'kesehatan'],
            ],
            [
                'title' => 'Pengembangan Sel Surya Perovskite Lokal Catat Rekor Efisiensi Energi Baru',
                'category_slug' => 'teknologi',
                'excerpt' => 'Inovasi material semikonduktor ramah lingkungan mampu menghasilkan daya listrik stabil bahkan saat kondisi langit mendung.',
                'content' => '<p>Tim periset gabungan universitas teknik berhasil menciptakan prototipe modul surya dengan penyerapan foton yang lebih sensitif.</p><p>Material baru ini memiliki biaya fabrikasi yang jauh lebih terjangkau dibandingkan silikon konvensional.</p>',
                'featured_image' => 'articles/demo-teknologi-solarsystem.svg',
                'image_caption' => 'Panel surya fotovoltaik generasi baru penyerap energi bersih berkelanjutan.',
                'image_alt' => 'Inovasi Sel Surya Perovskite',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 3890,
                'published_at' => now()->subDay(),
                'tag_slugs' => ['teknologi', 'inovasi'],
            ],
            [
                'title' => 'IHSG Menguat Signifikan Dipicu Masuknya Investasi Strategis Hijau',
                'category_slug' => 'bisnis',
                'excerpt' => 'Indeks saham domestik terapresiasi seiring tingginya minat pemodal institusi terhadap instrumen pembiayaan berkelanjutan.',
                'content' => '<p>Aksi beli bersih investor asing terfokus pada emiten sektor energi terbarukan dan perbankan ramah lingkungan.</p><p>Analis memperkirakan tren positif ini akan berlanjut seiring stabilitas nilai tukar rupiah dan inflasi yang terkendali.</p>',
                'featured_image' => 'articles/demo-bisnis-pasarmodal.svg',
                'image_caption' => 'Grafik pergerakan indeks harga saham dan instrumen pasar modal hijau.',
                'image_alt' => 'Pasar Modal Menguat',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 4620,
                'published_at' => now()->subDays(2),
                'tag_slugs' => ['bisnis', 'ekonomi'],
            ],
            [
                'title' => 'Persiapan Arena Kejuaraan Bulu Tangkis Indonesia Open Telah Tuntas',
                'category_slug' => 'olahraga',
                'excerpt' => 'Panitia pelaksana memastikan sarana pertandingan berstandar BWF siap menyambut atlet bulu tangkis papan atas dunia.',
                'content' => '<p>Pemasangan karpet lapangan, sistem pencahayaan bebas silau, dan sensor garis instan telah terverifikasi secara teknis.</p><p>Antusiasme penonton terpantau tinggi dengan tiket babak perempat final hingga final yang ludes terjual dalam hitungan jam.</p>',
                'featured_image' => 'articles/demo-olahraga-badminton.svg',
                'image_caption' => 'Perspektif lapangan bulu tangkis berstandar internasional dan kok pertandingan.',
                'image_alt' => 'Kejuaraan Bulu Tangkis',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 7850,
                'published_at' => now()->subDays(2),
                'tag_slugs' => ['olahraga'],
            ],
            [
                'title' => 'Jembatan Penghubung Antarwilayah Pesisir Siap Dongkrak Akses Logistik',
                'category_slug' => 'nasional',
                'excerpt' => 'Infrastruktur bentang panjang ini memangkas waktu tempuh antarkabupaten dari empat jam menjadi kurang dari empat puluh lima menit.',
                'content' => '<p>Uji beban struktural jembatan gantung modern telah dinyatakan memenuhi seluruh parameter keselamatan konstruksi nasional.</p><p>Konektivitas yang lebih lancar diproyeksikan membuka sentra ekonomi baru bagi komunitas nelayan dan UMKM pesisir.</p>',
                'featured_image' => 'articles/demo-nasional-infrastruktur.svg',
                'image_caption' => 'Struktur jembatan gantung modern perajut konektivitas logistik pesisir nusantara.',
                'image_alt' => 'Jembatan Penghubung Pesisir',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 3120,
                'published_at' => now()->subDays(3),
                'tag_slugs' => ['nasional', 'infrastruktur'],
            ],
            [
                'title' => 'Eksplorasi Kuliner Nusantara: Harmoni Rempah Tradisional di Era Modern',
                'category_slug' => 'lifestyle',
                'excerpt' => 'Para koki muda menghadirkan sajian klasik dengan teknik penyajian kontemporer tanpa menghilangkan keaslian cita rasa rempah leluhur.',
                'content' => '<p>Pendekatan gastronomi modern kini kian mengangkat khazanah kuliner tradisional Jawa Timur dan kepulauan nusantara.</p><p>Penggunaan bahan lokal segar langsung dari petani menjadi komitmen utama dalam menjaga keberlanjutan kuliner autentik.</p>',
                'featured_image' => 'articles/demo-lifestyle-kuliner.svg',
                'image_caption' => 'Eksplorasi estetika hidangan rempah tradisional dalam presentasi kontemporer.',
                'image_alt' => 'Kuliner Nusantara Kontemporer',
                'is_featured' => false,
                'is_trending' => false,
                'view_count' => 2950,
                'published_at' => now()->subDays(3),
                'tag_slugs' => ['lifestyle', 'kuliner'],
            ],
            [
                'title' => 'Perpaduan Musik Tradisi Gamelan dan Orkes Kontemporer Hipnotis Penonton',
                'category_slug' => 'hiburan',
                'excerpt' => 'Kolaborasi musisi lintas generasi menghasilkan gubahan simfoni yang dinamis dan mendapat sambutan meriah dari audiens muda.',
                'content' => '<p>Gedung kesenian bergemuruh saat tabuhan laras gamelan berpadu apik dengan gesekan biola dan dentuman ritmis modern.</p><p>Konser lintas genre ini membuktikan kekayaan seni budaya tradisional selalu relevan melintasi sekat zaman.</p>',
                'featured_image' => 'articles/demo-hiburan-musik.svg',
                'image_caption' => 'Spektrum gelombang harmoni musikal dan tata cahaya panggung pertunjukan seni.',
                'image_alt' => 'Harmoni Musik Tradisi dan Modern',
                'is_featured' => false,
                'is_trending' => true,
                'view_count' => 8900,
                'published_at' => now()->subDays(4),
                'tag_slugs' => ['hiburan', 'musik', 'budaya'],
            ],
        ];

        foreach ($demoArticles as $item) {
            $cat = $categories->get($item['category_slug']);
            if (! $cat) {
                continue;
            }

            $slug = Str::slug($item['title']);

            $article = Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $admin->id,
                    'category_id' => $cat->id,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'featured_image' => $item['featured_image'],
                    'image_caption' => $item['image_caption'],
                    'image_alt' => $item['image_alt'],
                    'status' => ArticleStatus::PUBLISHED,
                    'is_featured' => $item['is_featured'],
                    'is_trending' => $item['is_trending'],
                    'view_count' => $item['view_count'],
                    'published_at' => $item['published_at'],
                ]
            );

            // Sync tags idempotently if tag slugs are specified
            if (! empty($item['tag_slugs'])) {
                $tagIds = [];
                foreach ($item['tag_slugs'] as $tagSlug) {
                    $tag = $tags->get($tagSlug) ?? Tag::firstOrCreate(
                        ['slug' => $tagSlug],
                        ['name' => ucfirst($tagSlug)]
                    );
                    $tagIds[] = $tag->id;
                }
                $article->tags()->sync($tagIds);
            }
        }
    }
}
