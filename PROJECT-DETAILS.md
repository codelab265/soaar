# **SoaaR! – Product Specification (MVP 1.0)**

## **1. Overview**

**App Name:** SoaaR!
**Organization:** Soar to Infinity (S2i)
**Version:** MVP 1.0

### **Purpose**

SoaaR! is a behavioral accountability application designed to:

- Increase execution
- Reduce procrastination

### **Core Features**

- Goal setting and breakdown
- Daily task execution
- Streak tracking
- Points and penalties system
- Social accountability

### **Psychological Drivers**

- Social pressure
- Loss aversion
- Streak visibility
- Immediate feedback
- Reward reinforcement

---

## **2. Target Users**

**Primary Audience:**

- Age: 20–35
- Professionals
- Growth-oriented
- Struggle with consistency
- Financially capable
- Interested in self-improvement

---

## **3. System Architecture Overview**

### **Core Pillars**

1. User Accounts
2. Goals & Tasks Engine
3. Points & Discipline System
4. Accountability Partner System
5. Streak & Challenge System
6. Course Redemption System

---

## **4. Tech Stack (Updated)**

### **Backend**

- Laravel 12

### **Frontend**

- Livewire (Primary)
- Vanilla JavaScript (for lightweight interactions)

### **Admin Panel**

- FilamentPHP v5

### **Database**

- MySQL

### **Infrastructure**

- Laravel Cloud / AWS (recommended)

### **Key Requirements**

- Real-time updates (Laravel Echo / WebSockets optional)
- Push notifications
- Secure authentication
- Scalable architecture

---

## **5. User Account System**

### **Authentication**

- Email / Phone / Google login

### **User Profile Features**

- Username
- Profile picture (optional)
- Discipline Score (0–100)
- Total Points
- Current Streak
- Longest Streak
- Active Goals
- Accountability Partners

---

## **6. Goal Management System**

### **Goal Structure**

```
Goal
 ├── Objectives
 │    ├── Tasks
 ├── Deadline
 ├── Justification (Why)
 └── Accountability Partner (optional)
```

### **Goal Creation Flow**

1. Enter title
2. Add description
3. Add **Why (required)**
4. Set deadline
5. Create objectives
6. Add tasks
7. Assign partner (optional)

### **Goal Status Types**

- Active
- Pending Verification
- Verified Completed
- Cancelled
- Expired

### **Rules**

- Deadline passed → Goal becomes **Expired**
- Automatic penalty applied

---

## **7. Accountability Partner System**

### **Rules**

- One partner per goal

### **Flow**

1. User selects partner or none
2. Search by username
3. Send request

### **Partner Actions**

- Accept
- Decline

### **If Accepted**

- Partner linked permanently (unless removed)
- Responsible for verification
- Receives inactivity alerts

### **Verification Process**

When goal is completed:

- Status → Pending Verification

Partner can:

- Approve → Full rewards
- Reject → Penalty applied
- Request proof

### **Auto Approval**

- After 48 hours → 80% reward
- No verification badge

---

## **8. Daily Task Engine**

### **Features**

- Create tasks
- Assign difficulty:

    - Simple (<30 min)
    - Medium (30–90 min)
    - Hard (90+ min)

- Set reminders
- Mark complete

### **Rules**

- Tasks reset daily if incomplete
- Missed tasks:

    - Break streak
    - Deduct points

---

## **9. Points System**

### **9.1 Task Points**

| Task Type | Points |
| --------- | ------ |
| Simple    | +5     |
| Medium    | +10    |
| Hard      | +20    |

- Daily cap: **60 points**
- Missed task: **-5 points**

---

### **9.2 Objective Completion**

- Base: +40
- Verified bonus: +10

---

### **9.3 Goal Completion**

- Base: +100

**Bonuses:**

- Early completion: +30
- 100% task completion: +25
- Partner verification: +20

**Max per goal: 175 points**

---

### **9.4 Streak Bonuses**

| Streak   | Reward |
| -------- | ------ |
| 7 days   | +15    |
| 14 days  | +30    |
| 30 days  | +75    |
| 60 days  | +150   |
| 100 days | +300   |

- Streak broken: -25

---

### **9.5 Challenges**

- 30-day challenge → +200
- 100-day challenge → +500
- Top 10 leaderboard → +100

---

### **9.6 Penalties**

- Goal expired → -75
- Missed deadline → -50
- Partner rejection → -15
- Streak broken → -25

---

## **10. Discipline Score (0–100)**

### **Formula Weighting**

- 40% Completion rate

- 30% Streak strength

- 20% Partner verification success

- 10% Penalty ratio

- Updates weekly

- Independent from total points

---

## **11. Anti-Gaming Protection**

- Daily cap: 60 points
- Tasks <5 minutes = no points
- Repetitive tasks lose value after 10 repeats
- Rapid goal deletion → cooldown enforced

---

## **12. Course Redemption System**

### **Admin Features**

- Create courses:

    - Name
    - Description
    - Duration
    - Price (MWK + Points)
    - Content (video/audio/text)

### **User Options**

- Buy with money
- Buy with points
- Hybrid payment

### **Conversion Model**

- 1,000 points ≈ MWK 10,000

### **Example**

- Course:

    - MWK 25,000
    - OR 2,500 points

### **Hybrid Example**

- User has 1,200 points:

    - Uses points
    - Pays remaining MWK balance

---

## **13. Streak System**

Tracks:

- Daily streak
- Challenge streak
- Current streak
- Longest streak

### **Rules**

- Missed day → reset streak
- Points deducted

---

## **14. Notification System**

### **Triggers**

- Deadline approaching
- Inactivity
- Missed partner check-in
- Streak at risk
- Points gained/lost

### **Tone (Psychological)**

Examples:

- “Your streak is nervous.”
- “Comfort zone detected.”
- “Still building or already quitting?”

---

## **15. Analytics Dashboard**

### **Metrics**

- Completion rate
- Weekly consistency
- Current streak
- Longest streak
- Points history
- Discipline trend

### **Visuals**

- Bar charts
- Heatmap calendar
- Line graphs

---

## **16. Admin Panel (FilamentPHP v5)**

### **Capabilities**

- Manage challenges
- Manage courses
- Send global notifications
- View analytics
- Monitor leaderboard
- Suspend users

---

## **17. Monetization Strategy**

### **Option 1: Freemium**

**Free Tier**

- Basic tracking
- Limited challenges

**Premium**

- Advanced analytics
- Leaderboards
- Commitment mode
- Premium challenges

---

### **Option 2: Subscription**

- MWK 5,000 – 15,000 / month

---

## **18. Development Roadmap**

### **Phase Order**

1. User accounts
2. Goal & task engine
3. Points logic
4. Accountability system
5. Streak system
6. Notifications
7. Analytics
8. Challenges
9. Course redemption

---

## **19. Success Metrics**

Track:

- Daily Active Users (DAU)
- 7-day retention
- Average streak length
- % completing 7+ days
- % completing 30 days
- Average monthly points
- Goal verification rate

---

## **20. Notes for Development**

- Prioritize **behavioral feedback loops**
- Ensure **low friction UX**
- Design for **habit formation**
- Keep system **strict but fair**
- Optimize for **mobile-first experience**

---

**End of Document**
