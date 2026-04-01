
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
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exercise (
    id SERIAL PRIMARY KEY,
    name VARCHAR(48) NOT NULL,
    description TEXT
);

CREATE TABLE general_exercises (
    exercise_id INT REFERENCES exercise(id) ON DELETE CASCADE PRIMARY KEY
);

CREATE TABLE user_exercises (
    exercise_id INT REFERENCES exercise(id) ON DELETE CASCADE PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE workout (
    id SERIAL PRIMARY KEY,
    name VARCHAR(48) NOT NULL,
    user_id INT REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE workout_exercise (
    id SERIAL PRIMARY KEY,
    workout_id INT REFERENCES workout(id) ON DELETE CASCADE,
    exercise_id INT REFERENCES exercise(id),
    exercise_order INT NOT NULL 
);

CREATE TABLE workout_set (
    id SERIAL PRIMARY KEY,
    workout_exercise_id INT REFERENCES workout_exercise(id) ON DELETE CASCADE,
    set_order INT NOT NULL
);

CREATE TABLE workout_session (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    workout_id INT REFERENCES workout(id) ON DELETE SET NULL,
    start_time TIMESTAMPTZ NOT NULL,
    end_time TIMESTAMPTZ,
    notes TEXT
);

CREATE TABLE set_log (
    id SERIAL PRIMARY KEY,
    session_id INT REFERENCES workout_session(id) ON DELETE CASCADE,
    exercise_id INT REFERENCES exercise(id),
    workout_set_id INT REFERENCES workout_set(id) ON DELETE SET NULL,
    weight DECIMAL(6,2),
    reps INT NOT NULL,
    note TEXT
);