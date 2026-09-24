<?php

namespace Database\Seeders;

use App\Models\FireEducationContent;
use Illuminate\Database\Seeder;

class FireEducationContentSeeder extends Seeder
{
    /**
     * Mobile app reference fire education content.
     * Source: firesight_mobile_cs.fire_education_content
     *
     * @var array<int, array<string, mixed>>
     */
    private const CONTENTS = [
        [
            'content_id' => 1,
            'title' => 'Philippines Fire Season Safety Guide 2026',
            'category' => 'prevention',
            'summary' => 'Essential fire prevention practices for Filipino households during the dry season.',
            'body' => 'Detailed guidance on preparing homes and communities for the dry season, covering electrical safety, cooking safety, and community-level prevention measures relevant to Lian, Batangas households.',
            'image_path' => null,
            'read_minutes' => 8,
            'is_featured' => true,
            'created_at' => '2026-07-18 02:38:41',
        ],
        [
            'content_id' => 2,
            'title' => 'Kitchen Fire Safety',
            'category' => 'prevention',
            'summary' => 'Most house fires start in the kitchen. Learn how to prevent cooking fires.',
            'body' => 'Never leave cooking unattended, keep flammable materials away from the stove, and know how to respond if a small grease fire starts (do not use water).',
            'image_path' => null,
            'read_minutes' => 4,
            'is_featured' => false,
            'created_at' => '2026-07-18 02:38:41',
        ],
        [
            'content_id' => 3,
            'title' => 'Electrical Safety at Home',
            'category' => 'prevention',
            'summary' => 'Overloaded circuits and faulty wiring are leading causes of residential fires.',
            'body' => 'Avoid overloading outlets, replace frayed cords, and have an electrician inspect wiring in older homes.',
            'image_path' => null,
            'read_minutes' => 5,
            'is_featured' => false,
            'created_at' => '2026-07-18 02:38:41',
        ],
        [
            'content_id' => 4,
            'title' => 'What To Do During a Fire',
            'category' => 'emergency_response',
            'summary' => 'Know the R.A.C.E. and P.A.S.S. procedures for fire emergencies.',
            'body' => 'R.A.C.E. (Rescue, Alarm, Contain, Extinguish/Evacuate) and P.A.S.S. (Pull, Aim, Squeeze, Sweep) are core procedures every household should memorize.',
            'image_path' => null,
            'read_minutes' => 5,
            'is_featured' => false,
            'created_at' => '2026-07-18 02:38:41',
        ],
        [
            'content_id' => 5,
            'title' => 'Understanding Barangay Fire Risk Levels',
            'category' => 'awareness',
            'summary' => 'How FireSight calculates Low, Moderate, and High risk levels for your barangay.',
            'body' => 'Risk levels are generated from historical incident data and demographic density, helping residents understand why their barangay is flagged at a particular risk level.',
            'image_path' => null,
            'read_minutes' => 4,
            'is_featured' => false,
            'created_at' => '2026-07-18 02:38:41',
        ],
    ];

    public function run(): void
    {
        foreach (self::CONTENTS as $content) {
            FireEducationContent::updateOrCreate(
                ['content_id' => $content['content_id']],
                $content
            );
        }
    }
}
