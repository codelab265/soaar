---
name: SoaaR MVP Phased Plan
overview: A phased development plan for the SoaaR! MVP, broken into 9 phases matching the spec roadmap. Each phase is chunked into small, focused tasks to minimize token usage per task.
todos:
    - id: phase-1
      content: 'Phase 1: User Accounts Polish (1A–1D) — profile picture, factory, fix soft-delete scope, tests'
      status: completed
    - id: phase-2
      content: 'Phase 2: Goal & Task Engine Workflows (2A–2J) — GoalService, ObjectiveCompletionService, GoalCompletionService, scheduled commands, tests'
      status: completed
    - id: phase-3
      content: 'Phase 3: Points Logic Wiring (3A–3E) — named penalty methods, streak milestone awards, tests'
      status: completed
    - id: phase-4
      content: 'Phase 4: Accountability Partner System (4A–4H) — partner request model, partner service, verification service, auto-approve, Filament, tests'
      status: completed
    - id: phase-5
      content: 'Phase 5: Streak System Logic (5A–5G) — StreakService, wire to task completion, daily check command, tests'
      status: completed
    - id: phase-6
      content: 'Phase 6: Notification System (6A–6E) — notification classes, service wiring, scheduled commands, tests'
      status: completed
    - id: phase-7
      content: 'Phase 7: Analytics Dashboard (7A–7F) — discipline score formula, analytics service, dashboard UI, tests'
      status: completed
    - id: phase-8
      content: 'Phase 8: Challenges (8A–8F) — challenge model, pivot, service, leaderboard, Filament, tests'
      status: completed
    - id: phase-9
      content: 'Phase 9: Course Redemption (9A–9E) — course model, enrollment, redemption service, Filament, tests'
      status: completed
isProject: false
---

# SoaaR! MVP 1.0 — Phased Development Plan

## Current State

The codebase has: all 6 domain models with migrations, enums for statuses/difficulty/transactions, `PointsService` (daily cap + generic award/deduct), `TaskCompletionService` (complete/miss/decay), Filament CRUD for all entities, Inertia auth/settings pages, and Pest tests for the two services.

**What's missing:** Domain workflows (goal lifecycle, objective completion, streak logic, accountability partner flow), scheduled jobs, notifications, analytics, challenges, and courses.

---

## Phase 1: User Accounts (Polish)

Auth and basic user fields exist. Remaining work:

- **1A** — Add profile picture upload to Filament `UserForm` and expose via the User model (file upload field, storage disk)
- **1B** — Add `profile_picture` to `UserFactory` and update `DatabaseSeeder` if needed
- **1C** — Fix `UserResource` using `SoftDeletingScope` when User model does not use `SoftDeletes` — either add the trait or remove the scope
- **1D** — Write Pest tests for user profile picture upload in Filament

---

## Phase 2: Goal and Task Engine (Workflows)

Models and CRUD exist. Missing: lifecycle automation, status transitions, daily resets.

- **2A** — Create `GoalService` with methods: `expireGoal()`, `cancelGoal()`, `submitForVerification()`, `markVerifiedCompleted()` — enforce valid status transitions
- **2B** — Create scheduled command `app:expire-goals` — finds active goals past deadline, calls `GoalService::expireGoal()` which applies `-75` penalty via `PointsService`
- **2C** — Create `ObjectiveCompletionService` — `completeObjective()` awards +40 base, `verifyObjective()` awards +10 bonus; updates status
- **2D** — Create `GoalCompletionService` — `completeGoal()` awards base +100, calculates early/full-task/partner bonuses, caps at 175 total per goal
- **2E** — Create scheduled command `app:process-missed-tasks` — finds pending tasks with `scheduled_date` in the past, calls `TaskCompletionService::missTask()` on each
- **2F** — Write Pest tests for `GoalService` status transitions (valid and invalid)
- **2G** — Write Pest tests for `app:expire-goals` command
- **2H** — Write Pest tests for `ObjectiveCompletionService`
- **2I** — Write Pest tests for `GoalCompletionService` (including 175 cap)
- **2J** — Write Pest tests for `app:process-missed-tasks` command

---

## Phase 3: Points Logic (Complete Wiring)

Constants and generic methods exist. Missing: named penalty flows, streak milestone grants.

- **3A** — Add named methods to `PointsService`: `applyGoalExpiredPenalty()`, `applyMissedDeadlinePenalty()`, `applyPartnerRejectionPenalty()`, `applyStreakBrokenPenalty()` — each calls `deductPoints` with correct type and amount
- **3B** — Add `awardStreakMilestone(User, int $streakDays)` to `PointsService` — looks up `STREAK_MILESTONES` map, awards if milestone hit
- **3C** — Wire `TaskCompletionService::missTask()` to also call streak break logic (Phase 5 dependency — stub for now)
- **3D** — Write Pest tests for each named penalty method
- **3E** — Write Pest tests for streak milestone awards

---

## Phase 4: Accountability Partner System

Only `accountability_partner_id` FK on Goal exists. Full partner workflow is missing.

