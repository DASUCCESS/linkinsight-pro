<?php

namespace Database\Seeders;

use App\Models\LinkedinAudienceDemographic;
use App\Models\LinkedinAudienceInsight;
use App\Models\LinkedinConnection;
use App\Models\LinkedinCreatorAudienceMetric;
use App\Models\LinkedinPost;
use App\Models\LinkedinPostMetric;
use App\Models\LinkedinProfile;
use App\Models\LinkedinProfileMetric;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LinkedinDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@linkinsightpro.com'],
            [
                'name'              => 'Michael Johnson',
                'password'          => Hash::make('demo-password-123'),
                'email_verified_at' => now(),
                'role'              => User::ROLE_USER,
                'status'            => 'active',
                'is_admin'          => false,
            ]
        );

        $profiles = [
            [
                'linkedin_id'       => 'urn:li:person:demo-mjohnson',
                'public_url'        => 'https://www.linkedin.com/in/michael-johnson-demo/',
                'name'              => 'Michael Johnson',
                'headline'          => 'Senior Product Manager | SaaS Growth',
                'location'          => 'Austin, Texas, United States',
                'industry'          => 'Technology, Information and Internet',
                'profile_image_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80',
                'connections_count' => 1284,
                'followers_count'   => 3120,
                'profile_type'      => 'own',
                'is_primary'        => true,
            ],
            [
                'linkedin_id'       => 'urn:li:person:demo-emilycarter',
                'public_url'        => 'https://www.linkedin.com/in/emily-carter-growth/',
                'name'              => 'Emily Carter',
                'headline'          => 'VP Marketing | Demand Generation Leader',
                'location'          => 'Seattle, Washington, United States',
                'industry'          => 'Software Development',
                'profile_image_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80',
                'connections_count' => 500,
                'followers_count'   => 8700,
                'profile_type'      => 'competitor',
                'is_primary'        => false,
            ],
            [
                'linkedin_id'       => 'urn:li:person:demo-davidlee',
                'public_url'        => 'https://www.linkedin.com/in/david-lee-ops/',
                'name'              => 'David Lee',
                'headline'          => 'Operations Director | B2B RevOps',
                'location'          => 'Chicago, Illinois, United States',
                'industry'          => 'Business Consulting and Services',
                'profile_image_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80',
                'connections_count' => 930,
                'followers_count'   => 2210,
                'profile_type'      => 'peer',
                'is_primary'        => false,
            ],
        ];

        foreach ($profiles as $profileData) {
            $profile = LinkedinProfile::updateOrCreate(
                [
                    'user_id'    => $user->id,
                    'public_url' => $profileData['public_url'],
                ],
                array_merge($profileData, [
                    'user_id'        => $user->id,
                    'sync_status'    => 'ok',
                    'last_synced_at' => now()->subMinutes(rand(5, 45)),
                    'sync_error'     => null,
                ])
            );

            $this->seedConnections($profile);
            $this->seedPostsWithMetrics($profile);
            $this->seedProfileAndAudienceData($profile);
        }
    }

    private function seedConnections(LinkedinProfile $profile): void
    {
        $people = [
            ['Olivia Brown', 'Product Designer', 'Denver, Colorado, United States', 'Design Services'],
            ['James Wilson', 'Founder at RevScale', 'Miami, Florida, United States', 'Advertising Services'],
            ['Sophia Martinez', 'Customer Success Manager', 'Phoenix, Arizona, United States', 'SaaS'],
            ['Liam Anderson', 'Data Analyst', 'Boston, Massachusetts, United States', 'Technology'],
            ['Ava Thompson', 'Growth Marketing Specialist', 'Nashville, Tennessee, United States', 'Marketing Services'],
            ['Noah Garcia', 'Enterprise Account Executive', 'San Diego, California, United States', 'Computer Software'],
            ['Isabella Rodriguez', 'HR Business Partner', 'Dallas, Texas, United States', 'Human Resources'],
            ['Ethan Miller', 'Full Stack Engineer', 'San Jose, California, United States', 'Software Development'],
            ['Mia Davis', 'Brand Strategist', 'Los Angeles, California, United States', 'Public Relations'],
            ['Benjamin Taylor', 'Revenue Operations Manager', 'New York, New York, United States', 'Business Consulting'],
        ];

        foreach ($people as $index => [$fullName, $headline, $location, $industry]) {
            $publicIdentifier = Str::slug($fullName) . '-' . ($profile->id) . '-' . ($index + 1);
            $dedupeKey = sha1(strtolower($profile->id . '|' . $publicIdentifier . '|' . $fullName));

            LinkedinConnection::updateOrCreate(
                [
                    'linkedin_profile_id' => $profile->id,
                    'dedupe_key'          => $dedupeKey,
                ],
                [
                    'linkedin_connection_id'    => 'urn:li:person:conn-' . Str::lower(Str::random(10)),
                    'public_identifier'         => $publicIdentifier,
                    'profile_url'               => 'https://www.linkedin.com/in/' . $publicIdentifier . '/',
                    'full_name'                 => $fullName,
                    'headline'                  => $headline,
                    'location'                  => $location,
                    'industry'                  => $industry,
                    'profile_image_url'         => 'https://i.pravatar.cc/300?img=' . (($index % 60) + 1),
                    'degree'                    => ($index % 2) + 1,
                    'mutual_connections_count'  => rand(2, 40),
                    'connected_at'              => now()->subDays(rand(20, 620)),
                    'last_seen_at'              => now()->subDays(rand(0, 15)),
                    'source_hash'               => hash('sha256', $profile->id . '|' . $publicIdentifier . '|' . $fullName),
                ]
            );
        }
    }

    private function seedPostsWithMetrics(LinkedinProfile $profile): void
    {
        $posts = [
            [
                'title' => 'How we improved product onboarding',
                'excerpt' => 'We cut onboarding time by 32% by simplifying our first-time user checklist and improving in-app guidance.',
                'post_type' => 'article',
                'media_type' => 'text',
                'activity_category' => 'posts',
            ],
            [
                'title' => 'Team offsite in Denver',
                'excerpt' => 'Great strategy offsite with the team. Sharing 5 lessons from our workshop on execution and accountability.',
                'post_type' => 'image',
                'media_type' => 'image',
                'activity_category' => 'posts',
            ],
            [
                'title' => 'Q2 hiring update',
                'excerpt' => 'We are expanding our customer success and engineering teams across multiple US hubs.',
                'post_type' => 'text',
                'media_type' => 'text',
                'activity_category' => 'comments',
            ],
            [
                'title' => 'Customer spotlight video',
                'excerpt' => 'A short walkthrough showing how one of our enterprise clients reduced manual reporting by 50%.',
                'post_type' => 'video',
                'media_type' => 'video',
                'activity_category' => 'videos',
            ],
        ];

        foreach ($posts as $index => $postData) {
            $postedAt = Carbon::now()->subDays(($index + 1) * 3);
            $linkedinPostId = sprintf('urn:li:activity:%d%d', $profile->id, 10000 + $index);

            $post = LinkedinPost::updateOrCreate(
                ['linkedin_post_id' => $linkedinPostId],
                [
                    'linkedin_profile_id' => $profile->id,
                    'permalink'           => 'https://www.linkedin.com/feed/update/' . $linkedinPostId . '/',
                    'target_permalink'    => 'https://www.linkedin.com/feed/update/' . $linkedinPostId . '/',
                    'posted_at'           => $postedAt,
                    'post_type'           => $postData['post_type'],
                    'activity_category'   => $postData['activity_category'],
                    'media_type'          => $postData['media_type'],
                    'is_reshare'          => false,
                    'is_sponsored'        => false,
                    'content_excerpt'     => $postData['title'] . ': ' . $postData['excerpt'],
                ]
            );

            LinkedinPostMetric::updateOrCreate(
                [
                    'linkedin_post_id' => $post->id,
                    'metric_date'      => $postedAt->toDateString(),
                ],
                [
                    'impressions'              => rand(700, 6000),
                    'unique_impressions'       => rand(500, 5000),
                    'clicks'                   => rand(40, 400),
                    'reactions'                => rand(20, 350),
                    'comments'                 => rand(5, 90),
                    'reposts'                  => rand(1, 40),
                    'saves'                    => rand(0, 65),
                    'video_views'              => $postData['post_type'] === 'video' ? rand(80, 1400) : 0,
                    'follows_from_post'        => rand(0, 35),
                    'profile_visits_from_post' => rand(4, 70),
                    'engagement_rate'          => rand(20, 90) / 100,
                ]
            );
        }
    }

    private function seedProfileAndAudienceData(LinkedinProfile $profile): void
    {
        $snapshotDate = now()->toDateString();

        LinkedinProfileMetric::updateOrCreate(
            [
                'linkedin_profile_id' => $profile->id,
                'metric_date'         => $snapshotDate,
            ],
            [
                'connections_count'   => $profile->connections_count,
                'followers_count'     => $profile->followers_count,
                'profile_views'       => rand(80, 1100),
                'search_appearances'  => rand(30, 600),
                'posts_count'         => 4,
                'impressions_7d'      => rand(1800, 22000),
                'engagements_7d'      => rand(120, 2400),
            ]
        );

        $topJobTitles = [
            ['label' => 'Software Engineer', 'percentage' => 22],
            ['label' => 'Product Manager', 'percentage' => 19],
            ['label' => 'Marketing Manager', 'percentage' => 16],
            ['label' => 'Founder', 'percentage' => 12],
            ['label' => 'Customer Success Manager', 'percentage' => 10],
        ];

        $topIndustries = [
            ['label' => 'Software Development', 'percentage' => 31],
            ['label' => 'Technology, Information and Internet', 'percentage' => 24],
            ['label' => 'Advertising Services', 'percentage' => 14],
            ['label' => 'Financial Services', 'percentage' => 11],
            ['label' => 'Business Consulting and Services', 'percentage' => 9],
        ];

        $topLocations = [
            ['label' => 'New York, New York, United States', 'percentage' => 21],
            ['label' => 'San Francisco Bay Area, United States', 'percentage' => 18],
            ['label' => 'Austin, Texas, United States', 'percentage' => 14],
            ['label' => 'Seattle, Washington, United States', 'percentage' => 11],
            ['label' => 'Chicago, Illinois, United States', 'percentage' => 9],
        ];

        LinkedinAudienceInsight::updateOrCreate(
            [
                'linkedin_profile_id' => $profile->id,
                'snapshot_date'       => $snapshotDate,
            ],
            [
                'top_job_titles'      => $topJobTitles,
                'top_industries'      => $topIndustries,
                'top_locations'       => $topLocations,
                'engagement_sources'  => [
                    ['label' => 'Feed', 'percentage' => 49],
                    ['label' => 'Search', 'percentage' => 21],
                    ['label' => 'Profile', 'percentage' => 17],
                    ['label' => 'Direct', 'percentage' => 13],
                ],
            ]
        );

        $demographics = [
            'job_titles' => [
                ['label' => 'Software Engineer', 'count' => 410],
                ['label' => 'Product Manager', 'count' => 305],
                ['label' => 'Marketing Manager', 'count' => 230],
                ['label' => 'Founder', 'count' => 150],
            ],
            'industries' => [
                ['label' => 'Software Development', 'count' => 580],
                ['label' => 'Technology, Information and Internet', 'count' => 460],
                ['label' => 'Advertising Services', 'count' => 215],
                ['label' => 'Business Consulting and Services', 'count' => 185],
            ],
            'locations' => [
                ['label' => 'New York, New York, United States', 'count' => 320],
                ['label' => 'San Francisco Bay Area, United States', 'count' => 275],
                ['label' => 'Austin, Texas, United States', 'count' => 210],
                ['label' => 'Seattle, Washington, United States', 'count' => 195],
            ],
            'seniority' => [
                ['label' => 'Senior', 'count' => 530],
                ['label' => 'Manager', 'count' => 280],
                ['label' => 'Director', 'count' => 160],
                ['label' => 'Entry', 'count' => 120],
            ],
            'company_size' => [
                ['label' => '11-50', 'count' => 270],
                ['label' => '51-200', 'count' => 390],
                ['label' => '201-500', 'count' => 310],
                ['label' => '500+', 'count' => 240],
            ],
            'age_range' => [
                ['label' => '25-34', 'count' => 470],
                ['label' => '35-44', 'count' => 410],
                ['label' => '45-54', 'count' => 205],
                ['label' => '18-24', 'count' => 95],
            ],
            'gender' => [
                ['label' => 'Male', 'count' => 640],
                ['label' => 'Female', 'count' => 520],
                ['label' => 'Non-binary', 'count' => 20],
            ],
        ];

        LinkedinAudienceDemographic::updateOrCreate(
            [
                'linkedin_profile_id' => $profile->id,
                'snapshot_date'       => $snapshotDate,
            ],
            [
                'demographics'   => $demographics,
                'followers_count'=> $profile->followers_count,
                'source_hash'    => hash('sha256', json_encode($demographics)),
            ]
        );

        $creatorMetrics = [
            'unique_viewers'      => rand(250, 3200),
            'follower_gains'      => rand(5, 90),
            'profile_visits'      => rand(40, 800),
            'audience_breakdown'  => [
                'returning_viewers' => rand(60, 700),
                'new_viewers'       => rand(140, 2500),
            ],
            'top_locations'       => array_slice($topLocations, 0, 3),
            'top_industries'      => array_slice($topIndustries, 0, 3),
        ];

        LinkedinCreatorAudienceMetric::updateOrCreate(
            [
                'linkedin_profile_id' => $profile->id,
                'metric_date'         => $snapshotDate,
            ],
            [
                'metrics'     => $creatorMetrics,
                'source_hash' => hash('sha256', json_encode($creatorMetrics)),
            ]
        );
    }

}
