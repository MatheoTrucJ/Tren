DROP TABLE IF EXISTS set_log;
DROP TABLE IF EXISTS workout_session;
DROP TABLE IF EXISTS workout_set;
DROP TABLE IF EXISTS workout_exercise;
DROP TABLE IF EXISTS workout;
DROP TABLE IF EXISTS user_exercises;
DROP TABLE IF EXISTS general_exercises;
DROP TABLE IF EXISTS exercise;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_ID SERIAL PRIMARY KEY,
    username VARCHAR(36),
    password VARCHAR(100),
    birth_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE exercise (
    exercise_ID SERIAL PRIMARY KEY,
    exercise_name VARCHAR(48),
    description VARCHAR(512)
);
CREATE TABLE general_exercises (
    exercise_ID_FK INT REFERENCES exercise(exercise_ID) PRIMARY KEY
);
CREATE TABLE user_exercises (
    exercise_ID_FK INT REFERENCES exercise(exercise_ID) PRIMARY KEY,
    user_ID_FK INT REFERENCES users(user_ID)
);
CREATE TABLE workout (
    workout_ID SERIAL PRIMARY KEY,
    workout_name VARCHAR(48),
    user_ID_FK INT REFERENCES users(user_ID)
);
CREATE TABLE workout_exercise (
    workout_exercise_ID SERIAL PRIMARY KEY,
    exercise_ID_FK INT REFERENCES exercise(exercise_ID),
    workout_ID_FK INT REFERENCES workout(workout_ID)
);
CREATE TABLE workout_set (
    workout_set_ID SERIAL PRIMARY KEY,
    set_number INT,
    workout_exercise_ID_FK INT REFERENCES workout_exercise(workout_exercise_ID)
);
CREATE TABLE workout_session (
    session_ID SERIAL PRIMARY KEY,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    notes VARCHAR(256),
    workout_ID_FK INT REFERENCES workout(workout_ID)
);
CREATE TABLE set_log (
    set_log_ID SERIAL PRIMARY KEY,
    weight DECIMAL(6,2),
    reps INT,
    set_note VARCHAR(256),
    workout_set_ID_FK INT REFERENCES workout_set(workout_set_ID),
    session_ID_FK INT REFERENCES workout_session(session_ID)
);