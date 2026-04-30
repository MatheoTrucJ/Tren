
DROP TABLE IF EXISTS set_log CASCADE;
DROP TABLE IF EXISTS workout_session CASCADE;
DROP TABLE IF EXISTS workout_set CASCADE;
DROP TABLE IF EXISTS workout_exercise CASCADE;
DROP TABLE IF EXISTS workout CASCADE;
DROP TABLE IF EXISTS user_exercises CASCADE;
DROP TABLE IF EXISTS general_exercises CASCADE;
DROP TABLE IF EXISTS exercise CASCADE;
DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(36) UNIQUE NOT NULL,
    password_hash VARCHAR(100) NOT NULL,
    birth_year INT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exercise (
    id SERIAL PRIMARY KEY,
    name VARCHAR(48) NOT NULL,
    description TEXT,
    is_personal BOOLEAN NOT NULL
);

CREATE TABLE general_exercises (
    exercise_id INT REFERENCES exercise(id) ON DELETE CASCADE PRIMARY KEY
);

CREATE TABLE user_exercises (
    exercise_id INT REFERENCES exercise(id) ON DELETE CASCADE PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE workout (
    id SERIAL PRIMARY KEY,
    name VARCHAR(48) NOT NULL,
    description TEXT,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE workout_exercise (
    id SERIAL PRIMARY KEY,
    workout_id INT NOT NULL REFERENCES workout(id) ON DELETE CASCADE,
    exercise_id INT NOT NULL REFERENCES exercise(id),
    exercise_order INT NOT NULL 
);

CREATE TABLE workout_set (
    id SERIAL PRIMARY KEY,
    workout_exercise_id INT NOT NULL REFERENCES workout_exercise(id) ON DELETE CASCADE,
    set_order INT NOT NULL
);

CREATE TABLE workout_session (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    workout_id INT REFERENCES workout(id) ON DELETE SET NULL,
    start_time TIMESTAMPTZ NOT NULL,
    end_time TIMESTAMPTZ,
    notes TEXT
);

CREATE TABLE set_log (
    id SERIAL PRIMARY KEY,
    session_id INT NOT NULL REFERENCES workout_session(id) ON DELETE CASCADE,
    exercise_id INT NOT NULL REFERENCES exercise(id),
    workout_set_id INT REFERENCES workout_set(id) ON DELETE SET NULL,
    weight DECIMAL(6,2),
    reps INT NOT NULL,
    note TEXT
);

INSERT INTO users (
    username,
    password_hash,
    birth_year
) VALUES (
    'matheo',
    'test123',
    '2003'
);

WITH inserted_exercises AS (
    INSERT INTO exercise (
        name,
        description,
        is_personal
    )
    SELECT
        exercise_seed.name,
        exercise_seed.description,
        FALSE
    FROM (
        VALUES
            ('Barbell Squat', 'Squat with a barbell on the back.'),
            ('Flat Benchpress', 'Benchpress on a flat bench.'),
            ('Deadlift', 'Conventional barbell deadlift from the floor.'),
            ('Overhead Press', 'Standing barbell press overhead.'),
            ('Front Squat', 'Squat variation with the barbell on the front rack.'),
            ('Romanian Deadlift', 'Hip hinge movement with emphasis on hamstrings and glutes.'),
            ('Leg Press', 'Machine-based lower body press movement.'),
            ('Walking Lunge', 'Alternating forward lunges for unilateral leg strength.'),
            ('Bulgarian Split Squat', 'Rear-foot elevated split squat for quads and glutes.'),
            ('Leg Extension', 'Machine isolation exercise for the quadriceps.'),
            ('Seated Leg Curl', 'Machine isolation exercise for hamstrings.'),
            ('Standing Calf Raise', 'Calf-focused movement done in a standing position.'),
            ('Incline Dumbbell Press', 'Chest press on an incline bench with dumbbells.'),
            ('Dumbbell Bench Press', 'Flat bench chest press with dumbbells.'),
            ('Push-Up', 'Bodyweight horizontal pressing movement.'),
            ('Dips', 'Bodyweight pressing movement for chest, shoulders, and triceps.'),
            ('Cable Fly', 'Cable-based chest isolation movement.'),
            ('Lat Pulldown', 'Vertical pulling movement targeting the lats.'),
            ('Pull-Up', 'Bodyweight vertical pulling movement.'),
            ('Barbell Row', 'Bent-over barbell row for upper back and lats.'),
            ('Seated Cable Row', 'Horizontal pulling movement using a cable machine.'),
            ('Single-Arm Dumbbell Row', 'Unilateral rowing movement with a dumbbell.'),
            ('Chest Supported Row', 'Row variation performed with torso support.'),
            ('Face Pull', 'Cable pull for rear delts and upper back.'),
            ('Barbell Curl', 'Biceps isolation exercise with a barbell.'),
            ('Hammer Curl', 'Neutral-grip dumbbell curl for biceps and brachialis.'),
            ('Triceps Pushdown', 'Cable isolation movement for triceps.'),
            ('Skullcrusher', 'Lying triceps extension movement.'),
            ('Lateral Raise', 'Dumbbell shoulder isolation for medial delts.'),
            ('Rear Delt Fly', 'Shoulder isolation movement for rear delts.'),
            ('Barbell Shrug', 'Upper trap isolation movement with a barbell.'),
            ('Hip Thrust', 'Glute-focused hip extension movement.'),
            ('Glute Bridge', 'Bodyweight or loaded bridge movement for glutes.'),
            ('Ab Wheel Rollout', 'Core stability movement with an ab wheel.'),
            ('Hanging Leg Raise', 'Hanging core movement targeting lower abs and hip flexors.'),
            ('Plank', 'Isometric core stabilization exercise.')
    ) AS exercise_seed(name, description)
    RETURNING id
)
INSERT INTO general_exercises (exercise_id)
SELECT id
FROM inserted_exercises;

WITH inserted_user_exercises AS (
    INSERT INTO exercise (
        name,
        description,
        is_personal
    )
    SELECT
        exercise_seed.name,
        exercise_seed.description,
        TRUE
    FROM (
        VALUES
            ('Hack Squat', 'Machine squat variation with back support and fixed path.'),
            ('Machine Chest Press', 'Seated chest press on a plate-loaded or selectorized machine.'),
            ('T-Bar Row', 'Row variation using a T-bar setup for mid-back and lats.'),
            ('Preacher Curl', 'Biceps curl performed on a preacher bench for strict form.'),
            ('Overhead Cable Triceps Extension', 'Cable triceps extension emphasizing the long head.'),
            ('Cable Lateral Raise', 'Single-arm cable raise for medial deltoid isolation.'),
            ('Seated Calf Raise', 'Calf isolation movement with knee flexion to target soleus.'),
            ('Cable Crunch', 'Weighted abdominal crunch using a high cable pulley.')
    ) AS exercise_seed(name, description)
    RETURNING id
)
INSERT INTO user_exercises (exercise_id, user_id)
SELECT e.id, u.id
FROM inserted_user_exercises e
JOIN users u ON u.id = 1;

SELECT * FROM users, exercise;

