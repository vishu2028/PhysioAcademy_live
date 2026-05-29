<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\LearningMaterial;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Subjects
        $subjects = [
            [
                'name' => 'Anatomy',
                'description' => 'Detailed study of human body structures, bones, muscles, and nerves.',
                'icon' => '🦴',
                'image' => null,
                'status' => true,
                'order' => 1,
            ],
            [
                'name' => 'Physiology',
                'description' => 'Study of how the human body and its systems function.',
                'icon' => '🫀',
                'image' => null,
                'status' => true,
                'order' => 2,
            ],
            [
                'name' => 'Biochemistry',
                'description' => 'Chemical processes within and relating to living organisms.',
                'icon' => '🧪',
                'image' => null,
                'status' => true,
                'order' => 3,
            ],
            [
                'name' => 'Kinesiology',
                'description' => 'Scientific study of human body movement.',
                'icon' => '🏃',
                'image' => null,
                'status' => true,
                'order' => 4,
            ],
            [
                'name' => 'Pathology',
                'description' => 'Study of the causes and effects of diseases or injuries.',
                'icon' => '🔬',
                'image' => null,
                'status' => true,
                'order' => 5,
            ],
            [
                'name' => 'Pharmacology',
                'description' => 'Study of drugs and their effect on the human body.',
                'icon' => '💊',
                'image' => null,
                'status' => true,
                'order' => 6,
            ],
            [
                'name' => 'Exercise Therapy',
                'description' => 'Techniques for therapeutic exercises and body rehabilitation.',
                'icon' => '🧘',
                'image' => null,
                'status' => true,
                'order' => 7,
            ],
            [
                'name' => 'Electrotherapy',
                'description' => 'Use of electrical energy as medical treatment.',
                'icon' => '⚡',
                'image' => null,
                'status' => true,
                'order' => 8,
            ],
            [
                'name' => 'Musculoskeletal Physiotherapy',
                'description' => 'Specialized treatment for muscle and bone injuries.',
                'icon' => '💪',
                'image' => null,
                'status' => true,
                'order' => 9,
            ],
            [
                'name' => 'Neurological Physiotherapy',
                'description' => 'Rehabilitation for patients with neurological disorders.',
                'icon' => '🧠',
                'image' => null,
                'status' => true,
                'order' => 10,
            ],
        ];

        $subjectModels = [];
        foreach ($subjects as $s) {
            $s['slug'] = Str::slug($s['name']);
            $subjectModels[$s['name']] = Subject::create($s);
        }

        // 2. Academic Years
        $years = [
            [
                'name' => 'First Year',
                'description' => 'Foundational subjects including Anatomy, Physiology and Biochemistry.',
                'units_count' => 8,
                'topics_count' => 120,
                'status' => true,
                'order' => 1,
            ],
            [
                'name' => 'Second Year',
                'description' => 'Introduction to core Physiotherapy skills, Pathology and Kinesiology.',
                'units_count' => 10,
                'topics_count' => 150,
                'status' => true,
                'order' => 2,
            ],
            [
                'name' => 'Third Year',
                'description' => 'Clinical subjects including Musculoskeletal and Electrotherapy.',
                'units_count' => 12,
                'topics_count' => 200,
                'status' => true,
                'order' => 3,
            ],
            [
                'name' => 'Fourth Year',
                'description' => 'Advanced Clinical Rehabilitation, Neurology and Research.',
                'units_count' => 15,
                'topics_count' => 250,
                'status' => true,
                'order' => 4,
            ],
            [
                'name' => 'Internship',
                'description' => 'Hands-on clinical practice and masterclasses.',
                'units_count' => 5,
                'topics_count' => 50,
                'status' => true,
                'order' => 5,
            ],
        ];

        $yearModels = [];
        foreach ($years as $y) {
            $y['slug'] = Str::slug($y['name']);
            $yearModels[$y['name']] = AcademicYear::create($y);

            // Add Semesters for each year
            Semester::create(['academic_year_id' => $yearModels[$y['name']]->id, 'name' => 'Semester 1', 'order' => 1]);
            Semester::create(['academic_year_id' => $yearModels[$y['name']]->id, 'name' => 'Semester 2', 'order' => 2]);
        }

        // 3. Topics & Materials
        $topicData = [
            // Year 1 - Anatomy
            [
                'title' => 'Human Skeleton Overview',
                'subject' => 'Anatomy',
                'year' => 'First Year',
                'module' => 'Unit I: Introduction',
                'description' => 'An in-depth look at the 206 bones of the human body, their structure, and classification.',
                'materials' => [
                    ['title' => 'Skeletal System PDF', 'type' => 'pdf', 'url' => 'https://example.com/skeletal-system.pdf'],
                    ['title' => 'Bone Classification Video', 'type' => 'video', 'url' => 'https://youtube.com/watch?v=example1'],
                    ['title' => 'Osteology Masterclass', 'type' => 'link', 'url' => 'https://physio-academy.com/osteology'],
                ]
            ],
            [
                'title' => 'Brachial Plexus',
                'subject' => 'Anatomy',
                'year' => 'First Year',
                'module' => 'Unit III: Upper Limb',
                'description' => 'Formation, branches, and clinical importance of the Brachial Plexus. Essential for neuro-assessment.',
                'materials' => [
                    ['title' => 'Plexus Diagram Download', 'type' => 'pdf', 'url' => 'https://example.com/brachial-plexus.pdf'],
                    ['title' => 'Clinical Correlations Note', 'type' => 'note', 'content' => 'Erb\'s Palsy and Klumpke\'s Paralysis are the most important clinical cases for this topic.'],
                    ['title' => 'Viva Voice Questions', 'type' => 'link', 'url' => 'https://example.com/viva-prep'],
                ]
            ],
            // Year 1 - Physiology
            [
                'title' => 'Cardiovascular System',
                'subject' => 'Physiology',
                'year' => 'First Year',
                'module' => 'Unit IV',
                'description' => 'Understanding heart sounds, cardiac cycle, and blood pressure regulation mechanism.',
                'materials' => [
                    ['title' => 'Cardiac Cycle Animation', 'type' => 'video', 'url' => 'https://youtube.com/watch?v=example2'],
                    ['title' => 'Heart Sounds Tutorial', 'type' => 'link', 'url' => 'https://example.com/heart-sounds'],
                ]
            ],

            // Year 2 - Kinesiology
            [
                'title' => 'Gait Cycle Analysis',
                'subject' => 'Kinesiology',
                'year' => 'Second Year',
                'module' => 'Unit V: Biomechanics',
                'description' => 'Detailed analysis of human walking patterns including stance phase and swing phase.',
                'materials' => [
                    ['title' => 'Gait Parameters Guide', 'type' => 'pdf', 'url' => 'https://example.com/gait-analysis.pdf'],
                    ['title' => 'Abnormal Gait Patterns', 'type' => 'video', 'url' => 'https://youtube.com/watch?v=example3'],
                ]
            ],

            // Year 3 - Musculoskeletal
            [
                'title' => 'ACL Tear & Rehabilitation',
                'subject' => 'Musculoskeletal Physiotherapy',
                'year' => 'Third Year',
                'module' => 'Unit II: Knee Complex',
                'description' => 'Comprehensive protocol for Anterior Cruciate Ligament (ACL) injury assessment and post-surgical rehab.',
                'materials' => [
                    ['title' => 'Rehab Protocol Stages', 'type' => 'pdf', 'url' => 'https://example.com/acl-rehab.pdf'],
                    ['title' => 'Special Tests for Knee', 'type' => 'video', 'url' => 'https://youtube.com/watch?v=example4'],
                ]
            ],

            // Year 4 - Neurology
            [
                'title' => 'Stroke Management (Hemiplegia)',
                'subject' => 'Neurological Physiotherapy',
                'year' => 'Fourth Year',
                'module' => 'Unit I',
                'description' => 'Rehabilitation strategies for stroke patients focusing on neuroplasticity and functional recovery.',
                'materials' => [
                    ['title' => 'Brunnstrom Stages Note', 'type' => 'note', 'content' => 'The 7 stages of recovery defined by Signe Brunnstrom are crucial for clinical assessment.'],
                    ['title' => 'Proprioceptive Neuromuscular Facilitation (PNF)', 'type' => 'video', 'url' => 'https://youtube.com/watch?v=example5'],
                ]
            ],
        ];

        foreach ($topicData as $idx => $t) {
            $subject = $subjectModels[$t['subject']] ?? null;
            $year = $yearModels[$t['year']] ?? null;
            $semester = Semester::where('academic_year_id', $year->id)->first(); // Default to Sem 1

            if ($subject && $year) {
                $topic = Topic::create([
                    'title' => $t['title'],
                    'slug' => Str::slug($t['title']) . '-' . $idx,
                    'description' => $t['description'],
                    'subject_id' => $subject->id,
                    'academic_year_id' => $year->id,
                    'semester_id' => $semester ? $semester->id : null,
                    'module_number' => $t['module'],
                    'status' => true,
                    'order' => $idx + 1,
                ]);

                // Create materials
                foreach ($t['materials'] as $mIdx => $m) {
                    LearningMaterial::create([
                        'topic_id' => $topic->id,
                        'title' => $m['title'],
                        'type' => $m['type'],
                        'content' => $m['content'] ?? null,
                        'url' => $m['url'] ?? null,
                        'order' => $mIdx + 1,
                    ]);
                }
            }
        }

        // Add some subtopics for "Human Skeleton Overview"
        $parentTopic = Topic::where('title', 'Human Skeleton Overview')->first();
        if ($parentTopic) {
            $subtopics = [
                ['title' => 'Axial Skeleton', 'description' => 'Includes the skull, vertebral column, and rib cage.'],
                ['title' => 'Appendicular Skeleton', 'description' => 'Includes the limbs and girdles.'],
            ];

            foreach ($subtopics as $sIdx => $st) {
                Topic::create([
                    'title' => $st['title'],
                    'slug' => Str::slug($st['title']),
                    'description' => $st['description'],
                    'subject_id' => $parentTopic->subject_id,
                    'academic_year_id' => $parentTopic->academic_year_id,
                    'semester_id' => $parentTopic->semester_id,
                    'parent_id' => $parentTopic->id,
                    'module_number' => $parentTopic->module_number,
                    'status' => true,
                    'order' => $sIdx + 1,
                ]);
            }
        }
    }
}
