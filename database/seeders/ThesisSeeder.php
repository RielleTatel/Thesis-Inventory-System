<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Database\Seeder;

class ThesisSeeder extends Seeder
{
    /**
     * Seed a handful of realistic theses across the two sample departments,
     * each with ordered authors / advisers / panelists / keywords so the
     * public browse + detail screens have meaningful data to show.
     *
     * Assumes DatabaseSeeder has already created the CCS and COE departments.
     */
    public function run(): void
    {
        $departments = Department::pluck('id', 'code');

        foreach ($this->theses() as $data) {
            $thesis = Thesis::create([
                'department_id' => $departments[$data['department']],
                'title' => $data['title'],
                'program' => $data['program'],
                'year' => $data['year'],
                'abstract' => $data['abstract'],
                'recommendations' => $data['recommendations'],
            ]);

            $this->attachOrdered($thesis, 'authors', $data['authors']);
            $this->attachOrdered($thesis, 'advisers', $data['advisers']);
            $this->attachOrdered($thesis, 'panelists', $data['panelists']);
            $this->attachOrdered($thesis, 'keywords', $data['keywords']);
        }
    }

    /**
     * @param  list<string>  $values
     */
    private function attachOrdered(Thesis $thesis, string $relation, array $values): void
    {
        foreach ($values as $position => $name) {
            $thesis->{$relation}()->create(['name' => $name, 'position' => $position]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function theses(): array
    {
        return [
            [
                'department' => 'CCS',
                'title' => 'A Tagalog–English Code-Switching Corpus for Low-Resource Speech Recognition',
                'program' => 'BS Computer Science',
                'year' => 2025,
                'abstract' => 'We present a 42-hour annotated corpus of conversational Tagalog–English code-switching and benchmark three end-to-end ASR architectures against it. Fine-tuning a multilingual transformer on the corpus reduced word error rate by 21.4% over a Tagalog-only baseline, with the largest gains on intra-sentential switch boundaries.',
                'recommendations' => 'We recommend expanding the corpus to include regional varieties such as Cebuano and Ilokano, and releasing speaker-anonymized audio under an open license to support reproducible low-resource speech research.',
                'authors' => ['Patricia Anne L. Soriano', 'Miguel V. Tan', 'Reza F. Hidalgo'],
                'advisers' => ['Dr. Anton G. Reyes', 'Dr. Carmela B. Lim'],
                'panelists' => ['Dr. Felix M. Ong', 'Engr. Dianne C. Salazar'],
                'keywords' => ['speech recognition', 'code-switching', 'NLP', 'low-resource', 'corpus'],
            ],
            [
                'department' => 'CCS',
                'title' => 'Sentiment Dynamics of Disaster Response on Philippine Social Media',
                'program' => 'BS Information Technology',
                'year' => 2024,
                'abstract' => 'We analyzed 1.8 million geotagged posts across three typhoon events to model how public sentiment shifts between warning, impact, and recovery phases. A phase-aware classifier identified actionable need signals such as rescue, relief, and medical with 0.88 F1, surfacing them a median of four hours before formal situation reports.',
                'recommendations' => 'Disaster agencies should pilot the classifier as a triage aid rather than a sole source, with a human-in-the-loop dashboard and clear handling of misinformation surges during peak impact.',
                'authors' => ['Christian Earl B. Navarro', 'Yuki S. Fernandez'],
                'advisers' => ['Dr. Carmela B. Lim'],
                'panelists' => ['Dr. Anton G. Reyes', 'Engr. Paolo M. Tan', 'Dr. Sheila R. Uy'],
                'keywords' => ['social media', 'disaster response', 'sentiment analysis', 'machine learning', 'crisis informatics'],
            ],
            [
                'department' => 'CCS',
                'title' => 'Edge-Caching Heuristics for Low-Bandwidth Campus Networks',
                'program' => 'BS Computer Science',
                'year' => 2023,
                'abstract' => 'This thesis proposes a lightweight popularity-decay heuristic for edge caches deployed on constrained campus routers. Trace-driven simulation over a semester of real request logs showed a 27% improvement in cache hit ratio versus least-recently-used eviction, with negligible memory overhead.',
                'recommendations' => 'A live deployment across multiple campus buildings is recommended to validate the heuristic under bursty enrollment-period traffic, alongside an evaluation of energy cost on low-power hardware.',
                'authors' => ['Diego R. Espinosa'],
                'advisers' => ['Dr. Anton G. Reyes'],
                'panelists' => ['Dr. Felix M. Ong', 'Engr. Dianne C. Salazar'],
                'keywords' => ['edge computing', 'caching', 'computer networks', 'simulation'],
            ],
            [
                'department' => 'COE',
                'title' => 'Bamboo-Reinforced Concrete Panels for Low-Cost Disaster-Resilient Housing',
                'program' => 'BS Civil Engineering',
                'year' => 2025,
                'abstract' => 'This research evaluated the flexural and seismic performance of treated-bamboo-reinforced concrete wall panels as an alternative to steel rebar in single-story housing. Treated bamboo specimens reached 71% of the load capacity of equivalent steel-reinforced panels at one-fifth the embodied carbon.',
                'recommendations' => 'Standardized borate-treatment protocols and accelerated weathering trials are recommended before field adoption, alongside a cost study factoring local bamboo supply chains.',
                'authors' => ['Nathaniel C. Ramos', 'Aira Joy M. Pascual'],
                'advisers' => ['Engr. Rodel S. Bautista'],
                'panelists' => ['Dr. Henry T. Lopez', 'Engr. Marivic D. Santos'],
                'keywords' => ['bamboo', 'sustainable construction', 'disaster resilience', 'low-cost housing', 'materials'],
            ],
            [
                'department' => 'COE',
                'title' => 'Solar-Powered Aquaponics for Urban Rooftop Food Security',
                'program' => 'BS Environmental Engineering',
                'year' => 2024,
                'abstract' => 'This study designed and evaluated a modular solar-powered aquaponics unit suited to dense urban rooftops. Across a six-month deployment, the system sustained tilapia and leafy-green production with a 38% reduction in municipal water use compared to conventional rooftop gardens, while operating entirely off-grid through a 1.2 kW photovoltaic array.',
                'recommendations' => 'Future iterations should integrate low-cost dissolved-oxygen telemetry to pre-empt fish stress events, and a community pilot across three barangays is recommended to assess adoption barriers and shared-maintenance models.',
                'authors' => ['Marisol D. Venancio', 'Karl Andre P. Bautista'],
                'advisers' => ['Dr. Elena R. Marquez'],
                'panelists' => ['Dr. Jose P. Antonio', 'Engr. Lila S. Cruz', 'Dr. Ramon T. delos Reyes'],
                'keywords' => ['aquaponics', 'renewable energy', 'urban agriculture', 'food security', 'sustainability'],
            ],
            [
                'department' => 'COE',
                'title' => 'Vibration-Based Structural Health Monitoring of Steel Truss Bridges',
                'program' => 'BS Civil Engineering',
                'year' => 2023,
                'abstract' => 'This thesis develops a low-cost accelerometer array and damage-detection pipeline for aging steel truss bridges. Using modal-frequency shifts validated against a scaled laboratory truss, the system localized induced damage to within one panel section in 9 of 10 trials.',
                'recommendations' => 'Long-term field instrumentation on an in-service bridge is recommended to characterize environmental temperature effects on modal baselines before the method is used for maintenance prioritization.',
                'authors' => ['Hannah K. Lim', 'Joanna Mae R. Villaluz'],
                'advisers' => ['Engr. Rodel S. Bautista', 'Dr. Henry T. Lopez'],
                'panelists' => ['Engr. Marivic D. Santos', 'Dr. Ramon T. delos Reyes'],
                'keywords' => ['structural health monitoring', 'bridges', 'vibration analysis', 'sensors', 'civil engineering'],
            ],
        ];
    }
}
