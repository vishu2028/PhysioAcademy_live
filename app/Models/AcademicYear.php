<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

    class AcademicYear extends Model
    {
        use \App\Traits\Loggable;

        protected $fillable = [
            'name',
            'slug',
            'description',
            'status',
            'order',
        ];

        public function semesters()
        {
            return $this->hasMany(Semester::class)->orderBy('order');
        }

        public function topics()
        {
            return $this->hasMany(Topic::class)->orderBy('order');
        }

        public function scopeActive(Builder $query): Builder
        {
            return $query->where('academic_years.status', true);
        }

        /**
         * Calculate curriculum counts from actual active records.
         *
         * Old manually saved units_count and topics_count values
         * are deliberately ignored.
         */
        public function scopeWithCurriculumCounts(Builder $query): Builder
        {
            return $query->addSelect([
                'calculated_subjects_count' => Topic::query()
                    ->selectRaw('COUNT(DISTINCT topics.subject_id)')
                    ->join(
                        'subjects',
                        'subjects.id',
                        '=',
                        'topics.subject_id'
                    )
                    ->join(
                        'unit_topics',
                        'unit_topics.id',
                        '=',
                        'topics.unit_topic_id'
                    )
                    ->join(
                        'units',
                        'units.id',
                        '=',
                        'unit_topics.unit_id'
                    )
                    ->whereColumn(
                        'topics.academic_year_id',
                        'academic_years.id'
                    )
                    ->where('topics.status', true)
                    ->whereNull('topics.parent_id')
                    ->where('subjects.status', true)
                    ->where('unit_topics.status', true)
                    ->where('units.is_active', true),

                'calculated_units_count' => Topic::query()
                    ->selectRaw('COUNT(DISTINCT unit_topics.unit_id)')
                    ->join(
                        'subjects',
                        'subjects.id',
                        '=',
                        'topics.subject_id'
                    )
                    ->join(
                        'unit_topics',
                        'unit_topics.id',
                        '=',
                        'topics.unit_topic_id'
                    )
                    ->join(
                        'units',
                        'units.id',
                        '=',
                        'unit_topics.unit_id'
                    )
                    ->whereColumn(
                        'topics.academic_year_id',
                        'academic_years.id'
                    )
                    ->where('topics.status', true)
                    ->whereNull('topics.parent_id')
                    ->where('subjects.status', true)
                    ->where('unit_topics.status', true)
                    ->where('units.is_active', true),

                'calculated_topics_count' => Topic::query()
                    ->selectRaw('COUNT(DISTINCT topics.id)')
                    ->join(
                        'subjects',
                        'subjects.id',
                        '=',
                        'topics.subject_id'
                    )
                    ->join(
                        'unit_topics',
                        'unit_topics.id',
                        '=',
                        'topics.unit_topic_id'
                    )
                    ->join(
                        'units',
                        'units.id',
                        '=',
                        'unit_topics.unit_id'
                    )
                    ->whereColumn(
                        'topics.academic_year_id',
                        'academic_years.id'
                    )
                    ->where('topics.status', true)
                    ->whereNull('topics.parent_id')
                    ->where('subjects.status', true)
                    ->where('unit_topics.status', true)
                    ->where('units.is_active', true),
            ]);
        }

        /**
         * Return calculated value instead of the old manual DB column.
         */
        public function getSubjectsCountAttribute(): int
        {
            return (int) (
                $this->attributes['calculated_subjects_count'] ?? 0
            );
        }

        /**
         * Return calculated value instead of the old manual DB column.
         */
        public function getUnitsCountAttribute($value): int
        {
            return (int) (
                $this->attributes['calculated_units_count'] ?? 0
            );
        }

        /**
         * Return calculated value instead of the old manual DB column.
         */
        public function getTopicsCountAttribute($value): int
        {
            return (int) (
                $this->attributes['calculated_topics_count'] ?? 0
            );
        }
    }
