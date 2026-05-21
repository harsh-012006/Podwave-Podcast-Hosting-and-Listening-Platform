<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Like;
use App\Models\ListeningHistory;
use App\Models\Podcast;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic podcast platform data.
     */
    public function run(): void
    {
        // -------------------------------------------------------
        // 1. CATEGORIES
        // -------------------------------------------------------
        $categories = [
            ['name' => 'Technology',       'icon' => 'bi-cpu-fill',        'color' => '#6366F1'],
            ['name' => 'Comedy',           'icon' => 'bi-emoji-laughing',   'color' => '#F59E0B'],
            ['name' => 'True Crime',       'icon' => 'bi-shield-exclamation','color' => '#EF4444'],
            ['name' => 'Business',         'icon' => 'bi-briefcase-fill',   'color' => '#10B981'],
            ['name' => 'Science',          'icon' => 'bi-flask-fill',       'color' => '#3B82F6'],
            ['name' => 'History',          'icon' => 'bi-book-fill',        'color' => '#D97706'],
            ['name' => 'Health & Fitness', 'icon' => 'bi-heart-pulse-fill', 'color' => '#EC4899'],
            ['name' => 'Music',            'icon' => 'bi-music-note-beamed','color' => '#8B5CF6'],
            ['name' => 'News & Politics',  'icon' => 'bi-newspaper',        'color' => '#64748B'],
            ['name' => 'Education',        'icon' => 'bi-mortarboard-fill', 'color' => '#0EA5E9'],
            ['name' => 'Society & Culture','icon' => 'bi-people-fill',      'color' => '#F97316'],
            ['name' => 'Sports',           'icon' => 'bi-trophy-fill',      'color' => '#22C55E'],
        ];

        foreach ($categories as $i => $cat) {
            Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'icon'       => $cat['icon'],
                'color'      => $cat['color'],
                'is_active'  => true,
                'sort_order' => $i,
            ]);
        }

        // -------------------------------------------------------
        // 2. GENRES
        // -------------------------------------------------------
        $genreMap = [
            'Technology'       => ['AI & Machine Learning', 'Cybersecurity', 'Software Development', 'Gadgets & Gear', 'Startups'],
            'Comedy'           => ['Stand-Up', 'Improv', 'Sketch Comedy', 'Interview Comedy', 'Dark Humor'],
            'True Crime'       => ['Murder Mysteries', 'Cold Cases', 'White Collar Crime', 'Cults', 'Investigations'],
            'Business'         => ['Entrepreneurship', 'Marketing', 'Finance', 'Leadership', 'Investing'],
            'Science'          => ['Space & Astronomy', 'Biology', 'Physics', 'Climate', 'Medicine'],
            'History'          => ['Ancient History', 'World War', 'American History', 'Biographies', 'Archaeology'],
            'Health & Fitness' => ['Mental Health', 'Nutrition', 'Yoga', 'Running', 'Wellness'],
            'Music'            => ['Hip-Hop', 'Rock', 'Classical', 'Jazz', 'Pop'],
            'News & Politics'  => ['World News', 'US Politics', 'Local News', 'Analysis', 'Debate'],
            'Education'        => ['Language Learning', 'Philosophy', 'Mathematics', 'Literature', 'Self-Help'],
            'Society & Culture'=> ['Relationships', 'Travel', 'Food', 'Fashion', 'Pop Culture'],
            'Sports'           => ['Football', 'Basketball', 'Tennis', 'Combat Sports', 'Fantasy Sports'],
        ];

        foreach ($genreMap as $catName => $genres) {
            $category = Category::where('name', $catName)->first();
            foreach ($genres as $genre) {
                Genre::create([
                    'name'        => $genre,
                    'slug'        => Str::slug($genre),
                    'category_id' => $category->id,
                    'is_active'   => true,
                ]);
            }
        }

        // -------------------------------------------------------
        // 3. USERS
        // -------------------------------------------------------

        // Admin
        $admin = User::create([
            'name'              => 'PodWave Admin',
            'username'          => 'admin',
            'email'             => 'admin@podwave.fm',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
            'bio'               => 'Platform administrator for PodWave.',
        ]);

        // Creators
        $creatorData = [
            ['name' => 'Alex Rivera',    'email' => 'creator@podwave.fm',       'bio' => 'Tech enthusiast and AI researcher. Bringing you the latest from Silicon Valley.'],
            ['name' => 'Maya Thompson',  'email' => 'maya@podwave.fm',           'bio' => 'True crime investigator and storyteller. 10 years in criminal justice.'],
            ['name' => 'Jordan Lee',     'email' => 'jordan@podwave.fm',         'bio' => 'Stand-up comedian and podcast host. Making the world laugh one episode at a time.'],
            ['name' => 'Dr. Sarah Chen', 'email' => 'sarah@podwave.fm',          'bio' => 'Medical doctor and health advocate. Science-backed wellness advice.'],
            ['name' => 'Marcus Williams','email' => 'marcus@podwave.fm',         'bio' => 'Serial entrepreneur and angel investor. Built 3 companies from zero to exit.'],
            ['name' => 'Elena Sousa',    'email' => 'elena@podwave.fm',          'bio' => 'History professor at Stanford. Bringing the past to life.'],
        ];

        $creators = [];
        foreach ($creatorData as $c) {
            $creators[] = User::create([
                'name'              => $c['name'],
                'username'          => Str::slug($c['name']) . '-' . Str::random(3),
                'email'             => $c['email'],
                'password'          => Hash::make('password'),
                'role'              => 'creator',
                'email_verified_at' => now(),
                'bio'               => $c['bio'],
            ]);
        }

        // Listener
        $listener = User::create([
            'name'              => 'Sam Listener',
            'username'          => 'samlistener',
            'email'             => 'listener@podwave.fm',
            'password'          => Hash::make('password'),
            'role'              => 'listener',
            'email_verified_at' => now(),
            'bio'               => 'Podcast addict. Always listening.',
        ]);

        // Extra listeners
        $listenerNames = ['Chris Park', 'Taylor Moon', 'Jamie Walsh', 'Robin Singh', 'Casey Brown'];
        $extraListeners = [];
        foreach ($listenerNames as $i => $name) {
            $extraListeners[] = User::create([
                'name'              => $name,
                'username'          => Str::slug($name) . $i,
                'email'             => Str::slug($name) . '@example.com',
                'password'          => Hash::make('password'),
                'role'              => 'listener',
                'email_verified_at' => now(),
            ]);
        }

        $allListeners = array_merge([$listener], $extraListeners);

        // -------------------------------------------------------
        // 4. PODCASTS
        // -------------------------------------------------------
        $podcastData = [
            [
                'creator' => $creators[0],
                'title'   => 'Silicon Minds',
                'desc'    => 'Deep dives into artificial intelligence, machine learning, and the future of technology. Weekly conversations with industry leaders building tomorrow\'s world.',
                'cat'     => 'Technology',
                'genre'   => 'AI & Machine Learning',
                'tags'    => ['AI', 'Machine Learning', 'Tech', 'Innovation', 'Startups'],
                'plays'   => 142500,
                'featured'=> true,
                'thumbnail' => 'silicon-mind.jpg',
            ],
            [
                'creator' => $creators[1],
                'title'   => 'Dark Files',
                'desc'    => 'Real stories of crime, mystery, and justice. From cold cases to courtroom dramas, we leave no stone unturned.',
                'cat'     => 'True Crime',
                'genre'   => 'Cold Cases',
                'tags'    => ['Crime', 'Mystery', 'Justice', 'Investigation'],
                'plays'   => 289000,
                'featured'=> true,
                'thumbnail' => 'dark-crime.jfif',
            ],
            [
                'creator' => $creators[2],
                'title'   => 'The Laugh Lab',
                'desc'    => 'Comedy, chaos, and conversations. Jordan brings on the funniest voices in comedy for an hour of pure entertainment.',
                'cat'     => 'Comedy',
                'genre'   => 'Stand-Up',
                'tags'    => ['Comedy', 'Humor', 'Entertainment', 'Talk'],
                'plays'   => 98700,
                'featured'=> false,
                'thumbnail' => 'laugh-lab.jpg',
            ],
            [
                'creator' => $creators[3],
                'title'   => 'Vital Signs',
                'desc'    => 'Evidence-based health and wellness. Dr. Sarah Chen decodes medical research so you can live your healthiest life.',
                'cat'     => 'Health & Fitness',
                'genre'   => 'Mental Health',
                'tags'    => ['Health', 'Wellness', 'Medicine', 'Mental Health', 'Fitness'],
                'plays'   => 175000,
                'featured'=> true,
                'thumbnail' => 'vital-signs.png',
            ],
            [
                'creator' => $creators[4],
                'title'   => 'Founder\'s Forge',
                'desc'    => 'Raw, unfiltered entrepreneurship stories. From first check to IPO — the real struggles and wins of building a company.',
                'cat'     => 'Business',
                'genre'   => 'Entrepreneurship',
                'tags'    => ['Business', 'Startup', 'Entrepreneur', 'VC', 'Scale'],
                'plays'   => 210000,
                'featured'=> true,
                'thumbnail' => 'founders-forge.jfif',
            ],
            [
                'creator' => $creators[5],
                'title'   => 'Through the Ages',
                'desc'    => 'History is stranger than fiction. Professor Elena Sousa brings ancient empires, forgotten wars, and fascinating figures back to life.',
                'cat'     => 'History',
                'genre'   => 'Ancient History',
                'tags'    => ['History', 'Ancient', 'Culture', 'Civilizations', 'Education'],
                'plays'   => 87400,
                'featured'=> false,
                'thumbnail' => 'through-the-ages.jfif',
            ],
            [
                'creator' => $creators[0],
                'title'   => 'Code & Coffee',
                'desc'    => 'Software development tips, career advice, and industry news for working developers. No fluff, just code.',
                'cat'     => 'Technology',
                'genre'   => 'Software Development',
                'tags'    => ['Coding', 'Developer', 'Programming', 'Career', 'Tech'],
                'plays'   => 65000,
                'featured'=> false,
                'thumbnail' => 'code-and-coffee.png',
            ],
            [
                'creator' => $creators[4],
                'title'   => 'Money Moves',
                'desc'    => 'Smart investing, personal finance, and building generational wealth. Practical advice for every stage of your financial journey.',
                'cat'     => 'Business',
                'genre'   => 'Investing',
                'tags'    => ['Finance', 'Investing', 'Money', 'Wealth', 'Stocks'],
                'plays'   => 134000,
                'featured'=> false,
                'thumbnail' => 'money-moves.png',
            ],
        ];

        $podcasts = [];
        foreach ($podcastData as $pd) {
            $category = Category::where('name', $pd['cat'])->first();
            $genre    = Genre::where('name', $pd['genre'])->first();

            $podcasts[] = Podcast::create([
                'user_id'     => $pd['creator']->id,
                'category_id' => $category->id,
                'genre_id'    => $genre->id,
                'title'       => $pd['title'],
                'slug'        => Str::slug($pd['title']) . '-' . Str::random(4),
                'description' => $pd['desc'],
                'thumbnail'   => $pd['thumbnail'] ?? null,
                'tags'        => $pd['tags'],
                'status'      => 'published',
                'is_featured' => $pd['featured'],
                'total_plays' => $pd['plays'],
                'language'    => 'English',
            ]);
        }

        // -------------------------------------------------------
        // 5. EPISODES
        // -------------------------------------------------------
        $episodeSets = [
            // Silicon Minds
            [
                'podcast' => $podcasts[0],
                'episodes' => [
                    ['title' => 'GPT-5 and the AGI Race', 'desc' => 'We break down the latest from OpenAI and what GPT-5 means for the future of AI. Are we closer to AGI than we think?', 'duration' => 3240],
                    ['title' => 'Inside Google DeepMind', 'desc' => 'An exclusive look at how DeepMind is solving protein folding, climate change, and more with reinforcement learning.', 'duration' => 2880],
                    ['title' => 'The Open Source AI Revolution', 'desc' => 'How Meta\'s Llama and open models are democratizing AI development worldwide.', 'duration' => 2640],
                    ['title' => 'AI Regulation: Europe vs America', 'desc' => 'A deep dive into the EU AI Act, Biden\'s executive orders, and what it means for companies building with AI.', 'duration' => 3000],
                    ['title' => 'Robotics & Embodied AI', 'desc' => 'Figure AI, Boston Dynamics, and Tesla Optimus — the race to build the first truly useful humanoid robot.', 'duration' => 3480],
                ],
            ],
            // Dark Files
            [
                'podcast' => $podcasts[1],
                'episodes' => [
                    ['title' => 'The Golden State Killer: 40 Years Later', 'desc' => 'How DNA genealogy finally solved the case that haunted California for decades. Inside the investigation.', 'duration' => 4200],
                    ['title' => 'The Disappearance of Maura Murray', 'desc' => 'On February 9, 2004, Maura Murray walked off into the New Hampshire woods and was never seen again.', 'duration' => 3900],
                    ['title' => 'JonBenét Ramsey: New Evidence', 'desc' => 'We examine newly released documents and what DNA technology might finally reveal about this infamous case.', 'duration' => 3600],
                    ['title' => 'The Zodiac Killer Decoded?', 'desc' => 'A team of cryptographers claims to have cracked the Zodiac\'s 340 cipher. We dig into what they found.', 'duration' => 3300],
                    ['title' => 'Cult of Personality: NXIVM Exposed', 'desc' => 'How a self-help organization became a sex cult, and how Keith Raniere was finally brought to justice.', 'duration' => 4500],
                    ['title' => 'The Black Dahlia: Unsolved', 'desc' => 'Over 75 years later, Elizabeth Short\'s murder remains unsolved. We review every major theory.', 'duration' => 3720],
                ],
            ],
            // The Laugh Lab
            [
                'podcast' => $podcasts[2],
                'episodes' => [
                    ['title' => 'ft. Dave Chappelle (Kind Of)', 'desc' => 'Jordan does an absolutely unhinged impression of every major comedian for 45 minutes. Featuring a surprise call-in.', 'duration' => 2700],
                    ['title' => 'Worst Dates Ever', 'desc' => 'Jordan and his panel share dating horror stories that will make you feel infinitely better about your own love life.', 'duration' => 2400],
                    ['title' => 'Airline Chaos Chronicles', 'desc' => 'Everything that can go wrong on a flight, told through the funniest lens possible. Based on real events.', 'duration' => 2100],
                    ['title' => 'Parents vs. The Internet', 'desc' => 'Reading our parents\' texts out loud. Thirty minutes of pure comedic gold from the greatest unintentional comedians.', 'duration' => 1800],
                ],
            ],
            // Vital Signs
            [
                'podcast' => $podcasts[3],
                'episodes' => [
                    ['title' => 'The Truth About Ozempic', 'desc' => 'Dr. Chen breaks down the science, benefits, and real risks of GLP-1 drugs. What the media gets wrong.', 'duration' => 3120],
                    ['title' => 'Sleep: Your Most Underrated Superpower', 'desc' => 'How 7–9 hours of quality sleep transforms every system in your body. Practical strategies to optimize rest.', 'duration' => 2760],
                    ['title' => 'The Gut-Brain Axis', 'desc' => 'New research on how your microbiome controls your mood, anxiety, and cognitive performance.', 'duration' => 3000],
                    ['title' => 'Stress Hormones & Your Health', 'desc' => 'Chronic cortisol elevation is quietly destroying your health. Here\'s how to fix your stress response.', 'duration' => 2880],
                    ['title' => 'Longevity Secrets from Blue Zones', 'desc' => 'What centenarians in Sardinia, Okinawa, and Costa Rica have in common — and how to apply it to your life.', 'duration' => 3360],
                ],
            ],
            // Founder's Forge
            [
                'podcast' => $podcasts[4],
                'episodes' => [
                    ['title' => 'From Zero to $100M ARR', 'desc' => 'Marcus interviews the founder of a bootstrapped SaaS that hit $100M ARR without ever raising VC money.', 'duration' => 3600],
                    ['title' => 'The Hardest Pivot I Ever Made', 'desc' => 'Three founders share the moment they had to kill their original idea and completely reinvent their companies.', 'duration' => 3240],
                    ['title' => 'Raising Your First Round', 'desc' => 'Everything you need to know about seed funding: deck, terms, VCs to approach, and red flags to avoid.', 'duration' => 3000],
                    ['title' => 'Hiring Your First 10 Employees', 'desc' => 'The people decisions that make or break startups. Who to hire first and how to build a culture that scales.', 'duration' => 2880],
                    ['title' => 'Surviving a Down Round', 'desc' => 'When your valuation gets cut in half. Three founders get brutally honest about navigating the hardest moment.', 'duration' => 3120],
                    ['title' => 'Exit Strategy: IPO vs. Acquisition', 'desc' => 'The real math and emotions behind choosing how to exit your company. What nobody tells you.', 'duration' => 3480],
                ],
            ],
            // Through the Ages
            [
                'podcast' => $podcasts[5],
                'episodes' => [
                    ['title' => 'The Fall of the Roman Empire', 'desc' => 'What actually caused Rome to fall? Professor Sousa dismantles the myths and presents the real complex causes.', 'duration' => 4320],
                    ['title' => 'Cleopatra: Beyond the Legend', 'desc' => 'The real Cleopatra was far more fascinating — and terrifying — than Hollywood would have you believe.', 'duration' => 3840],
                    ['title' => 'The Black Death: How a Plague Changed Everything', 'desc' => 'The bubonic plague wiped out 1/3 of Europe. Here\'s how that catastrophe actually accelerated modernity.', 'duration' => 3960],
                    ['title' => 'Alexander the Great\'s Lost Tomb', 'desc' => 'Archaeologists have long searched for Alexander\'s tomb. New discoveries in Egypt point to a startling location.', 'duration' => 3600],
                ],
            ],
        ];

        $sampleAudioFiles = [
            'audio/sample-podcast-1.mp4',
            'audio/sample-podcast-2.mp4',
            'audio/sample-podcast-3.mp4',
        ];

        $audioIndex = 0;
        foreach ($episodeSets as $set) {
            foreach ($set['episodes'] as $i => $epData) {
                Episode::create([
                    'podcast_id'     => $set['podcast']->id,
                    'title'          => $epData['title'],
                    'slug'           => Str::slug($epData['title']) . '-' . Str::random(4),
                    'description'    => $epData['desc'],
                    'audio_file'     => $sampleAudioFiles[$audioIndex % 3], // Cycle through sample podcasts
                    'duration'       => $epData['duration'],
                    'episode_number' => $i + 1,
                    'season_number'  => 1,
                    'episode_type'   => 'full',
                    'status'         => 'published',
                    'release_date'   => now()->subDays(($i + 1) * 7),
                    'play_count'     => rand(1000, 50000),
                ]);
                $audioIndex++;
            }
        }

        // -------------------------------------------------------
        // 6. SUBSCRIPTIONS & FAVORITES
        // -------------------------------------------------------
        foreach ($allListeners as $listener) {
            // Each listener subscribes to 2–4 creators
            $creatorSubset = collect($creators)->random(rand(2, 4));
            foreach ($creatorSubset as $creator) {
                $listener->subscriptions()->syncWithoutDetaching([$creator->id]);
            }

            // Each listener favorites 2–5 podcasts
            $favSubset = collect($podcasts)->random(rand(2, 5));
            foreach ($favSubset as $podcast) {
                $listener->favorites()->syncWithoutDetaching([$podcast->id]);
            }
        }

        // -------------------------------------------------------
        // 7. LIKES
        // -------------------------------------------------------
        $allEpisodes = Episode::all();
        foreach ($allListeners as $listener) {
            $likedEpisodes = $allEpisodes->random(rand(3, 10));
            foreach ($likedEpisodes as $ep) {
                Like::firstOrCreate([
                    'user_id'       => $listener->id,
                    'likeable_type' => Episode::class,
                    'likeable_id'   => $ep->id,
                ]);
            }
        }

        // -------------------------------------------------------
        // 8. COMMENTS
        // -------------------------------------------------------
        $commentBodies = [
            'This episode completely changed my perspective. Absolutely incredible work!',
            'I\'ve been listening to this podcast for years. This is one of their best episodes.',
            'The research here is so thorough. Really appreciate the depth.',
            'Can\'t wait for the next episode. Left me wanting more!',
            'Shared this with my entire team. Everyone loved it.',
            'The guest on this episode was phenomenal. Please bring them back!',
            'I learned more in 45 minutes here than in months of reading articles.',
            'Absolutely gripping from start to finish. 10/10.',
            'The production quality on this show is next level.',
            'This topic is so important and you handled it perfectly.',
        ];

        foreach ($allEpisodes->take(15) as $episode) {
            $commentCount = rand(2, 5);
            for ($i = 0; $i < $commentCount; $i++) {
                $user    = $allListeners[array_rand($allListeners)];
                $comment = Comment::create([
                    'episode_id' => $episode->id,
                    'user_id'    => $user->id,
                    'body'       => $commentBodies[array_rand($commentBodies)],
                ]);

                // Add a reply to some comments
                if (rand(0, 1)) {
                    $replier = $allListeners[array_rand($allListeners)];
                    Comment::create([
                        'episode_id' => $episode->id,
                        'user_id'    => $replier->id,
                        'parent_id'  => $comment->id,
                        'body'       => 'Totally agree! ' . $commentBodies[array_rand($commentBodies)],
                    ]);
                }
            }
        }

        // -------------------------------------------------------
        // 9. LISTENING HISTORY
        // -------------------------------------------------------
        foreach ($allListeners as $listener) {
            $historyEpisodes = $allEpisodes->random(rand(3, 8));
            foreach ($historyEpisodes as $ep) {
                $progress = rand(0, $ep->duration);
                ListeningHistory::updateOrCreate(
                    ['user_id' => $listener->id, 'episode_id' => $ep->id],
                    [
                        'progress_seconds' => $progress,
                        'completed'        => $progress >= $ep->duration * 0.9,
                        'listened_at'      => now()->subDays(rand(1, 30)),
                    ]
                );
            }
        }

        // -------------------------------------------------------
        // 10. RATINGS
        // -------------------------------------------------------
        foreach ($allListeners as $listener) {
            $ratedPodcasts = collect($podcasts)->random(rand(2, 5));
            foreach ($ratedPodcasts as $podcast) {
                Rating::updateOrCreate(
                    ['user_id' => $listener->id, 'podcast_id' => $podcast->id],
                    ['rating' => rand(3, 5)]
                );
            }
        }

        // Recalculate ratings
        foreach ($podcasts as $podcast) {
            $podcast->recalculateRating();
        }

        if ($this->command) {
            $this->command->info('✅ PodWave database seeded successfully!');
            $this->command->info('');
            $this->command->info('🔐 Login credentials:');
            $this->command->info('   Admin   → admin@podwave.fm / password');
            $this->command->info('   Creator → creator@podwave.fm / password');
            $this->command->info('   Listener→ listener@podwave.fm / password');
        }
    }
}
