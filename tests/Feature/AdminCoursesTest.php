<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders courses with summary counts', function () {
    Course::factory()->create(['is_active' => true]);
    Course::factory()->create(['is_active' => false]);

    $this->get('/admin/courses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/courses')
            ->where('filters.search', '')
            ->where('filters.active', 'all')
            ->where('summary.total', 2)
            ->where('summary.active', 1)
            ->where('summary.inactive', 1)
            ->has('courses.data', 2)
        );
});

it('filters courses by active and search', function () {
    Course::factory()->create(['name' => 'Mindset 101', 'is_active' => true]);
    Course::factory()->create(['name' => 'Deep work', 'is_active' => false]);

    $this->get('/admin/courses?active=inactive&search=Deep')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.active', 'inactive')
            ->where('filters.search', 'Deep')
            ->has('courses.data', 1)
            ->where('courses.data.0.name', 'Deep work')
        );
});

it('allows admins to create courses', function () {
    $this->get('/admin/courses/create')->assertOk();

    $this->post('/admin/courses', [
        'name' => 'Focus 101',
        'description' => 'Test course',
        'duration' => '4 weeks',
        'price_mwk' => 1000,
        'price_points' => 200,
        'content_type' => 'video',
        'content_url' => 'https://example.com/course',
        'is_active' => true,
    ])->assertRedirect('/admin/courses');

    expect(Course::query()->where('name', 'Focus 101')->exists())->toBeTrue();
});

it('allows admins to edit courses', function () {
    $course = Course::factory()->create(['name' => 'Old course', 'is_active' => true]);

    $this->get("/admin/courses/{$course->id}/edit")->assertOk();

    $this->put("/admin/courses/{$course->id}", [
        'name' => 'New course',
        'description' => $course->description,
        'duration' => $course->duration,
        'price_mwk' => $course->price_mwk,
        'price_points' => $course->price_points,
        'content_type' => $course->content_type,
        'content_url' => $course->content_url,
        'is_active' => false,
    ])->assertRedirect('/admin/courses');

    expect($course->fresh()->name)->toBe('New course');
    expect($course->fresh()->is_active)->toBeFalse();
});

it('allows admins to activate and deactivate courses quickly', function () {
    $course = Course::factory()->create(['is_active' => true]);

    $this->post("/admin/courses/{$course->id}/active", ['is_active' => 0])->assertRedirect();
    expect($course->fresh()->is_active)->toBeFalse();

    $this->post("/admin/courses/{$course->id}/active", ['is_active' => 1])->assertRedirect();
    expect($course->fresh()->is_active)->toBeTrue();
});

it('prevents deleting courses with enrollments', function () {
    $enrollment = CourseEnrollment::factory()->create();

    $this->delete("/admin/courses/{$enrollment->course_id}")
        ->assertRedirect()
        ->assertSessionHasErrors('course');

    expect(Course::query()->whereKey($enrollment->course_id)->exists())->toBeTrue();
});

it('allows deleting courses without enrollments', function () {
    $course = Course::factory()->create();

    $this->delete("/admin/courses/{$course->id}")
        ->assertRedirect()
        ->assertSessionHas('success', 'Course deleted.');

    expect(Course::query()->whereKey($course->id)->exists())->toBeFalse();
});
