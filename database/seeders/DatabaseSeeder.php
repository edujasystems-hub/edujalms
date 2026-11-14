<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Company;
use App\Models\Course;
use App\Models\CourseInvitation;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Perusahaan (Companies)
        $companyTelkom = Company::create([
            'name' => 'PT Nusantara Digital Solusindo',
            'code' => 'NDS',
            'industry' => 'Teknologi Informasi & Telekomunikasi',
            'email' => 'corporate@nusantaradigital.id',
            'phone' => '+62 21 555 1234',
            'address' => 'Gedung Cyber 2, Lt. 18, Jl. H.R. Rasuna Said, Jakarta Selatan',
            'logo_url' => 'https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?w=160&auto=format&fit=crop&q=80',
            'is_active' => true,
        ]);

        $companyBank = Company::create([
            'name' => 'Bank Mandiri Sejahtera Tbk',
            'code' => 'BMS',
            'industry' => 'Perbankan & Finansial',
            'email' => 'learning@bankmandirisejahtera.co.id',
            'phone' => '+62 21 555 8899',
            'address' => 'Plaza Mandiri, Jl. Jend. Sudirman Kav 54-55, Jakarta',
            'logo_url' => 'https://images.unsplash.com/photo-1542744094-3a31f272c490?w=160&auto=format&fit=crop&q=80',
            'is_active' => true,
        ]);

        $companyAstra = Company::create([
            'name' => 'Astra Industri Logistik',
            'code' => 'AIL',
            'industry' => 'Manufaktur & Supply Chain',
            'email' => 'hrd@astralogistik.co.id',
            'phone' => '+62 21 555 7766',
            'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
            'logo_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=160&auto=format&fit=crop&q=80',
            'is_active' => true,
        ]);

        // 2. Tutors
        $tutorBudi = Tutor::create([
            'name' => 'Dr. Budi Santoso, M.Kom',
            'email' => 'budi.santoso@eduja.academy',
            'phone' => '+62 812 3456 7890',
            'title' => 'VP of Artificial Intelligence & Cloud Architecture',
            'bio' => 'Praktisi teknologi lebih dari 15 tahun di bidang AI generatif, arsitektur microservices, dan implementasi big data berskala enterprise.',
            'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
            'specialization' => 'Artificial Intelligence, Big Data, Cloud Infrastructure',
            'is_active' => true,
        ]);

        $tutorSiti = Tutor::create([
            'name' => 'Siti Rahmawati, MBA',
            'email' => 'siti.rahmawati@eduja.academy',
            'phone' => '+62 813 9876 5432',
            'title' => 'Chief People Officer & Executive Coach',
            'bio' => 'Executive coach bersertifikasi ICF dengan pengalaman memimpin transformasi budaya organisasi dan program kepemimpinan di Fortune 500.',
            'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&auto=format&fit=crop&q=80',
            'specialization' => 'Strategic Leadership, Corporate Culture, People Management',
            'is_active' => true,
        ]);

        $tutorRian = Tutor::create([
            'name' => 'Rian Adiputra, PMP, CSM',
            'email' => 'rian.adiputra@eduja.academy',
            'phone' => '+62 811 2233 4455',
            'title' => 'Head of Agile Delivery & Program Management',
            'bio' => 'Konsultan manajemen agile yang telah memandu lebih dari 40 transformasi korporasi dari waterfall menuju continuous delivery & Scrum.',
            'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80',
            'specialization' => 'Agile Frameworks, Scrum, OKR & Strategy Execution',
            'is_active' => true,
        ]);

        $tutorDewi = Tutor::create([
            'name' => 'Dewi Lestari, M.Psi',
            'email' => 'dewi.lestari@eduja.academy',
            'phone' => '+62 817 6543 2100',
            'title' => 'Corporate Communications & Negotiation Strategist',
            'bio' => 'Pakar komunikasi bisnis dan negosiasi lintas budaya, sering menjadi narasumber kementerian dan badan BUMN.',
            'avatar_url' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&auto=format&fit=crop&q=80',
            'specialization' => 'High-Stakes Negotiation, Executive Presentation, Public Speaking',
            'is_active' => true,
        ]);

        // 3. Class Groups / Categories (Group Kelas)
        $groupTech = ClassGroup::create([
            'name' => 'Digital Transformation & AI Track',
            'slug' => 'digital-transformation-ai-track',
            'description' => 'Akselerasi adopsi teknologi digital, cloud computing, generative AI, dan keamanan data untuk seluruh lini bisnis perusahaan.',
            'badge_color' => 'indigo',
            'icon' => 'cpu',
            'is_active' => true,
        ]);

        $groupLeadership = ClassGroup::create([
            'name' => 'Corporate Leadership Acceleration',
            'slug' => 'corporate-leadership-acceleration',
            'description' => 'Program pengembangan calon pemimpin masa depan korporasi: strategic thinking, coaching, dan manajemen perubahan organisasi.',
            'badge_color' => 'amber',
            'icon' => 'award',
            'is_active' => true,
        ]);

        $groupAgile = ClassGroup::create([
            'name' => 'Business Agility & Project Execution',
            'slug' => 'business-agility-project-execution',
            'description' => 'Metodologi kerja lincah, manajemen resiko proyek, dan eksekusi strategi dengan pendekatan agile & Scrum modern.',
            'badge_color' => 'emerald',
            'icon' => 'zap',
            'is_active' => true,
        ]);

        $groupSoftskills = ClassGroup::create([
            'name' => 'Professional Mastery & Communication',
            'slug' => 'professional-mastery-communication',
            'description' => 'Keterampilan komunikasi bisnis strategis, negosiasi beresiko tinggi, dan kolaborasi produktif antar departemen.',
            'badge_color' => 'violet',
            'icon' => 'users',
            'is_active' => true,
        ]);

        // 4. Courses (Kelas)
        $course1 = Course::create([
            'class_group_id' => $groupTech->id,
            'tutor_id' => $tutorBudi->id,
            'company_id' => $companyTelkom->id,
            'title' => 'Generative AI for Corporate Productivity',
            'slug' => 'generative-ai-for-corporate-productivity',
            'short_description' => 'Tingkatkan efisiensi kerja tim korporasi hingga 300% dengan pemanfaatan LLM, automasi, dan AI Prompting.',
            'description' => 'Di era disrupsi kecerdasan buatan, profesional dituntut tidak hanya menguasai tools konvensional tetapi mampu berkolaborasi dengan asisten AI. Kursus ini membedah cara memanfaatkan Generative AI secara etis dan aman untuk analisis dokumen, perumusan laporan eksekutif, analisis sentimen, serta automasi alur kerja korporat harian.',
            'level' => 'intermediate',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop&q=80',
            'estimated_duration_hours' => 3.5,
            'competency_impact' => [
                'Literasi Data & AI' => 35,
                'Keahlian Teknis' => 25,
                'Pemecahan Masalah' => 20,
            ],
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        $course2 = Course::create([
            'class_group_id' => $groupLeadership->id,
            'tutor_id' => $tutorSiti->id,
            'company_id' => null,
            'title' => 'Strategic Decision Making in High Uncertainty',
            'slug' => 'strategic-decision-making-in-high-uncertainty',
            'short_description' => 'Kuasai kerangka kerja pengambilan keputusan strategis yang tajam dalam kondisi bisnis yang volatil dan ambigu.',
            'description' => 'Pemimpin masa kini sering menghadapi situasi VUCA (Volatile, Uncertain, Complex, Ambiguous). Modul ini membekali peserta dengan matrix prioritisasi dampak vs risiko, teknik Scenario Planning, serta cara membangun konsensus tim lintas fungsi saat mengambil keputusan krusial.',
            'level' => 'advanced',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&auto=format&fit=crop&q=80',
            'estimated_duration_hours' => 4.0,
            'competency_impact' => [
                'Kepemimpinan' => 40,
                'Pemecahan Masalah' => 30,
                'Manajemen Proyek' => 15,
            ],
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        $course3 = Course::create([
            'class_group_id' => $groupAgile->id,
            'tutor_id' => $tutorRian->id,
            'company_id' => null,
            'title' => 'Scrum & Agile Framework Implementation',
            'slug' => 'scrum-and-agile-framework-implementation',
            'short_description' => 'Praktik langsung sprint planning, retrospective, backlog grooming, dan delivery tepat waktu dalam tim korporat.',
            'description' => 'Agile bukan sekadar teori berdiri 15 menit setiap pagi. Ini adalah pergeseran mindset dari sekadar sibuk menjadi menghasilkan nilai nyata bagi pelanggan bisnis setiap dua minggu. Kursus ini memandu transisi tim konvensional menuju squad agile berkinerja tinggi.',
            'level' => 'beginner',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=600&auto=format&fit=crop&q=80',
            'estimated_duration_hours' => 2.5,
            'competency_impact' => [
                'Manajemen Proyek' => 35,
                'Komunikasi' => 25,
                'Pemecahan Masalah' => 20,
            ],
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        $course4 = Course::create([
            'class_group_id' => $groupSoftskills->id,
            'tutor_id' => $tutorDewi->id,
            'company_id' => null,
            'title' => 'High-Impact Executive Presentation & Storytelling',
            'slug' => 'high-impact-executive-presentation-and-storytelling',
            'short_description' => 'Ubah data kering dan spreadsheet rumit menjadi narasi visual yang memikat Dewan Direksi dan klien korporat.',
            'description' => 'Proposal sehebat apa pun akan ditolak bila tidak disampaikan dengan memikat. Pelajari cara merangkai cerita bisnis berbasis piramida Minto, merancang slide minimalis bergaya C-level, dan menguasai teknik vokal serta bahasa tubuh yang meyakinkan audiens level direksi.',
            'level' => 'intermediate',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=600&auto=format&fit=crop&q=80',
            'estimated_duration_hours' => 3.0,
            'competency_impact' => [
                'Komunikasi' => 45,
                'Kepemimpinan' => 25,
                'Literasi Data & AI' => 15,
            ],
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        $course5 = Course::create([
            'class_group_id' => $groupTech->id,
            'tutor_id' => $tutorBudi->id,
            'company_id' => $companyTelkom->id,
            'title' => 'Enterprise Cloud & Data Security Fundamentals',
            'slug' => 'enterprise-cloud-and-data-security-fundamentals',
            'short_description' => 'Fondasi keamanan data, kepatuhan PDP (Pelindungan Data Pribadi), dan mitigasi ancaman siber enterprise.',
            'description' => 'Memahami arsitektur Zero Trust, enkripsi data in-transit dan at-rest, serta kewajiban regulasi perlindungan data pribadi korporasi. Cocok untuk profesional yang menangani data pelanggan dan sistem bisnis sensitif.',
            'level' => 'intermediate',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=600&auto=format&fit=crop&q=80',
            'estimated_duration_hours' => 3.0,
            'competency_impact' => [
                'Keahlian Teknis' => 40,
                'Literasi Data & AI' => 25,
                'Pemecahan Masalah' => 20,
            ],
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        $course6 = Course::create([
            'class_group_id' => $groupLeadership->id,
            'tutor_id' => $tutorSiti->id,
            'company_id' => null,
            'title' => 'Empathetic Leadership & Conflict Resolution',
            'slug' => 'empathetic-leadership-and-conflict-resolution',
            'short_description' => 'Seni menyelesaikan friksi antar departemen secara elegan tanpa mengorbankan target bisnis perusahaan.',
            'description' => 'Konflik kerja seringkali menjadi penghambat inovasi terbesar di korporasi. Pelajari bagaimana mendengarkan secara aktif, membedakan posisi vs kebutuhan sejati, dan memediasi perselisihan dengan prinsip win-win.',
            'level' => 'intermediate',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=600&auto=format&fit=crop&q=80',
            'estimated_duration_hours' => 2.0,
            'competency_impact' => [
                'Kepemimpinan' => 35,
                'Komunikasi' => 35,
                'Pemecahan Masalah' => 20,
            ],
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(30),
        ]);

        // 5. Course Modules & Lessons
        // Course 1: Generative AI for Corporate Productivity
        $c1m1 = CourseModule::create([
            'course_id' => $course1->id,
            'title' => 'Modul 1: Fondasi & Kepatuhan AI Korporat',
            'slug' => 'c1-modul-1-fondasi-kepatuhan-ai',
            'description' => 'Memahami arsitektur Large Language Model (LLM), regulasi UU PDP, dan prompting terstruktur.',
            'sort_order' => 1,
        ]);

        $c1m2 = CourseModule::create([
            'course_id' => $course1->id,
            'title' => 'Modul 2: Implementasi Praktis & Ekstraksi Data',
            'slug' => 'c1-modul-2-implementasi-praktis-ekstraksi-data',
            'description' => 'Praktek analisis dokumen laporan keuangan serta wawancara strategis praktisi data science.',
            'sort_order' => 2,
        ]);

        // Lessons for C1 - Module 1
        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m1->id,
            'title' => 'Pre-Test: Pemahaman Awal AI & Keamanan Data Korporat',
            'slug' => 'pre-test-pemahaman-awal-ai',
            'description' => 'Uji pemahaman dasar Anda sebelum menyimak materi video dan panduan modul ini.',
            'sort_order' => 1,
            'duration_minutes' => 10,
            'points' => 30,
            'content_type' => 'pre_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c1m1_pre_1',
                    'question' => 'Apa prinsip utama keamanan data saat menggunakan Generative AI di korporasi?',
                    'options' => [
                        'Memasukkan semua kata sandi server ke dalam prompt',
                        'Zero Data Retention: tidak menggunakan data rahasia untuk melatih model publik',
                        'Menonaktifkan enkripsi jaringan kantor',
                        'Membagikan akun ke seluruh departemen tanpa audit',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Zero Data Retention memastikan prompt tidak disimpan atau dijadikan bahan training bagi publik.',
                ],
                [
                    'id' => 'c1m1_pre_2',
                    'question' => 'Mengapa prinsip Human-in-the-Loop krusial dalam adopsi AI di bidang perbankan/hukum?',
                    'options' => [
                        'Karena AI tidak membutuhkan listrik',
                        'Keputusan finansial dan legal memiliki risiko tinggi sehingga wajib divalidasi manusia',
                        'Agar kecepatan respons AI berkurang',
                        'Hanya formalitas tanpa dampak hukum',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Validasi manusia mencegah halusinasi AI yang berpotensi melanggar kepatuhan regulasi.',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m1->id,
            'title' => 'Pengenalan Generative AI di Lingkungan Korporat',
            'slug' => 'pengenalan-generative-ai-korporat',
            'description' => 'Memahami arsitektur Large Language Model (LLM) dan batas-batas etika serta privasi data korporasi.',
            'sort_order' => 2,
            'duration_minutes' => 18,
            'points' => 50,
            'content_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=aircAruvnKk', // 3Blue1Brown Neural Networks
            'content_text' => "### Apa itu Generative AI untuk Bisnis?
Generative AI bukan sekadar alat obrolan santai, melainkan akselerator kognitif yang memproses sintesis dokumen tebal, penarikan kesimpulan data angka, hingga penyusunan draf kontrak awal dalam hitungan detik.

#### Poin Kunci Keamanan Data Korporasi:
1. **Zero Data Retention**: Pastikan platform AI internal tidak menggunakan prompt sensitif perusahaan sebagai data latihan publik.
2. **Human-in-the-Loop**: Segala keputusan finansial dan hukum wajib melalui validasi manusia sebelum dieksekusi.
3. **Audit Trail**: Simpan riwayat prompt untuk transparansi kepatuhan regulasi OJK & PDP.",
            'pdf_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'attachments' => [
                [
                    'name' => 'Pedoman_Etika_AI_Korporat.pdf',
                    'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                    'size' => '1.8 MB',
                    'type' => 'pdf',
                ],
                [
                    'name' => 'Prompt_Engineering_Cheatsheet.xlsx',
                    'url' => '#',
                    'size' => '420 KB',
                    'type' => 'excel',
                ],
            ],
            'is_preview' => true,
            'due_date' => Carbon::now()->addDays(14),
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m1->id,
            'title' => 'Mastering Prompt Engineering: Struktur CRAFT',
            'slug' => 'mastering-prompt-engineering-craft',
            'description' => 'Pelajari kerangka Context, Role, Action, Format, dan Target untuk mendapatkan hasil AI yang presisi 99%.',
            'sort_order' => 3,
            'duration_minutes' => 22,
            'points' => 50,
            'content_type' => 'mixed',
            'video_url' => 'https://www.youtube.com/watch?v=jC4v5AS4RIM',
            'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            'content_text' => "### Framework C.R.A.F.T. untuk Profesional

Untuk menghasilkan jawaban AI yang langsung siap pakai tanpa perlu revisi berulang, gunakan kerangka CRAFT:

- **C - Context (Konteks)**: Jelaskan latar belakang perusahaan, industri, target audiens, dan tujuan bisnis.
- **R - Role (Peran)**: Instruksikan AI untuk berperan spesifik, misalnya *'Bertindaklah sebagai Chief Financial Officer bank BUMN'*.
- **A - Action (Aksi)**: Rincikan instruksi teknis langkah demi langkah yang harus dilakukan.
- **F - Format (Bentuk Keluaran)**: Tetapkan apakah berupa tabel perbandingan markdown, poin ringkas, atau memo eksekutif 3 paragraf.
- **T - Tone & Constraints (Batasan Gaya)**: Tentukan nada bicara (formal, diplomatis, persuasif) dan hal yang dilarang disebut.",
            'pdf_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'attachments' => [
                [
                    'name' => 'Template_Framework_CRAFT.docx',
                    'url' => '#',
                    'size' => '650 KB',
                    'type' => 'word',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m1->id,
            'title' => 'Post-Test: Uji Pemahaman Penerapan AI Korporat',
            'slug' => 'post-test-pemahaman-penerapan-ai',
            'description' => 'Jawab soal evaluasi ini untuk memastikan Anda telah menguasai materi modul 1.',
            'sort_order' => 4,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'post_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c1m1_post_1',
                    'question' => 'Apa kepanjangan dari UU PDP yang mengatur privasi data karyawan dan nasabah?',
                    'options' => [
                        'Perlindungan Data Pribadi',
                        'Pengendalian Dokumen Perusahaan',
                        'Protokol Digital Perbankan',
                        'Pusat Data Pemerintahan',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'UU PDP adalah Undang-Undang Perlindungan Data Pribadi yang mengatur sanksi pelanggaran kebocoran data.',
                ],
                [
                    'id' => 'c1m1_post_2',
                    'question' => 'Manakah langkah awal terbaik sebelum mengeksekusi automasi laporan dengan AI?',
                    'options' => [
                        'Langsung kirim hasil draft AI ke Dewan Direksi tanpa dibaca',
                        'Anonimkan data sensitif (PII) sebelum memasukkannya ke sistem AI',
                        'Hapus semua backup dokumen',
                        'Gunakan koneksi Wi-Fi publik tanpa VPN',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Anonimisasi data mencegah paparan informasi pribadi atau rahasia perusahaan ke model AI.',
                ],
            ],
            'is_preview' => false,
        ]);

        // Lessons for C1 - Module 2
        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m2->id,
            'title' => 'Embed PDF: Studi Kasus Analisis Laporan Keuangan dengan AI',
            'slug' => 'studi-kasus-analisis-laporan-keuangan',
            'description' => 'Bedah dokumen PDF laporan keuangan dan temukan anomali serta peluang efisiensi biaya secara kilat.',
            'sort_order' => 1,
            'duration_minutes' => 25,
            'points' => 50,
            'content_type' => 'pdf',
            'pdf_url' => 'https://raw.githubusercontent.com/mozilla/pdf.js/master/web/compressed.tracemonkey-pldi-09.pdf',
            'content_text' => "### Instruksi Praktik Mandiri:
1. Buka dokumen PDF di samping.
2. Gunakan asisten AI internal untuk mengekstrak CAGR 3 tahun terakhir.
3. Buat ringkasan executive memo mengenai rasio likuiditas dan rekomendasi mitigasi risiko pasar modal.",
            'attachments' => [
                [
                    'name' => 'Financial_Sample_Dataset.csv',
                    'url' => '#',
                    'size' => '890 KB',
                    'type' => 'csv',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m2->id,
            'title' => 'Audio Briefing: Wawancara dengan VP Data Science',
            'slug' => 'audio-briefing-vp-data-science',
            'description' => 'Dengarkan rekaman podcast eksklusif bersama praktisi mengenai masa depan tenaga kerja korporasi di era AI.',
            'sort_order' => 2,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'audio',
            'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
            'content_text' => "### Ringkasan Podcast:
Dalam sesi ini, Dr. Budi Santoso membahas 3 mitos terbesar otomasi pekerjaan di Indonesia:
1. AI tidak menggantikan manusia, melainkan manusia yang memakai AI akan mengungguli manusia yang tidak memakainya.
2. Keahlian komunikasi empatik dan negosiasi menjadi 2x lebih berharga seiring komoditisasi coding dan drafting.
3. Karyawan yang proaktif membangun otomatisasi mini di timnya memiliki akselerasi promosi lebih cepat.",
            'attachments' => [],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'course_module_id' => $c1m2->id,
            'title' => 'Post-Test: Ekstraksi Laporan & Tren Masa Depan AI',
            'slug' => 'post-test-ekstraksi-laporan-tren-ai',
            'description' => 'Uji pemahaman akhir modul 2 tentang analisis laporan keuangan dan tren AI.',
            'sort_order' => 3,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'post_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c1m2_post_1',
                    'question' => 'Apa keunggulan utama penggunaan AI dalam audit atau pembacaan laporan keuangan ratusan halaman?',
                    'options' => [
                        'Menghilangkan kewajiban membayar pajak',
                        'Mempercepat deteksi anomali rasio likuiditas dan ringkasan tren pendapatan',
                        'Menghapus jejak audit keuangan',
                        'Memanipulasi angka profit perusahaan',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'AI memampukan pemindaian anomali data numerik dan sintesis dokumen keuangan tebal secara cepat.',
                ],
                [
                    'id' => 'c1m2_post_2',
                    'question' => 'Keahlian non-teknis apa yang paling dicari perusahaan saat adopsi AI semakin meluas?',
                    'options' => [
                        'Menghafal rumus manual',
                        'Komunikasi empatik, problem solving etis, dan kolaborasi strategis',
                        'Menolak penggunaan internet',
                        'Mengerjakan pekerjaan repetitif tanpa bantuan sistem',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Kecerdasan emosional dan penalaran etis manusia tetap menjadi faktor penentu keputusan bisnis strategis.',
                ],
            ],
            'is_preview' => false,
        ]);

        // Course 2: Strategic Decision Making in High Uncertainty
        $c2m1 = CourseModule::create([
            'course_id' => $course2->id,
            'title' => 'Modul 1: Kerangka Pengambilan Keputusan Strategis',
            'slug' => 'c2-modul-1-kerangka-keputusan-strategis',
            'description' => 'Matriks resiko vs dampak dan penyusunan scenario planning skenario darurat.',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'course_module_id' => $c2m1->id,
            'title' => 'Pre-Test: Refleksi Pengambilan Keputusan Bisnis',
            'slug' => 'c2-pre-test-keputusan-bisnis',
            'description' => 'Identifikasi bias kognitif dan naluri kepemimpinan sebelum memulai modul.',
            'sort_order' => 1,
            'duration_minutes' => 10,
            'points' => 30,
            'content_type' => 'pre_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c2_pre_1',
                    'question' => 'Apa risiko terbesar dari "Analysis Paralysis" bagi eksekutif perusahaan?',
                    'options' => [
                        'Kehilangan momentum pasar karena terlalu lama mengumpulkan data tanpa aksi nyata',
                        'Membuat terlalu banyak produk baru',
                        'Menambah anggaran pemasaran',
                        'Menurunkan biaya produksi',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'Analysis Paralysis menahan inisiatif strategis karena ketakutan berlebihan terhadap ketidakpastian.',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'course_module_id' => $c2m1->id,
            'title' => 'Analisis Matriks Resiko vs Dampak Strategis',
            'slug' => 'analisis-matriks-resiko-dampak',
            'description' => 'Menyaring 100 ide inisiatif menjadi 3 prioritas utama yang memberi leverage terbesar.',
            'sort_order' => 2,
            'duration_minutes' => 20,
            'points' => 50,
            'content_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=0h3j2L4l37M',
            'content_text' => "### Prinsip Prioritisasi Berbasis Dampak:
Pelajari metode Eisen-Matrix dan cara menyelaraskan keputusan dengan OKR tahunan korporasi.",
            'pdf_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'is_preview' => true,
            'due_date' => Carbon::now()->addDays(14),
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'course_module_id' => $c2m1->id,
            'title' => 'Scenario Planning: Menyiapkan Rencana B dan C',
            'slug' => 'scenario-planning-rencana-kontingensi',
            'description' => 'Merancang respon cepat saat terjadi krisis likuiditas atau perubahan kebijakan regulator.',
            'sort_order' => 3,
            'duration_minutes' => 30,
            'points' => 50,
            'content_type' => 'text',
            'content_text' => "### Langkah Membangun Skenario Kontingensi:
1. Identifikasi faktor ketidakpastian kritis (Critical Uncertainties).
2. Bangun 4 kuadran kemungkinan masa depan (Paling optimis, pesimis, regulasi ketat, adopsi masif).
3. Buat 'Trigger Indicator' yang memberi peringatan dini sebelum skenario terjadi.",
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'course_module_id' => $c2m1->id,
            'title' => 'Post-Test: Pengujian Scenario Planning & Matriks Keputusan',
            'slug' => 'c2-post-test-keputusan',
            'description' => 'Uji pemahaman akhir mengenai matriks resiko dan rencana mitigasi krisis.',
            'sort_order' => 4,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'post_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c2_post_1',
                    'question' => 'Apa fungsi utama menentukan "Trigger Indicator" dalam scenario planning?',
                    'options' => [
                        'Menghukum anggota tim yang gagal mencapai target',
                        'Menjadi sinyal objektif kapan rencana cadangan harus segera diaktifkan',
                        'Mempercepat penutupan kantor cabang',
                        'Menghapus meeting mingguan',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Trigger indicator memberikan batas kuantitatif bagi manajemen untuk mengaktifkan rencana kontingensi.',
                ],
            ],
            'is_preview' => false,
        ]);

        // Course 3: Scrum & Agile Framework Implementation
        $c3m1 = CourseModule::create([
            'course_id' => $course3->id,
            'title' => 'Modul 1: Pelaksanaan Sprint & Retrospektif',
            'slug' => 'c3-modul-1-pelaksanaan-sprint',
            'description' => 'Alur siklus sprint dua mingguan dan evaluasi berkala untuk performa tim.',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'course_module_id' => $c3m1->id,
            'title' => 'Pre-Test: Mindset Agile vs Waterfall',
            'slug' => 'c3-pre-test-agile',
            'description' => 'Uji wawasan awal tentang perbedaan iterasi agile dengan metodologi konvensional.',
            'sort_order' => 1,
            'duration_minutes' => 10,
            'points' => 30,
            'content_type' => 'pre_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c3_pre_1',
                    'question' => 'Apa nilai utama dari manifesto Agile terkait perubahan kebutuhan bisnis?',
                    'options' => [
                        'Menolak perubahan bila dokumen spesifikasi sudah ditandatangani',
                        'Merespons perubahan lebih diutamakan daripada sekadar mengikuti rencana kaku',
                        'Membebankan denda kepada stakeholder yang mengubah ide',
                        'Menghentikan seluruh proyek',
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Agile mengutamakan fleksibilitas merespons perubahan demi memberikan nilai nyata bagi pengguna.',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'course_module_id' => $c3m1->id,
            'title' => 'Anatomi Sprint: Dari Backlog Menuju Demo',
            'slug' => 'anatomi-sprint-backlog-menuju-demo',
            'description' => 'Membedah siklus dua mingguan: Planning, Daily Standup, Review, dan Retrospective.',
            'sort_order' => 2,
            'duration_minutes' => 24,
            'points' => 50,
            'content_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=XU0llRltyFM',
            'content_text' => "Memahami peran Product Owner, Scrum Master, dan Development Team secara presisi.",
            'is_preview' => true,
            'due_date' => Carbon::now()->addDays(14),
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'course_module_id' => $c3m1->id,
            'title' => 'Simulasi Retrospektif: Menemukan Hambatan Tanpa Saling Menyalahkan',
            'slug' => 'simulasi-retrospektif-tim',
            'description' => 'Gunakan teknik Mad-Sad-Glad dan Sailboat untuk perbaikan berkelanjutan tim Anda.',
            'sort_order' => 3,
            'duration_minutes' => 18,
            'points' => 50,
            'content_type' => 'text',
            'content_text' => "Psikologi keselamatan (psychological safety) adalah fondasi keberhasilan retrospective tim.",
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'course_module_id' => $c3m1->id,
            'title' => 'Post-Test: Pemahaman Scrum & Delivery Value',
            'slug' => 'c3-post-test-scrum',
            'description' => 'Evaluasi penguasaan peran Scrum Master, Product Owner, dan ritme sprint.',
            'sort_order' => 4,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'post_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c3_post_1',
                    'question' => 'Siapa yang bertanggung jawab memprioritaskan item backlog berdasarkan nilai bisnis?',
                    'options' => [
                        'Product Owner',
                        'Scrum Master',
                        'Quality Assurance',
                        'Staff HRD',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'Product Owner memegang visi produk dan prioritas backlog sesuai feedback pengguna dan target bisnis.',
                ],
            ],
            'is_preview' => false,
        ]);

        // Course 4: High-Impact Executive Presentation
        $c4m1 = CourseModule::create([
            'course_id' => $course4->id,
            'title' => 'Modul 1: Struktur & Delivery Komunikasi Eksekutif',
            'slug' => 'c4-modul-1-komunikasi-eksekutif',
            'description' => 'Menyusun argumen berbasis piramida Minto dan visualisasi data yang berdampak.',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course4->id,
            'course_module_id' => $c4m1->id,
            'title' => 'Pre-Test: Kebiasaan Presentasi Bisnis',
            'slug' => 'c4-pre-test-presentasi',
            'description' => 'Evaluasi kebiasaan menyusun slide dan struktur pembuka presentasi Anda.',
            'sort_order' => 1,
            'duration_minutes' => 10,
            'points' => 30,
            'content_type' => 'pre_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c4_pre_1',
                    'question' => 'Di mana kesimpulan utama harus diletakkan saat mempresentasikan usulan bisnis ke Direksi?',
                    'options' => [
                        'Di awal presentasi (Bottom-line First)',
                        'Di slide lampiran paling belakang',
                        'Hanya jika ditanya oleh audiens',
                        'Di lembar kuesioner setelah meeting bubar',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'Pendekatan Bottom-line First memastikan pesan kunci tersampaikan sebelum perhatian eksekutif terbagi.',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course4->id,
            'course_module_id' => $c4m1->id,
            'title' => 'Piramida Minto: Komunikasi "Bottom-Line First" untuk C-Level',
            'slug' => 'piramida-minto-bottom-line-first',
            'description' => 'Sampaikan kesimpulan di detik ke-10 untuk memikat perhatian direktur yang super sibuk.',
            'sort_order' => 2,
            'duration_minutes' => 20,
            'points' => 50,
            'content_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=k48k1q6d_gU',
            'content_text' => "Jangan mulai presentasi dengan metodologi bertele-tele. Langsung beritahukan rekomendasi tindakan!",
            'is_preview' => true,
            'due_date' => Carbon::now()->addDays(14),
        ]);

        Lesson::create([
            'course_id' => $course4->id,
            'course_module_id' => $c4m1->id,
            'title' => 'Post-Test: Penguasaan Storytelling Eksekutif',
            'slug' => 'c4-post-test-storytelling',
            'description' => 'Uji pemahaman tentang struktur presentasi piramida Minto.',
            'sort_order' => 3,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'post_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c4_post_1',
                    'question' => 'Prinsip MECE dalam Piramida Minto merupakan singkatan dari apa?',
                    'options' => [
                        'Mutually Exclusive, Collectively Exhaustive',
                        'More Energy, Clear Explanation',
                        'Maximum Efficiency, Constant Effort',
                        'Modern Enterprise, Corporate Ethics',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'MECE memastikan pengelompokan argumen tidak tumpang tindih dan mencakup seluruh aspek penting.',
                ],
            ],
            'is_preview' => false,
        ]);

        // Course 5: Enterprise Cloud & Data Security Fundamentals
        $c5m1 = CourseModule::create([
            'course_id' => $course5->id,
            'title' => 'Modul 1: Prinsip Zero Trust & Kepatuhan Keamanan Data',
            'slug' => 'c5-modul-1-zero-trust-keamanan-data',
            'description' => 'Fondasi keamanan enterprise, perlindungan kredensial, dan UU Perlindungan Data Pribadi.',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course5->id,
            'course_module_id' => $c5m1->id,
            'title' => 'Pre-Test: Kesadaran Keamanan Informasi (Cyber Awareness)',
            'slug' => 'c5-pre-test-keamanan-data',
            'description' => 'Cek pemahaman Anda mengenai ancaman phishing, kebocoran data, dan keamanan sandi.',
            'sort_order' => 1,
            'duration_minutes' => 10,
            'points' => 30,
            'content_type' => 'pre_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c5_pre_1',
                    'question' => 'Apa mantra utama dari arsitektur Zero Trust?',
                    'options' => [
                        'Never trust, always verify (Jangan pernah percaya, selalu verifikasi)',
                        'Percayai semua perangkat di dalam jaringan kantor',
                        'Nonaktifkan autentikasi ganda agar login lebih cepat',
                        'Gunakan kata sandi yang sama di semua server',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'Zero Trust berasumsi bahwa ancaman bisa datang dari dalam maupun luar jaringan internal.',
                ],
            ],
            'is_preview' => false,
        ]);

        Lesson::create([
            'course_id' => $course5->id,
            'course_module_id' => $c5m1->id,
            'title' => 'Prinsip Zero Trust & Kepatuhan UU Perlindungan Data Pribadi',
            'slug' => 'prinsip-zero-trust-uu-pdp',
            'description' => 'Jangan pernah percaya, selalu verifikasi. Lindungi data nasabah dan kredensial korporasi.',
            'sort_order' => 2,
            'duration_minutes' => 25,
            'points' => 50,
            'content_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=1x8_wT7k8U8',
            'is_preview' => true,
            'due_date' => Carbon::now()->addDays(14),
        ]);

        Lesson::create([
            'course_id' => $course5->id,
            'course_module_id' => $c5m1->id,
            'title' => 'Post-Test: Sertifikasi Kepatuhan Data Keamanan',
            'slug' => 'c5-post-test-kepatuhan-data',
            'description' => 'Uji kompetensi keamanan data siber enterprise dan mitigasi insiden.',
            'sort_order' => 3,
            'duration_minutes' => 15,
            'points' => 50,
            'content_type' => 'post_test',
            'passing_score' => 70,
            'quiz_questions' => [
                [
                    'id' => 'c5_post_1',
                    'question' => 'Sanksi apa yang dapat dikenakan kepada korporasi jika lalai melindungi data pribadi nasabah menurut UU PDP?',
                    'options' => [
                        'Denda administratif miliaran rupiah hingga sanksi pidana bagi penanggung jawab data',
                        'Tidak ada sanksi apapun',
                        'Hanya teguran lisan santai',
                        'Mendapatkan insentif pajak',
                    ],
                    'correct_answer' => 0,
                    'explanation' => 'UU PDP menetapkan denda berat dan tanggung jawab pidana atas kebocoran data yang diakibatkan kelalaian.',
                ],
            ],
            'is_preview' => false,
        ]);

        // 6. Users: Admins and Employees
        $admin = User::create([
            'name' => 'Alexander Pratama',
            'email' => 'admin@corporate.lms',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'company_id' => $companyTelkom->id,
            'position' => 'Head of Corporate Learning & Talent Development',
            'department' => 'Human Capital',
            'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=160&auto=format&fit=crop&q=80',
            'phone' => '+62 811 1122 3344',
            'email_verified_at' => Carbon::now(),
            'competency_scores' => [
                'Kepemimpinan' => 95,
                'Komunikasi' => 92,
                'Pemecahan Masalah' => 90,
                'Keahlian Teknis' => 88,
                'Manajemen Proyek' => 94,
                'Literasi Data & AI' => 90,
            ],
        ]);

        $edujaAdmin = User::create([
            'name' => 'Eduja System',
            'email' => 'eduja.systems@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'company_id' => $companyTelkom->id,
            'position' => 'Super Administrator LMS',
            'department' => 'Executive Office',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=160&auto=format&fit=crop&q=80',
            'phone' => '+62 811 0000 1234',
            'email_verified_at' => Carbon::now(),
            'competency_scores' => [
                'Kepemimpinan' => 95,
                'Komunikasi' => 92,
                'Pemecahan Masalah' => 90,
                'Keahlian Teknis' => 88,
                'Manajemen Proyek' => 94,
                'Literasi Data & AI' => 90,
            ],
        ]);

        $employeeUser = User::create([
            'name' => 'John Hendrawan',
            'email' => 'employee@corporate.lms',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'company_id' => $companyTelkom->id,
            'position' => 'Senior Corporate Digital Strategist',
            'department' => 'Digital Enterprise & Strategy',
            'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=160&auto=format&fit=crop&q=80',
            'phone' => '+62 812 8888 9999',
            'email_verified_at' => Carbon::now(),
            'competency_scores' => [
                'Kepemimpinan' => 82,
                'Komunikasi' => 88,
                'Pemecahan Masalah' => 92,
                'Keahlian Teknis' => 85,
                'Manajemen Proyek' => 78,
                'Literasi Data & AI' => 86,
            ],
        ]);

        $employee2 = User::create([
            'name' => 'Anita Permatasari',
            'email' => 'anita.permatasari@bank.co.id',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'company_id' => $companyBank->id,
            'position' => 'Risk & Compliance Analyst',
            'department' => 'Governance & Risk',
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=160&auto=format&fit=crop&q=80',
            'phone' => '+62 813 1234 5678',
            'email_verified_at' => Carbon::now(),
            'competency_scores' => [
                'Kepemimpinan' => 75,
                'Komunikasi' => 80,
                'Pemecahan Masalah' => 86,
                'Keahlian Teknis' => 78,
                'Manajemen Proyek' => 82,
                'Literasi Data & AI' => 74,
            ],
        ]);

        $employee3 = User::create([
            'name' => 'Dimas Wicaksono',
            'email' => 'dimas.wicaksono@astralogistik.co.id',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'company_id' => $companyAstra->id,
            'position' => 'Logistics Operations Lead',
            'department' => 'Supply Chain',
            'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=160&auto=format&fit=crop&q=80',
            'phone' => '+62 815 9900 1122',
            'email_verified_at' => Carbon::now(),
            'competency_scores' => [
                'Kepemimpinan' => 80,
                'Komunikasi' => 76,
                'Pemecahan Masalah' => 84,
                'Keahlian Teknis' => 80,
                'Manajemen Proyek' => 90,
                'Literasi Data & AI' => 70,
            ],
        ]);

        // 7. Assign Employee to Class Groups (Requirement: group assignment grants access to all classes in that group)
        // Assign John to Digital Transformation Track & Corporate Leadership Track
        $employeeUser->classGroups()->attach([
            $groupTech->id => ['assigned_by' => $admin->id, 'assigned_at' => Carbon::now()->subDays(14)],
            $groupLeadership->id => ['assigned_by' => $admin->id, 'assigned_at' => Carbon::now()->subDays(10)],
        ]);

        $employee2->classGroups()->attach([
            $groupSoftskills->id => ['assigned_by' => $admin->id, 'assigned_at' => Carbon::now()->subDays(5)],
        ]);

        // 8. Direct Course Enrollments & Progress for John Hendrawan (Employee)
        // 5 recent courses to showcase the "My Summary" requirement:
        // Course 1: Generative AI (75% completed)
        DB::table('course_user')->insert([
            'course_id' => $course1->id,
            'user_id' => $employeeUser->id,
            'assigned_by' => $admin->id,
            'enrollment_source' => 'group',
            'status' => 'in_progress',
            'progress_percent' => 75,
            'last_accessed_at' => Carbon::now()->subHours(2),
            'completed_at' => null,
            'created_at' => Carbon::now()->subDays(12),
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        // Mark 3 lessons completed for Course 1
        $c1Lessons = $course1->lessons;
        if (isset($c1Lessons[0])) {
            DB::table('lesson_user')->insert([
                'user_id' => $employeeUser->id,
                'lesson_id' => $c1Lessons[0]->id,
                'course_id' => $course1->id,
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(5),
                'last_watched_seconds' => 1080,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ]);
        }
        if (isset($c1Lessons[1])) {
            DB::table('lesson_user')->insert([
                'user_id' => $employeeUser->id,
                'lesson_id' => $c1Lessons[1]->id,
                'course_id' => $course1->id,
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(3),
                'last_watched_seconds' => 1320,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ]);
        }
        if (isset($c1Lessons[2])) {
            DB::table('lesson_user')->insert([
                'user_id' => $employeeUser->id,
                'lesson_id' => $c1Lessons[2]->id,
                'course_id' => $course1->id,
                'is_completed' => true,
                'completed_at' => Carbon::now()->subHours(2),
                'last_watched_seconds' => 1500,
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ]);
        }

        // Course 2: Strategic Decision Making (50% completed)
        DB::table('course_user')->insert([
            'course_id' => $course2->id,
            'user_id' => $employeeUser->id,
            'assigned_by' => $admin->id,
            'enrollment_source' => 'group',
            'status' => 'in_progress',
            'progress_percent' => 50,
            'last_accessed_at' => Carbon::now()->subDays(1),
            'completed_at' => null,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        $c2Lessons = $course2->lessons;
        if (isset($c2Lessons[0])) {
            DB::table('lesson_user')->insert([
                'user_id' => $employeeUser->id,
                'lesson_id' => $c2Lessons[0]->id,
                'course_id' => $course2->id,
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(1),
                'last_watched_seconds' => 1200,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ]);
        }

        // Course 3: Scrum & Agile (Direct assignment, 100% completed)
        DB::table('course_user')->insert([
            'course_id' => $course3->id,
            'user_id' => $employeeUser->id,
            'assigned_by' => $admin->id,
            'enrollment_source' => 'direct',
            'status' => 'completed',
            'progress_percent' => 100,
            'last_accessed_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subDays(2),
            'created_at' => Carbon::now()->subDays(18),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $c3Lessons = $course3->lessons;
        foreach ($c3Lessons as $les) {
            DB::table('lesson_user')->insert([
                'user_id' => $employeeUser->id,
                'lesson_id' => $les->id,
                'course_id' => $course3->id,
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(2),
                'last_watched_seconds' => $les->duration_minutes * 60,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ]);
        }

        // Course 4: Executive Presentation (Direct assignment, 30% progress)
        DB::table('course_user')->insert([
            'course_id' => $course4->id,
            'user_id' => $employeeUser->id,
            'assigned_by' => $admin->id,
            'enrollment_source' => 'direct',
            'status' => 'in_progress',
            'progress_percent' => 30,
            'last_accessed_at' => Carbon::now()->subDays(4),
            'completed_at' => null,
            'created_at' => Carbon::now()->subDays(6),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        // Course 5: Cloud & Data Security (from Tech group, 10% progress)
        DB::table('course_user')->insert([
            'course_id' => $course5->id,
            'user_id' => $employeeUser->id,
            'assigned_by' => $admin->id,
            'enrollment_source' => 'group',
            'status' => 'in_progress',
            'progress_percent' => 10,
            'last_accessed_at' => Carbon::now()->subDays(5),
            'completed_at' => null,
            'created_at' => Carbon::now()->subDays(8),
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        // 9. Course Invitations
        CourseInvitation::create([
            'course_id' => $course1->id,
            'invited_by' => $admin->id,
            'user_id' => $employee2->id,
            'email' => $employee2->email,
            'token' => 'inv_' . bin2hex(random_bytes(16)),
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }
}
