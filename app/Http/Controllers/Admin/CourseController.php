<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function setActive(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $course->forceFill(['is_active' => (bool) $data['is_active']])->save();

        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->enrollments()->exists()) {
            return back()->withErrors([
                'course' => 'Course has enrollments and cannot be deleted.',
            ]);
        }

        $course->delete();

        return back()->with('success', 'Course deleted.');
    }

    public function create(): Response
    {
        return Inertia::render('admin/courses/create', [
            'defaults' => [
                'is_active' => true,
                'price_mwk' => 0,
                'price_points' => 0,
            ],
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        Course::create($request->validated());

        return redirect()
            ->to(route('admin.courses', absolute: false))
            ->with('success', 'Course created.');
    }

    public function edit(Course $course): Response
    {
        return Inertia::render('admin/courses/edit', [
            'course' => [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'duration' => $course->duration,
                'price_mwk' => $course->price_mwk,
                'price_points' => $course->price_points,
                'content_type' => $course->content_type,
                'content_url' => $course->content_url,
                'is_active' => $course->is_active,
            ],
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()
            ->to(route('admin.courses', absolute: false))
            ->with('success', 'Course updated.');
    }

    public function index(Request $request): Response
    {
        $active = (string) $request->query('active', 'all');
        if (! in_array($active, ['all', 'active', 'inactive'], true)) {
            $active = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $courses = Course::query()
            ->withCount('enrollments')
            ->when($active !== 'all', fn (Builder $query): Builder => $query->where('is_active', $active === 'active'))
            ->when($search !== '', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Course $course): array => [
                'id' => $course->id,
                'name' => $course->name,
                'duration' => $course->duration,
                'price_mwk' => $course->price_mwk,
                'price_points' => $course->price_points,
                'content_type' => $course->content_type,
                'content_url' => $course->content_url,
                'is_active' => $course->is_active,
                'enrollments_count' => $course->enrollments_count,
                'created_at' => $course->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/courses', [
            'filters' => [
                'search' => $search,
                'active' => $active,
            ],
            'courses' => $courses,
            'summary' => [
                'total' => Course::count(),
                'active' => Course::where('is_active', true)->count(),
                'inactive' => Course::where('is_active', false)->count(),
            ],
        ]);
    }
}