- **4A** — Create migration for `accountability_partner_requests` table (goal_id, requester_id, partner_id, status enum, responded_at)
- **4B** — Create `AccountabilityPartnerRequest` model + enum `PartnerRequestStatus` (pending, accepted, declined)
- **4C** — Create `AccountabilityPartnerService` — `sendRequest()`, `acceptRequest()`, `declineRequest()`, handles setting `Goal.accountability_partner_id`
- **4D** — Create `GoalVerificationService` — `submitForVerification()`, `approveGoal()`, `rejectGoal()`, `requestProof()`; applies rewards/penalties
- **4E** — Create scheduled command `app:auto-approve-goals` — finds goals pending verification for 48+ hours, auto-approves at 80% reward
- **4F** — Add Filament resource for `AccountabilityPartnerRequest`
- **4G** — Write Pest tests for partner request flow (send/accept/decline)
- **4H** — Write Pest tests for goal verification flow (approve/reject/auto-approve)

---

## Phase 5: Streak System (Logic)

Streak model and columns exist on both `streaks` and `users` tables. No calculation logic.

- **5A** — Create `StreakService` — `recordActivity(User, StreakType)` increments `current_count`, updates `longest_count`, checks milestones; `breakStreak(User, StreakType)` resets `current_count`, applies `-25` penalty
- **5B** — Sync `User.current_streak` / `longest_streak` from `Streak` model in `StreakService` methods
- **5C** — Wire `TaskCompletionService::completeTask()` to call `StreakService::recordActivity()` for daily streak
- **5D** — Wire `TaskCompletionService::missTask()` to call `StreakService::breakStreak()`
- **5E** — Create scheduled command `app:check-streaks` — finds users with no activity yesterday, breaks their daily streak
- **5F** — Write Pest tests for `StreakService` (increment, break, milestone trigger, longest tracking)
- **5G** — Write Pest tests for `app:check-streaks` command

---

## Phase 6: Notification System

Not started. Spec requires push notifications with psychological tone.

- **6A** — Create notification classes: `DeadlineApproachingNotification`, `InactivityNotification`, `StreakAtRiskNotification`, `PointsChangedNotification`, `PartnerCheckInNotification`
- **6B** — Wire notifications into services (e.g., `GoalService::expireGoal()` triggers deadline notification, `StreakService::breakStreak()` triggers streak notification)
- **6C** — Create scheduled command `app:send-streak-risk-notifications` — warns users whose streak will break if no activity today
- **6D** — Create scheduled command `app:send-inactivity-notifications` — notifies users inactive for 2+ days
- **6E** — Write Pest tests for notification dispatch (assert notifications sent)

---

## Phase 7: Analytics Dashboard

Not started. Spec requires completion rates, streaks, points history, discipline trend.

- **7A** — Create `DisciplineScoreService` — implements the weighted formula (40% completion, 30% streak, 20% partner verification, 10% penalty ratio)
- **7B** — Create scheduled command `app:update-discipline-scores` — runs weekly, recalculates for all users
- **7C** — Create `AnalyticsService` — methods for completion rate, weekly consistency, points history, discipline trend
- **7D** — Create Inertia dashboard page with analytics data (or Filament widgets for admin)
- **7E** — Write Pest tests for `DisciplineScoreService` formula
- **7F** — Write Pest tests for `app:update-discipline-scores` command

---

## Phase 8: Challenges

Not started. Spec mentions 30-day and 100-day challenges, leaderboard.

- **8A** — Create migration + model for `challenges` table (title, description, duration_days, reward_points, status, start/end dates)
- **8B** — Create migration + model for `challenge_user` pivot (user_id, challenge_id, joined_at, completed_at, status)
- **8C** — Create `ChallengeService` — `joinChallenge()`, `completeChallenge()`, `checkProgress()`
- **8D** — Create `LeaderboardService` — top users by points, with leaderboard bonus (+100 for top 10)
- **8E** — Add Filament resources for Challenge management
- **8F** — Write Pest tests for challenge flow and leaderboard

---

## Phase 9: Course Redemption System

Not started. Admin creates courses; users buy with money, points, or hybrid.

- **9A** — Create migration + model for `courses` table (name, description, duration, price_mwk, price_points, content_type, content_url)
- **9B** — Create migration + model for `course_enrollments` table (user_id, course_id, payment_method, points_used, amount_paid, enrolled_at)
- **9C** — Create `CourseRedemptionService` — `enrollWithMoney()`, `enrollWithPoints()`, `enrollHybrid()` with conversion rate (1000 pts = MWK 10,000)
- **9D** — Add Filament resource for Course management + enrollment viewing
- **9E** — Write Pest tests for redemption logic (full points, full money, hybrid, insufficient balance)

---

## Anti-Gaming Protection (Cross-Cutting)

- Daily 60-point cap — **already implemented**
- 5-minute minimum — **already implemented**
- Repetition decay after 10 — **already implemented**
- **Missing:** Rapid goal deletion cooldown — add in Phase 2 as part of `GoalService`

---

## Execution Notes

- Each task (e.g., 2A, 2B) is designed to be a single prompt/session
- Tasks within a phase are mostly sequential (services before tests, models before services)
- Cross-phase dependencies are noted (e.g., 3C depends on Phase 5)
- Every task that modifies PHP must run `vendor/bin/pint --dirty --format agent` and `php artisan test --compact` on affected tests
